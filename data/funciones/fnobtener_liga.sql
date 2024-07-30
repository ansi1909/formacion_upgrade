--Obtiene los usuario que pertenecen a una liga
CREATE OR REPLACE FUNCTION fnobtener_liga(ppagina_id integer, pempresa_id integer,ppuntaje_min integer, ppuntaje_max integer) RETURNS text AS $$
DECLARE
	json_respuesta text:='{';
    usuario record;
    foto varchar(255);
BEGIN
        FOR usuario IN

            --Tabla temporal para obtener la puntuacion de los usuarios que usare para el siguiente qyuery
            WITH certi_puntos as (
                SELECT au.id as usuario_id,cpl.puntos as  puntos
                FROM admin_usuario au 
                LEFT JOIN certi_pagina_log cpl ON cpl.usuario_id = au.id 
                INNER JOIN admin_nivel n ON au.nivel_id = n.id
                INNER JOIN certi_pagina_empresa pe ON pe.empresa_id = au.empresa_id
                INNER JOIN certi_nivel_pagina cnp ON pe.id = cnp.pagina_empresa_id
                WHERE cpl.pagina_id = ppagina_id
                AND  (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
                AND au.empresa_id = pempresa_id
                AND pe.pagina_id  = ppagina_id
                AND au.activo
                GROUP BY au.id, cpl.puntos
            )
            SELECT 
                    au.id as id,
                    au.nombre as nombre,
                    au.apellido as apellido,
                    au.foto as foto,
                    (CASE WHEN (SELECT cpu.puntos FROM certi_puntos cpu WHERE cpu.usuario_id  = au.id) > 0 
                        THEN (SELECT cpu.puntos FROM certi_puntos cpu WHERE cpu.usuario_id  = au.id)
                        ELSE 0 
                    END) as puntuacion
            FROM admin_usuario au 
            INNER JOIN admin_nivel n ON au.nivel_id = n.id
            INNER JOIN certi_pagina_empresa pe ON pe.empresa_id = au.empresa_id
            WHERE au.activo 
            AND  (LOWER(n.nombre) NOT LIKE 'revisor%' AND LOWER(n.nombre) NOT LIKE 'tutor%')
            AND au.empresa_id = pempresa_id
            AND pe.pagina_id  = ppagina_id
            AND (CASE WHEN (SELECT cpu.puntos FROM certi_puntos cpu WHERE cpu.usuario_id  = au.id) > 0 
                    THEN (SELECT cpu.puntos FROM certi_puntos cpu WHERE cpu.usuario_id  = au.id)
                    ELSE 0 
                    END
                ) BETWEEN ppuntaje_min AND ppuntaje_max
            GROUP BY au.id, au.nombre, au.apellido,au.foto, puntuacion 
            ORDER BY puntuacion DESC
            
        LOOP
                IF usuario.foto IS NULL THEN
                    foto:='';
                ELSE
                    foto:=usuario.foto;
                END IF;
                json_respuesta := json_respuesta||'"'||usuario.id||'":'||'{"nombre":'||'"'||usuario.nombre||'"'||',"apellido":'||'"'||usuario.apellido||'"'||',"foto":'||'"'||foto||'"'||',"puntos":'||'"'||usuario.puntuacion||'"'||'},';
                RAISE NOTICE 'Puntuacion: %',usuario.foto;
        END LOOP;
        json_respuesta := json_respuesta||'}';
        RAISE NOTICE 'Response: %',json_respuesta;

    RETURN json_respuesta;
END;
$$ LANGUAGE plpgsql;

--SELECT * from fnobtener_liga(4514,1,12504,16463,'{4514,4515,4516,4517,4523,4524,4525,4526,4527,4528,4519,4520,4521,4522}');