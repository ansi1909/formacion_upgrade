CREATE OR REPLACE FUNCTION "public"."fnpaginas_login"("ppempresa_id" int4, "ppnivel_id" int4, "ppfecha" timestamp)
  RETURNS "pg_catalog"."json" AS $BODY$
DECLARE
    --variables para almacenar las string con formato json
    json_response text:='{';
    json_modulo text;
    json_materia text;
    json_leccion text;
    --Total de programas, modulos, materias y lecciones
    paginas integer;
    modulos integer;
    materias integer;
    lecciones integer;
    -- contadores de programa, materias , cursos y lecciones
    cp integer:=0;
    cm integer;
    cma integer;
    cl integer;
    -- Variable para las prelaciones
    pr integer;
    --Variable para el ranking
    rk integer;
    --Variables para las fotos
    pf varchar(200);
    mf varchar(200);
    maf varchar(200);
    lf varchar (200);
    puntuacion_padre integer;
    pagina record;
    modulo record;
    materia record;
    leccion record;

BEGIN
    SELECT COUNT(cp.id)  FROM certi_nivel_pagina cnp
        INNER JOIN certi_pagina_empresa cpe ON cnp.pagina_empresa_id = cpe.id
        INNER JOIN certi_pagina cp ON cpe.pagina_id = cp.id
        INNER JOIN certi_categoria cc ON cp.categoria_id = cc.id
        WHERE cpe.empresa_id = ppempresa_id
        AND cnp.nivel_id = ppnivel_id
        AND cpe.activo
        AND cpe.fecha_inicio <= ppfecha
        AND cp.estatus_contenido_id = 2
    INTO paginas;

    ---- Ciclo para obtener paginas padre
    FOR pagina
    IN SELECT cp.id as id,
              cp.orden as orden,
              cp.nombre as pagina,
              cc.nombre as categoria,
              cc.pronombre as pronombre,
              cc.bienvenida as bienvenida,
              cc.tarjetas as tarjetas,
              cc.notas as notas,
              cp.foto as foto,
              cpe.prueba_activa as tiene_evaluacion,
              cpe.acceso as acceso,
              cpe.muro_activo as muro_activo,
              cpe.ranking as ranking,
              cpe.prelacion as prelacion,
              cpe.colaborativo as espacio_colaborativo,
              cpe.fecha_inicio as inicio,
              cpe.fecha_vencimiento as vencimiento,
              cp.puntuacion as puntuacion_padre
        FROM certi_nivel_pagina cnp
        INNER JOIN certi_pagina_empresa cpe ON cnp.pagina_empresa_id = cpe.id
        INNER JOIN certi_pagina cp ON cpe.pagina_id = cp.id
        INNER JOIN certi_categoria cc ON cp.categoria_id = cc.id
        WHERE cpe.empresa_id = ppempresa_id
        AND cnp.nivel_id = ppnivel_id
        AND cpe.activo
        AND cpe.fecha_inicio <= ppfecha
        AND cp.estatus_contenido_id = 2
        ORDER BY cpe.orden  ASC
        LOOP
            IF pagina.foto IS NULL THEN
                pf:='';
            ELSE
                pf:=pagina.foto;
            END IF;

            IF pagina.puntuacion_padre IS NULL THEN
                puntuacion_padre:= 0;
            ELSE
                puntuacion_padre:= pagina.puntuacion_padre;
            END IF;

            cp:=cp+1;
            IF pagina.prelacion IS NULL THEN
                pr:=0;
            ELSE
                pr:=pagina.prelacion;
            END IF;

            IF pagina.ranking THEN
                rk:= 1;
            ELSE
                rk:= 0;
            END IF;
            json_response:= json_response||'"'||pagina.id||'" : {"id":'||pagina.id||','||'"orden":'||pagina.orden||','||'"nombre":"'||pagina.pagina||'",'||'"categoria":"'||pagina.categoria||'",'||'"pronombre":"'||pagina.pronombre||'",'||'"binvenida":"'||pagina.bienvenida||'",'||'"notas":"'||pagina.notas||'",'||'"tarjetas":"'||pagina.tarjetas||'",'||'"foto":"'||pf||'",'||'"tiene_evaluacion":'||pagina.tiene_evaluacion||','||'"acceso":'||pagina.acceso||','||'"muro_activo":'||pagina.muro_activo||','||'"espacio_colaborativo":'||pagina.espacio_colaborativo||','||'"prelacion":'||pr||','||'"inicio":"'||to_char(pagina.inicio,'DD/MM/YYYY')||'",'||'"puntuacion":'||puntuacion_padre||','||'"vencimiento":"'||to_char(pagina.vencimiento,'DD/MM/YYYY')||'",'||'"ranking":"'||rk||'"';
            ----Obtener subpaginas
            json_response:=json_response||','||'"subpaginas":{';
            ---Contar cuantos modulos tiene la pagina
                SELECT COUNT(cp.id)  FROM certi_pagina cp
                    INNER JOIN certi_pagina_empresa cpe ON cpe.pagina_id = cp.id
                    WHERE cp.pagina_id = pagina.id
                    AND cpe.empresa_id = ppempresa_id
                    AND cpe.activo
                    AND cp.estatus_contenido_id = 2
                    AND cpe.fecha_inicio <= ppfecha
                INTO modulos;
                json_modulo := '';
                cm:=0;
                IF modulos > 0 THEN
                    FOR modulo IN SELECT cp.id as id,
                              cp.orden as orden,
                              cp.nombre as pagina,
                              cc.nombre as categoria,
                              cc.pronombre as pronombre,
                              cc.bienvenida as bienvenida,
                              cc.tarjetas as tarjetas,
                              cc.notas as notas,
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
                        WHERE cpe.empresa_id = ppempresa_id
                        AND cpe.activo
                        AND cp.pagina_id = pagina.id
                        AND cpe.fecha_inicio <= ppfecha
                        AND cp.estatus_contenido_id = 2
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
                            json_modulo:= json_modulo||'"'||modulo.id||'" : {"id":'||modulo.id||','||'"orden":'||modulo.orden||','||'"nombre":"'||modulo.pagina||'",'||'"categoria":"'||modulo.categoria||'",'||'"pronombre":"'||modulo.pronombre||'",'||'"bienvenida":"'||modulo.bienvenida||'",'||'"tarjetas":"'||modulo.tarjetas||'",'||'"notas":"'||modulo.notas||'",'||'"foto":"'||mf||'",'||'"tiene_evaluacion":'||modulo.tiene_evaluacion||','||'"acceso":'||modulo.acceso||','||'"muro_activo":'||modulo.muro_activo||','||'"espacio_colaborativo":'||modulo.espacio_colaborativo||','||'"prelacion":'||pr||','||'"inicio":"'||to_char(modulo.inicio,'DD/MM/YYYY')||'",'||'"vencimiento":"'||to_char(modulo.vencimiento,'DD/MM/YYYY')||'"';
                            json_modulo:=json_modulo||','||'"subpaginas":{';
                            ---Contar cuantas materias tiene
                            SELECT COUNT(cp.id)  FROM certi_pagina cp
                                INNER JOIN certi_pagina_empresa cpe ON cpe.pagina_id = cp.id
                                WHERE cp.pagina_id = modulo.id
                                AND cpe.empresa_id = ppempresa_id
                                AND cpe.activo
                                AND cpe.fecha_inicio <= ppfecha
                                AND cp.estatus_contenido_id = 2
                            INTO materias;
                            json_materia :='';
                            cma:=0;
                            IF materias > 0 THEN
                                FOR materia IN SELECT cp.id as id,
                                      cp.orden as orden,
                                      cp.nombre as pagina,
                                      cc.nombre as categoria,
                                      cc.pronombre as pronombre,
                                      cc.bienvenida as bienvenida,
                                      cc.tarjetas as tarjetas, 
                                      cc.notas as notas,
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
                                WHERE cpe.empresa_id = ppempresa_id
                                AND cpe.activo
                                AND cp.pagina_id = modulo.id
                                AND cpe.fecha_inicio <= ppfecha
                                AND cp.estatus_contenido_id = 2
                                ORDER BY cp.orden  ASC
                                LOOP
                                    cma:=cma+1;
                                    IF materia.prelacion IS NULL THEN
                                        pr:=0;
                                    ELSE
                                        pr:=materia.prelacion;
                                    END IF;
                                    IF materia.foto IS NULL THEN
                                        maf:='';
                                    ELSE
                                        maf:=materia.foto;
                                    END IF;
                                    json_materia:= json_materia||'"'||materia.id||'" : {"id":'||materia.id||','||'"orden":'||materia.orden||','||'"nombre":"'||materia.pagina||'",'||'"categoria":"'||materia.categoria||'",'||'"pronombre":"'||materia.pronombre||'",'||'"notas":"'||materia.notas||'",'||'"bienvenida":"'||materia.bienvenida||'",'||'"tarjetas":"'||materia.tarjetas||'",'||'"foto":"'||maf||'",'||'"tiene_evaluacion":'||materia.tiene_evaluacion||','||'"acceso":'||materia.acceso||','||'"muro_activo":'||materia.muro_activo||','||'"espacio_colaborativo":'||materia.espacio_colaborativo||','||'"prelacion":'||pr||','||'"inicio":"'||to_char(materia.inicio,'DD/MM/YYYY')||'",'||'"vencimiento":"'||to_char(materia.vencimiento,'DD/MM/YYYY')||'"';
                                   --abrir lecciones
                                   json_materia:=json_materia||','||'"subpaginas":{';
                                   --Obtener lecciones
                                        SELECT COUNT(cp.id)  FROM certi_pagina cp
                                        INNER JOIN certi_pagina_empresa cpe ON cpe.pagina_id = cp.id
                                        WHERE cp.pagina_id = materia.id
                                        AND cpe.empresa_id = ppempresa_id
                                        AND cpe.activo
                                        AND cpe.fecha_inicio <= ppfecha
                                        AND cp.estatus_contenido_id = 2
                                        INTO lecciones;
                                    json_leccion :='';
                                    cl:=0;
                                    IF lecciones > 0 THEN
                                            FOR leccion IN SELECT cp.id as id,
                                              cp.orden as orden,
                                              cp.nombre as pagina,
                                              cc.nombre as categoria,
                                              cc.pronombre as pronombre,
                                              cc.bienvenida as bienvenida,
                                              cc.tarjetas as tarjetas,
                                              cc.notas as notas,
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
                                        WHERE cpe.empresa_id = ppempresa_id
                                        AND cpe.activo
                                        AND cp.pagina_id = materia.id
                                        AND cpe.fecha_inicio <= ppfecha
                                        AND cp.estatus_contenido_id = 2
                                        ORDER BY cp.orden  ASC
                                        LOOP
                                        cl:=cl+1;
                                        IF leccion.prelacion IS NULL THEN
                                            pr:=0;
                                        ELSE
                                            pr:=leccion.prelacion;
                                        END IF;
                                        IF leccion.foto IS NULL THEN
                                            lf:='';
                                        ELSE
                                            lf:=leccion.foto;
                                        END IF;
                                        json_leccion:= json_leccion||'"'||leccion.id||'" : {"id":'||leccion.id||','||'"orden":'||leccion.orden||','||'"nombre":"'||leccion.pagina||'",'||'"categoria":"'||leccion.categoria||'",'||'"pronombre":"'||leccion.pronombre||'",'||'"bienvenida":"'||leccion.bienvenida||'",'||'"tarjetas":"'||leccion.tarjetas||'",'||'"notas":"'||leccion.notas||'",'||'"foto":"'||lf||'",'||'"tiene_evaluacion":'||leccion.tiene_evaluacion||','||'"acceso":'||leccion.acceso||','||'"muro_activo":'||leccion.muro_activo||','||'"espacio_colaborativo":'||leccion.espacio_colaborativo||','||'"prelacion":'||pr||','||'"inicio":"'||to_char(leccion.inicio,'DD/MM/YYYY')||'",'||'"vencimiento":"'||to_char(leccion.vencimiento,'DD/MM/YYYY')||'","subpaginas":{}}';
                                        IF cl < lecciones THEN
                                            json_leccion:=json_leccion||',';
                                        END IF;
                                        END LOOP; --Loop de lecciones

                                    END IF;---If de lecciones
                                    json_materia:=json_materia||json_leccion;
                                    json_materia:=json_materia||'}';--subpaginas de la materia
                                    json_materia:=json_materia||'}';--cerrando la materia
                                    IF cma < materias THEN
                                        json_materia:=json_materia||',';
                                    END IF;

                                END LOOP;--Loop de materias
                            END IF;--If de materias
                            json_modulo:=json_modulo||json_materia;
                            json_modulo:=json_modulo||'}';--subpaginas del modulo
                            json_modulo:=json_modulo||'}';--cerrando modulo

                            IF cm < modulos THEN
                                json_modulo:=json_modulo||',';
                            END IF;
                        END LOOP;---Loop modulos

                END IF;--if de modulos

            json_response:=json_response||json_modulo;
            --cierre de subpaginas padres
            json_response:=json_response||'}';
            --cierre de la fila de la pagina padre
            json_response:=json_response||'}';
            IF cp < paginas THEN
                json_response:=json_response||',';
            END IF;
        END LOOP;
        json_response:=json_response||'}'; --cierre del json principal
        RETURN json_response::json;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE

  ------SELECT * from fnpaginas_login(1,265,'2021-04-28');
