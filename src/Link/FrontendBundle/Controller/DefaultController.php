<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{
    public function indexAction()
    {

    	/*$em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini'))
      	{
        	return $this->redirectToRoute('_login');
      	}
        $f->setRequest($session->get('sesion_id'));

      	if ($this->container->get('session')->isStarted())
      	{
        	
        	$empresa_id = $session->get('empresa_id');
        	$datos_usuario = $session->get('usuario');
        	$nombre_usuario = $datos_usuario['nombre'];
        	$apellido_usuario = $datos_usuario['apellido'];
        	$correo_usuario = $datos_usuario['correo'];
        	$foto_usuario = $datos_usuario['foto'];
        	$roles_usuario = $datos_usuario['roles'];

      	}
      	else {
      		return $this->redirectToRoute('_login');
      	}*/

      	/*	$usuario_id = 2;
      	$usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
      	$query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
		            ->setParameter('usuario_id', $usuario->getId());
        $roles_usuario = $query->getResult();

		$nombre = $usuario->getNombre();
		$apellido = $usuario->getApellido();
		$correo = $usuario->getCorreoPersonal();
		$foto = $usuario->getFoto();
		$roles = $roles_usuario;*/




        return $this->render('LinkFrontendBundle:Default:index.html.twig');

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    

}