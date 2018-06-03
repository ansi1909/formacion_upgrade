-- Function: fnrecordatorios_usuarios(timestamp)

-- DROP FUNCTION fnrecordatorios_usuarios(timestamp);

CREATE OR REPLACE FUNCTION fnrecordatorios_usuarios(pfecha timestamp)
 --RETURNS SETOF text AS
 RETURNS SETOF text[] AS
$BODY$
DECLARE
  arr text[];
  reg  record;             -- Cursor para el SELECT de la página
  rst  record;
  i INTEGER := 0;
  str text;
BEGIN

  FOR reg IN SELECT np.id as id, np.tipo_destino_id as tipo_destino_id, np.entidad_id as entidad_id, n.asunto as asunto, n.mensaje as mensaje, n.empresa_id as empresa_id 
             FROM admin_notificacion_programada np 
             JOIN admin_notificacion n ON np.notificacion_id = n.id 
             JOIN admin_empresa e ON n.empresa_id = e.id 
             WHERE np.fecha_difusion = pfecha
                AND e.activo = true 
                AND np.tipo_destino_id IN (1,2,3,4) 
                AND np.grupo_id IS NULL 
                ORDER BY np.id ASC LOOP
      
        -- En caso de ser una programacion dirigida a todos los participante de una empresa
        IF reg.tipo_destino_id = 1 THEN
            FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo 
                       FROM admin_usuario u 
                       WHERE u.activo = true 
                          AND u.empresa_id = reg.empresa_id 
                          ORDER BY u.id ASC LOOP
                str = rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.id;
                arr = '{}';
                arr[i] = str;
                RETURN NEXT arr;
                i = i + 1;
            END LOOP;
        -- En caso de ser una programacion dirigida a los participantes de un nivel especifico
        ELSEIF reg.tipo_destino_id = 2 THEN
            FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo 
                       FROM admin_usuario u 
                       WHERE u.activo = true 
                          AND u.empresa_id = reg.empresa_id 
                          AND u.nivel_id = reg.entidad_id 
                          ORDER BY u.id ASC LOOP
                str = rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.id;
                arr = '{}';
                arr[i] = str;
                RETURN NEXT arr;
                i = i + 1;
            END LOOP;
        -- En caso de ser una programacion dirigida a los participantes de un programa especifico
        ELSEIF reg.tipo_destino_id = 3 THEN
            FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo 
                       FROM admin_usuario u, certi_nivel_pagina cnp, certi_pagina_empresa cpe, certi_pagina cp 
                       WHERE cp.id = reg.entidad_id 
                          AND cp.estatus_contenido_id = 2 
                          AND cpe.activo = true 
                          AND cpe.empresa_id = reg.empresa_id 
                          AND cpe.pagina_id = cp.id 
                          AND cnp.pagina_empresa_id = cpe.id 
                          AND cnp.nivel_id = u.nivel_id 
                          AND u.activo = true 
                          ORDER BY u.id ASC LOOP
                str = rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.id;
                arr = '{}';
                arr[i] = str;
                RETURN NEXT arr;
                i = i + 1;
            END LOOP;
        -- En caso de ser una programacion dirigida a un grupo de participantes
        ELSE
            FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo 
                       FROM admin_usuario u, admin_notificacion_programada p 
                       WHERE p.grupo_id = reg.id 
                       AND p.entidad_id = u.id 
                       AND u.activo = true
                       ORDER BY u.id ASC LOOP
                str = rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.id;
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