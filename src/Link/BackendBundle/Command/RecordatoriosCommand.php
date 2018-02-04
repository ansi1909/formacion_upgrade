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

class RecordatoriosCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('link:recordatorios')
            ->setDescription('Envía por correo notificaciones programadas y recordatorios a los usuarios que no han ingresado a la plataforma o programa específico')
        ;
    }

    /*protected function execute(InputInterface $input, OutputInterface $output)
    {
      $em = $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        // tomando fecha actual para buscar usuarios que no hallan ingresado en 5 días
        $fecha = date('Y-m-j');
        $nuevafecha = strtotime ( '-5 day' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-j' , $nuevafecha );
        $controller = 'LinkRecordatoriosCommand';
        $parametros = array();
        $template = "LinkBackendBundle:Programados:email.html.twig";

        // busco todos los usuarios registrados en la plataforma
        $active_users = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                     WHERE u.activo = 'true'
                                     ORDER BY u.id ASC");
        $usuarios = $active_users->getResult();

        foreach ($usuarios as $usuario){
          $output->writeln($usuario->getId());
        }

    }*/

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        //$doctrine = $this->getContainer()->get('doctrine');
        $em = $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        // tomando fecha actual para buscar usuarios que no hallan ingresado en 5 días
        $fecha = date('Y-m-j');
        $nuevafecha = strtotime ( '-5 day' , strtotime ( $fecha ) ) ;
        $nuevafecha = date ( 'Y-m-j' , $nuevafecha );
        $controller = 'LinkRecordatoriosCommand';
        $parametros = array();
        $template = "LinkBackendBundle:Programados:email.html.twig";

        // busco todos los usuarios registrados en la plataforma
        $active_users = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                     WHERE u.activo = 'true' AND u.empresa IS NOT NULL
                                     ORDER BY u.id ASC");
        $usuarios = $active_users->getResult();

        foreach ($usuarios as $usuario){
            $output->writeln('usuario_id: '.$usuario->getId());
            // verifico si el usuario tiene registros en admin_sesion entre la fecha actual y 5 días antes
            $ssesion = $em->createQuery("SELECT p FROM LinkComunBundle:AdminSesion p
                                         WHERE p.usuario = :usuario_id AND p.fechaIngreso BETWEEN :fecha and :nuevafecha
                                         ORDER BY p.id ASC")
                          ->setParameters(array('usuario_id' => $usuario->getId(),
                                                'fecha' => $fecha,
                                                'nuevafecha' => $nuevafecha));
            $sinsession = $ssesion->getResult();

           // en caso de no tener registros
           if(count($sinsession) < 1){
                $output->writeln('El usuario_id: '.$usuario->getId().' No ha ingreso a la plataforma en 5 dias');
               // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado", es decir id = 5
               $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                               WHERE p.entidadId IS NULL AND p.tipoDestino = 5
                                               ORDER BY p.id ASC");

               $programadas = $programada->getResult();

               foreach ($programadas as $prog){
                    $output->writeln('Programacion tipo "Usuarios que no han ingresado" - programacion_id:'.$prog->getId());

                    // busco en admin_notificacion la notificacion
                    $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($prog->getNotificacion()->getId());
                    $output->writeln('notificacion_id:'.$prog->getNotificacion()->getId());
                    $output->writeln('empresa_id:'.$usuario->getEmpresa()->getId());
                    // si la notificación pertenece a la empresa a la que esta asignada el usuario envio el mail
                    if($usuario->getEmpresa()->getId() == $notificacion->getEmpresa()->getId()){
                        $output->writeln('El usuario pertenece a la empresa de la notificacion');
                        $output->writeln('Enviando la prormacion_id: '.$prog->getId().', notificacion_id:'.$prog->getNotificacion()->getId().' al usuario_id: '.$usuario->getId().' de la empresa_id:'.$usuario->getEmpresa()->getId());
                        $parametros= array('twig'=>$template,
                                           'asunto'=>$notificacion->getAsunto(),
                                           'remitente'=>array('info@formacion2-0.com' => 'Formación 2.0'),
                                           'destinatario'=>$usuario->getCorreoCorporativo(),
                                           'datos'=>array('mensaje' => $notificacion->getMensaje(), 'usuario' => $usuario ));

                        $f->sendEmail($parametros, $controller);

                    }
               }
            }

        }
        // lo mismo de arriba pero esta vez para usuarios que no han ingresado a un programa
        foreach ($usuarios as $usuario){
            $output->writeln('usuario_id: '.$usuario->getId());
            // primero busco los programas a los que el usuario tiene acceso
            $pd = $em->createQuery("SELECT cn FROM LinkComunBundle:CertiNivelPagina cn
                                     WHERE cn.nivel = :nivel_id
                                     ORDER BY cn.id ASC")
                      ->setParameters(array('nivel_id' => $usuario->getNivel()->getId()));
            $programas_disponibles = $pd->getResult();

            foreach ($programas_disponibles as $prog_dis) {
                $output->writeln('programa_disponible_id: '.$prog_dis->getId());
                // verifico si el usuario tiene registros en certi_pagina_log para un programa en estatus de iniciada o evalución y entre la fecha actual y 5 días antes
                $ssesion_pagina = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPaginaLog p
                                                    WHERE p.usuario = :usuario_id AND p.pagina = :pagina_id AND p.estatusPagina IN (1,2) AND p.fechaInicio BETWEEN :fecha and :nuevafecha
                                                    ORDER BY p.id ASC")
                                     ->setParameters(array('usuario_id' => $usuario->getId(),
                                                           'pagina_id' => $prog_dis->getId(),
                                                           'fecha' => $fecha,
                                                           'nuevafecha' => $nuevafecha));
                $sinsession_pagina = $ssesion_pagina->getResult();

               // en caso de no tener registros
               if(count($sinsession_pagina) < 1){
                  $output->writeln('El usuario_id: '.$usuario->getId().' No ha ingresado a sus programas disponibles en 5 dias');

                   // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado a un programa", es decir id = 6 y que la entidad_id sea el programa
                   $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                                   WHERE p.entidadId = :entidad_id AND p.tipoDestino = 6
                                                   ORDER BY p.id ASC")
                                    ->setParameters(array('entidad_id' => $prog_dis->getId()));

                   $programadas = $programada->getResult();

                   foreach ($programadas as $prog){
                        $output->writeln('Programacion tipo "Usuarios que no han ingresado a un programa" - programacion_id:'.$prog->getId());
                        // busco en admin_notificacion la notificacion
                        $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($prog->getNotificacion()->getId());
                        $output->writeln('notificacion_id:'.$prog->getNotificacion()->getId());
                        $output->writeln('empresa_id:'.$usuario->getEmpresa()->getId());

                        // si la notificación pertenece a la empresa a la que esta asignada el usuario envio el mail
                        if($usuario->getEmpresa()->getId() == $notificacion->getEmpresa()->getId()){
                          $output->writeln('El usuario pertenece a la empresa de la notificacion');
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
            }

        }

        // finalmente busaco todas las notificaciones progaramadas para el día de hoy
        $notificaciones_hoy = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                                WHERE p.fechaDifusion = :fecha_difusion
                                                AND p.tipoDestino NOT IN (5,6,7)
                                                ORDER BY p.id ASC")
                                 ->setParameters(array('fecha_difusion' => $fecha));
        $lista_hoy = $notificaciones_hoy->getResult();

        foreach ($lista_hoy as $lh){

            $output->writeln('Notificacion tipo Nivel, Programa, Todos o Grupo de Usuarios programada para hoy id: '.$lh->getId());

            $programacion = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($lh->getId());

            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($programacion->getNotificacion()->getId());
            $parametros = array();
            $template = "LinkBackendBundle:Programados:email.html.twig";

            if($programacion->getTipoDestino()->getNombre() == "Nivel")
            {
                $output->writeln('Programacion tipo NIVEL');
                $output->writeln('Buscando usuarios del nivel_id: '.$programacion->getEntidadId().' y la empresa:_id '.$notificacion->getEmpresa()->getId());
                $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminUsuario p
                                           WHERE p.nivel = :nivel_id AND p.empresa = :empresa_id
                                           ORDER BY p.id ASC")
                            ->setParameters(array('nivel_id' => $programacion->getEntidadId(),
                                                  'empresa_id' => $notificacion->getEmpresa()->getId()));
                $usuarios = $query->getResult();

            }
            elseif($programacion->getTipoDestino()->getNombre() == "Programa")
            {
                $output->writeln('Programacion tipo Programa');
                $output->writeln('Buscando usuarios del asignado al programa_id: '.$programacion->getEntidadId());
                $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                           JOIN LinkComunBundle:CertiNivelPagina c 
                                           WHERE c.paginaEmpresa = :programa
                                           AND c.nivel = u.nivel
                                           ORDER BY u.id ASC")
                            ->setParameters(array('programa' => $programacion->getEntidadId()));

                $usuarios = $query->getResult();

            }
            elseif($programacion->getTipoDestino()->getNombre() == "Todos")
            {
                $output->writeln('Programacion tipo TODOS');
                $output->writeln('Buscando usuarios del de la empresa_id: '.$notificacion->getEmpresa()->getId());
                $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());

            }
            elseif($programacion->getTipoDestino()->getNombre() == "Grupo de usuarios")
            {
                $output->writeln('Programacion tipo GRUPO DE USUARIOS');
                $output->writeln('Buscando usuarios asignado a la programacion_id: '.$programacion->getId());

                $programacion_grupo = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($programacion->getId());
                $query = $em->createQuery("SELECT u FROM LinkComunBundle:AdminUsuario u
                                                JOIN LinkComunBundle:AdminNotificacionProgramada p 
                                                WHERE p.grupo = :grupo
                                                AND p.entidad = u.id
                                                ORDER BY u.id ASC")
                                ->setParameters(array('grupo' => $programacion->getId()));

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

       }

    }
}