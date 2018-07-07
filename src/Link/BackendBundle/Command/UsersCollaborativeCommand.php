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
use Symfony\Component\Routing\RouterInterface;

class UsersCollaborativeCommand extends ContainerAwareCommand
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    protected function configure()
    {
        $this->setName('link:espacio-colaborativo')
             ->setDescription('Envía correos y genera alarmas a los participantes al momento de crearse un espacio colaborativo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $query = $em->createQuery("SELECT f FROM LinkComunBundle:CertiForo f 
                                    JOIN f.empresa e 
                                    JOIN f.pagina p 
                                    WHERE f.fechaPublicacion = :hoy 
                                    AND f.fechaVencimiento >= :hoy 
                                    AND f.foro IS NULL 
                                    AND e.activo = :activo 
                                    AND p.estatusContenido = :estatus 
                                    ORDER BY f.fechaRegistro ASC")
                    ->setParameters(array('hoy' => date('Y-m-d'),
                                          'activo' => true,
                                          'estatus' => $yml['parameters']['estatus_contenido']['activo']));
        $foros = $query->getResult();

        foreach ($foros as $foro)
        {

            $query = $em->createQuery("SELECT np FROM LinkComunBundle:CertiNivelPagina np 
                                        JOIN np.paginaEmpresa pe 
                                        WHERE pe.empresa = :empresa_id 
                                        AND pe.pagina = :pagina_id 
                                        ORDER BY np.id ASC")
                        ->setParameters(array('empresa_id' => $foro->getEmpresa()->getId(),
                                              'pagina_id' => $foro->getPagina()->getId()));
            $nivel_paginas = $query->getResult();

            $descripcion = $foro->getUsuario()->getNombre().' '.$foro->getUsuario()->getApellido().' ha creado un tema en el espacio colaborativo del '.$foro->getPagina()->getCategoria()->getNombre().' '.$foro->getPagina()->getNombre().'.';

            $usuarios_id = array();
            $usuarios_arr = array();

            foreach ($nivel_paginas as $np)
            {
                $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findByNivel($np->getNivel()->getId());
                foreach ($usuarios as $usuario_nivel)
                {
                    $query = $em->createQuery('SELECT COUNT(ru.id) FROM LinkComunBundle:AdminRolUsuario ru 
                                                WHERE ru.rol = :rol_id AND ru.usuario = :usuario_id')
                                ->setParameters(array('rol_id' => $yml['parameters']['rol']['participante'],
                                                      'usuario_id' => $usuario_nivel->getId()));
                    $participante = $query->getSingleScalarResult();
                    if (!in_array($usuario_nivel->getId(), $usuarios_id) && $participante && $usuario_nivel->getId() != $foro->getUsuario()->getId())
                    {
                        $usuarios_id[] = $usuario_nivel->getId();
                        $usuarios_arr[] = $usuario_nivel;
                    }
                }
            }

            foreach ($usuarios_arr as $usuario_nivel)
            {

                $f->newAlarm($yml['parameters']['tipo_alarma']['espacio_colaborativo'], $descripcion, $usuario_nivel, $foro->getId());

                $correo_participante = (!$usuario_nivel->getCorreoPersonal() || $usuario_nivel->getCorreoPersonal() == '') ? (!$usuario_nivel->getCorreoCorporativo() || $usuario_nivel->getCorreoCorporativo() == '') ? 0 : $usuario_nivel->getCorreoCorporativo() : $usuario_nivel->getCorreoPersonal();
                if ($correo_participante)
                {
                    $parametros_correo = array('twig' => 'LinkFrontendBundle:Colaborativo:emailColaborativoParticipantes.html.twig',
                                               'datos' => array('descripcion' => $descripcion,
                                                                'href' => $this->router->generate('_detalleColaborativo', array('foro_id' => $foro->getId()))),
                                               'asunto' => 'Formación 2.0: Nuevo espacio colaborativo.',
                                               'remitente' => $yml['parameters']['mailer_user'],
                                               'destinatario' => $correo_participante);
                    $correo = $f->sendEmail($parametros_correo);
                    $output->writeln(var_dump($parametros_correo));
                    $output->writeln(var_dump($correo));
                }

            }

        }
        
        

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