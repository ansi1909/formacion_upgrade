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
        
        $hoy = date('Y-m-d');
        $query = $em->getConnection()->prepare('SELECT fnrecordatorios_usuarios(:pfecha) AS resultado;');
        $query->bindValue(':pfecha', $hoy, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();
        $notificaciones_id = array();

        //error_log('-------------------CRON JOB DEL DIA '.date('d/m/Y H:i').'---------------------------------------------------');
        $output->writeln('CANTIDAD: '.count($r));

        $background = $yml['parameters']['folders']['uploads'].'recursos/decorate_certificado.png';
        $logo = $yml['parameters']['folders']['uploads'].'recursos/logo_formacion.png';
        $footer = $yml['parameters']['folders']['uploads'].'recursos/footer.bg.form.png';
        //$logo = ''; // Solo por requerimiento para BANFONDESA
        $j = 0; // Contador de correos exitosos
        $np_id = 0; // notificacion_programada_id

        for ($i = 0; $i < count($r); $i++) 
        {

            if ($j == 100)
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

                        // Si es una notificación para un grupo de participantes, se marca como enviado
                        $notificacion_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                        $tipo_destino_id = $notificacion_programada->getTipoDestino()->getId();
                         if ($notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['grupo'] || $notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['programa'] || $notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['aprobados']|| $notificacion_programada->getTipoDestino()->getId() == $yml2['parameters']['tipo_destino']['en_curso'])
                        {
                            $notificacion_programada->setEnviado(true);
                            $em->persist($notificacion_programada);
                            $em->flush();
                            array_push($notificaciones_id,$np_id);
                        }else{
                            if(count($notificaciones_id) == 0){
                                array_push($notificaciones_id, $np_id);
                            }
                        }

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

        // Si se enviaron todos los correos, se coloca la notificación como enviada
        if ($np_id)
        {
            if($tipo_destino_id == $yml2['parameters']['tipo_destino']['todos'] || $tipo_destino_id == $yml2['parameters']['tipo_destino']['nivel'] ||
                       $tipo_destino_id == $yml2['parameters']['tipo_destino']['no_ingresado'] || $tipo_destino_id == $yml2['parameters']['tipo_destino']['no_ingresado_programa']){
                           $np_main = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
            }else{
                     $np_hija = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                     $np_main = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_hija->getGrupo());
                           
            }
             $query = $em->createQuery('SELECT COUNT(c.id) FROM LinkComunBundle:AdminCorreo c 
                                                WHERE c.tipoCorreo = :notificacion_programada 
                                                AND c.entidadId IN (:np_id)' )
                                ->setParameters(array('notificacion_programada' => $yml2['parameters']['tipo_correo']['notificacion_programada'],
                                                      'np_id' => $notificaciones_id));
             $emails = $query->getSingleScalarResult();
             if($emails == count($r)){
                $np_main->setEnviado(true);
                $em->persist($np_main);
                $em->flush();
             }
                         
            // $notificacion_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);

            // $query = $em->createQuery('SELECT COUNT(c.id) FROM LinkComunBundle:AdminCorreo c 
            //                             WHERE c.tipoCorreo = :notificacion_programada 
            //                             AND c.entidadId = :np_id')
            //             ->setParameters(array('notificacion_programada' => $yml2['parameters']['tipo_correo']['notificacion_programada'],
            //                                   'np_id' => $np_id));
            // $emails = $query->getSingleScalarResult();

            // if ($notificacion_programada->getTipoDestino()->getId() != $yml2['parameters']['tipo_destino']['grupo'] && $emails >= count($r))
            // {
            //     $notificacion_programada->setEnviado(true);
            //     $em->persist($notificacion_programada);
            //     $em->flush();
            // }
            
        }

    }
}