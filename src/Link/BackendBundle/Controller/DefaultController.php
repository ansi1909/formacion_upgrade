<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');

        /* Nos permitirá acceder a los parámetros varios */
        //$values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        //return new Response ($values['parameters']['database_host']);

    	/*
      	if (!$session->get('ini'))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}

      	if ($this->container->get('session')->isStarted())
      	{
        	
        	// Lógica para mostrar el dashboard del backend
      	}
      	else {
      		return $this->redirectToRoute('_loginAdmin');
      	}*/

        /* **************************** BLOQUE ******************************************** */
        // OJO: Quitar este bloque de código una vez que se desarrolle el login del backend
        
        // Datos de Usuario Administrador y sus autoservicios
        $datosUsuario = array('id' => 1,
                              'nombre' => 'Administrador',
                              'apellido' => 'Sistema',
                              'correo' => 'soporte_link_gerencial@gmail.com',
                              'roles' => array(1));

        // Opciones del menu (Asumiendo que el único rol es Administrador = 1)
        $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a 
                                    WHERE p.rol = :rol_id 
                                    AND a.activo = :activo 
                                    AND a.aplicacion IS NULL")
                    ->setParameters(array('rol_id' => 1,
                                          'activo' => true));
        $permisos = $query->getResult();

        $menu = array();
        foreach ($permisos as $permiso)
        {

            $submenu = array();

            $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a 
                                        WHERE p.rol = :rol_id 
                                        AND a.activo = :activo 
                                        AND a.aplicacion = :app_id")
                        ->setParameters(array('rol_id' => 1,
                                              'activo' => true,
                                              'app_id' => $permiso->getAplicacion()->getId()));
            $subpermisos = $query->getResult();

            foreach ($subpermisos as $subpermiso)
            {
                $submenu[] = array('id' => $subpermiso->getAplicacion()->getId(),
                                   'url' => $subpermiso->getAplicacion()->getUrl(),
                                   'nombre' => $subpermiso->getAplicacion()->getNombre(),
                                   'icono' => $subpermiso->getAplicacion()->getIcono(),
                                   'url_existente' => $subpermiso->getAplicacion()->getUrl() ? 1 : 0);
            }

            $menu[] = array('id' => $permiso->getAplicacion()->getId(),
                            'url' => $permiso->getAplicacion()->getUrl(),
                            'nombre' => $permiso->getAplicacion()->getNombre(),
                            'icono' => $permiso->getAplicacion()->getIcono(),
                            'url_existente' => $permiso->getAplicacion()->getUrl() ? 1 : 0,
                            'submenu' => $submenu);
        }


        $session->set('ini', true);
        $session->set('code', $f->getLocaleCode());
        $session->set('administrador', true);
        $session->set('usuario', $datosUsuario);
        $session->set('menu', $menu);

        /* **************************** FIN BLOQUE ****************************************** */

        return $this->render('LinkBackendBundle:Default:index.html.twig');

    }

    public function authExceptionAction()
    {
        return $this->render('LinkBackendBundle:Default:authException.html.twig');
    }
}
