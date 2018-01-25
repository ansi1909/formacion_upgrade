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

    public function ajaxUpdateTutorialAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $tutorial_id = $request->request->get('tutorial_id');
        $nombre = $request->request->get('nombre');
        $pdf = $request->request->get('pdf');
        $video = $request->request->get('video');

        if ($tutorial_id)
        {
            $tutorial = $em->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);
        }
        else {
            $tutorial = new AdminTutorial();
        }

        $tutorial->setNombre($nombre);
        $tutorial->setPdf($pdf);
        $tutorial->setVideo($video);
        
        $em->persist($tutorial);
        $em->flush();
                    
        $return = array('id' => $tutorial->getId(),
                        'nombre' =>$tutorial->getNombre(),
                        'pdf' =>$tutorial->getPdf(),
                        'video' =>$tutorial->getVideo(),
                        'delete_disabled' =>$f->linkEliminar($tutorial->getId(),'AdminTutorial'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditTutorialAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $tutorial_id = $request->query->get('tutorial_id');
                
        $tutorial = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);

        $return = array('nombre' => $tutorial->getNombre(),
                        'pdf' => $tutorial->getPdf(),
                        'video' => $tutorial->getVideo());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
