-- Function: public.fnlistado_participantes_correos(refcursor, integer)

-- DROP FUNCTION public.fnlistado_participantes_correos( refcursor,integer);

CREATE OR REPLACE FUNCTION public.fnlistado_participantes_correos(
    resultado refcursor,
    pnotificacion_programada_id integer)
  RETURNS refcursor AS
$BODY$


begin
	OPEN resultado FOR
    SELECT c.entidad_id as codigo_np,
		   u.id as codigo,
		   u.nombre as nombre, 
		   u.apellido as apellido, 
		   u.login as login, 
		   u.correo_personal as correo1, 
		   u.correo_corporativo as correo2 FROM admin_correo c
	JOIN admin_usuario u ON u.id = c.usuario_id
    WHERE (entidad_id = pnotificacion_programada_id OR entidad_id IN 
    (SELECT id  from admin_notificacion_programada where grupo_id= pnotificacion_programada_id));

    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE;
  
  --select * from fnlistado_participantes_correos('re', 1088) as resultado; fetch all from re;