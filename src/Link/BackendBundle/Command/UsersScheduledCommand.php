<?php

// formacion2.0/src/Link/BackendBundle/Command/RecordatoriosCommand.php

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
        $fecha = date('Y-m-j');
        $controller = 'LinkRecordatoriosCommand';
        $parametros = array();
        $template = "LinkBackendBundle:Programados:email.html.twig";

        // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado"
       $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                       JOIN LinkComunBundle:AdminTipoDestino t
                                       WHERE t.nombre LIKE '%Todos%' 
                                       OR t.nombre LIKE '%Nivel%' 
                                       OR t.nombre LIKE '%Programa%' 
                                       OR t.nombre LIKE '%Grupo de participantes%' 
                                       AND p.tipoDestino = t.id
                                       AND p.fechaDifusion = :fecha_difusion
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
              $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($notificacion->getEmpresa()->getId()); 
              if($empresa->getActivo() == 'true'){

                    $output->writeln('La empresa_id: '.$empresa->getId().' esta activa');

                    if($prog->getTipoDestino()->getNombre() == "Nivel")
                    {
                        $output->writeln('Programacion tipo NIVEL');
                        $output->writeln('Buscando usuarios del nivel_id: '.$prog->getEntidadId().' y la empresa:_id '.$notificacion->getEmpresa()->getId());
                        $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminUsuario p
                                                   WHERE p.nivel = :nivel_id AND p.empresa = :empresa_id
                                                   ORDER BY p.id ASC")
                                    ->setParameters(array('nivel_id' => $prog->getEntidadId(),
                                                          'empresa_id' => $notificacion->getEmpresa()->getId()));
                        $usuarios = $query->getResult();

                    }
                    elseif($prog->getTipoDestino()->getNombre() == "Programa")
                    {
                        $output->writeln('Programacion tipo Programa');
                        $output->writeln('Buscando usuarios del asignado al programa_id: '.$prog->getEntidadId());
                        $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                                   JOIN LinkComunBundle:CertiNivelPagina c 
                                                   WHERE c.paginaEmpresa = :programa
                                                   AND c.nivel = u.nivel
                                                   ORDER BY u.id ASC")
                                    ->setParameters(array('programa' => $prog->getEntidadId()));

                        $usuarios = $query->getResult();

                    }
                    elseif($prog->getTipoDestino()->getNombre() == "Todos")
                    {
                        $output->writeln('Programacion tipo TODOS');
                        $output->writeln('Buscando usuarios del de la empresa_id: '.$notificacion->getEmpresa()->getId());
                        $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());

                    }
                    elseif($prog->getTipoDestino()->getNombre() == "Grupo de usuarios")
                    {
                        $output->writeln('Programacion tipo GRUPO DE USUARIOS');
                        $output->writeln('Buscando usuarios asignado a la programacion_id: '.$prog->getId());

                        $programacion_grupo = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($prog->getId());
                        $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                                        JOIN LinkComunBundle:AdminNotificacionProgramada p 
                                                        WHERE p.grupo = :grupo
                                                        AND p.entidad = u.id
                                                        ORDER BY u.id ASC")
                                        ->setParameters(array('grupo' => $prog->getId()));

                        $usuarios = $query->getResult();

                    }

                    foreach ($usuarios as $usuario) {

                      $parametros= array('twig'=>$template,
                                         'asunto'=>$notificacion->getAsunto(),
                                         'remitente'=>array('info@formacion2-0.com' => 'Formación 2.0'),
                                         'destinatario'=>$usuario->getCorreoCorporativo(),
                                         'datos'=>array('mensaje' => $notificacion->getMensaje(), 'usuario' => $usuario ));

                      $f->sendEmail($parametros, $controller);
                  }

                  // Cambio el estatus de la programacion a enviada, siempre que se cumplan las condiciones anteriores
                  $prog->setEnviado(true);
                  $em->persist($prog);
                  $em->flush();

              }
        }
      }

    }
}