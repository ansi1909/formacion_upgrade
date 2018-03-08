<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini'))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}
        $f->setRequest($session->get('sesion_id'));

      	if ($this->container->get('session')->isStarted())
      	{
        	
        	// Lógica para mostrar el dashboard del backend

      	}
      	else {
      		return $this->redirectToRoute('_loginAdmin');
      	}

        $empresas_db = $em->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        $empresas=0;
        $empresas_a=0;
        $empresas_i=0;
        foreach($empresas_db as $empresa)
        {
            $empresas=$empresas+'1';
            if($empresa->getActivo() == 'true') 
            {
                $empresas_a=$empresas_a+1;
            }
            else
            {
                $empresas_i=$empresas_i+1;
            }

        }

        $response = $this->render('LinkBackendBundle:Default:index.html.twig', array('empresas'=>$empresas,
                                                                                     'activas'=>$empresas_a,
                                                                                     'inactivas'=>$empresas_i));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;

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

    public function ajaxOrderAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $orden = $request->request->get('orden');

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $object->setOrden($orden);
        $em->persist($object);
        $em->flush();
                    
        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function loginAction(Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if ($session->get('ini'))
        {
            return $this->redirectToRoute('_inicioAdmin');
        }

        if ($request->getMethod() == 'POST')
        {
            $em = $this->getDoctrine()->getManager();
            $login = $request->request->get('usuario');
            $clave = $request->request->get('clave');

            //$cookies = $request->cookies->all();
            /*if (isset($cookies['Peter'])){
                return new Response('Existe Peter 2');
            }
            else {
                return new Response('No existe Peter');
            }*/
            
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $login,
                                                                                                            'clave' => $clave));
    
            if (!$usuario)
            {
                $error = $this->get('translator')->trans('Usuario o clave incorrecta.');
            }
            else {
                
                if (!$usuario->getActivo())
                {
                    $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
                }
                else {

                    // Se verifica si el usuario pertenece a una empresa activa
                    $empresa_activa = 1;
                    if ($usuario->getEmpresa())
                    {
                        if (!$usuario->getEmpresa()->getActivo())
                        {
                            $empresa_activa = 0;
                        }
                    }

                    if (!$empresa_activa)
                    {
                        $error = $this->get('translator')->trans('La empresa a la que pertenece este usuario está inactiva.');
                    }
                    else {

                        $roles_bk = array();
                        $roles_usuario = array();
                        $roles_bk[] = $yml['parameters']['rol']['administrador'];
                        $roles_bk[] = $yml['parameters']['rol']['empresa'];
                        $roles_bk[] = $yml['parameters']['rol']['tutor'];
                        $roles_ok = 0;
                        $administrador = false;
                        
                        $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
                                    ->setParameter('usuario_id', $usuario->getId());
                        $roles_usuario_db = $query->getResult();
                        
                        foreach ($roles_usuario_db as $rol_usuario)
                        {
                            
                            // Verifico si el rol está dentro de los roles de backend
                            if (in_array($rol_usuario->getRol()->getId(), $roles_bk))
                            {
                                $roles_ok = 1;
                            }

                            if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['administrador'])
                            {
                                $administrador = true;
                            }
                            
                            $roles_usuario[] = $rol_usuario->getRol()->getId();
                            
                        }
                        
                        if (!$roles_ok)
                        {
                            $error = $this->get('translator')->trans('Los roles que tiene el usuario no son permitidos para ingresar a la aplicación.');
                        }
                        else {
                            
                            $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a 
                                        WHERE p.rol IN (:roles) AND a.activo = :activo AND a.aplicacion IS NULL')
                                ->setParameters(array('roles' => $roles_usuario,
                                                      'activo' => true));
                            
                            if (!$query->getSingleScalarResult())
                            {
                                $error = $this->get('translator')->trans('Usted no tiene aplicaciones asignadas para su rol.');
                            }
                            else {

                                // Se setea la sesion y se prepara el menu
                                $datosUsuario = array('id' => $usuario->getId(),
                                                      'nombre' => $usuario->getNombre(),
                                                      'apellido' => $usuario->getApellido(),
                                                      'correo' => $usuario->getCorreoPersonal(),
                                                      'foto' => $usuario->getFoto(),
                                                      'roles' => $roles_usuario);

                                // Opciones del menu
                                $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a 
                                                            WHERE p.rol IN (:rol_id) 
                                                            AND a.activo = :activo 
                                                            AND a.aplicacion IS NULL
                                                            ORDER BY a.orden ASC")
                                            ->setParameters(array('rol_id' => $roles_usuario,
                                                                  'activo' => true));
                                $permisos = $query->getResult();

                                $permisos_id = array();
                                $menu = array();
                                
                                foreach ($permisos as $permiso)
                                {

                                    if (!in_array($permiso->getId(), $permisos_id))
                                    {

                                        $permisos_id[] = $permiso->getId();

                                        $submenu = array();

                                        $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a 
                                                                    WHERE p.rol IN (:rol_id) 
                                                                    AND a.activo = :activo 
                                                                    AND a.aplicacion = :app_id
                                                                    ORDER BY a.orden ASC")
                                                    ->setParameters(array('rol_id' => $roles_usuario,
                                                                          'activo' => true,
                                                                          'app_id' => $permiso->getAplicacion()->getId()));
                                        $subpermisos = $query->getResult();

                                        foreach ($subpermisos as $subpermiso)
                                        {

                                            if (!in_array($subpermiso->getId(), $permisos_id))
                                            {

                                                $permisos_id[] = $subpermiso->getId();

                                                $submenu[] = array('id' => $subpermiso->getAplicacion()->getId(),
                                                                   'url' => $subpermiso->getAplicacion()->getUrl(),
                                                                   'nombre' => $subpermiso->getAplicacion()->getNombre(),
                                                                   'icono' => $subpermiso->getAplicacion()->getIcono(),
                                                                   'url_existente' => $subpermiso->getAplicacion()->getUrl() ? 1 : 0);

                                            }
                                            
                                        }

                                        $menu[] = array('id' => $permiso->getAplicacion()->getId(),
                                                        'url' => $permiso->getAplicacion()->getUrl(),
                                                        'nombre' => $permiso->getAplicacion()->getNombre(),
                                                        'icono' => $permiso->getAplicacion()->getIcono(),
                                                        'url_existente' => $permiso->getAplicacion()->getUrl() ? 1 : 0,
                                                        'submenu' => $submenu);

                                    }
                                    
                                }

                                // Cierre de sesiones activas
                                $sesiones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                                                             'disponible' => true));
                                foreach ($sesiones as $s)
                                {
                                    $s->setDisponible(false);
                                }

                                // Se crea la sesión en BD
                                $admin_sesion = new AdminSesion();
                                $admin_sesion->setFechaIngreso(new \DateTime('now'));
                                $admin_sesion->setUsuario($usuario);
                                $admin_sesion->setDisponible(true);
                                $em->persist($admin_sesion);
                                $em->flush();

                                $session->set('ini', true);
                                $session->set('sesion_id', $admin_sesion->getId());
                                $session->set('code', $f->getLocaleCode());
                                $session->set('administrador', $administrador);
                                $session->set('usuario', $datosUsuario);
                                $session->set('menu', $menu);

                                return $this->redirectToRoute('_inicioAdmin');

                            }
                            
                        }
                    }

                }
            }
        }
        
        //return $this->render('LinkBackendBundle:Default:login.html.twig', array('error' => $error));
        $response = $this->render('LinkBackendBundle:Default:login.html.twig', array('error' => $error));
        //$response->headers->clearCookie('Peter');
        //$response->headers->removeCookie('Peter');
        return $response;

    }

    public function ajaxQRAction(Request $request)
    {

        $nombre = $request->request->get('nombre');
        $contenido = $request->request->get('contenido');
        $size = $request->request->get('size');

        $nombre = $nombre.'.png';

        \PHPQRCode\QRcode::png($contenido, "../qr/".$nombre, 'H', $size, 4);

        $ruta ='<img src="/formacion2.0/qr/'.$nombre.'">';
        
        $return = array('ruta' =>$ruta);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}
