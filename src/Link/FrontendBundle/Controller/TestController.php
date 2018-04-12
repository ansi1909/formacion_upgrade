<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPruebaLog;
use Link\ComunBundle\Entity\CertiRespuesta;
use Symfony\Component\Yaml\Yaml;

class TestController extends Controller
{
    public function indexAction($pagina_id, $programa_id, Request $request)
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

        $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina_id);

        // Duración en segundos
        $duracion = intval($prueba->getDuracion()->format('G'))*3600;
        $duracion += intval($prueba->getDuracion()->format('i'))*60;
        $duracion += intval($prueba->getDuracion()->format('s'));

        if (!$prueba)
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'prueba'));
        }

        $status_pagina = $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($yml['parameters']['estatus_pagina']['en_evaluacion']);
        $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                            'pagina' => $pagina_id));
        $pagina_log->setEstatusPagina($status_pagina);
        $em->persist($pagina_log);
        $em->flush();

        $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->findOneBy(array('prueba' => $prueba->getId(),
                                                                                            'usuario' => $session->get('usuario')['id'],
                                                                                            'estado' => $yml['parameters']['estado_prueba']['curso']),
                                                                                      array('id' => 'DESC'));
        
        if (!$prueba_log)
        {

            // Es posible que ya haya aprobado esta evaluación
            $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->findOneBy(array('prueba' => $prueba->getId(),
                                                                                                'usuario' => $session->get('usuario')['id'],
                                                                                                'estado' => $yml['parameters']['estado_prueba']['aprobado']),
                                                                                          array('id' => 'DESC'));
            if ($prueba_log)
            {
                return $this->redirectToRoute('_resultadosTest', array('programa_id' => $programa_id, 'prueba_log_id' => $prueba_log->getId(), 'puntos' => 0));
            }
            else {
                $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
                $prueba_log = new CertiPruebaLog();
                $prueba_log->setPrueba($prueba);
                $prueba_log->setUsuario($usuario);
                $prueba_log->setFechaInicio(new \DateTime('now'));
                $prueba_log->setEstado($yml['parameters']['estado_prueba']['curso']);
            }
        }

        // Reseteo de valores
        $prueba_log->setPorcentajeAvance(0);
        $prueba_log->setCorrectas(0);
        $prueba_log->setErradas(0);
        $prueba_log->setNota(0);
        $prueba_log->setPreguntasErradas(null);
        $em->persist($prueba_log);
        $em->flush();

        // Eliminamos las respuestas de esta prueba_log
        $respuestas = $em->getRepository('LinkComunBundle:CertiRespuesta')->findByPruebaLog($prueba_log->getId());
        foreach ($respuestas as $respuesta)
        {
            $em->remove($respuesta);
            $em->flush();
        }

        // Preguntas
        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPregunta p 
                                    WHERE p.prueba = :prueba_id 
                                    AND p.estatusContenido = :activo 
                                    AND p.pregunta IS NULL")
                    ->setParameters(array('prueba_id' => $prueba->getId(),
                                          'activo' => $yml['parameters']['estatus_contenido']['activo']));
        $preguntas = $query->getResult();

        $preguntas_ids = array();
        foreach ($preguntas as $pregunta)
        {
            $preguntas_ids[] = $pregunta->getId();
        }
        shuffle($preguntas_ids);

        // Estilos de bordes para las opciones asociadas
        $bordes[] = '#CFE65C';
        $bordes[] = '#007bff';
        $bordes[] = '#fd7e14';
        $bordes[] = '#6610f2';
        $bordes[] = '#dc3545';
        $bordes[] = '#ffc107';
        $bordes[] = '#343a40';
        $bordes[] = '#e83e8c';
        $bordes[] = '#28a745';
        $bordes[] = '#20c997';

        $preguntas = array();
        //for ($i=0; $i<count($preguntas_ids); $i++)
        for ($i=0; $i<$prueba->getCantidadMostrar(); $i++)
        {

            $pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($preguntas_ids[$i]);
            $opciones = array();

            if ($pregunta->getTipoPregunta()->getId() != $yml['parameters']['tipo_pregunta']['asociacion'])
            {

                $query = $em->createQuery("SELECT po FROM LinkComunBundle:CertiPreguntaOpcion po 
                                            JOIN po.opcion o 
                                            WHERE po.pregunta = :pregunta_id AND o.prueba = :prueba_id 
                                            ORDER BY o.id ASC")
                            ->setParameters(array('pregunta_id' => $pregunta->getId(),
                                                  'prueba_id' => $pregunta->getPrueba()->getId()));
                $opciones_bd = $query->getResult();

                foreach ($opciones_bd as $po)
                {
                    $opciones[] = array('po_id' => $po->getId(),
                                        'descripcion' => $po->getOpcion()->getDescripcion(),
                                        'imagen' => $po->getOpcion()->getImagen());
                }

            }
            else {
                
                $asociacion = $em->getRepository('LinkComunBundle:CertiPreguntaAsociacion')->findOneByPregunta($pregunta->getId());

                if ($asociacion)
                {

                    $preguntas_asociadas = explode(",", $asociacion->getPreguntas());
                    $opciones_asociadas = explode(",", $asociacion->getOpciones());
                    shuffle($preguntas_asociadas);
                    shuffle($opciones_asociadas);

                    for ($a=0; $a<count($preguntas_asociadas); $a++)
                    {

                        $pregunta_asociada = $em->getRepository('LinkComunBundle:CertiPregunta')->find($preguntas_asociadas[$a]);
                        $opcion_asociada = $em->getRepository('LinkComunBundle:CertiOpcion')->find($opciones_asociadas[$a]);

                        $opciones[] = array('pregunta_asociada_id' => $pregunta_asociada->getId(),
                                            'enunciado' => $pregunta_asociada->getEnunciado(),
                                            'pregunta_asociada_imagen' => $pregunta_asociada->getImagen(),
                                            'borde_color' => $bordes[$a],
                                            'opcion_asociada_id' => $opcion_asociada->getId(),
                                            'descripcion' => $opcion_asociada->getDescripcion(),
                                            'opcion_asociada_imagen' => $opcion_asociada->getImagen());

                    }

                }

            }

            $preguntas_arr[] = $pregunta->getId();

            $preguntas[] = array('id' => $pregunta->getId(),
                                 'enunciado' => $pregunta->getEnunciado(),
                                 'imagen' => $pregunta->getImagen(),
                                 'tipo_pregunta' => $pregunta->getTipoPregunta()->getId(),
                                 'tipo_elemento' => $pregunta->getTipoElemento()->getId(),
                                 'opciones' => $opciones);

        }

        //return new Response(var_dump($preguntas));
        $preguntas_str = implode(",", $preguntas_arr);

        if (!count($preguntas))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'pregunta'));
        }

        return $this->render('LinkFrontendBundle:Test:index.html.twig', array('prueba_log' => $prueba_log,
                                                                              'preguntas' => $preguntas,
                                                                              'preguntas_str' => $preguntas_str,
                                                                              'programa_id' => $programa_id,
                                                                              'tipo_pregunta' => $yml['parameters']['tipo_pregunta'],
                                                                              'tipo_elemento' => $yml['parameters']['tipo_elemento'],
                                                                              'duracion' => $duracion));

    }

    public function ajaxTestResponseAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $ok = 1;

        $prueba_log_id = $request->request->get('prueba_log_id');
        $pregunta_id = $request->request->get('pregunta_id');
        $nro = $request->request->get('nro');
        $porcentaje = $request->request->get('porcentaje');
        $p_opciones = $request->request->get('pregunta_id'.$pregunta_id);

        $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->find($prueba_log_id);
        $pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($pregunta_id);

        $correcta = 1;

        if ($pregunta->getTipoPregunta()->getId() != $yml['parameters']['tipo_pregunta']['asociacion'])
        {

            // Simples o múltiples
            $p_opciones = trim($p_opciones); // Viene separado por comas
            if ($p_opciones != '')
            {
                $p_opciones_arr = explode(",", $p_opciones);
            }
            else {
                $p_opciones_arr = array();
            }

            if (!count($p_opciones_arr))
            {
                // No contestó. Se cuenta como errada.
                $correcta = 0;
                $respuesta = $em->getRepository('LinkComunBundle:CertiRespuesta')->findOneBy(array('pruebaLog' => $prueba_log_id,
                                                                                                   'nro' => $nro,
                                                                                                   'pregunta' => $pregunta_id));
                if (!$respuesta)
                {
                    $respuesta = new CertiRespuesta();
                    $respuesta->setNro($nro);
                    $respuesta->setPregunta($pregunta);
                    $respuesta->setPruebaLog($prueba_log);
                    $respuesta->setFechaRegistro(new \DateTime('now'));
                }
                $respuesta->setOpcion(null);
                $em->persist($respuesta);
                $em->flush();
            }
            else {

                $respuestas = $em->getRepository('LinkComunBundle:CertiRespuesta')->findBy(array('pruebaLog' => $prueba_log_id,
                                                                                                 'nro' => $nro,
                                                                                                 'pregunta' => $pregunta_id));
                // Borramos primero las respuestas de esta pregunta
                foreach ($respuestas as $respuesta)
                {
                    $em->remove($respuesta);
                    $em->flush();
                }

                // Se guardan las nuevas respuestas
                foreach ($p_opciones_arr as $po_id)
                {
                    $pregunta_opcion = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->find($po_id);
                    if (!$pregunta_opcion->getCorrecta())
                    {
                        $correcta = 0;
                    }
                    $respuesta = new CertiRespuesta();
                    $respuesta->setNro($nro);
                    $respuesta->setPregunta($pregunta);
                    $respuesta->setPruebaLog($prueba_log);
                    $respuesta->setFechaRegistro(new \DateTime('now'));
                    $respuesta->setOpcion($pregunta_opcion->getOpcion());
                    $em->persist($respuesta);
                    $em->flush();
                }
            }

        }
        else {

            $respuestas = $em->getRepository('LinkComunBundle:CertiRespuesta')->findBy(array('pruebaLog' => $prueba_log_id,
                                                                                             'nro' => $nro));
            // Borramos primero las respuestas de esta pregunta
            foreach ($respuestas as $respuesta)
            {
                $em->remove($respuesta);
                $em->flush();
            }

            // Respuestas de asociación
            $correctas_arr = array(); // Forma pregunta_id => opcion_id
            $respuestas_arr = array(); // Forma pregunta_id => opcion_id_seleccionada

            foreach ($p_opciones as $p_opcion)
            {

                $po_arr = explode("_", $p_opcion);
                $respuestas_arr[$po_arr[0]] = $po_arr[1];
                if ($po_arr[1] != 0)
                {
                    $opcion = $em->getRepository('LinkComunBundle:CertiOpcion')->find($po_arr[1]);
                }
                else {
                    $opcion = null;
                }

                $pregunta_opcion = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->findOneByPregunta($po_arr[0]);
                $correctas_arr[$po_arr[0]] = $pregunta_opcion->getOpcion()->getId();

                $respuesta = new CertiRespuesta();
                $respuesta->setNro($nro);
                $respuesta->setPregunta($pregunta_opcion->getPregunta());
                $respuesta->setPruebaLog($prueba_log);
                $respuesta->setFechaRegistro(new \DateTime('now'));
                $respuesta->setOpcion($opcion);
                $em->persist($respuesta);
                $em->flush();

            }

            foreach ($correctas_arr as $p_id => $o_id)
            {
                if ($respuestas_arr[$p_id] != $o_id)
                {
                    $correcta = 0;
                    break;
                }
            }

        }

        $errada = !$correcta ? 1 : 0;
        $erradas_str = $prueba_log->getPreguntasErradas();
        $new_erradas = array();

        if ($erradas_str)
        {
            $erradas_arr = explode(",", $erradas_str);
        }
        else {
            $erradas_arr = array();
        }

        if ($errada){
            if (count($erradas_arr))
            {
                if (!in_array($nro, $erradas_arr))
                {
                    $erradas_arr[] = $nro;
                }
            }
            else {
                $erradas_arr[] = $nro;
            }
            $new_erradas = $erradas_arr;
        }
        else {
            foreach ($erradas_arr as $e)
            {
                if ($e != $nro)
                {
                    $new_erradas[] = $e;
                }
            }
        }

        if (count($new_erradas))
        {
            $erradas_str = implode(",", $new_erradas);
            $prueba_log->setPreguntasErradas($erradas_str);
        }

        $erradas = count($new_erradas);
        $prueba_log->setPorcentajeAvance($porcentaje);
        $prueba_log->setErradas($erradas);
        $em->persist($prueba_log);
        $em->flush();

        $return = array('ok' => $ok);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function finAction($programa_id, $prueba_log_id, $cantidad_preguntas, $preguntas_str, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);

        // También se anexa a la indexación el programa
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

        $em = $this->getDoctrine()->getManager();

        $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->find($prueba_log_id);

        // Cantidad de intentos
        $query = $em->createQuery("SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPruebaLog pl 
                                    WHERE pl.usuario = :usuario_id 
                                    AND pl.prueba = :prueba_id")
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                          'prueba_id' => $prueba_log->getPrueba()->getId()));
        $intentos = $query->getSingleScalarResult();

        $puntos = 0;
        $preguntas_ids = explode(",", $preguntas_str);

        // Verificar si todas las preguntas fueron contestadas
        $query = $em->createQuery("SELECT DISTINCT(r.nro) AS nro FROM LinkComunBundle:CertiRespuesta r 
                                    WHERE r.pruebaLog = :prueba_log_id ORDER BY r.nro ASC")
                    ->setParameter('prueba_log_id', $prueba_log_id);
        $nros = $query->getResult();

        $nros_arr = array();
        foreach ($nros as $nro)
        {
            $nros_arr[] = $nro['nro'];
        }

        if (count($nros) < $cantidad_preguntas)
        {

            $erradas = 0;
            $erradas_str = $prueba_log->getPreguntasErradas();
            if ($erradas_str)
            {
                $erradas_arr = explode(",", $erradas_str);
            }
            else {
                $erradas_arr = array();
            }

            for ($i=1; $i<=count($preguntas_ids); $i++)
            {
                if (!in_array($i, $nros_arr))
                {
                    // No contestó esta pregunta
                    if (isset($preguntas_ids[$i-1]))
                    {
                        $erradas++;
                        $erradas_arr[] = $i;
                        $pregunta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPregunta')->find($preguntas_ids[$i-1]);
                        $respuesta = new CertiRespuesta();
                        $respuesta->setNro($i);
                        $respuesta->setPregunta($pregunta);
                        $respuesta->setPruebaLog($prueba_log);
                        $respuesta->setFechaRegistro(new \DateTime('now'));
                        $em->persist($respuesta);
                        $em->flush();
                    }
                }
            }
            if ($erradas)
            {
                $erradas_str = implode(",", $erradas_arr);
                $total_erradas = $prueba_log->getErradas() + $erradas;
                $prueba_log->setErradas($total_erradas);
                $prueba_log->setPreguntasErradas($erradas_str);
            }
        }

        $correctas = $cantidad_preguntas - $prueba_log->getErradas();
        $prueba_log->setCorrectas($correctas);
        $contestadas = $prueba_log->getErradas() + $prueba_log->getCorrectas();
        
        if ((!$prueba_log->getPreguntasErradas() || $prueba_log->getPreguntasErradas() == '') && ($contestadas == $cantidad_preguntas))
        {
            // Pasó con el 100%. No hace falta cálculos.
            $nota = 100;
            $estado = $yml['parameters']['estado_prueba']['aprobado'];
            $puntos = $yml['parameters']['puntos']['aprobar_perfecto'];
            if ($intentos > 1)
            {
                $puntos = $puntos/$intentos;
                $puntos = round($puntos, 0, PHP_ROUND_HALF_UP);
            }
        }
        else {

            $erradas = explode(",", $prueba_log->getPreguntasErradas());

            // Se determina el total de puntaje que suman todas las preguntas
            $total_valor = 0;
            $nota = 0;
            $preguntas = array();
            for ($i=1; $i<=$cantidad_preguntas; $i++)
            {
                $respuesta = $em->getRepository('LinkComunBundle:CertiRespuesta')->findOneBy(array('pruebaLog' => $prueba_log_id,
                                                                                                   'nro' => $i));
                if ($respuesta->getPregunta()->getPregunta())
                {
                    // Es una respuesta de asociación
                    $pregunta_id = $respuesta->getPregunta()->getPregunta()->getId();
                    $valor = $respuesta->getPregunta()->getPregunta()->getValor();
                }
                else {
                    // Respuesta de selección simple o múltiple
                    $pregunta_id = $respuesta->getPregunta()->getId();
                    $valor = $respuesta->getPregunta()->getValor();
                }
                $preguntas[$i] = array('id' => $pregunta_id,
                                       'valor' => $valor);
                $total_valor += $valor;
            }

            foreach ($preguntas as $nro => $pregunta)
            {
                if (!in_array($nro, $erradas))
                {
                    $porcentaje = ($pregunta['valor']*100)/$total_valor;
                    $nota += $porcentaje;
                }
            }
            $nota = round($nota, 2, PHP_ROUND_HALF_UP);

            $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                        'pagina' => $prueba_log->getPrueba()->getPagina()->getId()));

            if ($nota < $pagina_empresa->getPuntajeAprueba())
            {
                $estado = $yml['parameters']['estado_prueba']['reprobado'];
            }
            else {
                $estado = $yml['parameters']['estado_prueba']['aprobado'];
                if ($intentos == 1)
                {
                    $puntos = $yml['parameters']['puntos']['aprobar_primer_intento'];
                }
            }

        }

        // Finalizar prueba_log
        $prueba_log->setFechaFin(new \DateTime('now'));
        $prueba_log->setPorcentajeAvance(100);
        $prueba_log->setNota($nota);
        $prueba_log->setEstado($estado);
        $em->persist($prueba_log);
        $em->flush();

        if ($estado == $yml['parameters']['estado_prueba']['aprobado'])
        {

            $pagina_id = $prueba_log->getPrueba()->getPagina()->getId();
            // Estatus de la página Completada
            $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                'pagina' => $pagina_id));
            $status_pagina = $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($yml['parameters']['estatus_pagina']['completada']);
            $pagina_log->setFechaFin(new \DateTime('now'));
            $pagina_log->setPorcentajeAvance(100);
            $pagina_log->setEstatusPagina($status_pagina);

            // Si la completó en menos de la mitad del período se gana unos puntos adicionales
            $mitad_periodo = $f->mitadPeriodo($indexedPages[$pagina_id]['inicio'], $indexedPages[$pagina_id]['vencimiento']);
            $inicio_arr = explode("/", $indexedPages[$pagina_id]['inicio']);
            $inicio = $inicio_arr[2].'-'.$inicio_arr[1].'-'.$inicio_arr[0];
            if ($inicio <= $mitad_periodo)
            {
                $puntos_agregados = $yml['parameters']['puntos']['mitad_periodo'];
                $puntos = $puntos + $puntos_agregados;
            }

            $puntos_pagina = $pagina_log->getPuntos() + $puntos;
            $pagina_log->setPuntos($puntos_pagina);
            $em->persist($pagina_log);
            $em->flush();

        }

        // Hacia la página de resultados
        return $this->redirectToRoute('_resultadosTest', array('programa_id' => $programa_id, 'prueba_log_id' => $prueba_log_id, 'puntos' => $puntos));

    }

    public function resultadosAction($programa_id, $prueba_log_id, $puntos, Request $request)
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
        $try_button = 0;

        $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->find($prueba_log_id);

        if (trim($prueba_log->getPreguntasErradas()))
        {
            $erradas = explode(",", $prueba_log->getPreguntasErradas());
        }
        else {
            $erradas = array();
        }

        $query = $em->createQuery("SELECT DISTINCT(r.nro) AS nro FROM LinkComunBundle:CertiRespuesta r 
                                    WHERE r.pruebaLog = :prueba_log_id ORDER BY r.nro ASC")
                    ->setParameter('prueba_log_id', $prueba_log_id);
        $nros = $query->getResult();

        $preguntas = array();
        foreach ($nros as $nro)
        {

            $respuesta = $em->getRepository('LinkComunBundle:CertiRespuesta')->findOneBy(array('pruebaLog' => $prueba_log_id,
                                                                                               'nro' => $nro['nro']));

            if ($respuesta->getPregunta()->getPregunta())
            {
                // Es una respuesta de asociación
                $pregunta = $respuesta->getPregunta()->getPregunta();
            }
            else {
                // Respuesta de selección simple o múltiple
                $pregunta = $respuesta->getPregunta();
            }

            $errada = in_array($nro['nro'], $erradas) ? 1 : 0;

            $preguntas[$nro['nro']] = array('id' => $pregunta->getId(),
                                            'enunciado' => $pregunta->getEnunciado(),
                                            'errada' => $errada);

        }

        if ($prueba_log->getEstado() == $yml['parameters']['estado_prueba']['reprobado'])
        {
            // Cantidad de intentos
            $query = $em->createQuery("SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPruebaLog pl 
                                        WHERE pl.usuario = :usuario_id 
                                        AND pl.prueba = :prueba_id")
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                              'prueba_id' => $prueba_log->getPrueba()->getId()));
            $intentos = $query->getSingleScalarResult();
            $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                        WHERE pe.empresa = :empresa_id 
                                        AND pe.pagina = :pagina_id")
                        ->setParameters(array('empresa_id' => $session->get('empresa')['id'],
                                              'pagina_id' => $prueba_log->getPrueba()->getPagina()->getId()));
            $pe = $query->getResult();
            $max_intentos = $pe[0]->getMaxIntentos();
            if ($intentos < $max_intentos)
            {
                $try_button = 1;
            }
        }

        return $this->render('LinkFrontendBundle:Test:resultados.html.twig', array('prueba_log' => $prueba_log,
                                                                                   'preguntas' => $preguntas,
                                                                                   'programa_id' => $programa_id,
                                                                                   'try_button' => $try_button,
                                                                                   'estados' => $yml['parameters']['estado_prueba'],
                                                                                   'puntos' => $puntos));

    }

}
