-- Function: fnrecordatorios_usuarios(date)

-- DROP FUNCTION fnrecordatorios_usuarios(date);

CREATE OR REPLACE FUNCTION fnrecordatorios_usuarios(pfecha date)
 RETURNS SETOF text[] AS
$BODY$
DECLARE
  arr text[];
  reg  record;
  rst  record;
  i INTEGER := 0;
  str text;
BEGIN

  FOR reg IN 
    SELECT np.id as id, np.tipo_destino_id as tipo_destino_id, np.entidad_id as entidad_id, n.asunto as asunto, n.mensaje as mensaje, n.empresa_id as empresa_id 
    FROM admin_notificacion_programada np 
    JOIN admin_notificacion n ON np.notificacion_id = n.id 
    JOIN admin_empresa e ON n.empresa_id = e.id 
    WHERE np.fecha_difusion = pfecha
        AND e.activo = true 
        AND np.grupo_id IS NULL 
    ORDER BY np.id ASC LOOP
      
    -- En caso de ser una programacion dirigida a todos usuarios de una empresa
    IF reg.tipo_destino_id = 1 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo 
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.empresa_id = reg.empresa_id 
                AND u.login NOT LIKE 'temp%'
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a los participantes de un nivel especifico
    ELSIF reg.tipo_destino_id = 2 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo  
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.empresa_id = reg.empresa_id 
                AND u.login NOT LIKE 'temp%'
                AND u.nivel_id = reg.entidad_id 
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a los participantes de un programa especifico
    ELSIF reg.tipo_destino_id = 3 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo  
            FROM admin_usuario u 
            INNER JOIN admin_nivel n ON u.nivel_id = n.id 
            WHERE u.empresa_id = reg.empresa_id 
                AND u.activo = true 
                AND u.login NOT LIKE 'temp%'
                AND u.nivel_id IN 
                    (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
                        (SELECT pe.id FROM certi_pagina_empresa pe 
                         WHERE pe.empresa_id = u.empresa_id 
                            AND pe.pagina_id = reg.entidad_id
                            AND pe.activo = true
                        )
                    )
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a un grupo de participantes
    ELSIF reg.tipo_destino_id = 4 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo  
            FROM admin_usuario u, admin_notificacion_programada np 
            WHERE np.grupo_id = reg.id 
                AND np.entidad_id = u.id 
                AND u.activo = true 
                AND u.login NOT LIKE 'temp%'
                AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los participantes que no han ingresado a la plataforma
    ELSIF reg.tipo_destino_id = 5 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo  
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.empresa_id = reg.empresa_id 
                AND u.login NOT LIKE 'temp%'
                AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                AND u.id NOT IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s) 
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los participantes que no han ingresado a un programa
    ELSIF reg.tipo_destino_id = 6 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo  
            FROM admin_usuario u 
            INNER JOIN admin_nivel n ON u.nivel_id = n.id 
            WHERE u.empresa_id = reg.empresa_id 
                AND u.activo = true 
                AND u.login NOT LIKE 'temp%'
                AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                AND u.nivel_id IN 
                    (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
                        (SELECT pe.id FROM certi_pagina_empresa pe 
                         WHERE pe.empresa_id = u.empresa_id 
                            AND pe.pagina_id = reg.entidad_id
                            AND pe.activo = true
                        )
                    ) 
                AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s) 
                AND u.id NOT IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = reg.entidad_id) 
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los participantes que han aprobado un programa
    ELSE 
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo_personal as correo_personal, u.correo_corporativo as correo_corporativo  
            FROM admin_usuario u 
            INNER JOIN admin_nivel n ON u.nivel_id = n.id 
            WHERE u.empresa_id = reg.empresa_id 
                AND u.activo = true 
                AND u.login NOT LIKE 'temp%'
                AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
                AND u.nivel_id IN 
                    (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
                        (SELECT pe.id FROM certi_pagina_empresa pe 
                            WHERE pe.empresa_id = u.empresa_id 
                                AND pe.pagina_id = reg.entidad_id
                                AND pe.activo = true
                        )
                    ) 
                AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s) 
                AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = reg.entidad_id AND pl.estatus_pagina_id = 3) 
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || CASE WHEN rst.correo_corporativo Is Null OR rst.correo_corporativo = '' THEN rst.correo_personal ELSE rst.correo_corporativo END || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.empresa_id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;
            
    END IF;

  END LOOP;

end;
$BODY$
 LANGUAGE 'plpgsql' VOLATILE;

--select * from fnrecordatorios_usuarios('2018-11-14');