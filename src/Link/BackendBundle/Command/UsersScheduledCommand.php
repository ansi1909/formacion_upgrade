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
      $em = $em = $this->getContainer()->get('doctrine')->getManager();
      $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
      // tomando fecha actual para buscar usuarios que no hallan ingresado en 5 días
      $fecha = '2018-03-31';
      $template = "LinkBackendBundle:Programados:email.html.twig";
      /*
      // busco en notificacion_programada todas las notificaciones que el tipo destino sea Todos, Nivel, Programa, Grupo de participantes, que no sea una programacion hija y que no este enviada
      $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                      WHERE p.tipoDestino IN (1,2,3,4)
                                      AND p.fechaDifusion = :fecha_difusion
                                      AND p.grupo IS NULL
                                      AND p.enviado IS NULL
                                      ORDER BY p.id ASC")
                       ->setParameters(array('fecha_difusion' => $fecha));

      $programadas = $programada->getResult();

     // en caso de que existan notificaciones de este tipo
    if(count($programadas) > 0){

        foreach ($programadas as $prog){
            $output->writeln('Programacion tipo "Usuarios que no han ingresado" - programacion_id:'.$prog->getId());
            // busco en admin_notificacion la notificacion
            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($prog->getNotificacion()->getId());
            $output->writeln('notificacion_id:'.$prog->getNotificacion()->getId());

            // busco la empresa dueña de la notificacion y valido que este activa
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.id = :empresa
                                       AND e.activo = :activo
                                       ORDER BY e.id ASC")
                        ->setParameters(array('empresa_id' => $notificacion->getEmpresa()->getId(),
                                              'activo' => true));
            $empresa = $query->getResult();

            $output->writeln('La empresa_id: '.$empresa[0]->getId().' esta activa');

            if($prog->getTipoDestino()->getNombre() == "Nivel")
            {
                $output->writeln('Programacion tipo NIVEL');
                $output->writeln('Buscando usuarios del nivel_id: '.$prog->getEntidadId().' y la empresa:_id '.$empresa[0]->getId());
                // busco usuarios activos que tengan el nivel y empresa de la notificación programada
                $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                           WHERE u.nivel = :nivel_id 
                                           AND u.empresa = :empresa_id
                                           AND u.activo = :activo
                                           ORDER BY u.id ASC")
                            ->setParameters(array('nivel_id' => $prog->getEntidadId(),
                                                  'activo' => true,
                                                  'empresa_id' => $empresa[0]->getId()));
                $usuarios = $query->getResult();

            }
            elseif($prog->getTipoDestino()->getNombre() == "Programa")
            {
                $output->writeln('Programacion tipo Programa');
                $output->writeln('Buscando usuarios del asignado al programa_id: '.$prog->getEntidadId());
                // busco usuarios activos que tengan el nivel disponible para el programa en su empresa, que el programa tenga el contenido activo y que este activo para la empresa 
                $query = $em->createQuery("SELECT u FROM 
                                                        LinkComunBundle:AdminUsuario u,
                                                        LinkComunBundle:CertiNivelPagina n,
                                                        LinkComunBundle:CertiPaginaEmpresa pe,
                                                        LinkComunBundle:CertiPagina p
                                           WHERE p.id = :programa
                                           AND p.estatusContenido = 2
                                           AND pe.activo = :activo
                                           AND pe.empresa = :empresa
                                           AND pe.pagina = p.id
                                           AND n.paginaEmpresa = pe.id
                                           AND n.nivel = u.nivel
                                           AND u.activo = :activo
                                           ORDER BY u.id ASC")
                            ->setParameters(array('programa' => $prog->getEntidadId(),
                                                  'activo' => true,
                                                  'empresa' => $empresa[0]->getId()));
                $usuarios = $query->getResult();

            }
            elseif($prog->getTipoDestino()->getNombre() == "Todos")
            {
                $output->writeln('Programacion tipo TODOS');
                $output->writeln('Buscando usuarios del de la empresa_id: '.$empresa[0]->getId());
                $active_users = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                              WHERE u.activo = :activo
                                              AND u.empresa = :empresa_id
                                              AND u.activo = :activo
                                              ORDER BY u.id ASC")
                                   ->setParameters(array('empresa_id' => $empresa[0]->getId(),
                                                         'activo' => true));
                $usuarios = $active_users->getResult();

            }
            elseif($prog->getTipoDestino()->getNombre() == "Grupo de usuarios")
            {
                $output->writeln('Programacion tipo GRUPO DE USUARIOS');
                $output->writeln('Buscando usuarios asignado a la programacion_id: '.$prog->getId());

                // busco usuarios activos, que sean la entidad_id de la programacion y que la programacion sea del grupo de la programacion padre
                $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                           JOIN LinkComunBundle:AdminNotificacionProgramada p 
                                           WHERE p.grupo = :grupo
                                           AND p.entidad = u.id
                                           AND u.activo = :activo
                                           ORDER BY u.id ASC")
                            ->setParameters(array('grupo' => $prog->getId(),
                                                  'activo' => true));
                $usuarios = $query->getResult();

            }

            // llamando a la funcion que recorre lo usuarios y envia el mail
            $f->emailUsuarios($usuarios, $notificacion, $template);

            // Cambio el estatus de la programacion a enviada, siempre que se cumplan las condiciones anteriores
            $prog->setEnviado(true);
            $em->persist($prog);
            $em->flush();

            // busco si hay notificaciones hijas de la programación para cambiar a enviado
            $grupo_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($prog->getId());
            foreach ($grupo_programada as $individual) {
                $individual->setEnviado(true);
                $em->persist($individual);
                $em->flush();
            }
        }
    }*/
        // Llamada a la función de BD que duplica la página
        $consulta = array();
        $query = $em->getConnection()->prepare('SELECT fnrecordatorios_usuarios(:pfecha) as resultado;');
        $query->bindValue(':pfecha', date('Y-m-d'), \PDO::PARAM_STR);
        $query->execute();
        $consulta = $query->fetchAll();
        $output->writeln('CONSULTA: '.$consulta[0]['resultado']);
        for ($i = 0; $i < count($consulta); $i++) {
            $output->writeln('ARREGLO: '.$consulta[$i]['resultado']);
            $valor = explode('__', $consulta[$i]['resultado']);
            $nombre = $valor[3];
            $apellido = $valor[4];
            $correo = $valor[5];
            $asunto = $valor[0];
            $mensaje = $valor[1];
            $id = $valor[2];
            $output->writeln('NOMBRE: '.$nombre.' APELLIDO: '.$apellido.' EMAIL: '.$correo.' ASUNTO: '.$asunto.' MENSAJE: '.$mensaje);
        }
  }
}