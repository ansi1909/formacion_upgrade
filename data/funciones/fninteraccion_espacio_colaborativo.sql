-- Function: fninteraccion_espacio_colaborativo(refcursor, integer, integer, integer, timestamp, timestamp)

-- DROP FUNCTION fninteraccion_espacio_colaborativo(refcursor, integer, integer, integer, timestamp, timestamp);

CREATE OR REPLACE FUNCTION fninteraccion_espacio_colaborativo(
    resultado refcursor,
    pempresa_id integer,
    ppagina_id integer,
    pforo_id integer,
    pdesde timestamp,
    phasta timestamp)
  RETURNS refcursor AS
$BODY$
   
begin

    OPEN resultado FOR 
    
    SELECT u.codigo AS codigo, u.login AS login, u.nombre AS nombre, u.apellido AS apellido, u.correo_personal AS correo_personal, 
  u.correo_corporativo AS correo_corporativo, e.nombre AS empresa, p.nombre AS pais, n.nombre AS nivel, 
  TO_CHAR(u.fecha_registro, 'DD/MM/YYYY') AS fecha_registro, u.campo1 AS campo1, u.campo2 AS campo2, u.campo3 AS campo3, u.campo4 AS campo4, 
  f.mensaje AS mensaje, f.fecha_registro AS fecha_mensaje 
    FROM certi_foro f INNER JOIN 
  (admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id 
       INNER JOIN 
    (admin_empresa e INNER JOIN admin_pais p ON e.pais_id = p.id) 
       ON u.empresa_id = e.id ) 
      ON f.usuario_id = u.id
      WHERE f.empresa_id = pempresa_id AND f.pagina_id = ppagina_id AND f.fecha_registro BETWEEN pdesde AND phasta
      AND f.foro_id = pforo_id
      AND u.login NOT LIKE 'temp%'
      ORDER BY u.login ASC, f.fecha_registro ASC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fninteraccion_espacio_colaborativo('re', 1, 83, 2, '2018-01-04 00:00:00', '2018-07-25 23:59:59') as resultado; fetch all from re;
 
  