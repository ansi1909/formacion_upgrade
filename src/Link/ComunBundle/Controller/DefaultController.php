<?php

namespace Link\ComunBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LinkComunBundle:Default:index.html.twig');
    }

    public function logoutAction($ruta)
    {

    	$session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($session->get('sesion_id'));
        if ($sesion)
        {
            $sesion->setDisponible(false);
            $em->persist($sesion);
            $em->flush();
        }

        $f->setRequest($session->get('sesion_id'));
        $session->invalidate();
        $session->clear();

        return $this->redirectToRoute($ruta);

    }
}
