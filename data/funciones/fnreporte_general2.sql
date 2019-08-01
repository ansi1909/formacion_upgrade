-- Function: fnreporte_general2(refcursor, integer)

-- DROP FUNCTION fnreporte_general2(refcursor, integer);

CREATE OR REPLACE FUNCTION fnreporte_general2(
    resultado refcursor,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$
   
begin

    OPEN resultado FOR 
       SELECT count(a.id) as logueado, 
               u.login as login
        FROM admin_usuario u INNER JOIN admin_nivel n ON u.nivel_id = n.id
        LEFT JOIN admin_sesion a ON u.id = a.usuario_id
        INNER JOIN admin_rol_usuario ru ON u.id = ru.usuario_id
        WHERE u.empresa_id = pempresa_id 
        AND u.login NOT LIKE 'temp%'
        AND ru.rol_id = 2
        GROUP BY u.login;
   
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE