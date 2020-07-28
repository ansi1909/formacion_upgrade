CREATE VIEW view_pruebas as(
	SELECT
				pr.id,
				pr.nombre,
				pr.fecha_modificacion as modificacion,
				p.nombre as pagina,
				cc.nombre as estatus,
				(SELECT COUNT(id) FROM certi_pregunta  WHERE prueba_id = pr.id) as preguntas,
				(SELECT COUNT(id) FROM certi_opcion WHERE prueba_id = pr.id ) as opciones,
				(SELECT COUNT(id) FROM certi_prueba_log WHERE prueba_id = pr.id) as logs
	FROM certi_prueba pr
	INNER JOIN certi_pagina p ON p.id = pr.pagina_id
	INNER JOIN certi_estatus_contenido cc ON cc.id = p.estatus_contenido_id
	GROUP BY pr.id,pr.nombre,p.nombre,cc.nombre,pr.fecha_modificacion
	ORDER BY id ASC
);