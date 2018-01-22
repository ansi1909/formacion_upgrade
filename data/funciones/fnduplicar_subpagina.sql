-- Function: fnduplicar_subpagina(integer, integer, timestamp)

-- DROP FUNCTION fnduplicar_subpagina(integer, integer, timestamp);

CREATE OR REPLACE FUNCTION fnduplicar_subpagina(ppagina_id integer,
    pusuario_id integer,
    pfecha timestamp)
  RETURNS text[] AS
$BODY$
declare
    arr text[];
    sub_arr text[];
    i INTEGER := 0;
    str text;
    rst  record;
begin

    FOR rst IN 
         SELECT * FROM certi_pagina WHERE pagina_id = ppagina_id ORDER BY orden ASC LOOP
         str = rst.id || '__' || rst.nombre || '__' || CASE WHEN rst.pagina_id Is Null THEN 0 ELSE rst.pagina_id END || '__' || pfecha;
         arr[i] = str;
         raise notice 'SUBPAGINA %',str;
         SELECT fnduplicar_subpagina( rst.id::integer, pusuario_id::integer, pfecha::timestamp ) INTO sub_arr;
         arr = arr || sub_arr;
         i = array_upper(arr, 1)+1;
     END LOOP;
   
    return arr;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnduplicar_subpagina(4, 1, '2018-01-22 16:18:55');