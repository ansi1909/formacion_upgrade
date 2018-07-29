-- Function: fnavance_programa(refcursor, integer, integer, timestamp, timestamp)

-- DROP FUNCTION fnavance_programa(refcursor, integer, integer, timestamp, timestamp);

CREATE OR REPLACE FUNCTION fnavance_programa(
    resultado refcursor,
    pempresa_id integer,
    ppagina_id integer,
    pdesde timestamp,
    phasta timestamp)
  RETURNS refcursor AS
$BODY$
   
begin

    OPEN resultado FOR 

    SELECT u.id, u.codigo AS codigo, u.login AS login, u.nombre AS nombre, u.apellido AS apellido, u.correo_personal AS correo_personal, 
        u.correo_corporativo AS correo_corporativo, e.nombre AS empresa, c.nombre AS pais, n.nombre AS nivel, 
        TO_CHAR(u.fecha_registro, 'DD/MM/YYYY') AS fecha_registro, u.campo1 AS campo1, u.campo2 AS campo2, u.campo3 AS campo3, u.campo4 AS campo4, 
        (SELECT COUNT(pl.id) AS modulos FROM certi_pagina_log pl INNER JOIN certi_pagina p ON pl.pagina_id = p.id 
            WHERE pl.estatus_pagina_id = 3 
            AND pl.usuario_id = u.id 
            AND p.pagina_id = ppagina_id 
            AND pl.fecha_inicio BETWEEN pdesde AND phasta 
        ), 
        (SELECT COUNT(pl.id) AS materias FROM certi_pagina_log pl INNER JOIN certi_pagina p ON pl.pagina_id = p.id 
            WHERE pl.estatus_pagina_id = 3 
            AND pl.usuario_id = u.id 
            AND p.pagina_id IN (SELECT p2.id FROM certi_pagina p2 WHERE p2.pagina_id = ppagina_id) 
            AND pl.fecha_inicio BETWEEN pdesde AND phasta 
        ), 
        (SELECT ROUND(AVG(prl.nota)::numeric,2) AS promedio FROM certi_prueba_log prl INNER JOIN certi_prueba pr ON prl.prueba_id = pr.id 
        WHERE prl.estado != 'EN CURSO' 
        AND prl.usuario_id = u.id 
        AND prl.fecha_inicio BETWEEN pdesde AND phasta 
        AND pr.pagina_id IN 
        (SELECT pl.pagina_id FROM certi_pagina_log pl INNER JOIN certi_pagina p ON pl.pagina_id = p.id 
            WHERE pl.usuario_id = u.id 
            AND p.pagina_id = ppagina_id
        )
    ),
        (SELECT TO_CHAR(pl.fecha_inicio, 'DD/MM/YYYY') AS fecha_inicio_programa FROM certi_pagina_log pl 
            WHERE pl.usuario_id = u.id 
            AND pl.pagina_id = ppagina_id 
            AND pl.fecha_inicio BETWEEN pdesde AND phasta 
        ), 
        (SELECT TO_CHAR(pl.fecha_inicio, 'HH:MI AM') AS hora_inicio_programa FROM certi_pagina_log pl 
            WHERE pl.usuario_id = u.id 
            AND pl.pagina_id = ppagina_id 
            AND pl.fecha_inicio BETWEEN pdesde AND phasta 
        ),
        (SELECT TO_CHAR(pl.fecha_fin, 'DD/MM/YYYY') AS fecha_fin_programa FROM certi_pagina_log pl 
            WHERE pl.usuario_id = u.id 
            AND pl.pagina_id = ppagina_id 
            AND pl.fecha_inicio BETWEEN pdesde AND phasta 
        ), 
        (SELECT TO_CHAR(pl.fecha_fin, 'HH:MI AM') AS hora_fin_programa FROM certi_pagina_log pl 
            WHERE pl.usuario_id = u.id 
            AND pl.pagina_id = ppagina_id 
            AND pl.fecha_inicio BETWEEN pdesde AND phasta 
        )
    FROM admin_usuario u INNER JOIN (admin_empresa e INNER JOIN admin_pais c ON e.pais_id = c.id) ON u.empresa_id = e.id 
    INNER JOIN admin_nivel n ON u.nivel_id = n.id 
    WHERE u.empresa_id = pempresa_id 
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        )
    ORDER BY u.codigo ASC, u.login ASC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fnavance_programa('re', 1, 12, '2018-05-04 00:00:00', '2018-07-24 23:59:59') as resultado; fetch all from re;
  --select * from fnavance_programa('re', 2, 12, '2018-05-04 00:00:00', '2018-07-19 20:20:59') as resultado; fetch all from re;
  