-- Function: fnduplicar_prueba(integer, integer, integer, timestamp)

-- DROP FUNCTION fnduplicar_prueba(integer, integer, integer, timestamp);

CREATE OR REPLACE FUNCTION fnduplicar_prueba(
    ppagina_origen_id integer,
    ppagina_id integer,
    pusuario_id integer,
    pfecha timestamp)
  --RETURNS SETOF text AS
  RETURNS integer AS
$BODY$
declare
    str text;                	-- Cadena para debug
    rst  record;             	-- Cursor para los SELECTs
    tiene_prueba integer;    	-- Para el resultado de COUNT de certi_prueba
    prueba_origen_id integer;	-- ID de certi_prueba origen
    pregunta_origen_id integer;	-- ID de certi_pregunta origen
    new_prueba_id integer;   	-- Nuevo ID retornado del INSERT
    neworden integer;        	-- Nuevo orden que tendrá la página duplicada
    ret integer :=0;	     	-- Retorno
begin

    SELECT COUNT(*) INTO tiene_prueba FROM certi_prueba WHERE pagina_id = ppagina_origen_id;

    If (tiene_prueba > 0) Then

	-- Duplicado de la prueba
	SELECT * INTO rst FROM certi_prueba WHERE pagina_id = ppagina_origen_id;
	prueba_origen_id = rst.id;

	-- Inserción de la nueva prueba
        /*INSERT INTO certi_prueba (nombre, pagina_id, cantidad_preguntas, cantidad_mostrar, duracion, usuario_id, estatus_contenido_id, fecha_creacion, fecha_modificacion) VALUES 
                                  (rst.nombre, ppagina_id, rst.cantidad_preguntas, rst.cantidad_mostrar, rst.duracion, pusuario_id, rst.estatus_contenido_id, pfecha, pfecha) 
		RETURNING id INTO new_prueba_id;*/

	-- Duplicación de preguntas
	FOR rst IN 

	    SELECT * from certi_pregunta where prueba_id = prueba_origen_id ORDER BY orden ASC LOOP
	    pregunta_origen_id = rst.id;

	    str = rst.id || '__' || rst.enunciado || '__' || rst.valor || '__' || rst.orden;
	    raise notice 'PREGUNTA: %', str;

	    -- Inserción de la nueva pregunta
	    /*INSERT INTO certi_pregunta (enunciado, imagen, prueba_id, tipo_pregunta_id, tipo_elemento_id, usuario_id, estatus_contenido_id, valor, pregunta_id, fecha_creacion, fecha_modificacion) VALUES 
                                  (rst.nombre, ppagina_id, rst.cantidad_preguntas, rst.cantidad_mostrar, rst.duracion, pusuario_id, rst.estatus_contenido_id, pfecha, pfecha) 
		RETURNING id INTO new_prueba_id;*/
         
	END LOOP;
	
    End If;

    /*
    FOR rst IN 

         SELECT * FROM certi_pagina WHERE id = ppagina_id ORDER BY orden ASC LOOP
         str = rst.id || '__' || rst.nombre || '__' || CASE WHEN rst.pagina_id Is Null THEN 0 ELSE rst.pagina_id END || '__' || pfecha || '__' || rst.orden;
         arr[i] = str; -- Agregando el elemento padre al arreglo

         -- Buscar el orden para el nuevo registro
         If rst.pagina_id is Null Then
            SELECT MAX(orden::integer) INTO neworden FROM certi_pagina WHERE pagina_id IS NULL;
         Else
            SELECT MAX(orden::integer) INTO neworden FROM certi_pagina WHERE pagina_id = rst.pagina_id;
         End If;
         neworden = neworden+1;

         -- Inserción de la nueva página padre
         INSERT INTO certi_pagina (nombre, pagina_id, categoria_id, descripcion, contenido, foto, pdf, fecha_creacion, fecha_modificacion, estatus_contenido_id, usuario_id, orden) VALUES 
                                  (pnombre, rst.pagina_id, rst.categoria_id, rst.descripcion, rst.contenido, rst.foto, rst.pdf, pfecha, pfecha, rst.estatus_contenido_id, pusuario_id, neworden) 
                     RETURNING id INTO newid;
         raise notice 'NEWID: %', newid;

         -- Duplicación de la prueba
         --SELECT fnduplicar_prueba( rst.id::integer, pusuario_id::integer, pfecha::timestamp, newid::integer ) INTO sub_arr;

         -- Llamada a la función que duplica las sub-páginas
         SELECT fnduplicar_subpagina( rst.id::integer, pusuario_id::integer, pfecha::timestamp, newid::integer) INTO sub_arr;
         arr = arr || sub_arr; -- Unión del sub_arr con arr

         i = array_upper(arr, 1)+1; -- Siguiente iteración a partir del último índice del arreglo fusionado
         
     END LOOP;
    */
    
    /*FOR i IN 0..array_upper(arr, 1) LOOP
        RETURN NEXT arr[i];
    END LOOP;*/
    -- Retorna la cantidad de registros insertados más el nuevo id de la página padre
    --str = array_upper(arr, 1)+1 || '__' || newid;
    RETURN ret;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnduplicar_prueba(20, 99, 1, '2018-01-23 13:58:02') as resultado;