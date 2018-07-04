<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiForo;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\AdminUsuario;
use Symfony\Component\Yaml\Yaml;

class ForoController extends Controller
{

   public function indexAction($app_id, Request $request)
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

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.colaborativo = 'true'
                                       AND e.pagina = p.id
                                       AND p.pagina IS NULL
                                       AND e.empresa = :empresa_id
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId()));
            $paginas = $query->getResult();

            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.foro IS NULL
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


            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       WHERE m.foro IS NULL
                                       ORDER BY m.id ASC");
            $coments = $query2->getResult();
        }

         foreach ($coments as $coment)
        {
            $comentarios[]= array('id'=>$coment->getId(),
                                  'mensaje'=>$coment->getMensaje(),
                                  'usuarioId'=>$coment->getUsuario()->getId(),
                                  'nombreUsuario'=>$coment->getUsuario()->getNombre(),
                                  'apellidoUsuario'=>$coment->getUsuario()->getApellido(),
                                  'fecharegistro'=>$coment->getFechaRegistro()->format("d/m/Y"),
                                  'delete_disabled'=>$f->linkEliminar($coment->getId(),'CertiForo'),
                                  'answer_delete'=>$answer_delete,
                                 );

        }


        return $this->render('LinkBackendBundle:Foro:index.html.twig', array('empresas' => $empresas,
                                                                             'paginas' => $paginas,
                                                                             'usuario_empresa' => $usuario_empresa,
                                                                             'comentarios' => $comentarios,
                                                                             'usuario' => $usuario));

    }

    public function ajaxPaginasForoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.colaborativo = 'true'
                                       AND e.pagina = p.id
                                       AND p.pagina IS NULL
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

    public function ajaxComentariosForoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';

        if($pagina_id == 0){
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.foro IS NULL
                                       AND m.empresa = :empresa_id
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $empresa_id));
        }else{
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.foro IS NULL
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
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($comentarios as $coment)
        {
            $delete_disabled = $f->linkEliminar($coment->getId(), 'CertiForo');
            $delete = $delete_disabled=='' ? 'delete' : '';
            
            $html .= '<tr>
                        <td class="respuesta'.$coment->getId().'">'.$coment->getMensaje().'</td>
                        <td>'.$coment->getUsuario()->getNombre().' '.$coment->getUsuario()->getApellido().'</td>
                        <td>'.$coment->getFechaRegistro()->format("d/m/Y").'</td>
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

    public function ajaxRespuestasComentariosForoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $foro_id = $request->query->get('foro_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';

        $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiForo m
                                   WHERE m.foro = :foro_id
                                   ORDER BY m.id ASC")
                     ->setParameters(array('foro_id' => $foro_id));
        
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
            $delete_disabled = $f->linkEliminar($coment->getId(), 'CertiForo');
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
                <input type="hidden" id="comentario_padre_foro_id" name="comentario_padre_foro_id" value="'.$foro_id.'">';
        $html .= '<div class="col text-right ">
                    <button type="button" class="bttn__nr add" data-toggle="modal" data-target="#formModal"><span class="fa fa-plus"></span><span class="text__nr">Responder</span></button>
                  </div>';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUpdateRespuestaForoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        $comentario_id = $request->request->get('comentario_id');
        $foro_id = $request->request->get('foro_id');
        $respuesta = $request->request->get('respuesta');

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $fecha_actual = date('Y/m/d H:m:s');

        if($comentario_id != ''){
            $new_respuesta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($comentario_id);
            $foro_padre = $new_respuesta->getForo();
        }else{
            $foro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
            $new_respuesta = new CertiForo();
        }
        
        $new_respuesta->setMensaje($respuesta);
        $new_respuesta->setFechaRegistro(new \DateTime($fecha_actual));
        $new_respuesta->setPagina($foro_padre->getPagina());
        $new_respuesta->setUsuario($usuario);
        $new_respuesta->setForo($foro_padre);
        $new_respuesta->setEmpresa($foro_padre->getEmpresa());
        
        $em->persist($new_respuesta);
        $em->flush();

        $return = array('id' => $new_respuesta->getId(),
                        'delete_disabled' =>$f->linkEliminar($new_respuesta->getId(),'CertiForo'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }
}
