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

    public function ajaxDeleteAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $em->remove($object);
        $em->flush();

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxActiveAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $checked = $request->request->get('checked');

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $object->setActivo($checked ? true : false);
        $em->persist($object);
        $em->flush();
                    
        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function loginAction(Request $request)
    {
        $session = new session();
        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if ($request->getMethod() == 'POST')
        {
            $em = $this->getDoctrine()->getManager();
            $login = $request->request->get('usuario');
            $clave = $request->request->get('clave');
            
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $login,
                                                                                                            'clave' => $clave));
    
            if(!$usuario){
                $error = $this->get('translator')->trans('Usuario o clave incorrecta.');
            }
            else {
                if (!$usuario->getActivo()){
                    $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
                }
                else{
                    
                    $roles_bk = array();
                    $roles_usuario = array();
                    $roles_bk[] = $yml['parameters']['rol']['administrador'];
                    $roles_bk[] = $yml['parameters']['rol']['empresa'];
                    $roles_ok = 0;
                    
                    $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
                                ->setParameter('usuario_id', $usuario->getId());
                    $roles_usuario = $query->getResult();
                    
                    foreach ($roles_usuario as $rol_usuario)
                    {
                        
                        // Verifico si el rol está dentro de los roles de backend
                        if (in_array($rol_usuario->getRol()->getId(), $roles_bk))
                        {
                            $roles_ok = 1;
                        }
                        
                        $roles_usuario[] = $rol_usuario->getRol()->getId();
                        
                    }
                    
                    if (!$roles_ok)
                    {
                        $error = $this->get('translator')->trans('Los roles que tiene el usuario no son permitidos para ingresar a la aplicación.');
                    }
                    else {
                        $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a 
                                    WHERE p.rol IN (:roles) AND a.activo = :activo')
                            ->setParameters(array('roles' => $roles_usuario,
                                                  'activo' => true));
                        if (!$query->getSingleScalarResult())
                        {
                            $error = $this->get('translator')->trans('Usted no tiene aplicaciones asignadas para su rol.');
                        }
                        
                        // Se setea la sesion y se prepara el menu
                        
                        
                    }
                }
            }
        }
        else {
            $session->invalidate();
        }
        return $this->render('LinkBackendBundle:Default:login.html.twig', array( 'error' => $error));
    }
}
