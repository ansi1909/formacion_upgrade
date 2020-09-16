--obtiene la estructura de un programa en forma de cadena JSON valida para php
-- organiza las paginas por padre
CREATE OR REPLACE FUNCTION fnpaginas_login(ppempresa_id integer,ppnivel_id integer) RETURNS json AS $$
DECLARE
    json_respuesta text:='{';
    json_fila text:='{';
    json_modulo text;
    json_materia text;
    json_leccion text;
    paginas integer;
    modulos integer;
    materias integer;
    lecciones integer;
    cp integer:=0;
    cm integer;
    cma integer;
    cl integer;
    pr integer;
    inicio varchar(50);
    fin varchar(50);
    mf varchar(200);
    maf varchar(200);
    lf varchar (200);
    estructura json;
    elemento json;
    pagina record;
    modulo record;
    leccion record;
    i record;

BEGIN
    SELECT COUNT(cp.id)  FROM certi_nivel_pagina cnp
        INNER JOIN certi_pagina_empresa cpe ON cnp.pagina_empresa_id = cpe.id
        INNER JOIN certi_pagina cp ON cpe.pagina_id = cp.id
        INNER JOIN certi_categoria cc ON cp.categoria_id = cc.id
        WHERE cpe.empresa_id = 18
        AND cnp.nivel_id = 54
        AND cpe.activo
        AND cpe.fecha_inicio <= now()
    INTO paginas;

    ---- Ciclo para obtener paginas padre
    FOR pagina
    IN SELECT cp.id as id,
              cp.orden as orden,
              cp.nombre as pagina,
              cc.nombre as categoria,
              cp.foto as foto,
              cpe.prueba_activa as tiene_evaluacion,
              cpe.acceso as acceso,
              cpe.muro_activo as muro_activo,
              cpe.prelacion as prelacion,
              cpe.colaborativo as espacio_colaborativo,
              cpe.fecha_inicio as inicio,
              cpe.fecha_vencimiento as vencimiento
        FROM certi_nivel_pagina cnp
        INNER JOIN certi_pagina_empresa cpe ON cnp.pagina_empresa_id = cpe.id
        INNER JOIN certi_pagina cp ON cpe.pagina_id = cp.id
        INNER JOIN certi_categoria cc ON cp.categoria_id = cc.id
        WHERE cpe.empresa_id = 18
        AND cnp.nivel_id = 54
        AND cpe.activo
        AND cpe.fecha_inicio <= now()
        ORDER BY cp.orden  ASC
        LOOP
            cp:=cp+1;
            IF pagina.prelacion IS NULL THEN
                pr:=0;
            ELSE
                pr:=pagina.prelacion;
            END IF;
            json_fila:= json_fila||'"'||pagina.id||'" : {"id":'||pagina.id||','||'"orden":'||pagina.orden||','||'"nombre":"'||pagina.pagina||'",'||'"categoria":"'||pagina.categoria||'",'||'"foto":"'||pagina.foto||'",'||'"tiene_evaluacion":'||pagina.tiene_evaluacion||','||'"acceso":'||pagina.acceso||','||'"muro_activo":'||pagina.muro_activo||','||'"espacio_colaborativo":'||pagina.espacio_colaborativo||','||'"prelacion":'||pr||','||'"inicio":"'||to_char(pagina.inicio,'DD/MM/YYYY')||'",'||'"vencimiento":"'||to_char(pagina.vencimiento,'DD/MM/YYYY')||'"';
            ----Obtener subpaginas
            json_fila:=json_fila||','||'"subpaginas":{';
            ---Contar cuantos modulos tiene la pagina
                SELECT COUNT(cp.id)  FROM certi_pagina cp
                    INNER JOIN certi_pagina_empresa cpe ON cpe.pagina_id = cp.id
                    WHERE cp.pagina_id = pagina.id
                    AND cpe.empresa_id = 18
                    AND cpe.activo
                    AND cpe.fecha_inicio <= now()
                INTO modulos;
                json_modulo := '';
                cm:=0;
                IF modulos > 0 THEN
                    FOR modulo IN SELECT cp.id as id,
                              cp.orden as orden,
                              cp.nombre as pagina,
                              cc.nombre as categoria,
                              cp.foto as foto,
                              cpe.prueba_activa as tiene_evaluacion,
                              cpe.acceso as acceso,
                              cpe.muro_activo as muro_activo,
                              cpe.prelacion as prelacion,
                              cpe.colaborativo as espacio_colaborativo,
                              cpe.fecha_inicio as inicio,
                              cpe.fecha_vencimiento as vencimiento
                        FROM certi_pagina cp
                        INNER JOIN certi_pagina_empresa cpe ON cpe.pagina_id = cp.id
                        INNER JOIN certi_categoria cc ON cp.categoria_id = cc.id
                        WHERE cpe.empresa_id = 18
                        AND cpe.activo
                        AND cp.pagina_id = pagina.id
                        AND cpe.fecha_inicio <= now()
                        ORDER BY cp.orden  ASC
                        LOOP
                            RAISE NOTICE 'Modulo: %',modulo.pagina;
                            cm:=cm+1;
                            IF modulo.prelacion IS NULL THEN
                                pr:=0;
                            ELSE
                                pr:=modulo.prelacion;
                            END IF;
                            IF modulo.foto IS NULL THEN
                                mf:='';
                            ELSE
                                mf:=modulo.foto;
                            END IF;
                            json_modulo:= json_modulo||'"'||modulo.id||'" : {"id":'||modulo.id||','||'"orden":'||modulo.orden||','||'"nombre":"'||modulo.pagina||'",'||'"categoria":"'||modulo.categoria||'",'||'"foto":"'||mf||'",'||'"tiene_evaluacion":'||modulo.tiene_evaluacion||','||'"acceso":'||modulo.acceso||','||'"muro_activo":'||modulo.muro_activo||','||'"espacio_colaborativo":'||modulo.espacio_colaborativo||','||'"prelacion":'||pr||','||'"inicio":"'||to_char(modulo.inicio,'DD/MM/YYYY')||'",'||'"vencimiento":"'||to_char(modulo.vencimiento,'DD/MM/YYYY')||'"';
                            json_modulo:=json_modulo||','||'"subpaginas":{';
                            ---Contar cuantas materias tiene
                            SELECT COUNT(cp.id)  FROM certi_pagina cp
                                INNER JOIN certi_pagina_empresa cpe ON cpe.pagina_id = cp.id
                                WHERE cp.pagina_id = modulo.id
                                AND cpe.empresa_id = 18
                                AND cpe.activo
                                AND cpe.fecha_inicio <= now()
                            INTO materias;
                            json_materia :='';
                            cma:=0;
                            IF materias > 0 THEN
                                RAISE NOTICE 'Corriendo';

                            END IF;--if de materias
                            json_modulo:=json_modulo||'}';--subpaginas del modulo
                            json_modulo:=json_modulo||'}';--cerrando modulo

                            IF cm < modulos THEN
                                json_modulo:=json_modulo||',';
                            END IF;
                        END LOOP;---Loop modulos

                END IF;--if de modulos

            json_fila:=json_fila||json_modulo;
            --cierre de subpaginas padres
            json_fila:=json_fila||'}';
            --cierre de la fila de la pagina padre
            json_fila:=json_fila||'}';
            IF cp < paginas THEN
                json_fila:=json_fila||',';
            END IF;
        END LOOP;
        json_fila:=json_fila||'}'; --cierre del json principal
        RETURN json_fila::json;
END;
$$ LANGUAGE plpgsql;

--SELECT * from fnpaginas_login(18,54);