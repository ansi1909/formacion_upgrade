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
		
		// $datosAjax=[
		// 			   'correo'=>$request->request->('correo'),
		// 			   'asunto'=>$request->request->get('asunto'), 
		// 			   'mensaje'=>$request->request->get('mensaje')
		// 		    ];
				    
		$correo=$request->request->get('correo');
		$asunto=$request->request->get('asunto');
		$mensaje=$request->request->get('mensaje');

		$coreoelectronico= \Swift_Message::newInstance()
		                    ->setSubject($asunto)
		                    ->setFrom('tutorvirtual@formacion2puntocero.com')
		                    ->setTo($correo)
		                    ->setBody('<p>'.$mensaje.'</p>','text/html');
		$retorno=$this->get('mailer')->send($coreoelectronico);

		return new Response(json_encode(['respuesta'=>$retorno]),200,array('Content-Type' => 'application/json'));


	}
}