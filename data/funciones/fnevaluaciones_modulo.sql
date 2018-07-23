-- Function: fnevaluaciones_modulo(refcursor, integer, integer, timestamp, timestamp)

-- DROP FUNCTION fnevaluaciones_modulo(refcursor, integer, integer, timestamp, timestamp);

CREATE OR REPLACE FUNCTION fnevaluaciones_modulo(
    resultado refcursor,
    pempresa_id integer,
    ppagina_id integer,
    pdesde timestamp,
    phasta timestamp)
  RETURNS refcursor AS
$BODY$
   
begin

    OPEN resultado FOR 

    SELECT prl.id AS prl_id, u.codigo AS codigo, u.login AS login, u.nombre AS nombre, u.apellido AS apellido, u.correo_personal AS correo_personal, 
        u.correo_corporativo AS correo_corporativo, u.campo1 AS campo1, u.campo2 AS campo2, u.campo3 AS campo3, u.campo4 AS campo4, 
        (SELECT TO_CHAR(pl.fecha_inicio, 'DD/MM/YYYY') AS fecha_inicio_programa FROM certi_pagina_log pl WHERE pl.usuario_id = u.id AND pl.pagina_id = pr.pagina_id), 
        (SELECT TO_CHAR(pl.fecha_inicio, 'HH:MI AM') AS hora_inicio_programa FROM certi_pagina_log pl WHERE pl.usuario_id = u.id AND pl.pagina_id = pr.pagina_id), 
        pr.nombre AS evaluacion, prl.estado AS estado, prl.nota AS nota, 
        TO_CHAR(prl.fecha_inicio, 'DD/MM/YYYY') AS fecha_inicio_prueba, TO_CHAR(prl.fecha_inicio, 'HH:MI AM') AS hora_inicio_prueba 
    FROM certi_prueba_log prl INNER JOIN admin_usuario u ON prl.usuario_id = u.id 
    INNER JOIN certi_prueba pr ON prl.prueba_id = pr.id 
    WHERE u.empresa_id = pempresa_id AND pr.pagina_id = ppagina_id AND prl.fecha_inicio BETWEEN pdesde AND phasta 
    ORDER BY u.codigo ASC, u.login ASC, prl.id ASC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fnevaluaciones_modulo('re', 2, 14, '2018-05-04 00:00:00', '2018-05-17 23:59:59') as resultado; fetch all from re;
  --select * from fnevaluaciones_modulo('re', 2, 26, '2018-05-04 00:00:00', '2018-05-19 20:20:59') as resultado; fetch all from re;
  