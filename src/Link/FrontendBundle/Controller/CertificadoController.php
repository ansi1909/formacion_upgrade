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

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
        	return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }

        $f->setRequest($session->get('sesion_id'));

        $uploads = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();

        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $categoria = $pagina->getCategoria()->getNombre();

        $contenidoMod = '<div style="font-size:21px;text-align:center"> <h1>'.$this->get('translator')->trans('Contenido del').' '.$categoria.': '.$pagina->getNombre().'</h1>';

        $item = 1;
        foreach ($session->get('paginas')[$programa_id]['subpaginas'] as $modulo)
        {
        	$contenidoMod .= '<h2> * '.$this->get('translator')->trans('Módulo').' '.$item.': '.$modulo['nombre'].'</h2>';
        	$item += 1;
        }
        $contenidoMod .= '</div>';

		if ($pagina)
		{

			$certificado = $f->getCertificado($session->get('empresa')['id'], $values['parameters']['tipo_certificado'], $pagina->getId());

	        if ($certificado)//si existe certificado imprimimos el documento
	        {

	        	//cambiamos la fecha al formato aaaa-mm-dd
	            $fn_array = explode("/", $session->get('paginas')[$pagina->getId()]['vencimiento']);
	            $d = $fn_array[0];
	            $m = $fn_array[1];
	            $a = $fn_array[2];
	            $fecha_vencimiento = "$a-$m-$d";

	        	$fecha = $f->fechaNatural($fecha_vencimiento);

				$pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                	'pagina' => $pagina->getId() ));

		        $size = 2;
				$contenido = $uploads['parameters']['folders']['verificar_codigo_qr'].'/'.$pagina_log->getId();

		        $nombre = $pagina->getId().'_'.$session->get('usuario')['id'].'.png';

 				$directorio = $uploads['parameters']['folders']['dir_uploads'].'recursos/qr/'.$session->get('empresa')['id'].'/'.$nombre;

		       	\PHPQRCode\QRcode::png($contenido, $directorio, 'H', $size, 4);

		        $ruta ='<img src="'.$directorio.'">';

				$file = $uploads['parameters']['folders']['dir_uploads'].$certificado->getImagen();

		        if ($certificado->getTipoImagenCertificado()->getId() == $values['parameters']['tipo_imagen_certificado']['certificado'] )
		        {

		            /*certificado numero 2*/
		            if ($pagina_log->getPagina()->getCategoria()->getNombre() == 'Curso') {

		            	$comodines = array('%%categoria%%');
			            $reemplazos = array('Curso');
			            $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());
		            }
		            else{
		            	$comodines = array('%%categoria%%');
			            $reemplazos = array('Programa');
			            $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());
		            }

            		$certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(0, 15, 0, 0));
		            $certificado_pdf->writeHTML('<page title="Certificado" pageset="new" backimg="'.$file.'" backimgw="90%" backimgx="center">
		            								<div style="margin-left:910px; ">'.$ruta.'</div>
		                                            <div style="font-size:22px; margin-top:90px; text-align:center">'.$certificado->getEncabezado().'</div>
                                            		<div style="text-align:center; font-size:40px; margin-top:25px; text-transform:uppercase;">'.$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'].'</div>
		                                            <div style="text-align:center; font-size:24px; margin-top:25px; ">'.$descripcion.'</div>
		                                            <div style="text-align:center; font-size:40px; margin-top:25px; text-transform:uppercase;">'.$pagina->getNombre().'</div>
		                                            <div style="text-align:center; margin-top:40px; font-size:14px;">'.$this->get('translator')->trans('Fecha inicio').':'.$pagina_log->getFechaInicio()->format("d/m/Y").'   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->get('translator')->trans('Fecha fin').':'.$pagina_log->getFechaFin()->format("d/m/Y").' </div>
		                                            <div style="text-align:center; margin-top:15px; font-size:14px;">'.$this->get('translator')->trans('Equivalente a').': '.$pagina->getHorasAcademicas().' hrs. '.$this->get('translator')->trans('académicas').'</div>
		                                        </page>');

		            $certificado_pdf->writeHtml('<page title="prueba" pageset="new"  backimgw="90%" backimgx="center">'
		            								.$contenidoMod.'
											   	</page>');

		            //Generamos el PDF
		            $certificado_pdf->output('certificado.pdf');

		        }
		        else {
		            if ($certificado->getTipoImagenCertificado()->getId() == $values['parameters']['tipo_imagen_certificado']['constancia'] )
		            {
                		$constancia_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 60, 10, 5));
		                $constancia_pdf->writeHTML('<page  orientation="portrait" format="A4" pageset="new" backimg="'.$file.'" backtop="20mm" backbottom="20mm" backleft="0mm" backright="0mm">
		                                                <div style=" text-align:center; font-size:20px;">'.$certificado->getEncabezado().'</div>
		                                                <div style="margin-top:30px; text-align:center; color: #00558D; font-size:30px;">'.$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'].'</div>
		                                                <div style="margin-top:40px; text-align:center; font-size:20px;">'.$certificado->getDescripcion().'</div>
		                                                <div style="margin-top:30px; text-align:center; color: #00558D; font-size:40px;">'.$pagina->getNombre().'</div>
		                                                <div style="margin-left:30px; margin-top:30px; text-align:left; font-size:16px; line-height:20px;">'.$certificado->getTitulo().'</div>
		                                                <div style="margin-top:50px; margin-left:500px; ">'.$ruta.'</div>
		                                            </page>');
		                $constancia_pdf->output('constancia.pdf');
		            }
		        }
		    }
		    else {
		    	return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'certificado'));
		    }

		}

    }

	public function notasAction($programa_id)
    {
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
        	return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }

        $f->setRequest($session->get('sesion_id'));

        $uploads = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();

		$nota = 0;

		//se consulta la informacion de la pagina padre
		$query = $em->createQuery('SELECT pl.nota as nota FROM LinkComunBundle:CertiPruebaLog pl
                                   JOIN pl.prueba p
                                   WHERE p.pagina = :pagina
                                   and pl.estado = :estado
                                   and pl.usuario = :usuario')
                    ->setParameters(array('usuario' => $session->get('usuario')['id'],
                						  'pagina' => $session->get('paginas')[$programa_id]['id'],
                                          'estado' => $values['parameters']['estado_prueba']['aprobado']))
                    ->setMaxResults(1);
		$nota_programa = $query->getResult();

		//return new response(var_dump($nota_programa));

		foreach ($nota_programa as $n)
		{
			$nota = (double)$n['nota'];
			//return new response($nota.'aqui');
		}

		$cantidad_intentos = '';
		$query = $em->createQuery('SELECT count(pl.id) FROM LinkComunBundle:CertiPruebaLog pl
                                   JOIN pl.prueba p
                                   WHERE p.pagina = :pagina
                                   and pl.usuario = :usuario')
                    ->setParameters(array('usuario' => $session->get('usuario')['id'],
                						  'pagina' => $session->get('paginas')[$programa_id]['id']));
        $cantidad_intentos = $query->getSingleScalarResult();
        $cantidad_intentos = $cantidad_intentos ? $cantidad_intentos : '';

		$programa_aprobado = array('id' => $session->get('paginas')[$programa_id]['id'],
							       'nombre' => $session->get('paginas')[$programa_id]['nombre'],
							       'categoria' => $values['parameters']['categoria']['programa'],
							       'nota' => $nota,
								   'cantidad_intentos' => $cantidad_intentos ? $cantidad_intentos : '');

        if (count($session->get('paginas')[$programa_id]['subpaginas']))
        {
			$subpaginas_ids = $f->hijas($session->get('paginas')[$programa_id]['subpaginas']);


			$programa_aprobado = $f->notasPrograma($subpaginas_ids, $session->get('usuario')['id'], $values['parameters']['estado_prueba']['aprobado']);
        }

		if ($programa_aprobado)
		{


		    $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],'pagina'=>$programa_id));

	        if($session->get('empresa')['logo']!='')
	        {
            	$file = $uploads['parameters']['folders']['dir_uploads'].$session->get('empresa')['logo'];

	        }
            else {
            	$file =  $uploads['parameters']['folders']['dir_project'].'web/img/logo_formacion_smart.png';
            }
            $firma =  $uploads['parameters']['folders']['dir_project'].'web/img/firma.png';

			//return new response($file);

		    $constancia_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(15, 10, 15, 5));

			$html = "<!DOCTYPE html>
				<html lang='es'>
				    <head>
				        <meta charset='UTF-8'>
				        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
				        <meta http-equiv='X-UA-Compatible' content='ie=edge'>
				        <title>Constancia de notas</title>
				        <style type='text/css'>
							body.detalle-noticias {
								background-color: #fff; }
							.constancia {
						    	padding: 2px 0px; }
							.constancia .imgConst {
								width: 250px;
								height: 72px; }
						    .constancia .imgFirma{
								width: 650px;
								height: 110px;
							}
							.constancia .tituloConst {
							    color: #5CAEE6;
							    font-size: 2.25rem;
							    font-weight: 400;
							    line-height: 10px;}
							.constancia .datosParticipante {
							    margin-top: 1rem;
							    border-radius: 1rem;
							    background: #FAFAFA; }
							.constancia .textConst {
							    color: #212529;
							    font-size: 1.5rem;
							    font-weight: 400;
							    line-height: 25px;
							    text-aling:justify; }
							.constancia .datosParticipante .tituloPart, .constancia .tituloPart {
								padding: 4px;
							  	color: #5C6266;
							  	font-size: 1.125rem;
							  	font-weight: 400;
							  	line-height: 10px; }
							.row {
							    display: flex; flex-wrap: wrap; padding: 4px; }
							.center {
				        		text-align: center;}
							.table-notas {
					        	font-size: 1.125rem;
					        	line-height: 10px;
					        	font-weight: 300;
					        	text-align: left; }
						    .table-notas thead th {
						    	line-height: 10px;
						    	color: #212529;
						    	font-weight: 300;
							    text-align: center;
							  	    padding: 6px; }
							.table-notas tbody tr {
							    color: #5CAEE6; }
							.table-notas tbody td {
							    border-bottom:1px solid #CFD1D2;
							    padding: 4px;
								cellpadding: 0;
								cellspacing: 0; }
							hr {
								color: #99c51b;
								background-color: #99c51b;
								height: 5px;
							}
					    </style>
			        </head>
				    <body class='detalle-noticias'>
				        <div class='constancia'>
			                <div class='row center'>
								<img class='imgConst' src='".$file."'/>
		                        <h3 class='tituloConst'>".$this->get('translator')->trans('Constancia de Notas')."</h3>
			                </div>
			                <div class='row'>
		                        <div class='datosParticipante'>
		                            <div class='row'>
	                                    <span class='tituloPart'>".$this->get('translator')->trans('Participante').": <span>".$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido']."</span></span>
		                            </div>
		                            <div class='row'>
	                                    <span class='tituloPart'>".$this->get('translator')->trans('Correo electrónico').": <span>".$session->get('usuario')['correo']."</span></span>
		                            </div>
		                            <div class='row'>
	                                    <span class='tituloPart'>".$this->get('translator')->trans('Programa').": <span>".$session->get('paginas')[$programa_id]['nombre']."</span></span>
		                            </div>
		                            <div class='row'>
	                                    <span class='tituloPart'>".$this->get('translator')->trans('Inicio del programa').": <span>".$pagina_log->getFechaInicio()->format('d/m/Y')."</span></span>
		                            </div>
		                            <div class='row'>
	                                    <span class='tituloPart'>".$this->get('translator')->trans('Fin del programa').": <span>".$pagina_log->getFechaFin()->format('d/m/Y')."</span></span>
		                            </div>
		                        </div>
			                </div>
			                <div class='row'>
		                        <p class='textConst'>".$this->get('translator')->trans('Por medio de la presente se certifica que el participante arriba indicado ha cursado y aprobado las pruebas correspondientes a').":</p>
			                </div>
			                <div class='row'>";
			                	$puntaje = 0;
			                	$indice = 0;
								if (count($session->get('paginas')[$programa_id]['subpaginas']))
								{
									$valor = '';
									$style = '';
									$guion = '';
									//return new response($nota);
									$puntaje = $puntaje + $nota;
									$nota = $cantidad_intentos != '' ? round($nota, 2) : '';
		                    		$html .= "<table class='table-notas'>
			                            <thead>
			                                <tr>
			                                    <th style='width: 380;'>".$this->get('translator')->trans('Módulos')."</th>
			                                    <th style='width: 100;'>".$this->get('translator')->trans('Puntaje')."</th>
			                                </tr>
			                            </thead>
			                            <tbody>
											<tr style='font-size: 14px; font-weight: 300;'>

								            </tr>";
									foreach ($programa_aprobado as $programa)
							        {

							        	if ($programa['categoria'] == $values['parameters']['categoria']['modulo'])
							        	{
							        		$valor = 20;
							        		$guion = '';
							        		$puntaje = $puntaje+$programa['nota'];

											if($programa['nota'] != 0)
											{
												$indice = $indice+1;
												$nota = $programa['nota'];
											}
		        							$html .= "<tr ".$style.">
								               			<td style='padding-left:".$valor."px;'>".$guion.$programa['nombre']."</td>
										               	<td class='center'>".round($nota,2)."</td>
										            </tr>";
							        	}
							        	// else {
								        // 	if ($programa['categoria'] == $values['parameters']['categoria']['materia'])
								        // 	{
								        // 		$valor = 30;
								        // 		$guion = '+ ';
								        // 	}
								        // 	else {
								        // 		if ($programa['categoria'] == $values['parameters']['categoria']['leccion'])
								        // 		{
								        // 			$valor = 40;
								        // 			$guion = '- ';
								        // 		}
								        // 	}
								        // }

									}
									if ($indice > 0)
									{
										$html .= "<tr style='font-size:16px; font-weight:300; font-color:#000000;'>
								               		<td style='color:#000000;'>".$this->get('translator')->trans('Puntaje definitivo del programa').":</td>
								               		<td style='color:#000000;' class='center'>".round($puntaje/$indice,2)."</td>
								            	</tr>";
									}
									$margin = '50';
									$html .= "</tbody>
	  			           		        </table>";
								}
								else {
									$puntaje = $programa_aprobado['nota'];
									if ($programa_aprobado['nota'] != 0)
									{
										$nota = $programa_aprobado['nota'];
									}
									else {
										$nota = "N/A";
									}
									$margin = '200';
									$html .= "<table class='table-notas' cellpadding='0' cellspacing='0'>
				                            	<thead>
				                                	<tr>
				                                    	<th style='width: 380;'>".$this->get('translator')->trans('Módulos')."</th>
				                                    	<th style='width: 100;'>".$this->get('translator')->trans('Puntaje')."</th>
				                                	</tr>
				                            	</thead>
				                            	<tbody>
													<tr style='font-size: 14px; font-weight: 300;'>
									               		<td style='padding-left:10px;'>".$session->get('paginas')[$programa_id]['nombre']."</td>
									               		<td class='center'>".round($nota,2)."</td>
									            	</tr>
												</tbody>
						                	</table>";
								}
        					$html .= "</div>
		                	<div class='row' style='margin-top:".$margin."px;'>
								<table text-align='center' width='800' border=0' height='150'>
									<tr>
									    <td><img class='imgFirma' src='".$firma."'/> </td>
									</tr>
									<tr>

									</tr>
								</table>
							</div>
							<div class='row' style='margin-top:".$margin."px;'>
								<hr>
							</div>
				        </div>
				    </body>
				</html>";

			$constancia_pdf->WriteHTML($html);
            $constancia_pdf->output('notas.pdf');

		}
		else {
			return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'prueba'));
	    }

    }
}