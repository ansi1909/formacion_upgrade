<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;




class SoporteController extends Controller
{

	
	
	
	protected function enviarMail($datosAjax,$nombreUsuario,$remitente,$asunto)
	{
		$sendMail= \Swift_Message::newInstance()
			        ->setSubject($asunto)
			        ->setFrom($remitente)
			        ->setTo($datosAjax['correo'])
			        ->setBody( $this->renderView(
                                                  'LinkFrontendBundle:Soporte:EmailSoporte.html.twig',array('nombreUsuario' => $nombreUsuario,'datos'=>$datosAjax)
                                                 ),'text/html'
			                 );

		$retorno=$this->get('mailer')->send($sendMail);

		return $retorno;
	}


	protected function tipoUsuario($session)
	{
        $iSparticipante=$session->get('usuario')['participante'];
        $iStutor=$session->get('usuario')['tutor'];

        if ($iStutor) 
        {
        	$retorno='Tutor';
        }
        else if($iSparticipante)
        {
            $retorno='Participante';
        }

        return $retorno;
	}



	
	public function _ajaxEnviarMailSoporteAction(Request $request)
	{
		
			$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
			$f = $this->get('funciones');

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
			
			$nombreUsuario=ucwords($session->get('usuario')['nombre']).' '.ucwords($session->get('usuario')['nombre']).' ('.ucwords($session->get('empresa')['nombre']).')';

			$datosCorreo=[
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

			//$rolUsuario=$this->tipoUsuario($session);//preparamos el rol del usuario para enviarlo por correo electronico

			//$retorno=$this->enviarMail($datosAjax,$nombreUsuario,$yml['parameters']['correo_soporte']['remitente'],$yml['parameters']['correo_soporte']['asunto']);


		return new Response(json_encode(['respuesta'=>$retorno]),200,array('Content-Type' => 'application/json'));


	}
}