CREATE FUNCTION fnasignar_subpaginas(ppadres_id varchar,pempresa_id integer,pestatus_contenido integer,only_dates boolean,only_muro boolean) RETURNS TEXT AS $$
DECLARE
   padres INT[];
   aux_padres INT[];
   length_padres INTEGER;
   pagina_empresa_id INTEGER;
   is_padre INTEGER;
   is_assigned INTEGER;
   orden_pag INTEGER;
   subpagina_empresa_id INTEGER;
   pagina_empresa certi_pagina_empresa%ROWTYPE;
   subpagina RECORD;
   tiene_prueba BOOLEAN;
   retorno TEXT :='';
   
   
   subpagina_empresa certi_pagina_empresa%ROWTYPE;
   pagina_padre certi_pagina_empresa%ROWTYPE;

BEGIN

     padres := ppadres_id::int[];
   pagina_empresa_id := padres[1];
   padres := '{}'::int[];
   
   RAISE NOTICE 'pagina empresa_id: (%)',pagina_empresa_id;
   RAISE NOTICE 'padre padres clear: (%)',padres;
   
   --obtener el registro donde se relaciona la pagina principal con la empresa para obtener las subpaginas
   SELECT * INTO pagina_empresa FROM certi_pagina_empresa AS cpe WHERE id = pagina_empresa_id;
   
   --preparar el primer arreglo de padres
   padres := padres||ARRAY[pagina_empresa.pagina_id];
   RAISE NOTICE 'Padre inicializado: (%)',padres;
   SELECT array_length(padres, 1) INTO length_padres;
   
   RAISE NOTICE 'Longitud de arreglo de padres: (%)',length_padres;
     WHILE length_padres > 0  LOOP
    RAISE NOTICE 'Entrando al while';
    FOR i IN 1..length_padres LOOP
            orden_pag:=0;
      IF retorno = '' THEN
        retorno:=padres[i];
      ELSE
        retorno:=retorno||'-'||padres[i];
      END IF;
      
      -- --se obtiener las subpaginas de una pagina 
            FOR subpagina IN 
                SELECT * FROM certi_pagina AS cp
                WHERE cp.pagina_id= padres[i] AND cp.estatus_contenido_id = pestatus_contenido
                ORDER BY cp.orden ASC
            LOOP
                orden_pag:=orden_pag+1;
                --verifica si la subpagina tiene hijos
                SELECT COUNT(cp.id) INTO is_padre FROM certi_pagina as cp
                WHERE cp.pagina_id = subpagina.id;
        --RAISE NOTICE 'hijos de la subpagina (%) (%) ',subpagina.id,is_padre;
        
              -- --se verifica si esas sub paginas estan asginadas 
              SELECT COUNT(cpe.id) INTO is_assigned FROM certi_pagina_empresa as cpe
              WHERE cpe.pagina_id = subpagina.id AND cpe.empresa_id = pagina_empresa.empresa_id;
        --RAISE NOTICE 'Esta asignada : (%)(%)',subpagina.id,is_assigned;
                
        --verifica si tiene prueba
              IF (SELECT COUNT(p.id) FROM certi_prueba AS p WHERE p.pagina_id = subpagina.id AND p.estatus_contenido_id = pestatus_contenido )>0 THEN
                tiene_prueba:=TRUE;
              ELSE
                tiene_prueba:=FALSE;
              END IF;

              IF is_assigned = 0 THEN
          RAISE NOTICE 'Asignando Pagina';
                INSERT INTO certi_pagina_empresa(
                  empresa_id,
                  pagina_id,
                  activo,
                  acceso,
                  fecha_inicio,
                  fecha_vencimiento,
                  prueba_activa,
                  max_intentos,
                  puntaje_aprueba,
                  muro_activo,
                  colaborativo,
                  orden)
                VALUES (
                  pagina_empresa.empresa_id,
                  subpagina.id,
                  pagina_empresa.activo,
                  pagina_empresa.acceso,
                  pagina_empresa.fecha_inicio,
                  pagina_empresa.fecha_vencimiento,
                  tiene_prueba,
                  pagina_empresa.max_intentos,
                  pagina_empresa.puntaje_aprueba,
                  pagina_empresa.muro_activo,
                  pagina_empresa.colaborativo,
                  orden_pag
                  ) RETURNING id INTO subpagina_empresa_id;

              ELSE
                    RAISE NOTICE 'La Pagina se encuentra asignada';
            SELECT cpe.id INTO subpagina_empresa_id FROM certi_pagina_empresa AS cpe
                    WHERE cpe.pagina_id = subpagina.id AND cpe.empresa_id = pagina_padre.empresa_id;
                      
                      IF only_dates = TRUE THEN
                        UPDATE certi_pagina_empresa AS cpe 
                        SET fecha_inicio = pagina_padre.fecha_inicio, fecha_vencimiento = pagina_padre.fecha_vencimiento
                        WHERE cpe.id = subpagina_empresa_id;
                      END IF;

                      IF only_muro = TRUE THEN
                         UPDATE certi_pagina_empresa AS cpe
                         SET muro_activo = pagina_padre.muro_activo
                         WHERE cpe.id = subpagina_empresa_id;
                      END IF;
                      
                      UPDATE certi_pagina_empresa AS cpe
                        SET orden = orden_pag
                        WHERE cpe.pagina_id = subpagina.id 
                        AND cpe.empresa_id = pagina_padre.empresa_id;
              END IF;

                IF is_padre>0 THEN 
           --actualizar la lista de padres para la proxima iteracion while
                   aux_padres:=aux_padres||ARRAY[subpagina.id];
                END IF;

            END LOOP;--for subpaginas

    END LOOP;--for padres 
    padres:='{}'::int[] ;
    padres:=padres||aux_padres;
    aux_padres:= '{}'::int[] ;
    SELECT array_length(padres, 1) INTO length_padres;
   END LOOP;--while
  --retorna un string con los ids de las paginas que tienen hijos
  RETURN retorno;
END;
$$ LANGUAGE plpgsql;