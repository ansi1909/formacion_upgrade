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
    protected function configure(){
        $this->setName('link:correos-fallidos')
        	 ->setDescription('Revisa en el buzon de tutorvirtual@formacionsmart.com si existen notificaciones por correo que no se entregaron a los usuarios de la plataforma');
    }
    
    public function transformArray($parameters,$yml){
      $string ='';
      foreach ($parameters as $key => $messages) {
        $string.=$key.$yml['parameters']['fallidos']['separador7'];
        for ($i=0; $i <count($messages) ; $i++) { 
          $string.=$messages[$i];
          if( $i<count($messages)-1){
            $string.=$yml['parameters']['fallidos']['separador8'];
          }
        }
        $string.=$yml['parameters']['fallidos']['separador9'];
      }
      $string = substr($string,0,strlen($string)-1);
      return $string;
    }

    public function fixTextSubject($str){
      $subject = '';
      $subject_array = imap_mime_header_decode($str);
      foreach ($subject_array AS $obj)
        $subject .= utf8_encode(rtrim($obj->text, "t"));
      return $subject;
    }

    public function getMail($messageBody,$yml){
      $cad1 = explode($yml['parameters']['fallidos']['separador2'],$messageBody[1]);
      $mail = explode($yml['parameters']['fallidos']['separador3'],$cad1[0]);
      return $mail[1];
    }

    public function getMessage($messaBody,$yml){
      $cd1 = explode($yml['parameters']['fallidos']['separador4'],$messaBody[0]);
      $cd2 = explode($yml['parameters']['fallidos']['separador5'],$cd1[0]);
      $cd3 =explode($yml['parameters']['fallidos']['separador6'],$cd2[1]);
      $message = trim($cd3[0]);
      return array('mail'=>strstr(trim($message)," ",true),'message'=>strstr(trim($message)," "));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $em = $this->getContainer()->get('doctrine')->getManager();
      $tiposCorreo = array('warning'=>0,'failed'=>0,'tutor'=>0);
      $parameters = array();
      //obtener parametros de conexion al buzon de correo
      $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
      $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
      //obtener fechas para filtrar  correos 
      //$fechaActual = date('d-M-Y');
      $fechaAyer = '20-Jan-2020';//date('d-M-Y',strtotime($fechaActual."- 1 days"));
      $fechaDB = '2020-01-20 00:00:000';
      $number = 1;
      try{
            $inbox = imap_open($yml['parameters']['fallidos']['inbox'],$yml2['parameters']['mailer_user_tutor'],$yml2['parameters']['mailer_password_tutor']) or die('Cannot connect to mail: ' . imap_last_error());
            //filtrar correos
            $emails=imap_search($inbox,'FROM "'.$yml['parameters']['fallidos']['from'].'"'.'SINCE "'.$fechaAyer.'"');
            if($emails){
              foreach ($emails as $email_number) {
                $overview=imap_fetch_overview($inbox,$email_number);
                foreach($overview as $over){
                  if(isset($over->subject) ){
                      $asunto=$this->fixTextSubject($over->subject);
                      $auxAsunto = explode(":",$asunto);
                      if($auxAsunto[0]==$yml['parameters']['fallidos']['subject']){
                         $tiposCorreo['failed']++;//correos rebote
                         $messageBody = explode($yml['parameters']['fallidos']['separador'],imap_body($inbox, $email_number));
                         $message = $this->getMessage($messageBody,$yml);
                         if(!array_key_exists($message['mail'], $parameters)){
                            $parameters[$message['mail']] = [$message['message']];
                          }else{
                            array_push($parameters[$message['mail']],$messaje['message']);
                          }
                        }
                        else if($auxAsunto[0]==$yml['parameters']['fallidos']['subject2']){
                            $tiposCorreo['warning']++;//correos en espera
                        }
                        else{
                            $tiposCorreo['tutor']++;//resto de correos 
                        }
                      }
                   }
                }
                /* llamar funcion de base de datos */
                /* crear registro de ejecucion del cron en la tabla admin_cronjob_log*/
            }else{
                /*Crear registro de ejecucion de l cron en la la tabla admin_cronjob_log*/
            }

      } catch (\Exception $ex){
        /*registrar en la tabla admin_cronjob_log la excepcion durante la ejecucion*/ 
       //$output->writeln($ex->getMessage());
      }
      //$output->writeln('Warnings: '.$tiposCorreo['warning'].' Failed: '.$tiposCorreo['failed']);
      //$output->writeln('Fecha Ayer: '.$fechaAyer);
      $closeSession = imap_close($inbox);
     // $output->writeln($closeSession);
    }
}