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

class NovedadController extends Controller
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
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $app_id = $session->get('app_id');

        //contultamos el nombre de la aplicacion para reutilizarla en la vista
        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);

        if($app_id==26)//se consulta el tipo de noticias: biblioteca virtual
        {
            $tipo_noticia = $em->getRepository('LinkComunBundle:AdminTipoNoticia')->find($yml['parameters']['tipo_noticias']['biblioteca_virtual']);
        }

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        $usuario_empresa = 0;
        $empresas = array();


        if($session->get('administrador')==true)//si es administrador
        {
            $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll(array('nombre' => 'ASC'));

            if($app_id==26)//se consulta la informacion del tipo de noticia: biblioteca virtual
            {
                $noticias = $em->getRepository('LinkComunBundle:AdminNoticia')->findBy(array('tipoNoticia' => $tipo_noticia->getId()));
            }else
            {
                if($app_id==17)//se consulta la informacion del tipo de noticia: noticias y novedades 
                {   
                    $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n JOIN n.tipoNoticia tn 
                                               WHERE tn.id < :tipo ')
                                ->setParameters(array('tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'] ));
                    $noticias = $query->getResult();
                }
            }
        }else
        {
            if ($usuario->getEmpresa()) 
                $usuario_empresa = 1; 
            
            if($app_id==26)//se la informacion del tipo de noticia: biblioteca virtual
            {
                $noticias = $em->getRepository('LinkComunBundle:AdminNoticia')->findBy(array('tipoNoticia' => $tipo_noticia->getId(),
                                                                                             'empresa' => $usuario->getEmpresa()->getId()  ) );
            }else
            {
                if($app_id==17)//se consulta la informacion del tipo de noticia: noticias y novedades 
                {   
                    $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n 
                                               JOIN n.tipoNoticia tn 
                                               JOIN n.empresa e 
                                               WHERE tn.id < :tipo and e.id= :empresa')
                                ->setParameters(array('tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'], 'empresa' => $usuario->getEmpresa()->getId() ));
                    $noticias = $query->getResult();
                }
            }
        }

        $noticiadb= array();
        if($noticias)
        {
            foreach ($noticias as $noticia)
            {
                $noticiadb[]= array('id'=>$noticia->getId(),
                                    'empresa'=>$noticia->getEmpresa()->getNombre(),
                                    'tipoNoticia'=>$noticia->getTipoNoticia()->getNombre(),
                                    'titulo'=>$noticia->getTitulo(),
                                    'fechaRegistro'=>$noticia->getFechaRegistro(),
                                    'delete_disabled'=>$f->linkEliminar($noticia->getId(),'AdminNoticia'));
            }
        }

        return $this->render('LinkBackendBundle:Novedad:index.html.twig', array('aplicacion' => $aplicacion,
                                                                                'noticias' => $noticiadb,
                                                                                'usuario_empresa' => $usuario_empresa,
                                                                                'empresas' => $empresas,
                                                                                'usuario' => $usuario ));
    }

    public function registroBibliotecaAction($biblioteca_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        //contultamos el nombre de la aplicacion para reutilizarla en la vista
        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($session->get('app_id'));

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll(array('nombre' => 'ASC'));

        $usuario_empresa = 0;
        if($session->get('administrador')==false)//si no es administrador
        {
            if ($usuario->getEmpresa()) 
                $usuario_empresa = 1; 
        }

        if ($biblioteca_id)
        {
            $biblioteca = $em->getRepository('LinkComunBundle:AdminNoticia')->find($biblioteca_id);
        }else 
        {
            $biblioteca = new AdminNoticia();
            $biblioteca->setFechaRegistro(new \DateTime('now'));
        }

        if ($request->getMethod() == 'POST')
        {

            if($usuario_empresa==1)
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

            $biblioteca->setUsuario($usuario);
            $biblioteca->setEmpresa($empresa);
            $biblioteca->setTipoNoticia($tipoNoticia);
            $biblioteca->setTitulo($titulo);
            $biblioteca->setPdf($pdf);
            $biblioteca->setImagen($imagen);
            $biblioteca->setContenido($contenido);
            $em->persist($biblioteca);
            $em->flush();


            return $this->redirectToRoute('_bibliotecas', array('app_id' => $aplicacion->getId() ));

        }
       // return new Response(var_dump($session->get('administrador'),$usuario_empresa));

        return $this->render('LinkBackendBundle:Novedad:registroBiblioteca.html.twig', array('empresas' => $empresas,
                                                                                             'biblioteca' => $biblioteca,
                                                                                             'usuario_empresa' => $usuario_empresa ));

    }

    public function registroNovedadAction($noticia_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        //contultamos el nombre de la aplicacion para reutilizarla en la vista
        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($session->get('app_id'));

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll(array('nombre' => 'ASC'));

        $query = $em->createQuery('SELECT tn FROM LinkComunBundle:AdminTipoNoticia tn
                                   WHERE tn.id < :tipo ')
                    ->setParameters(array('tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual'] ));
        $tipoNoticias = $query->getResult();

        $usuario_empresa = 0;

        if($session->get('administrador')==false)//si no es administrador
        {
            if ($usuario->getEmpresa()) 
                $usuario_empresa = 1; 
        }

        if ($noticia_id)
        {
            $noticia = $em->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
        }else 
        {
            $noticia = new AdminNoticia();
            $noticia->setFechaRegistro(new \DateTime('now'));
        }

        if ($request->getMethod() == 'POST')
        {

            if($usuario_empresa==1)
            {
                $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($usuario->getEmpresa()->getId());
            }else
            {
                $empresa_id = $request->request->get('empresa_id');
                $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            }

            $tipo_noticia_id = $request->request->get('tipo_noticia_id');
            $tipoNoticia = $em->getRepository('LinkComunBundle:AdminTipoNoticia')->find($tipo_noticia_id);

            $titulo = trim($request->request->get('titulo'));
            $autor = trim($request->request->get('autor'));

            $fecha_vencimiento = trim($request->request->get('fecha_vencimiento'));
            $fv = explode("/", $fecha_vencimiento);
            $vencimiento = $fv[2].'-'.$fv[1].'-'.$fv[0];
            
            $fecha_publicacion = trim($request->request->get('fecha_publicacion'));
            $fp = explode("/", $fecha_publicacion);
            $publicacion = $fp[2].'-'.$fp[1].'-'.$fp[0];

            $pdf = trim($request->request->get('pdf'));
            $imagen = trim($request->request->get('imagen'));

            $contenido = trim($request->request->get('contenido'));
            $resumen = trim($request->request->get('resumen'));

            $noticia->setUsuario($usuario);
            $noticia->setEmpresa($empresa);
            $noticia->setTipoNoticia($tipoNoticia);
            $noticia->setTitulo($titulo);
            $noticia->setAutor($autor);
            $noticia->setPdf($pdf);
            $noticia->setImagen($imagen);
            $noticia->setResumen($resumen);
            $noticia->setContenido($contenido);
            $noticia->setFechaVencimiento(new \DateTime($vencimiento));
            $noticia->setFechaPublicacion(new \DateTime($publicacion));
            $em->persist($noticia);
            $em->flush();

            return $this->redirectToRoute('_bibliotecas', array('app_id' => $aplicacion->getId() ));

        }
        
        return $this->render('LinkBackendBundle:Novedad:registroNovedad.html.twig', array('empresas' => $empresas,
                                                                                          'tipoNoticias' => $tipoNoticias,
                                                                                          'noticia' => $noticia,
                                                                                          'usuario_empresa' => $usuario_empresa ));

    }

/*    public function mostrarAction($empresa_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        return $this->render('LinkBackendBundle:Empresa:mostrar.html.twig', array('empresa' => $empresa));

    }*/

}
