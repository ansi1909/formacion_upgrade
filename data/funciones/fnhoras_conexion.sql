-- Function: fnhoras_conexion(integer, timestamp, timestamp, time, time)

-- DROP FUNCTION fnhoras_conexion(integer, timestamp, timestamp, time, time);

CREATE OR REPLACE FUNCTION fnhoras_conexion(pempresa_id integer,
    pdesde timestamp,
    phasta timestamp,
    phora1 time,
    phora2 time)
  RETURNS text AS
$BODY$
declare
    i integer;      -- Iterador de los días de la semana (0 = Domingo)
    str text;           -- Cadena para debug
    c integer;          -- Cantidad de registros del query
begin

    FOR i IN 0..6 LOOP

    SELECT COUNT(s.id) INTO c FROM admin_sesion s INNER JOIN admin_usuario u ON s.usuario_id = u.id 
            INNER JOIN admin_nivel an ON an.id = u.nivel_id
            WHERE u.empresa_id = pempresa_id 
            AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2) 
            AND CAST(fecha_ingreso AS TIME) BETWEEN phora1 and phora2 
            AND fecha_ingreso BETWEEN pdesde AND phasta 
            AND LOWER(an.nombre) NOT LIKE 'revisor%'
            AND date_part('dow', fecha_ingreso) = i;

    If i = 0 Then 
      str = c;
    Else 
      str = str || '__' || c;
    End If;

        raise notice 'Return: %', str;
         
    END LOOP;
   
    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnhoras_conexion(2, '2018-05-10 00:00:00', '2018-05-30 23:59:59', '08:00:00', '08:59:59');