<?php

namespace Link\ComunBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use Link\ComunBundle\Model\UploadHandler;

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

    public function ajaxServicioInteractivoAction(Request $request)
    {
        
        $session = new Session();
        
        $codigo = $request->request->get('codigo');
        $visto = $request->request->get('visto');

        if ($visto && ($visto == 1 || $visto == '1'))
        {
            $end_msg = 'Recurso visto.';
        }
        else {
            $end_msg = 'Recurso NO visto.';
        }

        $msg = 'El código recibido es: '.$codigo.'. '.$end_msg;

        $return = array('ok' => 1,
                        'msg' => $msg);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxUploadAction(Request $request)
    {
        
        // Parámetro adicional
        $base_upload = $request->request->get('base_upload');      

        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
        $uploads = $this->container->getParameter('folders')['uploads'];
        $upload_dir = $dir_uploads.$base_upload;
        $upload_url = $uploads.$base_upload;
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);

        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

}
