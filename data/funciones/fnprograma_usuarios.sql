-- Function: fnprograma_usuarios(timestamp)

-- DROP FUNCTION fnprograma_usuarios(timestamp);

CREATE OR REPLACE FUNCTION fnprograma_usuarios()
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
             WHERE np.tipo_destino_id = 6 
                AND e.activo = true 
                AND np.grupo_id IS NULL 
                ORDER BY np.id ASC LOOP
      
        -- Buscando usuarios que no han ingresado a un programa
        FOR rst IN SELECT u.nombre as nombre, u.apellido as apellido, u.correo_corporativo as correo 
                   FROM admin_usuario u, certi_nivel_pagina cnp, certi_pagina_empresa cpe, certi_pagina cp 
                   WHERE cp.id = reg.entidad_id AND cp.estatus_contenido_id = 2 
                      AND cpe.activo = true 
                      AND cpe.empresa_id = reg.empresa_id 
                      AND cpe.pagina_id = cp.id 
                      AND cnp.pagina_empresa_id = cpe.id 
                      AND cnp.nivel_id = u.nivel_id 
                      AND u.activo = true  
                      AND NOT EXISTS (SELECT l FROM certi_pagina_log l 
                                               WHERE l.usuario_id = u.id 
                                               AND l.pagina_id = cp.id) 
                      ORDER BY u.id ASC LOOP
            str = rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje || '__' || reg.id;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

   END LOOP;

end;
$BODY$
 LANGUAGE 'plpgsql' VOLATILE;