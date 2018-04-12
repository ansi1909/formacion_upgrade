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

        if (!$session->get('iniFront'))
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
                $lis_mods .= '<div class="card-hrz-body ">';
                $lis_mods .= '<h4 class="title-grey my-3 font-weight-normal ">'.$subpagina['nombre'].'</h4>';
                $lis_mods .= '<div class="card-mod-less text-sm color-light-grey">';
                $lis_mods .= '<ol>';

                $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                            'pagina' => $subpagina['id']));
                $next_pagina = 0;

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
                
                if (count($subpagina['subpaginas']))
                {
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

                            if(!$leccion_completada){
                                $visto = 'color-grey';
                                if($next_pagina == 0){
                                    $next_pagina = $sub_subpagina['id'];
                                }
                            }else{
                                $visto = '';
                            }

                            
                            $lis_mods .= '<li class="my-1 '.$visto.' ">'.$sub_subpagina['nombre'].'</li>';
                            
                        }
                    }
                    $lis_mods .= '</ol>';
                    $lis_mods .= '</div>';
                    

                    $lis_mods .= '<div class="progress mt-4 mb-3">';
                    $lis_mods .= '<div class="progress-bar" role="progressbar" style="width: '.$porcentaje.'%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>';
                    $lis_mods .= '</div>';
                    $lis_mods .= '</div>';
                    if($datos_log && $datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada']){

                        $lis_mods .= '<div class="card-hrz-right d-flex flex-column  justify-content-top mx-4 pb-1">';
                        $lis_mods .= '<div class="percent text-center mt-5">';
                        $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0"> '.$porcentaje.' </h2>';
                        $lis_mods .= '<span class="count mt-0 mb-2 text-xs  color-light-grey ">Calificación</span>';
                        $lis_mods .= '</div>';
                        $lis_mods .= '<div class="badge-wrap-mod mt-4 d-flex flex-column align-items-center">';
                        $lis_mods .= '<i class="material-icons badge-aprobado ">check_circle</i>';
                        $lis_mods .= '<span class="text-badge"> Aprobado </span>';
                        $lis_mods .= '</div>';
                        $lis_mods .= '</div>';
                        
                    }else{

                        $lis_mods .= '<div class="card-hrz-right d-flex flex-column  justify-content-end mx-4 pb-1">';
                        $lis_mods .= '<div class="percent text-center mt-3">';
                        $lis_mods .= '<h2 class="color-light-grey mb-0 pb-0"> '.$porcentaje.'% </h2>';
                        $lis_mods .= '</div>';
                        if($avanzar == 1){
                            $lis_mods .= '<a href="'. $this->generateUrl('_lecciones', array('programa_id' => $programa_id, 'subpagina_id' => $next_pagina)).'" class="btn btn-sm '.$clase.' mt-6 mb-4"> '.$boton.' </a>';
                        }else{
                            $lis_mods .= '<a href="#" class="btn btn-sm disabled '.$clase.' mt-6 mb-4"> '.$boton.' </a>';
                        }
                        
                        $lis_mods .= '</div>';

                    }
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

    public function misProgramasAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $datos = $session;
        $empresa_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
        $bienvenida = $empresa_obj->getBienvenida();

        // buscando las últimas 3 interacciones del usuario donde la página no este completada
        $query_actividad_padre = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar
                                                   JOIN LinkComunBundle:CertiPagina p 
                                                   WHERE ar.usuario = :usuario_id
                                                   AND ar.estatusPagina != :completada
                                                   AND p.id = ar.pagina
                                                   AND p.pagina IS NULL
                                                   ORDER BY ar.id DESC')
                                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                          'completada' => $yml['parameters']['estatus_pagina']['completada']))
                                    ->setMaxResults(3);
        $actividadreciente_padre = $query_actividad_padre->getResult();

        $actividad_reciente = array();
        // Si tiene actividades
        if(count($actividadreciente_padre) >=  1){
            $reciente = 1;
            foreach ($actividadreciente_padre as $arp) {
                $ar = array();
                $pagina_sesion = $session->get('paginas')[$arp->getPagina()->getId()];
                $subpaginas_ids = $f->hijas($pagina_sesion['subpaginas']);
                //return new Response(var_dump($subpaginas_ids));
                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $arp->getPagina()->getId()));

                if(count($subpaginas_ids)){

                    $query_actividad_hija = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                              WHERE ar.usuario = :usuario_id
                                                              AND ar.estatusPagina != :completada
                                                              AND ar.pagina IN (:hijas)
                                                              ORDER BY ar.id DESC')
                                                ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                                      'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                                                      'hijas' => $subpaginas_ids))
                                        ->setMaxResults(1);
                    $ar = $query_actividad_hija->getResult();
                }
                if($ar){
                    if($ar[0]){

                        $id =  $ar[0]->getPagina()->getId();
                        $padre_id = $arp->getPagina()->getId();
                        $titulo_padre = $arp->getPagina()->getNombre();
                        $titulo_hijo = $ar[0]->getPagina()->getNombre();
                        $imagen = $arp->getPagina()->getFoto();
                        $categoria = $ar[0]->getPagina()->getCategoria()->getNombre();
                        $porcentaje = round($arp->getPorcentajeAvance());
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                    }
                }else{

                    $id = 0;
                    $padre_id = $arp->getPagina()->getId();
                    $titulo_padre = $arp->getPagina()->getNombre();
                    $titulo_hijo = '';
                    $imagen = $arp->getPagina()->getFoto();
                    $categoria = $arp->getPagina()->getCategoria()->getNombre();
                    $porcentaje = round($arp->getPorcentajeAvance());
                    $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                }

                $actividad_reciente[]= array('id'=>$id,
                                             'padre_id'=>$padre_id,
                                             'titulo_padre'=>$titulo_padre,
                                             'titulo_hijo'=>$titulo_hijo,
                                             'imagen'=>$imagen,
                                             'categoria'=>$categoria,
                                             'fecha_vencimiento'=>$fecha_vencimiento,
                                             'porcentaje'=>$porcentaje);
            }
        // No tiene actividades
        }else{
            $reciente = 0;
        }

        $programas_aprobados = array();
        
        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach ($session->get('paginas') as $pg) {
            $paginas_ids[] = $pg['id'];
        }

        // Buscamos los programas finalizados y disponibles en la sesion
        $group_query_completados = $em->createQuery('SELECT cp
                                                    FROM LinkComunBundle:CertiPagina cp, 
                                                         LinkComunBundle:CertiPaginaLog cpl
                                                    WHERE cp.id IN (:pagina)
                                                    AND cpl.pagina = cp.id
                                                    AND cpl.usuario = :usuario
                                                    AND cpl.estatusPagina = :estatus
                                                    ORDER BY cp.id ASC')
                                      ->setParameters(array('usuario' => $session->get('usuario')['id'],
                                                            'estatus' => $yml['parameters']['estatus_pagina']['completada'],
                                                            'pagina' => $paginas_ids));
        $prog_completados = $group_query_completados->getResult();

        if(count($prog_completados) >=  1){
            $completado = 1;
            foreach ($prog_completados as $pg) {

                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $pg->getId()));

                if($datos_certi_pagina->getAcceso()){
                    // aprobado y con acceso de seguir viendo
                    $continuar = 2;
                }else{
                    // aprobado y sin poder ver solo descargar notas y certificados
                    $continuar = 3;
                }
               
                $programas_aprobados[]= array('id'=>$pg->getId(),
                                              'nombre'=>$pg->getNombre(),
                                              'imagen'=>$pg->getFoto(),
                                              'descripcion'=>$pg->getDescripcion(),
                                              'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                              'continuar'=>$continuar);
                
            }
        }else{
            $completado = 0;
        }

        return $this->render('LinkFrontendBundle:Programa:misProgramas.html.twig', array('reciente' => $reciente,
                                                                                         'actividad_reciente' => $actividad_reciente,
                                                                                         'completado' => $completado,
                                                                                         'programas_aprobados' => $programas_aprobados));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    
    public function programasAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $datos = $session;

        $programas_disponibles = array();
        
        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach ($session->get('paginas') as $pg) {
            $paginas_ids[] = $pg['id'];
        }

        // Buscamos los grupos disponibles para el usuario por los programas disponibles en la sesión
        $group_query = $em->createQuery('SELECT cg FROM LinkComunBundle:CertiGrupo cg 
                                        JOIN LinkComunBundle:CertiGrupoPagina cgp
                                        WHERE cg.empresa = :empresa
                                        AND  cgp.grupo = cg.id
                                        AND cgp.pagina IN (:pagina)
                                        ORDER BY cg.id ASC')
                        ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                              'pagina' => $paginas_ids));
        $grupos = $group_query->getResult();

        // Buscamos datos especificos de los programas de la sesion obteniendo el grupo al que pertenece cada programa
        $query_pages_by_group = $em->createQuery('SELECT cgp 
                                                  FROM LinkComunBundle:CertiGrupo cg,
                                                       LinkComunBundle:CertiGrupoPagina cgp,
                                                       LinkComunBundle:CertiPagina cp
                                                  WHERE cg.empresa = :empresa
                                                  AND  cgp.grupo = cg.id
                                                  AND cp.id IN (:pagina)
                                                  AND cgp.pagina = cp.id
                                                  ORDER BY cg.id ASC')
                                    ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                                          'pagina' => $paginas_ids));
        $pages_by_group = $query_pages_by_group->getResult();
        
        foreach ($pages_by_group as $pg) {

            // contruimos un array con los datos necesarios para el template y el grupo de cada programa
            $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                             'pagina' => $pg->getPagina()->getId()));

            $pagina_sesion = $session->get('paginas')[$pg->getPagina()->getId()];

            if(count($pagina_sesion['subpaginas']) >= 1){
                $tiene_subpaginas = 1;
            }else{
                $tiene_subpaginas = 0;
            }

            $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                'pagina' => $pg->getPagina()->getId()));
            if($datos_log){
                if($datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada']){
                    if($datos_certi_pagina->getAcceso()){
                        // aprobado y con acceso de seguir viendo
                        $continuar = 2;
                    }else{
                        // aprobado y sin poder ver solo descargar notas y certificados
                        $continuar = 3;
                    }
                }else{
                   // cursando actualemnete el progarama - continuar
                   $continuar = 1; 
                }
                
            }else{
                // si registros - iniciar
                $continuar = 0;
            }
           
            $programas_disponibles[]= array('id'=>$pg->getPagina()->getId(),
                                            'nombre'=>$pg->getPagina()->getNombre(),
                                            'nombregrupo'=>$pg->getGrupo()->getNombre(),
                                            'imagen'=>$pg->getPagina()->getFoto(),
                                            'descripcion'=>$pg->getPagina()->getDescripcion(),
                                            'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                            'tiene_subpaginas'=>$tiene_subpaginas,
                                            'continuar'=>$continuar);
            
        }

        return $this->render('LinkFrontendBundle:Programa:programas.html.twig', array('grupos' => $grupos,
                                                                                      'programas_disponibles' => $programas_disponibles));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

}