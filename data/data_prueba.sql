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

INSERT INTO admin_usuario (id, login, clave, nombre, apellido, correo_personal, correo_corporativo, activo, fecha_registro, fecha_nacimiento, pais, ciudad, region, empresa_id, foto, division_funcional, cargo, nivel_id) VALUES (1, 'admin', 'admin', 'Administrador', 'Sistema', 'soporte_link_gerencial@gmail.com', NULL, TRUE, '2017-09-21 10:28:00', '1981-01-29', 'Venezuela', 'Caracas', 'Capital', NULL, NULL, NULL, NULL, NULL);

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_permiso_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_permiso', 'id'), 5, false);

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
------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_rol_usuario_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol_usuario', 'id'), 2, false);

--
-- Data for Name: admin_rol_usuario; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_rol_usuario (id, rol_id, usuario_id) VALUES (1, 1, 1);
