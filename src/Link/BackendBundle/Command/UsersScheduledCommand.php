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
        
        $query = $em->getConnection()->prepare('SELECT fnrecordatorios_usuarios(:pfecha) AS resultado;');
        $query->bindValue(':pfecha', date('Y-m-d'), \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        error_log('-------------------CRON JOB DEL DIA '.date('d/m/Y H:i').'---------------------------------------------------');
        $output->writeln('CANTIDAD: '.count($r));

        $background = $yml['parameters']['folders']['uploads'].'recursos/decorate_certificado.png';
        //$logo = $yml['parameters']['folders']['uploads'].'recursos/logo_formacion.png';
        $logo = ''; // Solo por requerimiento para BANFONDESA
        $j = 0; // Contador de correos exitosos

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
                                                            'link_plataforma' => $base.$empresa_id),
                                           'asunto' => $asunto,
                                           'remitente' => $yml['parameters']['mailer_user'],
                                           'destinatario' => $correo);
                $ok = $f->sendEmail($parametros_correo);
                if ($ok)
                {
                    $j++;
                    $notificacion_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                    $notificacion_programada->setEnviado(true);
                    $em->persist($notificacion_programada);
                    $em->flush();
                    $output->writeln($j.' .----------------------------------------------------------------------------------------------');
                    $output->writeln($reg);
                	$output->writeln('Correo enviado a '.$correo);
                    
                    // ERROR LOG
                    error_log($j.' .----------------------------------------------------------------------------------------------');
                    error_log($reg);
                    error_log('Correo enviado a '.$correo);

                }
                else {
                    error_log(' .----------------------------------------------------------------------------------------------');
                    error_log($reg);
                    error_log('NO SE ENVIO '.$correo);
                }

            }
            
        }

    }
}