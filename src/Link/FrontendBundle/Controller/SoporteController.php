<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;


class SoporteController extends Controller
{

	public function _ajaxEnviarMailSoporteAction(Request $request)
	{
		
		$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
		$f = $this->get('funciones');
        $session = new Session();
		$datosAjax =
		[
			'correo'=>$request->request->get('correo'),
			'asunto'=>$request->request->get('asunto'), 
			'mensaje'=>$request->request->get('mensaje'), 
			'datosSession'=>(int)$request->request->get('sesion')
		];

		if ($datosAjax['datosSession']==1)
		{
			$datosAjax['correo']=$session->get('usuario')['correo'];
	    }
			
		$nombreUsuario=ucwords($session->get('usuario')['nombre']).' '.ucwords($session->get('usuario')['nombre']).' ('.ucwords($session->get('empresa')['nombre']).')';

		$datosCorreo=
		[
			'twig'=>$yml['parameters']['correo_soporte']['plantilla'],
			'asunto'=>$yml['parameters']['correo_soporte']['asunto'],
			'remitente'=>$yml['parameters']['correo_soporte']['remitente'],
			'destinatario'=>$yml['parameters']['correo_soporte']['destinatario'],
			'datos'=>
			[
				'nombreUsuario'=>$nombreUsuario,
				'correoUsuario'=>$datosAjax['correo'],
				'asuntoMensaje'=>$datosAjax['asunto'],
				'mensaje'=>$datosAjax['mensaje']
			]
		];

		$retorno=$f->sendEmail($datosCorreo);
		return new Response(json_encode(['respuesta'=>$retorno]),200,array('Content-Type' => 'application/json'));


	}
}