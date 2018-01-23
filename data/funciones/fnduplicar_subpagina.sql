-- Function: fnduplicar_subpagina(integer, integer, timestamp, integer)

-- DROP FUNCTION fnduplicar_subpagina(integer, integer, timestamp, integer);

CREATE OR REPLACE FUNCTION fnduplicar_subpagina(ppagina_id integer,
    pusuario_id integer,
    pfecha timestamp,
    ppagina_padre_id integer)
  RETURNS text[] AS
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

         SELECT * FROM certi_pagina WHERE pagina_id = ppagina_id ORDER BY orden ASC LOOP
         str = rst.id || '__' || rst.nombre || '__' || CASE WHEN rst.pagina_id Is Null THEN 0 ELSE rst.pagina_id END || '__' || pfecha;
         arr[i] = str; -- Agregando el elemento padre al arreglo
         
         -- Buscar el orden para el nuevo registro
         SELECT MAX(orden::integer) INTO neworden FROM certi_pagina WHERE pagina_id = ppagina_padre_id;
         neworden = CASE WHEN neworden Is Null THEN 1 ELSE neworden+1 END;

         -- Inserción de la nueva sub-página
         INSERT INTO certi_pagina (nombre, pagina_id, categoria_id, descripcion, contenido, foto, pdf, fecha_creacion, fecha_modificacion, estatus_contenido_id, usuario_id, orden) VALUES 
                                  (rst.nombre, ppagina_padre_id, rst.categoria_id, rst.descripcion, rst.contenido, rst.foto, rst.pdf, pfecha, pfecha, rst.estatus_contenido_id, pusuario_id, neworden) 
                     RETURNING id INTO newid;
         raise notice 'NEWID: %', newid;

         -- Auto Llamada a la función que duplica las sub-páginas
         SELECT fnduplicar_subpagina( rst.id::integer, pusuario_id::integer, pfecha::timestamp, newid::integer ) INTO sub_arr;
         arr = arr || sub_arr; -- Unión del sub_arr con arr
         
         i = array_upper(arr, 1)+1; -- Siguiente iteración a partir del último índice del arreglo fusionado
         
     END LOOP;
   
    return arr;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnduplicar_subpagina(4, 1, '2018-01-22 16:18:55', 4);
  --select max(orden) from certi_pagina where pagina_id = 19;