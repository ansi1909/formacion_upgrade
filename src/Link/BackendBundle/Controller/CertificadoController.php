<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiCertificado;

class CertificadoController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }else 
        {
            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id'))  && $session->get('administrador')==false )
            {
                return $this->redirectToRoute('_authException');
            }
        }

        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $app_id = $session->get('app_id');

        //contultamos el nombre de la aplicacion para reutilizarla en la vista
        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);

        $certificados = $em->getRepository('LinkComunBundle:CertiCertificado')->findAll();

        $certificadodb= array();
        if($certificados)
        {
            foreach ($certificados as $certificado)
            {
                $certificadodb[]= array('id'=>$certificado->getId(),
                                    'empresa'=>$certificado->getEmpresa()->getNombre(),
                                    'tipoCertificado'=>$certificado->getTipoCertificado()->getNombre(),
                                    'tipoImagenCertificado'=>$certificado->getTipoImagenCertificado()->getNombre(),
                                    'delete_disabled'=>$f->linkEliminar($certificado->getId(),'CertiCertificado'));
            }
        }

        return $this->render('LinkBackendBundle:Certificado:index.html.twig', array('aplicacion' => $aplicacion,
                                                                                    'certificados' => $certificadodb ));

    }

    public function registroAction($certificado_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) && $session->get('administrador')==false )
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
     
        $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll(array('nombre' => 'ASC'));
        $tipo_certificados = $em->getRepository('LinkComunBundle:CertiTipoCertificado')->findAll(array('nombre' => 'ASC')); 
        $tipo_imagen_certificados = $em->getRepository('LinkComunBundle:CertiTipoImagenCertificado')->findAll(array('nombre' => 'ASC'));

        if ($certificado_id)
            $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($certificado_id);
        else 
            $certificado = new CertiCertificado();

        if ($request->getMethod() == 'POST')
        {
            $empresa_id = $request->request->get('empresa_id');
            $tipo_imagen_certificado_id = $request->request->get('tipo_imagen_certificado_id');
            $tipo_certificado_id = $request->request->get('tipo_certificado_id');
            $entidad = $request->request->get('entidad');
            $imagen = trim($request->request->get('imagen'));
            $encabezado = trim($request->request->get('encabezado'));
            $nombre = trim($request->request->get('nombre'));
            $descripcion = trim($request->request->get('descripcion'));
            $titulo = trim($request->request->get('titulo'));
            $fecha = trim($request->request->get('fecha'));
            $qr = trim($request->request->get('qr'));

            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $tipoCertificado = $em->getRepository('LinkComunBundle:CertiTipoCertificado')->find($tipo_certificado_id);
            $tipoImagenCertificado = $em->getRepository('LinkComunBundle:CertiTipoImagenCertificado')->find($tipo_imagen_certificado_id);
            
            $certificado->setEmpresa($empresa);
            $certificado->setTipoCertificado($tipoCertificado);
            $certificado->setTipoImagenCertificado($tipoImagenCertificado);
            $certificado->setEntidadId($entidad);
            $certificado->setImagen($imagen);
            $certificado->setEncabezado($encabezado);
            $certificado->setNombre($nombre);
            $certificado->setDescripcion($descripcion);
            $certificado->setTitulo($titulo);
            $certificado->setFecha($fecha);
            $certificado->setQr($qr);            
            $em->persist($certificado);
            $em->flush();

            return $this->redirectToRoute('_showCertificado', array('certificado_id' => $certificado->getId()));

        }

        return $this->render('LinkBackendBundle:Certificado:registro.html.twig', array('empresas' => $empresas,
                                                                                       'certificado' => $certificado,
                                                                                       'tipoCertificados' => $tipo_certificados,
                                                                                       'tipoImagenCertificados' => $tipo_imagen_certificados ));

    }

    public function mostrarAction($certificado_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) && $session->get('administrador')==false )
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($certificado_id);

        return $this->render('LinkBackendBundle:Certificado:mostrar.html.twig', array('certificado' => $certificado));

    }


}
