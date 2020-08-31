<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\AdminIntroduccion;
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

        if(!$pagina_log)
        {
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

        $lis_mods = '';
        $tiene_evaluacion = 0;

        if (count($pagina_sesion['subpaginas']))
        {

            $modulos = 1;
            $contador = 0;

            foreach ($pagina_sesion['subpaginas'] as $subpagina)
            {
                $nota = 0;
                $contador = $contador + 1;

                if ($subpagina['tiene_evaluacion'])
                {
                    $tiene_evaluacion = 1;
                    $prueba = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $subpagina['id'])) ;
                    $pruebaLog = $prueba? $this->getDoctrine()->getRepository('LinkComunBundle:CertiPruebaLog')->findOneBy(array('prueba' => $prueba->getId(),'usuario'=>$session->get('usuario')['id'],'estado'=>$yml['parameters']['estado_prueba']['aprobado'])):false ;
                }
                $lis_mods .= '<div class="card-hrz card-mod d-flex flex-column flex-md-row">';
                $lis_mods .= '<div class="card-mod-num  mr-xl-3 d-flex justify-content-center align-items-center px-3 py-3 px-md-6 py-md-6">';
                $lis_mods .= '<h1>'.$contador.'</h1>';
                $lis_mods .= '</div>';
                $lis_mods .= '<div class="wraper d-flex flex-column flex-md-row justify-content-center">';
                $lis_mods .= ' <div class="card-hrz-body ">';
                $lis_mods .= '<h4 class="title-grey my-3 font-weight-normal ">'.$subpagina['nombre'].'</h4>';
                $lis_mods .= ' <div class="card-mod-less text-md color-light-grey">';

                $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                    'pagina' => $subpagina['id']));

                $next_pagina = 0;
                $evaluacion_pagina = 0;
                $evaluacion_programa = 0;

                if ($datos_log && $datos_log->getPorcentajeAvance())
                {
                    $boton = $this->get('translator')->trans('Continuar');
                    $clase = 'btn-continuar';
                    $porcentaje = round($datos_log->getPorcentajeAvance());
                }
                else {
                    $boton = $this->get('translator')->trans('Iniciar');
                    $clase = 'btn-primary';
                    $porcentaje = 0;
                    $next_pagina = $subpagina['id'];
                }

                if ($subpagina['prelacion'])
                {
                    $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                               WHERE pl.pagina = :pagina_id
                                               AND pl.usuario = :usuario_id
                                               AND pl.estatusPagina = :completada')
                                ->setParameters(array('pagina_id' => $subpagina['prelacion'],
                                                      'usuario_id' => $session->get('usuario')['id'],
                                                      'completada' => $yml['parameters']['estatus_pagina']['completada']));
                    $leccion_completada = $query->getSingleScalarResult();

                    if ($leccion_completada)
                    {
                        $avanzar = 1;
                    }
                    else {
                        $avanzar = 0;
                    }
                }else{
                    $avanzar = 1;
                }

                // validando si la subpagina esta en evaluacion
                if ($datos_log && $datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                {
                    $avanzar = 2;
                    $evaluacion_pagina = $subpagina['id'];
                    $evaluacion_programa = $programa_id;
                }

                if (count($subpagina['subpaginas']))
                {

                    $lis_mods .= '<ol>';

                    foreach ($subpagina['subpaginas'] as $sub_subpagina)
                    {

                        if ($sub_subpagina['tiene_evaluacion'])
                        {
                            $tiene_evaluacion = 1;
                        }

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
                            $statusPaginaId = ($datos_log_sub) ? $datos_log_sub->getEstatusPagina()->getId() : 0;

                            if(!$leccion_completada)
                            {
                                if($next_pagina == 0)
                                {
                                    $next_pagina = $sub_subpagina['id'];
                                }
                            }

                            //validando si la leccion se vio inicio
                            if ($statusPaginaId != 0)
                            {

                                $enlace = $this->generateUrl('_lecciones', array('programa_id' => $programa_id)).'/'.$sub_subpagina['id'];
                                // seleccionando el icono que debe mostrarse
                                if ($statusPaginaId == $yml['parameters']['estatus_pagina']['completada']) {
                                    $icono = ['nombre' => 'visibility', 'tooltit' => $this->get('translator')->trans('Volver al contenido')];
                                }
                                else{
                                     $icono = ['nombre' => 'visibility', 'tooltit' => $this->get('translator')->trans('Volver al contenido')];
                                }


                                $titulo_leccion = '<a href="'.$enlace.'" class="color-light-grey" >
                                                     <li class="my-1" >
                                                        <span class="d-flex list-text">'.$sub_subpagina['nombre'].'</span>
                                                         <i class="material-icons d-flex icVc " title="'.$icono['tooltit'].'" data-toggle="tooltip" data-placement="bottom">'.$icono['nombre'].'</i>
                                                     </li>
                                                  <a/>';

                            }
                            else {
                                $titulo_leccion =  '<li class="mygit-1 color-grey" >'.$sub_subpagina['nombre'].'  </li>';
                            }

                            // validando si la sub_subpagina esta en evaluacion
                            if ($statusPaginaId == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                            {
                                $avanzar = 2;
                                $evaluacion_pagina = $sub_subpagina['id'];
                                $evaluacion_programa = $programa_id;
                            }

                            $lis_mods .= $titulo_leccion;

                        }
                    }

                    $lis_mods .= '</ol>';
                    $lis_mods .= '</div>';

                }
                else {
                    $lis_mods .= '</div>';
                }

                // buscando registros de la pagina principal para validar si esta en evaluación
                $datos_log_pag = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                        'pagina' => $programa_id));
                if ($datos_log_pag && $datos_log_pag->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                {
                    $avanzar = 2;
                    $evaluacion_pagina = $programa_id;
                    $evaluacion_programa = $programa_id;
                }

                $lis_mods .= '<div class="progress mt-4 mb-3 mt-md-2">';
                $lis_mods .= '<div class="progress-bar" role="progressbar" style="width: '.$porcentaje.'%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>';
                $lis_mods .= '</div>';
                $lis_mods .= '</div>';
                if ($datos_log && $datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'])
                {

                    $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                     'pagina' => $programa_id));

                    if ($datos_certi_pagina->getAcceso())
                    {
                        // aprobado y con acceso de seguir viendo
                        $boton_continuar = '<a href="'. $this->generateUrl('_lecciones', array('programa_id' => $programa_id, 'subpagina_id' => 0)).'" class="btn btn-sm btn-primary mt-3 btnAp px-4"> Ver </a>';
                        $div_class1 = 'card-hrz-right d-flex flex-column justify-content-top mx-3 pb-1 align-item align-items-center';
                        $div_class2 = 'percent text-center mt-1';
                        $span_class = 'count mt-0 text-xs color-light-grey';
                    }
                    else {
                        $boton_continuar = '';
                        $div_class1 = 'card-hrz-right d-flex flex-column justify-content-top mx-4 pb-1 align-items-center';
                        $div_class2 = 'percent text-center mt-5';
                        $span_class = 'count mt-0 mb-2 text-xs color-light-grey';
                    }
                    $score = $tiene_evaluacion ? $this->get('translator')->trans('Calificación') : '';
                    $lis_mods .= '<div class="'.$div_class1.'">';
                    $lis_mods .= '<div class="'.$div_class2.'">';
                    if($score && $pruebaLog){
                        $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0"> '.round($pruebaLog->getNota()).' </h2>';
                        $lis_mods .= '<span class="'.$span_class.'">'.$score.'</span>';
                    }else{
                        $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0" style="visibility:hidden"> '.''.' </h2>';
                        $lis_mods .= '<span class="'.$span_class.'">'.''.'</span>';
                    }


                    $lis_mods .= '</div>';
                    $lis_mods .= '<div class="badge-wrap-mod mt-3 d-flex flex-column align-items-center">';
                    $lis_mods .= '<i class="material-icons badge-aprobado text-center">check_circle</i>';
                    $lis_mods .= '<span class="text-badge" style="visibility: hidden"> '.$this->get('translator')->trans('Aprobado').' </span>';
                    $lis_mods .= '</div>';
                    $lis_mods .= $boton_continuar;
                    $lis_mods .= '</div>';

                }
                else {

                    $lis_mods .= '<div class="card-hrz-right d-flex flex-column  justify-content-center align-items-center mx-4 pb-1">';
                    $lis_mods .= '<div class="percent text-center mt-1">';
                    $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0"> '.$porcentaje.'% </h2>';
                    $lis_mods .= '</div>';
                    if($avanzar == 2)
                    {
                        $lis_mods .= '<a href="'. $this->generateUrl('_test', array('pagina_id' => $evaluacion_pagina, 'programa_id' => $evaluacion_programa)).'" class="btn btn-sm '.$clase.' mt-2 mb-4"> '.$boton.' </a>';
                    }
                    elseif($avanzar == 1)
                    {
                        $lis_mods .= '<a href="'. $this->generateUrl('_lecciones', array('programa_id' => $programa_id, 'subpagina_id' => $next_pagina)).'" class="btn btn-sm '.$clase.' mt-6 mb-4"> '.$boton.' </a>';
                    }
                    else{
                        $lis_mods .= '<a href="#" class="btn btn-sm disabled '.$clase.' mt-2 mb-4"> '.$boton.' </a>';
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

        $user_id = $session->get('usuario')['id'];
        $intro_del_usuario = $em->getRepository('LinkComunBundle:AdminIntroduccion')->findByUsuario(
            array('id' => $user_id)
        );
        $paso_actual_intro = $intro_del_usuario[0]->getPasoActual();
        $cancelar_intro = $intro_del_usuario[0]->getCancelado();

        return $this->render('LinkFrontendBundle:Programa:index.html.twig', array('pagina' => $pagina,
                                                                                  'modulos' =>$modulos,
                                                                                  'porcentaje_avance' =>$porcentaje_avance,
                                                                                  'lis_mods' =>$lis_mods,
                                                                                  'paso_actual_intro' =>$paso_actual_intro,
                                                                                  'cancelar_intro' =>$cancelar_intro));

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
        $yml2 = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $timeZone = 0;

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);

        /********************** LÓGICA PARA LA ESTRUCTURA DE ACTIVIDADES RECIENTES *******************/

        $actividades_recientes = $f->getActividadesRecientes($session->get('usuario')['id'], $session->get('paginas'), $session->get('empresa')['id'], $yml);
        $actividad_reciente = $actividades_recientes['actividad_reciente'];
        $reciente = $actividades_recientes['reciente'];


        /********************** LÓGICA PARA LA ESTRUCTURA DE LOS PROGRAMAS FINALIZADOS *******************/
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

                $modulo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('pagina' => $pagina_empresa->getPagina()->getId()));
                $prueba = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $modulo->getId()));
                $notas = $f->notasDisponibles($pl->getPagina()->getId(),$session->get('usuario')['id'],$yml);

                //$link_enabled = $pagina_empresa->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d') ? 0 : 1;
                $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
                $fechaFin = $pagina_empresa->getFechaVencimiento();
                $fechaInicio = $pagina_empresa->getFechaInicio();
                if($usuario->getNivel()){
                    if ($usuario->getNivel()->getFechaInicio() && $usuario->getNivel()->getFechaFin()) {
                        $fechaInicio = $usuario->getNivel()->getFechaInicio();
                        $fechaFin = $usuario->getNivel()->getFechaFin();
                    }
                }
                if ($pagina_empresa->getEmpresa()->getZonaHoraria()){
                    $timeZone = 1;
                    $zonaHoraria = $pagina_empresa->getEmpresa()->getZonaHoraria()->getNombre();
                }

                if($timeZone){
                    date_default_timezone_set($zonaHoraria);
                }
                $fechaActual = date('d-m-Y H:i:s');
                $fechaInicio = $fechaInicio->format('d-m-Y 00:00:00');
                $fechaFin = new \DateTime($fechaFin->format('d-m-Y 23:59:00'));
                $fechaFin = $fechaFin->format('d-m-Y H:i:s');
                $link_enabled = (strtotime($fechaActual)<strtotime($fechaFin))? 1:0;

                $dias = $f->timeAgo($fechaFin);
                $porcentaje = $f->porcentaje_finalizacion($fechaInicio,$fechaFin,$dias);
                $porcentaje_finalizacion = $dias;
                $class_finaliza = $f->classFinaliza($porcentaje);



                if ($pagina_empresa->getAcceso() && $link_enabled)
                {
                    // aprobado y con acceso de seguir viendo
                    $continuar = 2;
                }
                else {
                    // aprobado y sin poder ver solo descargar notas y certificados
                    $continuar = 3;
                }

               if ($link_enabled)
                    {
                      $nivel_vigente = true;
                      if($dias == 0){
                         $dias_vencimiento = $this->get('translator')->trans('Vence hoy');
                      }else{
                         $dias_vencimiento = $this->get('translator')->trans('Finaliza en').' '.$dias.' '.$this->get('translator')->trans('días');
                      }
                    }
                    else {
                        $nivel_vigente = false;
                        $dias_vencimiento = $this->get('translator')->trans('Programa Vencido');
                    }

                $paginas[] = array('id' => $pl->getPagina()->getId(),
                                   'nombre' => $pl->getPagina()->getNombre(),
                                   'imagen' => $pl->getPagina()->getFoto(),
                                   'descripcion' => $pl->getPagina()->getDescripcion(),
                                   'dias_vencimiento' => $dias_vencimiento,
                                   'continuar' => $continuar,
                                   'link_enabled' => $link_enabled,
                                   'class_finaliza' => $class_finaliza,
                                   'prueba' => $prueba,
                                   'notas' => $notas,
                                   'pdf' => ($pl->getPagina()->getPdf())? $yml2['parameters']['folders']['uploads'].$pl->getPagina()->getPdf():null);


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
        $timeZone = 0;

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach ($session->get('paginas') as $pg)
        {
            $paginas_ids[] = $pg['id'];
        }

        // // Buscamos los grupos disponibles para la empresa
        $query = $em->createQuery('SELECT g FROM LinkComunBundle:CertiGrupo g
                                     WHERE g.empresa = :empresa
                                     ORDER BY g.orden ASC')
                     ->setParameters(array('empresa' => $session->get('empresa')['id']));
        $gps = $query->getResult();

        $grupos_ids = array();
        $grupo_consulta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->findByEmpresa($session->get('empresa')['id']);

        foreach ($gps as $grupo) {
            array_push($grupos_ids,$grupo->getId());
        }

        $grupos = array();
        foreach ($grupos_ids as $grupo_id) {
            $paginas = array();
            $agregar = false;
            foreach ($paginas_ids as $pagina_id) {
                $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupoPagina')->findOneBy(['grupo' => $grupo_id,'pagina' =>$pagina_id]);
                if($grupo){
                    $agregar = true;
                    $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $pagina_id));

                    $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
                    $fechaFin = $pagina_empresa->getFechaVencimiento();
                    $fechaInicio = $pagina_empresa->getFechaInicio();
                    if($usuario->getNivel()){
                        if ($usuario->getNivel()->getFechaInicio() && $usuario->getNivel()->getFechaFin()) {
                            $fechaInicio = $usuario->getNivel()->getFechaInicio();
                            $fechaFin = $usuario->getNivel()->getFechaFin();
                        }
                    }

                    if ($pagina_empresa->getEmpresa()->getZonaHoraria()){
                        $timeZone = 1;
                        $zonaHoraria = $pagina_empresa->getEmpresa()->getZonaHoraria()->getNombre();
                    }

                    if($timeZone){
                        date_default_timezone_set($zonaHoraria);
                    }

                    $fechaActual = date('d-m-Y H:i:s');
                    $fechaInicio = $fechaInicio->format('d-m-Y 00:00:00');
                    $fechaFin = new \DateTime($fechaFin->format('d-m-Y 23:59:00'));
                    $fechaFin = $fechaFin->format('d-m-Y H:i:s');
                    $link_enabled = (strtotime($fechaActual)<strtotime($fechaFin))? 1:0;
                    $pagina_sesion = $session->get('paginas')[$grupo->getPagina()->getId()];

                    if (count($pagina_sesion['subpaginas']) >= 1)
                    {
                        $tiene_subpaginas = 1;
                    }
                    else {
                        $tiene_subpaginas = 0;
                    }

                    $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                         'pagina' => $grupo->getPagina()->getId()));

                    $modulo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('pagina' => $grupo->getPagina()->getId()));
                    $prueba = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $modulo->getId()));
                    $notas = $f->notasDisponibles($grupo->getPagina()->getId(),$session->get('usuario')['id'],$yml);

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
                    $dias = $f->timeAgo($fechaFin);
                    $porcentaje = $f->porcentaje_finalizacion($fechaInicio,$fechaFin,$dias);
                    $porcentaje_finalizacion = $dias;
                    $class_finaliza = $f->classFinaliza($porcentaje);

                    if ($link_enabled)
                    {
                      $nivel_vigente = true;
                      if($dias == 0){
                         $dias_vencimiento = $this->get('translator')->trans('Vence hoy');
                      }else{
                         $dias_vencimiento = $this->get('translator')->trans('Finaliza en').' '.$dias.' '.$this->get('translator')->trans('días');
                      }
                    }
                    else {
                        $nivel_vigente = false;
                        $dias_vencimiento = $this->get('translator')->trans('Programa Vencido');
                    }
                    //agregar pagina al array de paginas para el grupo
                    $paginas[] = array('id' => $grupo->getPagina()->getId(),
                                       'nombre' => $grupo->getPagina()->getNombre(),
                                       'imagen' => $grupo->getPagina()->getFoto(),
                                       'descripcion' => $grupo->getPagina()->getDescripcion(),
                                       'pdf' => ($grupo->getPagina()->getPdf())?$yml2['parameters']['folders']['uploads'].$grupo->getPagina()->getPdf():null,
                                       'dias_vencimiento' => $dias_vencimiento,
                                       'class_finaliza' => $class_finaliza,
                                       'tiene_subpaginas' => $tiene_subpaginas,
                                       'continuar' => $continuar,
                                       'link_enabled' => $link_enabled,
                                       'nivel_vigente' => $nivel_vigente,
                                       'prueba' => $prueba,
                                       'notas' => $notas);

                }/*if de si existe un certigrupo*/
            } /*foreach de paginas*/
            if($agregar){
                $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);
                $grupos[] = array('id' => $grupo_id,
                                 'nombre' => $grupo->getNombre(),
                                 'paginas' => $paginas);
            }


        }/* foreach de grupos */


    return $this->render('LinkFrontendBundle:Programa:programas.html.twig', array('grupos' => $grupos));

    }

}