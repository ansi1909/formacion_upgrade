CREATE OR REPLACE FUNCTION  fnmigrar_programa(ppagina_id integer) RETURNS VOID AS $$
DECLARE
    dictionary json;
    p record;
	son record;
	pagina_empresa record;
	prueba certi_prueba%rowtype;
	origin_structure int[];
	parents int[];
    temp_parents int[];
    next_id bigint;
    new_id bigint;
    id bigint;
    pagina_id bigint;
    parent_id bigint;
    i int;
    len int;
    is_parent int;
    video int;
    string_json text;
    fields text;
    vals text;
    select_videos text;
    update_videos text;
    query text;
DECLARE
BEGIN
   PERFORM dblink_connect('migrar_programa','host=amsterdam.venred.com user=postgres password=F$mart.4dmin dbname=fmart_develop');
   SELECT * FROM dblink('migrar_programa','SELECT nextval('''||'certi_pagina_id_seq'||''')') AS seq(nextval bigint) INTO next_id;
   select_videos := '';
   update_videos := '';
	--- Obtener array de la estructura del programa en la BD origen y crear un diccionario con los ids correspondientes en la BD destino4
   origin_structure := origin_structure||ARRAY[ppagina_id];
   parents := parents||ARRAY[ppagina_id];
   next_id := next_id + 1;
   new_id := next_id;
   SELECT array_length(parents, 1) INTO len;
   string_json := '{"'||ppagina_id||'" : '||next_id;
   WHILE len >0 LOOP
   		FOR i IN 1..len LOOP
   			FOR son IN SELECT * FROM certi_pagina AS cp WHERE cp.pagina_id = parents[i] LOOP
   				next_id := next_id + 1;
   				SELECT COUNT(cp.id) INTO is_parent FROM certi_pagina as cp WHERE cp.pagina_id = son.id;
                origin_structure := origin_structure||ARRAY[son.id];
                string_json := string_json ||' , '||'"'||son.id||'" : '||next_id;
                IF is_parent > 0 THEN
                    temp_parents := temp_parents||ARRAY[son.id];
                END IF;
            END LOOP;
        END LOOP;
        parents := '{}'::int[];
        parents := parents||temp_parents;
        temp_parents:= '{}'::int[];
        SELECT array_length(parents, 1) INTO len;
   END LOOP;--While
   string_json := string_json || '}';
   SELECT array_length(origin_structure, 1) INTO len;
   i := 1;
   dictionary := string_json::json;
   WHILE i <= len LOOP
   		fields:='INSERT INTO certi_pagina (';
   		vals:='VALUES (''';
   		SELECT * INTO p FROM certi_pagina as cp WHERE cp.id = origin_structure[i];
   		---Crear query excluyendo los campos NULL y el campo id
   		fields := fields||'nombre,';
   		vals := vals||p.nombre||''',''';
   		IF p.pagina_id IS NOT NULL THEN
   			SELECT json_extract_path_text (dictionary,p.id::text) INTO pagina_id;
   			SELECT json_extract_path_text (dictionary,p.pagina_id::text) INTO parent_id;
         	fields := fields||'pagina_id,';
         	vals := vals||parent_id||''',''';
        END IF;
      	fields := fields||'categoria_id,';
      	vals := vals||p.categoria_id||''',''';
      	IF p.descripcion IS NOT NULL THEN
         	fields := fields||'descripcion,';
         	vals := vals||p.descripcion||''',''';
      	END IF;
      	SELECT POSITION('<video' IN p.contenido) INTO video;
      	-- Si el contenido no posee video se inserta
      	IF video = 0 THEN
      		fields := fields||'contenido,';
         	vals := vals||p.contenido||''',''';
      	ELSE
      		IF select_videos = '' THEN
         		select_videos := 'SELECT id,contenido FROM certi_pagina WHERE id = '||p.id;
         	ELSE
         		select_videos := select_videos||' OR id = '||p.id;
         	END IF;

         	IF update_videos = '' THEN
         		update_videos := 'UPDATE certi_pagina SET contenido = WHERE id = '||pagina_id||'; ';
         	ELSE
         		update_videos := update_videos || 'UPDATE certi_pagina SET contenido = WHERE id = '||pagina_id||'; ';
         	END IF;

      	END IF;
      	IF p.foto IS NOT NULL THEN
         	fields := fields||'foto,';
         	vals := vals||p.foto||''',''';
      	END IF;
      	IF p.pdf IS NOT NULL THEN
         	fields := fields||'pdf,';
         	vals := vals||p.pdf||''',''';
      	END IF;
      	fields := fields||'fecha_creacion,';
      	vals := vals||p.fecha_creacion||''',''';
      	fields := fields||'fecha_modificacion,';
      	vals := vals||p.fecha_modificacion||''',''';
      	fields := fields||'estatus_contenido_id,';
      	vals := vals||p.estatus_contenido_id||''',''';
      	fields := fields||'usuario_id,';
      	vals := vals||1||''',''';
      	fields := fields||'orden';
      	vals := vals||p.orden;
      	IF p.encuesta IS NOT NULL THEN
      		fields := fields||',encuesta';
         	vals := vals||''','''||p.encuesta;
      	END IF;
      	IF p.horas_academicas IS NOT NULL THEN
      		fields := fields||',horas_academicas';
         	vals := vals||''','''||p.horas_academicas;
      	END IF;
      	fields := fields||')';
      	vals := vals||''') RETURNING id';
      	query := fields||vals;
      	SELECT * FROM dblink('migrar_programa',query) AS insertion(id INTEGER) INTO id;
      	i := i+1;
   END LOOP; --WHILE DE INSERCION
   PERFORM dblink_disconnect('migrar_programa');
   IF select_videos = '' THEN
   		RAISE NOTICE 'El programa no posee videos';
   ELSE
   		RAISE NOTICE 'Sentencias SQL para obtener contenido a actualizar';
   		RAISE NOTICE '% ',select_videos;
   		RAISE NOTICE 'Sentencias SQL para actualizar contenido';
   		RAISE NOTICE '% ',update_videos;
   END IF;
        RAISE NOTICE 'Id del programa en produccion: %',new_id;
        RAISE NOTICE 'Diccionario ids';
        RAISE NOTICE '% ',string_json;
END
$$ LANGUAGE plpgsql;
--SELECT * from fnmigrar_programa(109);
--SELECT dblink_disconnect('migrar_programa');