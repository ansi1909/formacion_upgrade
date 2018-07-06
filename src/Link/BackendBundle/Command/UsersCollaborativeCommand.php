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

class UsersCollaborativeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:espacio-colaborativo')
             ->setDescription('Envía correos y genera alarmas a los participantes al momento de crearse un espacio colaborativo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');

        // Generación de alarmas
        /*$descripcion = $usuario->getNombre().' '.$usuario->getApellido().' '.$this->get('translator')->trans('ha creado un tema en el espacio colaborativo del').' '.$pagina->getCategoria()->getNombre().' '.$pagina->getNombre().'.';

        $query = $em->createQuery("SELECT np FROM LinkComunBundle:CertiNivelPagina np 
                                    JOIN np.paginaEmpresa pe 
                                    WHERE pe.empresa = :empresa_id 
                                    AND pe.pagina = :pagina_id 
                                    ORDER BY np.id ASC")
                    ->setParameters(array('empresa_id' => $session->get('empresa')['id'],
                                          'pagina_id' => $pagina_id));
        $nivel_paginas = $query->getResult();

        $usuarios_id = array();
        $usuarios_arr = array();

        foreach ($nivel_paginas as $np)
        {
            $usuarios = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findByNivel($np->getNivel()->getId());
            foreach ($usuarios as $usuario_nivel)
            {
                $query = $em->createQuery('SELECT COUNT(ru.id) FROM LinkComunBundle:AdminRolUsuario ru 
                                            WHERE ru.rol = :rol_id AND ru.usuario = :usuario_id')
                            ->setParameters(array('rol_id' => $yml['parameters']['rol']['participante'],
                                                  'usuario_id' => $usuario_nivel->getId()));
                $participante = $query->getSingleScalarResult();
                if (!in_array($usuario_nivel->getId(), $usuarios_id) && $participante && $usuario_nivel->getId() != $usuario->getId())
                {
                    $usuarios_id[] = $usuario_nivel->getId();
                    $usuarios_arr[] = $usuario_nivel;
                }
            }
        }

        foreach ($usuarios_arr as $usuario_nivel)
        {
            $f->newAlarm($yml['parameters']['tipo_alarma']['espacio_colaborativo'], $descripcion, $usuario_nivel, $foro->getId());
        }*/
        
        // tomando fecha actual para buscar usuarios que no hallan ingresado en 5 días
        $fecha = '2018-03-31';
        $destinatarios = array();
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
                                 'remitente'=>array('tutorvirtual@formacion2puntocero.com' => 'Formación2.0'),
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