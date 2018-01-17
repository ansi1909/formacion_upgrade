<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminTipoNotificacion; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TipoNotificacionController extends Controller
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

        $rolesdb= array();

        $query= $em->createQuery('SELECT r FROM LinkComunBundle:AdminTipoNotificacion r
                                        ORDER BY r.nombre ASC');
        $tipo_notificaciones=$query->getResult();
        
       //return new Response(var_dump($roles));
        
        foreach ($tipo_notificaciones as $tipo_notificacion)
        {
            $tipo_notificacionesdb[]= array('id'=>$tipo_notificacion->getId(),
                              'nombre'=>$tipo_notificacion->getNombre(),
                              'delete_disabled'=>$f->linkEliminar($tipo_notificacion->getId(),'AdminTipoNotificacion'));

        }

       return $this->render('LinkBackendBundle:TipoNotificacion:index.html.twig', array('tipo_notificaciones'=>$tipo_notificacionesdb));

    }

   public function ajaxUpdateTipoNotificacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $tipo_notificacion_id = $request->request->get('tipo_notificacion_id');
        $nombre = $request->request->get('tipo_notificacion');

        if ($tipo_notificacion_id)
        {
            $tipo_notificacion = $em->getRepository('LinkComunBundle:AdminTipoNotificacion')->find($tipo_notificacion_id);
        }
        else {
            $tipo_notificacion = new AdminTipoNotificacion();
        }

        $tipo_notificacion->setNombre($nombre);
        
        $em->persist($tipo_notificacion);
        $em->flush();
                    
        $return = array('id' => $tipo_notificacion->getId(),
                        'nombre' =>$tipo_notificacion->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($tipo_notificacion->getId(),'AdminTipoNotificacion'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditTipoNotificacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $tipo_notificacion_id = $request->query->get('tipo_notificacion_id');
                
        $tipo_notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoNotificacion')->find($tipo_notificacion_id);

        $query = $em->createQuery("SELECT r FROM LinkComunBundle:AdminTipoNotificacion r 
                                    WHERE r.nombre IS NULL 
                                    ORDER BY r.id ASC");
        $apps = $query->getResult();


        $return = array('nombre' => $tipo_notificacion->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
