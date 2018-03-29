<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Validator\Constraints\DateTime;

class UsuarioController extends Controller
{

    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {

            $usuario_id = $session->get('usuario')['id'];

            if ($request->getMethod() == 'POST')
            {
                $user = $request->request->get('username');
                
                $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
                
                $usuario->setLogin($user);

                $em->persist($usuario);
                $em->flush();   

            }

            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id); 
            

        }else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Usuario:index.html.twig', array('usuario' => $usuario));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;

    }
}