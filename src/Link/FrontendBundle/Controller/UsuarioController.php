<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Model\UploadHandler;

class UsuarioController extends Controller
{

    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $usuario_id = $session->get('usuario')['id'];

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
        
        $query = $em->createQuery('SELECT pl FROM LinkComunBundle:CertiPaginaLog pl
                                   WHERE pl.usuario = :usuario_id')
                    ->setParameter('usuario_id', $usuario_id);
        $paginalogs = $query->getResult();
        $puntos = 0;

        foreach( $paginalogs as $paginalog )
        {
            $puntos = $puntos + $paginalog->getPuntos();
        }
        $fechaNacimiento= ($usuario->getFechaNacimiento())? $usuario->getFechaNacimiento()->format('d/m/Y'):'';

        return $this->render('LinkFrontendBundle:Usuario:index.html.twig', array('usuario' => $usuario,
                                                                                 'fecha' => $fechaNacimiento,
                                                                                 'puntos' => $puntos));

    }

    public function ajaxClaveAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();

        $clave = $request->request->get('password');

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 
        if( $clave == $usuario->getClave())
        {
            $return = array('ok' => 2,
                            'mensaje' => 'La contraseña debe ser distinta a la actual');
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        $usuario->setClave($clave);
        $em->persist($usuario);
        $em->flush();

        $return = array('ok' => 1,
                        'mensaje' => 'Cambio de contraseña realizado');
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxImagenAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();

        $foto = $request->request->get('foto');

        // Actualización de la foto en la BD
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $usuario->setFoto($foto);
        $em->persist($usuario);
        $em->flush();

        // Actualización en la sesión
        $datosUsuario = $session->get('usuario');
        $datosUsuario['foto'] = $usuario->getFoto();
        $session->set('usuario', $datosUsuario);

        $return = json_encode(array('id' => $usuario->getId()));
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxUploadFotoUsuarioAction(Request $request)
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

    public function ajaxSavePerfilAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $correo_secundario = trim($request->request->get('correo_secundario'));
        $f = $this->get('funciones');
        
        // Actualización del correo en la BD
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $html = '';
        $validarCorreo = ($correo_secundario!='')? $f->searchMail($correo_secundario,$usuario->getEmpresa(),$usuario->getId()): false;

        if ($correo_secundario != '' && $usuario->getCorreoPersonal() == $correo_secundario) {

             $html .= $this->get('translator')->trans('Este correo no puede ser igual al corporativo');    
        }
        else if($correo_secundario != '' && $validarCorreo != 0 ){
            $html .= $this->get('translator')->trans('Este correo se encuentra registrado');
        }
        else {
            $html ='';            
            $usuario->setCorreoCorporativo($correo_secundario && $correo_secundario != '' ? $correo_secundario : null);
            $em->persist($usuario);
            $em->flush();

            // Actualización en la sesión
            $datosUsuario = $session->get('usuario');
            $datosUsuario['correo_corporativo'] = $usuario->getCorreoCorporativo();
            $session->set('usuario', $datosUsuario);
            
        }

        $return = array('correo' => $usuario->getCorreoPersonal(),
                        'correo_corporativo' => $usuario->getCorreoCorporativo(),
                        'fechaNacimiento' => $usuario->getFechaNacimiento()->format('d/m/Y'),
                        'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

}