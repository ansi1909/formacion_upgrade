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
            ->setDescription('Revisa en el buzon de tutorvirtual@formacionsmart.com en busca de correos del tipo: Mail delivery failed');
    }

    public function transformArray($parameters, $yml)
    {
        $string = '';
        foreach ($parameters as $key => $messages)
        {
            $string .= trim($key) . $yml['parameters']['fallidos']['separadorMailMessage'];
            for ($i = 0;$i < count($messages);$i++)
            {
                $string .= $messages[$i];
                if ($i < count($messages) - 1)
                {
                    $string .= $yml['parameters']['fallidos']['separadorMessages'];
                }
            }
            $string .= $yml['parameters']['fallidos']['separadorMails'];
        }
        $string = substr($string, 0, strlen($string) - 1);
        return $string;
    }

    public function fixTextSubject($str)
    {
        $subject = '';
        $subject_array = imap_mime_header_decode($str);
        foreach ($subject_array AS $obj) $subject .= utf8_encode(rtrim($obj->text, "t"));
        return $subject;
    }
    public function getMessage($messaBody, $yml, $output)
    {
        $analizar = explode($yml['parameters']['fallidos']['separador'], $messaBody);
        $analizar = explode($yml['parameters']['fallidos']['separador2'], $analizar[1]);
        $analizar = $analizar[0];
        ///Obtener mensaje de error
        $message = explode($yml['parameters']['fallidos']['separador3'], $analizar);
        $message = trim($message[1]);
        //Obtener correo destino
        $email_string = explode($yml['parameters']['fallidos']['separador4'], $analizar);
        $email_string = $email_string[1];
        $email_string = explode($yml['parameters']['fallidos']['separador5'], $email_string);
        $string_code = rawurlencode($email_string[0]);
        $email = explode($yml['parameters']['fallidos']['salto_linea'], $string_code);
        $email = trim(rawurldecode($email[0]));
        $output->writeln($email);
        return array(
            'mail' => $email,
            'message' => $message
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try
        {

            $yml = Yaml::parse(file_get_contents($this->getApplication()
                ->getKernel()
                ->getRootDir() . '/config/parametros.yml'));
            $yml2 = Yaml::parse(file_get_contents($this->getApplication()
                ->getKernel()
                ->getRootDir() . '/config/parameters.yml'));
            $em = $this->getContainer()
                ->get('doctrine')
                ->getManager();
            $mailTypes = array(
                'warning' => 0,
                'failed' => 0,
                'tutor' => 0
            );
            $parameters = array();
            //$dateFilterMail = date('d-M-Y',strtotime(date('d-M-Y')."- 5 days"));
            $dateFilterMail = date('d-M-Y');
            $output->writeln('Fecha consulta:' . $dateFilterMail);
            $cronJob = new AdminCronjobLog();
            $cronJob->setNombre('link:correos-fallidos');
            $cronJob->setFecha(new \DateTime('now'));
            $inbox = imap_open($yml['parameters']['fallidos']['inbox'], $yml2['parameters']['mailer_user_tutor'], $yml2['parameters']['mailer_password_tutor']) or die('Cannot connect to mail: ' . imap_last_error());
            $emails = imap_search($inbox, 'FROM "' . $yml['parameters']['fallidos']['from'] . '"' . 'ON "' . $dateFilterMail . '"');
            if ($emails)
            {
                $output->writeln('Si hay');
                foreach ($emails as $email_number)
                {
                    $overview = imap_fetch_overview($inbox, $email_number);
                    foreach ($overview as $over)
                    {
                        if (isset($over->subject))
                        {
                            $asunto = $this->fixTextSubject($over->subject);
                            $auxAsunto = explode(":", $asunto);
                            $output->writeln('Asunto : ' . $auxAsunto[0]);
                            if ($auxAsunto[0] == $yml['parameters']['fallidos']['subject'] || $auxAsunto[0] == $yml['parameters']['fallidos']['subject2'] || $auxAsunto[0] == $yml['parameters']['fallidos']['subject3'])
                            {
                                $mailTypes['failed']++;
                                $messageBody = imap_body($inbox, $email_number);
                                $message = $this->getMessage($messageBody, $yml, $output);
                                if (!array_key_exists($message['mail'], $parameters))
                                {
                                    $parameters[$message['mail']] = [$message['message']];
                                }
                                else
                                {
                                    array_push($parameters[$message['mail']], $message['message']);
                                }
                            }
                            else if ($auxAsunto[0] == $yml['parameters']['fallidos']['subject10'])
                            {
                                $mailTypes['warning']++;
                            }
                            else
                            {
                                $mailTypes['tutor']++;
                            }
                        }
                    }
                }
                if (count($parameters) > 0)
                {
                    $parameters = $this->transformArray($parameters, $yml);
                    $query = $em->getConnection()
                        ->prepare('SELECT fncorreos_noentregados(:pcorreos) AS resultado;');
                    $query->bindValue(':pcorreos', $parameters, \PDO::PARAM_STR);
                    $query->execute();
                    $r = $query->fetchAll();
                }
                $cronJob->setMensaje('Successful execution, failed emails: ' . $mailTypes['failed'] . ', Warnings: ' . $mailTypes['warning']);
            }
            else
            {
                $cronJob->setMensaje('Successful execution: NO EXISTEN CORREOS DEL TIPO: ' . $yml['parameters']['fallidos']['from'] . ' EN EL BUZON');
            }
            $closeSession = imap_close($inbox);

        }
        catch(\Exception $ex)
        {
            $cronJob->setMensaje('Execution failed: ' . $ex->getMessage() . ' - ' . $ex->getFile() . '  ' . $ex->getLine());
        }
        $em->persist($cronJob);
        $em->flush();
    }
}

