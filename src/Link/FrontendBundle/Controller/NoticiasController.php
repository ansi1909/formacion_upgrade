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

class NoticiasController extends Controller
{

    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {

            $usuario_id = $session->get('usuario')['id'];
            $empresa_id = $session->get('empresa')['id'];
            $hoy = new \DateTime();
            $now = strtotime($hoy->format('d-m-Y'));
            $todos = array();
            $noticias = array();
            $novedades = array();

            if ($request->getMethod() == 'POST'){

                $buscar = $request->request->get('buscar');

                $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.titulo LIKE :buscar
                                           AND n.empresa = :empresa_id")
                            ->setParameters(array('buscar'=> '%'.$buscar.'%',
                                                  'empresa_id'=> $empresa_id));
                $busquedas = $query->getResult();

                foreach($busquedas as $busqueda)
                {
                    $fecha_i = strtotime($busqueda->getFechaPublicacion()->format('d-m-Y'));
                    $fecha_f = strtotime($busqueda->getFechaVencimiento()->format('d-m-Y'));
                    if ($now >= $fecha_i && $now < $fecha_f) 
                   {
                        $todos[] =array('id'=>$busqueda->getId(),
                                           'titulo'=>$busqueda->getTitulo(),
                                           'fecha'=>$busqueda->getFechaPublicacion()->format('d/m/Y'),
                                           'tipo'=>$busqueda->getTipoNoticia()->getNombre(),
                                           'imagen'=>$busqueda->getImagen(),
                                           'tid'=>$busqueda->getTipoNoticia()->getId());

                        if($busqueda->getTipoNoticia()->getId() == '1')
                        {
                            $noticias[] =array('id'=>$busqueda->getId(),
                                           'titulo'=>$busqueda->getTitulo(),
                                           'fecha'=>$busqueda->getFechaPublicacion()->format('d/m/Y'),
                                           'tipo'=>$busqueda->getTipoNoticia()->getNombre(),
                                           'imagen'=>$busqueda->getImagen(),
                                           'tid'=>$busqueda->getTipoNoticia()->getId());

                        }elseif ($busqueda->getTipoNoticia()->getId() == '2') 
                        {
                            $novedades[] =array('id'=>$busqueda->getId(),
                                           'titulo'=>$busqueda->getTitulo(),
                                           'fecha'=>$busqueda->getFechaPublicacion()->format('d/m/Y'),
                                           'tipo'=>$busqueda->getTipoNoticia()->getNombre(),
                                           'imagen'=>$busqueda->getImagen(),
                                           'tid'=>$busqueda->getTipoNoticia()->getId());
                        }
                    }
                }

                return $this->render('LinkFrontendBundle:Noticias:index.html.twig', array('usuario_id' => $usuario_id,
                                                                                  'todos' => $todos,
                                                                                  'noticias' => $noticias,
                                                                                  'novedades' => $novedades));

                $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

                return $response;
            }

            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.empresa = :empresa_id
                                       AND n.tipoBiblioteca IS NULL')
                        ->setParameter('empresa_id', $empresa_id);
            $noticias_db = $query->getResult();

            foreach($noticias_db as $noticia)
            {
                $fecha_i = strtotime($noticia->getFechaPublicacion()->format('d-m-Y'));
                $fecha_f = strtotime($noticia->getFechaVencimiento()->format('d-m-Y'));
                if ($now >= $fecha_i && $now < $fecha_f) 
               {
                    $todos[] =array('id'=>$noticia->getId(),
                                       'titulo'=>$noticia->getTitulo(),
                                       'fecha'=>$noticia->getFechaPublicacion()->format('d/m/Y'),
                                       'tipo'=>$noticia->getTipoNoticia()->getNombre(),
                                       'imagen'=>$noticia->getImagen(),
                                       'tid'=>$noticia->getTipoNoticia()->getId());

                    if($noticia->getTipoNoticia()->getId() == '1')
                    {
                        $noticias[] =array('id'=>$noticia->getId(),
                                       'titulo'=>$noticia->getTitulo(),
                                       'fecha'=>$noticia->getFechaPublicacion()->format('d/m/Y'),
                                       'tipo'=>$noticia->getTipoNoticia()->getNombre(),
                                       'imagen'=>$noticia->getImagen(),
                                       'tid'=>$noticia->getTipoNoticia()->getId());

                    }elseif ($noticia->getTipoNoticia()->getId() == '2') 
                    {
                        $novedades[] =array('id'=>$noticia->getId(),
                                       'titulo'=>$noticia->getTitulo(),
                                       'fecha'=>$noticia->getFechaPublicacion()->format('d/m/Y'),
                                       'tipo'=>$noticia->getTipoNoticia()->getNombre(),
                                       'imagen'=>$noticia->getImagen(),
                                       'tid'=>$noticia->getTipoNoticia()->getId());
                    }
                }
            }

        }else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Noticias:index.html.twig', array('usuario_id' => $usuario_id,
                                                                                  'todos' => $todos,
                                                                                  'noticias' => $noticias,
                                                                                  'novedades' => $novedades));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;

    }

    public function detalleAction($noticia_id)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {

            $noticia = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
            $noticias=array();

            if ($noticia->getTipoNoticia()->getId() == 1) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoNoticia = :noticia
                                           AND n.id != :noticia_id')
                            ->setMaxResults(3)
                            ->setParameters(array('noticia'=> 1,
                                                  'noticia_id'=> $noticia_id));
                $noticias = $query->getResult();
            }elseif ($noticia->getTipoNoticia()->getId() == 2) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoNoticia = :noticia
                                           AND n.id != :noticia_id')
                            ->setMaxResults(3)
                            ->setParameters(array('noticia'=> 2,
                                                  'noticia_id'=> $noticia_id));
                $noticias = $query->getResult();
            }

            

        }else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Noticias:detalle.html.twig', array('noticia' => $noticia,
                                                                                    'noticias'=> $noticias));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;
    }

}