<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Link\ComunBundle\Entity\CertiMuro;
use Link\ComunBundle\Entity\AdminCorreo;
use Link\ComunBundle\Entity\AdminMedallasUsuario;
use Link\ComunBundle\Entity\AdminMedallas;
use Symfony\Component\Yaml\Yaml;

class LeccionController extends Controller
{
    public function indexAction($programa_id, $subpagina_id, $puntos, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();


        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);

        // También se anexa a la indexación el programa padre
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $boton_continuar = ($programa->getCategoria()->getId() != $yml['parameters']['categoria']['competencia'])? 'Continuar':'Terminar';
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
        //return new Response(var_dump($menu_str));
        
        $wizard = 0; // 1 Indica que llevan los círculos de navegación
        $titulo = '';
        $subtitulo = '';
        $pagina_id = $programa_id;
       // return new response($subpagina_id.' '.$programa_id);

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
                    $titulo = $this->get('translator')->trans(trim($indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['categoria'])).': '.$indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['nombre'];
                    $subtitulo = $this->get('translator')->trans(trim($indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria'])).' '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['orden'].': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                }
                else {
                    $titulo = $this->get('translator')->trans(trim($indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria'])).': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                }
            }
            else {
                if ($indexedPages[$subpagina_id]['padre'] == $programa_id)
                {
                    $pagina_id = $programa_id;
                    $titulo = $this->get('translator')->trans($indexedPages[$pagina_id]['categoria']).': '.$indexedPages[$pagina_id]['nombre'];
                }
                else {
                    $pagina_id = $indexedPages[$subpagina_id]['padre'];
                    if ($indexedPages[$indexedPages[$subpagina_id]['padre']]['padre'])
                    {
                        $titulo = $this->get('translator')->trans($indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['categoria']).': '.$indexedPages[$indexedPages[$indexedPages[$subpagina_id]['padre']]['padre']]['nombre'];
                        $subtitulo = $this->get('translator')->trans($indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria']).' '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['orden'].': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                    }
                    else {
                        $titulo = $this->get('translator')->trans($indexedPages[$indexedPages[$subpagina_id]['padre']]['categoria']).': '.$indexedPages[$indexedPages[$subpagina_id]['padre']]['nombre'];
                    }
                }
                $wizard = 1;
            }
        }

        $lecciones = $f->contenidoLecciones($indexedPages[$pagina_id], $wizard, $session->get('usuario')['id'], $yml, $session->get('empresa')['id']);
        //print_r($lecciones);die();
        $lecciones['wizard'] = $wizard;

        
        $id_pagina_log = $wizard ? $lecciones['subpaginas'][0]['id'] : $lecciones['id'];
        $logs = $f->startLesson($indexedPages, $id_pagina_log, $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['iniciada']);
      
        $totalComentarios = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->findBy(array('pagina'=>$id_pagina_log,'empresa'=>$session->get('empresa')['id']));
        $totalComentarios = count($totalComentarios);
        //print_r($lecciones);die();
        return $this->render('LinkFrontendBundle:Leccion:index.html.twig', array('programa' => $programa,
                                                                                 'subpagina_id' => $subpagina_id,
                                                                                 'lecciones' => $lecciones,
                                                                                 'titulo' => $titulo,
                                                                                 'subtitulo' => $subtitulo,
                                                                                 'wizard' => $wizard,
                                                                                 'puntos' => $puntos,
                                                                                 'comentarios'=> $totalComentarios,
                                                                                 'boton_continuar'=> $boton_continuar));

    }

    public function ajaxIniciarPaginaAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $programa_id = $request->request->get('programa_id');
        $pagina_id = $request->request->get('pagina_id');
        $empresa_id = $session->get('empresa')['id'];
        $comentarios = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->findBy(array('pagina'=>$pagina_id,'empresa'=>$empresa_id));
        
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

        $return = array('logs' => $logs,'comentarios'=>count($comentarios));

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
        
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
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

        // Se completa la lección
        $log_id = $f->finishLesson($indexedPages, $subpagina_id, $session->get('usuario')['id'], $yml);
        
        // Extraer los puntos generados
        $log_id_arr = explode("_", $log_id);
        $puntos += intval($log_id_arr[1]);

        // Determinar siguiente lección a ver
        $continue_button = $f->nextLesson($indexedPages, $subpagina_id, $session->get('usuario')['id'], $session->get('empresa')['id'], $yml, $programa_id);

        if ($continue_button['evaluacion'])
        {

            $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($continue_button['evaluacion']);

            // Duración en minutos
            $duracion = intval($prueba->getDuracion()->format('G'))*60;
            $duracion += intval($prueba->getDuracion()->format('i'));

        }
        else {
            $duracion = 0;
        }

        /*if($continue_button['next_lesson'] == 0)
        {
            $continue_button['pagina_padre_id']
        }*/
       // print_r($indexedPages[$subpagina_id]);die();
        $certificado = ($programa->getCategoria()->getId() == $yml['parameters']['categoria']['competencia'])? true:false;

        return $this->render('LinkFrontendBundle:Leccion:finLecciones.html.twig', array('programa' => $programa,
                                                                                        'subpagina' => $indexedPages[$subpagina_id],
                                                                                        'certificado' => $certificado,
                                                                                        'continue_button' => $continue_button,
                                                                                        'puntos' => $puntos,
                                                                                        'duracion' => $duracion));

    }

    

