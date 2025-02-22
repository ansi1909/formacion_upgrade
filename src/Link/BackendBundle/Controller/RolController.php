<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminRol; 


class RolController extends Controller
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

        $rolesdb = array();

        $query = $em->createQuery('SELECT r FROM LinkComunBundle:AdminRol r
                                        ORDER BY r.nombre ASC');
        $roles = $query->getResult();
                
        foreach ($roles as $rol)
        {
            $rolesdb[]= array('id' => $rol->getId(),
                              'nombre' => $rol->getNombre(),
                              'descripcion' => $rol->getDescripcion(),
                              'empresa' => $rol->getEmpresa() ? $this->get('translator')->trans('Sí') : 'No',
                              'backend' => $rol->getBackend() ? $this->get('translator')->trans('Sí') : 'No',
                              'delete_disabled' => $f->linkEliminar($rol->getId(),'AdminRol'));

        }

       return $this->render('LinkBackendBundle:Rol:index.html.twig', array('roles'=>$rolesdb));

    }

   public function ajaxUpdateRolAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $rol_id = $request->request->get('rol_id');
        $nombre = $request->request->get('rol');
        $descripcion = $request->request->get('descripcion');
        $empresa = $request->request->get('empresa');
        $backend = $request->request->get('backend');

        if ($rol_id)
        {
            $rol = $em->getRepository('LinkComunBundle:AdminRol')->find($rol_id);
        }
        else {
            $rol = new AdminRol();
        }

        $rol->setNombre($nombre);
        $rol->setDescripcion($descripcion); 
        $rol->setEmpresa(isset($empresa) ? $empresa ? true : false : false);
        $rol->setBackend(isset($backend) ? $backend ? true : false : false);
        $em->persist($rol);
        $em->flush();
                    
        $return = array('id' => $rol->getId(),
                        'nombre' => $rol->getNombre(),
                        'descripcion' => $rol->getDescripcion(),
                        'empresa' => $rol->getEmpresa() ? $this->get('translator')->trans('Sí') : 'No',
                        'backend' => $rol->getBackend() ? $this->get('translator')->trans('Sí') : 'No',
                        'delete_disabled' => $f->linkEliminar($rol->getId(),'AdminRol'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditRolAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $rol_id = $request->query->get('rol_id');
                
        $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($rol_id);

        $query = $em->createQuery("SELECT r FROM LinkComunBundle:AdminRol r 
                                    WHERE r.nombre IS NULL 
                                    ORDER BY r.id ASC");
        $apps = $query->getResult();


        $return = array('nombre' => $rol->getNombre(),
                        'descripcion' => $rol->getDescripcion(),
                        'empresa' => $rol->getEmpresa(),
                        'backend' => $rol->getBackend());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
