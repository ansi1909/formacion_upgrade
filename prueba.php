<?php 
	
   function fixTextSubject($str)
    {
        $subject = '';
        $subject_array = imap_mime_header_decode($str);
 
        foreach ($subject_array AS $obj)
            $subject .= utf8_encode(rtrim($obj->text, "t"));
 
        return $subject;
    }

    function getMail($messageBody,$yml){

    }
    
    function transformArray($parameters){
      //=> separador de correos y mensajes
      // ++ separador de mensajes
      // | separador de elementos del array
      $string ='';

      foreach ($parameters as $key => $messages) {
        $string.=$key.'=>';
        for ($i=0; $i <count($messages) ; $i++) { 
          $string.=$messages[$i];
          if( $i<count($messages)-1){
            $string.='++';
          }
        }

        $string.='|';
      }

      $string = substr($string,0,strlen($string)-1);
      return $string;
    }


    $tiposCorreo = array('warning'=>0,'failed'=>0,'tutor'=>0);
    $fechaAyer = '20-Jan-2020';//date('d-M-Y',strtotime($fechaActual."- 1 days"));
    $fechaDB = '2020-01-20 00:00:000';
    $number = 1;
    $parameters = array();
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
                        $asunto=fixTextSubject($over->subject);
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
                            $mensaje = trim($ms3[0]);
                            $correo = explode("Action: failed",$cadena2[0]);

                            $message = strstr(trim($mensaje)," ");//mensaje
                            $em = strstr(trim($mensaje)," ",true);//coreo

                            if(!array_key_exists($em, $parameters)){
                                $parameters[$em] = [$message];

                            }else{
                                array_push($parameters[$em],$message);
                            }
                            

                           //print_r($correo);
                            //print_r(strstr($ms3[0]," "));
                            //print_r($message.'   -----   '.$em);
                            //print_r($correo[1]);
                           // echo"<BR><BR>FIN<BR><BR>";
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
                print_r(transformArray($parameters));
                //$correos = $this->transform_mail_array($correos);
               // $output->writeln($correos);
            }else{
                /*Enviar notificacion de exito en la ejecucion del cron crear una tabla*/
            }
//agregar a la tabla el campo dominio 

 ?>

