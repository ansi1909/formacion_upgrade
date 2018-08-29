<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiMuro;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\AdminUsuario;
use Symfony\Component\Yaml\Yaml;

class MuroController extends Controller
{

   public function indexAction($app_id, $empresa_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));
        $em = $this->getDoctrine()->getManager();
        $roles=$session->get('usuario')['roles'];
        $answer_delete= (in_array(3,$roles) || in_array(5,$roles)) ? 1:0;//verifica si el usuario posee el rol tutor virtual(3) o empresa(5)
        $usuario_empresa = 0;
        $empresas = array();
        $paginas = array();
        $comentarios = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        //return new Response (var_dump($empresa_id));

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.muroActivo = 'true'
                                       AND e.pagina = p.id
                                       AND p.pagina IS NULL
                                       AND e.empresa = :empresa_id
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId()));
            $paginas = $query->getResult();

            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.muro IS NULL
                                       AND m.empresa = :empresa_id
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId()));
            $coments = $query2->getResult();

        }
        else {
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = 'true'
                                       ORDER BY e.id ASC");
            $empresas = $query->getResult();


            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       WHERE m.muro IS NULL
                                       ORDER BY m.id ASC");
            $coments = $query2->getResult();
        }

         foreach ($coments as $coment)
        {
            $query3 = $em->createQuery("SELECT COUNT (m.id) FROM LinkComunBundle:CertiMuro m
                                       WHERE m.muro = :muro_id")
                         ->setParameters(array('muro_id' => $coment->getId()));
            $hijos = $query3->getSingleScalarResult();



            $comentarios[]= array('id'=>$coment->getId(),
                                  'mensaje'=>$coment->getMensaje(),
                                  'usuarioId'=>$coment->getUsuario()->getId(),
                                  'nombreUsuario'=>$coment->getUsuario()->getNombre(),
                                  'apellidoUsuario'=>$coment->getUsuario()->getApellido(),
                                  'fecharegistro'=>$coment->getFechaRegistro()->format("d/m/Y"),
                                  'delete_disabled'=>$f->linkEliminar($coment->getId(),'CertiMuro'),
                                  'answer_delete'=>$answer_delete,
                                  'hijos'=>$hijos);

        }

        //return new Response (var_dump($empresa_id));

        if ($empresa_id)
        {

            $str = '';
            $tiene = 0;
            $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                        JOIN pe.pagina p
                                        WHERE p.pagina IS NULL
                                        AND pe.empresa = :empresa_id 
                                        ORDER BY p.id ASC")
                        ->setParameter('empresa_id', $empresa_id);
            $pages = $query->getResult();

            //return new Response (var_dump($empresa_id));

            foreach ($pages as $page)
            {
                $tiene++;
                $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getPagina()->getId().'" p_str="'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().'">'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre();
                $subPaginas = $f->subPaginasEmpresa($page->getPagina()->getId(), $empresa_id);
                if ($subPaginas['tiene'] > 0)
                {
                    $str .= '<ul>';
                    $str .= $subPaginas['return'];
                    $str .= '</ul>';
                }
                $str .= '</li>';
            }

            $paginas = array('tiene' => $tiene,
                             'str' => $str);

        }


        return $this->render('LinkBackendBundle:Muro:index.html.twig', array('empresas' => $empresas,
                                                                             'paginas' => $paginas,
                                                                             'usuario_empresa' => $usuario_empresa,
                                                                             'comentarios' => $comentarios,
                                                                             'usuario' => $usuario));

    }

    public function ajaxPaginasMuroAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.muroActivo = 'true'
                                       AND e.pagina = p.id
                                       AND e.empresa = :empresa_id
                                       ORDER BY p.nombre ASC")
                    ->setParameters(array('empresa_id' => $empresa_id));
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $options .= '<option value="'.$pagina->getId().'">'.$pagina->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxComentariosMuroAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';

        if($pagina_id == 0){
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.muro IS NULL
                                       AND m.empresa = :empresa_id
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $empresa_id));
        }else{
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.muro IS NULL
                                       AND m.empresa = :empresa_id
                                       AND m.pagina = :pagina_id
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $empresa_id,
                                               'pagina_id' => $pagina_id));
        }
        
        
        $comentarios = $query2->getResult();

        $html .= '<table class="table" id="dt">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Mensaje').'</th>
                                <th>'.$this->get('translator')->trans('usuario').'</th>
                                <th>'.$this->get('translator')->trans('Fecha').'</th>
                                <th>'.$this->get('translator')->trans('Respuestas').'</th>
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($comentarios as $coment)
        {
            $delete_disabled = $f->linkEliminar($coment->getId(), 'CertiMuro');
            $delete = $delete_disabled=='' ? 'delete' : '';

            $query3 = $em->createQuery("SELECT COUNT (m.id) FROM LinkComunBundle:CertiMuro m
                                       WHERE m.muro = :muro_id")
                         ->setParameters(array('muro_id' => $coment->getId()));
            $hijos = $query3->getSingleScalarResult();
            
            $html .= '<tr>
                        <td class="respuesta'.$coment->getId().'">'.$coment->getMensaje().'</td>
                        <td>'.$coment->getUsuario()->getNombre().' '.$coment->getUsuario()->getApellido().'</td>
                        <td>'.$coment->getFechaRegistro()->format("d/m/Y").'</td>
                        <td>'. $hijos .' </td>
                        <td class="center">';
                          if($coment->getUsuario()->getId() == $usuario_id){
                             $html .= '<a href="#" title="'.$this->get('translator')->trans("Editar").'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-pencil"></span></a>';
                          }
                           $html .= '<a href="#" title="'.$this->get('translator')->trans("Responder").'" class="btn btn-link btn-sm add" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-plus"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans("Ver").'" class="btn btn-link btn-sm see" data="'.$coment->getId().'"><span class="fa fa-eye"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans("Eliminar").'" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$coment->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
        }
        $html .= '</tbody>
                </table>';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxRespuestasComentariosMuroAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $muro_id = $request->query->get('muro_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';

        $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                   WHERE m.muro = :muro_id
                                   ORDER BY m.id ASC")
                     ->setParameters(array('muro_id' => $muro_id));
        
        $comentarios = $query2->getResult();

        
        $html .= '<table class="table" id="dtSub">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Mensaje').'</th>
                                <th>'.$this->get('translator')->trans('usuario').'</th>
                                <th>'.$this->get('translator')->trans('Fecha').'</th>
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($comentarios as $coment)
        {
            $delete_disabled = $f->linkEliminar($coment->getId(), 'CertiMuro');
            $delete = $delete_disabled=='' ? 'delete' : '';
            
            $html .= '<tr>
                        <td class="respuesta'.$coment->getId().'">'.$coment->getMensaje().'</td>
                        <td>'.$coment->getUsuario()->getNombre().' '.$coment->getUsuario()->getApellido().'</td>
                        <td>'.$coment->getFechaRegistro()->format("d/m/Y").'</td>
                        <td class="center">';
                          if($coment->getUsuario()->getId() == $usuario_id){
                             $html .= '<a href="#" title="'.$this->get('translator')->trans("Editar").'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-pencil"></span></a>';
                          }
                           $html .= '<a href="#" title="'.$this->get('translator')->trans("Eliminar").'" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$coment->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
        }
        $html .= '</tbody>
                </table>
                <input type="hidden" id="comentario_padre_muro_id" name="comentario_padre_muro_id" value="'.$muro_id.'">';
        $html .= '<div class="col text-right ">
                    <button type="button" class="bttn__nr add" data-toggle="modal" data-target="#formModal"><span class="fa fa-plus"></span><span class="text__nr">Responder</span></button>
                  </div>';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUpdateRespuestaMuroAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        $comentario_id = $request->request->get('comentario_id');
        $muro_id = $request->request->get('muro_id');
        $respuesta = $request->request->get('respuesta');

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $fecha_actual = date('Y/m/d H:m:s');

        if($comentario_id != ''){
            $new_respuesta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($comentario_id);
            $muro_padre = $new_respuesta->getMuro();
        }else{
            $muro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);
            $new_respuesta = new CertiMuro();
        }
        
        $new_respuesta->setMensaje($respuesta);
        $new_respuesta->setFechaRegistro(new \DateTime($fecha_actual));
        $new_respuesta->setPagina($muro_padre->getPagina());
        $new_respuesta->setUsuario($usuario);
        $new_respuesta->setMuro($muro_padre);
        $new_respuesta->setEmpresa($muro_padre->getEmpresa());
        
        $em->persist($new_respuesta);
        $em->flush();

        $return = array('id' => $new_respuesta->getId(),
                        'delete_disabled' =>$f->linkEliminar($new_respuesta->getId(),'CertiMuro'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }
}
