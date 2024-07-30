<?php

// formacion2.0/src/Link/BackendBundle/Command/UsersEventoCommand.php

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

class UsersEventoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:evento')
             ->setDescription('Envía correos a los usuarios de una empresa un día antes de que un evento creado tenga lugar.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
        $base = $yml2['parameters']['base_url'];
        $background = $yml2['parameters']['folders']['uploads'].'recursos/decorate_certificado.png';
        $logo = $yml2['parameters']['folders']['uploads'].'recursos/logo_formacion_smart.png';
        $footer = $yml2['parameters']['folders']['uploads'].'recursos/footer.bg.form.png';
        $link_plataforma = $yml2['parameters']['link_plataforma'];
        $tomorrow_start = date('Y-m-d', strtotime('now')).' 00:00:00';
        $tomorrow_end = date('Y-m-d', strtotime('now')).' 23:59:59';

        $query = $em->createQuery("SELECT ev FROM LinkComunBundle:AdminEvento ev 
                                    JOIN ev.empresa e 
                                    WHERE ev.fechaCreacion BETWEEN :tomorrow_start AND :tomorrow_end 
                                    AND e.activo = :activo 
                                    ORDER BY ev.id ASC")
                    ->setParameters(array('tomorrow_start' => $tomorrow_start,
                                          'tomorrow_end' => $tomorrow_end,
                                          'activo' => true));
        $eventos = $query->getResult();

    
        foreach ($eventos as $evento)
        {

            if ($evento->getNivel())
            {
                $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findBy(array('nivel' => $evento->getNivel()->getId(),
                                                                                             'activo' => true));
            }
            else {
                $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findBy(array('empresa' => $evento->getEmpresa()->getId(),
                                                                                             'activo' => true));
            }

            foreach ($usuarios as $usuario)
            {

                $correo_usuario = (!$usuario->getCorreoPersonal() || $usuario->getCorreoPersonal() == '') ? (!$usuario->getCorreoCorporativo() || $usuario->getCorreoCorporativo() == '') ? 0 : $usuario->getCorreoCorporativo() : $usuario->getCorreoPersonal();
                if ($correo_usuario)
                {
                    $ruta = $this->getContainer()->get('router')->generate('_calendarioDeEventos', array('view' => 'basicDay', 'date' => $evento->getFechaInicio()->format('Y-m-d')));
                    $parametros_correo = array('twig' => 'LinkBackendBundle:Calendario:email.html.twig',
                                               'datos' => array('tomorrow' => $evento->getFechaInicio()->format('d/m/Y H:i:s'),
                                                                'href' => $base.$ruta,
                                                                'background' => $background,
                                                                'logo' => $logo,
                                                                'footer' => $footer,
                                                                'link_plataforma' => $link_plataforma.$usuario->getEmpresa()->getId()),
                                               'asunto' => 'Formación Smart: Recordatorio de evento corporativo.',
                                               'remitente' => $yml2['parameters']['mailer_user_tutor'],
                                               'remitente_name' => $yml2['parameters']['mailer_user_tutor_name'],
                                               'destinatario' => $correo_usuario,
                                               'mailer' => 'tutor_mailer');
                    $correo = $f->sendEmail($parametros_correo);
                    $output->writeln(var_dump($parametros_correo));
                    $output->writeln(var_dump($correo));
                }

            }

        }
        
    }
}