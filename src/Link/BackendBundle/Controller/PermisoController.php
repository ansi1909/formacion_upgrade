<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminAplicacion;

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

        $em = $this->getDoctrine()->getManager();

        $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($rol_id);

        if ($request->getMethod() == 'POST')
        {
            /*
            // Se guardan los servicios seleccionados
            $total = $request->request->get('total');
            $servicios = $request->request->get('servicios');
            $hoy = new \DateTime('now');

            // Se buscan los servicio contratados para eliminar los que no fueron seleccionados
            $scs = $em->getRepository('PsTelemedBundle:ServicioContratado')->findByOrganizacion($organizacion_id);

            foreach ($scs as $sc)
            {
                if (!in_array($sc->getServicio()->getId(), $servicios))
                {
                    $em->remove($sc);
                    $em->flush();
                }
            }

            // Ordenamos el arreglo de servicios de menor a mayor
            asort($servicios);

            // Contrato de servicios
            foreach ($servicios as $servicio)
            {
                $indefinido = $request->request->get('indefinido'.$servicio);
                $vencimiento = $request->request->get('vencimiento'.$servicio);
                if ($vencimiento)
                {
                    $v_array = explode("/", $vencimiento);
                    $d = $v_array[0];
                    $m = $v_array[1];
                    $a = $v_array[2];
                    $vencimiento = "$a-$m-$d";
                }

                $servicio_bd = $em->getRepository('PsTelemedBundle:Servicio')->find($servicio);
                $servicio_contratado = $em->getRepository('PsTelemedBundle:ServicioContratado')->findOneBy(array('servicio' => $servicio,
                                                                                                                 'organizacion' => $organizacion_id));
                
                if (!$servicio_contratado)
                {
                    $servicio_contratado = new ServicioContratado();
                    $servicio_contratado->setServicio($servicio_bd);
                    $servicio_contratado->setOrganizacion($organizacion);
                    $servicio_contratado->setEmision($hoy);
                }

                if ($indefinido || !$vencimiento)
                {
                    $servicio_contratado->setVigenciaIndefinida(true);
                    $servicio_contratado->setVencimiento(null);
                }
                else {
                    $servicio_contratado->setVigenciaIndefinida(false);
                    $servicio_contratado->setVencimiento(new \DateTime($vencimiento));
                }

                $em->persist($servicio_contratado);
                $em->flush();

                if ($servicio_bd->getSubservicio())
                {
                    // Se compara las fechas de vencimiento. Si el subservicio tiene fecha mayor que el servicio se actualiza éste último.
                    $subservicio_contratado = $em->getRepository('PsTelemedBundle:ServicioContratado')->findOneBy(array('servicio' => $servicio_bd->getSubservicio()->getId(),
                                                                                                                        'organizacion' => $organizacion_id));

                    if ($subservicio_contratado)
                    {
                        if ($indefinido)
                        {
                            $subservicio_contratado->setVigenciaIndefinida(true);
                            $subservicio_contratado->setVencimiento(null);
                        }
                        else {
                            if ($vencimiento && !$subservicio_contratado->getVigenciaIndefinida())
                            {
                                if ($vencimiento > $subservicio_contratado->getVencimiento()->format('Y-m-d'))
                                {
                                    $subservicio_contratado->setVencimiento(new \DateTime($vencimiento));
                                }
                            }
                        }
                        $em->persist($subservicio_contratado);
                        $em->flush();
                    }
                }

            }

            return $this->redirectToRoute('_serviciosContratados', array('organizacion_id' => $organizacion->getId()));
            */
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
                                               'icono' => $subapp->getIcono(),
                                               'checked' => $permiso_subaplicacion ? true : false,
                                               'display' => $permiso_aplicacion ? '' : 'style=display:none;');

                }

                $aplicaciones[] = array('id' => $app->getId(),
                                        'nombre' => $app->getNombre(),
                                        'icono' => $app->getIcono(),
                                        'checked' => $permiso_aplicacion ? true : false,
                                        'subaplicaciones' => $subaplicaciones);

            }

            //return new Response(var_dump($aplicaciones));

            return $this->render('LinkBackendBundle:Permiso:permisosRol.html.twig', array('rol' => $rol,
                                                                                          'aplicaciones' => $aplicaciones));

        }

    }

}
