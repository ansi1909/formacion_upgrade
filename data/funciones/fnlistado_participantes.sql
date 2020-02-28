-- Function: fnlistado_participantes(refcursor, integer, integer, integer, integer)

-- DROP FUNCTION fnlistado_participantes(refcursor, integer, integer, integer, integer);

CREATE OR REPLACE FUNCTION fnlistado_participantes(
    resultado refcursor,
    preporte integer,
    pempresa_id integer,
    pnivel_id integer,
    ppagina_id integer)
  RETURNS refcursor AS
$BODY$
   
begin

    If pnivel_id = 0 AND ppagina_id = 0 Then 

        If preporte = 1 then
            -- Para el reporte 1
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
                    u.competencia as competencia
                FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id 
                    AND u.login NOT LIKE 'temp%'
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                GROUP BY u.competencia,u.clave,u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo
                ORDER BY u.nombre ASC;
      ElsIf preporte = 2 Then
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
			u.id as id
		    FROM admin_usuario u INNER JOIN 
			(admin_nivel n INNER JOIN 
			    (certi_nivel_pagina np INNER JOIN certi_pagina_empresa pe ON np.pagina_empresa_id = pe.id) 
			ON n.id = np.nivel_id) 
		    ON u.nivel_id = n.id 
		    LEFT JOIN admin_sesion a ON u.id = a.usuario_id
		    WHERE u.empresa_id = pempresa_id 
			AND pe.activo = true
			AND u.login NOT LIKE 'temp%'
			AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
		    GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo
		    ORDER BY u.nombre ASC;
		
	End if;
    ElsIf pnivel_id > 0 AND ppagina_id = 0 Then 

        -- Para el reporte 1
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
                u.id as id
            FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id
            LEFT JOIN admin_sesion a ON u.id = a.usuario_id
            WHERE u.empresa_id = pempresa_id AND u.nivel_id = pnivel_id 
                AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                AND u.login NOT LIKE 'temp%'
            GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo
            ORDER BY u.nombre ASC;

    ElsIf pnivel_id = 0 AND ppagina_id > 0 Then 

        If preporte = 2 Then 

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
                        u.id as id
                    FROM admin_usuario u INNER JOIN 
                        (admin_nivel n INNER JOIN 
                            (certi_nivel_pagina np INNER JOIN certi_pagina_empresa pe ON np.pagina_empresa_id = pe.id) 
                        ON n.id = np.nivel_id) 
                    ON u.nivel_id = n.id 
                    LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                    WHERE u.empresa_id = pempresa_id AND pe.pagina_id = ppagina_id 
			AND pe.activo = true
                        AND u.login NOT LIKE 'temp%'
                        AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                    GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo
                    ORDER BY u.nombre ASC;
                
        ElsIf preporte = 3 Then 

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
                    u.id as id
                FROM admin_usuario u INNER JOIN (admin_nivel n INNER JOIN 
			    (certi_nivel_pagina np INNER JOIN certi_pagina_empresa pe ON np.pagina_empresa_id = pe.id) 
			ON n.id = np.nivel_id) ON u.nivel_id = n.id 
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id 
		AND pe.activo = true
                    AND u.id IN 
                        (SELECT pl.usuario_id FROM certi_pagina_log pl 
                        WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id != 3 ) 
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                    AND u.login NOT LIKE 'temp%'
                GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo
                ORDER BY u.nombre ASC;

        ElsIf preporte = 4 Then

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
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id 
                    AND u.id IN 
                        (SELECT pl.usuario_id FROM certi_pagina_log pl 
                            WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3 ) 
                    AND u.login NOT LIKE 'temp%'
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo,promedio,fecha_inicio_programa,hora_inicio_programa,fecha_fin_programa,hora_fin_programa
                ORDER BY u.nombre ASC;

        ElsIf preporte = 5 Then

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
                    u.id as id
                FROM admin_usuario u INNER JOIN 
                    (admin_nivel n INNER JOIN 
                        (certi_nivel_pagina np INNER JOIN certi_pagina_empresa pe ON np.pagina_empresa_id = pe.id) 
                        ON n.id = np.nivel_id)  
                    ON u.nivel_id = n.id 
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id 
		AND pe.activo = true
                    AND NOT EXISTS 
                        (SELECT * FROM certi_pagina_log pl 
                        WHERE  pl.usuario_id = u.id AND pl.pagina_id = ppagina_id ) 
                    AND u.login NOT LIKE 'temp%'
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                    AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s )
                    AND pe.pagina_id = ppagina_id
                GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id,u.codigo
                ORDER BY u.nombre ASC;

        End If;

    End If;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fnlistado_participantes('re', 1, 1, 0, 0) as resultado; fetch all from re;
  --select * from fnlistado_participantes('re', 1, 1, 1, 0) as resultado; fetch all from re;
  --select * from fnlistado_participantes('re', 2, 1, 0, 1) as resultado; fetch all from re;
  --select * from fnlistado_participantes('re', 3, 1, 0, 1) as resultado; fetch all from re;
  --select * from fnlistado_participantes('re', 4, 2, 0, 12) as resultado; fetch all from re;
  --select * from fnlistado_participantes('re', 5, 1, 0, 12) as resultado; fetch all from re;
