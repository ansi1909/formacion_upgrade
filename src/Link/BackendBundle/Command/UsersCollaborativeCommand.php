<?php

// formacion2.0/src/Link/BackendBundle/Command/UsersCollaborativeCommand.php

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
use Symfony\Component\Routing\RequestContext;
use Link\ComunBundle\Entity\AdminCorreo;

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
        $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
        $translator = $this->getContainer()->get('translator');
        $base = $yml2['parameters']['base_url'];
        $background = $yml2['parameters']['folders']['uploads'].'recursos/decorate_certificado.png';
        $logo = $yml2['parameters']['folders']['uploads'].'recursos/logo_formacion_smart.png';
        $link_plataforma = $yml2['parameters']['link_plataforma'];

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

            $descripcion = $foro->getUsuario()->getNombre().' '.$foro->getUsuario()->getApellido().' '.$translator->trans('ha creado un tema en el espacio colaborativo del').' '.$foro->getPagina()->getCategoria()->getNombre().' '.$foro->getPagina()->getNombre().'.';

            $usuarios_id = array();
            $usuarios_arr = array();

            foreach ($nivel_paginas as $np)
            {
                $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findBy(array('nivel' => $np->getNivel()->getId(),
                                                                                             'activo' => true));
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

            // Solo se enviarán 100 notificaciones por corrida
            $j = 0;

            foreach ($usuarios_arr as $usuario_nivel)
            {

                if ($j == 100)
                {
                    // Cantidad tope de correos en una corrida
                    break;
                }

                // Validar que no se haya creado la alarma a este usuario
                $alarma = $em->getRepository('LinkComunBundle:AdminAlarma')->findOneBy(array('tipoAlarma' => $yml['parameters']['tipo_alarma']['espacio_colaborativo'],
                                                                                             'usuario' => $usuario_nivel->getId(),
                                                                                             'entidadId' => $foro->getId()));
                if (!$alarma)
                {
                    $f->newAlarm($yml['parameters']['tipo_alarma']['espacio_colaborativo'], $descripcion, $usuario_nivel, $foro->getId());
                }

                $correo_participante = (!$usuario_nivel->getCorreoPersonal() || $usuario_nivel->getCorreoPersonal() == '') ? (!$usuario_nivel->getCorreoCorporativo() || $usuario_nivel->getCorreoCorporativo() == '') ? 0 : $usuario_nivel->getCorreoCorporativo() : $usuario_nivel->getCorreoPersonal();

                if ($correo_participante)
                {

                    // Validar que no se haya enviado el correo a este destinatario
                    $correo_bd = $em->getRepository('LinkComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml['parameters']['tipo_correo']['espacio_colaborativo'],
                                                                                                    'entidadId' => $foro->getId(),
                                                                                                    'usuario' => $usuario_nivel->getId(),
                                                                                                    'correo' => $correo_participante));

                    if (!$correo_bd)
                    {

                        $ruta = $this->getContainer()->get('router')->generate('_detalleColaborativo', array('foro_id' => $foro->getId()));
                        $parametros_correo = array('twig' => 'LinkFrontendBundle:Colaborativo:emailColaborativoParticipantes.html.twig',
                                                   'datos' => array('descripcion' => $descripcion,
                                                                    'href' => $base.$ruta,
                                                                    'background' => $background,
                                                                    'logo' => $logo,
                                                                    'link_plataforma' => $link_plataforma.$usuario_nivel->getEmpresa()->getId()),
                                                   'asunto' => 'Formación Smart: '.$translator->trans('Nuevo espacio colaborativo').'.',
                                                   'remitente' => $yml['parameters']['mailer_user_tutor'],
                                                   'remitente_name' => $yml['parameters']['mailer_user_tutor_name'],
                                                   'mailer' => 'tutor_mailer',
                                                   'destinatario' => $correo_participante);
                        $correo = $f->sendEmail($parametros_correo);
                        $output->writeln(var_dump($parametros_correo));
                        $output->writeln(var_dump($correo));

                        if ($correo)
                        {

                            $j++;

                            // Registro del correo recien enviado
                            $tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['espacio_colaborativo']);
                            $email = new AdminCorreo();
                            $email->setTipoCorreo($tipo_correo);
                            $email->setEntidadId($foro->getId());
                            $email->setUsuario($usuario_nivel);
                            $email->setCorreo($correo_participante);
                            $email->setFecha(new \DateTime('now'));
                            $em->persist($email);
                            $em->flush();
                            
                            // ERROR LOG
                            //error_log($j.' .----------------------------------------------------------------------------------------------');
                            //error_log($reg);
                            //error_log('Correo enviado a '.$correo);

                        }

                    }

                }

            }

        }
        
    }
}