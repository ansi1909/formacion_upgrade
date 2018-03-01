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
        /*$session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini'))
      	{
        	return $this->redirectToRoute('_login');
      	}
        $f->setRequest($session->get('sesion_id'));

      	if ($this->container->get('session')->isStarted())
      	{
        	
        	$datos = $session;
			$empresa_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
			$bienvenida = $empresa_obj->getBienvenida();

			$usuario_session = $session->get('usuario');
			$usuario_id = $datos_usuario['nombre'];

			$query_actividad = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
												 WHERE ar.usuario = :usuario_id
												 ORDER BY u.id DESC')
		                          ->setParameter('usuario_id', $session->get('usuario')['id'])
		                          ->setMaxResults(3);
            $actividad_reciente = $query_actividad->getResult();

            if(count($actividad_reciente) > 1){
				$reciente = 1;
            }else{
				$reciente = 0;
            }
      	}
      	else {
      		return $this->redirectToRoute('_login');
      	}*/
      	$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
      	$temporal = array();

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
											 ORDER BY ar.id DESC')
	                          ->setParameter('usuario_id', $usuario->getId())
	                          ->setMaxResults(3);
	    $actividad_reciente = $query_actividad->getResult();

	    if(count($actividad_reciente) > 1){
			$reciente = 1;
        }else{
			$reciente = 0;
        }

		$datos = array('usuario'=>$datosUsuario,
                       'empresa'=>$datosEmpresa,
                       'paginas'=>$datosPagina);


        return $this->render('LinkFrontendBundle:Default:index.html.twig', array('sesion' => $datos,
                                                                                 'bienvenida' => $bienvenida,
                                                                                 'reciente' => $reciente,
                                                                                 'actividad_reciente' => $actividad_reciente));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    

}