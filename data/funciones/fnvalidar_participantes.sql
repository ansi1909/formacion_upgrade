-- Function: fnvalidar_participantes(integer, text)

-- DROP FUNCTION fnvalidar_participantes(integer, text);

CREATE OR REPLACE FUNCTION fnvalidar_participantes(
    pempresa_id integer,
    ptransaccion text)
  RETURNS text AS
$BODY$
declare
    arr text[];                 -- Arreglo con toda la estructura de la página y sub-páginas
    u INTEGER := 0;             -- Contador de existencia de registros de usuarios y de roles
    activost INTEGER := 0;    -- Contador de registros insertados
    str text;                   -- Cadena para debug
    reg  record;
    rst  record;                -- Cursor para el SELECT de tmp_participante
    ut INTEGER := 0;
begin

    FOR rst IN 

        SELECT * FROM tmp_participante WHERE empresa_id = pempresa_id AND transaccion = ptransaccion ORDER BY id ASC LOOP
         
        SELECT COUNT(id) INTO u FROM admin_usuario WHERE login = rst.login and empresa_id = pempresa_id;
         
        -- Si existe el login para esta empresa, se actualiza, sino se inserta
        If u > 0 Then

            SELECT * INTO reg FROM admin_usuario WHERE login = rst.login and empresa_id = pempresa_id;

            if rst.activo = true and reg.activo = false then
                activost = activost+1;
            elsIF rst.activo =false AND reg.activo = true then
                activost = activost-1;
             End If;
        Else
             
            activost = activost+1;

            
        End If;
         
    END LOOP;

    SELECT COUNT(u.id) FROM Admin_Rol_Usuario ru
                                       INNER JOIN admin_usuario u on ru.usuario_id = u.id
                                          INNER join admin_nivel n on u.nivel_id = n.id
                                        WHERE u.empresa_id = 36
                                        AND  u.activo = true
                                        AND ru.rol_id = 2
                                        AND n.nombre not like 'revisor%'
                                        AND n.nombre not like 'tutor%'

    activost = activost + ut;

    -- Limpiar la tabla tmp_particpante
    DELETE FROM tmp_participante WHERE empresa_id = pempresa_id AND transaccion = ptransaccion;
   
    -- Retorna la cantidad de registros insertados más los actualizados
    str = activost;
    RETURN str;

end;
$BODY$
  LANGUAGE "plpgsql" VOLATILE;
