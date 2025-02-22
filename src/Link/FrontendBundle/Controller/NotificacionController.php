<?php

namespace Link\FrontendBundle\Controller;

use Link\ComunBundle\Entity\CertiMuro;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Yaml\Yaml;

class NotificacionController extends Controller {

	public function ajaxNotiAction(Request $request) {

		$session = new Session();
		$em = $this->getDoctrine()->getManager();
		$f = $this->get('funciones');
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));
		$cPush =  0 ;

		$nuevaNotificacion = array();

		$query = $em->createQuery('SELECT a FROM LinkComunBundle:AdminAlarma a
                                   WHERE a.usuario = :usuario_id
                                   AND a.fechaCreacion <= :hoy
                                   ORDER BY a.id DESC')
			->setMaxResults(10)
			->setParameters(array('usuario_id' => $session->get('usuario')['id'],
				'hoy' => date('Y-m-d 23:59:59')));
		$notificaciones = $query->getResult();
		$usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
		$sonar = 0;
		$html = '';

		foreach ($notificaciones as $notificacion) {
			$vencido = false;
			$mostrar = 0;
			$push = 0;

			$tiempoActual = new \DateTime('now');
			$tiempoCreacion = new \DateTime($notificacion->getFechaCreacion()->format('Y-m-d H:i:s'));
			$diff = $tiempoActual->diff($tiempoCreacion);

			//Si la notificacion se creo hace un minuto o menos se muestra
			if ((!$diff->y) and (!$diff->m) and (!$diff->d) and (!$diff->h)) {
				if( ($diff->i == $yml['parameters']['notificaciones_push']['maximo_minutos'] and !$diff->s ) or (!$diff->i and ($diff->s >= $yml['parameters']['notificaciones_push']['minimo_segundos'] and $diff->s <= $yml['parameters']['notificaciones_push']['maximo_segundos'] )) ){
					$push = 1;
				}
			}

			$auxNotificacion = array(
				"muro" => 0,

			);

			if ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['respuesta_muro'] || $notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_muro']) {

				$muro = $em->getRepository('LinkComunBundle:CertiMuro')->find($notificacion->getEntidadId());

				if ($muro) {
					// Se verifica si el programa al que pertenece el muro está vencido
					$vencido = $f->programaVencido($muro->getPagina()->getId(), $session->get('empresa')['id'], $usuario);

					if (!$vencido) {

						$html .= '<a href="#" data-toggle="modal" data-target="#modalMn" class="click" data=' . $notificacion->getId() . ' titulo="' . $this->get('translator')->trans('Muro') . ': ' . $muro->getPagina()->getNombre() . '.">
                                <input type="hidden" id="muro_id' . $notificacion->getId() . '" value="' . $notificacion->getEntidadId() . '">';
						if ($push) {
							$auxNotificacion["id"] = $notificacion->getId();
							$auxNotificacion["entidad"] = $notificacion->getEntidadId();
							$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
							$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
							$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
							$auxNotificacion["muro"] = 1;
							$auxNotificacion["data_toggle"] = "modal";
							$auxNotificacion["data_target"] = "#modalMn";
							$auxNotificacion["class"] = "click";
							$auxNotificacion["titulo"] = $this->get('translator')->trans('Muro') . ': ' . $muro->getPagina()->getNombre();
						}

					}
					$mostrar = 1;
				}

			} elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['espacio_colaborativo']) {

				$foro = $em->getRepository('LinkComunBundle:CertiForo')->find($notificacion->getEntidadId());

				if ($foro) {
					// Se verifica si el programa al que pertenece el foro está vencido
					$vencido = $f->programaVencido($foro->getPagina()->getId(), $session->get('empresa')['id'], $usuario);
					if (!$vencido) {
						$html .= '<a href="' . $this->generateUrl('_detalleColaborativo', array('foro_id' => $notificacion->getEntidadId())) . '">';
						if ($push) {
							$auxNotificacion["id"] = $notificacion->getId();
							$auxNotificacion["entidad"] = $notificacion->getEntidadId();
							$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
							$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
							$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
							$auxNotificacion["href"] = $this->generateUrl('_detalleColaborativo', array('foro_id' => $notificacion->getEntidadId()));
						}
					}
					$mostrar = 1;
				}

			} elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento']) {

				$evento = $em->getRepository('LinkComunBundle:AdminEvento')->find($notificacion->getEntidadId());
				if ($evento) {
					$vencido = $f->eventoVencido($evento);
					if (!$vencido) {
						$html .= '<a href="' . $this->generateUrl('_calendarioDeEventos', array('view' => 'basicDay', 'date' => $evento->getFechaInicio()->format('Y-m-d'))) . '">';
						$mostrar = 1;
						if ($push) {
							$auxNotificacion["id"] = $notificacion->getId();
							$auxNotificacion["entidad"] = $notificacion->getEntidadId();
							$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
							$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
							$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
							$auxNotificacion["href"] = $this->generateUrl('_calendarioDeEventos', array('view' => 'basicDay', 'date' => $evento->getFechaInicio()->format('Y-m-d')));
						}
					}
				}
			} elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo']) {

				$foro = $em->getRepository('LinkComunBundle:CertiForo')->find($notificacion->getEntidadId());

				if ($foro) {
					// Se verifica si el programa al que pertenece el foro está vencido
					$vencido = $f->programaVencido($foro->getPagina()->getId(), $session->get('empresa')['id'], $usuario);
					if (!$vencido) {
						$html .= '<a href="' . $this->generateUrl('_detalleColaborativo', array('foro_id' => $notificacion->getEntidadId())) . '">';
						if ($push) {
							$auxNotificacion["id"] = $notificacion->getId();
							$auxNotificacion["entidad"] = $notificacion->getEntidadId();
							$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
							$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
							$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
							$auxNotificacion["href"] = $this->generateUrl('_detalleColaborativo', array('foro_id' => $notificacion->getEntidadId()));
						}
					}
					$mostrar = 1;
				}

			} elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['noticia'] || $notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['novedad']) {

				$noticia = $em->getRepository('LinkComunBundle:AdminNoticia')->find($notificacion->getEntidadId());

				if ($noticia) {
					$vencido = $f->noticiaVencida($noticia);
					if (!$vencido) {
						$html .= '<a href="' . $this->generateUrl('_noticiaDetalle', array('noticia_id' => $notificacion->getEntidadId())) . '">';
						if ($push) {
							$auxNotificacion["id"] = $notificacion->getId();
							$auxNotificacion["entidad"] = $notificacion->getEntidadId();
							$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
							$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
							$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
							$auxNotificacion["href"] = $this->generateUrl('_noticiaDetalle', array('noticia_id' => $notificacion->getEntidadId()));
						}
						$mostrar = 1;
					}

				}

			} elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['biblioteca']) {

				$biblioteca = $em->getRepository('LinkComunBundle:AdminNoticia')->find($notificacion->getEntidadId());

				if ($biblioteca) {
					$vencido = $f->noticiaVencida($biblioteca);
					if (!$vencido) {
						$html .= '<a href="' . $this->generateUrl('_bibliotecaDetalle', array('biblioteca_id' => $notificacion->getEntidadId())) . '">';
						if ($push) {
							$auxNotificacion["id"] = $notificacion->getId();
							$auxNotificacion["entidad"] = $notificacion->getEntidadId();
							$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
							$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
							$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
							$auxNotificacion["href"] = $this->generateUrl('_bibliotecaDetalle', array('biblioteca_id' => $notificacion->getEntidadId()));
						}
						$mostrar = 1;
					}

				}

			} elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['medalla']) {
				
				$html .= '<a href="' . $this->generateUrl('_usuariop', array('pagina_id' => $notificacion->getEntidadId())) . '">';
				if ($push) {
					$auxNotificacion["id"] = $notificacion->getId();
					$auxNotificacion["entidad"] = $notificacion->getEntidadId();
					$auxNotificacion["icono"] = $notificacion->getTipoAlarma()->getIcono();
					$auxNotificacion["css"] = $notificacion->getTipoAlarma()->getCss();
					$auxNotificacion["descripcion"] = $notificacion->getDescripcion();
					$auxNotificacion["href"] = $this->generateUrl('_usuariop', array('pagina_id' => $notificacion->getEntidadId()));
				}
				$mostrar = 1;
			}

			if (!$vencido && $mostrar) {

				if ($notificacion->getLeido()) {
					$html .= '<li class="AnunListNotify " data="' . $notificacion->getId() . '">
                                  <input type="hidden" id="tipo_noti' . $notificacion->getId() . '" value="' . $notificacion->getTipoAlarma()->getId() . '">';
				} else {
					$sonar = 1;
					$html .= '<li class="AnunListNotify notiSinLeer leido " data="' . $notificacion->getId() . '">
                              <input type="hidden" id="tipo_noti' . $notificacion->getId() . '" value="' . $notificacion->getTipoAlarma()->getId() . '">';
				}

				$html .= '<div class="anunNotify">
                            <span class="stickerNotify ' . $notificacion->getTipoAlarma()->getCss() . '"><i class="material-icons icNotify">' . $notificacion->getTipoAlarma()->getIcono() . '</i></span>
                                <p class="textNotify text-justify">' . $notificacion->getDescripcion() . '</p>
                        </div>
                    </li>
                </a>';


				if ((($push) and ($cPush < $yml['parameters']['notificaciones_push']['mostrar_maximo'])) and (!$notificacion->getVisto()) ) {
					$cPush++;
					$notificacion->setVisto(TRUE);
					$em->persist($notificacion);
					$em->flush();
					array_push($nuevaNotificacion, $auxNotificacion);
				}

			}

		}

		$html .= '<li class="listMoreNotify text-center">
                    <a href="' . $this->generateUrl('_notificaciones') . '"><span class="moreNotify"><i class="material-icons icMore">add</i>' . $this->get('translator')->trans('Ver más') . '</span></a>
                  </li>';

		$return = json_encode(array('html' => $html,
			'sonar' => $sonar,
			'push' => $nuevaNotificacion,
		));

		return new Response($return, 200, array('Content-Type' => 'application/json'));

	}

	public function ajaxLeidoAction(Request $request) {

		$em = $this->getDoctrine()->getManager();

		$noti_id = $request->request->get('noti_id');

		$notificacion = $em->getRepository('LinkComunBundle:AdminAlarma')->find($noti_id);

		$notificacion->setLeido(TRUE);

		$em->persist($notificacion);
		$em->flush();

		$return = 'ok';

		$return = json_encode($return);
		return new Response($return, 200, array('Content-Type' => 'application/json'));

	}

	public function notificacionesAction(Request $request) {

		$session = new Session();
		$em = $this->getDoctrine()->getManager();
		$f = $this->get('funciones');
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));
		$usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

		$todas = array();
		$no_leidas = array();
		$leidas = array();

		if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id'))) {
			return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
		}
		$f->setRequest($session->get('sesion_id'));

		$query = $em->createQuery('SELECT a FROM LinkComunBundle:AdminAlarma a
                                   WHERE a.usuario = :usuario_id
                                    AND a.fechaCreacion <= :hoy
                                   ORDER BY a.id DESC')
			->setParameters(array('usuario_id' => $session->get('usuario')['id'],
				'hoy' => date('Y-m-d 23:59:59')));
		$alarmas = $query->getResult();

		foreach ($alarmas as $alarma) {
			$vencido = false;
			$mostrar = 0;

			// Se verifica si el programa al que pertenece la alarma está vencido
			if ($alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['respuesta_muro'] || $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_muro']) {
				$muro = $em->getRepository('LinkComunBundle:CertiMuro')->find($alarma->getEntidadId());
				if ($muro) {
					$vencido = $f->programaVencido($muro->getPagina()->getId(), $session->get('empresa')['id'], $usuario);
					$mostrar = 1;
				}
			} elseif ($alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['espacio_colaborativo'] || $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo']) {
				$foro = $em->getRepository('LinkComunBundle:CertiForo')->find($alarma->getEntidadId());
				if ($foro) {
					$vencido = $f->programaVencido($foro->getPagina()->getId(), $session->get('empresa')['id'], $usuario);
					$mostrar = 1;
				}
			} elseif ($alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['noticia'] || $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['novedad'] || $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['biblioteca']) {
				$noticia = $em->getRepository('LinkComunBundle:AdminNoticia')->find($alarma->getEntidadId());
				if ($noticia) {
					$vencido = $f->noticiaVencida($noticia);
					if (!$vencido) {
						$mostrar = 1;
					}

				}
			} elseif ($alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento']) {
				$evento = $em->getRepository('LinkComunBundle:AdminEvento')->find($alarma->getEntidadId());
				if ($evento) {
					$vencido = $f->eventoVencido($evento);
					if (!$vencido) {
						$mostrar = 1;
					}
				}

			} else {
				$mostrar = 1;
			}

			if (!$vencido && $mostrar) {

				$todas[] = array('id' => $alarma->getId(),
					'descripcion' => $alarma->getDescripcion(),
					'css' => $alarma->getTipoAlarma()->getCss(),
					'icono' => $alarma->getTipoAlarma()->getIcono(),
					'leido' => $alarma->getLeido(),
					'tipo' => $alarma->getTipoAlarma()->getid(),
					'entidad' => $alarma->getEntidadId(),
					'fecha' => $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento'] ? $evento->getFechaInicio()->format('Y-m-d') : $alarma->getFechaCreacion()->format('Y-m-d'));

				if ($alarma->getLeido()) {
					$leidas[] = array('id' => $alarma->getId(),
						'descripcion' => $alarma->getDescripcion(),
						'css' => $alarma->getTipoAlarma()->getCss(),
						'icono' => $alarma->getTipoAlarma()->getIcono(),
						'leido' => $alarma->getLeido(),
						'tipo' => $alarma->getTipoAlarma()->getid(),
						'entidad' => $alarma->getEntidadId(),
						'fecha' => $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento'] ? $evento->getFechaInicio()->format('Y-m-d') : $alarma->getFechaCreacion()->format('Y-m-d'));

				} else {
					$no_leidas[] = array('id' => $alarma->getId(),
						'descripcion' => $alarma->getDescripcion(),
						'css' => $alarma->getTipoAlarma()->getCss(),
						'icono' => $alarma->getTipoAlarma()->getIcono(),
						'leido' => $alarma->getLeido(),
						'tipo' => $alarma->getTipoAlarma()->getid(),
						'entidad' => $alarma->getEntidadId(),
						'fecha' => $alarma->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento'] ? $evento->getFechaInicio()->format('Y-m-d') : $alarma->getFechaCreacion()->format('Y-m-d'));
				}

			}

		}

		return $this->render('LinkFrontendBundle:Notificaciones:index.html.twig', array('todas' => $todas,
			'leidas' => $leidas,
			'no_leidas' => $no_leidas));

	}

	public function ajaxNotiMuroAction(Request $request) {
		$session = new Session();
		$em = $this->getDoctrine()->getManager();
		$f = $this->get('funciones');
		$muro_id = $request->query->get('muro_id');
		$html = "";
		$upload = $this->container->getParameter('folders')['uploads'];
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));

		$padre = $em->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

		$query = $em->createQuery('SELECT m FROM LinkComunBundle:CertiMuro m
                                   WHERE m.muro = :muro_id
                                   ORDER BY m.id ASC')
			->setParameter('muro_id', $padre->getId());
		$hijos = $query->getResult();

		$img = $padre->getUsuario()->getFoto() ? $upload . $padre->getUsuario()->getFoto() : $f->getWebDirectory() . '/front/assets/img/user-default.png';
		$autor = $padre->getUsuario()->getId() == $session->get('usuario')['id'] ? $this->get('translator')->trans('Yo') : $padre->getUsuario()->getNombre() . ' ' . $padre->getUsuario()->getApellido();

		$likes = $f->likes($yml['parameters']['social']['muro'], $padre->getId(), $session->get('usuario')['id']);
		$like = $likes["ilike"] ? "ic-lke-act" : "";

		$fechap = $f->sinceTime($padre->getFechaRegistro()->format('Y-m-d H:i:s'));

		$html .= '<div class="msjMuro" >
                    <div class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex">
                                <img class="avatar-img" src="' . $img . '" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">' . $autor . '</div>
                                    <div class="date text-xs color-grey">' . $fechap . '</div>
                                </div>
                            </div>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">' . $padre->getMensaje() . '</p>
                        </div>
                    </div>
                </div>
                <ul class="msjMuroResp">';

		foreach ($hijos as $hijo) {
			$likes = $f->likes($yml['parameters']['social']['muro'], $hijo->getId(), $session->get('usuario')['id']);
			$like = $likes["ilike"] ? "ic-lke-act" : "";
			$cantidad = $likes['cantidad'];

			$img = $hijo->getUsuario()->getFoto() ? $upload . $hijo->getUsuario()->getFoto() : $f->getWebDirectory() . '/front/assets/img/user-default.png';
			$autor = $hijo->getUsuario()->getId() == $session->get('usuario')['id'] ? $this->get('translator')->trans('Yo') : $hijo->getUsuario()->getNombre() . ' ' . $hijo->getUsuario()->getApellido();

			$query = $em->createQuery('SELECT COUNT(l.id) FROM LinkComunBundle:AdminLike l
                                       WHERE l.entidadId = :muro_id')
				->setParameter('muro_id', $hijo->getId());
			$likes = $query->getSingleScalarResult();

			$fechah = $f->sinceTime($hijo->getFechaRegistro()->format('Y-m-d H:i:s'));

			$html .= '<li class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex text-left">
                                <img class="avatar-img" src="' . $img . '" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">' . $autor . '</div>
                                    <div class="date text-xs color-grey">' . $fechah . '</div>
                                </div>
                            </div>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">' . $hijo->getMensaje() . '</p>
                        </div>
                    </li>';
		}

		$html .= '</ul>';

		$return = array('html' => $html);

		$return = json_encode($return);
		return new Response($return, 200, array('Content-Type' => 'application/json'));
	}

	public function ajaxRespuestaComentarioAction(Request $request) {
		$session = new Session();
		$em = $this->getDoctrine()->getManager();
		$f = $this->get('funciones');
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir() . '/config/parametros.yml'));
		$upload = $this->container->getParameter('folders')['uploads'];

		$usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
		$mensaje = $request->request->get('mensaje');
		$muro_id = $request->request->get('muro_id');

		$muro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

		$comentario = new CertiMuro();
		$comentario->setMensaje($mensaje);
		$comentario->setPagina($muro->getPagina());
		$comentario->setUsuario($usuario);
		$comentario->setMuro($muro);
		$comentario->setEmpresa($muro->getEmpresa());
		$comentario->setFechaRegistro(new \DateTime('now'));

		$em->persist($comentario);
		$em->flush();

		$categoria = $this->obtenerProgramaCurso_($comentario->getPagina());
		$tutores = $f->getTutoresEmpresa($usuario->getEmpresa()->getId(), $yml);
		$background = $this->container->getParameter('folders')['uploads'] . 'recursos/decorate_certificado.png';
		$logo = $this->container->getParameter('folders')['uploads'] . 'recursos/logo_formacion_smart.png';
		$link_plataforma = $session->get('empresa')['id'];
		$sendMails = $f->sendMailNotificationsMuro($tutores, $yml, $comentario, $categoria, 'Respondió', $background, $logo, $link_plataforma);

		$img = $usuario->getFoto() ? $upload . $usuario->getFoto() : $f->getWebDirectory() . '/front/assets/img/user-default.png';
		$autor = $this->get('translator')->trans('Yo');
		$fechah = $this->get('translator')->trans('Ahora');
		$likes = 0;

		$html = '<li class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex text-left">
                                <img class="avatar-img" src="' . $img . '" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">' . $autor . '</div>
                                    <div class="date text-xs color-grey">' . $fechah . '</div>
                                </div>
                            </div>
                            <a href="" class="mr-0 text-sm color-light-grey">
                                <i class="material-icons mr-1 text-sm color-light-grey">thumb_up</i> ' . $likes . '
                            </a>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">' . $mensaje . '</p>
                        </div>
                    </li>';

		$return = array('html' => $html);

		$return = json_encode($return);
		return new Response($return, 200, array('Content-Type' => 'application/json'));
	}

	public function obtenerProgramaCurso_($pagina) {
		while ($pagina->getPagina()) {
			$pagina = $pagina->getPagina();
		}

		$categoria = $pagina->getCategoria();

		return ['categoria' => $categoria->getNombre(),
			'nombre' => $pagina->getNombre(),
			'programa_id' => $pagina->getId()];

	}

}