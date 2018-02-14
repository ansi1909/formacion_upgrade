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

class UsersScheduledCommand extends ContainerAwareCommand
{
      protected function configure()
      {
          $this
          ->setName('link:recordatorio-programados')
          ->setDescription('Envía por correo notificaciones programadas y recordatorios de la fecha actual')
          ;
      }

      protected function execute(InputInterface $input, OutputInterface $output)
      {

          //$doctrine = $this->getContainer()->get('doctrine');
          $em = $this->getContainer()->get('doctrine')->getManager();
          $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
          // tomando fecha actual para buscar usuarios que no hallan ingresado en 5 días
          $fecha = '2018-03-31';
          $template = "LinkBackendBundle:Programados:emailCommand.html.twig";
          $consulta = array();
          $query = $em->getConnection()->prepare('SELECT fnrecordatorios_usuarios(:pfecha) AS resultado;');
          $query->bindValue(':pfecha', date('Y-m-d'), \PDO::PARAM_STR);
          $query->execute();
          $consulta = $query->fetchAll();
          $output->writeln(var_dump($consulta));
          $output->writeln('CANTIDAD: '.count($consulta));
          for ($i = 0; $i < count($consulta); $i++) {
              $valor = explode('__', $consulta[$i]['resultado']);
              $_nombre = explode('"', $valor[0]);
              $nombre = $_nombre[1];
              $apellido = $valor[1];
              $correo = $valor[2];
              $asunto = $valor[3];
              $mensaje = $valor[4];
              $_id = explode('"', $valor[5]);
              $id = $_id[0];
              $output->writeln('NOMBRE: '.$nombre.' APELLIDO: '.$apellido.' EMAIL: '.$correo.' ASUNTO: '.$asunto.' MENSAJE: '.$mensaje.' ID NOTIFICACION: '.$id[0]);
              $parametros = array();
              $parametros= array('twig'=>$template,
                                 'asunto'=>$asunto,
                                 'remitente'=>array('info@formacion2-0.com' => 'Formación 2.0'),
                                 'destinatario'=>$correo,
                                 'datos'=>array('mensaje' => $mensaje, 'nombre'=> $nombre, 'apellido' => $apellido ));

              $f->sendEmailCommand($parametros);

              $programacion = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($id);
              $programacion->setEnviado(true);
                    
              $em->persist($programacion);
              $em->flush();

                // busco si hay notificaciones hijas de la programación para cambiar a enviado
              $grupo_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($programacion->getId());
              foreach ($grupo_programada as $individual) {
                  $individual->setEnviado(true);
                  $em->persist($individual);
                  $em->flush();
              }
          }
      }
}