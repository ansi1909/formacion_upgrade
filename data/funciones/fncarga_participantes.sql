-- Function: fncarga_participantes(integer, text)

-- DROP FUNCTION fncarga_participantes(integer, text);

CREATE OR REPLACE FUNCTION fncarga_participantes(
    pempresa_id integer,
    ptransaccion text)
  RETURNS text AS
$BODY$
declare
    arr text[];                 -- Arreglo con toda la estructura de la página y sub-páginas
    u INTEGER := 0;             -- Contador de existencia de registros de usuarios y de roles
    rolid INTEGER := 2;         -- Rol de participante
    insertados INTEGER := 0;    -- Contador de registros insertados
    actualizados INTEGER := 0;  -- Contador de registros actualizados
    str text;                   -- Cadena para debug
    rst  record;                -- Cursor para el SELECT de tmp_participante
    usuarioid integer;          -- Nuevo ID retornado del INSERT de admin_usuario o del que se actualiza
begin

    FOR rst IN 

         SELECT * FROM tmp_participante WHERE empresa_id = pempresa_id AND transaccion = ptransaccion ORDER BY id ASC LOOP
         
         SELECT COUNT(id) INTO u FROM admin_usuario WHERE login = rst.login and empresa_id = pempresa_id;
         
     -- Si existe el login para esta empresa, se actualiza, sino se inserta
     If u > 0 Then

         actualizados = actualizados+1;

         UPDATE admin_usuario SET 
             clave = rst.clave, 
             nombre = rst.nombre, 
             apellido = rst.apellido, 
             correo_personal = rst.correo_personal, 
             pais_id = rst.pais_id, 
             campo1 = rst.campo1, 
             campo2 = rst.campo2, 
             campo3 = rst.campo3, 
             campo4 = rst.campo4, 
             nivel_id = rst.nivel_id, 
             competencia = rst.competencia, 
             codigo = rst.codigo, 
             fecha_modificacion = rst.fecha_registro
         WHERE empresa_id = pempresa_id AND login = rst.login;

         SELECT id INTO usuarioid FROM admin_usuario WHERE empresa_id = pempresa_id AND login = rst.login LIMIT 1;
         
         Else
         
             insertados = insertados+1;

             INSERT INTO admin_usuario(login, 
                       clave, 
                       nombre, 
                       apellido, 
                       correo_personal, 
                       activo, 
                       fecha_registro, 
                       pais_id, 
                       campo1, 
                       campo2, 
                       campo3, 
                       campo4, 
                       empresa_id, 
                       nivel_id, 
                       competencia, 
                       codigo, 
                       fecha_modificacion) 
                  VALUES 
                                      (rst.login, 
                                       rst.clave, 
                                       rst.nombre, 
                                       rst.apellido, 
                                       rst.correo_personal, 
                                       true, 
                                       rst.fecha_registro, 
                                       rst.pais_id, 
                                       rst.campo1, 
                                       rst.campo2, 
                                       rst.campo3, 
                                       rst.campo4,
                                       pempresa_id,
                                       rst.nivel_id,
                                       rst.competencia,
                                       rst.codigo,
                                       rst.fecha_registro) 
             RETURNING id INTO usuarioid;
             raise notice 'NEWID: %', usuarioid;
         
         End If;

         -- Rol de participante
         SELECT COUNT(id) INTO u FROM admin_rol_usuario WHERE rol_id = rolid and usuario_id = usuarioid;

         If u < 1 Then
         INSERT INTO admin_rol_usuario(rol_id, usuario_id) VALUES (rolid, usuarioid);
         End If;
         
    END LOOP;

    -- Limpiar la tabla tmp_particpante
    DELETE FROM tmp_participante WHERE empresa_id = pempresa_id AND transaccion = ptransaccion;
   
    -- Retorna la cantidad de registros insertados más los actualizados
    str = insertados || '__' || actualizados;
    RETURN str;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION fncarga_participantes(integer, text)
  OWNER TO postgres;
