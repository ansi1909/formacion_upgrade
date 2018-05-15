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
		
			//$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

		    $session = new Session();
			$datosAjax=[
						   'correo'=>$request->request->get('correo'),
						   'asunto'=>$request->request->get('asunto'), 
						   'mensaje'=>$request->request->get('mensaje'), 
						   'datosSession'=>(int)$request->request->get('sesion')
					    ];
					    
			
			if ($datosAjax['datosSession']==1) //se verifica si el correo del usuario debe tomarse de la session
				{
					$datosAjax['correo']=$session->get('usuario')['correo'];
				}

			$nombreUsuario=ucwords($session->get('usuario')['nombre']).' '.ucwords($session->get('usuario')['nombre']);


			$coreoelectronico= \Swift_Message::newInstance()
			                    ->setSubject($datosAjax['asunto'])
			                    ->setFrom('tutorvirtual@formacion2puntocero.com')
			                    ->setTo($datosAjax['correo'])
			                    ->setBody( $this->renderView(
                                                              'LinkFrontendBundle:Soporte:EmailSoporte.html.twig',array('nombreUsuario' => $nombreUsuario,'correoUsuario'=>$datosAjax['correo'],'mensaje'=>$datosAjax['mensaje'])),'text/html');

			$retorno=$this->get('mailer')->send($coreoelectronico);

		return new Response(json_encode(['respuesta'=>$retorno]),200,array('Content-Type' => 'application/json'));


	}
}