
CREATE FUNCTION fnalarmas_participantes(ptipo_alarma INTEGER, pdescripcion VARCHAR(500), pentidad_id INTEGER,pfecha timestamp, pgrupo_id INTEGER, pempresa_id INTEGER,pusuario_id INTEGER, prolin_id INTEGER, prolex_id INTEGER) RETURNS INTEGER AS $$
DECLARE
   participante RECORD;
   retorno INTEGER:=0;
BEGIN
    FOR participante IN SELECT u.id as id FROM admin_usuario u INNER JOIN 
		(admin_nivel n INNER JOIN 
		(certi_nivel_pagina np INNER JOIN 
		 certi_pagina_empresa pe ON np.pagina_empresa_id = pe.id) ON n.id = np.nivel_id) 
		 ON u.nivel_id = n.id 
		 LEFT JOIN admin_sesion a ON u.id = a.usuario_id
		 WHERE u.empresa_id = pempresa_id AND pe.pagina_id = pgrupo_id 
		 AND pe.activo = true
		 AND u.activo = true
		 AND u.login NOT LIKE 'temp%'
		 AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = prolin_id) 
		 AND u.id NOT IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = prolex_id) 
		 AND u.id <> pusuario_id
		 GROUP BY u.id
		 ORDER BY u.id ASC
	 LOOP
	    INSERT INTO admin_alarma(tipo_alarma_id,descripcion,usuario_id,entidad_id,leido,fecha_creacion)
		VALUES(ptipo_alarma,pdescripcion,participante.id,pentidad_id,FALSE,pfecha);
		retorno:=retorno+1;
	END LOOP;
	
	RETURN retorno;
END;
$$ LANGUAGE plpgsql;

--SELECT fnalarmas_participantes(8,'Esta es una notificacion de prueba',272,'2020-03-20 23:50:52',113,1,5) as resultado;