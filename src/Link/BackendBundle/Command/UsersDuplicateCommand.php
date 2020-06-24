<?php

// formacion2.0/src/Link/BackendBundle/Command/CloseSessionsCommand.php

namespace Link\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\Parameter;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Link\ComunBundle\Entity\AdminUsuario;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\CertiPaginaLog;

class UsersDuplicateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:usuarios-duplicados')
             ->setDescription('Retorna posibles usuarios duplicados (por empresa)');

        $this->addArgument('empresa_id', InputArgument::REQUIRED,'Empresa a consultar');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $empresa_id = $input->getArgument('empresa_id');
        $usuarios = $em->getRepository('LinkComunBundle:AdminUsuario')->findBy(array('empresa'=>$empresa_id,'activo'=>true));
        $verificados = array();
        $cs = 0;
        $output->writeln('                      ');

        foreach ($usuarios as $usuario) {
            $nombre =  explode('@', strtolower($usuario->getLogin()));
            $nombre = $nombre[0];

            if(!in_array($nombre,$verificados)){
                array_push($verificados,$nombre);
                $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u 
                                           WHERE u.login LIKE :term 
                                           AND u.empresa = :empresa_id 
                                           AND u.activo = TRUE')
                            ->setParameters(array( 'term' => $nombre.'%', 'empresa_id' => $empresa_id));
                $usuarios2 = $query->getResult();

                if(count($usuarios2)>1){
                    $output->writeln('Nombre: '.$nombre.'('. count($usuarios2) .')');

                    foreach ($usuarios2 as $sospechoso){
                        $cs+=1;
                        $sesiones = $em->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario'=>$sospechoso->getId()));
                        $logs = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findBy(array('usuario'=>$sospechoso->getId()));
                        $output->writeln($sospechoso->getLogin().' | nombre: '.$sospechoso->getNombre().' | apellido: '.$sospechoso->getApellido().' | id: '.$sospechoso->getId().'| Conecciones: '.count($sesiones).' | logs: '.count($logs). ' | Registro: '.$sospechoso->getFechaRegistro()->format('d/F/Y'));
                    }

                     $output->writeln('----------------------');
                     $output->writeln('                      ');
                }
            }

        }
        $output->writeln('Cantidad de usuarios activos: '.count($usuarios).', Usuarios sospechosos: '.$cs );


        
    }

}