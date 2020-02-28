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
        $string.=trim($key).$yml['parameters']['fallidos']['separadorMailMessage'];
        for ($i=0; $i <count($messages) ; $i++) { 
          $string.=$messages[$i];
          if( $i<count($messages)-1){
            $string.=$yml['parameters']['fallidos']['separadorMessages'];
          }
        }
        $string.=$yml['parameters']['fallidos']['separadorMails'];
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

    public function getMessage($messaBody,$yml){
      $cd1 = explode($yml['parameters']['fallidos']['separador4'],$messaBody[0]);
      $cd2 = explode($yml['parameters']['fallidos']['separador5'],$cd1[0]);
      $cd3 =explode($yml['parameters']['fallidos']['separador6'],$cd2[1]);
      $message = trim($cd3[0]);
      return array('mail'=>strstr(trim($message)," ",true),'message'=>trim(str_replace("'","",strstr(trim($message),' '))));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

      try{
            
            $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
            $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
            $em = $this->getContainer()->get('doctrine')->getManager();
            $mailTypes = array('warning'=>0,'failed'=>0,'tutor'=>0);
            $parameters = array();
            $dateFilterMail = date('d-M-Y',strtotime(date('d-M-Y')."- 1 days"));
            //$dateFilterMail = '01-Feb-2020';
            //$dateFilterDbBegin = '2020-01-28 00:00:00';
            //$dateFilterDbEnd = '2020-01-28 23:59:00';
            //$dateFilterDbBegin = date('Y-m-d 00:00:00',strtotime(date('Y-m-d 00:00:00')."- 1 days"));
            //$dateFilterDbEnd = date('Y-m-d 23:59:00',strtotime(date('Y-m-d 23:59:00')."- 1 days"));
            $cronJob = new AdminCronjobLog();
            $cronJob->setNombre('link:correos-fallidos') ;
            $cronJob->setFecha(new \DateTime('now'));
            $inbox = imap_open($yml['parameters']['fallidos']['inbox'],$yml2['parameters']['mailer_user_tutor'],$yml2['parameters']['mailer_password_tutor']) or die('Cannot connect to mail: ' . imap_last_error());
      		  $emails=imap_search($inbox,'FROM "'.$yml['parameters']['fallidos']['from'].'"'.'ON "'.$dateFilterMail.'"');
      		  if($emails){
      			  foreach ($emails as $email_number) {
      				  $overview=imap_fetch_overview($inbox,$email_number);
      				  foreach($overview as $over){
      					  if(isset($over->subject) ){
      						  $asunto=$this->fixTextSubject($over->subject);
      						  $auxAsunto = explode(":",$asunto);
      						  if($auxAsunto[0]==$yml['parameters']['fallidos']['subject']){
      							  $mailTypes['failed']++;
      							  $messageBody = explode($yml['parameters']['fallidos']['separador'],imap_body($inbox, $email_number));
      							  $message = $this->getMessage($messageBody,$yml);
      							  if(!array_key_exists($message['mail'], $parameters)){
      								  $parameters[$message['mail']] = [$message['message']];
      								  }else{
      									  array_push($parameters[$message['mail']],$message['message']);
      							    }
                    }
      						else if($auxAsunto[0]==$yml['parameters']['fallidos']['subject2']){
      							$mailTypes['warning']++;
      						} else{
                         $mailTypes['tutor']++;
                    }
                  }
                }
              }
      				$parameters = $this->transformArray($parameters,$yml);
      				$query = $em->getConnection()->prepare('SELECT fncorreos_noentregados(:pcorreos) AS resultado;');
      				$query->bindValue(':pcorreos', $parameters, \PDO::PARAM_STR);
      				$query->execute();
      				$r = $query->fetchAll();
              $output->writeln($r);
              $cronJob->setMensaje('Successful execution, failed emails: '.$mailTypes['failed'].', Warnings: '.$mailTypes['warning']);
              }else{
                $cronJob->setMensaje('Successful execution: NO EXISTEN CORREOS DEL TIPO: '.$yml['parameters']['fallidos']['from'].', PARA LA FECHA INDICADA EN EL BUZON');
              }
          $closeSession = imap_close($inbox);

        } catch (\Exception $ex){
         $cronJob->setMensaje('Execution failed: '.$ex->getMessage().' - '.$ex->getFile().'  '.$ex->getLine());
      }
      $em->persist($cronJob);
      $em->flush();
    }
}