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

   public function indexAction($app_id, Request $request)
    {
        /*$session = new Session();
        $f = $this->get('funciones');
        if (!$session->get('ini'))
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
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 
        $tipo_destino = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoDestino')->findAll();

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
            $notificaciones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->findByEmpresa($usuario->getEmpresa());
        }
        else {
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = 'true'
                                       ORDER BY e.id ASC");
            $empresas = $query->getResult();
            
            $query2 = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNotificacion n
                                       JOIN LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = 'true' AND e.id = n.empresa
                                       ORDER BY n.id ASC");
            $notificaciones = $query2->getResult();
        }



        return $this->render('LinkBackendBundle:Programados:index.html.twig', array('empresas' => $empresas,
                                                                                    'usuario_empresa' => $usuario_empresa,
                                                                                    'notificaciones' => $notificaciones,
                                                                                    'tipo_destino' => $tipo_destino,
                                                                                    'usuario' => $usuario));*/

        return new Response(
            '<html><body>Hello Wordl</body></html>'
        );
    }

   /* public function ajaxNotificacionAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $usuario_empresa = $request->query->get('usuario_empresa');
        $f = $this->get('funciones');

        $qb = $em->createQueryBuilder();

        if ($empresa_id)
        {
            $qb->select('u')
               ->from('LinkComunBundle:AdminNotificacion', 'u');
            $qb->andWhere('u.empresa = :empresa_id');
            $parametros['empresa_id'] = $empresa_id;
            $qb->setParameters($parametros);
        }else{
            $qb->select('u')
               ->from('LinkComunBundle:AdminNotificacion', 'u');
        }

        $query = $qb->getQuery();
        $notificaciones_db = $query->getResult();
        $notificaciones = '';
        $notificaciones .= '<table class="table" id="dt">
                            <thead class="sty__title">
                                <tr>
                                    <th>'.$this->get('translator')->trans('Asunto').'</th>';
                                    if ($usuario_empresa == 0){
                                        $notificaciones .= '<th>'.$this->get('translator')->trans('Empresa').'</th>';
                                    }
                                    $notificaciones .= '<th>'.$this->get('translator')->trans('Tipo notificación').'</th>
                                    <th>'.$this->get('translator')->trans('Acciones').'</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach ($notificaciones_db as $notificacion) {
            $delete_disabled = $f->linkEliminar($notificacion->getId(), 'AdminNotificacion');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $notificaciones .= '<tr>
                                    <td>'.$notificacion->getAsunto().'</td>
                                    <input type="hidden" id="asuntoTable'.$notificacion->getId().'" value="'.$notificacion->getAsunto().'">
                                    <td>'.$notificacion->getEmpresa()->getNombre().'</td><td>'.$notificacion->getTipoNotificacion()->getNombre().'</td>
                                    <td class="center">
                                        <a href="#" title="'.$this->get('translator')->trans('Nuevo registro').'" class="btn btn-link btn-sm add" data-toggle="modal" data-target="#formModal" data="'.$notificacion->getId().'"><span class="fa fa-plus"></span></a>
                                        <a href="#" title="'.$this->get('translator')->trans('Ver historial').'" class="btn btn-link btn-sm see" data="'.$notificacion->getId().'"><span class="fa fa-eye"></span></a>
                                    </td>
                                </tr>';
        }
        $notificaciones .= '</tbody>
                        </table>';
        
        $return = array('notificaciones' => $notificaciones);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxHistoryProgramationsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $notificacion_id = $request->query->get('notificacion_id');
        $html = '';
        $fecha_actual = date('d/m/Y');
        $deshabilitado = "";
        $trclass = "";
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                   WHERE p.notificacion = :notificacion_id 
                                   AND p.grupo IS NULL
                                   ORDER BY p.id ASC")
                            ->setParameters(array('notificacion_id' => $notificacion->getId()));
        $notificaciones_programadas = $query->getResult();

        //$notificaciones_programadas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByNotificacion($notificacion_id);
        $entidad = '';

        $html .= '<table class="table" id="dtSub">
                        <thead class="sty__title">
                            <tr>
                                <th>'.$this->get('translator')->trans('Destino').'</th>
                                <th>'.$this->get('translator')->trans('Entidad').'</th>
                                <th>'.$this->get('translator')->trans('Fecha').'</th>
                                <th>'.$this->get('translator')->trans('Acciones').'</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach ($notificaciones_programadas as $notificacion_programada)
        {
            $delete_disabled = $f->linkEliminar($notificacion_programada->getId(), 'AdminNotificacionProgramada');
            $delete = $delete_disabled=='' ? 'delete' : '';
            if($notificacion_programada->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa']){

                $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidad = $programa->getNombre();

            }elseif($notificacion_programada->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['nivel']){
                $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($notificacion_programada->getEntidadId());
                $entidad = $nivel->getNombre();

            }elseif($notificacion_programada->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo']){
                $programacion_grupo = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $entidad .= '<div class="tree">
                                <ul data-jstree=\'{ "opened": true }\'>
                                    <li data-jstree=\'{ "icon": "fa fa-angle-double-right", "opened" : true }\'>'.$this->get('translator')->trans('Usuarios').'
                                            <ul>';
                                                foreach ($programacion_grupo as $programada){
                                                    $usuario_programado = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($programada->getEntidadId());
                                                    $entidad .= '<li data-jstree=\'{ "icon": "fa fa-angle-right" }\'>'.$usuario_programado->getNombre().' '.$usuario_programado->getApellido().'</li>';
                                                }
                                $entidad .= '</ul>
                                    </li>
                                </ul>
                            </div>';
            }elseif($notificacion_programada->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['no_ingresado_programa']){
                $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidad = $programa->getNombre();

            }else{
                $entidad = 'N/A';
            }
            if ($notificacion_programada->getEnviado()) {
                $trclass = 'class="table-active"';
                $deshabilitado = 'disabled';
            }
            $html .= '<tr '.$trclass.'>
                        <td>'.$notificacion_programada->getTipoDestino()->getNombre().'</td>
                        <td>'.$entidad.'</td>
                        <td>'.$notificacion_programada->getFechaDifusion()->format("d/m/Y").'</td>
                        <td class="center">
                            <input type="hidden" id="hidden_notificacion_id" name="hidden_notificacion_id" value="'.$notificacion->getId().'">
                            <input type="hidden" id="asuntoTableEdit'.$notificacion->getId().'" value="'.$notificacion->getAsunto().'">
                            <a href="#" class="btn btn-link btn-sm edit edit_programacion '.$deshabilitado.'" data-toggle="modal" data-target="#formModal" data="'.$notificacion_programada->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" class="btn btn-link btn-sm '.$deshabilitado.' '.$delete.' '.$delete_disabled.'" data="'.$notificacion_programada->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
            $entidad = '';
            $trclass = '';
            $deshabilitado = '';
        }
        $html .= '</tbody>
                </table>';

        $return = array('html' => $html,
                        'notificacion' => $notificacion->getAsunto());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTipoDestinoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $tipo_destino_id = $request->query->get('tipo_destino_id');
        $notificacion_id = $request->query->get('notificacion_id');
        $f = $this->get('funciones');
        $formulario = '';
        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        $tipo_destino = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoDestino')->find($tipo_destino_id);
        $fecha_difusion = date('d/m/Y');
        $aviso = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if($tipo_destino->getId() == $yml['parameters']['tipo_destino']['nivel']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findByEmpresa($notificacion->getEmpresa()->getId());
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el nivel').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($niveles as $nivel) {
                            $formulario .='<option value="'.$nivel->getId().'" >'.$nivel->getNombre().'</option>';
                        }
             $formulario .= '</select>
                        <span class="fa fa-line-chart"></span>
                        <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                    </div>
                </div>
                <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';
            
        }elseif($tipo_destino->getId() == $yml['parameters']['tipo_destino']['programa']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa pe 
                                       WHERE p.estatusContenido = :estatus
                                       AND pe.activo = :activo
                                       AND pe.empresa = :empresa
                                       AND pe.pagina = p.id
                                       AND p.pagina IS NULL
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId(),
                                              'activo' => true,
                                              'estatus' => $yml['parameters']['estatus_contenido']['activo']));

            $programas_asignados = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa) {
                            $formulario .='<option value="'.$programa->getId().'" >'.$programa->getNombre().'</option>';
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>
            <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';

            
        }elseif($tipo_destino->getId() == $yml['parameters']['tipo_destino']['grupo']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                       WHERE u.activo = 'true'
                                       AND u.empresa = :empresa
                                       ORDER BY u.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId()));
            $usuarios_grupo = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el/los usuarios').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select multiple=multiple class="form-control form_sty_sel form_sty_modal" id="entidad_id_grupo" name="entidad_id_grupo[]" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($usuarios_grupo as $usuario_grupo) {
                            $formulario .='<option value="'.$usuario_grupo->getId().'" >'.$usuario_grupo->getNombre().' '.$usuario_grupo->getApellido().' ('.$usuario_grupo->getLogin().')</option>';
                        }
             $formulario .= '</select>
                            </div>
                        </div>
                        <label id="entidad_id_grupo-error" class="error" for="entidad_id_grupo" style="display:none;"></label>';
            
        }elseif($tipo_destino->getId() == $yml['parameters']['tipo_destino']['no_ingresado_programa']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Esta notificación será enviada por el sistema de forma automática, puede dejar la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa pe 
                                       WHERE p.estatusContenido = :estatus
                                       AND pe.activo = :activo
                                       AND pe.empresa = :empresa
                                       AND pe.pagina = p.id
                                       AND p.pagina IS NULL
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId(),
                                              'activo' => true,
                                              'estatus' => $yml['parameters']['estatus_contenido']['activo']));

            $programas_asignados = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa) {
                            $formulario .='<option value="'.$programa->getId().'" >'.$programa->getNombre().'</option>';
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>
            <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';
            
        }elseif($tipo_destino->getId() == $yml['parameters']['tipo_destino']['aprobados']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Esta notificación será enviada por el sistema de forma automática, puede dejar la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa pe 
                                       WHERE p.estatusContenido = :estatus
                                       AND pe.activo = :activo
                                       AND pe.empresa = :empresa
                                       AND pe.pagina = p.id
                                       AND p.pagina IS NULL
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId(),
                                              'activo' => true,
                                              'estatus' => $yml['parameters']['estatus_contenido']['activo']));

            $programas_asignados = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa) {
                            $formulario .='<option value="'.$programa->getId().'" >'.$programa->getNombre().'</option>';
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>
            <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';
            
        }elseif($tipo_destino->getId() == $yml['parameters']['tipo_destino']['todos']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';

        }else{

            $aviso = '<em><strong>'.$this->get('translator')->trans('Esta notificación será enviada por el sistema de forma automática, puede dejar la fecha de hoy').'</strong></em>';

        }

        $formulario .='<div class="jsonfileds">
                            <label for="fecha_difusion" class="form-control-label">'.$this->get('translator')->trans('Seleccione fecha difusión').':</label>
                            <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                                <input type="text" class="form-control form_sty1" name="fecha_difusion" id="fecha_difusion" value="'.$fecha_difusion.'" style="border-radius: 5px;" data-date-start-date="0d">
                                <span class="fa fa-calendar"></span>
                                '.$aviso.'
                            </div>
                        </div>
                        <label id="fecha_difusion-error" class="error" for="fecha_difusion" style="display:none;"></label>';
        
        $return = array('formulario' => $formulario);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

   public function ajaxUpdateProgramationsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        $notificacion_id = $request->request->get('notificacion_id');
        $programacion_id = $request->request->get('programacion_id');
        $tipo_destino_id = $request->request->get('tipo_destino_id');
        $entidad_id = $request->request->get('entidad_id');
        $entidad_id_grupo = $request->request->get('entidad_id_grupo');
        $fecha_difusion = trim($request->request->get('fecha_difusion'));
        $fv = explode("/", $fecha_difusion);
        $difusion = $fv[2].'-'.$fv[1].'-'.$fv[0];

        $tipo_destino = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoDestino')->find($tipo_destino_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if($tipo_destino->getId() == $yml['parameters']['tipo_destino']['grupo']){

            if ($programacion_id)
            {
                // actualizo la programacion padre
                $programacion = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($programacion_id);
                $programacion->setFechaDifusion(new \DateTime($difusion));
                $programacion->setNotificacion($notificacion);
                $programacion->setTipoDestino($tipo_destino);
                $programacion->setUsuario($usuario);
                
                $em->persist($programacion);
                $em->flush();

                // se me ocurre eliminar los registros de este grupo, y dejar solo el principal porque no se como determinar si un usurio fe deseleccionado
                $programacion_grupo = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($programacion->getId());
                foreach ($programacion_grupo as $individual){
                        $em->remove($individual);
                        $flush = $em->flush();
                }
                // una vez eliminados se crean los nuevos
                foreach ($entidad_id_grupo as $entidad){

                        $programacion_nuevo_grupo = new AdminNotificacionProgramada();
                        $programacion_nuevo_grupo->setEntidadId($entidad);
                        $programacion_nuevo_grupo->setFechaDifusion(new \DateTime($difusion));
                        $programacion_nuevo_grupo->setNotificacion($notificacion);
                        $programacion_nuevo_grupo->setTipoDestino($tipo_destino);
                        $programacion_nuevo_grupo->setUsuario($usuario);
                        $programacion_nuevo_grupo->setGrupo($programacion);
                        
                        $em->persist($programacion_nuevo_grupo);
                        $em->flush();
                }

                $this->sendNowEmail($programacion->getId());
                
                $this->sendNowEmail($programacion->getId());
                
            }
            else {

                $programacion = new AdminNotificacionProgramada();
                $programacion->setFechaDifusion(new \DateTime($difusion));
                $programacion->setNotificacion($notificacion);
                $programacion->setTipoDestino($tipo_destino);
                $programacion->setUsuario($usuario);
                
                $em->persist($programacion);
                $em->flush();

                foreach ($entidad_id_grupo as $entidad){

                        $programacion_nuevo_grupo = new AdminNotificacionProgramada();
                        $programacion_nuevo_grupo->setEntidadId($entidad);
                        $programacion_nuevo_grupo->setFechaDifusion(new \DateTime($difusion));
                        $programacion_nuevo_grupo->setNotificacion($notificacion);
                        $programacion_nuevo_grupo->setTipoDestino($tipo_destino);
                        $programacion_nuevo_grupo->setUsuario($usuario);
                        $programacion_nuevo_grupo->setGrupo($programacion);
                        
                        $em->persist($programacion_nuevo_grupo);
                        $em->flush();
                }

                $this->sendNowEmail($programacion->getId());
                
                $this->sendNowEmail($programacion->getId());
                
            }

        }else{

           if ($programacion_id)
            {
                $programacion = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($programacion_id);
            }
            else {
                $programacion = new AdminNotificacionProgramada();
            }

            $programacion->setEntidadId($entidad_id);
            $programacion->setFechaDifusion(new \DateTime($difusion));
            $programacion->setNotificacion($notificacion);
            $programacion->setTipoDestino($tipo_destino);
            $programacion->setUsuario($usuario);
            
            $em->persist($programacion);
            $em->flush();

            $this->sendNowEmail($programacion->getId());
         
        }

        
                    
        $return = array('id' => $programacion->getId(),
                        'asunto' =>$notificacion->getAsunto(),
                        'destino' =>$programacion->getTipoDestino()->getNombre(),
                        'fecha' =>$fecha_difusion,
                        'delete_disabled' =>$f->linkEliminar($programacion->getId(),'AdminNotificacionProgramada'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditProgramationsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $programacion_id = $request->query->get('programacion_id');
        $notificacion_id = $request->query->get('notificacion_id');
        $f = $this->get('funciones');
        $formulario = '';
        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        $programacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($programacion_id);
        $fecha_difusion = $programacion->getFechaDifusion()->format('d/m/Y');
        $aviso = '';
        $selected = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['nivel']){


            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findByEmpresa($notificacion->getEmpresa()->getId());
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el nivel').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($niveles as $nivel) {
                            if($nivel->getId() == $programacion->getEntidadId()){
                                $selected = 'selected="selected"';
                            }
                            $formulario .='<option value="'.$nivel->getId().'" '.$selected.'>'.$nivel->getNombre().'</option>';
                            $selected = '';
                        }
             $formulario .= '</select>
                        <span class="fa fa-line-chart"></span>
                        <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                    </div>
                </div>
                <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';
            
        }elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa pe 
                                       WHERE p.estatusContenido = :estatus
                                       AND pe.activo = :activo
                                       AND pe.empresa = :empresa
                                       AND pe.pagina = p.id
                                       AND p.pagina IS NULL
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId(),
                                              'activo' => true,
                                              'estatus' => $yml['parameters']['estatus_contenido']['activo']));

            $programas_asignados = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa) {
                            if($programa->getId() == $programacion->getEntidadId()){
                                $selected = 'selected="selected"';
                            }
                            $formulario .='<option value="'.$programa->getId().'" '.$selected.' >'.$programa->getNombre().'</option>';
                            $selected = '';
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>
            <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';
            
        }elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                       WHERE u.activo = 'true'
                                       AND u.empresa = :empresa
                                       ORDER BY u.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId()));
            $usuarios_grupo = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el/los usuarios').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select multiple=multiple class="form-control form_sty_sel form_sty_modal" id="entidad_id_grupo" name="entidad_id_grupo[]" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($usuarios_grupo as $usuario_grupo) {
                            $query = $em->createQuery("SELECT np FROM LinkComunBundle:AdminNotificacionProgramada np 
                                                       WHERE np.entidadId = :usuario_id
                                                       AND np.grupo = :grupo_id
                                                       AND np.notificacion = :notificacion_id
                                                       ORDER BY np.id ASC")
                            ->setParameters(array('usuario_id' => $usuario_grupo->getId(),
                                                  'grupo_id' => $programacion->getId(),
                                                  'notificacion_id' => $notificacion->getId()));

                            $programacion_grupo = $query->getResult();

                            if(count($programacion_grupo) > 0){
                                $selected = 'selected="selected"';
                            }
                            $formulario .='<option value="'.$usuario_grupo->getId().'" '.$selected.' >'.$usuario_grupo->getNombre().' '.$usuario_grupo->getApellido().' ('.$usuario_grupo->getLogin().')</option>';
                            $selected = '';
                        }
             $formulario .= '</select>
                            </div>
                        </div>
                        <label id="entidad_id_grupo-error" class="error" for="entidad_id_grupo" style="display:none;"></label>';
            
        }elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['no_ingresado_programa']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Esta notificación será enviada por el sistema de forma automática, puede dejar la fecha de hoy').'</strong></em>';
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                       JOIN LinkComunBundle:CertiPaginaEmpresa pe 
                                       WHERE p.estatusContenido = :estatus
                                       AND pe.activo = :activo
                                       AND pe.empresa = :empresa
                                       AND pe.pagina = p.id
                                       AND p.pagina IS NULL
                                       ORDER BY p.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId(),
                                              'activo' => true,
                                              'estatus' => $yml['parameters']['estatus_contenido']['activo']));

            $programas_asignados = $query->getResult();
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa) {
                            if($programa->getId() == $programacion->getEntidadId()){
                                $selected = 'selected="selected"';
                            }
                            $formulario .='<option value="'.$programa->getId().'" '.$selected.' >'.$programa->getNombre().'</option>';
                            $selected = '';
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>
            <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';
            
        }elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['todos']){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';

        }else{

            $aviso = '<em><strong>'.$this->get('translator')->trans('Esta notificación será enviada por el sistema de forma automática, puede dejar la fecha de hoy').'</strong></em>';

        }

        $formulario .='<div class="jsonfileds">
                            <label for="fecha_difusion" class="form-control-label">'.$this->get('translator')->trans('Seleccione fecha difusión').':</label>
                            <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                                <input type="text" class="form-control form_sty1" name="fecha_difusion" id="fecha_difusion" value="'.$programacion->getFechaDifusion()->format("d/m/Y").'" style="border-radius: 5px;">
                                <span class="fa fa-calendar"></span>
                                '.$aviso.'
                            </div>
                        </div>
                        <label id="fecha_difusion-error" class="error" for="fecha_difusion" style="display:none;"></label>';
        
        $return = array('tipo_destino' => $programacion->getTipoDestino()->getId(),
                        'formulario' =>$formulario);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function sendNowEmail($programacion_id)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $programacion = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($programacion_id);
        $hoy = date('d/m/Y');
        $template = "LinkBackendBundle:Programados:email.html.twig";
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['nivel'] or $programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa'] or $programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['todos'] or $programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo'])
        {
            if($hoy == $programacion->getFechaDifusion()->format("d/m/Y"))
            {

                $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($programacion->getNotificacion()->getId());

                if($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['nivel'])
                {

                    $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                               WHERE u.nivel = :nivel_id 
                                               AND u.empresa = :empresa_id
                                               AND u.activo = 'true'
                                               ORDER BY u.id ASC")
                                ->setParameters(array('nivel_id' => $programacion->getEntidadId(),
                                                      'empresa_id' => $notificacion->getEmpresa()->getId()));
                    $usuarios = $query->getResult();

                }
                elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa'])
                {

                    $query = $em->createQuery("SELECT u FROM 
                                                            LinkComunBundle:AdminUsuario u,
                                                            LinkComunBundle:CertiNivelPagina n,
                                                            LinkComunBundle:CertiPaginaEmpresa pe
                                               WHERE pe.activo = 'true'
                                               AND pe.empresa = :empresa
                                               AND pe.pagina = :programa
                                               AND n.paginaEmpresa = pe.id
                                               AND n.nivel = u.nivel
                                               AND u.activo = 'true'
                                               ORDER BY u.id ASC")
                                ->setParameters(array('programa' => $programacion->getEntidadId(),
                                                      'empresa' => $notificacion->getEmpresa()->getId()));;

                    $usuarios = $query->getResult();

                }
                elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['todos'])
                {

                    $usuarios = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());

                }
                elseif($programacion->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo'])
                {
                    $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                               JOIN LinkComunBundle:AdminNotificacionProgramada p 
                                               WHERE p.grupo = :grupo
                                               AND p.entidadId = u.id
                                               ORDER BY u.id ASC")
                                ->setParameters(array('grupo' => $programacion->getId()));

                    $usuarios = $query->getResult();

                }

                // llamando a la funcion que recorre lo usuarios y envia el mail
                $f->emailUsuarios($usuarios, $notificacion, $template);


                $programacion->setEnviado(true);
                
                $em->persist($programacion);
                $em->flush();

                // busco si hay notificaciones hijas de la programación para cambiar a enviado
                $grupo_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($programacion->getId());
                foreach ($grupo_programada as $individual) {
                    $individual->setEnviado(true);
                    $em->persist($individual);
                    $em->flush();
                }

            }
        }
    }*/

}
