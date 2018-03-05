<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_login');
        }
        $f->setRequest($session->get('sesion_id'));

        if ($this->container->get('session')->isStarted())
        {
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $datos = $session;
            $empresa_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
            $bienvenida = $empresa_obj->getBienvenida();

            $query_actividad = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                 WHERE ar.usuario = :usuario_id
                                                 AND ar.estatusPagina != :completada
                                                 ORDER BY ar.id DESC')
                                  ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                        'completada' => $yml['parameters']['estatus_pagina']['completada']))
                                  ->setMaxResults(3);
            $actividadreciente = $query_actividad->getResult();

            $actividad_reciente = array();
            if(count($actividadreciente) >= 1){
                $reciente = 1;
                foreach ($actividadreciente as $ar) {

                    if($ar->getPagina()->getPagina()){

                        $datos_log_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getPagina()->getId()));
                        $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getPagina()->getId()));
                        $titulo_padre = $ar->getPagina()->getPagina()->getNombre();
                        $titulo_hijo = $ar->getPagina()->getNombre();
                        $imagen = $ar->getPagina()->getPagina()->getFoto();
                        $categoria = $ar->getPagina()->getCategoria()->getNombre();
                        $porcentaje = $datos_log_padre->getPorcentajeAvance();
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                    }else{
                        
                        $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getId()));
                        $titulo_padre = $ar->getPagina()->getNombre();
                        $titulo_hijo = '';
                        $imagen = $ar->getPagina()->getFoto();
                        $categoria = $ar->getPagina()->getNombre();
                        $porcentaje = $ar->getPorcentajeAvance();
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
                    }

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
            }else{
                $reciente = 0;
            }

            $programas_disponibles = array();
            foreach ($session->get('paginas') as $pg) {

                $pag_obj = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pg['id']);

                if(!$pag_obj->getPagina()){

                    $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                     'pagina' => $pg['id']));

                    $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                        'pagina' => $pg['id']));
                    if($datos_log){
                        $cotinuar = 1;
                    }else{
                        $cotinuar = 0;
                    }
                    if($pg['foto']){
                        $imagen = $pg['foto'];
                    }else{
                        $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                    }
                    $programas_disponibles[]= array('id'=>$pg['id'],
                                                    'nombre'=>$pg['nombre'],
                                                    'imagen'=>$imagen,
                                                    'descripcion'=>$pag_obj->getDescripcion(),
                                                    'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                                    'continuar'=>$cotinuar);
                }
                
            }
        }
        else {
            return $this->redirectToRoute('_login');
        }
        /*$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $datos = array();

        $usuario_id = 2;
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
        $paginaEmpresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->findAll();

        $datosPagina = array();
        $roles_bk = array();
        $roles_bk[] = $yml['parameters']['rol']['participante'];
        $roles_bk[] = $yml['parameters']['rol']['tutor'];
        $roles_ok = 0;
        $participante = false;
        $tutor = false;

        $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
                    ->setParameter('usuario_id', $usuario->getId());
        $roles_usuario_db = $query->getResult();
        
        foreach ($roles_usuario_db as $rol_usuario)
        {
            // Verifico si el rol está dentro de los roles de backend
            if (in_array($rol_usuario->getRol()->getId(), $roles_bk))
            {
                $roles_ok = 1;
            }
            if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['participante'])
            {
                $participante = true;
            }
            if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['tutor'])
            {
                $tutor = true;
            }
        }

        $datosUsuario = array('id' => $usuario->getId(),
                              'nombre' => $usuario->getNombre(),
                              'apellido' => $usuario->getApellido(),
                              'correo' => $usuario->getCorreoPersonal(),
                              'foto' => $usuario->getFoto(),
                              'participante' => $participante,
                              'tutor' => $tutor);
        
        // Se setea los datos de la empresa
        $datosEmpresa = array('id' => $usuario->getEmpresa()->getId(),
                              'nombre' => $usuario->getEmpresa()->getNombre(),
                              'chat' => $usuario->getEmpresa()->getChatActivo(),
                              'webinar' => $usuario->getEmpresa()->getWebinar(),
                              'platilla' => 'twig para el layout principal',
                              'logo' => 'url del logo de la empresa');

        foreach ($paginaEmpresa as $pagina)
        {

            $datosPagina[] = array('id' => $pagina->getId(),
                                    'nombre' => $pagina->getNombre(),
                                    'categoria' => $pagina->getCategoria(),
                                    'foto' => $pagina->getFoto(),
                                    'tiene_evaluacion' => true,
                                    'sub_paginas' =>  ''
                                    );
        }

        $bienvenida = $usuario->getEmpresa()->getBienvenida();

        $query_actividad = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                             WHERE ar.usuario = :usuario_id
                                             AND ar.estatusPagina != :completada
                                             ORDER BY ar.id DESC')
                              ->setParameters(array('usuario_id' => $usuario->getId(),
                                                    'completada' => $yml['parameters']['estatus_pagina']['completada']))
                              ->setMaxResults(3);
        $actividadreciente = $query_actividad->getResult();

        
        $actividad_reciente = array();
        if(count($actividadreciente) > 1){
            $reciente = 1;
            foreach ($actividadreciente as $ar) {

                if($ar->getPagina()->getPagina()){

                    $datos_log_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $usuario->getId(),
                                                                                                                              'pagina' => $ar->getPagina()->getPagina()->getId()));
                    $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $datosEmpresa['id'],
                                                                                                                                     'pagina' => $ar->getPagina()->getPagina()->getId()));
                    $titulo_padre = $ar->getPagina()->getPagina()->getNombre();
                    $titulo_hijo = $ar->getPagina()->getNombre();
                    $imagen = $ar->getPagina()->getPagina()->getFoto();
                    $categoria = $ar->getPagina()->getCategoria()->getNombre();
                    $porcentaje = $datos_log_padre->getPorcentajeAvance();
                    $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                }else{

                    $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $datosEmpresa['id'],
                                                                                                                                  'pagina' => $ar->getPagina()->getId()));
                    $titulo_padre = $ar->getPagina()->getNombre();
                    $titulo_hijo = '';
                    $imagen = $ar->getPagina()->getFoto();
                    $categoria = $ar->getPagina()->getNombre();
                    $porcentaje = $ar->getPorcentajeAvance();
                    $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
                }

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
        }else{
            $reciente = 0;
        }

        $datos = array('usuario'=>$datosUsuario,
                       'empresa'=>$datosEmpresa,
                       'paginas'=>$datosPagina);

        $programas_disponibles = array();
        foreach ($datos['paginas'] as $pg) {

            $pag_obj = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pg['id']);

            if(!$pag_obj->getPagina()){

                $datos_cert_pag = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $datos['empresa']['id'],
                                                                                                                                 'pagina' => $pg['id']));
               
                $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $datos['usuario']['id'],
                                                                                                                    'pagina' => $pg['id']));
                if($datos_log){
                    $cotinuar = 1;
                }else{
                    $cotinuar = 0;
                }
                if($pg['foto']){
                    $imagen = $pg['foto'];
                }else{
                    $imagen = 'http://localhost/formacion2.0/web/front/assets/img/liderazgo.png';
                }
                $programas_disponibles[]= array('id'=>$pg['id'],
                                                'nombre'=>$pg['nombre'],
                                                'imagen'=>$imagen,
                                                'descripcion'=>$pag_obj->getDescripcion(),
                                                'fecha_vencimiento'=>$f->timeAgo($datos_cert_pag->getFechaVencimiento()->format("Y/m/d")),
                                                'continuar'=>$cotinuar);
            }
            
        }*/

        return $this->render('LinkFrontendBundle:Default:index.html.twig', array('bienvenida' => $bienvenida,
                                                                                 'reciente' => $reciente,
                                                                                 'actividad_reciente' => $actividad_reciente,
                                                                                 'programas_disponibles' => $programas_disponibles));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    

}