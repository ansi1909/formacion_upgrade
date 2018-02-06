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
        $controller = 'LinkRecordatoriosCommand';
        $parametros = array();
        $template = "LinkBackendBundle:Programados:email.html.twig";

        // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado a un programa"
       $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                       JOIN LinkComunBundle:AdminTipoDestino t
                                       WHERE t.nombre LIKE '%Usuarios que no han ingresado a un programa%'
                                       AND p.tipoDestino = t.id
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
              $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($notificacion->getEmpresa()->getId()); 
              if($empresa->getActivo() == 'true'){
                $output->writeln('La empresa_id: '.$empresa->getId().' esta activa');
                // busco los niveles diponibles para el programa de la notificación
                $nd = $em->createQuery("SELECT cn FROM LinkComunBundle:CertiNivelPagina cn
                                        JOIN LinkComunBundle:CertiPaginaEmpresa cp
                                        WHERE cp.empresa = :empresa_id
                                        AND cp.pagina = :programa_id
                                        AND cn.paginaEmpresa = cp.id
                                        ORDER BY cn.id ASC")
                          ->setParameters(array('empresa_id' => $notificacion->getEmpresa()->getId(),
                                                'programa_id' => $prog->getEntidadId()));
                $niveles = $nd->getResult();

                // verifico si existen niveles disponibles
                if(count($niveles) > 0){

                  // recorro para buscar usuarios por nivel, empresa y que no han ingresado al programa
                  foreach ($niveles as $nivel) {

                    $output->writeln('Niveles disponibles al programa: '.$nivel->getId());
                    $active_users = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                                      WHERE u.activo = 'true'
                                                      AND u.empresa = :empresa_id
                                                      AND u.nivel = :id_nivel
                                                      AND u.activo = 'true'
                                                      AND NOT EXISTS (SELECT l FROM LinkComunBundle:CertiPaginaLog l 
                                                                      WHERE l.pagina = :programa_id AND l.usuario = u.id)
                                                      ORDER BY u.id ASC")
                                        ->setParameters(array('empresa_id' => $notificacion->getEmpresa()->getId(),
                                                              'id_nivel' => $nivel->getId(),
                                                              'programa_id' => $prog->getEntidadId()));
                    $usuarios = $active_users->getResult();

                    foreach ($usuarios as $usuario){
                      
                      $output->writeln('Enviando la programacion_id: '.$prog->getId().', notificacion_id:'.$prog->getNotificacion()->getId().' al usuario_id: '.$usuario->getId().' de la empresa_id:'.$usuario->getEmpresa()->getId());
                        $parametros= array('twig'=>$template,
                                           'asunto'=>$notificacion->getAsunto(),
                                           'remitente'=>array('info@formacion2-0.com' => 'Formación 2.0'),
                                           'destinatario'=>$usuario->getCorreoCorporativo(),
                                           'datos'=>array('mensaje' => $notificacion->getMensaje(), 'usuario' => $usuario ));

                        $f->sendEmail($parametros, $controller);

                    }
                  }
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