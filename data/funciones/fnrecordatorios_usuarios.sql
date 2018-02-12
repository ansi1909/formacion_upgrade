-- Function: fnrecordatorios_usuarios(timestamp)

-- DROP FUNCTION fnrecordatorios_usuarios(timestamp);

CREATE OR REPLACE FUNCTION fnrecordatorios_usuarios(pfecha timestamp)
  --RETURNS SETOF text AS
  RETURNS TABLE( nombre VARCHAR(50), apellido VARCHAR(20), correo VARCHAR(50), asunto VARCHAR, mensaje VARCHAR, id INTEGER) AS
$BODY$
declare
    arr text[];              -- Arreglo con toda la estructura de usuario y notificacion
    i INTEGER := 0;          -- Contador de arr
    str text;                -- Cadena para debug
    rst  record;             -- Cursor para el SELECT de la página
begin

    FOR rst IN 
         SELECT np.id as id, np.tipo_destino_id as tipo_destino_id, np.entidad_id as entidad_id, n.asunto as asunto, n.mensaje as mensaje, n.empresa_id as empresa_id FROM admin_notificacion_programada np JOIN admin_notificacion n ON np.notificacion_id = n.id WHERE np.fecha_difusion = pfecha AND np.tipo_destino_id IN (1,2,3,4) AND np.grupo_id IS NULL ORDER BY np.id ASC LOOP
         CASE 
             -- En caso de ser una programacion dirigida a todos los participante de una empresa
             WHEN rst.tipo_destino_id = 1 THEN
             FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo FROM admin_usuario u WHERE u.activo = 'true' AND u.empresa_id = rst.empresa_id ORDER BY u.id ASC LOOP
                nombre := rst.nombre;
                apellido   := rst.apellido;
                correo   := rst.correo;
                RETURN NEXT;
             END LOOP;
             RETURN;
             -- En caso de ser una programacion dirigida a los participantes de un nivel especifico
             WHEN rst.tipo_destino_id = 2 THEN
             FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo FROM admin_usuario u WHERE u.activo = 'true' AND u.empresa_id = rst.empresa_id AND u.nivel_id = rst.entidad_id ORDER BY u.id ASC LOOP
                nombre := rst.nombre;
                apellido   := rst.apellido;
                correo   := rst.correo;
                RETURN NEXT;
             END LOOP;
             RETURN;
             -- En caso de ser una programacion dirigida a los participantes de un programa especifico
             WHEN rst.tipo_destino_id = 3 THEN
             FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo FROM admin_usuario u, certi_nivel_pagina cnp, certi_pagina_empresa cpe, certi_pagina cp WHERE cp.id = rst.entidad_id AND cp.estatus_contenido_id = 2 AND cpe.activo = 'true' AND cpe.empresa_id = rst.empresa_id AND cpe.pagina_id = cp.id AND cnp.pagina_empresa_id = cpe.id AND cnp.nivel_id = u.nivel_id AND u.activo = 'true' ORDER BY u.id ASC LOOP
                nombre := rst.nombre;
                apellido   := rst.apellido;
                correo   := rst.correo;
                RETURN NEXT;
             END LOOP;
             RETURN;
             -- En caso de ser una programacion dirigida a un grupo de participantes
             ELSE
             FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo FROM admin_usuario u, admin_notificacion_programada p WHERE p.grupo_id = rst.id AND p.entidad_id = u.id AND u.activo = 'true' ORDER BY u.id ASC LOOP
                nombre := rst.nombre;
                apellido   := rst.apellido;
                correo   := rst.correo;
                RETURN NEXT;
             END LOOP;
             RETURN;
         END CASE;   
    asunto   := rst.asunto;
    mensaje   := rst.mensaje;
    id   := rst.id;
    RETURN NEXT;
    END LOOP;
    RETURN;
end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;