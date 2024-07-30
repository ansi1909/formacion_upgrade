--- Procedimiento para eliminar una prueba de la base de datos
--- No retorna nada
CREATE OR REPLACE FUNCTION fneliminar_prueba(pprueba_id integer) RETURNS VOID AS $$
DECLARE
   pregunta record;
BEGIN
    FOR pregunta IN SELECT * FROM certi_pregunta WHERE prueba_id = pprueba_id  LOOP
        delete from certi_pregunta_asociacion where pregunta_id = pregunta.id;
        delete from certi_respuesta where pregunta_id = pregunta.id;
        delete from certi_pregunta_opcion where pregunta_id = pregunta.id;
    END LOOP;
    delete from certi_prueba_log where prueba_id = pprueba_id;
    delete from certi_pregunta where prueba_id = pprueba_id;
    delete from certi_opcion where prueba_id = pprueba_id;
    delete from certi_prueba where id = pprueba_id;
END;
$$ LANGUAGE plpgsql;

--SELECT * from fneliminar_prueba(1);