CREATE OR REPLACE FUNCTION fnreporte_general(
    resultado refcursor,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$
   
begin


    -- Para el reporte 1
    OPEN resultado FOR 
       SELECT p.id AS id, p.nombre as nombre, TO_CHAR(pe.fecha_inicio, 'DD/MM/YYYY') as fecha_inicio, TO_CHAR(pe.fecha_vencimiento, 'DD/MM/YYYY') as fecha_vencimiento, 
       (SELECT COUNT(u.id) AS registrados FROM admin_usuario u INNER JOIN 
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
           ON u.nivel_id = n.id 
           WHERE np.pagina_empresa_id = pe.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
               AND u.activo = true
               AND u.login NOT LIKE 'temp%'
               AND u.empresa_id = pempresa_id
       ) as registrados,
       (SELECT COUNT(u.id) AS cursando FROM admin_usuario u INNER JOIN 
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
           ON u.nivel_id = n.id 
           WHERE np.pagina_empresa_id = pe.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
               AND u.activo = true
               AND u.login NOT LIKE 'temp%'
               AND u.empresa_id = pempresa_id
               AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pe.pagina_id AND pl.estatus_pagina_id != 3)        
       ) as cursando,
       (SELECT COUNT(u.id) AS culminado FROM admin_usuario u INNER JOIN 
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
           ON u.nivel_id = n.id 
           WHERE np.pagina_empresa_id = pe.id
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
               AND u.activo = true
               AND u.login NOT LIKE 'temp%'
               AND u.empresa_id = pempresa_id
               AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pe.pagina_id AND pl.estatus_pagina_id = 3)
       ) as culminado,
       (SELECT COUNT(u.id) AS no_iniciados FROM admin_usuario u INNER JOIN 
           (admin_nivel n INNER JOIN certi_nivel_pagina np ON n.id = np.nivel_id) 
           ON u.nivel_id = n.id 
           WHERE np.pagina_empresa_id = pe.id 
               AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
               AND u.activo = true 
               AND u.login NOT LIKE 'temp%'
               AND u.empresa_id = pempresa_id
               AND u.id NOT IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pe.pagina_id)
       ) as no_iniciados,
       (SELECT COUNT(u.id) AS activos FROM admin_usuario u 
           WHERE u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
               AND u.activo = true 
               AND u.login NOT LIKE 'temp%'
               AND u.empresa_id = pempresa_id
               AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s )
               AND u.nivel_id IN 
                   (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
                       (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id)
                   )
       ) as activos
       FROM certi_pagina_empresa pe INNER JOIN certi_pagina p ON pe.pagina_id = p.id 
       WHERE p.pagina_id IS NULL
           AND pe.empresa_id = pempresa_id
       ORDER BY pe.orden ASC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

 --select * from fnreporte_general('re', 2) as resultado; fetch all from re;