<?php
// formacion2.0/src/Link/BackendBundle/Command/UpdateProgramScoreCommand.php
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
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPrueba;

class UpdateProgramScoreCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:update-score')
            ->setDescription('Actualiza los puntajes maximos de los programas, en base a sus evaluaciones y las medallas que se pueden obtener');

        $this->addArgument('ppagina_id', InputArgument::OPTIONAL,'Programa a actualiza, si no se indica un programa actualiza la puntuacion de todos');
    }
    

    protected function updateProgram($programId,$output,$em){
            $points = 0;
            $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir() . '/config/parametros.yml'));
            $query = $em->getConnection()->prepare('SELECT fnobtener_estructura(:ppagina_id) as resultado;');
            $query->bindValue(':ppagina_id', $programId, \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();
            $result = json_decode($r[0]['resultado'],true);
            foreach($result as $key  => $array ){
                if($key == 'padre'){
                    $test = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array(
                            'pagina' => $array['id'],
                            'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']
                    ));
                    
                    if($test){
                        $points+= $yml['parameters']['puntos']['aprobar_primer_intento'];
                        $points+= $yml['parameters']['puntos']['aprobar_perfecto'];
                    }
                }else{
                    foreach($array as $child){
                        $test = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $child['id'],
                        'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']));                    
                        if($test){
                            $points+= $yml['parameters']['puntos']['aprobar_primer_intento'];
                            $points+= $yml['parameters']['puntos']['aprobar_perfecto'];
                        }
                    }
                }
            }
            //Sumando puntuacion del resto de medallas que pueden obtenerse 
            //Categoria Influencia
            $points+= $yml['parameters']['puntos']['influencer_1'];
            $points+= $yml['parameters']['puntos']['influencer_2'];
            $points+= $yml['parameters']['puntos']['influencer_3'];
            $points+= $yml['parameters']['puntos']['amigable_1'];
            $points+= $yml['parameters']['puntos']['amigable_2'];
            $points+= $yml['parameters']['puntos']['amigable_3'];
            //Categoria Intelectual
            $points+= $yml['parameters']['puntos']['super_smart'];
            $points+= $yml['parameters']['puntos']['perfeccionista'];
            $points+= $yml['parameters']['puntos']['imparable'];
            //Categoria eficiencia, se coloca el primer lugar solamente ya que estamos calculando la maxima puntuacion posible para un curso/programa
            $points+= $yml['parameters']['puntos']['primer_lugar'];
            //Categoria Variedades
            $points+= $yml['parameters']['puntos']['vencedor'];
            $program = $em->getRepository('LinkComunBundle:CertiPagina')->findOneById($programId);
            $program->setPuntuacion($points);
            $em->persist($program);
            $em->flush();
            $output->writeln('La puntuación del programa: '.$program->getNombre().'('.$program->getId().')'.',  se actualizo correctamente ('.$points.')');

        return 1; //update done
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $programId = $input->getArgument('ppagina_id');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $updates = 0;
        
        if ($programId){
            $output->writeln('Esta apunto de actualizar la puntuacion del programa: '.$programId.' , si no desea continuar presione ctrl + c antes de que pasen 10 segundos');
            sleep(10);
            $output->writeln('Actualizando....');
            $updates+= $this->updateProgram($programId,$output,$em);
        }else{
            $output->writeln('Esta apunto de actualizar la puntuacion de todos los programas, si no desea continuar presione ctrl + c antes de que pasen 20 segundos');
            sleep(20);
            $output->writeln('Actualizando....');
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p 
                                       WHERE p.pagina IS NULL ");
                     
            $programs = $query->getResult();
            foreach ($programs as $program ) {
                $updates+= $this->updateProgram($program->getId(),$output,$em);
            }
        }

        $output->writeln('Programas actualizados: '.$updates);
    }
}

