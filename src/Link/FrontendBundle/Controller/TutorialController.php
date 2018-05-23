<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;


class TutorialController extends Controller
{

    

    public function indexAction(Request $request)
    {
       
       $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
       $directorioTut=$yml['parameters']['folders']['uploads'].'recursos/tutoriales/';

       $tutoriales = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTutorial')->findAll();
        
       return $this->render('LinkFrontendBundle:Tutoriales:indexTutorial.html.twig',['tutoriales'=>$tutoriales,'directorio'=>$directorioTut]);
    }


    public function detalleAction(Request $request, $tutorial_id)
    {

        $tutorial=$this->getDoctrine()->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $directorioTut=$yml['parameters']['folders']['uploads'].'recursos/tutoriales/';
        //$fecha=$tutorial->getFecha()->format('y-m-d');

        
       return $this->render('LinkFrontendBundle:Tutoriales:detalleTutorial.html.twig',['tutorial'=>$tutorial,'directorio'=>$directorioTut]);
    }


    public function _descargarPdfAction(Request $request,$tutorial_id)
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