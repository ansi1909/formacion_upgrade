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

    public function indexAction(Request $request, $filtro )
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
        $filter = mb_strtolower(trim($filtro));
        if ($filter ==""){
            
            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                    WHERE n.empresa = :empresa_id
                    AND n.tipoBiblioteca IS NULL
                    AND n.fechaVencimiento >= :hoy')
            ->setParameters(   array(
                                        'empresa_id' => $empresa_id,
                                        'hoy' => date('Y-m-d')
            ));
            $noticias_db = $query->getResult();
        }else{
            $pattern = "/^([\¿\¡a-zA-Z0-9-ñÑáéíóúÁÉÍÓÚ])+((\s*)+([a-zA-Z0-9-ZñÑáéíóúÁÉÍÓÚ\?\!]*)*)+$/";
            if(preg_match($pattern, $filter)){    
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                            WHERE n.empresa = :empresa_id
                                            AND n.tipoBiblioteca IS NULL
                                            AND (LOWER (n.titulo) LIKE :filtro)
                                            AND n.fechaVencimiento >= :hoy
                                            ')
                ->setParameters(array(
                                            'empresa_id'  => $empresa_id,
                                            'filtro'      => '%'.$filter.'%',
                                            'hoy' => date('Y-m-d')
                                    )
                                
                                );

                $noticias_db = $query->getResult();
            }else{
                $noticias_db = [];
            }
        }


        
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
                                   'tid'=>$noticia->getTipoNoticia()->getId(),
                                   'imagen_default'=> ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['noticia'])? $yml['parameters']['tipo_noticias']['noticia_default'] : $yml['parameters']['tipo_noticias']['novedad_default']
                                   );

                if($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['noticia'])
                {
                    $noticias[] =array('id'=>$noticia->getId(),
                                   'titulo'=>$noticia->getTitulo(),
                                   'fecha'=>$noticia->getFechaPublicacion()->format('d/m/Y'),
                                   'tipo'=>$noticia->getTipoNoticia()->getNombre(),
                                   'imagen'=>$noticia->getImagen(),
                                   'tid'=>$noticia->getTipoNoticia()->getId(),
                                   'imagen_default'=> $yml['parameters']['tipo_noticias']['noticia_default']
                                );

                }elseif ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['novedad']) 
                {
                    $novedades[] =array('id'=>$noticia->getId(),
                                   'titulo'=>$noticia->getTitulo(),
                                   'fecha'=>$noticia->getFechaPublicacion()->format('d/m/Y'),
                                   'tipo'=>$noticia->getTipoNoticia()->getNombre(),
                                   'imagen'=>$noticia->getImagen(),
                                   'tid'=>$noticia->getTipoNoticia()->getId(),
                                   'imagen_default'=> $yml['parameters']['tipo_noticias']['novedad_default']
                                   );
                }
            }
        }

        return $this->render('LinkFrontendBundle:Noticias:index.html.twig', array(
                                                                                  'filtro'     => $filtro,
                                                                                  'usuario_id' => $usuario_id,
                                                                                  'todos' => $todos,
                                                                                  'noticias' => $noticias,
                                                                                  'novedades' => $novedades,
                                                                                  'recursos_disponibles' => $this->get('translator')->trans('Recursos disponibles') . ': ' . count($todos)
                                                                                ));

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
        $noticias = array();

        if ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['noticia']) 
        {
            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoNoticia = :noticia
                                       AND n.id != :noticia_id 
                                       AND n.empresa = :empresa_id')
                        ->setMaxResults(3)
                        ->setParameters(array('noticia' => $yml['parameters']['tipo_noticias']['noticia'],
                                              'noticia_id' => $noticia_id,
                                              'empresa_id' => $noticia->getEmpresa()->getId()));
            $noticias = $query->getResult();
        }
        elseif ($noticia->getTipoNoticia()->getId() == $yml['parameters']['tipo_noticias']['novedad']) 
        {
            $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
                                       WHERE n.tipoNoticia = :noticia
                                       AND n.id != :noticia_id
                                       AND n.empresa = :empresa_id')
                        ->setMaxResults(3)
                        ->setParameters(array('noticia' => $yml['parameters']['tipo_noticias']['novedad'],
                                              'noticia_id' => $noticia_id,
                                              'empresa_id' => $noticia->getEmpresa()->getId()));
            $noticias = $query->getResult();
        }

        return $this->render('LinkFrontendBundle:Noticias:detalle.html.twig', array('noticia' => $noticia,
                                                                                    'noticias'=> $noticias,
                                                                                     'nt' => $yml['parameters']['tipo_noticias']['noticia'],
                                                                                     'nv' => $yml['parameters']['tipo_noticias']['novedad'],
                                                                                     'nt_default' =>  $yml['parameters']['tipo_noticias']['noticia_default'],
                                                                                     'nv_default' =>  $yml['parameters']['tipo_noticias']['novedad_default']
                                                                                    ));

    }

    public function ajaxSearchNoticiaAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pattern = "/^([\¿\¡a-zA-Z0-9-ñÑáéíóúÁÉÍÓÚ])+((\s*)+([a-zA-Z0-9-ZñÑáéíóúÁÉÍÓÚ\?\!]*)*)+$/";
        $term = mb_strtolower($request->query->get('term'));
        $empresa_id = $session->get('empresa')['id'];
        $noticias = array();
		if (preg_match($pattern, $term)) {
			$query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNoticia n
										WHERE n.tipoBiblioteca IS NULL
                                        AND n.empresa = :empresa_id
										AND ( LOWER(n.titulo) LIKE :term )
										AND n.fechaVencimiento >= :hoy
										AND n.pdf IS NOT NULL')
				->setParameters(array(
					'term' => '%'.$term.'%',
					'hoy' => date('Y-m-d'),
					'empresa_id' => $empresa_id,
				));
                
			$noticias_db = $query->getResult();
		} else {
			$noticias_db = [];
		}

        foreach ($noticias_db as $noticia)
        {
            $noticias[] = array('id' => $noticia->getId(),
                             'label' => $noticia->getTitulo(),
                             'value' => $noticia->getTitulo());
        }
        $return = json_encode($noticias);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}