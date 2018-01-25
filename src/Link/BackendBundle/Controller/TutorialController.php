<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminTutorial; 


class TutorialController extends Controller
{
   public function indexAction($app_id)
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

        $tutorialdb= array();

        $query= $em->createQuery('SELECT t FROM LinkComunBundle:AdminTutorial t
                                        ORDER BY t.nombre ASC');
        $tutoriales=$query->getResult();
        
       //return new Response(var_dump($roles));
        
        foreach ($tutoriales as $tutorial)
        {
            $tutorialdb[]= array('id'=>$tutorial->getId(),
                              'nombre'=>$tutorial->getNombre(),
                              'pdf'=>$tutorial->getPdf(),
                              'video'=>$tutorial->getVideo(),
                              'delete_disabled'=>$f->linkEliminar($tutorial->getId(),'AdminTutorial'));

        }

       return $this->render('LinkBackendBundle:Tutorial:index.html.twig', array('tutoriales'=>$tutorialdb) );

    }

}
