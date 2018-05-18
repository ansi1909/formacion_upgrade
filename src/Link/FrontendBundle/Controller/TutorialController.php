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
        $tutoriales=[
                     ['id'=>1,'nombre'=>'Como navegar por la plataforma','fecha'=>'18/05/2018'],
                     ['id'=>2,'nombre'=>'Como utilizar los faqs','fecha'=>'17/05/2018'],
                     ['id'=>3,'nombre'=>'Como editar su perfil de usuario','fecha'=>'16/05/2018'],
                     ['id'=>4,'nombre'=>'Como se usan las notificaciones del sistema','fecha'=>'15/05/2018'],
                     ['id'=>5,'nombre'=>'Como navegar por la plataforma','fecha'=>'18/05/2018'],
                     ['id'=>6,'nombre'=>'Como utilizar los faqs','fecha'=>'17/05/2018'],
                     ['id'=>7,'nombre'=>'Como editar su perfil de usuario','fecha'=>'16/05/2018'],
                     ['id'=>8,'nombre'=>'Como se usan las notificaciones del sistema','fecha'=>'15/05/2018']
                    ];
        
        return $this->render('LinkFrontendBundle:Tutoriales:indexTutorial.html.twig',['tutoriales'=>$tutoriales]);
    }

}