    public function ajaxEnviarComentarioAction(Request $request)
    {
          try{  
            $session = new Session();
            $em = $this->getDoctrine()->getManager();
            $f = $this->get('funciones');
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $tipoMensaje = 'Publicó';
            $usuarioPadre = '';
            $mensajePadre = '';
            $is_tutor = $session->get('usuario')['tutor']? 1:0;
            $pagina_id = $request->request->get('pagina_id');
            //return new response($pagina_id);
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

            $background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
            $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion_smart.png';
            $footer = $this->container->getParameter('folders')['uploads'].'recursos/footer.bg.form.png';
            $link_plataforma = $this->container->getParameter('link_plataforma').$empresa->getId();
            $correo = 0;

            if ($muro_id)
            {
                $puntos_recibidos = $yml['parameters']['puntos']['respuesta_muro'];
                $muro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);
                $mensajePadre = $muro_padre->getMensaje();
                $usuarioPadre = $muro_padre->getUsuario()->getNombre().' '.$muro_padre->getUsuario()->getApellido();
                $muro->setMuro($muro_padre);
                //return new response($muro_padre->getPagina()->getId());
                $pagina_log_padre = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $muro_padre->getPagina()->getId(),
                                                                                                         'usuario' => $muro_padre->getUsuario()->getId()));
                
                $tipoMensaje = 'Respondió';
                $puntos_padre = $pagina_log->getPuntos() + $puntos_recibidos;
                $pagina_log_padre->setPuntos($puntos_padre);
                $em->persist($pagina_log_padre);
                $em->flush();

                // Nueva alarma
                $descripcion = $usuario->getNombre().' '.$usuario->getApellido().' '.$this->get('translator')->trans('respondió a tu comentario en el muro de').' '.$pagina->getNombre().'.';
                $f->newAlarm($yml['parameters']['tipo_alarma']['respuesta_muro'], $descripcion, $muro_padre->getUsuario(), $muro_padre->getId());

                // Envío de correo al tutor virtual solo sí el dueño del comentario inicial es de éste y la respuesta es de otro usuario
                $query = $em->createQuery('SELECT COUNT(ru.id) FROM LinkComunBundle:AdminRolUsuario ru 
                                            WHERE ru.usuario = :usuario_id 
                                            AND ru.rol = :tutor')
                            ->setParameters(array('usuario_id' => $muro_padre->getUsuario()->getId(),
                                                  'tutor' => $yml['parameters']['rol']['tutor']));
                $owner_tutor = $query->getSingleScalarResult();

                $correo_tutor = (!$muro_padre->getUsuario()->getCorreoPersonal() || $muro_padre->getUsuario()->getCorreoPersonal() == '') ? (!$muro_padre->getUsuario()->getCorreoCorporativo() || $muro_padre->getUsuario()->getCorreoCorporativo() == '') ? 0 : $muro_padre->getUsuario()->getCorreoCorporativo() : $muro_padre->getUsuario()->getCorreoPersonal();
                if ($muro_padre->getUsuario()->getId() != $usuario->getId() && $owner_tutor && $correo_tutor)
                {

                    $categoria = $f->obtenerProgramaCurso($pagina);
                    $parametros_correo = array('twig' => 'LinkFrontendBundle:Leccion:emailMuro.html.twig',
                                               'datos' => array('logo'=> $logo,
                                                                'footer' => $footer,
                                                                'background' => $background, 
                                                                'nombre' => $muro_padre->getUsuario()->getNombre().' '.$muro_padre->getUsuario()->getApellido(),
                                                                'usuario'=> $usuario->getNombre().' '.$usuario->getApellido(),
                                                                'comentarioPadre' => $muro_padre->getMensaje(),
                                                                'respuesta' => $mensaje,
                                                                'link_plataforma' => $link_plataforma,
                                                                'logo' => $logo,
                                                                'categoria' => $categoria['categoria'],
                                                                'pagina' => $categoria['nombre'],
                                                                'empresa' => $empresa->getNombre()),
                                               'asunto' => 'Formación Smart: '.$descripcion,
                                               'remitente' => $this->container->getParameter('mailer_user'),
                                               'remitente_name' => $this->container->getParameter('mailer_user_name'),
                                               'mailer'=>'soporte_mailer',
                                               'destinatario' => $correo_tutor);
                    $correo = $f->sendEmail($parametros_correo);

                }             

            }

