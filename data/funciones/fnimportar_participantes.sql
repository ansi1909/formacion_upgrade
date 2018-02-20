-- Function: fnduplicar_pagina(integer, text, integer, timestamp)

-- DROP FUNCTION fnduplicar_pagina(integer, text, integer, timestamp);

--COPY tmp_participante(codigo,login,nombre,apellido,fecha_registro,clave,correo_personal,competencia,pais_id,campo1,campo2,campo3,campo4,nivel_id,empresa_id,transaccion) 
--    FROM 'C:/wamp/www/uploads/recursos/participantes/ZqiYHue.csv' DELIMITER '|';

CREATE OR REPLACE FUNCTION fnduplicar_pagina(ppagina_id integer,
    pnombre text,
    pusuario_id integer,
    pfecha timestamp)
  --RETURNS SETOF text AS
  RETURNS text AS
$BODY$
declare
    arr text[];              -- Arreglo con toda la estructura de la página y sub-páginas
    sub_arr text[];          -- Arreglo con la estructura de las sub-páginas
    i INTEGER := 0;          -- Contador de arr
    str text;                -- Cadena para debug
    rst  record;             -- Cursor para el SELECT de la página
    newid integer;           -- Nuevo ID retornado del INSERT de certi_pagina
    neworden integer;        -- Nuevo orden que tendrá la página duplicada
begin

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

         -- Llamada a la función que duplica las sub-páginas
         SELECT fnduplicar_subpagina( rst.id::integer, pusuario_id::integer, pfecha::timestamp, newid::integer) INTO sub_arr;
         arr = arr || sub_arr; -- Unión del sub_arr con arr

         i = array_upper(arr, 1)+1; -- Siguiente iteración a partir del último índice del arreglo fusionado
         
     END LOOP;
   
    /*FOR i IN 0..array_upper(arr, 1) LOOP
        RETURN NEXT arr[i];
    END LOOP;*/
    -- Retorna la cantidad de registros insertados más el nuevo id de la página padre
    str = array_upper(arr, 1)+1 || '__' || newid;
    RETURN str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnduplicar_pagina(24, 'Copia de tertulias', 1, '2018-01-23 13:58:02') as resultado;