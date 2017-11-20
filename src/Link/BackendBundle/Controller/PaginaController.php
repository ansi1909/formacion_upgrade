<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPagina;

class PermisoController extends Controller
{
    public function indexAction($app_id)
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

        $roles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->findAll();

        $permisos = array();
        
        foreach ($roles as $rol)
        {

        	// Accesos de cada rol
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p 
            							JOIN p.aplicacion a 
                                        WHERE p.rol = :rol_id AND a.aplicacion IS NULL
                                        ORDER BY a.id ASC")
                        ->setParameter('rol_id', $rol->getId());
            $permisos_aplicacion = $query->getResult();

            $aplicaciones = array();
            foreach ($permisos_aplicacion as $pa)
            {

            	// Subaplicaciones a la que el rol tiene acceso
            	$query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p 
	            							JOIN p.aplicacion a 
	                                        WHERE p.rol = :rol_id AND a.aplicacion = :aplicacion_id
	                                        ORDER BY a.id ASC")
	                        ->setParameters(array('rol_id' => $rol->getId(),
	                        					  'aplicacion_id'=> $pa->getAplicacion()->getId()));
	            $permisos_subaplicacion = $query->getResult();

	            $subaplicaciones = array();
	            foreach ($permisos_subaplicacion as $psa)
	            {
	            	$subaplicaciones[] = array('id' => $psa->getAplicacion()->getId(),
		                                	   'nombre' => $psa->getAplicacion()->getNombre());
	            }
            	
            	$aplicaciones[] = array('id' => $pa->getAplicacion()->getId(),
	                                	'nombre' => $pa->getAplicacion()->getNombre(),
	                                	'subaplicaciones' => $subaplicaciones);

            }

            $permisos[] = array('rol_id' => $rol->getId(),
                                'rol_nombre' => $rol->getNombre(),
                                'aplicaciones' => $aplicaciones);

        }

        //return new Response(var_dump($permisos));

        return $this->render('LinkBackendBundle:Permiso:index.html.twig', array('permisos' => $permisos));

    }

}
