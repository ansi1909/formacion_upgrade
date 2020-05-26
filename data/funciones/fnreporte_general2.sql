-- Function: fnreporte_general2(refcursor, integer)

-- DROP FUNCTION fnreporte_general2(refcursor, integer);

CREATE OR REPLACE FUNCTION fnreporte_general2(
    resultado refcursor,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$
   
begin

    OPEN resultado FOR 
        SELECT count(ass.usuario_id) as logueado,au.login as login
        FROM admin_usuario au 
        LEFT  JOIN admin_sesion ass ON au.id = ass.usuario_id
        INNER JOIN admin_nivel an ON au.nivel_id = an.id
        INNER JOIN admin_rol_usuario ru ON au.id = ru.usuario_id
        WHERE au.empresa_id = pempresa_id
        AND LOWER(an.nombre) NOT LIKE 'revisor%'
        AND ru.rol_id = 2
        GROUP BY au.id,au.login;
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE