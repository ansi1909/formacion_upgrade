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
                $mailp = $request->request->get('mailp');
                $mailad = $request->request->get('mailad');
                $fechan = $request->request->get('fechan');
                
                $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
                
                $usuario->setLogin($user);
                $usuario->setCorreoPersonal($mailp);
                $usuario->setCorreoCorporativo($mailad);
                $fn_array = explode("-", $fechan);
                $d = $fn_array[0];
                $m = $fn_array[1];
                $a = $fn_array[2];
                $fechan = "$a-$m-$d";
                $usuario->setFechaNacimiento(new \DateTime($fechan));

                $em->persist($usuario);
                $em->flush();   

            }

            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id); 
            

        }else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Usuario:index.html.twig', array('usuario' => $usuario,
                                                                                 'fecha' => $usuario->getFechaNacimiento()->format('Y-m-d')));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;

    }

    public function ajaxVerificarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $html ='';
        $usuario_id = $request->request->get('usuario_id');
        $nueva = $request->request->get('p_new');
        $confirmar = $request->request->get('p_conf');

        if ($nueva == $confirmar) {

            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id); 

            $usuario->setClave($nueva);
            $em->persist($usuario);
            $em->flush();

            $html ='<strong>Contraseña cambiada con exito</strong>';
        }else
        {
            $html='<strong>Contraseñas no coinciden</strong>';
        }

        $return = array('html' => $html);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}