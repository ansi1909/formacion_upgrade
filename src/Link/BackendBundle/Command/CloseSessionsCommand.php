<?php

// formacion2.0/src/Link/BackendBundle/Command/CloseSessionsCommand.php

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

class CloseSessionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:cerrar-sesiones')
             ->setDescription('Setea en false el campo disponible de la tabla admin_sesion');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
        $translator = $this->getContainer()->get('translator');
        $base = $yml2['parameters']['base_url'];

        // Llamada a la función que verifica aquellas sesiones abiertas de hace muchas horas
        $query = $em->getConnection()->prepare('SELECT
                                                fncerrar_sesiones() as
                                                resultado;');
        $query->execute();
        $r = $query->fetchAll();

        $output->writeln('Cantidad de sesiones cerradas: '.$r[0]['resultado']);
        
    }
}