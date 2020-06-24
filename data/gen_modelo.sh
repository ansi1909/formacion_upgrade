#!/bin/sh

rm -f definition.sql
touch definition.sql
touch DEFINITION.SQL
dia2code -t sql -cl admin_rol DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_aplicacion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_permiso DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_pais DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_empresa DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_nivel DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_usuario DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_rol_usuario DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_sesion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_grupo DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_categoria DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_estatus_contenido DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_pagina DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_pagina_empresa DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_prueba DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_grupo_pagina DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_tipo_elemento DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_tipo_pregunta DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_pregunta DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_opcion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_pregunta_opcion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_pregunta_asociacion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_respuesta DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_estatus_pagina DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_pagina_log DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_prueba_log DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_tipo_noticia DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_noticia DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_muro DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_foro DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_tipo_notificacion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_notificacion DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl certi_nivel_pagina DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_layout DClases.dia; cat DEFINITION.SQL >> definition.sql
dia2code -t sql -cl admin_preferencia DClases.dia; cat DEFINITION.SQL >> definition.sql

rm -f DEFINITION.SQL