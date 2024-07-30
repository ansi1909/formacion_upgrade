<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Validator\Constraints\DateTime;

class FaqsController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $usuario_id = $session->get('usuario')['id'];
        $faqs = array();

        $query = $em->createQuery('SELECT tp FROM LinkComunBundle:AdminTipoPregunta tp');
                    
        $tipos = $query->getResult();

        foreach($tipos as $tipo){

            $preguntas = array();

            $query = $em->createQuery('SELECT f FROM LinkComunBundle:AdminFaqs f
                                       WHERE f.tipoPregunta = :tipo_id')
                        ->setParameter('tipo_id', $tipo->getId());
            $faqs_db = $query->getResult();
            $numero=0;

            foreach($faqs_db as $faq){
                $numero++;
                $preguntas[] = array('id_pregunta'=> $faq->getId(),
                                     'pregunta'=> $faq->getPregunta(),
                                     'respuesta'=> $faq->getRespuesta(),
                                     'numero'=> $numero);
            }

            $faqs[] = array('id_categoria'=> $tipo->getId(),
                            'nombre_categoria'=>$tipo->getNombre(),
                            'preguntas'=>$preguntas);
        }

        return $this->render('LinkFrontendBundle:Faqs:index.html.twig', array('usuario_id' => $usuario_id,
                                                                              'faqs' => $faqs));

    }
}