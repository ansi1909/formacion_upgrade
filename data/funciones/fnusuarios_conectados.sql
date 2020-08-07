-- Function: fnusuarios_conectados(refcursor, integer, integer)

-- DROP FUNCTION fnusuarios_conectados(refcursor, integer, integer);


CREATE OR REPLACE FUNCTION fnusuarios_conectados(
    resultado refcursor,
    pempresa_id integer,
    pusuario_id integer
)
  RETURNS refcursor AS
$BODY$

begin

    OPEN resultado FOR
		SELECT
			  au.nombre AS nombre,
			  au.apellido AS apellido,
			  au.login AS login,
			  au.correo_corporativo AS correo_corporativo,
			  au.correo_personal AS correo_personal,
			  an.nombre  AS nivel
		FROM
			  admin_usuario au
		INNER JOIN
			  admin_sesion ass
		ON au.id = ass.usuario_id
		INNER JOIN
			  admin_nivel an
		ON an.id = au.nivel_id
		WHERE
			  ass.disponible IS TRUE
		AND
			  au.empresa_id = pempresa_id
	    AND
	         ( LOWER(an.nombre) NOT LIKE 'revisor%' AND LOWER(an.nombre) NOT LIKE 'tutor%' )
		AND
			  ass.usuario_id <> pusuario_id
		GROUP BY au.login, au.nombre, au.apellido, an.nombre, au.correo_corporativo,au.correo_personal
		ORDER BY au.login DESC;

    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE


   --select * from fnusuarios_conectados('re', 2, 24) as resultado; fetch all from re;