<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Yaml\Yaml;


class TutorialController extends Controller
{

    

    public function indexAction()
    {
       
       $session = new Session();
       $f = $this->get('funciones');
       $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
       $directorioTut=$yml['parameters']['folders']['uploads'].'recursos/tutoriales/';

       if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
       
       
       $em = $this->getDoctrine()->getManager();
       $query= $em->createQuery('SELECT t FROM LinkComunBundle:AdminTutorial t
                                                ORDER BY t.id DESC');
       $tutoriales = $query->getResult();
        
       return $this->render('LinkFrontendBundle:Tutoriales:indexTutorial.html.twig',['tutoriales'=>$tutoriales,'directorio'=>$directorioTut]);
    }


    public function detalleAction($tutorial_id)
    {

        $tutorial=$this->getDoctrine()->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $directorioTut=$yml['parameters']['folders']['uploads'].'recursos/tutoriales/';

        
       return $this->render('LinkFrontendBundle:Tutoriales:detalleTutorial.html.twig',['tutorial'=>$tutorial,'directorio'=>$directorioTut]);
    }


    public function _descargarPdfAction($tutorial_id)
    {
       
        $tutorial=$this->getDoctrine()->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $ruta=$yml['parameters']['folders']['dir_uploads'].'recursos/tutoriales/'.$tutorial->getId().'/'.$tutorial->getPdf();
        $archivo=$tutorial->getPdf();


        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename='.$archivo);
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($ruta));
        
        readfile($ruta); 
        /////Queda pendiente mejorarlo realizando la descarga con symfony////////////

    }

}