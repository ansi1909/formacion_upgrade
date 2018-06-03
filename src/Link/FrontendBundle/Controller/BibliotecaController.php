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

    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

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
            $hoy = new \DateTime();
            $now = strtotime($hoy->format('d-m-Y'));
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

            if ($request->getMethod() == 'POST'){

                $buscar = $request->request->get('buscar');

                $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.titulo LIKE :buscar
                                           AND n.empresa = :empresa_id
                                           AND n.tipoNoticia = :tipo
                                           AND n.pdf IS NOT NULL")
                            ->setParameters(array('buscar'=> '%'.$buscar.'%',
                                                  'empresa_id'=> $empresa_id,
                                                  'tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual']));
                $busquedas = $query->getResult();

                foreach($busquedas as $busqueda)
                {
                    $fecha_i = strtotime($busqueda->getFechaPublicacion()->format('d-m-Y'));
                    $fecha_f = strtotime($busqueda->getFechaVencimiento()->format('d-m-Y'));
                    if ($now >= $fecha_i && $now < $fecha_f) 
                       {
                        $todos[] =array('id'=>$busqueda->getId(),
                                        'titulo'=>$busqueda->getTitulo(),
                                        'tipo'=>$busqueda->getTipoBiblioteca()->getNombre(),
                                        'tid'=>$busqueda->getTipoBiblioteca()->getId());

                        if ($busqueda->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['video']) 
                        {
                            $videos[] =array('id'=>$busqueda->getId(),
                                             'titulo'=>$busqueda->getTitulo(),
                                             'tipo'=>$busqueda->getTipoBiblioteca()->getNombre(),
                                             'tid'=>$busqueda->getTipoBiblioteca()->getId());
                        }
                        else if ($busqueda->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['podcast']) {
                            $podcast[] =array('id'=>$busqueda->getId(),
                                              'titulo'=>$busqueda->getTitulo(),
                                              'tipo'=>$busqueda->getTipoBiblioteca()->getNombre(),
                                              'tid'=>$busqueda->getTipoBiblioteca()->getId());
                        }
                        else if ($busqueda->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['articulo']) {
                            $articulos[] =array('id'=>$busqueda->getId(),
                                                'titulo'=>$busqueda->getTitulo(),
                                                'tipo'=>$busqueda->getTipoBiblioteca()->getNombre(),
                                                'tid'=>$busqueda->getTipoBiblioteca()->getId());
                        }
                        else{   
                            $libros[] =array('id'=>$busqueda->getId(),
                                             'titulo'=>$busqueda->getTitulo(),
                                             'tipo'=>$busqueda->getTipoBiblioteca()->getNombre(),
                                             'tid'=>$busqueda->getTipoBiblioteca()->getId());
                        }
                    }
                }

                return $this->render('LinkFrontendBundle:Biblioteca:index.html.twig', array('usuario_id' => $usuario_id,
                                                                                            'todos' => $todos,
                                                                                            'videos' => $videos,
                                                                                            'podcasts' => $podcast,
                                                                                            'articulos' => $articulos,
                                                                                            'libros' => $libros));
            }

            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.empresa = :empresa_id
                                       AND n.tipoNoticia = :tipo
                                       AND n.pdf IS NOT NULL')
                        ->setParameters(array('empresa_id' => $empresa_id,
                                              'tipo' => $yml['parameters']['tipo_noticias']['biblioteca_virtual']));
            $noticias_db = $query->getResult();

            foreach($noticias_db as $noticia)
            {
                $fecha_i = strtotime($noticia->getFechaPublicacion()->format('d-m-Y'));
                $fecha_f = strtotime($noticia->getFechaVencimiento()->format('d-m-Y'));
                if ($now >= $fecha_i && $now < $fecha_f) 
               {
                    $todos[] =array('id'=>$noticia->getId(),
                                    'titulo'=>$noticia->getTitulo(),
                                    'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                    'tid'=>$noticia->getTipoBiblioteca()->getId());

                    if ($noticia->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['video']) 
                    {
                        $videos[] =array('id'=>$noticia->getId(),
                                         'titulo'=>$noticia->getTitulo(),
                                         'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                         'tid'=>$noticia->getTipoBiblioteca()->getId());
                    }
                    else if ($noticia->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['podcast']) {
                        $podcast[] =array('id'=>$noticia->getId(),
                                          'titulo'=>$noticia->getTitulo(),
                                          'tipo'=>$noticia->getTipoBiblioteca()->getNombre(),
                                          'tid'=>$noticia->getTipoBiblioteca()->getId());
                    }
                    else if ($noticia->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['articulo']) {
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

    }

    public function detalleAction($biblioteca_id)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

       
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {

            $biblioteca = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($biblioteca_id);
            $anuncios=array();
            $hoy = new \DateTime();
            $now = strtotime($hoy->format('d-m-Y'));
            $videos = array();
            $audios = array();
            $pdfs = array();
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

            if ($biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['video']) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoBiblioteca = :biblioteca
                                           AND n.tipoNoticia = :noticia_id
                                           AND n.id != :id
                                           AND n.pdf IS NOT NULL')
                            ->setMaxResults(3)
                            ->setParameters(array('biblioteca'=> $yml['parameters']['tipo_biblioteca']['video'],
                                                  'noticia_id'=> $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
                                                  'id'=> $biblioteca_id));
                $anuncios = $query->getResult();

                foreach($anuncios as $anuncio){
                    $fecha_i = strtotime($anuncio->getFechaPublicacion()->format('d-m-Y'));
                    $fecha_f = strtotime($anuncio->getFechaVencimiento()->format('d-m-Y'));
                    if ($now >= $fecha_i && $now < $fecha_f) 
                    {
                        $videos[] =array('id'=>$anuncio->getId(),
                                         'titulo'=>$anuncio->getTitulo(),
                                         'tipo'=>$anuncio->getTipoBiblioteca()->getNombre(),
                                         'tid'=>$anuncio->getTipoBiblioteca()->getId());
                    }
                }

            }elseif ($biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['podcast']) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoBiblioteca = :biblioteca
                                           AND n.tipoNoticia = :noticia_id
                                           AND n.id != :id
                                           AND n.pdf IS NOT NULL')
                            ->setMaxResults(3)
                            ->setParameters(array('biblioteca'=> $yml['parameters']['tipo_biblioteca']['podcast'],
                                                  'noticia_id'=> $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
                                                  'id'=> $biblioteca_id));
                $anuncios = $query->getResult();

                foreach($anuncios as $anuncio){
                    $fecha_i = strtotime($anuncio->getFechaPublicacion()->format('d-m-Y'));
                    $fecha_f = strtotime($anuncio->getFechaVencimiento()->format('d-m-Y'));
                    if ($now >= $fecha_i && $now < $fecha_f) 
                    {
                        $audios[] =array('id'=>$anuncio->getId(),
                                         'titulo'=>$anuncio->getTitulo(),
                                         'tipo'=>$anuncio->getTipoBiblioteca()->getNombre(),
                                         'tid'=>$anuncio->getTipoBiblioteca()->getId());
                    }
                }
            }elseif ($biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['articulo'] || $biblioteca->getTipoBiblioteca()->getId() == $yml['parameters']['tipo_biblioteca']['libro']) {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                           WHERE n.tipoBiblioteca IN (:biblioteca)
                                           AND n.tipoNoticia = :noticia_id
                                           AND n.id != :id
                                           AND n.pdf IS NOT NULL')
                            ->setMaxResults(3)
                            ->setParameters(array('biblioteca'=> array($yml['parameters']['tipo_biblioteca']['articulo'],$yml['parameters']['tipo_biblioteca']['libro']),
                                                  'noticia_id'=> $yml['parameters']['tipo_noticias']['biblioteca_virtual'],
                                                  'id'=> $biblioteca_id));
                $anuncios = $query->getResult();

                foreach($anuncios as $anuncio){
                    $fecha_i = strtotime($anuncio->getFechaPublicacion()->format('d-m-Y'));
                    $fecha_f = strtotime($anuncio->getFechaVencimiento()->format('d-m-Y'));
                    if ($now >= $fecha_i && $now < $fecha_f) 
                    {
                        $pdfs[] =array('id'=>$anuncio->getId(),
                                         'titulo'=>$anuncio->getTitulo(),
                                         'tipo'=>$anuncio->getTipoBiblioteca()->getNombre(),
                                         'tid'=>$anuncio->getTipoBiblioteca()->getId());
                    }
                }
            }
        }else {
            return $this->redirectToRoute('_login');
        }

        //return new Response (var_dump($anuncios));

        return $this->render('LinkFrontendBundle:Biblioteca:detalle.html.twig', array('biblioteca' => $biblioteca,
                                                                                      'videos' => $videos,
                                                                                      'audios' => $audios,
                                                                                      'pdfs' => $pdfs));
    }

}