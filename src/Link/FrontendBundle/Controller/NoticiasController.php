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
        
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
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

                    if($busqueda->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['noticia'])
                    {
                        $noticias[] =array('id'=>$busqueda->getId(),
                                       'titulo'=>$busqueda->getTitulo(),
                                       'fecha'=>$busqueda->getFechaPublicacion()->format('d/m/Y'),
                                       'tipo'=>$busqueda->getTipoNoticia()->getNombre(),
                                       'imagen'=>$busqueda->getImagen(),
                                       'tid'=>$busqueda->getTipoNoticia()->getId());

                    }elseif ($busqueda->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['novedad']) 
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

                if($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['noticia'])
                {
                    $noticias[] =array('id'=>$noticia->getId(),
                                   'titulo'=>$noticia->getTitulo(),
                                   'fecha'=>$noticia->getFechaPublicacion()->format('d/m/Y'),
                                   'tipo'=>$noticia->getTipoNoticia()->getNombre(),
                                   'imagen'=>$noticia->getImagen(),
                                   'tid'=>$noticia->getTipoNoticia()->getId());

                }elseif ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['novedad']) 
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

        return $this->render('LinkFrontendBundle:Noticias:index.html.twig', array('usuario_id' => $usuario_id,
                                                                                  'todos' => $todos,
                                                                                  'noticias' => $noticias,
                                                                                  'novedades' => $novedades));

    }

    public function detalleAction($noticia_id)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $noticia = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
        $noticias=array();

        if ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['noticia']) {
            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoNoticia = :noticia
                                       AND n.id != :noticia_id')
                        ->setMaxResults(3)
                        ->setParameters(array('noticia'=> $yml['parameters']['tipo_noticias']['noticia'],
                                              'noticia_id'=> $noticia_id));
            $noticias = $query->getResult();
        }elseif ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['novedad']) {
            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoNoticia = :noticia
                                       AND n.id != :noticia_id')
                        ->setMaxResults(3)
                        ->setParameters(array('noticia'=> $yml['parameters']['tipo_noticias']['novedad'],
                                              'noticia_id'=> $noticia_id));
            $noticias = $query->getResult();
        }

        return $this->render('LinkFrontendBundle:Noticias:detalle.html.twig', array('noticia' => $noticia,
                                                                                    'noticias'=> $noticias));

    }

}