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
-- Name: idadmin_pais_seq;; Type: SEQUENCE SET; Schema: public;
--

INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('ATG', 'Antigua y Barbuda                                 ', 'North America                                                                                       ', 'Caribbean                 ', 'Antigua and Barbuda                          ', 63, 'AG');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('ARG', 'Argentina                                           ', 'South America                                                                                       ', 'South America             ', 'Argentina                                    ', 69, 'AR');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('ABW', 'Aruba                                               ', 'North America                                                                                       ', 'Caribbean                 ', 'Aruba                                        ', 129, 'AW');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('BHS', 'Bahamas                                             ', 'North America                                                                                       ', 'Caribbean                 ', 'The Bahamas                                  ', 148, 'BS');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('BRB', 'Barbados                                            ', 'North America                                                                                       ', 'Caribbean                 ', 'Barbados                                     ', 174, 'BB');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('BLZ', 'Belize                                              ', 'North America                                                                                       ', 'Central America           ', 'Belize                                       ', 185, 'BZ');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('BMU', 'Bermuda                                             ', 'North America                                                                                       ', 'North America             ', 'Bermuda                                      ', 191, 'BM');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('BOL', 'Bolivia                                             ', 'South America                                                                                       ', 'South America             ', 'Bolivia                                      ', 194, 'BO');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('BRA', 'Brazil                                              ', 'South America                                                                                       ', 'South America             ', 'Brasil                                       ', 211, 'BR');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('CAN', 'Canada                                              ', 'North America                                                                                       ', 'North America             ', 'Canada                                       ', 1822, 'CA');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('CHL', 'Chile                                               ', 'South America                                                                                       ', 'South America             ', 'Chile                                        ', 554, 'CL');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('COL', 'Colombia                                            ', 'South America                                                                                       ', 'South America             ', 'Colombia                                     ', 2257, 'CO');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('CRI', 'Costa Rica                                          ', 'North America                                                                                       ', 'Central America           ', 'Costa Rica                                   ', 584, 'CR');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('CUB', 'Cuba                                                ', 'North America                                                                                       ', 'Caribbean                 ', 'Cuba                                         ', 2413, 'CU');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('DMA', 'Dominica                                            ', 'North America                                                                                       ', 'Caribbean                 ', 'Dominica                                     ', 586, 'DM');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('SLV', 'El Salvador                                         ', 'North America                                                                                       ', 'Central America           ', 'El Salvador                                  ', 645, 'SV');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('ECU', 'Ecuador                                             ', 'South America                                                                                       ', 'South America             ', 'Ecuador                                      ', 594, 'EC');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('ESP', 'España                                               ', 'Europe                                                                                              ', 'Southern Europe           ', 'España                                       ', 653, 'ES');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('USA', 'Estados Unidos                                       ', 'North America                                                                                       ', 'North America             ', 'United States                                ', 3813, 'US');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('GRD', 'Granada                                             ', 'North America                                                                                       ', 'Caribbean                 ', 'Grenada                                      ', 916, 'GD');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('GRL', 'Groenlandia                                           ', 'North America                                                                                       ', 'North America             ', 'Kalaallit Nunaat/Grønland                    ', 917, 'GL');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('GTM', 'Guatemala                                           ', 'North America                                                                                       ', 'Central America           ', 'Guatemala                                    ', 922, 'GT');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('GLP', 'Guadalupe                                         ', 'North America                                                                                       ', 'Caribbean                 ', 'Guadeloupe                                   ', 919, 'GP');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('GUF', 'Guyana Francesa                                       ', 'South America                                                                                       ', 'South America             ', 'Guyane française                             ', 3014, 'GF');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('GUY', 'Guyana                                              ', 'South America                                                                                       ', 'South America             ', 'Guyana                                       ', 928, 'GY');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('HTI', 'Haiti                                               ', 'North America                                                                                       ', 'Caribbean                 ', 'Haïti/Dayti                                  ', 929, 'HT');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('HND', 'Honduras                                            ', 'North America                                                                                       ', 'Central America           ', 'Honduras                                     ', 933, 'HN');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('FLK', 'Islas Malvinas                                    ', 'South America                                                                                       ', 'South America             ', 'Falkland Islands                             ', 763, 'FK');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('TCA', 'Islas Turcas y Caicos                            ', 'North America                                                                                       ', 'Caribbean                 ', 'The Turks and Caicos Islands                 ', 3423, 'TC');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('VGB', 'Islas Virgenes Britanicas                            ', 'North America                                                                                       ', 'Caribbean                 ', 'British Virgin Islands                       ', 537, 'VG');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('VIR', 'Islas Virgenes de los Estados Unidos                                ', 'North America                                                                                       ', 'Caribbean                 ', 'Virgin Islands of the United States          ', 4067, 'VI');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('JAM', 'Jamaica                                             ', 'North America                                                                                       ', 'Caribbean                 ', 'Jamaica                                      ', 1530, 'JM');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('MTQ', 'Martinica                                          ', 'North America                                                                                       ', 'Caribbean                 ', 'Martinique                                   ', 2508, 'MQ');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('MEX', 'México                                              ', 'North America                                                                                       ', 'Central America           ', 'México                                       ', 2515, 'MX');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('MSR', 'Montserrat                                          ', 'North America                                                                                       ', 'Caribbean                 ', 'Montserrat                                   ', 2697, 'MS');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('NIC', 'Nicaragua                                           ', 'North America                                                                                       ', 'Central America           ', 'Nicaragua                                    ', 2734, 'NI');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('PAN', 'Panamá                                              ', 'North America                                                                                       ', 'Central America           ', 'Panamá                                       ', 2882, 'PA');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('PRY', 'Paraguay                                            ', 'South America                                                                                       ', 'South America             ', 'Paraguay                                     ', 2885, 'PY');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('PER', 'Perú                                                ', 'South America                                                                                       ', 'South America             ', 'Perú/Piruw                                   ', 2890, 'PE');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('PRI', 'Puerto Rico                                         ', 'North America                                                                                       ', 'Caribbean                 ', 'Puerto Rico                                  ', 2919, 'PR');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('VEN', 'Venezuela', 'South America                                                                                       ', 'South America             ', 'Venezuela                                    ', 3539, 'VE');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('DOM', 'República Dominicana                                 ', 'North America                                                                                       ', 'Caribbean                 ', 'República Dominicana                         ', 587, 'DO');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('KNA', 'San Cristobal y Nieves                               ', 'North America                                                                                       ', 'Caribbean                 ', 'Saint Kitts and Nevis                        ', 3064, 'KN');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('LCA', 'Santa Lucia                                         ', 'North America                                                                                       ', 'Caribbean                 ', 'Saint Lucia                                  ', 3065, 'LC');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('SPM', 'San Pedro and Miquelón                           ', 'North America                                                                                       ', 'North America             ', 'Saint-Pierre-et-Miquelon                     ', 3067, 'PM');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('VCT', 'San Vicente y Las Granadinas                    ', 'North America                                                                                       ', 'Caribbean                 ', 'Saint Vincent and the Grenadines             ', 3066, 'VC');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('SUR', 'Surinam                                           ', 'South America                                                                                       ', 'South America             ', 'Surinombre                                     ', 3243, 'SR');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('TTO', 'Trinidad y Tobago                                 ', 'North America                                                                                       ', 'Caribbean                 ', 'Trinidad and Tobago                          ', 3336, 'TT');
INSERT INTO admin_pais (id, nombre, continente, region, nombre_local, capital, id2) VALUES ('URY', 'Uruguay                                             ', 'South America                                                                                       ', 'South America             ', 'Uruguay                                      ', 3492, 'UY');


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

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_notificacion', 'id'), 4, false);

