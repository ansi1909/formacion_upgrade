<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNoticia; 


class NovedadController extends Controller
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

        $em = $this->getDoctrine()->getManager();

        
        $tipo_noticia = $em->getRepository('LinkComunBundle:AdminTipoNoticia')->find(3);

        if($session->get('administrador')==true)//si es administrador
        {
            $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll(array('nombre' => 'ASC'));

            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNoticia n
                                       JOIN n.tipoNoticia tn 
                                       WHERE tn.id = :tipo ")
                        ->setParameter('tipo', $tipo_noticia->getId() ) ;

        }else
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($usuario->getEmpresa()->getId() );
 
            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNoticia n
                                       JOIN n.tipoNoticia tn 
                                       WHERE tn.id = :tipo AND n.empresa = :empresa_id ")
                        ->setParameters(array('tipo' => $tipo_noticia->getId(),'empresa_id' => $empresas->getId() ) );

        }

        // ORDER BY n.fechaPublicacion ASC");                    
        
        $noticias = $query->getResult();
        $noticiadb= array();
       //return new Response(var_dump($roles));
        
        foreach ($noticias as $noticia)
        {
            $noticiadb[]= array('id'=>$noticia->getId(),
                                'empresa'=>$noticia->getEmpresa()->getNombre(),
                                'titulo'=>$noticia->getTitulo(),
                                'fechaRegistro'=>$noticia->getFechaRegistro(),
                                'delete_disabled'=>$f->linkEliminar($noticia->getId(),'AdminNoticia'));
        }



       return $this->render('LinkBackendBundle:Novedad:index.html.twig', array('noticias'=>$noticiadb,
                                                                                  'empresas' => $empresas));

    }

   public function ajaxUpdateBibliotecaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        $app_id = $request->request->get('app_id');
        $usuario_id = $session->get('usuario')['id'];
        $noticia_id = $request->request->get('noticia_id');
        $empresa_id = $request->request->get('empresa_id');
        $tipo_noticia_id = 3;

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $tipo_noticia = $em->getRepository('LinkComunBundle:AdminTipoNoticia')->find($tipo_noticia_id);

        $titulo = trim($request->request->get('titulo'));
        $contenido = trim($request->request->get('contenido'));
        $pdf = "pdf";//trim($request->request->get('pdf'));
        $imagen = "imagen";//trim($request->request->get('imagen'));

        if ($noticia_id)
        {
            $noticia = $em->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
        }
        else {
            $noticia = new AdminNoticia();
        }

        $noticia->setEmpresa($empresa);
        $noticia->setUsuario($usuario);
        $noticia->setTipoNoticia($tipo_noticia);
        $noticia->setFechaRegistro(new \DateTime('now'));
        $noticia->setContenido($contenido);
        $noticia->setTitulo($titulo);

        $noticia->setPdf($contenido);
        $noticia->setImagen($titulo);
        
        $em->persist($noticia);
        $em->flush();

        $return[]= array('id'=>$noticia->getId(),
                         'empresa'=>$pa->getEmpresa()->getNombre(),
                         'titulo'=>$noticia->getTitulo(),
                         'fechaRegistro'=>$noticia->getFechaRegistro(),
                         'delete_disabled'=>$f->linkEliminar($noticia->getId(),'AdminNoticia'));


        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditBibliotecaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $rol_id = $request->query->get('rol_id');
                
        $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($rol_id);

        $query = $em->createQuery("SELECT r FROM LinkComunBundle:AdminRol r 
                                    WHERE r.nombre IS NULL 
                                    ORDER BY r.id ASC");
        $apps = $query->getResult();


        $return = array('nombre' => $rol->getNombre(),
                        'descripcion' => $rol->getDescripcion());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}