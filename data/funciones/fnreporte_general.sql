-- Function: fnreporte_general(refcursor, integer)

-- DROP FUNCTION fnreporte_general(refcursor, integer);

CREATE OR REPLACE FUNCTION fnreporte_general(
    resultado refcursor,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$
   
begin


    -- Para el reporte 1
    OPEN resultado FOR 
       SELECT count(a.id) as logueado, 
               u.nombre as nombre, 
               u.apellido as apellido, 
               u.login as login, 
               u.correo_personal as correo,
               u.correo_corporativo as correo2,
               u.activo as activo,
               to_char(u.fecha_registro, 'DD/MM/YYYY HH:MI am') as fecha_registro, 
               to_char(u.fecha_nacimiento, 'DD/MM/YYYY') as fecha_nacimiento, 
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
        GROUP BY u.nombre,u.apellido,u.login,u.correo_personal,u.correo_corporativo,u.activo,fecha_registro,fecha_nacimiento,u.pais_id,n.nombre,u.campo1,u.campo2,u.campo3,u.campo4,u.id;

   
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE