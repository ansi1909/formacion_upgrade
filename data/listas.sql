--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_rol_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol', 'id'), 6, false);

--
-- Data for Name: admin_rol; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_rol (id, nombre, descripcion) VALUES (1, 'Administrador', 'Puede acceder a todos los módulos del backend, puede crear más administradores, administrar las empresas que tendrán acceso al sistema, crear perfiles para estas empresas, configurar las páginas, entre otros.');
INSERT INTO admin_rol (id, nombre, descripcion) VALUES (2, 'Participante', 'Usado para los usuarios que verán los programas asociados a las empresas a las que pertenecen.');
INSERT INTO admin_rol (id, nombre, descripcion) VALUES (3, 'Tutor Virtual', 'Es un perfil más alto que el de un participante, puesto que permite el monitoreo de las actividades e interacciones de los participantes con los programas y sus evaluaciones. Además puede generar debates en cada programa asignado a la empresa.');
INSERT INTO admin_rol (id, nombre, descripcion) VALUES (4, 'Reporte', 'Con este rol el usuario puede visualizar cualquier reporte generado en el sistema filtrado por la empresa a la que pertenece desde la interfaz del backend.');
INSERT INTO admin_rol (id, nombre, descripcion) VALUES (5, 'Empresa', 'Permite la administración de los participantes, la creación de niveles de acceso a los contenidos de los programas, la generación de consultas relacionadas con la auditoria de las interacciones de los usuarios.');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_categoria_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_categoria', 'id'), 5, false);

--
-- Data for Name: certi_categoria; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_categoria (id, nombre) VALUES (1, 'Programa');
INSERT INTO certi_categoria (id, nombre) VALUES (2, 'Módulo');
INSERT INTO certi_categoria (id, nombre) VALUES (3, 'Materia');
INSERT INTO certi_categoria (id, nombre) VALUES (4, 'Recurso');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_estatus_contenido_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_estatus_contenido', 'id'), 4, false);

--
-- Data for Name: certi_estatus_contenido; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_estatus_contenido (id, nombre) VALUES (1, 'Borrador');
INSERT INTO certi_estatus_contenido (id, nombre) VALUES (2, 'Activo');
INSERT INTO certi_estatus_contenido (id, nombre) VALUES (3, 'Inactivo');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_tipo_elemento_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_tipo_elemento', 'id'), 3, false);

--
-- Data for Name: certi_tipo_elemento; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_tipo_elemento (id, nombre) VALUES (1, 'Texto');
INSERT INTO certi_tipo_elemento (id, nombre) VALUES (2, 'Imagen');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_tipo_pregunta_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_tipo_pregunta', 'id'), 4, false);

--
-- Data for Name: certi_tipo_pregunta; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_tipo_pregunta (id, nombre) VALUES (1, 'Selección simple');
INSERT INTO certi_tipo_pregunta (id, nombre) VALUES (2, 'Selección múltiple');
INSERT INTO certi_tipo_pregunta (id, nombre) VALUES (3, 'Asociacion');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_estatus_pagina_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_estatus_pagina', 'id'), 4, false);

--
-- Data for Name: certi_estatus_pagina; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_estatus_pagina (id, nombre) VALUES (1, 'Iniciada');
INSERT INTO certi_estatus_pagina (id, nombre) VALUES (2, 'En evaluación');
INSERT INTO certi_estatus_pagina (id, nombre) VALUES (3, 'Completada');

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_tipo_notificacion_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_notificacion', 'id'), 3, false);

--
-- Data for Name: admin_tipo_notificacion; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_tipo_notificacion (id, nombre) VALUES (1, 'Muro');
INSERT INTO admin_tipo_notificacion (id, nombre) VALUES (2, 'Foro');

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_aplicacion_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_aplicacion', 'id'), 23, false);

--
-- Data for Name: admin_aplicacion; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (1, 'Roles de usuarios', '_roles', NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (2, 'Usuarios', '_usuarios', NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (3, 'Aplicaciones', '_aplicaciones', 'fa-list-ul', TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (4, 'Configuración de permisos', '_permisos', NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (5, 'Configuración de páginas', NULL, NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (6, 'Categorías', '_categorias', NULL, TRUE, 5);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (7, 'Páginas', '_paginas', NULL, TRUE, 5);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (8, 'Configuración de evaluaciones', '_paginasEvaluacion', NULL, TRUE, 5);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (9, 'Configuración de empresas', NULL, NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (10, 'Empresas', '_empresas', NULL, TRUE, 9);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (11, 'Asignación de páginas', '_empresasPaginas', NULL, TRUE, 9);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (12, 'Niveles por empresa', '_empresasNiveles', NULL, TRUE, 9);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (13, 'Agrupación de páginas', '_empresasGrupo', NULL, TRUE, 9);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (14, 'Participantes', '_participantes', NULL, TRUE, 9);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (15, 'Mural', '_filtroMuro', NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (16, 'Foros', '_filtroForo', NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (17, 'Noticias y novedades', '_empresasNoticias', NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (18, 'Reportes y consultas', NULL, NULL, TRUE, NULL);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (19, 'Sesiones de usuarios', NULL, NULL, TRUE, 18);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (20, 'Usuarios sin ingresar', NULL, NULL, TRUE, 18);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (21, 'Estadisticas de las páginas', NULL, NULL, TRUE, 18);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id) VALUES (22, 'Auditoria de evaluaciones', NULL, NULL, TRUE, 18);
