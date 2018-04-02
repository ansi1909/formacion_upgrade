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

 				$directorio = $this->container->getParameter('folders')['dir_uploads'].'recursos/qr/'.$session->get('empresa')['id'].'/'.$nombre;

		        \PHPQRCode\QRcode::png($contenido, $directorio, 'H', $size, 4);

		        $ruta ='<img src="'.$directorio.'">';

		        $file = $this->container->getParameter('folders')['dir_uploads'].$certificado->getImagen();

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
		                                                <div style="margin-left:30px; margin-top:30px; text-align:left; font-size:16px; line-height:20px;">'.$certificado->getTitulo().'</div>
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
		}
    }


	public function notasAction($programa_id)
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

		//return new response(var_dump($session->get('paginas')[$programa_id]));

 		// se consulta si la pagina es padres
		$query = $em->createQuery('SELECT p FROM LinkComunBundle:CertiPagina p
                                   WHERE p.id = :pagina')
                    ->setParameters(array('pagina' => $programa_id));
        $pagina = $query->getResult();

 		// Estructura de páginas
        $paginas = array();
        foreach ($pagina as $pag)
        {
            $query = $em->createQuery('SELECT COUNT(cp.id) FROM LinkComunBundle:CertiPrueba cp
                                       WHERE cp.estatusContenido = :activo and cp.pagina = :pagina_id')
                        ->setParameters(array('activo' => $datos['yml']['estatus_contenido']['activo'],
                                              'pagina_id' => $pagina->getPaginaEmpresa()->getPagina()->getId()));
            $tiene_evaluacion = $query->getSingleScalarResult();

            $subPaginas = $this->prueba($pagina->getPaginaEmpresa()->getPagina()->getId(), $datos['yml']['estatus_contenido']['activo'], $datos['empresa']['id']);

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

//return new response(var_dump($paginas_bd));

        if($paginas_bd)
        {
        	$programa_aprobado = $f->notasPrograma($session->get('paginas')[$programa_id], $session->get('usuario')['id'], $yml['parameters']['estado_prueba']['aprobado']);
        }else
        {
			/*$pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($programa['id']);
			if(!$pagina->getPagina())
			{*/
				$query = $em->createQuery('SELECT pl.nota FROM LinkComunBundle:CertiPruebaLog pl
		                                   JOIN pl.prueba p
		                                   WHERE p.pagina = :pagina 
		                                   and pl.estado = :estado
		                                   and pl.usuario = :usuario')
		                    ->setParameters(array('usuario' => $session->get('usuario')['id'],
		                						  'pagina' => $programa_id,
		                                          'estado' => $yml['parameters']['estado_prueba']['aprobado']));
		        $nota_programa = $query->getSingleScalarResult();

				$query = $em->createQuery('SELECT count(pl.id) FROM LinkComunBundle:CertiPruebaLog pl
		                                   JOIN pl.prueba p
		                                   WHERE p.pagina = :pagina 
		                                   and pl.usuario = :usuario')
		                    ->setParameters(array('usuario' => $session->get('usuario')['id'],
		                						  'pagina' => $programa_id));
		        $cantidad_intentos = $query->getSingleScalarResult();

		        $programa_aprobado[]=array('nota' => $nota_programa,
                                   'cantidad_intentos' => $cantidad_intentos);
	        //}
        }

return new response(var_dump($programa_aprobado));


		if($programa_aprobado)
		{
			
        	//cambiamos la fecha al formato aaaa-mm-dd
          /*  $fn_array = explode("/", $session->get('paginas')[$pagina->getId()]['vencimiento']);
            $d = $fn_array[0];
            $m = $fn_array[1];
            $a = $fn_array[2];
            $fecha_vencimiento = "$a-$m-$d";

        	$fecha = $f->fechaNatural($fecha_vencimiento);
			
			*/
	        if($session->get('empresa')['logo']!='')
            	$file =  $this->container->getParameter('folders')['dir_project'].$session->get('empresa')['logo'];
            else
            	$file =  $this->container->getParameter('folders')['dir_project'].'web/img/logo_formacion.png'; 

            $constancia_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(15, 10, 15, 5));

			$html="<!DOCTYPE html>
					<html lang='en'>
					<head>
					    <meta charset='UTF-8'>
					    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
					    <meta http-equiv='X-UA-Compatible' content='ie=edge'>
			 		    <title>Login</title>
				        <style>
					        .center{
					        	text-align: center;
					        }
					        .color {
							    color: #0E5586;
							}
					    </style> 
					</head>
					<body>
				        <div class='row'>
	                        <div class='color center'>
								<img class='center' src='".$file."' width='132' height='72' />
	                            <h3> Constancia de Notas </h3>
	                        </div>
	                        <div>
                                <span class=''>Participante:</span>
	                            <span class=''>".$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido']."</span>
	                        </div>
	                        <div>
                                <span class=''>Email:</span>
	                            <span class=''>".$session->get('usuario')['correo']."</span>
	                        </div>
	                        <div>
                                <span class=''>Fecha de Creacion del Programa:</span>
	                            <span class=''>".$session->get('paginas')[$programa_id]['inicio']."</span>
	                        </div>
	                        <div>
                                <span class=''>Programa:</span>
	                            <span class=''>".$session->get('paginas')[$programa_id]['nombre']."</span>
	                        </div>	                        
	                        <br>
							<div style='text-aling:justify;'>
	                            <h3>Por medio de la presente se certifica que el participante arriba indicado ha cursado y aprobado las pruebas correspondientes a:</h3>
	                        </div>";
							if(count($session->get('paginas')[$programa_id]['subpaginas']))
							{
		                    $html .= "<table border='1' align='left'>
							            <tr>
							               <td>Módulo</td>
							               <td>Indice de Repitencias</td>
							               <td>Puntaje</td>
							            </tr>";
								foreach ($rograma_aprobado as $programa)
						        {
					        $html .= "  <tr>
							               <td>".$programa['nombre']."</td>
							               <td>".$programa['cantidad_intentos']."</td>
							               <td>".$programa['nota']."</td>
							            </tr>";

								}
					        $html .= "  <tr>
							               <td colspan='2'>Puntaje definitivo del programa:</td>
							               <td>".$puntaje."</td>
							            </tr>
	  			        			  </table>";
							}else
							{
							$html .= "<table border='1' align='left'>
						            <tr>
						               <td>Puntaje definitivo del programa:</td>
						               <td>".$programa_aprobado[0]['nota']."</td>
						            </tr>
						            <tr>
						               <td>Indice de Repitencias:</td>
						               <td>".$programa_aprobado[0]['cantidad_intentos']."</td>
						            </tr>
					              </table>";
							}
				        $html .= "</div>
					</body>
					</html>";

			$constancia_pdf->WriteHTML($html);
            $constancia_pdf->output('notas.pdf');
		    
		}else
	    {
        	return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos, la empresa no ha registrado certificado para está página.')));
	    }
    }
}