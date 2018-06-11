CREATE OR REPLACE FUNCTION fnimportar_participantes(IN path text) RETURNS void AS $$
BEGIN
  --EXECUTE format('COPY tmp_participante(codigo,login,nombre,apellido,fecha_registro,clave,correo_personal,competencia,pais_id,campo1,campo2,campo3,campo4,nivel_id,empresa_id,transaccion) FROM %L WITH DELIMITER ''|''', path);
  EXECUTE 'COPY tmp_participante(codigo,login,nombre,apellido,fecha_registro,clave,correo_personal,competencia,pais_id,campo1,campo2,campo3,campo4,nivel_id,empresa_id,transaccion) FROM '|| quote_nullable(path) ||' WITH DELIMITER ''|''';
END;
$$ LANGUAGE plpgsql;
-- select * from fnimportar_participantes('C:/wamp/www/uploads/recursos/participantes/nWYNgVgK.csv') as resultado;
