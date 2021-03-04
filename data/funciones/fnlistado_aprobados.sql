-- Function: fnlistado_aprobados(refcursor, integer,integer, timestamp, timestamp)

-- DROP FUNCTION fnlistado_aprobados(refcursor, integer,integer,  timestamp, timestamp);

CREATE OR REPLACE FUNCTION fnlistado_aprobados(
    resultado refcursor,
    ppagina_id integer,
    pempresa_id integer,
    pnivel_id integer,
    pfecha_inicio timestamp without time zone,
    pfecha_fin timestamp without time zone)
  RETURNS refcursor AS
$BODY$

begin
     
        IF pnivel_id > 0 THEN 
        OPEN resultado FOR
            SELECT count(a.id) as logueado,
                    u.codigo as codigo,
                    u.nombre as nombre,
                    u.apellido as apellido,
                    u.login as login,
                    u.correo_personal as correo,
                    u.correo_corporativo as correo2,
                    u.activo as activo,
                    u.fecha_registro as fecha_registro,
                    u.fecha_nacimiento as fecha_nacimiento,
                    u.pais_id as pais,
                    n.nombre as nivel,
                    u.campo1 as campo1,
                    u.campo2 as campo2,
                    u.campo3 as campo3,
                    u.campo4 as campo4,
                    u.id as id,
                    u.clave as clave,
                    (SELECT ROUND(AVG(prl.nota)::numeric,2) AS promedio FROM certi_prueba_log prl INNER JOIN certi_prueba pr ON prl.prueba_id = pr.id
                        WHERE prl.estado = 'APROBADO'
                            AND prl.usuario_id = u.id
                            AND pr.pagina_id IN
                                (SELECT pl.pagina_id FROM certi_pagina_log pl INNER JOIN certi_pagina p ON pl.pagina_id = p.id
                                    WHERE pl.usuario_id = u.id
                                        AND p.pagina_id = ppagina_id
                                )
                    ) as promedio,
                    (SELECT pl.fecha_inicio AS fecha_inicio_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    ) as fecha_inicio_programa,
                    (SELECT pl.fecha_inicio AS hora_inicio_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    )as hora_inicio_programa,
                    (SELECT pl.fecha_fin AS fecha_fin_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    ) as fecha_fin_programa,
                    (SELECT pl.fecha_fin AS hora_fin_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    ) as hora_fin_programa
                FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id
                INNER JOIN certi_pagina_log cpl ON cpl.usuario_id = u.id
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id
                AND u.activo
                AND u.nivel_id = pnivel_id
                    AND u.id IN
                        (SELECT pl.usuario_id FROM certi_pagina_log pl
                            WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3 )
                    AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
                AND cpl.pagina_id = ppagina_id
                AND cpl.fecha_fin BETWEEN pfecha_inicio::timestamp AND pfecha_fin::timestamp
                GROUP BY u.id,u.codigo,u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,u.fecha_registro,u.fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.clave
                ORDER BY u.nombre ASC;
            
            ElSIF pnivel_id = 0 THEN
             OPEN resultado FOR
                SELECT count(a.id) as logueado,
                    u.codigo as codigo,
                    u.nombre as nombre,
                    u.apellido as apellido,
                    u.login as login,
                    u.correo_personal as correo,
                    u.correo_corporativo as correo2,
                    u.activo as activo,
                    u.fecha_registro as fecha_registro,
                    u.fecha_nacimiento as fecha_nacimiento,
                    u.pais_id as pais,
                    n.nombre as nivel,
                    u.campo1 as campo1,
                    u.campo2 as campo2,
                    u.campo3 as campo3,
                    u.campo4 as campo4,
                    u.id as id,
                    u.clave as clave,
                    (SELECT ROUND(AVG(prl.nota)::numeric,2) AS promedio FROM certi_prueba_log prl INNER JOIN certi_prueba pr ON prl.prueba_id = pr.id
                        WHERE prl.estado = 'APROBADO'
                            AND prl.usuario_id = u.id
                            AND pr.pagina_id IN
                                (SELECT pl.pagina_id FROM certi_pagina_log pl INNER JOIN certi_pagina p ON pl.pagina_id = p.id
                                    WHERE pl.usuario_id = u.id
                                        AND p.pagina_id = ppagina_id
                                )
                    ) as promedio,
                    (SELECT pl.fecha_inicio AS fecha_inicio_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    ) as fecha_inicio_programa,
                    (SELECT pl.fecha_inicio AS hora_inicio_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    )as hora_inicio_programa,
                    (SELECT pl.fecha_fin AS fecha_fin_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    ) as fecha_fin_programa,
                    (SELECT pl.fecha_fin AS hora_fin_programa FROM certi_pagina_log pl
                        WHERE pl.usuario_id = u.id
                            AND pl.pagina_id = ppagina_id
                    ) as hora_fin_programa
                FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id
                INNER JOIN certi_pagina_log cpl ON cpl.usuario_id = u.id
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id
                AND u.activo
                    AND u.id IN
                        (SELECT pl.usuario_id FROM certi_pagina_log pl
                            WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3 )
                    AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
                AND cpl.pagina_id = ppagina_id
                AND cpl.fecha_fin BETWEEN pfecha_inicio::timestamp AND pfecha_fin::timestamp
                GROUP BY u.id,u.codigo,u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,u.fecha_registro,u.fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.clave
                ORDER BY u.nombre ASC;
            END IF;
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fnlistado_aprobados('re', 1,1,'2020-12-01 00:00:00','2020-12-31 23:59:59') as resultado; fetch all from re;
 