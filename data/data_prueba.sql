--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;


------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_permiso_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_permiso', 'id'), 23, false);

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
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (22, 11, 1);
INSERT INTO admin_permiso (id, aplicacion_id, rol_id) VALUES (23, 31, 1);

------------------------------------------------------------------------------------------------------------
-- Name: admin_empresa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_empresa_id_seq', 7, true);


--
-- Data for Name: admin_empresa; Type: TABLE DATA; Schema: public; Owner: develo
--

INSERT INTO admin_empresa VALUES (1, 'Link Gerencial', NULL, NULL, true, NULL, '2018-02-09 13:45:36', NULL, '<p>Bienvenida de la empresa</p>
', 'VEN', true, true);
INSERT INTO admin_empresa VALUES (2, 'Altrum', NULL, NULL, true, NULL, '2018-02-09 13:47:20', NULL, '<p>BIenvenida</p>
', 'ARG', true, true);
INSERT INTO admin_empresa VALUES (3, 'CAEI', NULL, NULL, true, NULL, '2018-02-09 13:48:26', NULL, '<p>Bienvenida</p>
', 'DOM', false, false);
INSERT INTO admin_empresa VALUES (4, 'OC-CENI', NULL, NULL, true, NULL, '2018-02-09 13:49:40', NULL, '<p>Bienvenida</p>
', 'DOM', false, false);
INSERT INTO admin_empresa VALUES (5, 'Equifax', NULL, NULL, true, NULL, '2018-02-09 13:51:10', NULL, '<p>Bienvenida&nbsp;</p>
', 'ARG', true, true);
INSERT INTO admin_empresa VALUES (6, 'Banreservas', NULL, NULL, true, NULL, '2018-02-09 13:54:10', NULL, '<p>Bienvenida</p>
', 'DOM', false, true);
INSERT INTO admin_empresa VALUES (7, 'TRF', NULL, NULL, true, NULL, '2018-02-09 13:55:04', NULL, '<p>Bienvenida</p>
', 'ARG', true, true);

-----------------------------------------------------------------------------------------------------------------
-- Name: admin_nivel_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_nivel_id_seq', 15, true);


--
-- Data for Name: admin_nivel; Type: TABLE DATA; Schema: public; Owner: develo
--

INSERT INTO admin_nivel VALUES (1, 'Ventas', 2);
INSERT INTO admin_nivel VALUES (2, 'Lideres', 2);
INSERT INTO admin_nivel VALUES (3, 'Coordinadores', 2);
INSERT INTO admin_nivel VALUES (4, 'Lideres', 6);
INSERT INTO admin_nivel VALUES (5, 'Operadores', 6);
INSERT INTO admin_nivel VALUES (6, 'Agencias', 6);
INSERT INTO admin_nivel VALUES (7, 'Lideres', 3);
INSERT INTO admin_nivel VALUES (8, 'Operadores', 3);
INSERT INTO admin_nivel VALUES (9, 'Lideres', 5);
INSERT INTO admin_nivel VALUES (10, 'Vendedores', 5);
INSERT INTO admin_nivel VALUES (11, 'Ventas', 1);
INSERT INTO admin_nivel VALUES (12, 'Lideres', 1);
INSERT INTO admin_nivel VALUES (13, 'Lideres', 4);
INSERT INTO admin_nivel VALUES (14, 'Coordinadores', 4);
INSERT INTO admin_nivel VALUES (15, 'Lideres', 7);

---------------------------------------------------------------------------------------------------
-- Name: admin_usuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_usuario_id_seq', 14, true);


--
-- Data for Name: admin_usuario; Type: TABLE DATA;
 Schema: public; Owner: develo
--

INSERT INTO admin_usuario VALUES (1, 'admin', 'admin', 'Administrador', 'Sistema', 'soporte_link_gerencial@gmail.com', NULL, true, '2017-09-21 10:28:00', '1981-01-29', 'VEN', 'Caracas', 'Capital', NULL, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (2, 'asegovia', '123456', 'Ansise', 'Segovia', 'ansi79@gmail.com', 'ansiisesegovia@linkgerencial.com', true, '2018-02-09 16:51:59', '1979-09-19', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de Tecnología', 'Gerente de Tecnoligía', 12);
INSERT INTO admin_usuario VALUES (3, 'mdominguez', '123456', 'Mary Flor', 'Dominguez', '', 'marydominguez@linkgerencial.com', true, '2018-02-09 16:53:31', '1960-09-13', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia General', 'Directora', 12);
INSERT INTO admin_usuario VALUES (4, 'eherrera', '123456', 'Elgui', 'Herrera', '', 'eherrera@linkgerencial.com', true, '2018-02-09 16:55:34', '1957-02-13', 'VEN', 'Caracas', 'Central', 1, '', 'Viceprensidencia', 'Vicepresidente Ejecutivo', 12);
INSERT INTO admin_usuario VALUES (5, 'mdaza', '123456', 'Marianella', 'Daza', '', 'mdaza@linkgerencial.com', true, '2018-02-09 16:57:14', '1979-07-27', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de Mercadeo', 'Gerente de mercadeo', 11);
INSERT INTO admin_usuario VALUES (8, 'jchiquin', '123456', 'Jeimy', 'Chiquin', '', 'jchiquin@linkgerencial.com', true, '2018-02-09 17:31:26', '1981-08-05', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de proyectos', 'Gerente de proyectos', 12);
INSERT INTO admin_usuario VALUES (6, 'aalvarez', '123456', 'Alnahir', 'Alvarez', 'alnahir07@hotmail.com', 'aalvarez@formacion2puntocero.com', true, '2018-02-09 17:22:16', '1999-07-29', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de mercadeo', 'Jefe de mercadeo', 11);
INSERT INTO admin_usuario VALUES (9, 'yavila', '123456', 'Yasaac', 'Ávila', 'yavila.linkgerencial.com@gmail.com', 'yavila@linkgerencial.com', true, '2018-02-09 17:47:49', '1999-06-30', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de tecnología', 'Analista', 12);
INSERT INTO admin_usuario VALUES (10, 'rvirguez', '123456', 'Ricardo', 'Virguez', 'rvirguez.linkgerencial@gmail.com', 'rvirguez@linkgerencial.com', true, '2018-02-09 17:51:27', '1990-11-29', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de tecnología', 'Analista', 12);
INSERT INTO admin_usuario VALUES (11, 'rvirguez', '123456', 'Ricardo', 'Virguez', 'rvirguez.linkgerencial@gmail.com', 'rvirguez@linkgerencial.com', true, '2018-02-09 17:51:27', '1990-11-29', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de tecnología', 'Analista', 12);
INSERT INTO admin_usuario VALUES (7, 'mrodriguez', '123456', 'Maria Gabriela', 'Rodriguez', 'mgrodriguez.linkgerencial@gmail.com', 'mgrodriguez@linkgerencial.com', true, '2018-02-09 17:27:02', '1999-09-19', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de tecnología', 'Administrador de contenido', 12);
INSERT INTO admin_usuario VALUES (12, 'cgonzalez', '123456', 'Celinet', 'Gonzalez', '', 'cgonzalez@linkgerencial.com', true, '2018-02-09 17:53:49', '1995-02-08', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia General', 'Asistente', 12);
INSERT INTO admin_usuario VALUES (13, 'jvelasquez', '123456', 'José', 'Velásquez', 'Josenriquev@gmail.com', 'jvelasquez@linkgerencial.com', true, '2018-02-09 17:55:56', '1979-01-29', 'VEN', 'Caracas', 'Central', 1, '', 'Gerencia de tecnoligía', 'Coordinador de desarrollo', 12);


------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_rol_usuario_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol_usuario', 'id'), 2, false);

--
-- Data for Name: admin_rol_usuario; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_rol_usuario (id, rol_id, usuario_id) VALUES (1, 1, 1);
