<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class LoginController extends Controller
{

    public function authExceptionEmpresaAction()
    {
        return $this->render('LinkFrontendBundle:Default:authException.html.twig');
    }

	/*public function ajaxMainCorreoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $correo = trim($request->request->get('correo'));
        $ok = 1;
        $error = '';
        
        $usuario = $this->getDoctrine()->getRepository('PetsComunBundle:Usuario')->findOneByCorreo($correo);

        if (!$usuario)
        {
            $ok = 0;
            $error = 'No existen registros de usuario con este correo.';
        }
        
		$correo = $request->request->get('correo_corporativo');

        if($correo!="")
        {
			$empresa_id = 1;

			$usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneByCorreoCorporativo($correo);

			if (!$usuario)//validamos que el correo exista
            {
	            $ok = 0;
	            $error = $this->get('translator')->trans('El correo no existe en la base de datos.');
            }
            else{
            	if (!$usuario->getActivo()) //validamos que el usuario este activo
                {
            	    $ok = 0;
                    $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
                }
                else {
            		$empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

            		if ($usuario->getEmpresa()->getId() != $empresa->getId()) //validamos que el usuario pertenezca a la empresa
	                {
                	    $ok = 0;
	                    $error = $this->get('translator')->trans('El correo no pertenece a un Usuario de la empresa. Contacte al administrador del sistema.');
	                }
	                else{
						$ok = 1;
   			            // Envío de correo con los datos de acceso, usuario y clave
			            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                             				'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
			                                'destinatario' => $correo,
			                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
			                                'datos' => array('usuario' => $usuario->getLogin(),
			                                                 'clave' => $usuario->getClave()) );
			            $correoRecuperacion = $f->sendEmail($parametros, $this);
               			return $this->redirectToRoute('_login');
	                }
	            }
			}
		}

        if ($ok == 1)
        {

            // Envío de correo con el usuario y clave provisional
            $f = $this->get('funciones');
            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                 				'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
                                'destinatario' => $correo,
                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
                                'datos' => array('usuario' => $usuario->getLogin(),
                                                 'clave' => $usuario->getClave()) );
            $correoRecuperacion = $f->sendEmail($parametros, $this);
        }
        
        $return = array('ok' => $ok, 'error' => $error);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }*/

	public function recuperarClaveAction($empresa_id, Request $request)
    {

		$f = $this->get('funciones');
		$error = '';

		if ($request->getMethod() == 'POST')
        {
        	$em = $this->getDoctrine()->getManager();

            $correo = $request->request->get('correo_corporativo');

            if($correo!="")
            {
				$empresa_id = 1;

				$usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneByCorreoCorporativo($correo);

				if (!$usuario)//validamos que el correo exista
	            {
	                $error = $this->get('translator')->trans('El correo no existe en la base de datos.');
	            }
	            else{
	            	if (!$usuario->getActivo()) //validamos que el usuario este activo
	                {
	                    $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
	                }
	                else {
	            		$empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

	            		if ($usuario->getEmpresa()->getId() != $empresa->getId()) //validamos que el usuario pertenezca a la empresa
		                {
		                    $error = $this->get('translator')->trans('El correo no pertenece a un Usuario de la empresa. Contacte al administrador del sistema.');
		                }
		                else{

	   			            // Envío de correo con los datos de acceso, usuario y clave
				            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
	                             				'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
				                                'destinatario' => $correo,
				                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
				                                'datos' => array('usuario' => $usuario->getLogin(),
				                                                 'clave' => $usuario->getClave()) );
				            $correoRecuperacion = $f->sendEmail($parametros, $this);
	               			return $this->redirectToRoute('_login');
		                }
		            }
				}
			}else
			{
                $error = $this->get('translator')->trans('Debe ingresar el correo electronico.');
			}
        }

        return $this->render('LinkFrontendBundle:Default:recuperarClave.html.twig', array('error' => $error));   
    }


    public function loginAction($empresa_id, Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();

		$session = new Session();

		if ($session->get('iniFront'))
        {
            return $this->redirectToRoute('_inicio');
        }

		$empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->findOneById($empresa_id);

		if ($empresa )
        {
			//se consulta la preferencia de la empresa
    		$preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa_id);

    		if($preferencia)
    		{
    			$logo= 'http://'.$_SERVER['HTTP_HOST'].'/uploads/'.$preferencia->getLogo();
    			$favicon = 'http://'.$_SERVER['HTTP_HOST'].'/uploads/'.$preferencia->getFavicon();
				$layout = explode("_", $preferencia->getLayout()->getTwig());
    			$layout = $layout[0]."_";
    			$title = $preferencia->getTitle();
    		}else
    		{
    			$logo='http://'.$_SERVER['HTTP_HOST'].'/uploads/recursos/empresas/logo_formacion.png';
    			$favicon='http://'.$_SERVER['HTTP_HOST'].'/uploads/recursos/empresas/icono.png';
    			$layout='';
    			$title = 'Sistema Formación 2.0';
    		}
	        if ($request->getMethod() == 'POST')
	        {
	            $login = $request->request->get('usuario');
	            $clave = $request->request->get('password');

	            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $login,
	                                                                                                            'clave' => $clave));
				if (!$usuario)//validamos que el usuario exista
	            {
	                $error = $this->get('translator')->trans('Usuario o clave incorrecta.');
	            }
	            else {
	                if (!$usuario->getActivo()) //validamos que el usuario este activo
	                {
	                    $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
	                }
	                else {

						if (!$empresa->getActivo()) //validamos que la empresa este activa
		                {
		                    $error = $this->get('translator')->trans('La empresa a la que pertenece este usuario está inactiva. Contacte al administrador del sistema.');
		                }
		                else {
		            		if ($usuario->getEmpresa()->getId() != $empresa->getId()) //validamos que el usuario pertenezca a la empresa
			                {
			                    $error = $this->get('translator')->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
			                }		        		
			                else {
								//se consulta si la empresa tien paginas activas
				 				$query = $em->createQuery('SELECT np FROM LinkComunBundle:CertiNivelPagina np
				                                           JOIN np.paginaEmpresa pe
				                                           WHERE pe.empresa= :empresa AND np.nivel = :nivel_usuario and pe.activo = :activo')
				                            ->setParameters(array('empresa' => $empresa->getId(),
				                                                  'nivel_usuario' => $usuario->getNivel()->getId(),
				                                                  'activo' => true ));
				                $paginaEmpresa = $query->getResult();

								if (!$paginaEmpresa)  //validamos que la empresa tenga paginas activas
				                {
				                    $error = $this->get('translator')->trans('No hay Programas disponibles para la empresa. Contacte al administrador del sistema.');
				                }
				                else{

			                        $roles_bk = array();
			                        $roles_bk[] = $yml['parameters']['rol']['participante'];
			                        $roles_bk[] = $yml['parameters']['rol']['tutor'];
			                        $roles_ok = 0;
			                        $participante = false;
			                        $tutor = false;

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
			                            if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['participante'])
			                            {
			                                $participante = true;
			                            }
			                            if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['tutor'])
			                            {
			                                $tutor = true;
			                            }
			                        }
			                        
			                        if (!$roles_ok)
			                        {
			                            $error = $this->get('translator')->trans('Los roles que tiene el usuario no son permitidos para ingresar al sistema.');
			                        }
			                        else {
			                        
		                                // Se setea los datos del usuario
		                                $datosUsuario = array('id' => $usuario->getId(),
		                                                      'nombre' => $usuario->getNombre(),
		                                                      'apellido' => $usuario->getApellido(),
		                                                      'correo' => $usuario->getCorreoPersonal(),
		                                                      'foto' => $usuario->getFoto(),
		                                                      'participante' => $participante,
		                                                      'tutor' => $tutor);
		                                
		                                // Se setea los datos de la empresa
		                                $datosEmpresa = array('id' => $empresa->getId(),
		                                                      'nombre' => $empresa->getNombre(),
		                                                      'chat' => $empresa->getChatActivo(),
		                                                      'webinar' => $empresa->getWebinar(),
		                                                      'favicon' => $favicon,
		                                                      'platilla' => $layout,
		                                                      'logo' => $logo);
										//se consulta las paginas padres
						 				$query = $em->createQuery('SELECT np FROM LinkComunBundle:CertiNivelPagina np
						                                           JOIN np.paginaEmpresa pe
						                                           JOIN pe.pagina p
						                                           WHERE pe.empresa= :empresa AND p.pagina IS NULL AND np.nivel = :nivel_usuario and pe.activo = :activo')
						                            ->setParameters(array('empresa' => $empresa->getId(),
						                                                  'nivel_usuario' => $usuario->getNivel()->getId(),
						                                                  'activo' => true ));
						                $paginaPadres = $query->getResult();

						                if($paginaPadres)
						                {
											foreach ($paginaPadres as $pagina)
					                        {
												$query = $em->createQuery('SELECT COUNT(cp.id) as cantidad FROM LinkComunBundle:CertiPrueba cp
								                                           WHERE cp.estatusContenido = :activo and cp.pagina = :pagina_id')
								                            ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
								                            					  'pagina_id' => $pagina->getId() ));
								                $tiene_evaluacion = $query->getSingleScalarResult();

										        $subPaginas = $f->subPaginasNivel($pagina->getPaginaEmpresa()->getPagina()->getId(), $usuario->getNivel()->getId(), $yml['parameters']['estatus_contenido']['activo'] );

								                $datosPagina[] = array('id' => $pagina->getPaginaEmpresa()->getPagina()->getId(),
				                                                        'nombre' => $pagina->getPaginaEmpresa()->getPagina()->getNombre(),
				                                                        'categoria' => $pagina->getPaginaEmpresa()->getPagina()->getCategoria()->getNombre(),
				                                                        'foto' => $pagina->getPaginaEmpresa()->getPagina()->getFoto(),
				                                                        'tiene_evaluacion' => $tiene_evaluacion,
				                                                        'sub_paginas' => $subPaginas);
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

        								//$session = new session();
		                                $session->set('ini', true);
		                                $session->set('sesion_id', $admin_sesion->getId());
		                                $session->set('code', $f->getLocaleCode());
		                                $session->set('usuario', $datosUsuario);
										$session->set('empresa', $datosEmpresa);
										$session->set('paginas', $datosPagina);

		                                return $this->redirectToRoute('_inicio');
		                            }
					            }
			                }
						}
	                }
	            }
	        }

	        $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa_id' => $empresa_id, 
    																								  'logo' => $logo,
    																								  'title' => $title,
    																								  'favicon' => $favicon,
	        																						  'error' => $error));
	        return $response;

	    }else
	    {

    		return $this->redirectToRoute('_authExceptionEmpresa');
	    }
    }

}