-- Function: public.fncantidad_programados(integer, integer, integer, date)

-- DROP FUNCTION public.fncantidad_programados(integer, integer, integer, date);

CREATE OR REPLACE FUNCTION public.fncantidad_programados(
    ptipo_destino_id integer,
    pempresa_id integer,
    pentidad_id integer,
    pfecha_hoy date)
  RETURNS integer AS
$BODY$
declare
    c INTEGER := 0;          -- Contador de arr
begin

    -- En caso de ser una programacion dirigida a todos usuarios de una empresa
    IF ptipo_destino_id = 1 THEN
        SELECT COUNT(*) INTO c
        FROM admin_usuario u
        INNER JOIN admin_nivel n ON n.id = u.nivel_id
        WHERE u.activo = true
            AND u.empresa_id = pempresa_id
            AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND LOWER(n.nombre) NOT LIKE 'revisor%'
            AND LOWER(n.nombre) NOT LIKE 'tutor%'
            AND (n.fecha_fin IS NULL OR n.fecha_fin >= pfecha_hoy);
    -- En caso de ser una programacion dirigida a los participantes de un nivel especifico
    ELSIF ptipo_destino_id = 2 THEN
        SELECT COUNT(*) INTO c
        FROM admin_usuario u
        WHERE u.activo = true
            AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND u.empresa_id = pempresa_id
            AND u.nivel_id = pentidad_id;
    -- En caso de ser una programacion dirigida a los participantes del(de los) programa(s)
    ELSIF ptipo_destino_id = 3 THEN
        SELECT COUNT(u.id) INTO c
        FROM admin_usuario u
        INNER JOIN admin_nivel n ON u.nivel_id = n.id
        WHERE u.empresa_id = pempresa_id
            AND u.activo = true
            AND LOWER(n.nombre) NOT LIKE 'revisor%'
            AND LOWER(n.nombre) NOT LIKE 'tutor%'
            AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND (n.fecha_fin IS NULL OR n.fecha_fin >= pfecha_hoy)
            AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
            AND u.nivel_id IN
                (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN
                    (SELECT pe.id FROM certi_pagina_empresa pe
                     WHERE pe.empresa_id = u.empresa_id
                        AND pe.pagina_id = pentidad_id
                        AND pe.activo = true
                    )
                );

    -- En caso de ser una programacion dirigida a un grupo de participantes
    ELSIF ptipo_destino_id = 4 THEN
        SELECT COUNT(*) INTO c
        FROM admin_notificacion_programada np
        INNER JOIN admin_usuario u ON u.id = np.entidad_id
        WHERE np.grupo_id = pentidad_id
        AND u.activo = true
        AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
        AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2);

    -- En caso de ser una programacion dirigida a todos los participantes que no han ingresado a la plataforma
    ELSIF ptipo_destino_id = 5 THEN
        SELECT COUNT(u.id) INTO c
        FROM admin_usuario u
        INNER JOIN admin_nivel n ON n.id = u.nivel_id
        WHERE u.activo = true
            AND LOWER(n.nombre) NOT LIKE 'revisor%'
            AND LOWER(n.nombre) NOT LIKE 'tutor%'
            AND (n.fecha_fin IS NULL OR n.fecha_fin >= pfecha_hoy)
            AND u.empresa_id = pempresa_id
             AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
            AND u.id NOT IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s);

    -- En caso de ser una programacion dirigida a todos los participantes que no han ingresado a un programa
    ELSIF ptipo_destino_id = 6 THEN
        SELECT COUNT(u.id) INTO c
        FROM admin_usuario u
        INNER JOIN admin_nivel n ON u.nivel_id = n.id
        WHERE u.empresa_id = pempresa_id
            AND u.activo = true
            AND LOWER(n.nombre) NOT LIKE 'revisor%'
            AND LOWER(n.nombre) NOT LIKE 'tutor%'
            AND (n.fecha_fin IS NULL OR n.fecha_fin >= pfecha_hoy)
            AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
             AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND u.nivel_id IN
                (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN
                    (SELECT pe.id FROM certi_pagina_empresa pe
                     WHERE pe.empresa_id = u.empresa_id
                        AND pe.pagina_id = pentidad_id
                        AND pe.activo = true
                    )
                )
            AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s)
            AND u.id NOT IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pentidad_id);

    -- En caso de ser una programacion dirigida a todos los participantes que han aprobado el(los) programa(s)
    ELSIF ptipo_destino_id = 7 THEN
        SELECT COUNT(u.id) INTO c
        FROM admin_usuario u
        INNER JOIN admin_nivel n ON u.nivel_id = n.id
        WHERE u.empresa_id = pempresa_id
            AND u.activo = true
            AND LOWER(n.nombre) NOT LIKE 'revisor%'
            AND LOWER(n.nombre) NOT LIKE 'tutor%'
            AND (n.fecha_fin IS NULL OR n.fecha_fin >= pfecha_hoy)
            AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
            AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND u.nivel_id IN
                (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN
                    (SELECT pe.id FROM certi_pagina_empresa pe
                     WHERE pe.empresa_id = u.empresa_id
                        AND pe.pagina_id = pentidad_id
                        AND pe.activo = true
                    )
                )
            AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s)
            AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pentidad_id AND pl.estatus_pagina_id = 3);

    -- En caso de ser una programacion dirigida a todos los participantes en curso en el(los) programa(s)
    ELSE
        SELECT COUNT(u.id) INTO c
        FROM admin_usuario u
        INNER JOIN admin_nivel n ON u.nivel_id = n.id
        WHERE u.empresa_id = pempresa_id
            AND u.activo = true
            AND LOWER(n.nombre) NOT LIKE 'revisor%'
            AND LOWER(n.nombre) NOT LIKE 'tutor%'
            AND (n.fecha_fin IS NULL OR n.fecha_fin >= pfecha_hoy)
            AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
            AND (((u.correo_personal<>'') AND (u.correo_personal IS NOT NULL)) OR((u.correo_corporativo<>'')AND(u.correo_corporativo IS NOT NULL)))
            AND u.nivel_id IN
                (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN
                    (SELECT pe.id FROM certi_pagina_empresa pe
                     WHERE pe.empresa_id = u.empresa_id
                        AND pe.pagina_id = pentidad_id
                        AND pe.activo = true
                    )
                )
            AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s)
            AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = pentidad_id AND pl.estatus_pagina_id = 1);

    END IF;

    RETURN c;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE;

  --select * from fncantidad_programados(1,1,0) as resultado;
  --select * from fncantidad_programados(2,1,12) as resultado;
  --select * from fncantidad_programados(3,1,12) as resultado;
  --select * from fncantidad_programados(4,1,25) as resultado;
  --select * from fncantidad_programados(5,1,0) as resultado;
  --select * from fncantidad_programados(6,1,12) as resultado;
  --select * from fncantidad_programados(7,1,83) as resultado;
  --select * from fncantidad_programados(8,1,83) as resultado;
