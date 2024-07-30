
CREATE OR REPLACE FUNCTION fnlistado_notificaciones_programadas(
    resultado refcursor,
    pnotificacion_id integer)
  RETURNS refcursor AS
$BODY$


BEGIN
    OPEN resultado FOR
    SELECT
          np.id as id,
          np.notificacion_id  as notificacion_id,
          np.tipo_destino_id as tipo_destino_id,
          np.entidad_id as entidad_id,
          np.usuario_id as usuario_id ,
          np.fecha_difusion as fecha_difusion ,
          np.grupo_id as grupo_id ,
          np.enviado as enviado ,
          np.fecha_inicio as fecha_inicio,
          np.fecha_fin as fecha_fin,
          td.nombre as destino,
          (SELECT COUNT(ac.id) FROM admin_correo ac WHERE (ac.entidad_id = np.id OR ac.entidad_id IN (SELECT id from      admin_notificacion_programada where grupo_id= np.id) )) as enviados
          FROM admin_notificacion_programada np
          INNER JOIN admin_tipo_destino td ON td.id = np.tipo_destino_id
          WHERE np.notificacion_id = pnotificacion_id
          AND np.grupo_id IS NULL;
    RETURN resultado;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE;

  --select * from fnlistado_notificaciones_programadas('re', 1088) as resultado; fetch all from re;