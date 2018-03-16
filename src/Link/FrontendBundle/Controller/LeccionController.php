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
    public function indexAction($programa_id, $subpagina_id, Request $request)
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

        // Prueba activa
        $prueba_activa = 0;
        if ($session->get('paginas')[$programa_id]['tiene_evaluacion'])
        {
            $prueba_activa = $f->pruebaActiva($session->get('paginas')[$programa_id], $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);
        }

        // También se anexa a la indexación el programa padre
        $pagina = $session->get('paginas')[$programa_id];
        $pagina['padre'] = 0;
        $pagina['sobrinos'] = 0;
        $pagina['hijos'] = count($pagina['subpaginas']);
        $indexedPages[$pagina['id']] = $pagina;

        //return new Response(var_dump($indexedPages));

        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $wizard = 0; // 1 Indica que llevan los círculos de navegación
        $titulo = '';
        $subtitulo = '';

        if ($subpagina_id == 0 || $subpagina_id == $programa_id)
        {
            foreach ($indexedPages[$programa_id]['subpaginas'] as $subpagina_arr)
            {
                $subpagina = $indexedPages[$subpagina_arr['id']];
                if ($subpagina['sobrinos'] > 0)
                {
                    $pagina_id = $subpagina['id'];
                }
                else {
                    $pagina_id = $programa_id;
                    $wizard = 1;
                }
                break;  // Solo la primera iteración. Se mostrará el primer módulo por defecto.
            }
            $titulo = $indexedPages[$programa_id]['nombre'];
        }
        else {
            if ($indexedPages[$subpagina_id]['hijos'] > 0 || $indexedPages[$subpagina_id]['sobrinos'] > 0)
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

        // Se reinicia el reinicia el reloj de pagina_log

        //return new Response(var_dump($lecciones));

        return $this->render('LinkFrontendBundle:Leccion:index.html.twig', array('programa' => $programa,
                                                                                 'subpagina_id' => $subpagina_id,
                                                                                 'menu_str' => $menu_str,
                                                                                 'lecciones' => $lecciones,
                                                                                 'titulo' => $titulo,
                                                                                 'subtitulo' => $subtitulo,
                                                                                 'wizard' => $wizard,
                                                                                 'prueba_activa' => $prueba_activa));

    }

}
