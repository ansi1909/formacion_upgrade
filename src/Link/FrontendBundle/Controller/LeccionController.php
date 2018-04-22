<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Link\ComunBundle\Entity\CertiMuro;
use Symfony\Component\Yaml\Yaml;

class LeccionController extends Controller
{
    public function indexAction($programa_id, $subpagina_id, $puntos, Request $request)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

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
        $espacio_colaborativo = $indexedPages[$programa_id]['espacio_colaborativo'];

        //return new Response(var_dump($indexedPages));

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($indexedPages, $session->get('paginas')[$programa_id], $subpagina_id, $this->generateUrl('_lecciones', array('programa_id' => $programa_id)), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);
        //return new Response(var_dump($menu_str));
        
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
            $titulo = $indexedPages[$programa_id]['categoria'].': '.$indexedPages[$programa_id]['nombre'];
        }
        else {
            if ($indexedPages[$subpagina_id]['hijos'] > 0 || $indexedPages[$subpagina_id]['sobrinos'] > 0 || $indexedPages[$subpagina_id]['tiene_evaluacion'])
            {
                $pagina_id = $indexedPages[$subpagina_id]['id'];
                if ($indexedPages[$indexedPages[$subpagina_id]['padre']]['padre'])
                {
                    $titulo = $indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['categoria'].': '.$indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['nombre'];
                    $subtitulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria'].': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                }
                else {
                    $titulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria'].': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                }
            }
            else {
                if ($indexedPages[$subpagina_id]['padre'] == $programa_id)
                {
                    $pagina_id = $programa_id;
                    $titulo = $indexedPages[$pagina_id]['categoria'].': '.$indexedPages[$pagina_id]['nombre'];
                }
                else {
                    $pagina_id = $indexedPages[$subpagina_id]['padre'];
                    if ($indexedPages[$indexedPages[$subpagina_id]['padre']]['padre'])
                    {
                        $titulo = $indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['categoria'].': '.$indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['nombre'];
                        $subtitulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria'].': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                    }
                    else {
                        $titulo = $indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria'].': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                    }
                }
                $wizard = 1;
            }
        }

        $lecciones = $f->contenidoLecciones($indexedPages[$pagina_id], $wizard, $session->get('usuario')['id'], $yml, $session->get('empresa')['id']);
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
                                                                                 'espacio_colaborativo' => $espacio_colaborativo,
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
        
        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

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

        //return new Response(var_dump($indexedPages));

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($indexedPages, $session->get('paginas')[$programa_id], $subpagina_id, $this->generateUrl('_lecciones', array('programa_id' => $programa_id)), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);

        // Se completa la lección
        $log_id = $f->finishLesson($indexedPages, $subpagina_id, $session->get('usuario')['id'], $yml);
        
        // Extraer los puntos generados
        $log_id_arr = explode("_", $log_id);
        $puntos += intval($log_id_arr[1]);

        // Determinar siguiente lección a ver
        $next_lesson = 0;
        $pagina_padre_id = 0;
        if ($indexedPages[$subpagina_id]['padre'])
        {
            $pagina_padre_id = $indexedPages[$subpagina_id]['padre'];
            $keys = array_keys($indexedPages[$pagina_padre_id]['subpaginas']);
            if (isset($keys[array_search($subpagina_id,$keys)+1]))
            {

                $next_lesson = $keys[array_search($subpagina_id,$keys)+1];

                // Se verifica si esta lección es prelada
                $pagina_empresa_next = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $next_lesson));
                if ($pagina_empresa_next->getPrelacion())
                {
                    // Se determina si el contenido estará bloqueado
                    $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl 
                                                WHERE pl.pagina = :pagina_id 
                                                AND pl.usuario = :usuario_id 
                                                AND pl.estatusPagina = :completada')
                                ->setParameters(array('pagina_id' => $pagina_empresa_next->getPrelacion(),
                                                      'usuario_id' => $session->get('usuario')['id'],
                                                      'completada' => $yml['parameters']['estatus_pagina']['completada']));
                    $leccion_completada = $query->getSingleScalarResult();
                    if (!$leccion_completada)
                    {
                        $next_lesson = 0;
                    }
                }

            }
            else {

                // Buscar la próxima página hermana que no haya sido completada
                foreach ($indexedPages[$pagina_padre_id]['subpaginas'] as $subpagina)
                {
                    $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                         'pagina' => $subpagina['id']));
                    if (!$pagina_log)
                    {
                        $next_lesson = $subpagina['id'];
                        break;
                    }
                    else {
                        if ($pagina_log->getEstatusPagina()->getId() != $yml['parameters']['estatus_pagina']['completada'])
                        {
                            $next_lesson = $subpagina['id'];
                            break;
                        }
                    }
                }

            }
        }

        // Si tiene evaluación, verificar que ya no haya presentado y aprobado.
        $boton_evaluacion = 0;
        $pagina_evaluacion_id = 0;
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
                    case $yml['parameters']['estado_prueba']['curso']:
                        $boton_evaluacion = 1;
                        break;
                    case $yml['parameters']['estado_prueba']['aprobado']:
                        $boton_evaluacion = 0;
                        break;
                    case $yml['parameters']['estado_prueba']['reprobado']:
                        // Cantidad de intentos
                        $query = $em->createQuery("SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPruebaLog pl 
                                                    JOIN pl.prueba p 
                                                    WHERE pl.usuario = :usuario_id 
                                                    AND p.pagina = :pagina_id")
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
                // Se verifica si la página tiene creada una evaluación
                $query = $em->createQuery("SELECT COUNT(p.id) FROM LinkComunBundle:CertiPrueba p 
                                            WHERE p.pagina = :pagina_id 
                                            AND p.estatusContenido = :activo")
                            ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                  'pagina_id' => $subpagina_id));
                $evaluaciones = $query->getSingleScalarResult();
                if ($evaluaciones)
                {
                    $boton_evaluacion = 1;
                }
            }
            if ($boton_evaluacion)
            {
                $pagina_evaluacion_id = $subpagina_id;
            }
        }
        else {
            // Si no hay más lecciones, se chequea si existe evaluación para el padre
            if (!$next_lesson && $pagina_padre_id)
            {
                if ($indexedPages[$pagina_padre_id]['tiene_evaluacion'])
                {
                    $query = $em->createQuery("SELECT pl FROM LinkComunBundle:CertiPruebaLog pl 
                                                JOIN pl.prueba p 
                                                WHERE pl.usuario = :usuario_id 
                                                AND p.pagina = :pagina_id 
                                                ORDER BY pl.id DESC")
                                ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                      'pagina_id' => $pagina_padre_id));
                    $pruebas_log = $query->getResult();
                    if ($pruebas_log)
                    {
                        switch ($pruebas_log[0]->getEstado())
                        {
                            case $yml['parameters']['estado_prueba']['curso']:
                                $boton_evaluacion = 1;
                                break;
                            case $yml['parameters']['estado_prueba']['aprobado']:
                                $boton_evaluacion = 0;
                                break;
                            case $yml['parameters']['estado_prueba']['reprobado']:
                                // Cantidad de intentos
                                $query = $em->createQuery("SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPruebaLog pl 
                                                            JOIN pl.prueba p 
                                                            WHERE pl.usuario = :usuario_id 
                                                            AND p.pagina = :pagina_id")
                                            ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                                  'pagina_id' => $pagina_padre_id));
                                $intentos = $query->getSingleScalarResult();
                                $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                                            WHERE pe.empresa = :empresa_id 
                                                            AND pe.pagina = :pagina_id")
                                            ->setParameters(array('empresa_id' => $session->get('empresa')['id'],
                                                                  'pagina_id' => $pagina_padre_id));
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
                        // Se verifica si la página tiene creada una evaluación
                        $query = $em->createQuery("SELECT COUNT(p.id) FROM LinkComunBundle:CertiPrueba p 
                                                    WHERE p.pagina = :pagina_id 
                                                    AND p.estatusContenido = :activo")
                                    ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                          'pagina_id' => $pagina_padre_id));
                        $evaluaciones = $query->getSingleScalarResult();
                        if ($evaluaciones)
                        {
                            $boton_evaluacion = 1;
                        }
                    }
                    if ($boton_evaluacion)
                    {
                        $pagina_evaluacion_id = $pagina_padre_id;
                    }
                }
            }
        }

        //return new Response('next_lesson: '.$next_lesson.', puntos: '.$puntos);
        //return new Response(var_dump($indexedPages[$subpagina_id]));

        return $this->render('LinkFrontendBundle:Leccion:finLecciones.html.twig', array('programa' => $programa,
                                                                                        'subpagina' => $indexedPages[$subpagina_id],
                                                                                        'menu_str' => $menu_str,
                                                                                        'next_lesson' => $next_lesson,
                                                                                        'puntos' => $puntos,
                                                                                        'boton_evaluacion' => $boton_evaluacion,
                                                                                        'pagina_evaluacion_id' => $pagina_evaluacion_id));

    }

    public function ajaxEnviarComentarioAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $pagina_id = $request->request->get('pagina_id');
        $mensaje = $request->request->get('mensaje');
        $muro_id = $request->request->get('muro_id');
        $prefix = $request->request->get('prefix');

        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);

        $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                            'usuario' => $session->get('usuario')['id']));

        $puntos_agregados = $yml['parameters']['puntos']['escribir_muro'];

        $muro = new CertiMuro();
        $muro->setMensaje($mensaje);
        $muro->setPagina($pagina);
        $muro->setUsuario($usuario);
        if ($muro_id)
        {

            $puntos_recibidos = $yml['parameters']['puntos']['respuesta_muro'];
            $muro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);
            $muro->setMuro($muro_padre);
            $pagina_log_padre = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $muro_padre->getPagina()->getId(),
                                                                                                      'usuario' => $muro_padre->getUsuario()->getId()));
            $puntos_padre = $pagina_log->getPuntos() + $puntos_recibidos;
            $pagina_log_padre->setPuntos($puntos_padre);
            $em->persist($pagina_log_padre);
            $em->flush();

            // Nueva alarma
            $descripcion = $usuario->getNombre().' '.$usuario->getApellido().' '.$this->get('translator')->trans('respondió a tu comentario en el muro de').' '.$pagina->getNombre().'.';
            $f->newAlarm($yml['parameters']['tipo_alarma']['respuesta_muro'], $descripcion, $muro_padre->getUsuario(), $muro_padre->getId());

        }
        $muro->setEmpresa($empresa);
        $muro->setFechaRegistro(new \DateTime('now'));
        $em->persist($muro);
        $em->flush();

        $puntos = $pagina_log->getPuntos() + $puntos_agregados;
        $pagina_log->setPuntos($puntos);
        $em->persist($pagina_log);
        $em->flush();

        // HTML para anexar al muro
        $uploads = $this->container->getParameter('folders')['uploads'];
        $img_user = $session->get('usuario')['foto'] ? $uploads.$session->get('usuario')['foto'] : $f->getWebDirectory().'/front/assets/img/user-default.png';
        $html = $muro_id ? '<div class="comment replied">' : '<div class="comment">';
        $html .= '<div class="comm-header d-flex align-items-center mb-2">
                    <img class="img-fluid avatar-img" src="'.$img_user.'" alt="">
                    <div class="wrap-info-user flex-column ml-2">
                        <div class="name text-xs color-dark-grey">'.$this->get('translator')->trans('Yo').'</div>
                        <div class="date text-xs color-grey">'.$this->get('translator')->trans('Ahora').'</div>
                    </div>
                </div>
                <div class="comm-body">
                    <p>'.$mensaje.'</p>
                </div>
                <div class="comm-footer d-flex justify-content-between align-items-center">
                    <a href="#" class="mr-0 text-sm color-light-grey like" data="'.$muro->getId().'">
                        <i id="'.$prefix.'_i-'.$muro->getId().'" class="material-icons mr-1 text-sm color-light-grey">thumb_up</i> <span id="'.$prefix.'_like-'.$muro->getId().'">0</span>
                    </a>';
        if (!$muro_id)
        {
            $html .= '<a href="#" class="links text-right text-xs reply_comment" data="'.$muro->getId().'">'.$this->get('translator')->trans('Responder').'</a>';
        }
        $html .= '</div>';
        if (!$muro_id)
        {
            $html .= '<div id="'.$prefix.'_div-response-'.$muro->getId().'"></div>
                      <div id="'.$prefix.'_respuestas-'.$muro->getId().'"></div>';
        }
        $html .= '</div>';

        $return = array('html' => $html,
                        'muro_id' => $muro->getId(),
                        'puntos_agregados' => $puntos_agregados);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDivResponseAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $muro_id = $request->query->get('muro_id');
        $prefix = $request->query->get('prefix');

        $uploads = $this->container->getParameter('folders')['uploads'];
        $img_user = $session->get('usuario')['foto'] ? $uploads.$session->get('usuario')['foto'] : $f->getWebDirectory().'/front/assets/img/user-default.png';
        
        $html = '<div class="response d-flex align-items-center justify-content-between" id="'.$prefix.'_response-'.$muro_id.'">
                    <img class="img-fluid avatar-img" src="'.$img_user.'" alt="">
                    <form class="mt-3" method="POST">
                        <div class="form-group">
                            <textarea class="form-control" id="'.$prefix.'_respuesta_'.$muro_id.'" name="'.$prefix.'_respuesta_'.$muro_id.'" rows="5" maxlength="340" placeholder="'.$this->get('translator')->trans('Escriba su respuesta').'"></textarea>
                        </div>
                        <button type="button" name="button" class="btn btn-sm btn-primary float-right button-reply" data="'.$muro_id.'" id="'.$prefix.'_button-reply-'.$muro_id.'">'.$this->get('translator')->trans('Responder').'</button>
                    </form>
                </div>';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxRefreshMuroAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $f = $this->get('funciones');
        $pagina_id = $request->query->get('pagina_id');
        $prefix = $request->query->get('prefix');
        $html = '';

        if ($prefix == 'recientes')
        {
            $muros = $f->muroPagina($pagina_id, 'id', 'DESC', 0, 5, $session->get('usuario')['id'], $session->get('empresa')['id'], $yml['parameters']['social']);
        }
        else {
            $muros = $f->muroPaginaValorados($pagina_id, 0, 5, $session->get('usuario')['id'], $session->get('empresa')['id'], $yml['parameters']['social']);
        }

        $total_comentarios = $muros['total_comentarios'];
        $total_muro = count($muros['muros']);

        foreach ($muros['muros'] as $muro)
        {
            $html .= $f->drawComment($muro, $prefix);
        }

        if ($total_comentarios > $total_muro)
        {
            $html .= '<input type="hidden" id="more_comments_'.$prefix.'-'.$pagina_id.'" name="more_comments_'.$prefix.'-'.$pagina_id.'" value="0">
                      <a href="#" class="links text-center d-block more_comments" data="'.$pagina_id.'">'.$this->get('translator')->trans('Ver más comentarios').'</a>';
        }
        
        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxMasMuroAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $f = $this->get('funciones');
        $pagina_id = $request->query->get('pagina_id');
        $muro_id = $request->query->get('muro_id');
        $prefix = $request->query->get('prefix');
        $offset = $request->query->get('offset');
        $offset += 5;
        $next_offset = $offset+5;
        $html = '';

        if ($muro_id)
        {

            // Más respuestas
            $submuros = $f->subMuros($muro_id, $offset, 5, $session->get('usuario')['id'], $yml['parameters']['social']);

            // Total de respuestas de este comentario
            $query = $em->createQuery('SELECT COUNT(m.id) FROM LinkComunBundle:CertiMuro m 
                                        WHERE m.muro = :muro_id')
                        ->setParameter('muro_id', $muro_id);
            $total_respuestas = $query->getSingleScalarResult();

            foreach ($submuros as $submuro)
            {
                $html .= $f->drawResponses($submuro, $prefix);
            }

            if ($total_respuestas > $next_offset)
            {
                $html .= '<input type="hidden" id="'.$prefix.'_more_answers-'.$muro_id.'" name="'.$prefix.'_more_answers-'.$muro_id.'" value="'.$offset.'">
                          <a href="#" class="links text-center d-block more_answers" data="'.$muro_id.'">'.$this->get('translator')->trans('Ver más respuestas').'</a>';
            }

        }
        else {

            // Más comentarios
            if ($prefix == 'recientes')
            {
                $muros = $f->muroPagina($pagina_id, 'id', 'DESC', $offset, 5, $session->get('usuario')['id'], $session->get('empresa')['id'], $yml['parameters']['social']);
            }
            else {
                $muros = $f->muroPaginaValorados($pagina_id, $offset, 5, $session->get('usuario')['id'], $session->get('empresa')['id'], $yml['parameters']['social']);
            }

            $total_comentarios = $muros['total_comentarios'];

            foreach ($muros['muros'] as $muro)
            {
                $html .= $f->drawComment($muro, $prefix);
            }

            if ($total_comentarios > $next_offset)
            {
                $html .= '<input type="hidden" id="more_comments_'.$prefix.'-'.$pagina_id.'" name="more_comments_'.$prefix.'-'.$pagina_id.'" value="'.$offset.'">
                          <a href="#" class="links text-center d-block more_comments" data="'.$pagina_id.'">'.$this->get('translator')->trans('Ver más comentarios').'</a>';
            }

        }
        
        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
