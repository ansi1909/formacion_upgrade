-- Function: fnconexion_usuario(refcursor, integer, timestamp, timestamp)

-- DROP FUNCTION fnconexion_usuario(refcursor, integer, timestamp, timestamp);

CREATE OR REPLACE FUNCTION fnconexion_usuario(
    resultado refcursor,
    pempresa_id integer,
    pdesde timestamp,
    phasta timestamp)
  RETURNS refcursor AS
$BODY$

begin

    OPEN resultado FOR

    SELECT u.id AS id, u.codigo AS codigo, u.login AS login, u.nombre AS nombre, u.apellido AS apellido, u.activo AS activo, u.correo_personal AS correo_personal,
        u.correo_corporativo AS correo_corporativo, e.nombre AS empresa, c.nombre AS pais, n.nombre AS nivel,
        u.fecha_registro AS fecha_registro, u.campo1 AS campo1, u.campo2 AS campo2, u.campo3 AS campo3, u.campo4 AS campo4,
    CAST (SUM(EXTRACT(minutes from s.fecha_request - s.fecha_ingreso)) as bigint) * interval '1 min' AS promedio, COUNT(s.usuario_id) AS visitas
    FROM admin_sesion s INNER JOIN
    (admin_usuario u INNER JOIN (admin_empresa e INNER JOIN admin_pais c ON e.pais_id = c.id) ON u.empresa_id = e.id
             INNER JOIN admin_nivel n ON u.nivel_id = n.id)
     ON s.usuario_id = u.id
    WHERE s.disponible = false
    AND u.empresa_id = pempresa_id
    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
    AND s.fecha_ingreso BETWEEN pdesde AND phasta
    AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
    AND u.activo
    GROUP BY u.id, e.nombre, c.nombre, n.nombre, u.codigo ,u.login, u.nombre , u.apellido, u.correo_personal,
             u.correo_corporativo, u.fecha_registro, u.campo1, u.campo2 , u.campo3, u.campo4
    ORDER BY u.login ASC;

    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  --select * from fnconexion_usuario('re', 2, '2018-04-04 00:00:00', '2018-05-24 23:59:59') as resultado; fetch all from re;
  --select * from fnconexion_usuario('re', 2, '2018-01-04 00:00:00', '2018-07-24 23:59:59') as resultado; fetch all from re;
