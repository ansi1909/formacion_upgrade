<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminTipoDestino; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class TipoDestinoController extends Controller
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

        $tipo_destinosdb= array();

        $query= $em->createQuery('SELECT r FROM LinkComunBundle:AdminTipoDestino r
                                        ORDER BY r.nombre ASC');
        $tipo_destinos=$query->getResult();
        
       //return new Response(var_dump($roles));
        
        foreach ($tipo_destinos as $tipo_destino)
        {
            $tipo_destinosdb[]= array('id'=>$tipo_destino->getId(),
                              'nombre'=>$tipo_destino->getNombre(),
                              'delete_disabled'=>$f->linkEliminar($tipo_destino->getId(),'AdminTipoDestino'));

        }

       return $this->render('LinkBackendBundle:TipoDestino:index.html.twig', array('tipo_destinos'=>$tipo_destinosdb));

    }

   public function ajaxUpdateTipoDestinoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $tipo_destino_id = $request->request->get('tipo_destino_id');
        $nombre = $request->request->get('tipo_destino');

        if ($tipo_destino_id)
        {
            $tipo_destino = $em->getRepository('LinkComunBundle:AdminTipoDestino')->find($tipo_destino_id);
        }
        else {
            $tipo_destino = new AdminTipoDestino();
        }

        $tipo_destino->setNombre($nombre);
        
        $em->persist($tipo_destino);
        $em->flush();
                    
        $return = array('id' => $tipo_destino->getId(),
                        'nombre' =>$tipo_destino->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($tipo_destino->getId(),'AdminTipoDestino'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditTipoDestinoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $tipo_destino_id = $request->query->get('tipo_destino_id');
                
        $tipo_destino = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoDestino')->find($tipo_destino_id);

        $query = $em->createQuery("SELECT r FROM LinkComunBundle:AdminTipoDestino r 
                                    WHERE r.nombre IS NULL 
                                    ORDER BY r.id ASC");
        $apps = $query->getResult();


        $return = array('nombre' => $tipo_destino->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
