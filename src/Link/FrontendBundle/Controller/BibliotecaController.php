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

class BibliotecaController extends Controller
{

    public function indexAction()
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
            $empresa_id = $session->get('empresa')['id'];
            $todos = array();
            $videos = array();
            $podcast = array();
            $articulos = array();
            $libros = array();

            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.empresa = :empresa_id
                                       AND n.tipoNoticia = :tipo
                                       AND n.pdf IS NOT NULL')
                        ->setParameters(array('empresa_id' => $empresa_id,
                                              'tipo' => '3'));
            $noticias_db = $query->getResult();

            foreach($noticias_db as $noticia)
            {
                $todos[] =array('id'=>$noticia->getId(),
                                'titulo'=>$noticia->getTitulo(),
                                'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                'tid'=>$noticia->getTipoBiblioteca()->getId());

                if ($noticia->getTipoBiblioteca()->getId() == '1') 
                {
                    $videos[] =array('id'=>$noticia->getId(),
                                     'titulo'=>$noticia->getTitulo(),
                                     'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                     'tid'=>$noticia->getTipoBiblioteca()->getId());
                }
                else if ($noticia->getTipoBiblioteca()->getId() == '2') {
                    $podcast[] =array('id'=>$noticia->getId(),
                                      'titulo'=>$noticia->getTitulo(),
                                      'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                      'tid'=>$noticia->getTipoBiblioteca()->getId());
                }
                else if ($noticia->getTipoBiblioteca()->getId() == '3') {
                    $articulos[] =array('id'=>$noticia->getId(),
                                        'titulo'=>$noticia->getTitulo(),
                                        'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                        'tid'=>$noticia->getTipoBiblioteca()->getId());
                }
                else{   
                    $libros[] =array('id'=>$noticia->getId(),
                                     'titulo'=>$noticia->getTitulo(),
                                     'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                     'tid'=>$noticia->getTipoBiblioteca()->getId());
                }
            }

        }else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:Biblioteca:index.html.twig', array('usuario_id' => $usuario_id,
                                                                                    'todos' => $todos,
                                                                                    'videos' => $videos,
                                                                                    'podcasts' => $podcast,
                                                                                    'articulos' => $articulos,
                                                                                    'libros' => $libros));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;

    }

    public function detalleAction($biblioteca_id)
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

            $biblioteca = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($biblioteca_id);
            $anuncios=array();

            if ($biblioteca->getTipoBiblioteca()->getId() == 1) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoBiblioteca = :biblioteca
                                           AND n.tipoNoticia = :noticia_id
                                           AND n.id != :id
                                           AND n.pdf IS NOT NULL')
                            ->setMaxResults(3)
                            ->setParameters(array('biblioteca'=> 1,
                                                  'noticia_id'=> 3,
                                                  'id'=> $biblioteca_id));
                $anuncios = $query->getResult();

            }elseif ($biblioteca->getTipoBiblioteca()->getId() == 2) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoBiblioteca = :biblioteca
                                           AND n.tipoNoticia = :noticia_id
                                           AND n.id != :id
                                           AND n.pdf IS NOT NULL')
                            ->setMaxResults(3)
                            ->setParameters(array('biblioteca'=> 2,
                                                  'noticia_id'=> 3,
                                                  'id'=> $biblioteca_id));
                $anuncios = $query->getResult();
            }elseif ($biblioteca->getTipoBiblioteca()->getId() == 3 || $biblioteca->getTipoBiblioteca()->getId() == 4) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoBiblioteca IN (:biblioteca)
                                           AND n.tipoNoticia = :noticia_id
                                           AND n.id != :id
                                           AND n.pdf IS NOT NULL')
                            ->setMaxResults(3)
                            ->setParameters(array('biblioteca'=> array('3','4'),
                                                  'noticia_id'=> 3,
                                                  'id'=> $biblioteca_id));
                $anuncios = $query->getResult();
            }

            

        }else {
            return $this->redirectToRoute('_login');
        }

        //return new Response (var_dump($anuncios));

        return $this->render('LinkFrontendBundle:Biblioteca:detalle.html.twig', array('biblioteca' => $biblioteca,
                                                                                      'anuncios' => $anuncios));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;
    }

}