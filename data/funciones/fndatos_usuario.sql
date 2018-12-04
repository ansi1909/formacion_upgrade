CREATE OR REPLACE FUNCTION fndatos_usuario(
    resultado refcursor,
	pusername varchar(100),
  pempresaid integer)
   RETURNS refcursor AS
$BODY$

 
begin
      OPEN resultado FOR 

      SELECT  
      au.id as id_usuario ,
      au.nombre as usuario_nombre,
      au.apellido as apellido,
      au.login as username,
      au.correo_corporativo as correo_corporativo,
      au.correo_personal as correo_personal,
      au.activo as status,
      au.foto as foto_perfil,
      ae.nombre as empresa_nombre,
      an.nombre as nivel_nombre,
      an.id as id_nivel,
      ( SELECT MIN(admin_sesion.fecha_ingreso) FROM admin_sesion WHERE admin_sesion.usuario_id = au.id) as fecha_primer_acceso,
      ( SELECT MAX(admin_sesion.fecha_ingreso) FROM admin_sesion WHERE admin_sesion.usuario_id = au.id) as fecha_ultimo_acceso,
      ( SELECT COUNT(admin_sesion.usuario_id)  FROM admin_sesion WHERE admin_sesion.usuario_id = au.id) as cantidad_accesos,
      ( SELECT CAST (SUM(EXTRACT(minutes from s.fecha_request - s.fecha_ingreso)) as bigint) * interval '1 min'  FROM admin_sesion as s WHERE s.usuario_id = au.id)AS promedio_conexiones,
      ( SELECT COUNT(cpl.usuario_id) FROM certi_pagina_log as cpl INNER JOIN certi_pagina as cp ON cpl.pagina_id = cp.id WHERE cpl.usuario_id = au.id AND cp.categoria_id = 1 AND cpl.estatus_pagina_id = 1  )AS programas_iniciados,
      
      ( SELECT COUNT(cpl.usuario_id) FROM certi_pagina_log as cpl INNER JOIN certi_pagina as cp ON cpl.pagina_id = cp.id WHERE cpl.usuario_id = au.id AND cp.categoria_id = 1 AND cpl.estatus_pagina_id = 3  )AS programas_culminados,
      ( 
        SELECT (COUNT(cp.id) -  ( SELECT COUNT(cpl.usuario_id) FROM certi_pagina_log as cpl INNER JOIN certi_pagina as cp ON cpl.pagina_id = cp.id WHERE cpl.usuario_id = au.id AND cp.categoria_id = 1 AND cpl.estatus_pagina_id = 3  ) - ( SELECT COUNT(cpl.usuario_id) FROM certi_pagina_log as cpl INNER JOIN certi_pagina as cp ON cpl.pagina_id = cp.id WHERE cpl.usuario_id = au.id AND cp.categoria_id = 1 AND cpl.estatus_pagina_id = 1  ) )
        FROM certi_nivel_pagina as cnp 
        INNER JOIN certi_pagina_empresa as cpe ON  cnp.pagina_empresa_id = cpe.id 
        INNER JOIN certi_pagina as cp ON cpe.pagina_id = cp.id
        WHERE cp.pagina_id IS NULL AND cnp.nivel_id = au.nivel_id AND cpe.empresa_id = au.empresa_id

      )AS programas_sin_iniciar
      

    FROM admin_usuario as au
    INNER JOIN admin_empresa as ae ON au.empresa_id = ae.id
    INNER JOIN admin_nivel an ON au.nivel_id = an.id 
    WHERE au.login = pusername AND au.empresa_id = pempresaid;

    RETURN resultado;
    

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fndatos_usuario('re','yavila',2) as resultado; fetch all from re;
