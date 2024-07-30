CREATE VIEW view_historico_aprobados as(
    SELECT 
                    p.id as id ,
                    p.nombre as nombre,
                    sp.nombre as estatus_pagina,
                    cc.nombre as categoria,
                    (
                        SELECT COUNT(lp.usuario_id) 
                        FROM certi_pagina_log lp 
                        INNER JOIN admin_usuario u ON u.id = lp.usuario_id
                        INNER JOIN admin_nivel n ON n.id = u.nivel_id
                        WHERE lp.pagina_id = p.id AND lp.estatus_pagina_id = 3 
                        AND LOWER(n.nombre) NOT LIKE 'revisor%'
                        AND LOWER(n.nombre) NOT LIKE 'tutor%'
                    ) as aprobados
    FROM certi_pagina p 
    INNER JOIN certi_pagina_log pl ON p.id = pl.pagina_id
    INNER JOIN admin_usuario u ON u.id = pl.usuario_id
    INNER JOIN admin_nivel n ON n.id = u.nivel_id
    INNER JOIN certi_estatus_contenido sp ON sp.id = p.estatus_contenido_id
    INNER JOIN certi_categoria cc ON cc.id = p.categoria_id
    WHERE p.pagina_id IS NULL 
    AND pl.estatus_pagina_id = 3
    AND LOWER(n.nombre) NOT LIKE 'revisor%'
    AND LOWER(n.nombre) NOT LIKE 'tutor%'
    GROUP BY p.id,p.nombre,sp.nombre,cc.nombre
    ORDER BY aprobados DESC
);