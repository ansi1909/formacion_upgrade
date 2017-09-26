<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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

        foreach ($apps as $app)
        {

            // Subaplicaciones
            $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                        WHERE a.aplicacion = :aplicacion_id  
                                        ORDER BY a.id ASC")
                        ->setParameter('aplicacion_id', $app->getId());
            $subaplicaciones = $query->getResult();

            $aplicaciones[] = array('id' => $app->getId(),
                                 	'nombre' => $app->getNombre(),
                                 	'url' => $app->getUrl(),
                                 	'icono' => $app->getIcono(),
                                 	'activo' => $app->getActivo(),
                                 	'subaplicaciones' => $subaplicaciones);

        }

        return $this->render('LinkBackendBundle:App:index.html.twig', array('aplicaciones' => $aplicaciones,
        																	'apps' =>$apps));

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
}
