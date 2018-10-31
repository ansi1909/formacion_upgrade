<?php

namespace Link\ComunBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;

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

        $empresa_id = isset($session->get('empresa')['id']) ? $session->get('empresa')['id'] : 0;
        $empresa_id = !$empresa_id ? ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0 : $empresa_id;

        if ($session->get('sesion_id'))
        {

            $sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($session->get('sesion_id'));
            if ($sesion)
            {

                $usuario_id = $session->get('usuario')['id'];
                $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

                // Borra la cookie que almacena la sesión del usuario logueado
                if(isset($_COOKIE['id_usuario'])) 
                {
                    $usuario->setCookies(null);
                    $em->persist($usuario);
                    $em->flush();

                    setcookie('id_usuario', '', time() - 42000, '/'); 
                    setcookie('marca_aleatoria_usuario', '', time() - 42000, '/');
                }

                $sesion->setDisponible(false);
                $em->persist($sesion);
                $em->flush();
                $f->setRequest($session->get('sesion_id'));
                
            }

        }
        
        $parametros = array();
        if ($ruta == '_login')
        {
            $parametros = array('empresa_id' => $empresa_id);
        }
        
        $session->invalidate();
        $session->clear();
        
        return $this->redirectToRoute($ruta, $parametros);

    }

    /*public function logoutEmpresaAction()
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $error = '';
        //return new response(var_dump($sesion));

        $sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($session->get('sesion_id'));
        $empresa_id = $session->get('empresa')['id'];

        if ($sesion)
        {
            $sesion->setDisponible(false);
            $em->persist($sesion);
            $em->flush();
            $f->setRequest($session->get('sesion_id'));
        }
        
        $session->invalidate();
        $session->clear();

        if($empresa_id)
        {
         
            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->findOneById($empresa_id);
            return $this->redirectToRoute('_login', array('empresa_id' => $empresa->getId()));

        }else
        {

        }        

    }*/
}
