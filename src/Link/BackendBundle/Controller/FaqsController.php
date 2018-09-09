<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminFaqs;
use Link\ComunBundle\Entity\AdminTipoPregunta;


class FaqsController extends Controller
{
   public function indexAction($app_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $faqs = array();
        $query = $em->createQuery("SELECT f FROM LinkComunBundle:AdminFaqs f 
                                    ORDER BY f.tipoPregunta ASC");
        $faqsdb = $query->getResult();

        foreach ($faqsdb as $faq)
        {
            $faqs[] = $faq;                     
        }

        $tipos = array();
        $query = $em->createQuery("SELECT tp FROM LinkComunBundle:AdminTipoPregunta tp 
                                    ORDER BY tp.nombre ASC");
        $tps = $query->getResult();

        foreach ($tps as $tp)
        {
            $tipos[] = $tp;                     
        }

        return $this->render('LinkBackendBundle:Faqs:index.html.twig', array('faqs' => $faqs,
                                                                             'tipos' => $tipos));
        
    }

    public function ajaxUpdateFaqsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $faq_id = $request->request->get('faq_id');
        $tipo_pregunta_id = $request->request->get('tipo_pregunta_id');
        $pregunta = $request->request->get('pregunta');
        $respuesta = $request->request->get('respuesta');

        if ($faq_id)
        {
            $faq = $em->getRepository('LinkComunBundle:AdminFaqs')->find($faq_id);
        }
        else {
            $faq = new AdminFaqs();
        }

        $tipo_pregunta = $em->getRepository('LinkComunBundle:AdminTipoPregunta')->find($tipo_pregunta_id);

        $faq->setTipoPregunta($tipo_pregunta);
        $faq->setPregunta($pregunta);
        $faq->setRespuesta($respuesta);
        $em->persist($faq);
        $em->flush();
                    
        $return = array('id' => $faq->getId(),
                        'pregunta' => $faq->getPregunta(),
                        'respuesta' => $faq->getRespuesta(),
                        'tipo_pregunta' => $faq->getTipoPregunta()->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxRespuestaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $faq_id = $request->query->get('faq_id');
        
        $faq = $this->getDoctrine()->getRepository('LinkComunBundle:AdminFaqs')->find($faq_id);

        $return = array('respuesta' => $faq->getRespuesta());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }


    public function ajaxEditFaqsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $faq_id = $request->query->get('faq_id');
        
        $faq = $this->getDoctrine()->getRepository('LinkComunBundle:AdminFaqs')->find($faq_id);

        $return = array('faq_id' => $faq->getId(),
                        'tipo_pregunta_id' => $faq->getTipoPregunta()->getId(),
                        'pregunta' => $faq->getPregunta(),
                        'respuesta' => $faq->getRespuesta());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxNuevaPreguntaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $new_tipo_pregunta = $request->request->get('new_tipo_pregunta');
        $ok = 0;
        $id = 0;

        $tp = $em->getRepository('LinkComunBundle:AdminTipoPregunta')->findOneByNombre($new_tipo_pregunta);

        if (!$tp)
        {
            $tipo_pregunta = new AdminTipoPregunta();
            $tipo_pregunta->setNombre($new_tipo_pregunta);
            $em->persist($tipo_pregunta);
            $em->flush();
            $ok = 1;
            $id = $tipo_pregunta->getId();
        }
        else {
            $id = $tp->getId();
        }
    
        $return = array('ok' => $ok,
                        'id' => $id);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

}