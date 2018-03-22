<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Spipu\Html2Pdf\Html2Pdf;

class CertificadoController extends Controller
{

	public function certificadoAction($programa_id)
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
			$certificado=false;
			//consultamos el certificado por pagina
	        $certificado_pagina = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $session->get('empresa')['id'],
	        																							  'tipoCertificado' => '2',
                                                                                               			  'entidadId' => $pagina->getId()));

        	if($certificado_pagina)
        	{
        		$certificado = $certificado_pagina;
        	}else
        	{
        		//consultamos el certificado por grupo de paginas
		        $certificado_grupos = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $session->get('empresa')['id'],
		        																							  'tipoCertificado' => 3,
	                                                                                               			  'entidadId' => $pagina->getId()));
				if($certificado_grupos)
	        	{
	        		$certificado = $certificado_grupos;

	        	}else
	        	{
	        		//consultamos el certificado por empresa
			        $certificado_empresas = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $session->get('empresa')['id'],
		        					     																		    'tipoCertificado' => 1,
		                                                                                               			    'entidadId' => null));
			        if($certificado_empresas)
		        	{
		        		$certificado = $certificado_empresas;

		        	}
	        	}
        	}

	        if($certificado)//si existe certificado imprimimos el documento
	        {
	        	//cambiamos la fecha al formato aaaa-mm-dd
	            $fn_array = explode("/", $session->get('paginas')[$pagina->getId()]['vencimiento']);
	            $d = $fn_array[0];
	            $m = $fn_array[1];
	            $a = $fn_array[2];
	            $fecha_vencimiento = "$a-$m-$d";

	        	$fecha = $f->fechaNatural($fecha_vencimiento);
				
				$aleatorio = $f->generarClave();

		        $contenido = $aleatorio.$session->get('usuario')['apellido'].$session->get('usuario')['nombre'].$pagina->getNombre();
		        $size = 2;

		        $nombre = $pagina->getId().'_'.$session->get('usuario')['id'].'.png';

 				$directorio = $yml['parameters']['folders']['dir_uploads'].'recursos/qr/'.$session->get('empresa')['id'].'/'.$nombre;

		        \PHPQRCode\QRcode::png($contenido, $directorio, 'H', $size, 4);

		        $ruta ='<img src="'.$directorio.'">';

		        $file = $yml['parameters']['folders']['dir_uploads'].$certificado->getImagen();

		        if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['certificado'] )
		        {
		            /*certificado numero 2*/
		            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(10, 35, 0, 0));
		            $certificado_pdf->writeHTML('<page title="prueba" pageset="new" backimg="'.$file.'" backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm"> 
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
		                $constancia_pdf->writeHTML('<page  orientation="portrait" format="A4" pageset="new" backimg="'.$file.'" backtop="20mm" backbottom="20mm" backleft="0mm" backright="0mm">
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
            	return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos, la empresa no ha registrado certificado para está página.')));
		    }			
		/*}else
		{
			return $this->redirectToRoute('_inicio');*/
		}
    }

}