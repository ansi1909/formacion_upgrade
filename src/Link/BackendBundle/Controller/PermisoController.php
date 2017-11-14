<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminPermiso;

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

    public function permisosRolAction($rol_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini'))
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

        $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($rol_id);

        if ($request->getMethod() == 'POST')
        {
            
            // Se guardan las aplicaciones seleccionadas
            $aplicaciones = $request->request->get('aplicaciones');
            
            // Se buscan las aplicaciones con acceso para eliminar las que no fueron seleccionadas
            $permisos = $em->getRepository('LinkComunBundle:AdminPermiso')->findByRol($rol_id);

            foreach ($permisos as $permiso)
            {
                if (!in_array($permiso->getAplicacion()->getId(), $aplicaciones))
                {
                    $em->remove($permiso);
                    $em->flush();
                }
            }

            // Ordenamos el arreglo de aplicaciones de menor a mayor
            asort($aplicaciones);

            foreach ($aplicaciones as $aplicacion_id)
            {
                
                $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($aplicacion_id);
                $permiso = $em->getRepository('LinkComunBundle:AdminPermiso')->findOneBy(array('aplicacion' => $aplicacion_id,
                                                                                               'rol' => $rol_id));
                
                if (!$permiso)
                {
                    $permiso = new AdminPermiso();
                    $permiso->setAplicacion($aplicacion);
                    $permiso->setRol($rol);
                    $em->persist($permiso);
                    $em->flush();
                }

            }

            return $this->redirectToRoute('_permisos', array('app_id' => $session->get('app_id')));
            
        }
        else {

            $aplicaciones = array();
            $i = 0;

            // Todas las aplicaciones principales
            $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                        WHERE a.aplicacion IS NULL 
                                        ORDER BY a.id ASC");
            $apps = $query->getResult();

            foreach ($apps as $app)
            {

                $permiso_aplicacion = $em->getRepository('LinkComunBundle:AdminPermiso')->findOneBy(array('aplicacion' => $app->getId(),
                                                                                                          'rol' => $rol_id));

                $subaplicaciones = array();

                // Subaplicaciones
                $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                            WHERE a.aplicacion = :aplicacion_id  
                                            ORDER BY a.id ASC")
                            ->setParameter('aplicacion_id', $app->getId());
                $subapps = $query->getResult();

                foreach ($subapps as $subapp)
                {

                    $permiso_subaplicacion = $em->getRepository('LinkComunBundle:AdminPermiso')->findOneBy(array('aplicacion' => $subapp->getId(),
                                                                                                                 'rol' => $rol_id));

                    $subaplicaciones[] = array('id' => $subapp->getId(),
                                               'nombre' => $subapp->getNombre(),
                                               'checked' => $permiso_subaplicacion ? true : false,
                                               'display' => $permiso_aplicacion ? '' : 'style=display:none;');

                }

                $aplicaciones[] = array('id' => $app->getId(),
                                        'nombre' => $app->getNombre(),
                                        'checked' => $permiso_aplicacion ? true : false,
                                        'subaplicaciones' => $subaplicaciones);

            }

            //return new Response(var_dump($aplicaciones));

            return $this->render('LinkBackendBundle:Permiso:permisosRol.html.twig', array('rol' => $rol,
                                                                                          'aplicaciones' => $aplicaciones));

        }

    }

}
