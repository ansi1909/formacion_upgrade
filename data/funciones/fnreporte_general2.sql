-- Function: fnreporte_general(refcursor, integer)

-- DROP FUNCTION fnreporte_general(refcursor, integer);

CREATE OR REPLACE FUNCTION fnreporte_general(
    resultado refcursor,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$
   
begin


    -- Para el reporte 1
    OPEN resultado FOR 
       SELECT p.id, p.nombre as programa, cp.fecha_inicio, cp.fecha_vencimiento, count(u.id) as usuarios
FROM admin_empresa e
INNER JOIN certi_pagina_empresa cp ON e.id = cp.empresa_id
INNER JOIN certi_pagina p ON cp.pagina_id = p.id
INNER JOIN certi_nivel_pagina np ON cp.id = np.pagina_empresa_id
INNER JOIN admin_usuario u ON np.nivel_id = u.nivel_id
WHERE e.id = 1
AND p.pagina_id is null
GROUP BY  p.id, p.nombre, cp.fecha_inicio, cp.fecha_vencimiento;

   
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE