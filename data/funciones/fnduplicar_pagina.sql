-- Function: fnduplicar_pagina(integer, text, integer, timestamp)

-- DROP FUNCTION fnduplicar_pagina(integer, text, integer, timestamp);

CREATE OR REPLACE FUNCTION fnduplicar_pagina(ppagina_id integer,
    pnombre text,
    pusuario_id integer,
    pfecha timestamp)
  RETURNS SETOF text AS
$BODY$
declare
    arr text[];
    sub_arr text[];
    i INTEGER := 0;
    str text;
    rst  record;
begin

    FOR rst IN 
         SELECT * FROM certi_pagina WHERE pagina_id is null ORDER BY orden ASC LOOP
         str = rst.id || '__' || rst.nombre || '__' || CASE WHEN rst.pagina_id Is Null THEN 0 ELSE rst.pagina_id END || '__' || pfecha;
         arr[i] = str;
         raise notice 'PAGINA %',str;
         SELECT fnduplicar_subpagina( rst.id::integer, pusuario_id::integer, pfecha::timestamp ) INTO sub_arr;
         arr = arr || sub_arr;
         i = array_upper(arr, 1)+1;
     END LOOP;


    --raise notice 'arr_len: %', array_upper(arr, 1);
    --raise notice 'subarr_len: %', CASE WHEN array_length(sub_arr, 1) Is Null THEN 0 ELSE array_length(sub_arr, 1) END;
   
    FOR i IN 0..array_upper(arr, 1) LOOP
        RETURN NEXT arr[i];
    END LOOP;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnduplicar_pagina(4, 'Copia', 1, '2018-01-22 16:17:02') as id__nombre__paginaid;