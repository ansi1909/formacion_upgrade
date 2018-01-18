-- Function: fnmascotas_estado(refcursor, integer, integer)

-- DROP FUNCTION fnmascotas_estado(refcursor, integer, integer);

CREATE OR REPLACE FUNCTION fnprueba(
    resultado refcursor,
    ppagina_id integer)
  RETURNS refcursor AS
$BODY$
BEGIN
 --     select fnprueba('resultado', 1 ) ;fetch all from resultado;

OPEN resultado FOR SELECT *
    FROM certi_pagina  
    WHERE pagina_id = ppagina_id 
    ORDER BY orden ASC;

RETURN resultado;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION fnprueba(refcursor, integer)
  OWNER TO postgres;