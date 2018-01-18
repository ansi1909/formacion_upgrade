<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminRol; 
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
        
        // Todas las aplicaciones principales
        $query = $em->createQuery("SELECT f FROM LinkComunBundle:AdminFaqs f
                                    ORDER BY f.pregunta ASC");
        $faqs = $query->getResult();

        foreach ($faqs as $faq)
        {

            $faqsdb[] = array('id' => $faq->getId(),
                              'pregunta' => $faq->getPregunta(),
                              'respuesta' => $faq->getRespuesta());
        }

        $tipo_pregunta = array();
        $tipo_pregunta = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoPregunta')->findAll();

        return $this->render('LinkBackendBundle:Faqs:index.html.twig', array('faqs' => $faqsdb,
                                                                             'tipos' => $tipo_pregunta));

    }

}
