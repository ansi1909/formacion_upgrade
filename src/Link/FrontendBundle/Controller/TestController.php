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
    public function indexAction($pagina_id, Request $request)
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
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('No existe evaluación para esta página.')));
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
            $prueba_log->setPrueba($usuario);
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
        $preguntas = $em->getRepository('LinkComunBundle:CertiPregunta')->findBy(array('prueba' => $prueba->getId(),
                                                                                       'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']));

        $preguntas_ids = array();
        foreach ($preguntas as $pregunta)
        {
            $preguntas_ids[] = $pregunta->getId();
        }
        shuffle($preguntas_ids);

        $preguntas = array();
        for ($i=0; $i<$prueba->getCantidadMostrar(); $i++)
        {

            $pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($preguntas_ids[$i]);

            // Vienen las opciones

        }

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

}
