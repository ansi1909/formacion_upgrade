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
    arr text[];              -- Arreglo con todos los datos del reporte
    i INTEGER := 0;          -- Contador de arr
    str text;                -- Cadena para debug
    rst  record;             -- Cursor para el SELECT de la página

begin

    If pnivel_id = 0 AND ppagina_id = 0 Then 

    -- Para el reporte 1
    OPEN resultado FOR 
        SELECT u.nombre as nombre, 
               u.apellido as apellido, 
               u.login as login, 
               u.correo_personal as correo,
               u.correo_corporativo as correo2,
               u.activo as activo,
               to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
               to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
               u.pais_id as pais, 
               n.nombre as nivel 
        FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id 
        WHERE u.empresa_id = pempresa_id 
        ORDER BY u.nombre ASC;

    ElsIf pnivel_id > 0 AND ppagina_id = 0 Then 

    -- Para el reporte 1
    OPEN resultado FOR 
        SELECT u.nombre as nombre, 
               u.apellido as apellido, 
               u.login as login, 
               u.correo_personal as correo, 
               u.correo_corporativo as correo2,
               u.activo as activo,
               to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
               to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
               u.pais_id as pais, 
               n.nombre as nivel 
        FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id 
        WHERE u.empresa_id = pempresa_id AND u.nivel_id = pnivel_id 
        ORDER BY u.nombre ASC;

    ElsIf pnivel_id = 0 AND ppagina_id > 0 Then 

    If preporte = 2 Then 
    
        OPEN resultado FOR 
            SELECT u.nombre as nombre, 
                   u.apellido as apellido, 
                   u.login as login, 
                   u.correo_personal as correo, 
                   u.activo as activo,
                   to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
                   to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
                   u.pais_id as pais, 
                   n.nombre as nivel 
            FROM admin_usuario u INNER JOIN 
                (admin_nivel n INNER JOIN 
                    (certi_nivel_pagina np INNER JOIN certi_pagina_empresa pe ON np.pagina_empresa_id = pe.id) 
                ON n.id = np.nivel_id) 
            ON u.nivel_id = n.id 
            WHERE u.empresa_id = pempresa_id AND pe.pagina_id = ppagina_id 
            ORDER BY u.nombre ASC;

    ElsIf preporte = 3 Then 

        OPEN resultado FOR 
            SELECT u.nombre as nombre, 
                   u.apellido as apellido, 
                   u.login as login, 
                   u.correo_personal as correo, 
                   u.activo as activo,
                   to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
                   to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
                   u.pais_id as pais, 
                   n.nombre as nivel
            FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id 
            WHERE u.empresa_id = pempresa_id 
                AND u.id IN 
                (SELECT pl.usuario_id FROM certi_pagina_log pl 
                    WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id != 3 )
            ORDER BY u.nombre ASC;

    ElsIf preporte = 4 Then

   OPEN resultado FOR 
            SELECT u.nombre as nombre, 
                   u.apellido as apellido, 
                   u.login as login, 
                   u.correo_personal as correo, 
                   u.activo as activo,
                   to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
                   to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
                   u.pais_id as pais, 
                   n.nombre as nivel
            FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id 
            WHERE u.empresa_id = pempresa_id 
                AND u.id IN 
                (SELECT pl.usuario_id FROM certi_pagina_log pl 
                    WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3 )
            ORDER BY u.nombre ASC;


    ElsIf preporte = 5 Then

  OPEN resultado FOR 
            SELECT u.nombre as nombre, 
                   u.apellido as apellido, 
                   u.login as login, 
                   u.correo_personal as correo, 
                   u.activo as activo,
                   to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
                   to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
                   u.pais_id as pais, 
                   n.nombre as nivel
            FROM admin_usuario u INNER JOIN (admin_nivel n INNER JOIN 
                 (certi_nivel_pagina np INNER JOIN certi_pagina_empresa pe 
                      ON np.pagina_empresa_id = pe.id) 
                 ON n.id = np.nivel_id)  
        ON u.nivel_id = n.id 
            WHERE u.empresa_id = pempresa_id 
                AND NOT EXISTS 
                (SELECT * FROM certi_pagina_log pl 
                    WHERE  pl.usuario_id = u.id AND pl.pagina_id = ppagina_id )
                AND pe.pagina_id = ppagina_id
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
  --select * from fnlistado_participantes('re', 4, 1, 0, 1) as resultado; fetch all from re;
  --select * from fnlistado_participantes('re', 5, 1, 0, 1) as resultado; fetch all from re;
