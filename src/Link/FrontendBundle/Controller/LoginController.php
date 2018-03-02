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

    public function recuperarClaveAction(Request $request)
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
				            $parametros = array('asunto' => 'Formación2.0 - Recuperación de usuario y clave',
	                             				'remitente'=>array('tutorvirtual@formacion2puntocero.com' => 'Formación 2.0'),
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

    public function loginAction(Request $request)
    {

        $session = new session();
        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if ($session->get('ini'))
        {
            return $this->redirectToRoute('_inicio');
        }

        $empresa_id = 1;

        if ($request->getMethod() == 'POST')
        {
            $em = $this->getDoctrine()->getManager();
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
            		$empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

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

			 				$query = $em->createQuery('SELECT np FROM LinkComunBundle:CertiNivelPagina np
			                                           JOIN np.paginaEmpresa pe
			                                           WHERE pe.empresa= :empresa AND np.nivel = :nivel_pagina and pe.activo = :activo')
			                            ->setParameters(array('empresa' => $empresa->getId(),
			                                                  'nivel_pagina' => $usuario->getNivel()->getId(),
			                                                  'activo' => true ));
			                $paginaEmpresa = $query->getResult();

						/*	$query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
			                                           JOIN pe.pagina p
													   JOIN pe.nivelPagina np
			                                           WHERE np.paginaEmpresa= :nivel_pagina AND pe.empresa= :empresa AND p.pagina IS NULL AND p.estatusContenido = :estatus_contenido_activo and pe.activo = :activo ORDER BY p.nombre ASC')
			                            ->setParameters(array('empresa' => $empresa->getId(),
			                            					  'nivel_pagina' => $usuario->getNivel()->getId(),
			                                                  'estatus_contenido_activo' => $yml['parameters']['estatus_contenido']['activo'],
			                                                  'activo' => true ));
			                $paginaEmpresa = $query->getResult();	*/		                

							/*if (!$paginaEmpresa)  //validamos que la empresa tenga paginas activas
			                {
			                    $error = $this->get('translator')->trans('No hay Programas disponibles para la empresa. Contacte al administrador del sistema.');
			                }
			                else{*/

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
		                            $error = $this->get('translator')->trans('Los roles que tiene el usuario no son permitidos para ingresar a la aplicación.');
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
	                                                      'platilla' => 'twig para el layout principal',
	                                                      'logo' => 'url del logo de la empresa');

									foreach ($paginaEmpresa as $pag)
			                        {
            							$pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pag->getId());

						                $datosPagina[] = array('id' => $pagina->getId(),
		                                                        'nombre' => $pagina->getNombre(),
		                                                        'categoria' => $pagina->getCategoria(),
		                                                        'foto' => $pagina->getFoto(),
		                                                        'tiene_evaluacion' => true,
		                                                        'sub_paginas' =>  ''
		                                                        );
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
	                                $session->set('usuario', $datosUsuario);
									$session->set('empresa', $datosEmpresa);
									//$session->set('paginas', $datosPagina);

	                                return $this->redirectToRoute('_inicio');
	                            //}
				            }
		                }
					}
                }
            }
        }

        $response = $this->render('LinkFrontendBundle:Default:login.html.twig', array('error' => $error));
        return $response;
    }

}