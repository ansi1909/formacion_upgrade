<?php

// formacion2.0/src/Link/BackendBundle/Command/UsersScheduledCommand.php

namespace Link\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\Parameter;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminCorreo;
use Link\ComunBundle\Entity\AdminCorreosDiarios;


class UsersScheduledCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:recordatorio-programados')
        	 ->setDescription('Envía por correo notificaciones programadas y recordatorios de la fecha actual');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
        $base = $yml['parameters']['link_plataforma'];

        //consulatr cuantos correos se an enviado el dia de hoy
        $totalCorreosHoy = $em->getRepository('LinkComunBundle:AdminCorreosDiarios')->findOneByfecha(new \DateTime("NOW"));

        if(!$totalCorreosHoy){
            $output->writeln('No se han enviados correos e dia de hoy. Se iniciara el conteo');
            $correosHoy = new AdminCorreosDiarios();
            $correosHoy->setfecha(new \DateTime("NOW"));
            $correosHoy->setCantidad(0);
            $em->persist($correosHoy);
            $em->flush();
            $cantidadHoy = 0;
        }else{
            $cantidadHoy = $totalCorreosHoy->getCantidad();
            $output->writeln('Se han enviado : '.$totalCorreosHoy->getCantidad());
        }
            $output->writeln('Hoy : '.$cantidadHoy.' ,'.'Limite diario: '.$yml2['parameters']['limite_correos_notificaciones']['diario']);
        if($cantidadHoy  < (integer) $yml2['parameters']['limite_correos_notificaciones']['diario']){
				$hoy = date('Y-m-d');
                $ayer = new \DateTime("NOW");
                $ayer->modify("-1 day");

				$query = $em->getConnection()->prepare('SELECT fnrecordatorios_usuarios(:pfechaHoy,:pfechaAyer) AS resultado;');
				$query->bindValue(':pfechaHoy', $hoy, \PDO::PARAM_STR);
                $query->bindValue(':pfechaAyer', $ayer->format('Y-m-d'), \PDO::PARAM_STR);
				$query->execute();
				$r = $query->fetchAll();
			    $output->writeln(count($r));
				$notificaciones_id = array();

				//error_log('-------------------CRON JOB DEL DIA '.date('d/m/Y H:i').'---------------------------------------------------');
				$output->writeln('FECHA DE HOY: '.$hoy);

				$output->writeln('CANTIDAD DE NOTIFICACIONES TOTAL: '.count($r));

				$background = $yml['parameters']['folders']['uploads'].'recursos/decorate_certificado.png';
				$logo = $yml['parameters']['folders']['uploads'].'recursos/logo_formacion.png';
				$footer = $yml['parameters']['folders']['uploads'].'recursos/footer.bg.form.png';
				//$logo = ''; // Solo por requerimiento para BANFONDESA
				$j = 0; // Contador de correos exitosos
				$np_id = 0; // notificacion_programada_id

				for ($i = 0; $i < count($r); $i++)
				{
                     $output->writeln('J : '.$j.' ,'.'cantidadHoy '.$cantidadHoy);
					if ($j == $yml2['parameters']['limite_correos_notificaciones']['cron'] || $cantidadHoy >= (integer) $yml2['parameters']['limite_correos_notificaciones']['diario'] )
					{
						// Cantidad tope de correos en una corrida
						break;
					}

					// Limpieza de resultados
					$reg = substr($r[$i]['resultado'], strrpos($r[$i]['resultado'], '{"')+2);
					$reg = str_replace('"}', '', $reg);

					// Se descomponen los elementos del resultado
					list($np_id, $usuario_id, $login, $clave, $nombre, $apellido, $correo, $asunto, $mensaje, $empresa_id) = explode("__", $reg);

					if ($correo != '')
					{

						// Validar que no se haya enviado el correo a este destinatario
						$correo_bd = $em->getRepository('LinkComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml2['parameters']['tipo_correo']['notificacion_programada'],
																										'entidadId' => $np_id,
																										'usuario' => $usuario_id,
																										'correo' => $correo));

						if (!$correo_bd)
						{

							// Sustitución de variables en el texto
							$comodines = array('%%usuario%%', '%%clave%%', '%%nombre%%', '%%apellido%%');
							$reemplazos = array($login, $clave, $nombre, $apellido);
							$mensaje = str_replace($comodines, $reemplazos, $mensaje);

							$parametros_correo = array('twig' => 'LinkBackendBundle:Notificacion:emailCommand.html.twig',
													   'datos' => array('nombre' => $nombre,
																		'apellido' => $apellido,
																		'mensaje' => $mensaje,
																		'background' => $background,
																		'logo' => $logo,
																		'footer' => $footer,
																		'link_plataforma' => $base.$empresa_id),
													   'asunto' => $asunto,
													   'remitente' => $yml['parameters']['mailer_user_tutor'],
													   'remitente_name' => $yml['parameters']['mailer_user_tutor_name'],
													   'destinatario' => $correo,
													   'mailer' => 'tutor_mailer');
							$ok = $f->sendEmail($parametros_correo);
							//$ok = 1;
							if ($ok)
							{


								$j++;
								$cantidadHoy++;

								// Si es una notificación para un grupo de participantes, se marca como enviado
								$notificacion_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
								$tipo_destino_id = $notificacion_programada->getTipoDestino()->getId();
								 if ($notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['grupo'] || $notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['programa'] || $notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['aprobados']|| $notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['en_curso'])
								{
									$notificacion_programada->setEnviado(true);
									$em->persist($notificacion_programada);
									$em->flush();
									array_push($notificaciones_id,$np_id);
								}
									//else{
								// 	if(count($notificaciones_id) == 0){
								// 		array_push($notificaciones_id, $np_id);
								// 	}
								// }

								$output->writeln($j.' .----------------------------------------------------------------------------------------------');
								$output->writeln('np_id: '.$np_id);
								$output->writeln('usuario_id: '.$usuario_id);
								$output->writeln('Usuario: '.$nombre.' '.$apellido);
								$output->writeln('Correo enviado a '.$correo);

								// Registro del correo recien enviado
								$tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml2['parameters']['tipo_correo']['notificacion_programada']);
								$usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
								$email = new AdminCorreo();
								$email->setTipoCorreo($tipo_correo);
								$email->setEntidadId($np_id);
								$email->setUsuario($usuario);
								$email->setCorreo($correo);
								$email->setFecha(new \DateTime('now'));
								$em->persist($email);
								$em->flush();

								// ERROR LOG
								//error_log($j.' .----------------------------------------------------------------------------------------------');
								//error_log($reg);
								//error_log('Correo enviado a '.$correo);

							}
							else {
								//error_log(' .----------------------------------------------------------------------------------------------');
								//error_log($reg);
								//error_log('NO SE ENVIO '.$correo);
							}

						}

					}

				}


				$totalCorreosHoy = $em->getRepository('LinkComunBundle:AdminCorreosDiarios')->findOneByfecha(new \DateTime("NOW"));
				$totalCorreosHoy->setCantidad($cantidadHoy);
				$em->persist($totalCorreosHoy);
                $em->flush();

                // Actualizar status de las notificaciones
		        $output->writeln('Corriendo Query : ');
		        $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNotificacionProgramada n
		        						   WHERE n.grupo IS NULL
		        						   AND n.enviado = :enviado
										   AND (n.fechaDifusion = :fechaHoy OR n.fechaDifusion = :fechaAyer) ' )
										   ->setParameters(array('fechaHoy' => new \DateTime("NOW") ,
															      'fechaAyer' => $ayer,
															       'enviado' => FALSE));
		        $np = $query->getResult();

		        $output->writeln('Catidad Abierta : '.count($np));

		        foreach ($np as $n) {
                    $destino = $n->getTipoDestino()->getId();
		            if($destino != $yml2['parameters']['tipo_destino']['aprobados'] AND $destino != $yml2['parameters']['tipo_destino']['en_curso'] AND $destino != $yml2['parameters']['tipo_destino']['programa']){

						        	$entidad[$yml2['parameters']['tipo_destino']['todos']] = 0;
						            $entidad[$yml2['parameters']['tipo_destino']['nivel']] = $n->getEntidadId();
						            $entidad[$yml2['parameters']['tipo_destino']['grupo']] = $n->getId();
						            $entidad[$yml2['parameters']['tipo_destino']['no_ingresado']] = 0;
						            $entidad[$yml2['parameters']['tipo_destino']['no_ingresado_programa']] = $n->getEntidadId();

						            $query = $em->getConnection()->prepare('SELECT
							    											   fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
								                                               resultado;');
									$query->bindValue(':ptipo_destino_id', $n->getTipoDestino()->getId(), \PDO::PARAM_INT);
									$query->bindValue(':pempresa_id', $n->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
									$query->bindValue(':pentidad_id', $entidad[$n->getTipoDestino()->getId()], \PDO::PARAM_INT);
									$query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
									$query->execute();
									$r = $query->fetchAll();
									$programados = $r[0]['resultado'];
									$output->writeln('Cantidad programados: '.$programados);

									$query = $em->createQuery('SELECT count(c.id) FROM LinkComunBundle:AdminCorreo c
															   WHERE (c.entidadId = :np_id
															   OR c.entidadId IN (SELECT np.id FROM LinkComunBundle:AdminNotificacionProgramada np WHERE np.grupo = :np_id))')
													           ->setParameters(array('np_id' => $n->getId()));
								    $correos = $query->getSingleScalarResult();

								    $output->writeln('correo Enviados: '.$correos);
								    if($correos == $programados ){
								    	$n->setEnviado(true);
									    $em->persist($n);
										$em->flush();
										$output->writeln('La notificacion: '. $n->getId().' , se proceso completa');
								    }
					}else{
						$na = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($n->getId());
						$total = 0;
						foreach ($na as $nh) {


						            $query = $em->getConnection()->prepare('SELECT
							    											   fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
								                                               resultado;');
									$query->bindValue(':ptipo_destino_id', $nh->getTipoDestino()->getId(), \PDO::PARAM_INT);
									$query->bindValue(':pempresa_id', $nh->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
									$query->bindValue(':pentidad_id', $nh->getEntidadId(), \PDO::PARAM_INT);
									$query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
									$query->execute();
									$r = $query->fetchAll();
									$programados = $r[0]['resultado'];
									$total = $total + $programados;

									$output->writeln('Cantidad programados: '.$programados);

									$query = $em->createQuery('SELECT count(c.id) FROM LinkComunBundle:AdminCorreo c
															   WHERE (c.entidadId = :np_id
															   OR c.entidadId IN (SELECT np.id FROM LinkComunBundle:AdminNotificacionProgramada np WHERE np.grupo = :np_id))')
													           ->setParameters(array('np_id' => $nh->getId()));
								    $correos = $query->getSingleScalarResult();

								    $output->writeln('correo Enviados: '.$correos);
								    if($correos == $programados || $correos > $programados ){
								    	$n->setEnviado(true);
									    $em->persist($n);
										$em->flush();
										$output->writeln('La notificacion: '. $n->getId().' , se proceso completa');
								    }


						}
						$na = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($n->getId());
						if (count($na) == $total) {
							$n->setEnviado(true);
						    $em->persist($n);
							$em->flush();
							$output->writeln('La notificacion: '. $n->getId().' , se proceso completa');
						}
					}

			    }//foreach


		}//else{
			// //Si ya se cumplieron los envios diarios
		// }

    }
}