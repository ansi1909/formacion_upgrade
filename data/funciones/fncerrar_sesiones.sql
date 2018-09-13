-- Function: fncerrar_sesiones()

-- DROP FUNCTION fncerrar_sesiones();

CREATE OR REPLACE FUNCTION fncerrar_sesiones()
  RETURNS integer AS
$BODY$
declare
    i INTEGER := 0;          -- Contador de arr
    rst  record;             -- Cursor para el SELECT de la página
begin

    FOR rst IN 

  SELECT * FROM admin_sesion WHERE disponible = TRUE AND fecha_request IS NOT NULL AND fecha_request < (current_timestamp - interval '100 minutes') ORDER BY id ASC LOOP

  i = i+1;
  
  -- Actualizando disponible en false
  UPDATE admin_sesion SET disponible = FALSE WHERE id = rst.id;
         
    END LOOP;
   
    RETURN i;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fncerrar_sesiones() as resultado;
