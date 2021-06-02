<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminLigas;
use Symfony\Component\Yaml\Yaml;


class LigaController extends Controller
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

        $ligasdb = array();

        $query = $em->createQuery('SELECT l FROM LinkComunBundle:AdminLigas l
                                        ORDER BY l.nombre ASC');
        $ligas = $query->getResult();
        
        foreach ($ligas as $liga)
        {
            $ligasdb[] = array('id' => $liga->getId(),
                               'nombre' => $liga->getNombre(),
                               'descripcion' => $liga->getDescripcion(),
                               'puntos' => $liga->getPuntuacion(),
                               'delete_disabled' => $f->linkEliminar($liga->getId(),'AdminLigas'));

        }

       return $this->render('LinkBackendBundle:Ligas:index.html.twig', array('ligas'=>$ligasdb));

    }

    public function ajaxUpdateLigaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $liga_id = $request->request->get('liga_id');
        $nombre = $request->request->get('nombre');
        $descripcion = $request->request->get('descripcion');
        $puntos = $request->request->get('puntos');
        
       

        if ($liga_id)
        {
            $liga = $em->getRepository('LinkComunBundle:AdminLigas')->find($liga_id);
        }
        else {
            $liga = new AdminLigas();
        }

        $liga->setNombre($nombre);
        $liga->setdescripcion($descripcion);
        $liga->setPuntuacion($puntos);
                        
        $em->persist($liga);
        $em->flush();
                    
        $return = array('id' => $liga->getId(),
                        'nombre'      => $liga->getNombre(),
                        'descripcion'   => $liga->getDescripcion(),
                        'puntos'  => $liga->getPuntuacion(),
                        'delete_disabled' => $f->linkEliminar($liga->getId(),'AdminLigas'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditLigaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $liga_id = $request->query->get('liga_id');
                
        $liga = $this->getDoctrine()->getRepository('LinkComunBundle:AdminLigas')->find($liga_id);

        $return = array(
                    'nombre'     => $liga->getNombre(), 
                    'descripcion'  => $liga->getDescripcion(),
                    'puntos' => $liga->getPuntuacion(),
                );

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
}