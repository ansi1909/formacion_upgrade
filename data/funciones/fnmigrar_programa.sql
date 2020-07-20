CREATE OR REPLACE FUNCTION fnmigrar_programa(ppagina_id integer) RETURNS VOID AS $$
DECLARE
   conexion text;
   --- Variables para construccion del query
   campos text;
   valores text;
   query text;
   p record;
   video int;
   --- Variables para estructura de un programa
    hijo record;
    pagina_empresa record;
    prueba certi_prueba%rowtype;
    paginas_padre int[];
    tmp_padre int[];
    estructura int[];
    len int;
    es_padre int;
    i int;
    next_id integer;
BEGIN
   PERFORM dblink_connect('migrar_programa','host=amsterdam.venred.com user=postgres password=F$mart.4dmin dbname=fmart_develop');
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
   SELECT array_length(estructura, 1) INTO len;
   i := 1;
   WHILE i <= len LOOP
      campos:='INSERT INTO certi_pagina (';
      valores:='VALUES (''';
      SELECT * INTO p FROM certi_pagina WHERE id = estructura[i];
      ---Crear query excluyendo los campos NULL
      campos := campos||'id,';
      valores := valores||p.id||''',''';
      campos := campos||'nombre,';
      valores := valores||p.nombre||''',''';
      IF p.pagina_id IS NOT NULL THEN
         campos := campos||'pagina_id,';
         valores := valores||p.pagina_id||''',''';
      END IF;
      campos := campos||'categoria_id,';
      valores := valores||p.categoria_id||''',''';
      IF p.descripcion IS NOT NULL THEN
         campos := campos||'descripcion,';
         valores := valores||p.descripcion||''',''';
      END IF;
      SELECT POSITION('<video' IN p.contenido) INTO video;
      IF video = 0 THEN
         campos := campos||'contenido,';
         valores := valores||p.contenido||''',''';
      ELSE
          RAISE NOTICE 'Video: %  Pagina: %',video,p.id;
      END IF;
      IF p.foto IS NOT NULL THEN
         campos := campos||'foto,';
         valores := valores||p.foto||''',''';
      END IF;
      IF p.pdf IS NOT NULL THEN
         campos := campos||'pdf,';
         valores := valores||p.pdf||''',''';
      END IF;
      campos := campos||'fecha_creacion,';
      valores := valores||p.fecha_creacion||''',''';
      campos := campos||'fecha_modificacion,';
      valores := valores||p.fecha_modificacion||''',''';
      campos := campos||'estatus_contenido_id,';
      valores := valores||p.estatus_contenido_id||''',''';
      campos := campos||'usuario_id,';
      valores := valores||1||''',''';
      campos := campos||'orden';
      valores := valores||p.orden;
      IF p.encuesta IS NOT NULL THEN
         campos := campos||',encuesta';
         valores := valores||''','''||p.encuesta;
      END IF;
      IF p.horas_academicas IS NOT NULL THEN
         campos := campos||',horas_academicas';
         valores := valores||''','''||p.horas_academicas;
      END IF;
      campos := campos||')';
      valores := valores||''') RETURNING id';
      query := campos||valores;
      SELECT * FROM dblink('migrar_programa',query) AS insercion(id INTEGER) INTO next_id;
      i := i+1;
   END LOOP; --WHILE DE INSERCION
   PERFORM dblink_disconnect('migrar_programa');
END;
$$ LANGUAGE plpgsql;
--SELECT * from fnmigrar_programa(109);
