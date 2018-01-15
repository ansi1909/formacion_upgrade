--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_usuario_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_usuario', 'id'), 2, false);

--
-- Data for Name: admin_usuario; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_usuario (id, login, clave, nombre, apellido, correo_personal, correo_corporativo, activo, fecha_registro, fecha_nacimiento, pais_id, ciudad, region, empresa_id, foto, division_funcional, cargo, nivel_id) VALUES (1, 'admin', 'admin', 'Administrador', 'Sistema', 'soporte_link_gerencial@gmail.com', NULL, TRUE, '2017-09-21 10:28:00', '1981-01-29', 'VEN', 'Caracas', 'Capital', NULL, NULL, NULL, NULL, NULL);

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_permiso_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_permiso', 'id'), 22, false);

--
-- Data for Name: admin_permiso; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (1, 1, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (2, 3, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (3, 10, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (4, 9, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (5, 5, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (6, 6, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (7, 4, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (8, 2, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (9, 12, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (10, 7, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (11, 14, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (12, 8, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (13, 23, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (14, 24, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (15, 25, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (16, 26, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (17, 27, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (18, 17, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (19, 16, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (20, 28, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (21, 29, 1);

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_rol_usuario_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol_usuario', 'id'), 2, false);

--
-- Data for Name: admin_rol_usuario; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_rol_usuario (id, rol_id, usuario_id) VALUES (1, 1, 1);
