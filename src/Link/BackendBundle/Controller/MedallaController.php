<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminMedallas;
use Symfony\Component\Yaml\Yaml;


class MedallaController extends Controller
{
    public function indexAction($app_id)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $medalladb = array();

        $query = $em->createQuery('SELECT m FROM LinkComunBundle:AdminMedallas m
                                        ORDER BY m.nombre ASC');
        $medallas = $query->getResult();
        
        foreach ($medallas as $medalla)
        {
            $medalladb[] = array('id' => $medalla->getId(),
                                 'nombre' => $medalla->getNombre(),
                                 'descripcion' => $medalla->getDescripcion(),
                                 'puntos' => $medalla->getPuntos(),
                                 'delete_disabled' => $f->linkEliminar($medalla->getId(),'AdminMedallas'));

        }

       return $this->render('LinkBackendBundle:Medalla:index.html.twig', array('medallas'=>$medalladb));

    }

    public function ajaxUpdateMedallaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $medalla_id = $request->request->get('medalla_id');
        $nombre = $request->request->get('nombre');
        $descripcion = $request->request->get('descripcion');
        $puntos = $request->request->get('puntos');
        
       

        if ($medalla_id)
        {
            $medalla = $em->getRepository('LinkComunBundle:AdminMedallas')->find($medalla_id);
        }
        else {
            $medalla = new AdminMedallas();
        }

        $medalla->setNombre($nombre);
        $medalla->setdescripcion($descripcion);
        $medalla->setPuntos($puntos);
                        
        $em->persist($medalla);
        $em->flush();
                    
        $return = array('id' => $medalla->getId(),
                        'nombre'      => $medalla->getNombre(),
                        'descripcion'   => $medalla->getDescripcion(),
                        'puntos'  => $medalla->getPuntos(),
                        'delete_disabled' => $f->linkEliminar($medalla->getId(),'AdminMedallas'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditMedallaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $medalla_id = $request->query->get('medalla_id');
                
        $medalla = $this->getDoctrine()->getRepository('LinkComunBundle:AdminMedallas')->find($medalla_id);

        $return = array(
                    'nombre'     => $medalla->getNombre(), 
                    'descripcion'  => $medalla->getDescripcion(),
                    'puntos' => $medalla->getPuntos(),
                );

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
}