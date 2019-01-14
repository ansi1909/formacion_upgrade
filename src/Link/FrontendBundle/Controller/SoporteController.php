<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;


class SoporteController extends Controller
{

	public function ajaxEnviarMailSoporteAction(Request $request)
	{
		
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
		$f = $this->get('funciones');
        $session = new Session();
		$datosAjax =
		[
			'correo' => $request->request->get('correo'),
			'asunto' => $request->request->get('asunto'), 
			'mensaje' => $request->request->get('mensaje'), 
			'datosSession' => (int)$request->request->get('sesion')
		];

		if ($datosAjax['datosSession'] == 1)
		{
			$datosAjax['correo'] = $session->get('usuario')['correo'];
	    }
			
		$nombreUsuario = ucwords($session->get('usuario')['nombre']).' '.ucwords($session->get('usuario')['nombre']).' ('.ucwords($session->get('empresa')['nombre']).')';

		$background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
        $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion_smart.png';
        $link_plataforma = $this->container->getParameter('link_plataforma').$session->get('empresa')['id'];
		$datosCorreo =
		[
			'twig' => $yml['parameters']['correo_soporte']['plantilla'],
			'asunto' => $yml['parameters']['correo_soporte']['asunto'],
			'remitente' => $this->container->getParameter('mailer_user'),
			'destinatario' => $yml['parameters']['correo_soporte']['destinatario'],
			'datos' =>
			[
				'nombreUsuario' => $nombreUsuario,
				'correoUsuario' => $datosAjax['correo'],
				'asuntoMensaje' => $datosAjax['asunto'],
				'mensaje' => $datosAjax['mensaje'],
				'background' => $background,
				'logo' => $logo,
				'link_plataforma' => $link_plataforma,
				'empresa' => $session->get('empresa')['nombre']
			]
		];

		$retorno = $f->sendEmail($datosCorreo);
		return new Response(json_encode(['respuesta'=>$retorno]),200,array('Content-Type' => 'application/json'));


	}
}