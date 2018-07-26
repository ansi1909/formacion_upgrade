-- Function: fnresumen_general(integer, integer, timestamp without time zone)

-- DROP FUNCTION fnresumen_general(integer, integer, timestamp without time zone);

CREATE OR REPLACE FUNCTION fnresumen_general(
    pempresa_id integer,
    ppagina_id integer,
    pfecha timestamp without time zone)
  RETURNS text AS
$BODY$
declare
    activos integer;        -- Participantes registrados y que han ingresado a la plataforma
    inactivos integer;      -- Participantes registrados y que no han ingresado a la plataforma
    no_iniciados integer;   -- Participantes activos que no han iniciado el programa
    en_curso integer;       -- Participantes activos que están viendo el programa
    aprobados integer;      -- Participantes activos que completaron el programa
    str text;               -- Cadena para debug
begin

    -- Activos
    SELECT COUNT(u.id) INTO activos FROM admin_usuario u 
    WHERE u.activo = true AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s WHERE s.fecha_ingreso <= pfecha)
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.empresa_id = pempresa_id 
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        );

    -- Inactivos
    SELECT COUNT(u.id) INTO inactivos FROM admin_usuario u 
    WHERE u.activo = true AND u.id NOT IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s WHERE s.fecha_ingreso <= pfecha)
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.empresa_id = pempresa_id 
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        );

    -- Asignados al programa
    /*SELECT COUNT(u.id) INTO asignados FROM admin_usuario u 
    WHERE u.activo = true 
    AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s WHERE s.fecha_ingreso <= pfecha)
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.empresa_id = pempresa_id 
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        );*/

    -- No iniciados en el programa
    SELECT COUNT(u.id) INTO no_iniciados FROM admin_usuario u 
    WHERE u.activo = true 
    AND u.id IN (SELECT DISTINCT(s.usuario_id) FROM admin_sesion s)
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.empresa_id = pempresa_id 
    AND u.id NOT IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = ppagina_id AND pl.fecha_inicio <= pfecha)
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        );

    -- En curso del programa
    SELECT COUNT(u.id) INTO en_curso FROM admin_usuario u 
    WHERE u.activo = true 
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.empresa_id = pempresa_id 
    AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id != 3 AND pl.fecha_inicio <= pfecha)
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        );

    -- Aprobados en el programa
    SELECT COUNT(u.id) INTO aprobados FROM admin_usuario u 
    WHERE u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND u.empresa_id = pempresa_id 
    AND u.id IN (SELECT pl.usuario_id FROM certi_pagina_log pl WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3 AND pl.fecha_inicio <= pfecha)
    AND u.nivel_id IN 
        (SELECT np.nivel_id FROM certi_nivel_pagina np WHERE np.pagina_empresa_id IN 
            (SELECT pe.id FROM certi_pagina_empresa pe WHERE pe.empresa_id = u.empresa_id AND pe.pagina_id = ppagina_id)
        );

    str = activos || '__' || inactivos  || '__' || no_iniciados || '__' || en_curso || '__' || aprobados;

    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fnresumen_general(2, 15, '2018-07-26 23:59:59');