            $muro->setEmpresa($empresa);
            $muro->setFechaRegistro(new \DateTime('now'));
            $em->persist($muro);
            $em->flush();

            $query = $em->createQuery('SELECT COUNT(cm.id) FROM LinkComunBundle:CertiMuro cm 
                                    WHERE cm.muro = :muro_id')
                        ->setParameter('muro_id', $muro_id);
            $comentariosTotal = $query->getSingleScalarResult();

            $pagina_padre_id = $f->paginaRaiz($pagina);
            
            $pagina_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_padre_id);

            $comentarios = $comentariosTotal / 3;

            if( is_int($comentarios))
            {
                if($comentariosTotal == 3)
                {
                    $medallaUsuario = $em->getRepository('LinkComunBundle:AdminMedallasUsuario')->findOneBy(array('pagina' => $pagina_padre->getId(),
                                                                                                                  'usuario' => $muro_padre->getUsuario()->getId(),
                                                                                                                  'medalla' => $yml['parameters']['medallas']['influencer_1']));
                    if(!$medallaUsuario)
                    {
                        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($yml['parameters']['medallas']['influencer_1']);
                        
                        $medallaUsuario = new AdminMedallasUsuario();
                        $medallaUsuario->setUsuario($usuario);
                        $medallaUsuario->setMedalla($medalla);
                        $medallaUsuario->setPagina($pagina_padre);
                        $em->persist($medallaUsuario);
                        $em->flush();

                        $puntos_agregados = $puntos_agregados + $yml['parameters']['puntos']['influencer_1'];
                        //return new response(var_dump($puntos_agregados));
                    }
                }
                elseif($comentariosTotal == 6)
                {
                    $medallaUsuario = $em->getRepository('LinkComunBundle:AdminMedallasUsuario')->findOneBy(array('pagina' => $pagina_padre->getId(),
                                                                                                                  'usuario' => $muro_padre->getUsuario()->getId(),
                                                                                                                  'medalla' => $yml['parameters']['medallas']['influencer_2']));
                    if(!$medallaUsuario)
                    {
                        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($yml['parameters']['medallas']['influencer_2']);
                        
                        $medallaUsuario = new AdminMedallasUsuario();
                        $medallaUsuario->setUsuario($usuario);
                        $medallaUsuario->setMedalla($medalla);
                        $medallaUsuario->setPagina($pagina_padre);
                        $em->persist($medallaUsuario);
                        $em->flush();

                        $puntos_agregados = $puntos_agregados + $yml['parameters']['puntos']['influencer_2'];
                        //return new response(var_dump($puntos_agregados));
                    }
                }
                elseif($comentariosTotal == 9)
                {
                    $medallaUsuario = $em->getRepository('LinkComunBundle:AdminMedallasUsuario')->findOneBy(array('pagina' => $pagina_padre->getId(),
                                                                                                                  'usuario' => $muro_padre->getUsuario()->getId(),
                                                                                                                  'medalla' => $yml['parameters']['medallas']['influencer_3']));
                    if(!$medallaUsuario)
                    {
                        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($yml['parameters']['medallas']['influencer_3']);
                        
                        $medallaUsuario = new AdminMedallasUsuario();
                        $medallaUsuario->setUsuario($usuario);
                        $medallaUsuario->setMedalla($medalla);
                        $medallaUsuario->setPagina($pagina_padre);
                        $em->persist($medallaUsuario);
                        $em->flush();

                        $puntos_agregados = $puntos_agregados + $yml['parameters']['puntos']['influencer_3'];
                        //return new response(var_dump($puntos_agregados));
                    }

                }
            //return new response($comentarios.'   '.$pagina_id);
            }
            $programa = '';
            $estructura = $f->obtenerEstructura($pagina_padre_id, $yml);
            $ids = 1;
            
            //return new response($usuario->getId());

