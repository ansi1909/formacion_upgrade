-- Function: fnlistado_certificados(refcursor, integer, integer, date, date)

-- DROP FUNCTION fnlistado_certificados(refcursor, integer, integer, date, date);

CREATE OR REPLACE FUNCTION fnlistado_certificados(
    resultado refcursor,
    pempresa_id integer,
    ppagina_id integer,
    pinicio date,
    pfin date)
  RETURNS refcursor AS
$BODY$

begin
OPEN resultado FOR
    SELECT count(a.id) as logueado,
                    u.codigo as codigo,
                    u.nombre as nombre,
                    u.apellido as apellido,
                    u.login as login,
                    u.correo_personal as correo,
                    u.correo_corporativo as correo2,
                    u.activo as activo,
                    u.fecha_registro as fecha_registro,
                    u.fecha_nacimiento as fecha_nacimiento,
                    u.pais_id as pais,
                    n.nombre as nivel,
                    u.campo1 as campo1,
                    u.campo2 as campo2,
                    u.campo3 as campo3,
                    u.campo4 as campo4,
                    u.id as id
					FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id
                LEFT JOIN admin_sesion a ON u.id = a.usuario_id
                WHERE u.empresa_id = pempresa_id
                    AND u.id IN
                        (SELECT pl.usuario_id FROM certi_pagina_log pl
                            WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3 AND fecha_fin BETWEEN pinicio AND pfin )
                    AND (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
                    AND u.id IN (SELECT ru.usuario_id FROM admin_rol_usuario ru WHERE ru.rol_id = 2)
                GROUP BY u.id,u.codigo,u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,u.fecha_registro,u.fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4
                ORDER BY u.nombre ASC;
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

  
