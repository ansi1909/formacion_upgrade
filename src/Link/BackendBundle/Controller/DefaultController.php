<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {

    	/*$session = new Session();
      	if (!$session->get('ini'))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}

      	if ($this->container->get('session')->isStarted())
      	{
        	
        	// Lógica para mostrar el dashboard del backend
      	}
      	else {
      		return $this->redirectToRoute('_loginAdmin');
      	}*/

        return $this->render('LinkBackendBundle:Default:index.html.twig');

    }
}
