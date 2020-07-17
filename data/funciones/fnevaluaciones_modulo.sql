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
        u.correo_corporativo AS correo_corporativo, e.nombre AS empresa, c.nombre AS pais, n.nombre AS nivel,
        u.fecha_registro AS fecha_registro, u.campo1 AS campo1, u.campo2 AS campo2, u.campo3 AS campo3, u.campo4 AS campo4,u.activo AS activo,
        (SELECT pl.fecha_inicio FROM certi_pagina_log pl WHERE pl.usuario_id = u.id AND pl.pagina_id = ppagina_id) AS fecha_inicio_programa,
        (SELECT pl.fecha_inicio FROM certi_pagina_log pl WHERE pl.usuario_id = u.id AND pl.pagina_id = ppagina_id) AS hora_inicio_programa,
        pr.nombre AS evaluacion, prl.estado AS estado, prl.nota AS nota,
        prl.fecha_inicio AS fecha_inicio_prueba,prl.fecha_inicio AS hora_inicio_prueba
    FROM certi_prueba_log prl INNER JOIN admin_usuario u ON prl.usuario_id = u.id
    INNER JOIN certi_prueba pr ON prl.prueba_id = pr.id
    INNER JOIN admin_nivel n ON u.nivel_id = n.id
    INNER JOIN (admin_empresa e INNER JOIN admin_pais c ON e.pais_id = c.id) ON u.empresa_id = e.id
    WHERE u.empresa_id = pempresa_id
    AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND prl.fecha_inicio BETWEEN pdesde AND phasta
    AND pr.pagina_id IN (SELECT p.id FROM certi_pagina p WHERE p.pagina_id = ppagina_id)
    ORDER BY u.codigo ASC, u.login ASC, prl.id ASC;

    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fnevaluaciones_modulo('re', 2, 12, '2018-05-04 00:00:00', '2018-05-24 23:59:59') as resultado; fetch all from re;
  --select * from fnevaluaciones_modulo('re', 2, 26, '2018-05-04 00:00:00', '2018-05-19 20:20:59') as resultado; fetch all from re;
