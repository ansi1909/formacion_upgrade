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
        $rs = $this->get('reportes');
        
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

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();
        $roles=$session->get('usuario')['roles'];
        $answer_delete= (in_array($yml['parameters']['rol']['tutor'],$roles) || in_array($yml['parameters']['rol']['empresa'],$roles)) ? 1 : 0; //verifica si el usuario posee el rol tutor virtual(3) o empresa(5)
        $usuario_empresa = 0;
        $empresas = array();
        $paginas = array();
        $comentarios = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) 
        {

            $usuario_empresa = 1;
            $str = '';
            $tiene = 0;
            $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                        JOIN pe.pagina p
                                        WHERE p.pagina IS NULL
                                        AND pe.empresa = :empresa_id 
                                        ORDER BY p.nombre ASC")
                        ->setParameter('empresa_id', $usuario->getEmpresa()->getId());
            $pages = $query->getResult();


            foreach ($pages as $page)
            {
                $total = 0;
                $tiene++;
                $estructura = $f->obtenerEstructura($page->getPagina()->getId(),$yml);
                sort($estructura);
                foreach ($estructura as $id) {
                     $listado = $rs->interaccionMuro($page->getEmpresa()->getId(), $id);    
                     $total = $total + count($listado);
                }
               

               // $cantidad_comentarios = $this->cantidadComentarios($page->getPagina()->getId(), $usuario->getEmpresa()->getId());
                $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getPagina()->getId().'" p_str="'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().'">'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().' ('.$total.' '.$this->get('translator')->trans('comentarios').')';
                $subPaginas = $this->subPaginasEmpresa($page->getPagina()->getId(), $usuario->getEmpresa()->getId());
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

            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       JOIN LinkComunBundle:CertiPaginaEmpresa e
                                       WHERE e.activo = 'true'
                                       AND e.pagina = m.pagina
                                       AND e.empresa = m.empresa
                                       AND m.muro IS NULL
                                       AND m.empresa = :empresa_id
                                       ORDER BY m.id ASC")
                         ->setParameter('empresa_id', $usuario->getEmpresa()->getId());
            $coments = $query2->getResult();

        }
        else {

            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       ORDER BY e.nombre ASC");
            $empresas = $query->getResult();

            if ($empresa_id)
            {
                $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                            WHERE m.muro IS NULL
                                            AND m.empresa = :empresa_id")
                              ->setParameter('empresa_id', $empresa_id);;
            }
            else {
                $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                           WHERE m.muro IS NULL
                                           ORDER BY m.id ASC");
            }
            $coments = $query2->getResult();

        }

        foreach ($coments as $coment)
        {

            $query3 = $em->createQuery("SELECT COUNT (m.id) FROM LinkComunBundle:CertiMuro m
                                       WHERE m.muro = :muro_id")
                         ->setParameters(array('muro_id' => $coment->getId()));
            $hijos = $query3->getSingleScalarResult();

            $comentarios[]= array('id' => $coment->getId(),
                                  'mensaje' => $coment->getMensaje(),
                                  'usuarioId' => $coment->getUsuario()->getId(),
                                  'nombreUsuario' => $coment->getUsuario()->getNombre(),
                                  'apellidoUsuario' => $coment->getUsuario()->getApellido(),
                                  'fecharegistro' => $coment->getFechaRegistro()->format("d/m/Y"),
                                  'delete_disabled' => $f->linkEliminar($coment->getId(),'CertiMuro'),
                                  'answer_delete' => $answer_delete,
                                  'hijos' => $hijos);

        }

        if ($empresa_id)
        {

            $str = '';
            $tiene = 0;
            $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                        JOIN pe.pagina p
                                        WHERE p.pagina IS NULL
                                        AND pe.empresa = :empresa_id 
                                        ORDER BY p.nombre ASC")
                        ->setParameter('empresa_id', $empresa_id);
            $pages = $query->getResult();


            foreach ($pages as $page)
            {
                $total = 0;
                $tiene++;
                $estructura = $f->obtenerEstructura($page->getPagina()->getId(),$yml);
                sort($estructura);
                foreach ($estructura as $id) {
                     $listado = $rs->interaccionMuro($page->getEmpresa()->getId(), $id);    
                     $total = $total + count($listado);
                }
                //$cantidad_comentarios = $this->cantidadComentarios($page->getPagina()->getId(), $empresa_id);
                $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getPagina()->getId().'" p_str="'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().'">'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().' ('.$total.' '.$this->get('translator')->trans('comentarios').')';
                $subPaginas = $this->subPaginasEmpresa($page->getPagina()->getId(), $empresa_id);
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
                                                                             'usuario' => $usuario,
                                                                             'empresa_id_a' => $empresa_id));

    }

    public function ajaxPaginasMuroAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa pe
                                       WHERE pe.pagina = p.id
                                       AND pe.empresa = :empresa_id
                                       ORDER BY p.nombre ASC")
                    ->setParameters(array('empresa_id' => $empresa_id));
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $options .= '<option value="'.$pagina->getId().'">'.$pagina->getCategoria()->getNombre().': '.$pagina->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxComentariosMuroAction(Request $request)
    {
        
        $session = new Session();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $usuario_id = $request->query->get('usuario_id');
        $html = '';

        $roles = $session->get('usuario')['roles'];
        $answer_delete= (in_array($yml['parameters']['rol']['tutor'],$roles) || in_array($yml['parameters']['rol']['empresa'],$roles)) ? 1:0; //verifica si el usuario posee el rol tutor virtual(3) o empresa(5)
       

        if($pagina_id == 0){
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       WHERE m.empresa=:empresa_id
                                       AND m.muro IS NULL
                                       ORDER BY m.id ASC")
                         ->setParameter('empresa_id', $empresa_id);
            $comentarios = $query2->getResult();
        }
        else {


            $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            if ($pagina->getCategoria()->getId() == $yml['parameters']['categoria']['programa'] || $pagina->getCategoria()->getId() == $yml['parameters']['categoria']['curso']) {
              $paginas_id = $f->obtenerEstructura($pagina_id,$yml);
              sort($paginas_id);
            }
            else{
              $paginas_id = array($pagina_id);
            }
            $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                       WHERE m.empresa=:empresa_id
                                       AND m.muro IS NULL
                                       AND m.pagina IN (:paginas_id)
                                       ORDER BY m.id ASC")
                         ->setParameters(array('empresa_id' => $empresa_id,
                                               'paginas_id' => $paginas_id));
            $comentarios = $query2->getResult();

        }
        $html .= '<table class="table" id="dt">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Mensaje').'</th>
                                <th>'.$this->get('translator')->trans('Usuario').'</th>
                                <th>'.$this->get('translator')->trans('Fecha').'</th>
                                <th>'.$this->get('translator')->trans('Respuestas').'</th>
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';
       // return new response(var_dump($comentarios));
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
                            if ($coment->getUsuario()->getId() !== $usuario_id )
                            {
                                $html .= '<a href="#" title="'.$this->get('translator')->trans("Responder").'" class="btn btn-link btn-sm add" data-toggle="modal" data-target="#formModal" data="'.$coment->getId().'"><span class="fa fa-plus"></span></a>';
                            }
                            $html .= '<a href="#history_programation" title="'.$this->get('translator')->trans("Ver").'" class="btn btn-link btn-sm see" data="'.$coment->getId().'"><span class="fa fa-eye"></span></a>
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
        $panel = '';

        $muro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

        $query2 = $em->createQuery("SELECT m FROM LinkComunBundle:CertiMuro m
                                   WHERE m.muro = :muro_id
                                   ORDER BY m.id ASC")
                     ->setParameters(array('muro_id' => $muro_id));
        
        $comentarios = $query2->getResult();

        $html .= '<table class="table" id="dtSub">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Mensaje').'</th>
                                <th>'.$this->get('translator')->trans('Participante').'</th>
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

        // Información del comentario padre en un panel separado
        $leccion_nombre = $muro->getPagina()->getNombre();
        $leccion_categoria = $muro->getPagina()->getCategoria()->getNombre();

        // Materia
        $materia = $muro->getPagina()->getPagina() ? $muro->getPagina()->getPagina() : 0;
        $materia_nombre = $materia ? $materia->getNombre() : '';
        $materia_categoria = $materia ? $materia->getCategoria()->getNombre() : '';

        // Módulo
        $modulo = $materia ? $materia->getPagina() ? $materia->getPagina() : 0 : 0;
        $modulo_nombre = $modulo ? $modulo->getNombre() : '';
        $modulo_categoria = $modulo ? $modulo->getCategoria()->getNombre() : '';

        // Programa
        $programa = $modulo ? $modulo->getPagina() ? $modulo->getPagina() : 0 : 0;
        $programa_nombre = $programa ? $programa->getNombre() : '';
        $programa_categoria = $programa ? $programa->getCategoria()->getNombre() : '';

        $panel = '<div class="card">
                    <div class="card-header gradiente">
                        <h5 class="card-title">'.$this->get('translator')->trans("Comentario seleccionado").'</h5>
                    </div>
                    <div class="card-block">
                        <table class="table">
                            <tbody>';
                                if ($programa)
                                {
                                    $panel .= '<tr>';
                                    $panel .= '<th><b>'.$programa_categoria.'<b></th>';
                                    $panel .= '<td>'.$programa_nombre.'</td>';
                                    $panel .= '</tr>';
                                }
                                if ($modulo)
                                {
                                    $panel .= '<tr>';
                                    $panel .= '<th><b>'.$modulo_categoria.'<b></th>';
                                    $panel .= '<td>'.$modulo_nombre.'</td>';
                                    $panel .= '</tr>';
                                }
                                if ($materia)
                                {
                                    $panel .= '<tr>';
                                    $panel .= '<th><b>'.$materia_categoria.'<b></th>';
                                    $panel .= '<td>'.$materia_nombre.'</td>';
                                    $panel .= '</tr>';
                                }
                                $panel .= '<tr>
                                    <th><b>'.$leccion_categoria.'<b></th>
                                    <td>'.$leccion_nombre.'</td>
                                </tr>
                                <tr>
                                    <th><b>'.$this->get('translator')->trans("Comentario").'<b></th>
                                    <td>'.$muro->getMensaje().'</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>';

        $return = array('html' => $html,
                        'panel' => $panel);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUpdateRespuestaMuroAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $comentario_id = $request->request->get('comentario_id');
        $muro_id = $request->request->get('muro_id');
        $respuesta = $request->request->get('respuesta');
        $tipoMensaje = 'Respondió';

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

         //$fecha_actual = date('Y/m/d H:m:s');
         $fecha_actual =  new \DateTime('now');

        if($comentario_id != ''){
            $new_respuesta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($comentario_id);
            $muro_padre = $new_respuesta->getMuro();
        }
        else {
            $muro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);
            $new_respuesta = new CertiMuro();
            $new_respuesta->setPagina($muro_padre->getPagina());
            $new_respuesta->setEmpresa($muro_padre->getEmpresa());
        }
        
        $new_respuesta->setMensaje($respuesta);
        $new_respuesta->setFechaRegistro($fecha_actual);
        $new_respuesta->setUsuario($usuario);
        $new_respuesta->setMuro($muro_padre);
        $em->persist($new_respuesta);
        $em->flush();

        $descripcion = $f->tipoDescripcion($tipoMensaje, $new_respuesta, $muro_padre->getUsuario()->getLogin());
        $f->newAlarm($yml['parameters']['tipo_alarma']['respuesta_muro'], $descripcion, $muro_padre->getUsuario(), $muro_padre->getId());

        $return = array('id' => $new_respuesta->getId(),
                        'delete_disabled' =>$f->linkEliminar($new_respuesta->getId(),'CertiMuro'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    // Retorna los pagina_id de categoría Recurso de un pagina_id dado
    protected function leccionesId($pagina_id, $lecciones_id=array())
    {

        $subpaginas = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->findByPagina($pagina_id);

        if (!$subpaginas)
        {
            // Ya esta pagina_id es una lección
            $lecciones_id[] = $pagina_id;
        }
        else {
            foreach ($subpaginas as $subpagina)
            {
                $lecciones_id = $this->leccionesId($subpagina->getId(), $lecciones_id);
            }
        }

        return $lecciones_id;
        
    }

    protected function subPaginasEmpresa($pagina_id, $empresa_id)
    {

        $em = $this->getDoctrine()->getManager();
        $subpaginas = array();
        $tiene = 0;
        $return = '';
        $f = $this->get('funciones');
        $rs = $this->get('reportes');

        $query = $em->createQuery("SELECT pe, p FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                    JOIN pe.pagina p 
                                    WHERE pe.empresa = :empresa_id AND p.pagina = :pagina_id 
                                    ORDER BY p.orden ASC")
                    ->setParameters(array('empresa_id' => $empresa_id,
                                          'pagina_id' => $pagina_id));
        $subpages = $query->getResult();
        
        foreach ($subpages as $subpage)
        {
            $tiene++;
            $listado = $rs->interaccionMuro($subpage->getEmpresa()->getId(), $subpage->getPagina()->getId());    
            $cantidad = count($listado);
            //$cantidad_comentarios = $this->cantidadComentarios($subpage->getPagina()->getId(), $empresa_id);
            $return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getPagina()->getId().'" p_str="'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().'">'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().' ('.$cantidad.' '.$this->get('translator')->trans('comentarios').')';
            $subPaginas = $this->subPaginasEmpresa($subpage->getPagina()->getId(), $subpage->getEmpresa()->getId());
            if ($subPaginas['tiene'] > 0)
            {
                $return .= '<ul>';
                $return .= $subPaginas['return'];
                $return .= '</ul>';
            }
            $return .= '</li>';
        }

        $subpaginas = array('tiene' => $tiene,
                            'return' => $return);

        return $subpaginas;

    }

    protected function cantidadComentarios($pagina_id, $empresa_id)
    {

        $em = $this->getDoctrine()->getManager();

        $paginas_id = $this->leccionesId($pagina_id);

        $query = $em->createQuery("SELECT COUNT (m.id) FROM LinkComunBundle:CertiMuro m
                                   JOIN LinkComunBundle:CertiPaginaEmpresa pe
                                   WHERE pe.pagina = m.pagina
                                   AND pe.empresa = m.empresa
                                   AND m.muro IS NULL
                                   AND m.empresa = :empresa_id
                                   AND m.pagina IN (:paginas_id)")
                     ->setParameters(array('empresa_id' => $empresa_id,
                                           'paginas_id' => $paginas_id));
        $cantidad_comentarios = $query->getSingleScalarResult();

        return $cantidad_comentarios;

    }
}
