<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPruebaLog;
use Symfony\Component\Yaml\Yaml;

class TestController extends Controller
{
    public function indexAction($pagina_id, $programa_id, Request $request)
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

        $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina_id);

        /*if (!$prueba)
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('No existe evaluación para esta página').'.'));
        }*/

        $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->findOneBy(array('prueba' => $prueba->getId(),
                                                                                            'usuario' => $session->get('usuario')['id'],
                                                                                            'estado' => $yml['parameters']['estado_prueba']['curso']),
                                                                                      array('id' => 'DESC'));
        
        if (!$prueba_log)
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            $prueba_log = new CertiPruebaLog();
            $prueba_log->setPrueba($prueba);
            $prueba_log->setUsuario($usuario);
            $prueba_log->setFechaInicio(new \DateTime('now'));
            $prueba_log->setEstado($yml['parameters']['estado_prueba']['curso']);
        }

        // Reseteo de valores
        $prueba_log->setPorcentajeAvance(0);
        $prueba_log->setCorrectas(0);
        $prueba_log->setErradas(0);
        $prueba_log->setNota(0);
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
                                    AND p.tipoPregunta = :tipo 
                                    AND p.pregunta IS NULL")
                    ->setParameters(array('prueba_id' => $prueba->getId(),
                                          'activo' => $yml['parameters']['estatus_contenido']['activo'],
                                          'tipo' => $yml['parameters']['tipo_pregunta']['asociacion']));
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
        //for ($i=0; $i<$prueba->getCantidadMostrar(); $i++)
        for ($i=0; $i<count($preguntas_ids); $i++)
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

            $preguntas[] = array('id' => $pregunta->getId(),
                                 'enunciado' => $pregunta->getEnunciado(),
                                 'imagen' => $pregunta->getImagen(),
                                 'tipo_pregunta' => $pregunta->getTipoPregunta()->getId(),
                                 'tipo_elemento' => $pregunta->getTipoElemento()->getId(),
                                 'opciones' => $opciones);

        }

        //return new Response(var_dump($preguntas));

        /*if (!count($preguntas))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Esta evaluación no tiene preguntas configuradas').'.'));
        }*/

        return $this->render('LinkFrontendBundle:Test:index.html.twig', array('prueba_log' => $prueba_log,
                                                                              'preguntas' => $preguntas,
                                                                              'programa_id' => $programa_id,
                                                                              'tipo_pregunta' => $yml['parameters']['tipo_pregunta'],
                                                                              'tipo_elemento' => $yml['parameters']['tipo_elemento']));

    }

}