--
-- Data for Name: admin_tipo_notificacion; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_tipo_notificacion (id, nombre) VALUES (1, 'Bienvenida');
INSERT INTO admin_tipo_notificacion (id, nombre) VALUES (2, 'Recordatorio');
INSERT INTO admin_tipo_notificacion (id, nombre) VALUES (3, 'Felicitación');

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_aplicacion_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_aplicacion', 'id'), 31, false);

--
-- Data for Name: admin_aplicacion; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (1, 'Administración', NULL, 'fa-cogs', TRUE, NULL, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (2, 'Usuarios', '_usuarios', 'fa-user', TRUE, 1, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (3, 'Aplicaciones', '_aplicaciones', 'fa-list-ul', TRUE, 1, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (4, 'Configuración de permisos', '_permisos', 'fa-key', TRUE, 1, 3);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (5, 'Administrar páginas', NULL, 'fa-files-o', TRUE, NULL, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (6, 'Categorías', '_categorias', 'fa-tags', TRUE, 5, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (7, 'Páginas', '_paginas', 'fa-file-text-o', TRUE, 5, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (8, 'Evaluaciones', '_paginasEvaluacion', 'fa-check-circle-o', TRUE, 5, 3);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (9, 'Administrar empresa', NULL, 'fa-building', TRUE, NULL, 3);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (10, 'Empresas', '_empresas', 'fa-industry', TRUE, 9, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (11, 'Asignación de páginas', '_empresasPaginas', 'fa-sitemap', TRUE, 9, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (12, 'Niveles por empresa', '_empresasNiveles', 'fa-list-ol', TRUE, 9, 3);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (13, 'Agrupación de páginas', '_Grupo', 'fa-cubes', TRUE, 9, 4);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (14, 'Participantes', '_participantes', 'fa-users', TRUE, 9, 5);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (15, 'Mural', '_filtroMuro', NULL, TRUE, NULL, 4);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (16, 'Administrar ayuda', NULL, 'fa-info-circle', TRUE, NULL, 5);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (17, 'Noticias y novedades', '_bibliotecas', 'fa-bell', TRUE, 9, 6);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (18, 'Reportes y consultas', NULL, NULL, TRUE, NULL, 6);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (19, 'Sesiones de usuarios', NULL, NULL, TRUE, 18, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (20, 'Usuarios sin ingresar', NULL, NULL, TRUE, 18, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (21, 'Estadisticas de las páginas', NULL, NULL, TRUE, 18, 3);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (22, 'Auditoria de evaluaciones', NULL, NULL, TRUE, 18, 4);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (23, 'Roles de usuarios', '_roles', 'fa-group', TRUE, 1, 4);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (24, 'Faqs', '_faqs', 'fa-question', TRUE, 16, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (25, 'Tutorial', '_tutorial', 'fa-desktop', TRUE, 16, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (26, 'Biblioteca virtual', '_bibliotecas', 'fa-book', TRUE, 9, 7);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (27, 'Administrar notificaciones', NULL, 'fa-inbox', TRUE, NULL, 7);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (28, 'Notificaciones', '_notificacion', 'fa-inbox', TRUE, 27, 1);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (29, 'Programar avisos', '_programados', 'fa-clock-o', TRUE, 27, 2);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (30, 'Certificados y Constancias', '_certificados', 'fa-graduation-cap', TRUE, 9, 8);
INSERT INTO admin_aplicacion (id, nombre, url, icono, activo, aplicacion_id, orden) VALUES (31, 'Calendario de eventos', '_calendario', 'fa-calendar', TRUE, NULL, 9);

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_tipo_noticia_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_noticia', 'id'), 4, false);

--
-- Data for Name: admin_tipo_noticia; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_tipo_noticia (id, nombre) VALUES (1, 'Noticia');
INSERT INTO admin_tipo_noticia (id, nombre) VALUES (2, 'Novedad');
INSERT INTO admin_tipo_noticia (id, nombre) VALUES (3, 'Biblioteca Virtual');

------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_tipo_destino_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_destino', 'id'), 8, false);

--
-- Data for Name: admin_tipo_destino; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_tipo_destino (id, nombre) VALUES (1, 'Todos');
INSERT INTO admin_tipo_destino (id, nombre) VALUES (2, 'Nivel');
INSERT INTO admin_tipo_destino (id, nombre) VALUES (3, 'Programa');
INSERT INTO admin_tipo_destino (id, nombre) VALUES (4, 'Grupo de participantes');
INSERT INTO admin_tipo_destino (id, nombre) VALUES (5, 'Usuarios que no han ingresado');
INSERT INTO admin_tipo_destino (id, nombre) VALUES (6, 'Usuarios que no han ingresado a un programa');
INSERT INTO admin_tipo_destino (id, nombre) VALUES (7, 'Usuarios aprobados');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_tipo_certificado_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_tipo_certificado', 'id'), 4, false);

--
-- Data for Name: certi_tipo_certificado; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_tipo_certificado (id, nombre) VALUES (1, 'Por Empresa');
INSERT INTO certi_tipo_certificado (id, nombre) VALUES (2, 'Por Página');
INSERT INTO certi_tipo_certificado (id, nombre) VALUES (3, 'Por Grupo de Páginas');

------------------------------------------------------------------------------------------------------------------------
-- Name: idcerti_tipo_imagen_certificado_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('certi_tipo_imagen_certificado', 'id'), 3, false);

--
-- Data for Name: certi_tipo_imagen_certificado; Type: TABLE DATA; Schema: public;
--

INSERT INTO certi_tipo_imagen_certificado (id, nombre) VALUES (1, 'Certificado');
INSERT INTO certi_tipo_imagen_certificado (id, nombre) VALUES (2, 'Constancia');
