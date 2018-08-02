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
       SELECT p.nombre as nombre, pe.fecha_inicio as fecha_inicio, pe.fecha_vencimiento as fecha_vencimiento, 
    (SELECT COUNT(u.id) AS registrados FROM admin_usuario u INNER JOIN 
        (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
    ON u.nivel_id = n.id 
    WHERE np.pagina_empresa_id = pe.id
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.usuario_id = u.id
                AND ru.rol_id = 2)),
    (SELECT COUNT(u.id) AS cursando FROM admin_usuario u INNER JOIN 
        (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
    ON u.nivel_id = n.id 
    WHERE np.pagina_empresa_id = pe.id
        AND p.id IN (SELECT pl.pagina_id FROM certi_pagina_log pl WHERE pl.usuario_id = u.id
                AND pl.estatus_pagina_id != 3)
        AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.usuario_id = u.id
                AND ru.rol_id = 2)
    ),
    (SELECT COUNT(u.id) AS culminado FROM admin_usuario u INNER JOIN 
        (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
    ON u.nivel_id = n.id 
    WHERE np.pagina_empresa_id = pe.id
        AND p.id IN (SELECT pl.pagina_id FROM certi_pagina_log pl WHERE pl.usuario_id = u.id
                AND pl.estatus_pagina_id = 3)
        AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.usuario_id = u.id
                AND ru.rol_id = 2)
    ),
    (SELECT COUNT(u.id) AS no_iniciados FROM admin_usuario u INNER JOIN 
        (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
    ON u.nivel_id = n.id 
    WHERE np.pagina_empresa_id = pe.id
        AND p.id NOT IN (SELECT pl.pagina_id FROM certi_pagina_log pl WHERE pl.usuario_id = u.id)
        AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.usuario_id = u.id
                AND ru.rol_id = 2)
    )
    FROM certi_pagina_empresa pe INNER JOIN certi_pagina p ON pe.pagina_id = p.id 
    WHERE p.pagina_id IS NULL
        AND pe.empresa_id = 1;

   
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE