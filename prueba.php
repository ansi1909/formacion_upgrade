<?php 
	
   function fix_text_subject($str)
    {
        $subject = '';
        $subject_array = imap_mime_header_decode($str);
 
        foreach ($subject_array AS $obj)
            $subject .= utf8_encode(rtrim($obj->text, "t"));
 
        return $subject;
    }
    

    $tiposCorreo = array('warning'=>0,'failed'=>0,'tutor'=>0);
    $fechaAyer = '14-Jan-2020';//date('d-M-Y',strtotime($fechaActual."- 1 days"));
    $fechaDB = '2020-01-20 00:00:000';
    $number = 1;
    
    $inbox = imap_open("{imap.formacionsmart.com:993/imap/ssl/novalidate-cert}INBOX","tutorvirtual@formacionsmart.com","S{NlRk,ExZ]]") or die('Cannot connect to mail: ' . imap_last_error());
    

    $emails=imap_search($inbox,'FROM "'.'Mail Delivery System'.'"'.'SINCE "'.$fechaAyer.'"');
    
    if($emails){
              $lenCorreos = count($emails);
               //$output->writeln(count($emails));
               $c = 1;
                foreach ($emails as $email_number) {
                   //enviando el identificdor del mail
                   $overview=imap_fetch_overview($inbox,$email_number);
                   foreach($overview as $over){
                      if(isset($over->subject) ){
                        $asunto=fix_text_subject($over->subject);
                        $auxAsunto = explode(":",$asunto);
                        //contar la cantidad de correos 
                        if($auxAsunto[0]=='Mail delivery failed'){
                            $tiposCorreo['failed']++;//correos rebote
                            $cadena = explode("Action: failed",imap_body($inbox, $email_number));
                            //Obtener mensaje de error
                            $ms = explode("Reporting-MTA",$cadena[0]);
                            $ms2 = explode("The following address(es) failed:",$ms[0]);
                            $ms3 =explode("--",$ms2[1]);
                            $cadena2 = explode("Warning",$cadena[1]);
                            $correo = explode("Action: failed",$cadena2[0]);

                            print_r($ms3[0]);
                            echo"<BR>FIN<BR>";
                            // if(!in_array($correo[1], $correos)){
                            //   array_push($correos,$correo[1]);
                            // }
                            
                           // $output->writeln($cadena);

                           
                        }
                        else if($auxAsunto[0]=="Warning"){
                            $tiposCorreo['warning']++;//correos en espera
                        }
                        else{
                            $tiposCorreo['tutor']++;//rsto de correos 
                        }
                      }
                   }
                 $number++;
                }
                //$correos = $this->transform_mail_array($correos);
               // $output->writeln($correos);
            }else{
                /*Enviar notificacion de exito en la ejecucion del cron crear una tabla*/
            }
//agregar a la tabla el campo dominio 

 ?>

