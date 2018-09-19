<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Link\ComunBundle\Entity\CertiPaginaLog;

class ProgramaController extends Controller
{

    public function programaAction($programa_id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $pagina_obj = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $usuario_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $status_pag_obj = $this->getDoctrine()->getRepository('LinkComunBundle:CertiEstatusPagina')->find($yml['parameters']['estatus_pagina']['iniciada']);

        //Validar si el usuario tiene registro en certipaginalog para este progaram y de lo contarrio crearlo
        $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                             'pagina' => $programa_id));

        if(!$pagina_log){

            $pagina_log = new CertiPaginaLog();
            $pagina_log->setPagina($pagina_obj);
            $pagina_log->setUsuario($usuario_obj);
            $pagina_log->setFechaInicio(new \DateTime('now'));
            $pagina_log->setEstatusPagina($status_pag_obj);
            $pagina_log->setPorcentajeAvance(0);
            $em->persist($pagina_log);
            $em->flush();
        }

        $porcentaje_avance = round($pagina_log->getPorcentajeAvance());

        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);

        $pagina_sesion = $session->get('paginas')[$programa_id];
        /*echo $programa_id;
        var_dump($pagina_sesion);*/
        $lis_mods = '';
        if (count($pagina_sesion['subpaginas'])) {

            $modulos = 1;
            $contador = 0;
            foreach ($pagina_sesion['subpaginas'] as $subpagina){
                $contador = $contador + 1;
                $lis_mods .= '<div class="card-hrz card-mod">';
                $lis_mods .= '<div class="card-mod-num  mr-xl-3 d-flex justify-content-center align-items-center px-3 py-3 px-md-6 py-md-6">';
                $lis_mods .= '<h1>'.$contador.'</h1>';
                $lis_mods .= '</div>';
                $lis_mods .= '<div class="wraper d-flex flex-wrap flex-row justify-content-center">';
                $lis_mods .= ' <div class="card-hrz-body ">';
                $lis_mods .= '<h4 class="title-grey my-3 font-weight-normal ">'.$subpagina['nombre'].'</h4>';
                $lis_mods .= ' <div class="card-mod-less text-sm color-light-grey">';

                $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                    'pagina' => $subpagina['id']));

                $next_pagina = 0;
                $evaluacion_pagina = 0;
                $evaluacion_programa = 0;

                if($datos_log && $datos_log->getPorcentajeAvance()){
                    $boton = 'Continuar';
                    $clase = 'btn-continuar';
                    $porcentaje = round($datos_log->getPorcentajeAvance());
                }else{
                    $boton = 'Iniciar';
                    $clase = 'btn-primary';
                    $porcentaje = 0;
                    $next_pagina = $subpagina['id'];
                }

                if($subpagina['prelacion']){
                    $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl 
                                               WHERE pl.pagina = :pagina_id 
                                               AND pl.usuario = :usuario_id 
                                               AND pl.estatusPagina = :completada')
                                ->setParameters(array('pagina_id' => $subpagina['prelacion'],
                                                      'usuario_id' => $session->get('usuario')['id'],
                                                      'completada' => $yml['parameters']['estatus_pagina']['completada']));
                    $leccion_completada = $query->getSingleScalarResult();

                    if($leccion_completada){
                        $avanzar = 1;
                    }else{
                        $avanzar = 0;
                    }
                }else{
                    $avanzar = 1;
                }

                // validando si la subpagina esta en evaluacion
                if($datos_log && $datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion']){
                    $avanzar = 2;
                    $evaluacion_pagina = $subpagina['id'];
                    $evaluacion_programa = $programa_id;
                }
                
                if (count($subpagina['subpaginas']))
                {
                    $lis_mods .= '<ol>';
                    foreach ($subpagina['subpaginas'] as $sub_subpagina)
                    {

                        if ($sub_subpagina['acceso'])
                        {

                            $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl 
                                                       WHERE pl.pagina = :pagina_id 
                                                       AND pl.usuario = :usuario_id 
                                                       AND pl.estatusPagina = :completada')
                                        ->setParameters(array('pagina_id' => $sub_subpagina['id'],
                                                              'usuario_id' => $session->get('usuario')['id'],
                                                              'completada' => $yml['parameters']['estatus_pagina']['completada']));
                            $leccion_completada = $query->getSingleScalarResult();

                            $datos_log_sub = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                                    'pagina' => $sub_subpagina['id'])) ;
                            //Obteniendo el status de la subpagina
                            $statusPaginaId = ($datos_log_sub) ? $datos_log_sub->getEstatusPagina()->getId():0;

                            if(!$leccion_completada){
                                if($next_pagina == 0){
                                    $next_pagina = $sub_subpagina['id'];
                                }
                            }

                            //validando si la leccion se vio inicio 
                            if ($statusPaginaId!=0) {
                                
                                $enlace = $this->generateUrl('_lecciones', array('programa_id' => $programa_id)).'/'.$sub_subpagina['id'];
                                // seleccionando el icono que debe mostrarse
                                if ($statusPaginaId==$yml['parameters']['estatus_pagina']['completada']) {
                                    $icono=['nombre'=>'visibility','tooltit'=>'Volver al contenido'];
                                }
                                else{
                                     $icono=['nombre'=>'visibility','tooltit'=>'Volver al contenido'];
                                }

                                
                                $titulo_leccion = '<a href="'.$enlace.'" class="color-light-grey" >
                                                     <li class="my-1" >
                                                        <span class="d-flex">'.$sub_subpagina['nombre'].'</span>
                                                         <i class="material-icons d-flex icVc " title="'.$icono['tooltit'].'" data-toggle="tooltip" data-placement="bottom">'.$icono['nombre'].'</i>
                                                     </li>
                                                  <a/>';
                            }
                            else{

                                $titulo_leccion =  '<li class="mygit-1 color-grey" >'.$sub_subpagina['nombre'].'  </li>';
                            }

                            // validando si la sub_subpagina esta en evaluacion
                            if($statusPaginaId == $yml['parameters']['estatus_pagina']['en_evaluacion']){
                                $avanzar = 2;
                                $evaluacion_pagina = $sub_subpagina['id'];
                                $evaluacion_programa = $programa_id;
                            }
                           
                            $lis_mods .= $titulo_leccion;

                           
                            
                            
                        }
                    }
                    $lis_mods .= '</ol>';
                    $lis_mods .= '</div>';
                    
                }else{
                    
                    $lis_mods .= '</div>';
                }
                // buscando registros de la pagina principal para validar si esta en evaluación
                $datos_log_pag = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                        'pagina' => $programa_id));
                if($datos_log_pag && $datos_log_pag->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion']){
                    $avanzar = 2;
                    $evaluacion_pagina = $programa_id;
                    $evaluacion_programa = $programa_id;
                }
                // depurando en el log symfony
                //$logger = $this->get('logger');
                //$logger->error('AVANZAR = '.$avanzar);

                $lis_mods .= '<div class="progress mt-4 mb-3">';
                $lis_mods .= '<div class="progress-bar" role="progressbar" style="width: '.$porcentaje.'%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>';
                $lis_mods .= '</div>';
                $lis_mods .= '</div>';
                if($datos_log && $datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada']){

                    $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                     'pagina' => $programa_id));

                    if($datos_certi_pagina->getAcceso()){
                        // aprobado y con acceso de seguir viendo
                        $boton_continuar = '<a href="'. $this->generateUrl('_lecciones', array('programa_id' => $programa_id, 'subpagina_id' => 0)).'" class="btn btn-sm btn-primary mt-3 btnAp px-4"> Ver </a>';
                        $div_class1 = 'card-hrz-right d-flex flex-column justify-content-top mx-3 pb-1';
                        $div_class2 = 'percent text-center mt-3';
                        $span_class = 'count mt-0 text-xs color-light-grey';
                    }else{
                        $boton_continuar = '';
                        $div_class1 = 'card-hrz-right d-flex flex-column justify-content-top mx-4 pb-1';
                        $div_class2 = 'percent text-center mt-5';
                        $span_class = 'count mt-0 mb-2 text-xs color-light-grey';                        
                    }
                    $lis_mods .= '<div class="'.$div_class1.'">';
                    $lis_mods .= '<div class="'.$div_class2.'">';
                    $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0"> '.$porcentaje.' </h2>';
                    $lis_mods .= '<span class="'.$span_class.'">Calificación</span>';
                    $lis_mods .= '</div>';
                    $lis_mods .= '<div class="badge-wrap-mod mt-3 d-flex flex-column align-items-center">';
                    $lis_mods .= '<i class="material-icons badge-aprobado text-center">check_circle</i>';
                    $lis_mods .= '<span class="text-badge"> Aprobado </span>';
                    $lis_mods .= '</div>';
                    $lis_mods .= $boton_continuar;
                    $lis_mods .= '</div>';
                    
                }else{

                    $lis_mods .= '<div class="card-hrz-right d-flex flex-column  justify-content-end mx-4 pb-1">';
                    $lis_mods .= '<div class="percent text-center mt-3">';
                    $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0"> '.$porcentaje.'% </h2>';
                    $lis_mods .= '</div>';
                    if($avanzar == 2){
                        $lis_mods .= '<a href="'. $this->generateUrl('_test', array('pagina_id' => $evaluacion_pagina, 'programa_id' => $evaluacion_programa)).'" class="btn btn-sm '.$clase.' mt-6 mb-4"> '.$boton.' </a>';
                    }elseif($avanzar == 1){
                        $lis_mods .= '<a href="'. $this->generateUrl('_lecciones', array('programa_id' => $programa_id, 'subpagina_id' => $next_pagina)).'" class="btn btn-sm '.$clase.' mt-6 mb-4"> '.$boton.' </a>';
                    }else{
                        $lis_mods .= '<a href="#" class="btn btn-sm disabled '.$clase.' mt-6 mb-4"> '.$boton.' </a>';
                    }
                    
                    $lis_mods .= '</div>';




                }

                $lis_mods .= '</div>';
                $lis_mods .= '</div>';
            }
        }
        else{

            $modulos = 0;

        }

        return $this->render('LinkFrontendBundle:Programa:index.html.twig', array('pagina' => $pagina,
                                                                                  'modulos' =>$modulos,
                                                                                  'porcentaje_avance' =>$porcentaje_avance,
                                                                                  'lis_mods' =>$lis_mods));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response; 

    }

    public function misProgramasAction($activo, Request $request )
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);

        // buscando las últimas 3 interacciones del usuario donde la página no esté completada
        $query = $em->createQuery('SELECT pl FROM LinkComunBundle:CertiPaginaLog pl
                                    JOIN pl.pagina p  
                                    WHERE pl.usuario = :usuario_id
                                        AND pl.estatusPagina != :completada
                                        AND p.pagina IS NULL
                                    ORDER BY pl.id DESC')
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                          'completada' => $yml['parameters']['estatus_pagina']['completada']))
                    ->setMaxResults(3);
        $actividadreciente_padre = $query->getResult();

        $actividad_reciente = array();
        
        // Si tiene actividades
        if (count($actividadreciente_padre))
        {

            $reciente = 1;

            foreach ($actividadreciente_padre as $arp) 
            {

                $ar = array();
                $pagina_sesion = $session->get('paginas')[$arp->getPagina()->getId()];
                $subpaginas_ids = $f->hijas($pagina_sesion['subpaginas']);
                //return new Response(var_dump($subpaginas_ids));
                $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                             'pagina' => $arp->getPagina()->getId()));

                $padre_id = $arp->getPagina()->getId();
                $imagen = $arp->getPagina()->getFoto();
                $porcentaje = round($arp->getPorcentajeAvance());
                $link_enabled = $pagina_empresa->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d') ? 0 : 1;
                $dias_vencimiento = $link_enabled ? $this->get('translator')->trans('Finaliza en').' '.$f->timeAgo($pagina_empresa->getFechaVencimiento()->format("Y/m/d")).' '.$this->get('translator')->trans('días') : $this->get('translator')->trans('Vencido');

                if (count($subpaginas_ids))
                {

                    $query = $em->createQuery('SELECT pl FROM LinkComunBundle:CertiPaginaLog pl 
                                                WHERE pl.usuario = :usuario_id
                                                    AND pl.estatusPagina != :completada
                                                    AND pl.pagina IN (:hijas)
                                                ORDER BY pl.id DESC')
                                ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                      'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                                      'hijas' => $subpaginas_ids))
                                ->setMaxResults(1);
                    $ar = $query->getResult();
                }

                if ($ar)
                {

                    $id =  $ar[0]->getPagina()->getId();
                    $titulo_padre = $arp->getPagina()->getNombre();
                    $titulo_hijo = $ar[0]->getPagina()->getNombre();
                    $categoria = $ar[0]->getPagina()->getCategoria()->getNombre();
                    
                    // buscando registros de la pagina para validar si está en evaluación
                    $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                         'pagina' => $id));
                    if ($pagina_log && $pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                    {
                        $avanzar = 2;
                        $evaluacion_pagina = $id;
                        $evaluacion_programa = $padre_id;
                    }
                    else {
                        $avanzar = 0;
                        $evaluacion_pagina = 0;
                        $evaluacion_programa = 0;
                    }

                }
                else {

                    $id = 0;
                    $titulo_padre = $arp->getPagina()->getNombre();
                    $titulo_hijo = '';
                    $categoria = $arp->getPagina()->getCategoria()->getNombre();
                    
                    // buscando registros de la pagina para validar si esta en evaluación
                    $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                        'pagina' => $padre_id));
                    if ($pagina_log && $pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                    {
                        $avanzar = 2;
                        $evaluacion_pagina = $padre_id;
                        $evaluacion_programa = $padre_id;
                    }
                    else {
                        $avanzar = 0;
                        $evaluacion_pagina = 0;
                        $evaluacion_programa = 0;
                    }

                }

                $porcentaje_finalizacion = $f->timeAgoPorcentaje($pagina_empresa->getFechaInicio()->format("Y/m/d"), $pagina_empresa->getFechaVencimiento()->format("Y/m/d"));
                if ($link_enabled)
                {
                    if ($porcentaje_finalizacion >= 70)
                    {
                       $class_finaliza = 'alertTimeGood';
                    }
                    elseif ($porcentaje_finalizacion >= 31 && $porcentaje_finalizacion <= 69)
                    {
                        $class_finaliza = 'alertTimeWarning';
                    }
                    elseif ($porcentaje_finalizacion <= 30) 
                    {
                        $class_finaliza = 'alertTimeDanger';
                    }
                    else {
                        $class_finaliza = '';
                    }
                }
                else {
                    $class_finaliza = '';
                }

                $actividad_reciente[] = array('id' => $id,
                                              'padre_id' => $padre_id,
                                              'titulo_padre' => $titulo_padre,
                                              'titulo_hijo' => $titulo_hijo,
                                              'imagen' => $imagen,
                                              'categoria' => $categoria,
                                              'dias_vencimiento' => $dias_vencimiento,
                                              'class_finaliza' => $class_finaliza,
                                              'porcentaje' => $porcentaje,
                                              'avanzar' => $avanzar,
                                              'evaluacion_pagina' => $evaluacion_pagina,
                                              'evaluacion_programa' => $evaluacion_programa,
                                              'link_enabled' => $link_enabled);

            }
        
        }
        else {
            $reciente = 0;
        }

        $paginas = array();
        
        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach ($session->get('paginas') as $pg) {
            $paginas_ids[] = $pg['id'];
        }

        // Buscamos los programas finalizados y disponibles en la sesion
        $query = $em->createQuery('SELECT pl FROM LinkComunBundle:CertiPaginaLog pl 
                                    JOIN pl.pagina p 
                                    WHERE pl.pagina IN (:paginas)
                                        AND pl.usuario = :usuario
                                        AND pl.estatusPagina = :completada
                                    ORDER BY p.orden ASC')
                    ->setParameters(array('usuario' => $session->get('usuario')['id'],
                                          'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                          'paginas' => $paginas_ids));
        $pls = $query->getResult();

        if (count($pls) >=  1)
        {

            $completado = 1;

            foreach ($pls as $pl) 
            {

                $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                             'pagina' => $pl->getPagina()->getId()));

                $link_enabled = $pagina_empresa->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d') ? 0 : 1;

                if ($pagina_empresa->getAcceso() && $link_enabled)
                {
                    // aprobado y con acceso de seguir viendo
                    $continuar = 2;
                }
                else {
                    // aprobado y sin poder ver solo descargar notas y certificados
                    $continuar = 3;
                }

                $dias_vencimiento = $link_enabled ? $this->get('translator')->trans('Finaliza en').' '.$f->timeAgo($pagina_empresa->getFechaVencimiento()->format("Y/m/d")).' '.$this->get('translator')->trans('días') : $this->get('translator')->trans('Vencido');

                $paginas[] = array('id' => $pl->getPagina()->getId(),
                                   'nombre' => $pl->getPagina()->getNombre(),
                                   'imagen' => $pl->getPagina()->getFoto(),
                                   'descripcion' => $pl->getPagina()->getDescripcion(),
                                   'dias_vencimiento' => $dias_vencimiento,
                                   'continuar' => $continuar,
                                   'link_enabled' => $link_enabled);
                
            }
            
        }
        else {
            $completado = 0;
        }

        return $this->render('LinkFrontendBundle:Programa:misProgramas.html.twig', array('reciente' => $reciente,
                                                                                         'actividad_reciente' => $actividad_reciente,
                                                                                         'completado' => $completado,
                                                                                         'paginas' => $paginas,
                                                                                         'activo' => $activo));

    }
    
    public function programasAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach ($session->get('paginas') as $pg) 
        {
            $paginas_ids[] = $pg['id'];
        }

        // Buscamos los grupos disponibles para el usuario por los programas disponibles en la sesión
        $query = $em->createQuery('SELECT gp FROM LinkComunBundle:CertiGrupoPagina gp 
                                    JOIN gp.grupo g 
                                    WHERE g.empresa = :empresa
                                        AND gp.pagina IN (:paginas)
                                    ORDER BY g.orden ASC')
                    ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                          'paginas' => $paginas_ids));
        $gps = $query->getResult();

        $grupos_id = array();
        foreach ($gps as $gp)
        {
            if (!in_array($gp->getGrupo()->getId(), $grupos_id))
            {
                $grupos_id[] = $gp->getGrupo()->getId();
            }
        }

        $grupos = array();
        foreach ($grupos_id as $grupo_id)
        {

            $grupos_bd = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupoPagina')->findByGrupo($grupo_id);

            $paginas = array();

            foreach ($grupos_bd as $grupo)
            {

                if (in_array($grupo->getPagina()->getId(), $paginas_ids))
                {

                    $nombre_grupo = $grupo->getGrupo()->getNombre();

                    // Programas pertenecientes al grupo
                    $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $grupo->getPagina()->getId()));

                    $pagina_sesion = $session->get('paginas')[$grupo->getPagina()->getId()];

                    if (count($pagina_sesion['subpaginas']) >= 1)
                    {
                        $tiene_subpaginas = 1;
                    }
                    else {
                        $tiene_subpaginas = 0;
                    }

                    $link_enabled = $pagina_empresa->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d') ? 0 : 1;

                    $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                         'pagina' => $grupo->getPagina()->getId()));
                    if ($pagina_log)
                    {
                        if ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'])
                        {
                            if ($pagina_empresa->getAcceso() && $link_enabled)
                            {
                                // aprobado y con acceso de seguir viendo
                                $continuar = 2;
                            }
                            else {
                                // aprobado y sin poder ver solo descargar notas y certificados
                                $continuar = 3;
                            }
                        }
                        else {
                           // cursando actualmente el programa - 1 = continuar, 4 = vencido y sin haber finalizado
                           $continuar = $link_enabled ? 1 : 4;
                        }
                    }
                    else {
                        // sin registros - 0 = iniciar, 4 = vencido y sin haber iniciado
                        $continuar = $link_enabled ? 0 : 4;
                    }

                    $porcentaje_finalizacion = $f->timeAgoPorcentaje($pagina_empresa->getFechaInicio()->format("Y/m/d"), $pagina_empresa->getFechaVencimiento()->format("Y/m/d"));
                    if ($link_enabled)
                    {
                        if ($porcentaje_finalizacion >= 70)
                        {
                           $class_finaliza = 'alertTimeGood';
                        }
                        elseif ($porcentaje_finalizacion >= 31 && $porcentaje_finalizacion <= 69)
                        {
                            $class_finaliza = 'alertTimeWarning';
                        }
                        elseif ($porcentaje_finalizacion <= 30) 
                        {
                            $class_finaliza = 'alertTimeDanger';
                        }
                        else {
                            $class_finaliza = '';
                        }
                    }
                    else {
                        $class_finaliza = '';
                    }
                    
                    $dias_vencimiento = $link_enabled ? $this->get('translator')->trans('Finaliza en').' '.$f->timeAgo($pagina_empresa->getFechaVencimiento()->format("Y/m/d")).' '.$this->get('translator')->trans('días') : $this->get('translator')->trans('Vencido');

                    $paginas[] = array('id' => $grupo->getPagina()->getId(),
                                       'nombre' => $grupo->getPagina()->getNombre(),
                                       'imagen' => $grupo->getPagina()->getFoto(),
                                       'descripcion' => $grupo->getPagina()->getDescripcion(),
                                       'dias_vencimiento' => $dias_vencimiento,
                                       'class_finaliza' => $class_finaliza,
                                       'tiene_subpaginas' => $tiene_subpaginas,
                                       'continuar' => $continuar,
                                       'link_enabled' => $link_enabled);

                }

            }

            $grupos[] = array('id' => $grupo_id,
                              'nombre' => $nombre_grupo,
                              'paginas' => $paginas);

        }

        return $this->render('LinkFrontendBundle:Programa:programas.html.twig', array('grupos' => $grupos));

    }

}