-- Function: fnreporte_general4(refcursor, integer, integer)

-- DROP FUNCTION fnreporte_general4(refcursor, integer, integer);

CREATE OR REPLACE FUNCTION fnreporte_general4(
    resultado refcursor,
    pempresa_id integer,
    ppagina_id integer)
  RETURNS refcursor AS
$BODY$
   
begin


    OPEN resultado FOR 
       select count (u.id) as usuarios
from certi_pagina_log l inner join admin_usuario u on l.usuario_id = u.id
where l.pagina_id = ppagina_id
and u.empresa_id = pempresa_id
and l.estatus_pagina_id IN (3);

   
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE