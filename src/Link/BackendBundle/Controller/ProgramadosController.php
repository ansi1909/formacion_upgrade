<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNotificacion;
use Link\ComunBundle\Entity\AdminNotificacionProgramada;
use Link\ComunBundle\Entity\AdminTipoDestino;
use Link\ComunBundle\Entity\AdminNivel;
use Link\ComunBundle\Entity\AdminEmpresa;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\AdminUsuario;

class ProgramadosController extends Controller
{

   public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $session->set('app_id', $app_id);
        $notificacionesdb = array();
        if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
        {
            return $this->redirectToRoute('_authException');
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
                                                                                    'usuario' => $usuario));
    }

    public function ajaxNotificacionAction(Request $request)
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
                                    $notificaciones .= '<th>'.$this->get('translator')->trans('Tipo Notificación').'</th>
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
                                        <a href="#" title="'.$this->get('translator')->trans('Nuevo Registro').'" class="btn btn-link btn-sm add" data-toggle="modal" data-target="#formModal" data="'.$notificacion->getId().'"><span class="fa fa-plus"></span></a>
                                        <a href="#" title="'.$this->get('translator')->trans('Ver Historial').'" class="btn btn-link btn-sm see" data="'.$notificacion->getId().'"><span class="fa fa-eye"></span></a>
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
        
        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                            WHERE p.notificacion = :notificacion_id AND p.grupo IS NULL
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
            if($notificacion_programada->getTipoDestino()->getNombre() == 'Programa'){

                $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidad = $programa->getNombre();

            }elseif($notificacion_programada->getTipoDestino()->getNombre() == 'Nivel'){
                $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($notificacion_programada->getEntidadId());
                $entidad = $nivel->getNombre();

            }elseif($notificacion_programada->getTipoDestino()->getNombre() == 'Grupo de participantes'){
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
            }else{
                $entidad = 'N/A';
            }
            /*if(strtotime($fecha_actual) >= strtotime($notificacion_programada->getFechaDifusion()->format("d/m/Y"))){
                $deshabilitado = 'disabled';
            }*/
            $html .= '<tr>
                        <td>'.$notificacion_programada->getTipoDestino()->getNombre().'</td>
                        <td>'.$entidad.'</td>
                        <td>'.$notificacion_programada->getFechaDifusion()->format("d/m/Y").'</td>
                        <td class="center">
                            <input type="hidden" id="hidden_notificacion_id" name="hidden_notificacion_id" value="'.$notificacion->getId().'">
                            <input type="hidden" id="asuntoTableEdit'.$notificacion->getId().'" value="'.$notificacion->getAsunto().'">
                            <a href="#" class="btn btn-link btn-sm edit edit_programacion" data-toggle="modal" data-target="#formModal" data="'.$notificacion_programada->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" class="btn btn-link btn-sm '.$deshabilitado.' '.$delete.' '.$delete_disabled.'" data="'.$notificacion_programada->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
            $entidad = '';
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

        if($tipo_destino->getNombre() == "Nivel"){

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
            
        }elseif($tipo_destino->getNombre() == "Programa"){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $programas_asignados = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findByEmpresa($notificacion->getEmpresa()->getId());
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa_asignado) {
                            if($programa_asignado->getActivo() == true){
                                $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_asignado->getEmpresa()->getId());
                                $formulario .='<option value="'.$programa->getId().'" >'.$programa->getNombre().'</option>';
                            }
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>
            <label id="entidad_id-error" class="error" for="entidad_id" style="display:none;"></label>';

            
        }elseif($tipo_destino->getNombre() == "Grupo de participantes"){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $usuarios_grupo = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());
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
            
        }elseif($tipo_destino->getNombre() == "Todos"){

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

        if($tipo_destino->getNombre() == "Grupo de participantes"){

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

        if($programacion->getTipoDestino()->getNombre() == "Nivel"){


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
                </div>';
            
        }elseif($programacion->getTipoDestino()->getNombre() == "Programa"){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $programas_asignados = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findByEmpresa($notificacion->getEmpresa()->getId());
            $formulario .='<div class="jsonfileds">
                <label for="entidad_id" class="form-control-label">'.$this->get('translator')->trans('Seleccione el programa').':</label>
                <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                    <select class="form-control form_sty_sel form_sty_modal" id="entidad_id" name="entidad_id" style="border-radius: 5px;">
                        <option value=""></option>';
                        foreach ($programas_asignados as $programa_asignado) {
                            if($programa_asignado->getActivo() == true){
                                $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_asignado->getEmpresa()->getId());
                                if($programa->getId() == $programacion->getEntidadId()){
                                    $selected = 'selected="selected"';
                                }
                                $formulario .='<option value="'.$programa->getId().'" '.$selected.' >'.$programa->getNombre().'</option>';
                                $selected = '';
                            }
                        }
             $formulario .= '</select>
                    <span class="fa fa-book"></span>
                    <span class="bttn_d"><img src="/formacion2.0/web/img/down-arrowwht.png" alt=""></span>
                </div>
            </div>';
            
        }elseif($programacion->getTipoDestino()->getNombre() == "Grupo de participantes"){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';
            $usuarios_grupo = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());
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
                            $formulario .='<option value="'.count($programacion_grupo).'-'.$usuario_grupo->getId().'" '.$selected.' >'.$usuario_grupo->getNombre().' '.$usuario_grupo->getApellido().' ('.$usuario_grupo->getLogin().')</option>';
                            $selected = '';
                        }
             $formulario .= '</select>
                            </div>
                        </div>';
            
        }elseif($programacion->getTipoDestino()->getNombre() == "Todos"){

            $aviso = '<em><strong>'.$this->get('translator')->trans('Para enviar la notificación inmediatamente seleccione la fecha de hoy').'</strong></em>';

        }

        $formulario .='<div class="jsonfileds">
                            <label for="fecha_difusion" class="form-control-label">'.$this->get('translator')->trans('Seleccione fecha difusión').':</label>
                            <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16" style="margin-top: 2rem; margin-bottom: 2rem">
                                <input type="text" class="form-control form_sty1" name="fecha_difusion" id="fecha_difusion" value="'.$programacion->getFechaDifusion()->format("d/m/Y").'" style="border-radius: 5px;">
                                <span class="fa fa-calendar"></span>
                                '.$aviso.'
                            </div>
                        </div>';
        
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
        $controller = 'ProgramadosController';

        if($programacion->getTipoDestino()->getNombre() == "Nivel" or $programacion->getTipoDestino()->getNombre() == "Programa" or $programacion->getTipoDestino()->getNombre() == "Todos" or $programacion->getTipoDestino()->getNombre() == "Grupo de usuarios")
        {
            if($hoy == $programacion->getFechaDifusion()->format("d/m/Y"))
            {

                $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($programacion->getNotificacion()->getId());
                $parametros = array();
                $template = "LinkBackendBundle:Programados:email.html.twig";

                if($programacion->getTipoDestino()->getNombre() == "Nivel")
                {

                    $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminUsuario p
                                                WHERE p.nivel = :nivel_id AND p.empresa = :empresa_id
                                                ORDER BY p.id ASC")
                                ->setParameters(array('nivel_id' => $programacion->getEntidadId(),
                                                      'empresa_id' => $notificacion->getEmpresa()->getId()));
                    $usuarios = $query->getResult();

                }
                elseif($programacion->getTipoDestino()->getNombre() == "Programa")
                {

                    $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                                JOIN LinkComunBundle:CertiNivelPagina c 
                                                WHERE c.paginaEmpresa = :programa
                                                AND c.nivel = u.nivel
                                                ORDER BY u.id ASC")
                                ->setParameters(array('programa' => $programacion->getEntidadId()));

                    $usuarios = $query->getResult();

                }
                elseif($programacion->getTipoDestino()->getNombre() == "Todos")
                {

                    $usuarios = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());

                }
                elseif($programacion->getTipoDestino()->getNombre() == "Grupo de usuarios")
                {
                    $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                                JOIN LinkComunBundle:AdminNotificacionProgramada p 
                                                WHERE p.grupo = :grupo
                                                AND p.entidad = u.id
                                                ORDER BY u.id ASC")
                                ->setParameters(array('grupo' => $programacion->getId()));

                    $usuarios = $query->getResult();

                }

                foreach ($usuarios as $usuario) {

                    $parametros= array('twig'=>$template,
                                       'asunto'=>$notificacion->getAsunto(),
                                       'remitente'=>array('info@formacion2-0.com' => 'Formación 2.0'),
                                       'destinatario'=>$usuario->getCorreoCorporativo(),
                                       'datos'=>array('mensaje' => $notificacion->getMensaje(), 'usuario' => $usuario ));

                    $f->sendEmail($parametros, $controller);
                }

            }
        }
    }

    public function sendAction()

    {

        $this_is = 'this is';
        $the_message = ' the message of the email';
        $mailer = $this->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject('The Subject for this Message')
            ->setFrom('tutorvirtual@formacion2puntocero.com')
            ->setTo('ponceelrelajado@gmail.com')
            ->setBody($this->renderView('LinkBackendBundle:Programados:emailTest.html.twig', ['this'=>$this_is, 'message'=>$the_message]));
        $mailer->send($message);
        return new Response('<html><body>The email has been sent successfully!</body></html>');

    }

}
