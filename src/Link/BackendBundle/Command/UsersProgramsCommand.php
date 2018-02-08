<?php

// formacion2.0/src/Link/BackendBundle/Command/UsersProgramsCommand.php

namespace Link\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\Parameter;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

class UsersProgramsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('link:recordatorio-programas')
        ->setDescription('Envía por correo notificaciones programadas y recordatorios a los usuarios que no han ingresado a alguno se sus programas disponibles')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

      $em = $em = $this->getContainer()->get('doctrine')->getManager();
      $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
      $template = "LinkBackendBundle:Programados:email.html.twig";

      // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado a un programa", que no sea una programacion hija y que no este enviada
      $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                      WHERE p.tipoDestino = 6
                                      AND p.grupo IS NULL
                                      AND p.enviado IS NULL
                                      ORDER BY p.id ASC");

      $programadas = $programada->getResult();

      // en caso de que existan notificaciones de este tipo
      if(count($programadas) > 0){

        foreach ($programadas as $prog){
            $output->writeln('Programacion tipo "Usuarios que no han ingresado a un programa" - programacion_id:'.$prog->getId());
            // busco en admin_notificacion la notificacion
            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($prog->getNotificacion()->getId());
            $output->writeln('notificacion_id:'.$prog->getNotificacion()->getId());

            // busco la empresa dueña de la notificacion y valido que este activa
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.id = :empresa
                                       AND e.activo = 'true'
                                       ORDER BY e.id ASC")
                        ->setParameters(array('empresa' => $notificacion->getEmpresa()->getId()));
            $empresa = $query->getResult(); 
            $output->writeln('La empresa_id: '.$empresa[0]->getId().' esta activa');

            // busco usuarios activos que tengan el nivel disponible para el programa en su empresa, que el programa tenga el contenido activo y que este activo para la empresa 
            $query = $em->createQuery("SELECT u FROM 
                                                    LinkComunBundle:AdminUsuario u,
                                                    LinkComunBundle:CertiNivelPagina n,
                                                    LinkComunBundle:CertiPaginaEmpresa pe,
                                                    LinkComunBundle:CertiPagina p
                                       WHERE p.id = :programa
                                       AND p.estatusContenido = 2
                                       AND pe.activo = 'true'
                                       AND pe.empresa = :empresa
                                       AND pe.pagina = p.id
                                       AND n.paginaEmpresa = pe.id
                                       AND n.nivel = u.nivel
                                       AND u.activo = 'true'
                                       ORDER BY u.id ASC")
                        ->setParameters(array('programa' => $prog->getEntidadId(),
                                              'empresa' => $empresa[0]->getId()));
            $usuarios = $query->getResult();

            // llamando a la funcion que recorre lo usuarios y envia el mail
            $f->emailUsuarios($usuarios, $notificacion, $template);

            // Cambio el estatus de la programacion a enviada, siempre que se cumplan las condiciones anteriores
            $prog->setEnviado(true);
            $em->persist($prog);
            $em->flush();
        }
      }
   }
}