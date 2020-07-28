CREATE OR REPLACE FUNCTION  fnmigrar_programa(ppagina_id integer) RETURNS VOID AS $$
DECLARE
    dictionary_structure json;
    dictionary_question json;
    dictionary_option json;
    p record;
    test record;
    option record;
    question record;
    op_qu record;
    option_question record;
	  son record;
	  pagina_empresa record;
	  prueba certi_prueba%rowtype;
	  origin_structure int[];
	  parents int[];
    temp_parents int[];
    next_id bigint;
    new_id bigint;
    op_qu_id bigint;
    next_question_id bigint;
    next_option_id bigint;
    question_id bigint;
    option_id bigint;
    id bigint;
    pagina_id bigint;
    parent_id bigint;
    prueba_id bigint;
    i int;
    len int;
    is_parent int;
    video int;
    string_structure text;
    string_questions text;
    string_options text;
    fields text;
    vals text;
    fields_question text;
    vals_question text;
    fields_option text;
    vals_option text;
    select_videos text;
    update_videos text;
    query text;
    query_test text;
    query_question text;
    query_option text;
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
   string_structure := '{"'||ppagina_id||'" : '||next_id;
   WHILE len >0 LOOP
   		FOR i IN 1..len LOOP
   			FOR son IN SELECT * FROM certi_pagina AS cp WHERE cp.pagina_id = parents[i] LOOP
   				next_id := next_id + 1;
   				SELECT COUNT(cp.id) INTO is_parent FROM certi_pagina as cp WHERE cp.pagina_id = son.id;
                origin_structure := origin_structure||ARRAY[son.id];
                string_structure := string_structure ||' , '||'"'||son.id||'" : '||next_id;
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
   string_structure := string_structure || '}';
   SELECT array_length(origin_structure, 1) INTO len;
   i := 1;
   dictionary_structure := string_structure::json;
   WHILE i <= len LOOP
   		fields := 'INSERT INTO certi_pagina (';
   		vals := 'VALUES (''';
   		query_test := '';
   		-- Obtener pagina
   		SELECT * INTO p FROM certi_pagina as cp WHERE cp.id = origin_structure[i];
   		-- Consultar si pose prueba cargada
   		SELECT * INTO test FROM certi_prueba as tes WHERE tes.pagina_id = origin_structure[i];
   		---Crear query excluyendo los campos NULL y el campo id
   		fields := fields||'nombre,';
   		vals := vals||p.nombre||''',''';
   		IF p.pagina_id IS NOT NULL THEN
   			SELECT json_extract_path_text (dictionary_structure,p.id::text) INTO pagina_id;
   			SELECT json_extract_path_text (dictionary_structure,p.pagina_id::text) INTO parent_id;
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
      	-- Si tiene prueba
      	IF test.id IS NOT NULL THEN
      		query_test := 'INSERT INTO certi_prueba(nombre,pagina_id,cantidad_preguntas,cantidad_mostrar,duracion,usuario_id,estatus_contenido_id,fecha_creacion,fecha_modificacion)  VALUES ('''||test.nombre||''','''||pagina_id||''','''||test.cantidad_preguntas||''','''||test.cantidad_mostrar||''','''||test.duracion||''','''||1||''','''||test.estatus_contenido_id||''','''||test.fecha_creacion||''','''||test.fecha_modificacion||''') RETURNING id';
      		SELECT * FROM dblink('migrar_programa',query_test) AS insertion_test(id INTEGER) INTO prueba_id;
      		-- Obtener preguntas e insertar preguntas
      		SELECT * FROM dblink('migrar_programa','SELECT nextval('''||'certi_pregunta_id_seq'||''')') AS seq(nextval bigint) INTO next_question_id;
      		SELECT * FROM dblink('migrar_programa','SELECT nextval('''||'certi_opcion_id_seq'||''')') AS seq(nextval bigint) INTO next_option_id;
            string_questions := '';
   			FOR question IN SELECT * FROM certi_pregunta AS cp WHERE cp.prueba_id = test.id LOOP
   			    fields_question := 'INSERT INTO certi_pregunta(';
    			vals_question := 'VALUES (''';
   				next_question_id := next_question_id + 1;
   				IF string_questions = '' THEN
   					string_questions := '{"'||question.id||'" : '||next_question_id;
   				ELSE
   					string_questions := string_questions ||' , '||'"'||question.id||'" : '||next_question_id;
   			    END IF;
                fields_question := fields_question||'enunciado,';
      			vals_question := vals_question||question.enunciado||''',''';
      			IF question.imagen IS NOT NULL THEN
		         	fields_question := fields_question||'imagen,';
		         	vals_question := vals_question||question.imagen||''',''';
		      	END IF;
		      	fields_question := fields_question||'prueba_id,';
      			vals_question := vals_question||prueba_id||''',''';
      			fields_question := fields_question||'tipo_pregunta_id,';
      			vals_question := vals_question||question.tipo_pregunta_id||''',''';
      			fields_question := fields_question||'tipo_elemento_id,';
      			vals_question := vals_question||question.tipo_elemento_id||''',''';
      			fields_question := fields_question||'usuario_id,';
      			vals_question := vals_question||1||''',''';
      			fields_question := fields_question||'estatus_contenido_id,';
      			vals_question := vals_question||question.estatus_contenido_id||''',''';
      			fields_question := fields_question||'valor,';
      			vals_question := vals_question||question.valor||''',''';
      			IF question.pregunta_id IS NOT NULL THEN
		         	fields_question := fields_question||'pregunta_id,';
		         	vals_question := vals_question||question.pregunta_id||''',''';
		      	END IF;
		      	fields_question := fields_question||'orden,';
      			vals_question := vals_question||question.orden||''',''';
      			fields_question := fields_question||'fecha_creacion,';
      			vals_question := vals_question||question.fecha_creacion||''',''';
      		    fields_question := fields_question||'fecha_modificacion';
      			vals_question := vals_question||question.fecha_modificacion;
      			fields_question := fields_question||')';
      			vals_question := vals_question||''') RETURNING id';
                query_question := fields_question||vals_question;
   			    SELECT * FROM dblink('migrar_programa',query_question) AS insertion_question(id INTEGER) INTO question_id;
            END LOOP; --FOR Questions
            string_questions := string_questions || '}';
            -- opciones
            string_options := '';
            FOR option IN SELECT * FROM certi_opcion AS co WHERE co.prueba_id = test.id LOOP
            	fields_option := 'INSERT INTO certi_opcion(';
    			vals_option := 'VALUES (''';
    		    next_option_id := next_option_id + 1;
    		    IF string_options = '' THEN
   					string_options := '{"'||option.id||'" : '||next_option_id;
   				ELSE
   					string_options := string_options ||' , '||'"'||option.id||'" : '||next_option_id;
   			    END IF;
   			    fields_option := fields_option||'descripcion,';
      			vals_option := vals_option||option.descripcion||''',''';
      			IF option.imagen IS NOT NULL THEN
		         	fields_option := fields_option||'imagen,';
		         	vals_option := vals_option||option.imagen||''',''';
		      	END IF;
		      	fields_option := fields_option||'prueba_id,';
      			vals_option := vals_option||prueba_id||''',''';
      			fields_option := fields_option||'usuario_id,';
      			vals_option := vals_option||1||''',''';
      			fields_option := fields_option||'fecha_creacion,';
      			vals_option := vals_option||option.fecha_creacion||''',''';
      		    fields_option := fields_option||'fecha_modificacion';
      			vals_option := vals_option||option.fecha_modificacion;
      			fields_option := fields_option||')';
      			vals_option := vals_option||''') RETURNING id';
      			query_option := fields_option||vals_option;
   			    SELECT * FROM dblink('migrar_programa',query_option) AS insertion_option(id INTEGER) INTO option_id;
            END LOOP; -- FOR Opciones
            string_options := string_options|| '}';
            -- pregunta_opcion
             dictionary_question := string_questions::json;
    		 dictionary_option := string_options::json;
             FOR option IN SELECT * FROM certi_opcion AS co WHERE co.prueba_id = test.id LOOP
                     SELECT * INTO op_qu FROM certi_pregunta_opcion as po WHERE po.opcion_id = option.id;
                     SELECT json_extract_path_text (dictionary_question,op_qu.pregunta_id::text) INTO question_id;
                     SELECT json_extract_path_text (dictionary_option,op_qu.opcion_id::text) INTO option_id;
             		 SELECT * FROM dblink('migrar_programa','INSERT INTO certi_pregunta_opcion (pregunta_id,opcion_id,correcta)  VALUES ('''||question_id||''','''||option_id||''','''||op_qu.correcta||''') RETURNING id')AS insertion_question_option(id INTEGER) INTO op_qu_id;
             END LOOP; -- FOR pregunta_opcion
      	END IF;
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
        RAISE NOTICE '% ',string_structure;
END
$$ LANGUAGE plpgsql;
--SELECT * from fnmigrar_programa(109);
--SELECT dblink_disconnect('migrar_programa');