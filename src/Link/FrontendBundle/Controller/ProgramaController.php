<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class ProgramaController extends Controller
{

    public function programaAction($programa_id, Request $request)
    {

         $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {

            $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
            $pagina_sesion = $session->get('paginas')[$programa_id];

            $subpaginas_array = array();

            if (count($pagina_sesion['subpaginas']) > 0) {
                $modulos = 1;

                $next_pagina_id = 0;
                foreach ($pagina_sesion['subpaginas'] as $subpagina){

                    $query_actividad = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                         WHERE ar.usuario = :usuario_id
                                                         AND ar.pagina = :pagina
                                                         AND ar.estatusPagina = :completada
                                                         ORDER BY ar.id DESC')
                                          ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                                'pagina' => $subpagina['id'],
                                                                'completada' => $yml['parameters']['estatus_pagina']['completada']));
                    $actividadreciente = $query_actividad->getResult();

                    if($$actividadreciente and $next_pagina == 0){
                        $next_pagina_id = $subpagina['id'];
                    }
                }
            }
            else{
                $modulos = 0;
            }

        }
        else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Programa:index.html.twig', array('pagina' => $pagina));

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
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $datos = $session;
            $empresa_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
            $bienvenida = $empresa_obj->getBienvenida();

            // buscando las últimas 3 interacciones del usuario donde la página no este completada
            $query_actividad = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                 WHERE ar.usuario = :usuario_id
                                                 AND ar.estatusPagina != :completada
                                                 ORDER BY ar.id DESC')
                                  ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                        'completada' => $yml['parameters']['estatus_pagina']['completada']))
                                  ->setMaxResults(3);
            $actividadreciente = $query_actividad->getResult();

            $actividad_reciente = array();
            // Si tiene actividades
            if(count($actividadreciente) >=  1){
                $reciente = 1;
                foreach ($actividadreciente as $ar) {
                    // Si la actividad reciente es con una pagina hija
                    if($ar->getPagina()->getPagina()){
                        $es_hija = 1;
                        // buscamos la página padre
                        $datos_log_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getPagina()->getId()));
                        // buscamos los datos de la pagina contra empresa para obntener la fecha de vencimiento
                        $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getPagina()->getId()));

                        // creamos variables para añadir al array
                        $padre_id = $ar->getPagina()->getPagina()->getId();
                        $titulo_padre = $ar->getPagina()->getPagina()->getNombre();
                        $titulo_hijo = $ar->getPagina()->getNombre();
                        $imagen = $ar->getPagina()->getPagina()->getFoto();
                        $categoria = $ar->getPagina()->getCategoria()->getNombre();
                        $porcentaje = $datos_log_padre->getPorcentajeAvance();
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                    // Si la actividad reciente es con una pagina padre
                    }else{
                        $es_hija = 0;
                        // buscamos los datos de la pagina contra empresa para obntener la fecha de vencimiento
                        $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getId()));

                        // creamos variables para añadir al array
                        $padre_id = 0;
                        $titulo_padre = $ar->getPagina()->getNombre();
                        $titulo_hijo = '';
                        $imagen = $ar->getPagina()->getFoto();
                        $categoria = $ar->getPagina()->getNombre();
                        $porcentaje = $ar->getPorcentajeAvance();
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
                    }

                    // Validando si el programa tiene imagen para asignar una por defecto
                    if($imagen){
                        $imagen = $imagen;
                    }else{
                        $imagen = 'front/assets/img/liderazgo.png';
                    }

                    $actividad_reciente[]= array('id'=>$ar->getPagina()->getId(),
                                                 'padre_id'=>$padre_id,
                                                 'es_hija'=>$es_hija,
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
                        
                    if($pg->getFoto()){
                        $imagen = $pg->getFoto();
                    }else{
                        $imagen = 'front/assets/img/liderazgo.png';
                    }
                   
                    $programas_aprobados[]= array('id'=>$pg->getId(),
                                                  'nombre'=>$pg->getNombre(),
                                                  'imagen'=>$imagen,
                                                  'descripcion'=>$pg->getDescripcion(),
                                                  'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                                  'continuar'=>$continuar);
                    
                }
            }else{
                $completado = 0;
            }

        }
        else {
            return $this->redirectToRoute('_login');
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
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {
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
                if($pg->getPagina()->getFoto()){
                    $imagen = $pg->getPagina()->getFoto();
                }else{
                    $imagen = 'front/assets/img/liderazgo.png';
                }
               
                $programas_disponibles[]= array('id'=>$pg->getPagina()->getId(),
                                                'nombre'=>$pg->getPagina()->getNombre(),
                                                'nombregrupo'=>$pg->getGrupo()->getNombre(),
                                                'imagen'=>$imagen,
                                                'descripcion'=>$pg->getPagina()->getDescripcion(),
                                                'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                                'continuar'=>$continuar);
                
            }
        }
        else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Programa:programas.html.twig', array('grupos' => $grupos,
                                                                                      'programas_disponibles' => $programas_disponibles));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

}