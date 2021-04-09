-- Function: fnavance_total_time(refcursor, timestamp, timestamp, integer)

-- DROP FUNCTION fnavance_total_time(refcursor, timestamp, timestamp, integer);

CREATE OR REPLACE FUNCTION fnavance_total_time(
    pfecha_inicio timestamp,
    pfecha_fin timestamp,
    pusuario_id integer)
  RETURNS text AS
$BODY$
declare
    str text;			-- Cadena para debug
    reg  record;		-- Se almacena la cantidad de conexiones y el promedio de conexión
begin
        SELECT INTO reg  sum(s.fecha_request - s.fecha_ingreso) AS promedio 
		FROM admin_sesion s
        WHERE s.fecha_ingreso between pfecha_inicio and pfecha_fin
        AND s.usuario_id = pusuario_id;
		--group by s.fecha_request,s.fecha_ingreso ;
		
		str = reg;

    RETURN str;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE
  
  --select * from fnavance_total_time('2020-10-12','2020-10-18 23:59:59','15466');