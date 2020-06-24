CREATE VIEW view_cursos as(
					SELECT
              p.id,
              p.orden,
              p.nombre,
              p.categoria_id,
              p.fecha_modificacion as modificacion,
              p.estatus_contenido_id,
              cc.nombre as categoria,
              cec.nombre as status,
              COUNT(pr.id) as prueba,
              COUNT(pe.id) as empresa,
              COUNT(gp.id) as grupo,
              COUNT(pl.id) as log,
              COUNT(cf.id) as foro,
              COUNT(cm.id) as muro,
              COUNT(cp.id) as subpaginas
        FROM certi_pagina p
        INNER JOIN certi_categoria cc ON cc.id = p.categoria_id
        INNER JOIN certi_estatus_contenido cec ON cec.id = p.estatus_contenido_id
        LEFT JOIN certi_prueba pr ON pr.pagina_id = p.id
        LEFT JOIN certi_pagina_empresa pe ON pe.pagina_id = p.id
        LEFT JOIN certi_grupo_pagina gp ON gp.pagina_id = p.id
        LEFT JOIN certi_pagina_log pl ON pl.pagina_id = p.id
        LEFT JOIN certi_muro cm ON cm.pagina_id = p.id
        LEFT JOIN certi_foro cf ON cf.pagina_id = p.id
        LEFT JOIN certi_pagina cp ON cp.pagina_id = p.id
        WHERE p.pagina_id IS NULL
        GROUP BY p.id,p.nombre,p.categoria_id,p.fecha_modificacion,p.estatus_contenido_id,cc.nombre,cec.nombre
        ORDER BY p.orden ASC
);