DROP INDEX IF EXISTS sesion_ndx1;
CREATE INDEX sesion_ndx1 on admin_sesion (usuario_id);

DROP INDEX IF EXISTS nivel_ndx1;
CREATE INDEX nivel_ndx1 on admin_nivel (empresa_id);

DROP INDEX IF EXISTS grupo_ndx1;
CREATE INDEX grupo_ndx1 on certi_grupo (empresa_id);

DROP INDEX IF EXISTS pagina_empresa_ndx1;
CREATE INDEX pagina_empresa_ndx1 on certi_pagina_empresa (empresa_id, pagina_id);

DROP INDEX IF EXISTS prueba_ndx1;
CREATE INDEX prueba_ndx1 on certi_prueba (pagina_id);

DROP INDEX IF EXISTS pregunta_ndx1;
CREATE INDEX pregunta_ndx1 on certi_pregunta (prueba_id, tipo_elemento_id);

DROP INDEX IF EXISTS opcion_ndx1;
CREATE INDEX opcion_ndx1 on certi_opcion (prueba_id);

DROP INDEX IF EXISTS pregunta_opcion_ndx1;
CREATE INDEX pregunta_opcion_ndx1 on certi_pregunta_opcion (pregunta_id, opcion_id);

DROP INDEX IF EXISTS pregunta_asociacion_ndx1;
CREATE INDEX pregunta_asociacion_ndx1 on certi_pregunta_asociacion (pregunta_id);

DROP INDEX IF EXISTS prueba_log_ndx1;
CREATE INDEX prueba_log_ndx1 on certi_prueba_log (prueba_id, usuario_id);

DROP INDEX IF EXISTS pagina_log_ndx1;
CREATE INDEX pagina_log_ndx1 on certi_pagina_log (pagina_id, usuario_id);

DROP INDEX IF EXISTS noticia_ndx1;
CREATE INDEX noticia_ndx1 on admin_noticia (empresa_id);

DROP INDEX IF EXISTS muro_ndx1;
CREATE INDEX muro_ndx1 on certi_muro (pagina_id);

DROP INDEX IF EXISTS foro_ndx1;
CREATE INDEX foro_ndx1 on certi_foro (pagina_id);

DROP INDEX IF EXISTS preferencia_ndx1;
CREATE INDEX preferencia_ndx1 on admin_preferencia (empresa_id);

DROP INDEX IF EXISTS nivel_pagina_ndx1;
CREATE INDEX nivel_pagina_ndx1 on certi_nivel_pagina (nivel_id, pagina_empresa_id);

------ actualizacion : 12/10/2020
DROP INDEX IF EXISTS correo_ndx1;
CREATE INDEX correo_ndx1 on admin_correo (id,usuario_id);

DROP INDEX IF EXISTS pagina_ndx1;
CREATE INDEX pagina_ndx1 on certi_pagina(id,pagina_id,categoria_id);

DROP INDEX IF EXISTS correo_fallido_ndx1;
CREATE INDEX correo_fallido_ndx1 on admin_correo_fallido (id,usuario_id,entidad_id);

DROP INDEX IF EXISTS introduccion_ndx1;
CREATE INDEX introduccion_ndx1 on admin_introduccion (id,usuario_id);

DROP INDEX IF EXISTS notificacion_ndx1;
CREATE INDEX notificacion_ndx1 on admin_notificacion(id,usuario_id);

DROP INDEX IF EXISTS notificacion_programada_ndx1;
CREATE INDEX notificacion_programada_ndx1 on admin_notificacion_programada(id,notificacion_id,tipo_destino_id,entidad_id,usuario_id,grupo_id);

DROP INDEX IF EXISTS grupo_pagina_ndx1;
CREATE INDEX grupo_pagina_ndx1 on certi_grupo_pagina(grupo_id,pagina_id);

DROP INDEX IF EXISTS categoria_ndx1;
CREATE INDEX categoria_ndx1 on certi_categoria(id);

DROP INDEX IF EXISTS rol_usuario_ndx1;
CREATE INDEX rol_usuario_ndx1 on admin_rol_usuario(id,rol_id,usuario_id);

DROP INDEX IF EXISTS usuario_ndx1;
CREATE INDEX usuario_ndx1 on admin_usuario(id,pais_id,empresa_id,nivel_id);









