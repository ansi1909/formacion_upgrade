--Tipos de errores considerados
--0A: correo --El correo se encuentra en la tabla admin correo pero esta asociado a una notficacion inexistente 
--0B: correo --El correo no se encuentra en la tabla admin_correo

CREATE FUNCTION fncorreos_noentregados(pcorreos TEXT) RETURNS TEXT AS $$
DECLARE
   admin_correo_db RECORD;
   notificacion_programada admin_notificacion_programada%ROWTYPE;
   notificaciones_id INTEGER[]:='{}'::int[];
   correos_buzon TEXT[];
   mensajes_correo TEXT[];
   correo_mensajes TEXT[];
   cantidad_correos_buzon INTEGER;
   cantidad_notificaciones_actualizar INTEGER;
   cantidad_mensajes_correo INTEGER;
   cantidad_notificaciones_id INTEGER:=0;
   cantidad_correos_fallidos INTEGER:=0;
   indice_mensaje INTEGER:=1;--para obtener el primer mensaje del correo 
   mensajes TEXT:='SUCCES';
BEGIN
	--Obtener los correos fallidos
	correos_buzon := string_to_array(pcorreos,'|');
	SELECT array_length(correos_buzon,1) INTO cantidad_correos_buzon;
	FOR i IN 1..cantidad_correos_buzon LOOP
		correo_mensajes := string_to_array(correos_buzon[i],'=>'); -- obtiene un array donde el indice 1 corresponde al correo electronico. Indice 2 corresponde a los mensajes
		mensajes_correo := string_to_array(correo_mensajes[2],'++'); -- Crea un array con los mensajes correspondientes al correo
		SELECT array_length(mensajes_correo,1) INTO cantidad_mensajes_correo;
		FOR admin_correo_db IN SELECT * FROM admin_correo AS ac
			WHERE ac.fecha = (SELECT MAX (c.fecha) FROM admin_correo as c WHERE UPPER(TRIM(c.correo)) = UPPER(TRIM(correo_mensajes[1])) ) --buscan en admin_correos el utimo correo registrado para el correo de turno
			AND UPPER(TRIM(ac.correo)) = UPPER(TRIM(correo_mensajes[1]))
		LOOP
			IF admin_correo_db  IS NOT NULL THEN
				SELECT * INTO notificacion_programada FROM admin_notificacion_programada AS np
				WHERE np.id = admin_correo_db.entidad_id;
				IF notificacion_programada.id IS NOT NULL THEN
					INSERT INTO admin_correo_fallido(correo,usuario_id,entidad_id,fecha,reenviado,mensaje)
					VALUES(admin_correo_db.correo,admin_correo_db.usuario_id,notificacion_programada.id,admin_correo_db.fecha,FALSE,mensajes_correo[indice_mensaje]);
					cantidad_correos_fallidos := cantidad_correos_fallidos + 1;
					--DELETE FROM admin_correo WHERE id=admin_correo_db.id;
					IF NOT ARRAY[notificacion_programada.id]<@notificaciones_id THEN
						notificaciones_id := notificaciones_id||ARRAY[notificacion_programada.id];
					END IF;
				ELSE
					IF mensajes = '' THEN
						mensajes := mensajes||'No esta asociado a una notificacion valida: '||correo_mensajes[1];
				    ELSE
						mensajes := mensajes||'/'||'No esta asociado a una notificacion valida: '||correo_mensajes[1]; 
					END IF;
				END IF;
			ELSE
					IF mensajes '' THEN
						mensajes := mensajes||'No se encuentra en la tabla admin_correo: '||correo_mensajes[1];
					ELSE
						mensajes := mensajes||'/'||'No se encuentra en la tabla admin_correo: '||correo_mensajes[1];
					END IF;
			END IF;
		END LOOP;--admin_correos
	END LOOP; --correos buzon
	--actualizar notificaciones programadas
		SELECT array_length(notificaciones_id,1) INTO cantidad_notificaciones_id;
		IF cantidad_notificaciones_id >0 THEN
			FOR i IN 1..cantidad_notificaciones_id LOOP
				UPDATE admin_notificacion_programada SET enviado = TRUE WHERE id = notificaciones_id[i];
			END LOOP; --loop actualizacion
		END IF;
	
	RETURN cantidad_notificaciones_id||' - '||cantidad_correos_fallidos||' - '||mensajes;
	-- cantidad de notificaciones actualizadas - cantidad de correos fallidos insertados - mensajes de error o exito
END;
$$ LANGUAGE plpgsql;

--SELECT fncorreos_noentregados('LfernandezA@edesur.com=>retry timeout exceeded|dacostad@edesur.com=>retry timeout exceeded|AMarionLandais@edesur.com=>all hosts for edesur.com have been failing for a long time (and retry time not reached)') as resultado;