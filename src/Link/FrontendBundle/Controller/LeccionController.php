<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Symfony\Component\Yaml\Yaml;

class LeccionController extends Controller
{
    public function indexAction($programa_id, $subpagina_id, $puntos, Request $request)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        /*if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));*/

        $em = $this->getDoctrine()->getManager();

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($session->get('paginas')[$programa_id], $subpagina_id, $this->generateUrl('_lecciones', array('programa_id' => $programa_id)), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);
        //return new Response(var_dump($menu_str));

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);
        //return new Response(var_dump($indexedPages));

        // Prueba activa
        $prueba_activa = 0;
        if ($session->get('paginas')[$programa_id]['tiene_evaluacion'])
        {
            $prueba_activa = $f->pruebaActiva($session->get('paginas')[$programa_id], $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);
        }

        // También se anexa a la indexación el programa padre
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $pagina = $session->get('paginas')[$programa_id];
        $pagina['padre'] = 0;
        $pagina['sobrinos'] = 0;
        $pagina['hijos'] = count($pagina['subpaginas']);
        $pagina['descripcion'] = $programa->getDescripcion();
        $pagina['contenido'] = $programa->getContenido();
        $pagina['foto'] = $programa->getFoto();
        $pagina['pdf'] = $programa->getPdf();
        $pagina['next_subpage'] = 0;
        $indexedPages[$pagina['id']] = $pagina;

        //return new Response(var_dump($indexedPages));
        
        $wizard = 0; // 1 Indica que llevan los círculos de navegación
        $titulo = '';
        $subtitulo = '';
        $pagina_id = $programa_id;

        if ($subpagina_id == 0 || $subpagina_id == $programa_id)
        {
            if (count($indexedPages[$programa_id]['subpaginas']))
            {
                $i = 0;
                foreach ($indexedPages[$programa_id]['subpaginas'] as $subpagina_arr)
                {
                    $i++;
                    $subpagina = $indexedPages[$subpagina_arr['id']];
                    if ($i == 1)
                    {
                        // Solo la primera iteración. Se mostrará el primer módulo por defecto.
                        if ($subpagina['sobrinos'] > 0)
                        {
                            $pagina_id = $subpagina['id'];
                        }
                        else {
                            $pagina_id = $programa_id;
                            $wizard = 1;
                        }
                    }
                    if ($subpagina['tiene_evaluacion'])
                    {
                        $pagina_id = $subpagina['id'];
                        $wizard = 0;
                        break;
                    }
                }
            }
            else {
                $pagina_id = $programa_id;
            }
            $titulo = $indexedPages[$programa_id]['nombre'];
        }
        else {
            if ($indexedPages[$subpagina_id]['hijos'] > 0 || $indexedPages[$subpagina_id]['sobrinos'] > 0 || $indexedPages[$subpagina_id]['tiene_evaluacion'])
            {
                $pagina_id = $indexedPages[$subpagina_id]['id'];
                if ($indexedPages[$indexedPages[$subpagina_id]['padre']]['padre'])
                {
                    $titulo = $indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['nombre'];
                    $subtitulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                }
                else {
                    $titulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                }
            }
            else {
                if ($indexedPages[$subpagina_id]['padre'] == $programa_id)
                {
                    $pagina_id = $programa_id;
                    $titulo = $indexedPages[$pagina_id]['nombre'];
                }
                else {
                    $pagina_id = $indexedPages[$subpagina_id]['padre'];
                    if ($indexedPages[$indexedPages[$subpagina_id]['padre']]['padre'])
                    {
                        $titulo = $indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['nombre'];
                        $subtitulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                    }
                    else {
                        $titulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                    }
                }
                $wizard = 1;
            }
        }

        $lecciones = $f->contenidoLecciones($indexedPages[$pagina_id], $wizard, $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada'], $yml['parameters']['estatus_pagina']['en_evaluación']);
        $lecciones['wizard'] = $wizard;

        // Se reinicia el reinicia el reloj de pagina_log
        $id_pagina_log = $wizard ? $lecciones['subpaginas'][0]['id'] : $lecciones['id'];
        $logs = $f->startLesson($indexedPages, $id_pagina_log, $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['iniciada']);

        //return new Response(var_dump($logs));
        //return new Response(var_dump($lecciones));

        return $this->render('LinkFrontendBundle:Leccion:index.html.twig', array('programa' => $programa,
                                                                                 'subpagina_id' => $subpagina_id,
                                                                                 'menu_str' => $menu_str,
                                                                                 'lecciones' => $lecciones,
                                                                                 'titulo' => $titulo,
                                                                                 'subtitulo' => $subtitulo,
                                                                                 'wizard' => $wizard,
                                                                                 'prueba_activa' => $prueba_activa,
                                                                                 'puntos' => $puntos));

    }

    public function ajaxIniciarPaginaAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $programa_id = $request->request->get('programa_id');
        $pagina_id = $request->request->get('pagina_id');

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);

        // También se anexa a la indexación el programa padre
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $pagina = $session->get('paginas')[$programa_id];
        $pagina['padre'] = 0;
        $pagina['sobrinos'] = 0;
        $pagina['hijos'] = count($pagina['subpaginas']);
        $pagina['descripcion'] = $programa->getDescripcion();
        $pagina['contenido'] = $programa->getContenido();
        $pagina['foto'] = $programa->getFoto();
        $pagina['pdf'] = $programa->getPdf();
        $pagina['next_subpage'] = 0;
        $indexedPages[$pagina['id']] = $pagina;

        $logs = $f->startLesson($indexedPages, $pagina_id, $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['iniciada']);

        $return = array('logs' => $logs);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxProcesarPaginaAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $programa_id = $request->request->get('programa_id');
        $pagina_id = $request->request->get('pagina_id');

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);

        // También se anexa a la indexación el programa padre
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $pagina = $session->get('paginas')[$programa_id];
        $pagina['padre'] = 0;
        $pagina['sobrinos'] = 0;
        $pagina['hijos'] = count($pagina['subpaginas']);
        $pagina['descripcion'] = $programa->getDescripcion();
        $pagina['contenido'] = $programa->getContenido();
        $pagina['foto'] = $programa->getFoto();
        $pagina['pdf'] = $programa->getPdf();
        $pagina['next_subpage'] = 0;
        $indexedPages[$pagina['id']] = $pagina;

        $log_id = $f->finishLesson($indexedPages, $pagina_id, $session->get('usuario')['id'], $yml);

        $return = array('id' => $log_id);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function finLeccionesAction($programa_id, $subpagina_id, $puntos, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        /*if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));*/

        $em = $this->getDoctrine()->getManager();

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($session->get('paginas')[$programa_id], $subpagina_id, $this->generateUrl('_lecciones', array('programa_id' => $programa_id)), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);

        // También se anexa a la indexación el programa padre
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $pagina = $session->get('paginas')[$programa_id];
        $pagina['padre'] = 0;
        $pagina['sobrinos'] = 0;
        $pagina['hijos'] = count($pagina['subpaginas']);
        $pagina['descripcion'] = $programa->getDescripcion();
        $pagina['contenido'] = $programa->getContenido();
        $pagina['foto'] = $programa->getFoto();
        $pagina['pdf'] = $programa->getPdf();
        $pagina['next_subpage'] = 0;
        $indexedPages[$pagina['id']] = $pagina;

        // Se completa la lección
        $log_id = $f->finishLesson($indexedPages, $subpagina_id, $session->get('usuario')['id'], $yml);
        
        // Extraer los puntos generados
        $log_id_arr = explode("_", $log_id);
        $puntos += $log_id[1];

        // Determinar siguiente lección a ver
        $next_lesson = 0;
        if ($indexedPages[$subpagina_id]['padre'])
        {
            $pagina_padre_id = $indexedPages[$subpagina_id]['padre'];
            $keys = array_keys($indexedPages[$pagina_padre_id]['subpaginas']);
            if (isset($keys[array_search($subpagina_id,$keys)+1]))
            {
                $next_lesson = $keys[array_search($subpagina_id,$keys)+1];
            }
        }

        // Si tiene evaluación, verificar que ya no haya presentado y aprobado.
        $boton_evaluacion = 0;
        if ($indexedPages[$subpagina_id]['tiene_evaluacion'])
        {
            $query = $em->createQuery("SELECT pl FROM LinkComunBundle:CertiPruebaLog pl 
                                        JOIN pl.prueba p 
                                        WHERE pl.usuario = :usuario_id 
                                        AND p.pagina = :pagina_id 
                                        ORDER BY pl.id DESC")
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                              'pagina_id' => $subpagina_id));
            $pruebas_log = $query->getResult();
            if ($pruebas_log)
            {
                switch ($pruebas_log[0]->getEstado())
                {
                    case 'EN CURSO':
                        $boton_evaluacion = 1;
                        break;
                    case 'APROBADO':
                        $boton_evaluacion = 0;
                        break;
                    case 'REPROBADO':
                        // Cantidad de intentos
                        $query = $em->createQuery("SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPruebaLog pl 
                                                    JOIN pl.prueba p 
                                                    WHERE pl.usuario = :usuario_id 
                                                    AND p.pagina = :pagina_id 
                                                    ORDER BY pl.id DESC")
                                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                          'pagina_id' => $subpagina_id));
                        $intentos = $query->getSingleScalarResult();
                        $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                                    WHERE pe.empresa = :empresa_id 
                                                    AND pe.pagina = :pagina_id")
                                    ->setParameters(array('empresa_id' => $session->get('empresa')['id'],
                                                          'pagina_id' => $subpagina_id));
                        $pe = $query->getResult();
                        $max_intentos = $pe[0]->getMaxIntentos();
                        if ($intentos < $max_intentos)
                        {
                            $boton_evaluacion = 1;
                        }
                        break;
                }
            }
            else {
                $boton_evaluacion = 1;
            }
        }

        //return new Response('next_lesson: '.$next_lesson.', puntos: '.$puntos);
        //return new Response(var_dump($indexedPages[$subpagina_id]));

        return $this->render('LinkFrontendBundle:Leccion:finLecciones.html.twig', array('programa' => $programa,
                                                                                        'subpagina' => $indexedPages[$subpagina_id],
                                                                                        'menu_str' => $menu_str,
                                                                                        'next_lesson' => $next_lesson,
                                                                                        'puntos' => $puntos,
                                                                                        'boton_evaluacion' => $boton_evaluacion));

    }

}
