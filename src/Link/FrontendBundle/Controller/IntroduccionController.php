<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\AdminLike;
use Link\ComunBundle\Entity\CertiMuro;
use Link\ComunBundle\Entity\AdminIntroduccion;
use Symfony\Component\HttpFoundation\Cookie;

class IntroduccionController extends Controller
{
  
  public function indexAction(Request $request)
  {
      
  }

  public function cambiarEstadoAction(Request $request)
  {
    
    $session = new Session();
    $user_id = $session->get('usuario')['id'];

    $em = $this->getDoctrine()->getManager();

    $siguiente_paso = $request->request->get('nextStep');
    $introduccion_cancelada = $request->request->get('cancel');

    
    $intro_del_usuario = $em->getRepository('LinkComunBundle:AdminIntroduccion')->findByUsuario(array('id' => $user_id));
    
    $intro_del_usuario[0]->setCancelado($introduccion_cancelada);
    $intro_del_usuario[0]->setPasoActual($siguiente_paso);

    $em->persist($intro_del_usuario[0]);
    $em->flush();

    
    $return = array('user_id' => $intro_del_usuario[0]->getCancelado());
    $return = json_encode($return);
    return new Response($return, 200, array('Content-Type' => 'application/json'));
  }

    
}