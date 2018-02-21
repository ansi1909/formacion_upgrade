CREATE OR REPLACE FUNCTION fnimportar_participantes(IN path text) RETURNS void AS $$
BEGIN
  EXECUTE format('COPY tmp_participante(codigo,login,nombre,apellido,fecha_registro,clave,correo_personal,competencia,pais_id,campo1,campo2,campo3,campo4,nivel_id,empresa_id,transaccion) FROM %L WITH DELIMITER ''|''', path);
END;
$$ LANGUAGE plpgsql;
-- select * from fnimportar_participantes('C:/wamp64/www/uploads/recursos/participantes/4cdm3FB.csv') as resultado;