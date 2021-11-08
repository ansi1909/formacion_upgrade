<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminNoticia;

class NovedadController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $app_id = $session->get('app_id');
        $encabezado = $app_id == $yml['parameters']['aplicacion']['biblioteca'] ? $this->get('translator')->trans('Biblioteca virtual') : $this->get('translator')->trans('Noticias y Novedades');
        $noticias = array();

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $qb = $em->createQueryBuilder();
        $qb->select('n')
           ->from('LinkComunBundle:AdminNoticia', 'n')
           ->orderBy('n.fechaRegistro', 'ASC');
        
        $parametros['tipo_noticia_id'] = $yml['parameters']['tipo_noticias']['biblioteca_virtual'];
        if ($app_id == $yml['parameters']['aplicacion']['biblioteca'])
        {
            $qb->andWhere('n.tipoNoticia = :tipo_noticia_id');
        }
        else {
            $qb->andWhere('n.tipoNoticia != :tipo_noticia_id');
        }
        if ($usuario->getEmpresa())
        {
            $qb->andWhere('n.empresa = :empresa_id');
            $parametros['empresa_id'] = $usuario->getEmpresa()->getId();
        }
        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $noticiasdb = $query->getResult();

        foreach ($noticiasdb as $noticia)
        {
            $noticias[] = array('id' => $noticia->getId(),
                                'empresa' => $noticia->getEmpresa()->getNombre(),
                                'tipoNoticia' => $noticia->getTipoNoticia()->getNombre(),
                                'tipoBiblioteca' => $noticia->getTipoBiblioteca() ? $noticia->getTipoBiblioteca()->getNombre() : '',
                                'titulo' => $noticia->getTitulo(),
                                'fechaRegistro' => $noticia->getFechaRegistro()->format('d/m/Y'),
                                'fechaPublicacion' => $noticia->getFechaPublicacion()->format('d/m/Y'),
                                'fechaVencimiento' => $noticia->getFechaVencimiento()->format('d/m/Y'),
                                'delete_disabled' => $f->linkEliminar($noticia->getId(),'AdminNoticia'));
        }

        return $this->render('LinkBackendBundle:Novedad:index.html.twig', array('noticias' => $noticias,
                                                                                'usuario' => $usuario,
                                                                                'encabezado' => $encabezado));

    }

    public function registroBibliotecaAction($noticia_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $nuevo = false;
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $query = $em->createQuery('SELECT tb FROM LinkComunBundle:AdminTipoBiblioteca tb
                                   ORDER BY tb.nombre ASC');
        $tipoBibliotecas = $query->getResult();

        $query = $em->createQuery('SELECT e FROM LinkComunBundle:AdminEmpresa e
                                   WHERE e.activo = :activo ORDER BY e.nombre ASC')
                    ->setParameters(array('activo' => true));
        $empresas = $query->getResult();

        $usuario_empresa = 0;
        if($session->get('administrador')==false)//si no es administrador
        {
            if ($usuario->getEmpresa()) 
                $usuario_empresa = $usuario->getEmpresa()->getId(); 
        }

        if ($noticia_id)
        {
            $biblioteca = $em->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
        }
        else {
            $biblioteca = new AdminNoticia();
            $biblioteca->setFechaRegistro(new \DateTime('now'));
            $nuevo = true;
        }

        if ($request->getMethod() == 'POST')
        {

            $recurso = '';
            $empresa_id = $request->request->get('empresa_id');
            $titulo = trim($request->request->get('titulo'));
            $autor = trim($request->request->get('autor')) ? trim($request->request->get('autor')) : '';
            $tema = trim($request->request->get('tema')) ? trim($request->request->get('tema')) : '';
            $pdf = trim($request->request->get('pdf'));
            $video = trim($request->request->get('video'));
            $audio = trim($request->request->get('audio'));
            $imagen = trim($request->request->get('imagen'));
            $contenido = trim($request->request->get('contenido'));
            $fecha_vencimiento = trim($request->request->get('fecha_vencimiento'));
            $fv = explode("/", $fecha_vencimiento);
            $vencimiento = $fv[2].'-'.$fv[1].'-'.$fv[0];
            
            $fecha_publicacion = trim($request->request->get('fecha_publicacion'));
            $fp = explode("/", $fecha_publicacion);
            $publicacion = $fp[2].'-'.$fp[1].'-'.$fp[0];
            $tipo_biblioteca_id = $request->request->get('tipo_biblioteca_id');
            
            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $tipoBiblioteca = $em->getRepository('LinkComunBundle:AdminTipoBiblioteca')->find($tipo_biblioteca_id);
            $tipoNoticia = $em->getRepository('LinkComunBundle:AdminTipoNoticia')->find($yml['parameters']['tipo_noticias']['biblioteca_virtual']);

            $biblioteca->setUsuario($usuario);
            $biblioteca->setEmpresa($empresa);
            $biblioteca->setTipoNoticia($tipoNoticia);
            $biblioteca->setTipoBiblioteca($tipoBiblioteca);
            $biblioteca->setAutor($autor);
            $biblioteca->setTema($tema);
            $biblioteca->setTitulo($titulo);
            $biblioteca->setFechaVencimiento(new \DateTime($vencimiento));
            $biblioteca->setFechaPublicacion(new \DateTime($publicacion));
            if ($tipo_biblioteca_id == $yml['parameters']['tipo_biblioteca']['video']) {
                $recurso = $video;
            }
            else if ($tipo_biblioteca_id == $yml['parameters']['tipo_biblioteca']['podcast']) {
                $recurso = $audio;
            }
            else{
                $recurso = $pdf;
            }
            $biblioteca->setPdf($recurso);
            $biblioteca->setImagen($imagen);
            $biblioteca->setContenido($contenido);
            $em->persist($biblioteca);
            $em->flush();

            // Generación de notificaciones a los usuario de la empresa
            $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                                       WHERE u.activo = :activo 
                                       AND u.empresa = :empresa_id')
                        ->setParameters(array('activo' => true,
                                              'empresa_id' => $empresa->getId()));
            $usuarios = $query->getResult();

            $descripcion = $this->get('translator')->trans('Ha sido publicado').' '.$titulo.' '.$this->get('translator')->trans('en la biblioteca').'.';
            $fecha_alarma = new \DateTime('now');
            foreach ($usuarios as $usuario){
                if($nuevo == true)
                {
                    $f->newAlarm($yml['parameters']['tipo_alarma']['biblioteca'], $descripcion, $usuario, $biblioteca->getId(),$fecha_alarma);
                }
            }

            return $this->redirectToRoute('_showBiblioteca', array('biblioteca_id' => $biblioteca->getId()));

        }

        return $this->render('LinkBackendBundle:Novedad:registroBiblioteca.html.twig', array('empresas' => $empresas,
                                                                                             'tipoBibliotecas' => $tipoBibliotecas,
                                                                                             'biblioteca' => $biblioteca,
                                                                                             'usuario_empresa' => $usuario_empresa ));

    }

    public function mostrarBibliotecaAction($biblioteca_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $biblioteca = $em->getRepository('LinkComunBundle:AdminNoticia')->find($biblioteca_id);

        return $this->render('LinkBackendBundle:Novedad:mostrarBiblioteca.html.twig', array('biblioteca' => $biblioteca));

    }

    public function registroNovedadAction($noticia_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $nuevo = false;

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        
        $query = $em->createQuery('SELECT e FROM LinkComunBundle:AdminEmpresa e
                                   WHERE e.activo = :activo ORDER BY e.nombre ASC')
                    ->setParameters(array('activo' => true));
        $empresas = $query->getResult();

        $query = $em->createQuery('SELECT tn FROM LinkComunBundle:AdminTipoNoticia tn
                                   WHERE tn.id != :tipo ')
                    ->setParameter('tipo', $yml['parameters']['tipo_noticias']['biblioteca_virtual']);
        $tipoNoticias = $query->getResult();

        $usuario_empresa = 0;
        if($session->get('administrador')==false)//si no es administrador
        {
            if ($usuario->getEmpresa()) 
                $usuario_empresa = $usuario->getEmpresa()->getId(); 
        }

        if ($noticia_id)
        {
            $noticia = $em->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
        }
        else {
            $noticia = new AdminNoticia();
            $noticia->setFechaRegistro(new \DateTime('now'));
            $nuevo = true;
        }

        if ($request->getMethod() == 'POST')
        {

            if($usuario_empresa != 0)
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
            $tema = trim($request->request->get('tema')) ? trim($request->request->get('tema')) : '';

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
            $noticia->setTema($tema);
            $noticia->setPdf($pdf);
            $noticia->setImagen($imagen);
            $noticia->setResumen($resumen);
            $noticia->setContenido($contenido);
            $noticia->setFechaVencimiento(new \DateTime($vencimiento));
            $noticia->setFechaPublicacion(new \DateTime($publicacion));
            $em->persist($noticia);
            $em->flush();

            $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                                       WHERE u.activo = :activo 
                                       AND u.empresa = :empresa_id')
                        ->setParameters(array('activo' => true,
                                              'empresa_id' => $empresa->getId()));
            $usuarios = $query->getResult();

            $fecha_alarma = new \DateTime('now');

            foreach($usuarios as $usuario){

                if ($tipoNoticia->getId() == $yml['parameters']['tipo_noticias']['noticia'] ) {
                   if($nuevo == true)
                   {
                        $descripcion= 'Ha sido publicado una nueva noticia:  '. $titulo;
                        $f->newAlarm($yml['parameters']['tipo_alarma']['noticia'], $descripcion, $usuario, $noticia->getId(), $fecha_alarma); 
                   }
                }
                elseif ($tipoNoticia->getId() == $yml['parameters']['tipo_noticias']['novedad'] ) {
                    if($nuevo == true)
                    {
                        $descripcion= 'Ha sido publicado una nueva novedad:  '. $titulo;
                        $f->newAlarm($yml['parameters']['tipo_alarma']['novedad'], $descripcion, $usuario, $noticia->getId(), $fecha_alarma); 
                    }
                }
                
            }

            return $this->redirectToRoute('_showNovedad', array('noticia_id' => $noticia->getId()));

        }
        
        return $this->render('LinkBackendBundle:Novedad:registroNovedad.html.twig', array('empresas' => $empresas,
                                                                                          'tipoNoticias' => $tipoNoticias,
                                                                                          'noticia' => $noticia,
                                                                                          'usuario_empresa' => $usuario_empresa));

    }

   public function mostrarNoticiaNovedadAction($noticia_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $noticia = $em->getRepository('LinkComunBundle:AdminNoticia')->find($noticia_id);
        
        return $this->render('LinkBackendBundle:Novedad:mostrarNovedad.html.twig', array('noticia' => $noticia));

    }

}