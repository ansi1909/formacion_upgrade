<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminFaqs;
use Link\ComunBundle\Entity\AdminTipoPregunta;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class FaqsController extends Controller
{
   public function indexAction($app_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini'))
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

        $faqsdb = array();
        $faqs = $em->getRepository('LinkComunBundle:AdminFaqs')->findAll();

        foreach ($faqs as $faq)
        {

            $faqsdb[] = array('id' => $faq->getId(),
                              'tipopregunta' => $faq->getTipoPregunta()->getNombre(),
                              'pregunta' => $faq->getPregunta(),
                              'respuesta' => $faq->getRespuesta());
                              
        }

        $tipo_pregunta = array();
        $tipo_pregunta = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoPregunta')->findAll();

        return $this->render('LinkBackendBundle:Faqs:index.html.twig', array('faqs' => $faqsdb,
                                                                             'tipos' => $tipo_pregunta));
    }

    public function ajaxUpdateFaqsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $faq_id = $request->request->get('faq_id');
        $pregunta = $request->request->get('pregunta');
        $respuesta = $request->request->get('respuesta');
        $tipo_pregunta_id = $request->request->get('tipo_pregunta_id');

        if ($faq_id)
        {
            $faq = $em->getRepository('LinkComunBundle:AdminFaqs')->find($faq_id);
        }
        else {
            $faq = new AdminFaqs();
        }

        $tipo_pregunta = $em->getRepository('LinkComunBundle:AdminTipoPregunta')->find($tipo_pregunta_id);

        $faq->setPregunta($pregunta);
        $faq->setRespuesta($respuesta);
        $faq->setTipoPregunta($tipo_pregunta);

        $em->persist($faq);
        $em->flush();
                    
        $return = array('id' => $faq->getId(),
                        'pregunta' =>$faq->getPregunta(),
                        'respuesta' =>$faq->getRespuesta(),
                        'tipo_pregunta' =>$faq->getTipoPregunta()->getnombre(),
                        'delete_disabled' =>$f->linkEliminar($faq->getId(),'AdminFaqs'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxRespuesta(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $faq_id = $request->query->get('faq_id');
        $respuesta = array();
        
        $respuesta = $this->getDoctrine()->getRepository('LinkComunBundle:AdminFaqs')->find($faq_id);


        $return = array('respuesta' => $respuesta->getrespuesta());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}