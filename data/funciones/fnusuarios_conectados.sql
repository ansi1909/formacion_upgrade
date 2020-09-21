-- Function: fnusuarios_conectados(refcursor, integer, integer)

-- DROP FUNCTION fnusuarios_conectados(refcursor, integer, integer);


CREATE OR REPLACE FUNCTION fnusuarios_conectados(
    resultado refcursor,
    pempresa_id integer,
    pusuario_id integer
)
  RETURNS refcursor AS $$
  DECLARE
   query text;
   admin integer;
  DECLARE
BEGIN
     SELECT COUNT (ar.id) INTO admin FROM admin_rol_usuario as ar WHERE ar.usuario_id = pusuario_id AND rol_id = 1;
	 query :='
	 			SELECT
		 			au.nombre AS nombre,
		 			au.apellido AS apellido,
		 			au.login AS login,
		 			au.correo_corporativo AS correo_corporativo,
		 			au.correo_personal AS correo_personal,
		 			ass.dispositivo AS dispositivo';

		 		IF admin > 0 THEN
		 			query:= query||',ae.nombre as empresa';
		 		END IF;

	 			query:=query||' FROM   admin_usuario au
	 			INNER JOIN
	 				admin_sesion ass
	 			ON au.id = ass.usuario_id
	 			INNER JOIN
	 				admin_nivel an
	 				ON an.id = au.nivel_id ';

				IF admin > 0 THEN
					query:= query||' INNER JOIN admin_empresa ae ON ae.id = au.empresa_id ';
				END IF;

				query := query||'WHERE ass.disponible IS TRUE
			     AND
			  		ass.usuario_id <> '||pusuario_id||'
			  	AND LOWER(an.nombre) NOT LIKE '||'''revisor%'''||'
			  	AND LOWER (an.nombre) NOT LIKE '||'''tutor%'''||'
	 			';

	 			IF admin = 0 THEN
	 				query:=  query ||' AND au.empresa_id = '||pempresa_id||' ';
	 			END IF;

	 			query := query||'GROUP BY au.login, au.nombre, au.apellido, an.nombre, au.correo_corporativo,au.correo_personal,ass.dispositivo';
	 			IF admin > 0 THEN
	 				query := query ||',ae.nombre ';
	 			END IF;

				query := query||' ORDER BY au.login DESC';
    OPEN resultado FOR
     EXECUTE query;
    RETURN resultado;
END;
$$ LANGUAGE plpgsql;


   --select * from fnusuarios_conectados('re', 2, 24) as resultado; fetch all from re;