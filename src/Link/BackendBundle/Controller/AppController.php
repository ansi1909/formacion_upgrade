<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminAplicacion;

class AppController extends Controller
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

        $aplicaciones = array();
        
        // Todas las aplicaciones principales
        $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                    WHERE a.aplicacion IS NULL 
                                    ORDER BY a.id ASC");
        $apps = $query->getResult();
        $aplicaciones_str = '<option value=""></option>';

        foreach ($apps as $app)
        {

        	$aplicaciones_str .= '<option value="'.$app->getId().'">'.$app->getNombre().'</option>';

            // Subaplicaciones
            $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                        WHERE a.aplicacion = :aplicacion_id  
                                        ORDER BY a.id ASC")
                        ->setParameter('aplicacion_id', $app->getId());
            $subapps = $query->getResult();

            $subaplicaciones = array();
            foreach ($subapps as $subapp)
            {
            	$subaplicaciones[] = array('id' => $subapp->getId(),
	                                	   'nombre' => $subapp->getNombre(),
	                                 	   'url' => $subapp->getUrl(),
	                                 	   'icono' => $subapp->getIcono(),
	                                 	   'activo' => $subapp->getActivo(),
	                                 	   'delete_disabled' => $f->linkEliminar($subapp->getId(), 'AdminAplicacion'));
            }

            $aplicaciones[] = array('id' => $app->getId(),
                                 	'nombre' => $app->getNombre(),
                                 	'url' => $app->getUrl(),
                                 	'icono' => $app->getIcono(),
                                 	'activo' => $app->getActivo(),
                                 	'delete_disabled' => $f->linkEliminar($app->getId(), 'AdminAplicacion'),
                                 	'subaplicaciones' => $subaplicaciones);

        }

        return $this->render('LinkBackendBundle:App:index.html.twig', array('aplicaciones' => $aplicaciones,
        																	'aplicaciones_str' =>$aplicaciones_str));

    }

    public function ajaxEditAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $app_id = $request->query->get('app_id');
        $subaplicaciones = '<option value=""></option>';
        
        $aplicacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);

        // Todas las aplicaciones principales
        $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                    WHERE a.aplicacion IS NULL 
                                    ORDER BY a.id ASC");
        $apps = $query->getResult();

        foreach ($apps as $app)
        {
            if (!(!$aplicacion->getAplicacion() && $aplicacion->getId() == $app->getId()))
            {
                $selected = $aplicacion->getAplicacion() ? $aplicacion->getAplicacion()->getId() == $app->getId() ? ' selected' : '' : '';
                $subaplicaciones .= '<option value="'.$app->getId().'"'.$selected.'>'.$app->getNombre().'</option>';
            }
        }

        $return = array('nombre' => $aplicacion->getNombre(),
                        'url' => $aplicacion->getUrl(),
                        'icono' => $aplicacion->getIcono(),
                        'activo' => $aplicacion->getActivo(),
                        'subaplicaciones' => $subaplicaciones);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUpdateAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        
        $app_id = $request->request->get('app_id');
        $nombre = $request->request->get('nombre');
        $url = $request->request->get('url');
        $icono = $request->request->get('icono');
        $activo = $request->request->get('activo');
        $subaplicacion_id = $request->request->get('subaplicacion_id');

        if ($app_id)
        {
        	$aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);
        }
        else {
        	$aplicacion = new AdminAplicacion();
        }

        $aplicacion->setNombre($nombre);
        $aplicacion->setUrl($url);
        $aplicacion->setIcono($icono);
        $aplicacion->setActivo($activo ? true : false);
        if ($subaplicacion_id)
        {
        	$subaplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($subaplicacion_id);
        	$aplicacion->setAplicacion($subaplicacion);
        }
        else {
        	$aplicacion->setAplicacion(null);
        }
        $em->persist($aplicacion);
        $em->flush();
                    
        $return = array('id' => $aplicacion->getId(),
        				'nombre' => $aplicacion->getNombre(),
        				'url' => $aplicacion->getUrl(),
        				'icono' => '<span class="fa '.$aplicacion->getIcono().'"></span> '.$aplicacion->getIcono(),
        				'activo' => $aplicacion->getActivo() ? $this->get('translator')->trans('Si') : 'No',
        				'subaplicacion_id' => $aplicacion->getAplicacion() ? 1 : 0,
        				'subaplicacion' => $aplicacion->getAplicacion() ? $aplicacion->getAplicacion()->getNombre() : '',
                        'delete_disabled' => $f->linkEliminar($aplicacion->getId(), 'AdminAplicacion'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxActiveAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $app_id = $request->request->get('app_id');
        $checked = $request->request->get('checked');

        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);
        $aplicacion->setActivo($checked ? true : false);
        $em->persist($aplicacion);
        $em->flush();
                    
        $return = array('id' => $aplicacion->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDeleteAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $app_id = $request->request->get('id');
        $ok = 1;

        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);
        $em->remove($aplicacion);
        $em->flush();
            
        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
}
