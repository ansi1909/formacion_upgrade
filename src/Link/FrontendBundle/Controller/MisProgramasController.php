<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class MisProgramasController extends Controller
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
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $datos = $session;
            $empresa_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
            $bienvenida = $empresa_obj->getBienvenida();

            // buscando las últimas 3 interacciones del usuario donde la página no este completada
            $query_actividad = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                 WHERE ar.usuario = :usuario_id
                                                 AND ar.estatusPagina != :completada
                                                 ORDER BY ar.id DESC')
                                  ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                        'completada' => $yml['parameters']['estatus_pagina']['completada']))
                                  ->setMaxResults(3);
            $actividadreciente = $query_actividad->getResult();

            $actividad_reciente = array();
            // Si tiene actividades
            if(count($actividadreciente) >=  1){
                $reciente = 1;
                foreach ($actividadreciente as $ar) {
                    // Si la actividad reciente es con una pagina hija
                    if($ar->getPagina()->getPagina()){
                        // buscamos la página padre
                        $datos_log_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getPagina()->getId()));
                        // buscamos los datos de la pagina contra empresa para obntener la fecha de vencimiento
                        $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getPagina()->getId()));

                        // creamos variables para añadir al array
                        $titulo_padre = $ar->getPagina()->getPagina()->getNombre();
                        $titulo_hijo = $ar->getPagina()->getNombre();
                        $imagen = $ar->getPagina()->getPagina()->getFoto();
                        $categoria = $ar->getPagina()->getCategoria()->getNombre();
                        $porcentaje = $datos_log_padre->getPorcentajeAvance();
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                    // Si la actividad reciente es con una pagina padre
                    }else{
                        
                        // buscamos los datos de la pagina contra empresa para obntener la fecha de vencimiento
                        $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getId()));

                        // creamos variables para añadir al array
                        $titulo_padre = $ar->getPagina()->getNombre();
                        $titulo_hijo = '';
                        $imagen = $ar->getPagina()->getFoto();
                        $categoria = $ar->getPagina()->getNombre();
                        $porcentaje = $ar->getPorcentajeAvance();
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
                    }

                    // Validando si el programa tiene imagen para asignar una por defecto
                    if($imagen){
                        $imagen = $imagen;
                    }else{
                        $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                    }

                    $actividad_reciente[]= array('id'=>$ar->getPagina()->getId(),
                                                 'titulo_padre'=>$titulo_padre,
                                                 'titulo_hijo'=>$titulo_hijo,
                                                 'imagen'=>$imagen,
                                                 'categoria'=>$categoria,
                                                 'fecha_vencimiento'=>$fecha_vencimiento,
                                                 'porcentaje'=>$porcentaje);
                }
            // No tiene actividades
            }else{
                $reciente = 0;
            }

            $programas_aprobados = array();
            $programas_en_curso = array();
            
            // Convertimos los id de las paginas de la sesion en un nuevo array
            $paginas_ids = array();
            foreach ($session->get('paginas') as $pg) {
                $paginas_ids[] = $pg['id'];
            }

            // Buscamos los programas finalizados y disponibles en la sesion
            $group_query_completados = $em->createQuery('SELECT cp.id as paginaid, cp.descripcion as descripcion, cp.nombre as nombrepagina, cp.foto as foto
                                                        FROM LinkComunBundle:CertiPagina cp, 
                                                             LinkComunBundle:CertiPaginaLog cpl
                                                        WHERE cp.id IN (:pagina)
                                                        AND cpl.pagina = cp.id
                                                        AND cpl.usuario = :usuario
                                                        AND cpl.estatusPagina = :estatus
                                                        ORDER BY cp.id ASC')
                                          ->setParameters(array('usuario' => $session->get('usuario')['id'],
                                                                'estatus' => $yml['parameters']['estatus_pagina']['completada'],
                                                                'pagina' => $paginas_ids));
            $prog_completados = $group_query_completados->getResult();

            // Buscamos los programas en curso y disponibles en la sesion
            $query_en_curso = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                WHERE ar.usuario = :usuario_id
                                                AND ar.estatusPagina != :completada
                                                ORDER BY ar.id DESC')
                                ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                      'completada' => $yml['parameters']['estatus_pagina']['completada']));
            $prog_en_curso = $query_en_curso->getResult();

            $group_query_sin_actividad = $em->createQuery('SELECT cp.id as paginaid, cp.descripcion as descripcion, cp.nombre as nombrepagina, cp.foto as foto
                                                        FROM LinkComunBundle:CertiPagina cp
                                                        WHERE cp.id IN (:pagina)
                                                        AND NOT EXISTS (SELECT l FROM LinkComunBundle:CertiPaginaLog l 
                                                                       WHERE l.usuario = :usuario
                                                                       AND l.pagina = cp.id) 
                                                        ORDER BY cp.id ASC')
                                          ->setParameters(array('usuario' => $session->get('usuario')['id'],
                                                                'pagina' => $paginas_ids));
            $prog_sin_actividad = $group_query_sin_actividad->getResult();
            
            foreach ($prog_completados as $pg) {

                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $pg['paginaid']));

                if($datos_certi_pagina->getAcceso()){
                    // aprobado y con acceso de seguir viendo
                    $continuar = 2;
                }else{
                    // aprobado y sin poder ver solo descargar notas y certificados
                    $continuar = 3;
                }
                    
                if($pg['foto']){
                    $imagen = $pg['foto'];
                }else{
                    $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                }
               
                $programas_aprobados[]= array('id'=>$pg['paginaid'],
                                                'nombre'=>$pg['nombrepagina'],
                                                'imagen'=>$imagen,
                                                'descripcion'=>$pg['descripcion'],
                                                'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                                'continuar'=>$continuar);
                
            }

            foreach ($prog_en_curso as $pg) {

                $continuar = 0;
                    
                // buscamos la página
                $datos_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pg['paginaid']);

                // buscamos los datos de la pagina contra empresa para obntener la fecha de vencimiento
                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                          'pagina' => $pg['paginaid']));

                if($pg['foto']){
                    $imagen = $pg['foto'];
                }else{
                    $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                }

                // creamos variables para añadir al array
                $titulo_padre = $pg['nombrepagina'];
                $titulo_hijo = '';
                $imagen = $imagen;
                $categoria = $datos_pagina->getCategoria()->getNombre();
                $porcentaje = $datos_pagina->getPorcentajeAvance() ;
                $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                $programas_en_curso[]= array('id'=>$pg['paginaid'],
                                             'titulo_padre'=>$titulo_padre,
                                             'titulo_hijo'=>$titulo_hijo,
                                             'imagen'=>$imagen,
                                             'categoria'=>$categoria,
                                             'fecha_vencimiento'=>$fecha_vencimiento,
                                             'continuar'=>$continuar,
                                             'porcentaje'=>$porcentaje);
                
            }

            foreach ($prog_sin_actividad as $pg) {

                $continuar = 0;
                    
                // buscamos la página
                $datos_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pg['paginaid']);

                // buscamos los datos de la pagina contra empresa para obntener la fecha de vencimiento
                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $pg['paginaid']));

                if($pg['foto']){
                    $imagen = $pg['foto'];
                }else{
                    $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                }

                // creamos variables para añadir al array
                $titulo_padre = $pg['nombrepagina'];
                $titulo_hijo = '';
                $imagen = $imagen;
                $categoria = $datos_pagina->getCategoria()->getNombre();
                $porcentaje = 0;
                $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
                
                $programas_en_curso[]= array('id'=>$pg['paginaid'],
                                             'titulo_padre'=>$titulo_padre,
                                             'titulo_hijo'=>$titulo_hijo,
                                             'imagen'=>$imagen,
                                             'categoria'=>$categoria,
                                             'fecha_vencimiento'=>$fecha_vencimiento,
                                             'continuar'=>$continuar,
                                             'porcentaje'=>$porcentaje);
                
            }
        }
        else {
            return $this->redirectToRoute('_login');
        }

        return $this->render('LinkFrontendBundle:MisProgramas:index.html.twig', array('reciente' => $reciente,
                                                                                      'actividad_reciente' => $actividad_reciente,
                                                                                      'programas_aprobados' => $programas_aprobados,
                                                                                      'programas_en_curso' => $programas_en_curso));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    

}