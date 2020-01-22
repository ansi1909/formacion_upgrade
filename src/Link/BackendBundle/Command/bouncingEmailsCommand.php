<?php

// formacion2.0/src/Link/BackendBundle/Command/bouncingEmailsCommand.php

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
use Link\ComunBundle\Entity\AdminCorreo;
use Link\ComunBundle\Entity\AdminCronjobLog;

class bouncingEmailsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('link:correos-fallidos')
        	 ->setDescription('Revisa en el buzon de tutorvirtual@formacionsmart.com si existen notificaciones por correo que no se entregaron a los usuarios de la plataforma');
    }
    public function fix_text_subject($str)
    {
        $subject = '';
        $subject_array = imap_mime_header_decode($str);
 
        foreach ($subject_array AS $obj)
            $subject .= utf8_encode(rtrim($obj->text, "t"));
 
        return $subject;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $em = $this->getContainer()->get('doctrine')->getManager();
      $tiposCorreo = array('warning'=>0,'failed'=>0,'tutor'=>0);
      $correos = array();

      //obtener parametros de conexion al buzon de correo
      $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
      $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));


      //obtener fechas para filtrar  correos 
      //$fechaActual = date('d-M-Y');
      $fechaAyer = '17-Jan-2020';//date('d-M-Y',strtotime($fechaActual."- 1 days"));
      $number = 1;

      try{
            //$em->getRepository('LinkComunBundle:AdminCronjobLog')->findAll();
            $log = new AdminCronjobLog();
            $log->setNombre('link:correos-fallidos');
            $log->setMensaje('Prueba');
            $log->setEntidadId(1);
            $log->setFecha(new \DateTime('now'));
            $log->setDisponible(true);
            $em->persist($log);
            $em->flush();
                            
            //conectarse al buzon de correo
            $inbox = imap_open($yml['parameters']['fallidos']['inbox'],$yml2['parameters']['mailer_user_tutor'],$yml2['parameters']['mailer_password_tutor']) or die('Cannot connect to mail: ' . imap_last_error());
            //filtrar correos
            $emails=imap_search($inbox,'FROM "'.$yml['parameters']['fallidos']['from'].'"'.'SINCE "'.$fechaAyer.'"');
            if($emails){
                foreach ($emails as $email_number) {
                   //enviando el identificdor del mail
                   $overview=imap_fetch_overview($inbox,$email_number);
                   foreach($overview as $over){
                      if(isset($over->subject) ){
                        $asunto=$this->fix_text_subject($over->subject);
                        $auxAsunto = explode(":",$asunto);
                        //contar la cantidad de correos 
                        if($auxAsunto[0]==$yml['parameters']['fallidos']['subject']){
                            $tiposCorreo['failed']++;//correos rebote
                            $cadena = explode($yml['parameters']['fallidos']['separador'],imap_body($inbox, $email_number));
                            $cadena2 = explode($yml['parameters']['fallidos']['separador2'],$cadena[1]);
                            $correo = explode($yml['parameters']['fallidos']['separador3'],$cadena2[0]);
                           $output->writeln($number.' -'.utf8_decode($correo[1]));

                        }
                        else if($auxAsunto[0]==$yml['parameters']['fallidos']['subject2']){
                            $tiposCorreo['warning']++;//correos en espera
                        }
                        else{
                            $tiposCorreo['tutor']++;//rsto de correos 
                        }
                      }
                   }
                 $number++;
                }

            }else{
                /*Enviar notificacion de exito en la ejecucion del cron crear una tabla*/
            }


      } catch (\Exception $ex){//try 1 conectarse y filtrar
         $output->writeln($ex->getMessage());
      }
      //$output->writeln('Warnings: '.$tiposCorreo['warning'].' Failed: '.$tiposCorreo['failed']);
      $output->writeln('Fecha Ayer: '.$fechaAyer);


      



      $closeSession = imap_close($inbox);
      $output->writeln($closeSession);



    }
}