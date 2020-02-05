CREATE FUNCTION fncorreos_noentregados(pfechaAyer timestamp,pfechaCron timestamp,pcorreos TEXT) RETURNS TEXT AS $$
DECLARE
   acorreo RECORD;
   ntProgramada admin_notificacion_programada%ROWTYPE;
   notificaciones_id INTEGER[]:='{}'::int[];
   correos TEXT [];
   correoMensaje TEXT [];
   mensajes TEXT[];
   lengthCorreos INTEGER;
   lengthNotificaciones INTEGER;
   lengthMensajes INTEGER;
   im INTEGER;
   retorno TEXT :='';
BEGIN 
   correos:=string_to_array(pcorreos,'|');
   SELECT array_length(correos, 1) INTO lengthCorreos;

   FOR i IN 1..lengthCorreos LOOP
	  correoMensaje:=string_to_array(correos[i],'=>');--1 correo electronico ,2,3..mensajes
	  mensajes:=string_to_array(correoMensaje[2],'++');--mensajes del correo
	  SELECT array_length(mensajes, 1) INTO lengthMensajes;
	  im:=1;
	  FOR acorreo
	  IN SELECT * FROM admin_correo AS ac 
	  WHERE (ac.fecha BETWEEN pfechaAyer AND pfechaCron)
	  AND upper(trim(ac.correo)) = upper(trim(correoMensaje[1]))
	  LOOP
	    SELECT * INTO ntProgramada FROM admin_notificacion_programada  AS np
	    WHERE np.id = acorreo.entidad_id;
	    INSERT INTO admin_correo_fallido(correo,usuario_id,entidad_id,fecha,reenviado,mensaje)
	    VALUES(acorreo.correo,acorreo.usuario_id,ntProgramada.id,acorreo.fecha,FALSE,mensajes[im]);
	    --DELETE FROM admin_correo WHERE id=acorreo.id;
	    IF NOT ARRAY[ntProgramada.id]<@notificaciones_id THEN
			notificaciones_id=notificaciones_id||ARRAY[ntProgramada.id];
		END IF;
		IF im<lengthMensajes THEN
			im=im+1;
	    END IF;
	  END LOOP;--admin_correos	  
	  --actualizar las notificaciones 
		SELECT array_length(notificaciones_id, 1) INTO lengthNotificaciones;
		FOR i IN 1..lengthNotificaciones LOOP
			IF retorno<>''THEN
				retorno:=retorno||'-'||notificaciones_id[i];
			ELSE
				retorno:=notificaciones_id[i];
			END IF;
			SELECT * INTO ntProgramada FROM admin_notificacion_programada  AS np
			WHERE np.id = notificaciones_id[i];
			UPDATE admin_notificacion_programada SET enviado = TRUE WHERE id = ntProgramada.id;
			IF ntProgramada.grupo_id IS NOT NULL THEN
				UPDATE admin_notificacion_programada SET enviado = TRUE  WHERE id = ntprogramada.grupo_id;
			END IF;
		END LOOP;
   END LOOP;--correos parameters
   
   
  RETURN retorno;
END;
$$ LANGUAGE plpgsql;

--SELECT fncorreos_noentregados('2020-01-21 00:00:00','2020-01-21 23:59:00','ypenac@edesur.com.do => host exchange.edesur.com.do [200.88.115.213] SMTP error from remote mail server after initial connection: 451 Spam email. Email Session ID: {5E26FC89-230023-208F00A-C0000000}: retry timeout exceeded++ all hosts for edesur.com.do have been failing for a long time (and retry time not reached)') as resultado;