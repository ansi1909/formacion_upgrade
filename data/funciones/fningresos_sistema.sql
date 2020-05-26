-- Function: fningresos_sistema(integer)

-- DROP FUNCTION fningresos_sistema(integer);

CREATE OR REPLACE FUNCTION fningresos_sistema(
    pusuario_id integer)
  RETURNS text AS
$BODY$
declare
    primeraConexion text;	-- Fecha y hora de la primera conexión del usuario
    ultimaConexion text;	-- Fecha y hora de la última logeado
    promedioConexion integer;	-- Promedio en minutos de conexión
    noIniciados integer;	-- Cantidad de programas no iniciados por el usuario
    enCurso integer;		-- Cantidad de programas en curso por el usuario
    finalizados integer;	-- Cantidad de programas aprobados por el usuario
    str text;			-- Cadena para debug
    reg  record;		-- Se almacena la cantidad de conexiones y el promedio de conexión
    usr  record;		-- Se almacena el registro de usuario
begin

    -- Usuario
    SELECT INTO usr * FROM admin_usuario u WHERE u.id = pusuario_id;
    
    -- Primera conexión
    SELECT s.fecha_ingreso AS fecha_ingreso INTO primeraConexion FROM admin_sesion s 
    WHERE s.usuario_id = pusuario_id 
    ORDER BY s.id ASC LIMIT 1;

    -- Última conexión
    SELECT s.fecha_ingreso AS fecha_ingreso INTO ultimaConexion FROM admin_sesion s 
    WHERE s.usuario_id = pusuario_id 
    ORDER BY s.id DESC LIMIT 1;

    -- Cantidad de conexiones y promedio de conexión en minutos
    SELECT INTO reg COUNT(s.id) AS conexiones,AVG(s.fecha_request - s.fecha_ingreso) as promedio
    FROM admin_sesion s 
    WHERE s.usuario_id = pusuario_id;

    IF reg.promedio > time '00:00:00'  THEN
        SELECT INTO promedioConexion (extract(hour from reg.promedio)*60) + (extract(minute from reg.promedio)) + 1; -- Horas convertidas a minutos + minutos + mas los segundos redondeados a un 1
    ELSE
        promedioConexion = reg.conexiones;
    END IF;


    -- Programas no iniciados
    SELECT COUNT(p.id) INTO noIniciados FROM certi_pagina p 
    WHERE p.pagina_id IS NULL 
    AND p.id IN (
                 SELECT pe.pagina_id FROM certi_pagina_empresa pe 
                 INNER JOIN certi_pagina p ON pe.pagina_id = p.id 
                 INNER JOIN certi_nivel_pagina np ON np.pagina_empresa_id = pe.id 
                 WHERE p.pagina_id IS NULL 
                 AND pe.empresa_id = usr.empresa_id 
                 AND np.nivel_id = usr.nivel_id
                 ) 
    AND p.id NOT IN (
                     SELECT pl.pagina_id FROM certi_pagina_log pl 
                     WHERE pl.usuario_id = pusuario_id
                    );
    
    -- Programas en curso
    SELECT COUNT(p.id) INTO enCurso FROM certi_pagina p 
    WHERE p.pagina_id IS NULL 
    AND p.id IN (
                 SELECT pe.pagina_id FROM certi_pagina_empresa pe 
                 INNER JOIN certi_pagina p ON pe.pagina_id = p.id 
                 INNER JOIN certi_nivel_pagina np ON np.pagina_empresa_id = pe.id 
                 WHERE p.pagina_id IS NULL 
                 AND pe.empresa_id = usr.empresa_id 
                 AND np.nivel_id = usr.nivel_id
                 ) 
    AND p.id IN (
                 SELECT pl.pagina_id FROM certi_pagina_log pl 
                 WHERE pl.usuario_id = pusuario_id 
                 AND pl.estatus_pagina_id != 3
                 );
    
    -- Programas finalizados
    SELECT COUNT(p.id) INTO finalizados FROM certi_pagina p 
    WHERE p.pagina_id IS NULL 
    AND p.id IN (
                 SELECT pe.pagina_id FROM certi_pagina_empresa pe 
                 INNER JOIN certi_pagina p ON pe.pagina_id = p.id 
                 INNER JOIN certi_nivel_pagina np ON np.pagina_empresa_id = pe.id 
                 WHERE p.pagina_id IS NULL 
                 AND pe.empresa_id = usr.empresa_id 
                 AND np.nivel_id = usr.nivel_id
                 ) 
    AND p.id IN (
                 SELECT pl.pagina_id FROM certi_pagina_log pl 
                 WHERE pl.usuario_id = pusuario_id 
                 AND pl.estatus_pagina_id = 3
                 );
    
    str = CASE WHEN primeraConexion IS NULL THEN 'Nunca se ha conectado' ELSE primeraConexion END || '__' || CASE WHEN ultimaConexion IS NULL THEN 'Nunca se ha conectado' ELSE ultimaConexion END || '__' || reg.conexiones || '__' || promedioConexion || ' minutos' || '__' || noIniciados || '__' || enCurso || '__' || finalizados;

    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fningresos_sistema(101);