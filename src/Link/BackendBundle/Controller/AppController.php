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

        $servicios = array();
        $i = 0;
        
        // Todas los servicios principales
        $query = $em->createQuery("SELECT s FROM PsTelemedBundle:Servicio s 
                                    WHERE s.subservicio IS NULL 
                                    ORDER BY s.id ASC");
        $services = $query->getResult();

        foreach ($services as $service)
        {

            $i++;
            $mod = $i%4;
            switch ($mod)
            {
                case 0:
                    $context_class = 'danger';
                    break;
                case 1:
                    $context_class = 'success';
                    break;
                case 2:
                    $context_class = 'info';
                    break;
                case 3:
                    $context_class = 'warning';
                    break;
            }

            switch ($service->getInterfaz())
            {
                case 1:
                    $interfaz = 'Backend';
                    break;
                case 2:
                    $interfaz = 'Frontend Especialistas';
                    break;
                case 3:
                    $interfaz = 'Frontend Pacientes';
                    break;
                default:
                    $interfaz = 'Backend';
                    break;
            }

            // Subservicios
            $query = $em->createQuery("SELECT s FROM PsTelemedBundle:Servicio s 
                                        WHERE s.subservicio = :servicio_id  
                                        ORDER BY s.id ASC")
                        ->setParameter('servicio_id', $service->getId());
            $subservicios = $query->getResult();

            $servicios[] = array('id' => $service->getId(),
                                 'nombre' => $service->getNombre(),
                                 'costo' => $service->getCosto(),
                                 'interfaz' => $interfaz,
                                 'activo' => $service->getActivo(),
                                 'context_class' => $context_class,
                                 'subservicios' => $subservicios);

        }

        return $this->render('PsTelemedBundle:Servicio:index.html.twig', array('servicios' => $servicios));

        return $this->render('LinkBackendBundle:Default:index.html.twig');

    }
}
