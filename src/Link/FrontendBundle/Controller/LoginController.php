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

    public function authExceptionEmpresaAction($mensaje)
    {
        return $this->render('LinkFrontendBundle:Default:authException.html.twig', array('mensaje' => $mensaje));
    }

	public function ajaxCorreoAction(Request $request)
    {
              
        $correo = trim($request->request->get('correo'));
        $empresa_id = $request->request->get('empresa_id');
        $ok = 1;
        $error = '';
        
		if($correo!="")
        {
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
                	if(!$usuario->getEmpresa())
                	{
						$error = $this->get('translator')->trans('El usuario no tiene empresa asignada. Contacte al administrador del sistema.');
                	}else
                	{
	            		$empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

	            		if ($empresa && $usuario->getEmpresa()->getId() != $empresa->getId()) //validamos que el usuario pertenezca a la empresa
		                {
		                    $error = $this->get('translator')->trans('El correo no pertenece a un Usuario de la empresa. Contacte al administrador del sistema.');
		                }
		                else{

        					$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
							$f = $this->get('funciones');

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
		}else
		{
            $error = $this->get('translator')->trans('Debe ingresar el correo electronico.');
		}

      /*  if ($ok == 1)
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
        }**/
        
        $return = array('ok' => $ok, 'error' => $error);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function loginAction($empresa_id, Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();

		$session = new Session();

		$empresa_bd = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

		if ($empresa_bd)
        {
			
			//se consulta la preferencia de la empresa
    		$preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa_id);

    		if ($preferencia)
    		{
    			$logo = $preferencia->getLogoLogin() ? $preferencia->getLogo() : '';
    			$favicon = $preferencia->getFavicon();
				$layout = explode(".", $preferencia->getLayout()->getTwig());
    			$layout = $layout[0]."_";
    			$title = $preferencia->getTitle();
    			$css = $preferencia->getCss();
    			$webinar = $empresa_bd->getWebinar();
    			$chat = $empresa_bd->getChatActivo();
    			$plantilla = $preferencia->getLayout()->getTwig();
    		}
    		else {
    			$logo = '';
    			$favicon = '';
    			$layout = 'base_';
    			$title = '';
    			$css = '';
    			$webinar = false;
    			$chat = false;
    			$plantilla = 'base.html.twig';
    		}

    		$empresa = array('id' => $empresa_id,
						  	 'nombre' => $empresa_bd->getNombre(),
						  	 'chat' => $chat,
						  	 'webinar' => $webinar,
						  	 'plantilla' => $plantilla,
						  	 'logo' => $logo,
						  	 'favicon' => $favicon,
						  	 'titulo' => $title,
						  	 'css' => $css);

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

						if (!$empresa_bd->getActivo()) //validamos que la empresa este activa
		                {
		                    $error = $this->get('translator')->trans('La empresa está inactiva. Contacte al administrador del sistema.');
		                }
		                else {
		            		
		            		if (!$usuario->getEmpresa())
		            		{
		            			$error = $this->get('translator')->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
		            		}
		            		else {
		            			if ($usuario->getEmpresa()->getId() != $empresa_bd->getId()) //validamos que el usuario pertenezca a la empresa
				                {
				                    $error = $this->get('translator')->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
				                }		        		
				                else {

				                	$roles_front = array();
			                        $roles_front[] = $yml['parameters']['rol']['participante'];
			                        $roles_front[] = $yml['parameters']['rol']['tutor'];
			                        $roles_ok = 0;
			                        $participante = false;
			                        $tutor = false;

			                        $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
			                                    ->setParameter('usuario_id', $usuario->getId());
			                        $roles_usuario_db = $query->getResult();
			                        
			                        foreach ($roles_usuario_db as $rol_usuario)
			                        {
			                            // Verifico si el rol está dentro de los roles de backend
			                            if (in_array($rol_usuario->getRol()->getId(), $roles_front))
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

				                        // se consulta si la empresa tiene paginas activas
										$query = $em->createQuery('SELECT np FROM LinkComunBundle:CertiNivelPagina np
						                                           JOIN np.paginaEmpresa pe
						                                           JOIN pe.pagina p
						                                           WHERE pe.empresa = :empresa 
						                                           	AND p.pagina IS NULL 
						                                           	AND np.nivel = :nivel_usuario 
						                                           	AND pe.activo = :activo 
						                                           	AND pe.fechaInicio <= :hoy 
						                                           	AND pe.fechaVencimiento >= :hoy
						                                           ORDER BY p.orden')
						                            ->setParameters(array('empresa' => $empresa_bd->getId(),
						                                                  'nivel_usuario' => $usuario->getNivel()->getId(),
						                                                  'activo' => true,
						                                                  'hoy' => date('Y-m-d')));
						                $paginas_bd = $query->getResult();
				                        
				                        if (!$paginas_bd)  //validamos que la empresa tenga paginas activas
						                {
						                    $error = $this->get('translator')->trans('No hay Programas disponibles para la empresa. Contacte al administrador del sistema.');
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
			                                
			                                // Estructura de páginas
							 				$paginas = array();
							                foreach ($paginas_bd as $pagina)
					                        {
												$query = $em->createQuery('SELECT COUNT(cp.id) FROM LinkComunBundle:CertiPrueba cp
								                                           WHERE cp.estatusContenido = :activo and cp.pagina = :pagina_id')
								                            ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
								                            					  'pagina_id' => $pagina->getPaginaEmpresa()->getPagina()->getId()));
								                $tiene_evaluacion = $query->getSingleScalarResult();

										        $subPaginas = $f->subPaginasNivel($pagina->getPaginaEmpresa()->getPagina()->getId(), $yml['parameters']['estatus_contenido']['activo'], $empresa_bd->getId());

								                $paginas[$pagina->getPaginaEmpresa()->getPagina()->getId()] = array('id' => $pagina->getPaginaEmpresa()->getPagina()->getId(),
				                                                        											'nombre' => $pagina->getPaginaEmpresa()->getPagina()->getNombre(),
				                                                        											'categoria' => $pagina->getPaginaEmpresa()->getPagina()->getCategoria()->getNombre(),
				                                                        											'foto' => $pagina->getPaginaEmpresa()->getPagina()->getFoto(),
				                                                        											'tiene_evaluacion' => $tiene_evaluacion ? true : false,
				                                                        											'acceso' => $pagina->getPaginaEmpresa()->getAcceso(),
				                                                        											'muro_activo' => $pagina->getPaginaEmpresa()->getMuroActivo(),
				                                                        											'prelacion' => $pagina->getPaginaEmpresa()->getPrelacion() ? $pagina->getPaginaEmpresa()->getPrelacion()->getId() : 0,
				                                                        											'inicio' => $pagina->getPaginaEmpresa()->getFechaInicio()->format('d/m/Y'),
				                                                        											'vencimiento' => $pagina->getPaginaEmpresa()->getFechaVencimiento()->format('d/m/Y'),
				                                                        											'subpaginas' => $subPaginas);
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
			                                $session->set('iniFront', true);
			                                $session->set('sesion_id', $admin_sesion->getId());
			                                $session->set('code', $f->getLocaleCode());
			                                $session->set('usuario', $datosUsuario);
											$session->set('empresa', $empresa);
											$session->set('paginas', $paginas);

									        //1) creo una marca aleatoria en el registro de este usuario
									        //alimentamos el generador de aleatorios
									        mt_srand (time());
									        //generamos un número aleatorio
									        $numero_aleatorio = mt_rand(1000000,999999999);
									        //2) meto la marca aleatoria en la tabla de usuario
									        //$ssql = "update usuario set cookie='$numero_aleatorio' where id_usuario=" . $usuario_encontrado->id_usuario;
									        //mysql_query($ssql);
									        //3) ahora meto una cookie en el ordenador del usuario con el identificador del usuario y la cookie aleatoria
									        setcookie("id_usuario", $session->get('usuario')['id'] , time()+(60*60*24*365));

									        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneById($session->get('usuario')['id']);

									        setcookie("clave_usuario", $usuario->getClave());
									        setcookie("marca_aleatoria_usuario", $numero_aleatorio, time()+(60*60*24*365));


			                                return $this->redirectToRoute('_inicio');
			                                
			                            }
						            }
				                }
		            		}
						}
	                }
	            }
	        }

	        $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa' => $empresa, 
    																								 'error' => $error));
	        return $response;

	    }
	    else {
    		return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Url de la empresa no existe')));
	    }
    }

	public function pdfAction($programa_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();

        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->findOneById($programa_id);

		if($pagina)
		{
	        $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                        'pagina' => $pagina->getId()));

	        $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                'pagina' => $pagina->getId(),
																								'estatusPagina' => $yml['parameters']['estatus_pagina']['completada']) );

			if($pagina_log)
			{
		        $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $session->get('empresa')['id'],
		                                                                                               'entidadId' => $pagina->getId()));
		        if($certificado)//si existe certificado imprimimos el documento
		        {

		        	$fecha = $f->fechaNatural($pagina_empresa->getFechaVencimiento()->format("Y-m-d"));
					
					$aleatorio = $f->generarClave();

			        $contenido = $aleatorio.$session->get('usuario')['apellido'].$session->get('usuario')['nombre'].$pagina->getNombre();
			        $size = 2;

			        $nombre = $aleatorio.date('Y-m-d').'.png';

			        \PHPQRCode\QRcode::png($contenido, "../qr/".$nombre, 'H', $size, 4);

			        $ruta ='<img src="'.'http://'.$_SERVER['HTTP_HOST'].'/formacion2.0/qr/'.$nombre.'">';
			        
			        $file = 'http://'.$_SERVER['HTTP_HOST'].'/uploads/'.$certificado->getImagen();

			        if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['certificado'] )
			        {
			            /*certificado numero 2*/
			            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(10, 35, 0, 0));
			            $certificado_pdf->writeHTML('<page  pageset="new" backimg="'.$file.'" backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm"> 
			            								<title>prueba</title>
			                                            <div style="font-size:24px;">'.$certificado->getEncabezado().'</div>
			                                            <div style="text-align:center; font-size:40px; margin-top:60px; text-transform:uppercase;">'.$session->get('usuario')['apellido'].' '.$session->get('usuario')['nombre'].'</div>
			                                            <div style="text-align:center; font-size:24px; margin-top:70px; ">'.$certificado->getDescripcion().'</div>
			                                            <div style="text-align:center; font-size:50px; margin-top:60px; text-transform:uppercase;">'.$pagina->getNombre().'</div>
			                                            <div style="text-align:center; font-size:14px; margin-top:40px;">'.$fecha.'</div>
                                            			<div style="margin-top:100px; margin-left:950px; ">'.$ruta.'</div>
			                                        </page>');

			            /*certificado numero 3
			            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(48, 60, 0, 0));
			            $certificado_pdf->writeHTML('<page pageset="new" backimg="'.$file.'" backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm"> 
			                                            <div style="text-align:center; font-size:24px;">'.$certificado->getEncabezado().'</div>
			                                            <div style="text-align:center; font-size:40px; margin-top:60px; text-transform:uppercase;">'.$session->get('usuario')['apellido'].' '.$session->get('usuario')['nombre'].'</div>
			                                            <div style="text-align:center; font-size:24px; margin-top:50px; ">'.$certificado->getDescripcion().'</div>
			                                            <div style="text-align:center; font-size:30px; margin-top:50px; text-transform:uppercase;">'.$pagina->getNombre().'</div>
			                                            <div style="text-align:center; font-size:18px; margin-top:10px;">'.$fecha.'</div>
			                                            <div style="margin-top:100px; margin-left:950px; ">'.$ruta.'</div>
			                                        </page>');*/
			            //Generamos el PDF
			            $certificado_pdf->output('certificiado.pdf');
			        }else
			        {
			            if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['constancia'] )
			            {                 
			                $constancia_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(15, 60, 15, 5));
			                $constancia_pdf->writeHTML('<page title="prueba" orientation="portrait" format="A4" pageset="new" backimg="'.$file.'" backtop="20mm" backbottom="20mm" backleft="0mm" backright="0mm">
			                                                <div style=" text-align:center; font-size:20px;">'.$certificado->getEncabezado().'</div>
			                                                <div style="margin-top:30px; text-align:center; color: #00558D; font-size:30px;">'.$session->get('usuario')['apellido'].' '.$session->get('usuario')['nombre'].'</div>
			                                                <div style="margin-top:40px; text-align:center; font-size:20px;">'.$certificado->getDescripcion().'</div>
			                                                <div style="margin-top:30px; text-align:center; color: #00558D; font-size:40px;">'.$pagina->getNombre().'</div>
			                                                <div style="margin-left:30px; margin-top:30px; text-align:left; font-size:16px; line-height:20px;">'.$certificado->getResumen().'</div>
			                                                <div style="margin-top:40px; text-align:center; font-size:14px;">'.$fecha.'</div>
			                                                <div style="margin-top:50px; margin-left:500px; ">'.$ruta.'</div>
			                                            </page>');
			                $constancia_pdf->output('constancia.pdf');
			            }
			        }
			    }else
			    {
			    	return $this->redirectToRoute('_inicio');
			    }
			}else
			{
				return $this->redirectToRoute('_inicio');
			}
		}else
		{
			return $this->redirectToRoute('_inicio');
		}
    }

}