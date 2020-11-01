CREATE OR REPLACE FUNCTION fnreporte_general(
    resultado refcursor,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$

begin


    -- Para el reporte 1
    OPEN resultado FOR
       SELECT p.id AS id, p.nombre as nombre, TO_CHAR(pe.fecha_inicio, 'DD/MM/YYYY') as fecha_inicio, TO_CHAR(pe.fecha_vencimiento, 'DD/MM/YYYY') as fecha_vencimiento, TO_CHAR(ne.fecha_inicio, 'DD/MM/YYYY') as fecha_inicio_nivel,  TO_CHAR(ne.fecha_fin, 'DD/MM/YYYY') as fecha_vencimiento_nivel, ne.nombre as nombre_nivel,
       (SELECT COUNT(u.id) AS registrados FROM admin_usuario u INNER JOIN
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id)
           ON u.nivel_id = n.id
           WHERE np.pagina_empresa_id = pe.id
           AND n.id = ne.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
               AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
               AND u.empresa_id = pempresa_id
       ) as registrados,
       (SELECT COUNT(u.id) AS cursando FROM admin_usuario u INNER JOIN
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id)
           ON u.nivel_id = n.id
           WHERE np.pagina_empresa_id = pe.id
           AND n.id = ne.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
               AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
               AND u.empresa_id = pempresa_id
               AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pe.pagina_id AND pl.estatus_pagina_id != 3)
               AND u.activo
       ) as cursando,
       (SELECT COUNT(u.id) AS culminado FROM admin_usuario u
        INNER JOIN admin_nivel n ON n.id = u.nivel_id
        INNER JOIN certi_pagina_log pl ON u.id = pl.usuario_id
        WHERE pl.pagina_id = pe.pagina_id  AND pl.estatus_pagina_id = 3
        AND u.empresa_id = pe.empresa_id
        AND n.id = ne.id
        AND u.activo
        AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
        ) as culminado,
       (SELECT COUNT(u.id) AS no_iniciados FROM admin_usuario u INNER JOIN
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id)
           ON u.nivel_id = n.id
           WHERE np.pagina_empresa_id = pe.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
               AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
               AND u.empresa_id = pempresa_id
               AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s)
               AND u.id NOT IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pe.pagina_id)
               AND n.id = ne.id
               AND u.activo
       ) as no_iniciados,
       (SELECT COUNT(u.id) AS activos FROM admin_usuario u INNER JOIN
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id)
           ON u.nivel_id = n.id
           WHERE np.pagina_empresa_id = pe.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
               AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
               AND u.empresa_id = pempresa_id
               AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s )
               AND n.id = ne.id
               AND u.activo
       ) as activos
       FROM certi_pagina_empresa pe INNER JOIN certi_pagina p ON pe.pagina_id = p.id
        INNER JOIN (certi_nivel_pagina np INNER JOIN admin_nivel ne ON np.nivel_id = ne.id)
        ON pe.id = np.pagina_empresa_id
       WHERE p.pagina_id IS NULL
           AND pe.empresa_id = pempresa_id
		   AND (LOWER(ne.nombre) NOT LIKE 'revisor%' AND LOWER(ne.nombre) NOT LIKE 'tutor%')
		   GROUP BY ne.id, p.nombre,p.id,pe.fecha_inicio,pe.fecha_vencimiento,ne.fecha_inicio,ne.fecha_fin,ne.nombre,pe.id
       ORDER BY p.nombre ASC;

    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

 --select * from fnreporte_general('re', 1) as resultado; fetch all from re;