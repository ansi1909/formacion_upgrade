CREATE FUNCTION fnasignar_subpaginas(ppadres_id varchar,pempresa_id integer,pestatus_contenido integer,only_dates boolean,only_muro boolean) RETURNS TEXT AS $$
DECLARE
   padres INT[];
   aux_padres INT[];
   tiene_prueba BOOLEAN;
   orden_pag INTEGER;
   is_padre INTEGER;
   length_padres INTEGER;
   subpagina_empresa_id INTEGER;
   subpagina RECORD;
   subpagina_empresa certi_pagina_empresa%ROWTYPE;
   pagina_padre certi_pagina_empresa%ROWTYPE;
   retorno TEXT :='';
BEGIN
  padres = ppadres_id::int[];
  SELECT array_length(padres, 1) INTO length_padres;
  WHILE length_padres > 0  LOOP
		FOR i IN 1..length_padres LOOP
			IF retorno = '' THEN
				retorno:=padres[i];
			ELSE
				retorno:=retorno||'-'||padres[i];
			END IF;
		    --se obtienen la pagina padre
		    SELECT * INTO pagina_padre FROM certi_pagina_empresa AS cpe
		    WHERE cpe.pagina_id = padres[i] AND cpe.empresa_id = pempresa_id;
			--se obtienen las subpaginas de una pagina padre
            FOR subpagina IN 
            	SELECT *
            	 FROM certi_pagina AS cp
            	 WHERE cp.pagina_id= padres[i] AND cp.estatus_contenido_id = pestatus_contenido
            	 ORDER BY cp.orden ASC
            LOOP
                --verifica si la subpagina tiene hijos
                SELECT COUNT(cp.id) INTO is_padre FROM certi_pagina as cp
                WHERE cp.pagina_id = subpagina.id;
            	--se verifica si esas sub paginas estan asginadas 
            	SELECT * INTO subpagina_empresa FROM certi_pagina_empresa as cpe
            	WHERE cpe.pagina_id = subpagina.pagina_id AND cpe.empresa_id = pagina_padre.empresa_id;
                --verifica si tiene prueba
            	IF (SELECT COUNT(p.id) FROM certi_prueba AS p WHERE p.pagina_id = subpagina.id AND p.estatus_contenido_id = pestatus_contenido )>0 THEN
            		tiene_prueba:=TRUE;
            	ELSE
            		tiene_prueba:=FALSE;
            	END IF;

            	IF subpagina_empresa IS NOT NULL THEN
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
            			pagina_padre.empresa_id,
            			subpagina.id,
            			pagina_padre.activo,
            			pagina_padre.acceso,
            			pagina_padre.fecha_inicio,
            			pagina_padre.fecha_vencimiento,
            			tiene_prueba,
            			pagina_padre.max_intentos,
            			pagina_padre.puntaje_aprueba,
            			pagina_padre.muro_activo,
            			pagina_padre.colaborativo,
            			orden_pag
            			) RETURNING id INTO subpagina_empresa_id;

            	ELSE
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