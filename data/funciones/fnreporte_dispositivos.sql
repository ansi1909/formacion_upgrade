CREATE OR REPLACE FUNCTION "public"."fnconexiones_dispositivos"("pfechainicio" timestamp, "pfechafin" timestamp )
  RETURNS "pg_catalog"."json" AS $BODY$
DECLARE
  json_response text:='{"dispositivos":{';
  pc integer;
  tablet integer;
  smartphone integer;
  in_string integer;
  key varchar(255);
  c integer := 0;
  i integer;
  dispositivos_os text := '';
  disp_os record;
  dis_os integer;
BEGIN
        SELECT COUNT(ass.id) FROM admin_sesion ass WHERE ass.fecha_ingreso >= pfechainicio AND ass.fecha_ingreso <= pfechafin AND UPPER(ass.dispositivo) LIKE 'PC%'  INTO pc;
        SELECT COUNT(ass.id) FROM admin_sesion ass WHERE ass.fecha_ingreso >= pfechainicio AND ass.fecha_ingreso <= pfechafin AND UPPER(ass.dispositivo) LIKE 'SMARTPHONE%'  INTO smartphone;
        SELECT COUNT(ass.id) FROM admin_sesion ass WHERE ass.fecha_ingreso >= pfechainicio AND ass.fecha_ingreso <= pfechafin AND UPPER(ass.dispositivo) LIKE 'TABLET%'  INTO tablet;
        json_response := json_response ||'"PC":'||pc||','||'"TABLET":'||tablet||','||'"SMARTPHONE":'||smartphone||'},"dispositivos_original":{';
        ---dispositivos os
        FOR disp_os IN SELECT ass.dispositivo as dispositivo FROM admin_sesion ass  WHERE ass.dispositivo IS NOT NULL AND  ass.dispositivo <> '' AND ass.fecha_ingreso >= pfechainicio AND ass.fecha_ingreso <= pfechafin  ORDER BY ass.dispositivo ASC LOOP
            RAISE NOTICE 'dispositivo: %',disp_os.dispositivo;
            in_string := 0;
            SELECT POSITION(disp_os.dispositivo IN dispositivos_os) INTO in_string;
            IF in_string = 0 THEN
                c := c+ 1;
                dispositivos_os := dispositivos_os||disp_os.dispositivo||' |';
            END IF;
        END LOOP;
        RAISE NOTICE 'etiquetas: %',dispositivos_os;
        FOR i IN 1..c LOOP
            dis_os:=0;
            SELECT split_part(dispositivos_os,'|',i) INTO key;
            SELECT COUNT(ass.id) FROM admin_sesion ass WHERE ass.fecha_ingreso >= pfechainicio AND ass.fecha_ingreso <= pfechafin AND TRIM(UPPER(ass.dispositivo)) = TRIM(UPPER(key))  INTO dis_os;
            json_response := json_response||'"'||TRIM(UPPER(key))||'":'||dis_os;
            IF i <> c THEN
                json_response := json_response||',';
            END IF;
        END LOOP;
        json_response:=json_response||'}}';
        RETURN json_response::json;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE

  ------SELECT * from fnconexiones_dispositivos('2020-01-01 00:00:00','2020-09-23 23:59:59');
