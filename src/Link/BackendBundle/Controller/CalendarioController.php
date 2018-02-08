<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminEmpresa;
use Link\ComunBundle\Entity\AdminUsuario;
use Link\ComunBundle\Entity\AdminEvento;


class CalendarioController extends Controller
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

        //$em = $this->getDoctrine()->getManager();

        $eventos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->findAll();

        return $this->render('LinkBackendBundle:Calendario:index.html.twig', array('eventos' => $eventos));
    }
}