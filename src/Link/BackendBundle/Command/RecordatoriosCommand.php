<?php

// formacion2.0/src/Link/BackendBundle/Command/RecordatoriosCommand.php

namespace Link\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class RecordatoriosCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('link:recordatorios')
            ->setDescription('Envía por correo notificaciones programadas y recordatorios a los usuarios que no han ingresado a la plataforma o programa específico')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        //$doctrine = $this->getContainer()->get('doctrine');
        $em = $this->getContainer()->get('doctrine')->getEntityManager('default');
        $f = $this->getContainer()->get('funciones');
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

               // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado", es decir id = 5
               $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                               WHERE p.entidadId IS NULL AND p.tipoDestino = 5
                                               ORDER BY p.id ASC");

               $programadas = $programada->getResult();

               foreach ($programadas as $prog){

                    // busco en admin_notificacion la notificacion
                    $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($prog->getNotificacion()->getId());

                    // si la notificación pertenece a la empresa a la que esta asignada el usuario envio el mail
                    if($usuario->getEmpresa()->getId() == $notificacion->getEmpresa()->getId()){

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

            // primero busco los programas a los que el usuario tiene acceso
            $programas_disponibles = $em->getRepository('LinkComunBundle:CertiNivelPagina')->findbyNivel($usuario->getNivel()->getId());

            foreach ($programas_disponibles as $prog_dis) {

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

                   // busco en notificacion_programada todas las notificaciones que el tipo destino sea "Usuarios que no han ingresado a un programa", es decir id = 6 y que la entidad_id sea el programa
                   $programada = $em->createQuery("SELECT p FROM LinkComunBundle:AdminNotificacionProgramada p
                                                   WHERE p.entidadId = :entidad_id AND p.tipoDestino = 6
                                                   ORDER BY p.id ASC")
                                    ->setParameters(array('entidad_id' => $prog_dis->getId()));

                   $programadas = $programada->getResult();

                   foreach ($programadas as $prog){

                        // busco en admin_notificacion la notificacion
                        $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($prog->getNotificacion()->getId());

                        // si la notificación pertenece a la empresa a la que esta asignada el usuario envio el mail
                        if($usuario->getEmpresa()->getId() == $notificacion->getEmpresa()->getId()){

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
                                                WHERE p.fecha_difusion = :fecha_difusion
                                                AND p.tipoDestino NOT IN (5,6,7)
                                                ORDER BY p.id ASC")
                                 ->setParameters(array('fecha_difusion' => $fecha));
        $lista_hoy = $notificaciones_hoy->getResult();

        foreach ($lista_hoy as $lh){

            $programacion = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($lh->getId());

            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($programacion->getNotificacion()->getId());
            $parametros = array();
            $template = "LinkBackendBundle:Programados:email.html.twig";

            if($programacion->getTipoDestino()->getNombre() == "Nivel")
            {

                $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminUsuario p
                                           WHERE p.nivel = :nivel_id AND p.empresa = :empresa_id
                                           ORDER BY p.id ASC")
                            ->setParameters(array('nivel_id' => $programacion->getEntidadId(),
                                                  'empresa_id' => $notificacion->getEmpresa()->getId()));
                $usuarios = $query->getResult();

            }
            elseif($programacion->getTipoDestino()->getNombre() == "Programa")
            {

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

                $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findByEmpresa($notificacion->getEmpresa()->getId());

            }
            elseif($programacion->getTipoDestino()->getNombre() == "Grupo de usuarios")
            {

                $programacion_grupo = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($programacion->getId());
                foreach ($programacion_grupo as $individual){

                        $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->find($individual->getEntidadId());
                }

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