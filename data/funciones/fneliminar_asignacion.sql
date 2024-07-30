--- Procedimiento para eliminar una asginacion de una pagina
--- No retorna nada
CREATE OR REPLACE FUNCTION fneliminar_asignacion(ppagina_id integer,pempresa_id integer) RETURNS VOID AS $$
DECLARE
   hijo record;
   pregunta record;
   foro record;
   pagina_empresa record;
   prueba_log record;
   prueba certi_prueba%rowtype;
   paginas_padre int[];
   tmp_padre int[];
   estructura int[];
   len int;
   es_padre int;
BEGIN
   --- Obtener un array con la estructura de un programa donde los ultimo elementos del array son las lecciones
   estructura := estructura||ARRAY[ppagina_id];
   paginas_padre := paginas_padre||ARRAY[ppagina_id];
   SELECT array_length(paginas_padre, 1) INTO len;
   WHILE len >0 LOOP
   		FOR i IN 1..len LOOP
   			FOR hijo IN SELECT * FROM certi_pagina AS cp WHERE cp.pagina_id = paginas_padre[i] LOOP
                SELECT COUNT(cp.id) INTO es_padre FROM certi_pagina as cp WHERE cp.pagina_id = hijo.id;
                estructura := estructura||ARRAY[hijo.id];
                IF es_padre > 0 THEN
                    tmp_padre := tmp_padre||ARRAY[hijo.id];
                END IF;
   			END LOOP;
   		END LOOP;
   		paginas_padre := '{}'::int[];
        paginas_padre := paginas_padre||tmp_padre;
        tmp_padre:= '{}'::int[];
        SELECT array_length(paginas_padre, 1) INTO len;
   END LOOP;--While
   --- recorrer el array desde el ultimo elemento al primero para eliminar prelaciones
   SELECT array_length(estructura, 1) INTO len;
   WHILE len> 0 LOOP
       FOR pagina_empresa IN SELECT * FROM certi_pagina_empresa AS pe WHERE pe.prelacion = estructura[len] and pe.empresa_id = pempresa_id LOOP
          update certi_pagina_empresa SET prelacion=NULL where id = pagina_empresa.id;
        END LOOP;
      len := len -1;
   END LOOP;
   --- recorrer array de estructua para proceder a eliminar asignaciones y navegacion de los usuarios
   --- El recorrido inicia del ultimo elemento (lecciones) hasta el primer elemento (programa/cursos)
   SELECT array_length(estructura, 1) INTO len;
   WHILE len > 0 LOOP
        delete from certi_pagina_log where pagina_id = estructura[len] and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id);
        --- Eliminar muros
        delete from certi_muro where pagina_id = estructura[len] and muro_id IS NOT NULL and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id);
        delete from certi_muro where pagina_id = estructura[len] and muro_id IS NULL and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id);
        --- Eliminar Archivos del foro
        FOR foro IN SELECT * FROM certi_foro  WHERE pagina_id = estructura[len] AND foro_id IS NULL and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id) LOOP
        	delete from certi_foro_archivo where foro_id = foro.id and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id);
        END LOOP;
        delete from certi_foro where pagina_id = estructura[len] and foro_id IS NOT NULL and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id);
        delete from certi_foro where pagina_id = estructura[len] and foro_id IS NULL and usuario_id in (select id from admin_usuario where empresa_id = pempresa_id);
        ----- ELiminar asignacion a empresas y niveles  ---------------------------
        FOR pagina_empresa IN SELECT * FROM certi_pagina_empresa AS pe WHERE pe.pagina_id = estructura[len] AND pe.empresa_id = pempresa_id LOOP
        	delete from certi_nivel_pagina where pagina_empresa_id = pagina_empresa.id;
          delete from certi_pagina_empresa where id = pagina_empresa.id;
        END LOOP;
        SELECT * INTO prueba FROM certi_prueba WHERE pagina_id = estructura[len];
        FOR prueba_log IN SELECT * FROM certi_prueba_log AS pl WHERE pl.prueba_id = prueba.id AND pl.usuario_id in (select id from admin_usuario where empresa_id = pempresa_id) LOOP
          delete from certi_respuesta where prueba_log_id = prueba_log.id;
          delete from certi_prueba_log where id = prueba_log.id;
        END LOOP;
   		len := len -1;
   END LOOP;
END;
$$ LANGUAGE plpgsql;

--SELECT * from fneliminar_asignacion(ppagina_id,pempresa_id);