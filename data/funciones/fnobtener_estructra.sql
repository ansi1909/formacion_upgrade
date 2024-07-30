--obtiene la estructura de un programa en forma de cadena JSON valida para php
-- organiza las paginas por padre
CREATE OR REPLACE FUNCTION fnobtener_estructura(ppagina_id integer) RETURNS text AS $$
DECLARE
	json_respuesta text:='{';
	pagina_curso certi_pagina%rowtype;
	categoria certi_categoria%rowtype;
	estatus_contenido certi_estatus_contenido%rowtype;
	subpagina record;
    tiene_empresa integer;
    tiene_prueba integer;
    tiene_grupo integer;
    tiene_log integer;
    tiene_muro integer;
    tiene_foro integer;
    tiene_hijos integer;
	cantidad_padres integer;
    no_comillas text;
    eliminar integer;
	cantidad_subpaginas integer;
	es_padre integer;
	indice_subpagina integer;
	paginas_padre int[];
	aux_padres int[];

BEGIN
    SELECT * INTO pagina_curso FROM certi_pagina WHERE id = ppagina_id;
    SELECT * INTO categoria FROM certi_categoria WHERE id = pagina_curso.categoria_id;
    SELECT * INTO estatus_contenido FROM certi_estatus_contenido WHERE id = pagina_curso.estatus_contenido_id;
    ---Consultar si la pagina esta en uso
    SELECT COUNT(pe.id) INTO tiene_empresa FROM certi_pagina_empresa pe WHERE pe.pagina_id = pagina_curso.id;
    SELECT COUNT(p.id) INTO tiene_prueba FROM certi_prueba p WHERE p.pagina_id = pagina_curso.id;
    SELECT COUNT(g.id) INTO tiene_grupo FROM certi_grupo_pagina g WHERE g.pagina_id = pagina_curso.id;
    SELECT COUNT(l.id) INTO tiene_log FROM certi_pagina_log l WHERE l.pagina_id = pagina_curso.id;
    SELECT COUNT(m.id) INTO tiene_muro FROM certi_muro m WHERE m.pagina_id = pagina_curso.id;
    SELECT COUNT(f.id) INTO tiene_foro FROM certi_foro f WHERE f.pagina_id = pagina_curso.id;
    SELECT COUNT(pa.id) INTO tiene_hijos FROM certi_pagina pa WHERE pa.pagina_id = pagina_curso.id;
    SELECT REPLACE(pagina_curso.nombre,Chr(34),Chr(39)) INTO no_comillas FROM certi_pagina pa WHERE pa.id = pagina_curso.id;

    eliminar := tiene_empresa+tiene_prueba+tiene_grupo+tiene_log+tiene_muro+tiene_foro+tiene_hijos;

    paginas_padre := '{}'::int[];
    json_respuesta := json_respuesta||'"padre":'||'{'||'"id":'||pagina_curso.id||',"nombre":'||'"'||no_comillas||'",'||'"categoria":"'||categoria.nombre||'",'||'"orden":'||pagina_curso.orden||','||'"modificado":"'||pagina_curso.fecha_modificacion||'",'||'"estatus_contenido":"'||estatus_contenido.nombre||'",'||'"mover":'||tiene_empresa||',"categoria_id":'||categoria.id||',';
    IF eliminar > 0 THEN
        json_respuesta := json_respuesta||'"delete_disabled": "disabled" ';
    ELSE
        json_respuesta := json_respuesta||'"delete_disabled": "" ';
    END IF;
    json_respuesta := json_respuesta || '}';
    paginas_padre := paginas_padre||ARRAY[pagina_curso.id];

    SELECT array_length(paginas_padre, 1) INTO cantidad_padres;
    WHILE cantidad_padres > 0 LOOP
    	FOR i IN 1..cantidad_padres LOOP
    	    cantidad_subpaginas := 0;
    	    indice_subpagina := 0;
    	    json_respuesta := json_respuesta||',"'||paginas_padre[i]||'":'||'[';
            SELECT COUNT(cp.id) INTO cantidad_subpaginas FROM certi_pagina AS cp WHERE cp.pagina_id = paginas_padre[i];
    		FOR subpagina IN SELECT * FROM certi_pagina AS cp WHERE cp.pagina_id = paginas_padre[i] ORDER BY cp.orden ASC LOOP
    		    indice_subpagina := indice_subpagina + 1;
    		    SELECT COUNT(cp.id) INTO es_padre FROM certi_pagina as cp WHERE cp.pagina_id = subpagina.id;
    				SELECT * INTO categoria FROM certi_categoria WHERE id = subpagina.categoria_id;
    				SELECT * INTO estatus_contenido FROM certi_estatus_contenido WHERE id = subpagina.estatus_contenido_id;
                       SELECT COUNT(pe.id) INTO tiene_empresa FROM certi_pagina_empresa pe WHERE pe.pagina_id = pagina_curso.id;
                       SELECT COUNT(p.id) INTO tiene_prueba FROM certi_prueba p WHERE p.pagina_id = subpagina.id;
                       SELECT COUNT(g.id) INTO tiene_grupo FROM certi_grupo_pagina g WHERE g.pagina_id = subpagina.id;
                       SELECT COUNT(l.id) INTO tiene_log FROM certi_pagina_log l WHERE l.pagina_id = subpagina.id;
                       SELECT COUNT(m.id) INTO tiene_muro FROM certi_muro m WHERE m.pagina_id = subpagina.id;
                       SELECT COUNT(f.id) INTO tiene_foro FROM certi_foro f WHERE f.pagina_id = subpagina.id;
                       SELECT COUNT(pa.id) INTO tiene_hijos FROM certi_pagina pa WHERE pa.pagina_id = subpagina.id;
                       SELECT REPLACE(subpagina.nombre,Chr(34),Chr(39)) INTO no_comillas FROM certi_pagina pa WHERE pa.id = subpagina.id;

                       eliminar := tiene_empresa+tiene_prueba+tiene_grupo+tiene_log+tiene_muro+tiene_foro+tiene_hijos;
    		        json_respuesta := json_respuesta||'{'||'"id":'||subpagina.id||',"nombre":'||'"'||no_comillas||'",'||'"categoria":"'||categoria.nombre||'",'||'"orden":'||subpagina.orden||','||'"modificado":"'||subpagina.fecha_modificacion||'",'||'"estatus_contenido":"'||estatus_contenido.nombre||'",'||'"mover":'||tiene_empresa||',"categoria_id":'||categoria.id||',';
                    IF eliminar > 0 THEN
                        json_respuesta := json_respuesta||'"delete_disabled":"disabled"';
                    ELSE
                        json_respuesta := json_respuesta||'"delete_disabled":""';
                    END IF;
                    json_respuesta := json_respuesta||'}';
                    IF indice_subpagina <> cantidad_subpaginas THEN
                    	json_respuesta := json_respuesta||',';
                    END IF;
                IF es_padre > 0 THEN
                	 aux_padres := aux_padres||ARRAY[subpagina.id];
                END IF;

    		END LOOP;--FOR SUBPAGINA
    	    json_respuesta := json_respuesta || ']';
    	END LOOP;--FOR PADRES
    	paginas_padre := '{}'::int[] ;
	    paginas_padre := paginas_padre||aux_padres;
	    aux_padres:= '{}'::int[];
	    SELECT array_length(paginas_padre, 1) INTO cantidad_padres;
    END LOOP;--WHILE
    json_respuesta := json_respuesta || '}';
    --json_respuesta := quote_literal(json_respuesta);
    RETURN json_respuesta;
END;
$$ LANGUAGE plpgsql;

--SELECT * from fnobtener_estructura(1);