            $query = $em->createQuery('SELECT COUNT(cm.id) FROM LinkComunBundle:CertiMuro cm 
                                    WHERE cm.usuario = :usuario_id
                                    AND cm.pagina IN (:programa)')
                        ->setParameters(array('usuario_id' => $usuario->getId(),
                                              'programa' => $estructura));
            $comentariosTotal2 = $query->getSingleScalarResult();
            

            $comentarios = $comentariosTotal2 / 5;
            
            if( is_int($comentarios))
            {   
                
                if($comentariosTotal2 == 5)
                {
                    $medallaUsuario = $em->getRepository('LinkComunBundle:AdminMedallasUsuario')->findOneBy(array('pagina' => $pagina_padre->getId(),
                                                                                                                  'usuario' => $usuario->getId(),
                                                                                                                  'medalla' => $yml['parameters']['medallas']['amigable_1']));
                    if(!$medallaUsuario)
                    {
                        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($yml['parameters']['medallas']['amigable_1']);
                        
                        $medallaUsuario = new AdminMedallasUsuario();
                        $medallaUsuario->setUsuario($usuario);
                        $medallaUsuario->setMedalla($medalla);
                        $medallaUsuario->setPagina($pagina_padre);
                        $em->persist($medallaUsuario);
                        $em->flush();

                        $puntos_agregados = $puntos_agregados + $yml['parameters']['puntos']['amigable_1'];
                        //return new response(var_dump($puntos_agregados));
                        
                    }
                }
                elseif($comentariosTotal2 == 10)
                {
                    $medallaUsuario = $em->getRepository('LinkComunBundle:AdminMedallasUsuario')->findOneBy(array('pagina' => $pagina_padre->getId(),
                                                                                                                  'usuario' => $usuario->getId(),
                                                                                                                  'medalla' => $yml['parameters']['medallas']['amigable_2']));
                    if(!$medallaUsuario)
                    {
                        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($yml['parameters']['medallas']['amigable_2']);
                        
                        $medallaUsuario = new AdminMedallasUsuario();
                        $medallaUsuario->setUsuario($usuario);
                        $medallaUsuario->setMedalla($medalla);
                        $medallaUsuario->setPagina($pagina_padre);
                        $em->persist($medallaUsuario);
                        $em->flush();

                        $puntos_agregados = $puntos_agregados + $yml['parameters']['puntos']['amigable_2'];
                        //return new response(var_dump($puntos_agregados));
                        
                    }
                }
                elseif($comentariosTotal2 == 15)
                {
                    
                    $medallaUsuario = $em->getRepository('LinkComunBundle:AdminMedallasUsuario')->findOneBy(array('pagina' => $pagina_padre->getId(),
                                                                                                                  'usuario' => $usuario->getId(),
                                                                                                                  'medalla' => $yml['parameters']['medallas']['amigable_3']));
                    if(!$medallaUsuario)
                    {
                        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($yml['parameters']['medallas']['amigable_3']);
                        
                        $medallaUsuario = new AdminMedallasUsuario();
                        $medallaUsuario->setUsuario($usuario);
                        $medallaUsuario->setMedalla($medalla);
                        $medallaUsuario->setPagina($pagina_padre);
                        $em->persist($medallaUsuario);
                        $em->flush();

                        $puntos_agregados = $puntos_agregados + $yml['parameters']['puntos']['amigable_3'];
                        //return new response(var_dump($puntos_agregados));
                    }

                }
            //return new response($comentarios.'   '.$pagina_id);
            }
            

            /////////// Enviar notificacion al tutor o tutores de actividad en el muro ///////////
            $background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
            $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion_smart.png';
            $link_plataforma = $this->container->getParameter('link_plataforma').$empresa->getId();
            $categoria = $f->obtenerProgramaCurso($pagina);
            $tutores = $f->getTutoresEmpresa($empresa->getId(), $yml);
            $sendMails = $f->sendMailNotificationsMuro($tutores, $yml, $muro, $categoria, $tipoMensaje, $background, $logo, $link_plataforma);

            $puntos = $pagina_log->getPuntos() + $puntos_agregados;
            $pagina_log->setPuntos($puntos);
            $em->persist($pagina_log);
            $em->flush();

            if ($correo && $muro_id)
            {

                // Nuevo registro en la tabla de admin_correo
                $tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['muro']);
                $email = new AdminCorreo();
                $email->setTipoCorreo($tipo_correo);
                $email->setEntidadId($muro_padre->getId());
                $email->setUsuario($muro_padre->getUsuario());
                $email->setCorreo($correo_tutor);
                $email->setFecha(new \DateTime('now'));
                $em->persist($email);
                $em->flush();
                    
                //crea la notificacion para el usuario cuando el usuario que publica
                $descripcion = $f->tipoDescripcion($tipoMensaje, $muro, $muro_padre->getUsuario()->getNombre().' '.$muro_padre->getUsuario()->getApellido());
                $tipoAlarma = ($tipoMensaje=='Respondió') ? 'respuesta_muro' : 'aporte_muro';
                $f->newAlarm($yml['parameters']['tipo_alarma'][$tipoAlarma], $descripcion, $muro_padre->getUsuario(), $muro->getId());

            }

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
                        <a href="#" class="mr-0 text-sm color-light-grey " data="'.$muro->getId().'">
                        </a>';
            if (!$muro_id)
            {
                $html .= '<a href="#" class="links text-right reply_comment" id="href_reply_'.$muro->getId().'" data="'.$muro->getId().'">'.$this->get('translator')->trans('Responder').'</a>';
            }
            $html .= '</div>';
            if (!$muro_id)
            {
                $html .= '<div id="'.$prefix.'_div-response-'.$muro->getId().'"></div>
                          <div id="'.$prefix.'_respuestas-'.$muro->getId().'"></div>';
                //alarma para participantes al momento de que un tutor haga una entrada en el muro

                if (($is_tutor == 1) and ($yml['parameters']['muro']['alarma_participantes'] !=0 )) {
                    $descripcion = $f->tipoDescripcion($tipoMensaje, $muro, $muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido());
                    $fecha = new \DateTime('now');
                    $fecha = $fecha->format('Y-m-d H:i:s');
                    $grupo = $f->obtenerProgramaCurso($muro->getPagina());
                    $alarmaGrupo = $f->alarmasGrupo($yml['parameters']['tipo_alarma']['aporte_muro'],$descripcion,$muro->getId(),$fecha,$grupo['programa_id'],$muro->getUsuario()->getEmpresa()->getId(),$muro->getUsuario()->getId());


                }
            }
            $html .= '</div>';
            $total_comentarios = $em->getRepository('LinkComunBundle:CertiMuro')->findBy(array('pagina'=>$pagina_id,'empresa'=>$session->get('empresa')['id']));
            $total_comentarios = count($total_comentarios);
            $return = array('html' => $html,
                            'muro_id' => $muro->getId(),
                            'puntos_agregados' => $puntos_agregados,
                            'comentarios'=>$total_comentarios);

            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        catch(\Exception $ex){
            $return = json_encode(array('mensaje'=>$ex->getMessage().' - '.$ex->getFile().'  '.$ex->getLine()));
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

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
        $html_radar = '<div class="radar-response" style="display:none" id="radar-comment-'.$muro_id.'">
                                <div class="contenedor" >
                                    <div class="radar"></div>
                                </div>
                        </div>';

        $html = '<div class="response" id="'.$prefix.'_response-'.$muro_id.'" >
                    <img class="img-fluid avatar-img" src="'.$img_user.'" alt="">
                    <form class="ml-3 w-100" method="POST" >
                        <div class="form-group d-inline-block w-100">
                            <textarea class="form-control w-100" id="'.$prefix.'_respuesta_'.$muro_id.'" name="'.$prefix.'_respuesta_'.$muro_id.'" rows="5" maxlength="1000" placeholder="'.$this->get('translator')->trans('Escriba su respuesta').'"></textarea>
                        </div>
                        <button type="button" name="button" style="" class="btn btn-sm btn-primary float-right button-reply" data="'.$muro_id.'" id="'.$prefix.'_button-reply-'.$muro_id.'">'.$this->get('translator')->trans('Responder').'</button>
                    </form>';
        $html .= $html_radar;
        $html .= '</div>';

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
                          <a href="#" class="btn btn-primary btn-sm  text-center mx-auto more_answers" data="'.$muro_id.'">'.$this->get('translator')->trans('Ver más respuestas').'</a>';
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
                          <a href="#" class="btn btn-primary btn-sm  text-center mx-auto more_comments" data="'.$pagina_id.'">'.$this->get('translator')->trans('Ver más comentarios').'</a>';
            }

        }
        
        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function menuAction($programa_id, $subpagina_id, $active)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $em = $this->getDoctrine()->getManager();

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);
        //return new Response(var_dump($indexedPages));

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

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($indexedPages, $session->get('paginas')[$programa_id], $subpagina_id, $this->generateUrl('_lecciones', array('programa_id' => $programa_id)), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);

        return $this->render('LinkFrontendBundle:Leccion:menu.html.twig', array('programa' => $programa,
                                                                                'subpagina_id' => $subpagina_id,
                                                                                'menu_str' => $menu_str,
                                                                                'espacio_colaborativo' => $espacio_colaborativo,
                                                                                'active' => $active));

    }

}
