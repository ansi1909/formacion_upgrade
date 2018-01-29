<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminNoticia;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CertificadoController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

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

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll(array('nombre' => 'ASC'));
        $noticias = $em->getRepository('LinkComunBundle:AdminNoticia')->findAll();
            
        $noticiadb= array();
      
        return $this->render('LinkBackendBundle:Certificado:index.html.twig', array('aplicacion' => $aplicacion,
                                                                            'noticias' => $noticiadb,
                                                                            'empresas' => $empresas,
                                                                            'usuario' => $usuario ));
    
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

        if ($certificado_id)
        {
            $certificado = $em->getRepository('LinkComunBundle:AdminNoticia')->find($certificado_id);
        }else 
        {
            $certificado = new AdminNoticia();
            $certificado->setFechaRegistro(new \DateTime('now'));
        }

        if ($request->getMethod() == 'POST')
        {

           /* if($usuario_empresa==1)
            {
                $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($usuario->getEmpresa()->getId());
            }else
            {
                $empresa_id = $request->request->get('empresa_id');
                $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            }

            $tipo_noticia_id = 3;
            $tipoNoticia = $em->getRepository('LinkComunBundle:AdminTipoNoticia')->find($tipo_noticia_id);

            $titulo = trim($request->request->get('titulo'));
            $pdf = trim($request->request->get('pdf'));
            $imagen = trim($request->request->get('imagen'));
            $contenido = trim($request->request->get('contenido'));

            $certificado->setUsuario($usuario);
            $certificado->setEmpresa($empresa);
            $certificado->setTipoNoticia($tipoNoticia);
            $certificado->setTitulo($titulo);
            $certificado->setPdf($pdf);
            $certificado->setImagen($imagen);
            $certificado->setContenido($contenido);
            $em->persist($certificado);
            $em->flush();*/

            return $this->redirectToRoute('_showCertificado', array('certificado_id' => $certificado->getId()));

        }

        return $this->render('LinkBackendBundle:Certificado:registro.html.twig', array('empresas' => $empresas,
                                                                                      'certificado' => $certificado));

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

        $certificado = $em->getRepository('LinkComunBundle:AdminNoticia')->find($certificado_id);

        return $this->render('LinkBackendBundle:Certificado:mostrar.html.twig', array('certificado' => $certificado));

    }


}
