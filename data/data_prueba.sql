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

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_permiso', 'id'), 78, false);

--
-- Data for Name: admin_permiso; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_permiso VALUES (1, 1, 1);
INSERT INTO admin_permiso VALUES (2, 3, 1);
INSERT INTO admin_permiso VALUES (3, 10, 1);
INSERT INTO admin_permiso VALUES (4, 9, 1);
INSERT INTO admin_permiso VALUES (5, 5, 1);
INSERT INTO admin_permiso VALUES (6, 6, 1);
INSERT INTO admin_permiso VALUES (7, 4, 1);
INSERT INTO admin_permiso VALUES (8, 2, 1);
INSERT INTO admin_permiso VALUES (9, 12, 1);
INSERT INTO admin_permiso VALUES (10, 7, 1);
INSERT INTO admin_permiso VALUES (11, 14, 1);
INSERT INTO admin_permiso VALUES (12, 8, 1);
INSERT INTO admin_permiso VALUES (13, 23, 1);
INSERT INTO admin_permiso VALUES (14, 24, 1);
INSERT INTO admin_permiso VALUES (15, 25, 1);
INSERT INTO admin_permiso VALUES (16, 26, 1);
INSERT INTO admin_permiso VALUES (17, 27, 1);
INSERT INTO admin_permiso VALUES (18, 17, 1);
INSERT INTO admin_permiso VALUES (19, 16, 1);
INSERT INTO admin_permiso VALUES (20, 28, 1);
INSERT INTO admin_permiso VALUES (21, 29, 1);
INSERT INTO admin_permiso VALUES (22, 11, 1);
INSERT INTO admin_permiso VALUES (23, 31, 1);
INSERT INTO admin_permiso VALUES (24, 30, 1);
INSERT INTO admin_permiso VALUES (25, 33, 1);
INSERT INTO admin_permiso VALUES (26, 32, 1);
INSERT INTO admin_permiso VALUES (27, 13, 1);
INSERT INTO admin_permiso VALUES (28, 19, 1);
INSERT INTO admin_permiso VALUES (29, 19, 5);
INSERT INTO admin_permiso VALUES (30, 20, 1);
INSERT INTO admin_permiso VALUES (31, 20, 5);
INSERT INTO admin_permiso VALUES (32, 21, 1);
INSERT INTO admin_permiso VALUES (33, 21, 5);
INSERT INTO admin_permiso VALUES (34, 22, 1);
INSERT INTO admin_permiso VALUES (35, 22, 5);
INSERT INTO admin_permiso VALUES (36, 34, 1);
INSERT INTO admin_permiso VALUES (37, 34, 5);
INSERT INTO admin_permiso VALUES (38, 18, 1);
INSERT INTO admin_permiso VALUES (45, 18, 3);
INSERT INTO admin_permiso VALUES (46, 19, 3);
INSERT INTO admin_permiso VALUES (47, 20, 3);
INSERT INTO admin_permiso VALUES (48, 21, 3);
INSERT INTO admin_permiso VALUES (49, 22, 3);
INSERT INTO admin_permiso VALUES (53, 34, 3);
INSERT INTO admin_permiso VALUES (62, 27, 3);
INSERT INTO admin_permiso VALUES (63, 28, 3);
INSERT INTO admin_permiso VALUES (64, 29, 3);
INSERT INTO admin_permiso VALUES (65, 9, 5);
INSERT INTO admin_permiso VALUES (66, 14, 5);
INSERT INTO admin_permiso VALUES (67, 17, 5);
INSERT INTO admin_permiso VALUES (68, 26, 5);
INSERT INTO admin_permiso VALUES (69, 30, 5);
INSERT INTO admin_permiso VALUES (70, 18, 5);
INSERT INTO admin_permiso VALUES (71, 18, 4);
INSERT INTO admin_permiso VALUES (72, 19, 4);
INSERT INTO admin_permiso VALUES (74, 21, 4);
INSERT INTO admin_permiso VALUES (75, 22, 4);
INSERT INTO admin_permiso VALUES (76, 34, 4);
INSERT INTO admin_permiso VALUES (77, 20, 4);

------------------------------------------------------------------------------------------------------------
-- Name: admin_empresa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_empresa_id_seq', 9, true);


--
-- Data for Name: admin_empresa; Type: TABLE DATA; Schema: public; Owner: develo
--

INSERT INTO admin_empresa VALUES (1, 'Link Gerencial', NULL, NULL, true, NULL, '2018-02-09 13:45:36', NULL, '<p>Estimado participante, bienvenido a&nbsp;<strong><em>Formaci&oacute;n 2.0&nbsp;</em></strong></p>

<p>A partir de ahora contar&aacute;s con una plataforma innovadora, que te permitir&aacute; formarte y capacitarte de manera integral, atendiendo a las necesidades de desarrollo y crecimiento que la empresa espera de todos sus colaboradores.</p>

<p>Esta nueva herramienta de aprendizaje, te ofrece la posibilidad de planificar el tiempo que le dedicar&aacute;s a tu propio desarrollo, sin barreras de lugar, ni tiempo, ya que contar&aacute;s con diversas alternativas de aprendizaje a las que podr&aacute;s acceder desde cualquier dispositivo y desde cualquier lugar con tan s&oacute;lo contar con una conexi&oacute;n a internet.</p>

<p>Toma en cuenta que<em><strong>&nbsp;t&uacute; eres el capital m&aacute;s valioso con el que cuenta nuestra empresa</strong></em>,&nbsp;por eso ponemos a tu disposici&oacute;n esta innovadora metodolog&iacute;a de formaci&oacute;n para que sigas mejorando cada vez m&aacute;s, tanto personal, como profesionalmente.</p>

<h2>Desde ya te deseamos el mayor de los &eacute;xitos!</h2>
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
INSERT INTO admin_empresa VALUES (8, 'Prueba', NULL, NULL, true, NULL, '2018-02-09 17:24:47', NULL, '<p>Prueba</p>

', 'VEN', true, true);
INSERT INTO admin_empresa VALUES (9, 'Grupo PUNTACANA', NULL, NULL, true, NULL, '2018-03-21 17:34:50', NULL, '<p><img alt="" src="http://formacion2puntocero.com/test/web/ckfinder/userfiles/images/banner_gpc.png" style="width:820px" /></p>

<p>Bienvenido(a)</p>

<p>A partir de ahora contar&aacute;s con una nueva oferta a nivel de actividades de capacitaci&oacute;n on line, que te permitir&aacute; formarte y capacitarte de manera integral, atendiendo a las necesidades de desarrollo y crecimiento que el&nbsp;<strong><em>GRUPO PUNTACANA</em></strong>&nbsp;espera de todos sus colaboradores.</p>

<p>A trav&eacute;s de herramientas de formaci&oacute;n on line, te ofrecemos la posibilidad de planificar el tiempo que le dedicar&aacute;s a tu propio desarrollo, sin barreras de lugar, ni tiempo, ya que contar&aacute;s con diversas alternativas de aprendizaje a las que podr&aacute;s acceder desde cualquier dispositivo y desde cualquier lugar con tan s&oacute;lo contar con una conexi&oacute;n a internet.</p>

<p>Toma en cuenta que<strong><em>&nbsp;t&uacute; eres el capital m&aacute;s valioso con el que cuenta nuestra organizaci&oacute;n</em></strong>,&nbsp;por eso ponemos a tu disposici&oacute;n esta innovadora metodolog&iacute;a de formaci&oacute;n para que sigas mejorando cada vez m&aacute;s, tanto personal, como profesionalmente.</p>

<h2>Si deseas conocer las novedades de esta nueva plataforma, te invitamos a observar el video tutorial que te mostrar&aacute; paso a paso c&oacute;mo debes navegar</h2>

<p>

<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/FORMACION/video/G_Navegacion.mp4" type="video/mp4" />

<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>

</video>

</p>

<p>&nbsp;</p>

<hr />

<p>Puedes descargar el tutorial en formato PDF haciendo clic&nbsp;<a href="https://recursos2puntocero.com/recursos/pdf/tutorialformacionmed.pdf" target="_blank"><strong><em>aqu&iacute;</em></strong></a>.</p>

<p>&nbsp;</p>

<p>Cualquier inquietud comun&iacute;cate con nosotros a trav&eacute;s de&nbsp;<a href="mailto:soporte@formacion2puntocero.com?subject=Soporte%20Formaci%C3%B3n2.0">soporte@formacion2puntocero.com</a></p>

', 'DOM', false, false);

-----------------------------------------------------------------------------------------------------------------
-- Name: admin_nivel_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_nivel_id_seq', 17, true);


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
INSERT INTO admin_nivel VALUES (16, 'Coordinadores', 1);
INSERT INTO admin_nivel VALUES (17, 'Analistas', 1);


---------------------------------------------------------------------------------------------------
-- Name: admin_usuario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_usuario_id_seq', 23, true);


--
-- Data for Name: admin_usuario; Type: TABLE DATA;
--
INSERT INTO admin_usuario VALUES (1, 'admin', 'admin', 'Administrador', 'Sistema', 'soporte_link_gerencial@gmail.com', NULL, true, '2017-09-21 10:28:00', '1981-01-29', 'VEN', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (2, 'asegovia', '123456', 'Ansise', 'Segovia', 'ansi79@gmail.com', 'ansiisesegovia@linkgerencial.com', true, '2018-02-09 16:51:59', '1979-09-19', 'VEN', NULL, NULL, NULL, NULL, 1, '', 12, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (3, 'mdominguez', '123456', 'Mary Flor', 'Dominguez', '', 'marydominguez@linkgerencial.com', true, '2018-02-09 16:53:31', '1960-09-13', 'VEN', NULL, NULL, NULL, NULL, 1, '', 12, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (4, 'eherrera', '123456', 'Elgui', 'Herrera', '', 'eherrera@linkgerencial.com', true, '2018-02-09 16:55:34', '1957-02-13', 'VEN', '1', 'recursos/usuarios/flor2.jpg', '', '', 1, '', 12, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (5, 'mdaza', '123456', 'Marianella', 'Daza', '', 'mdaza@linkgerencial.com', true, '2018-02-09 16:57:14', '1979-07-27', 'VEN', '1', '', '11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (6, 'aalvarez', '123456', 'Alnahir', 'Alvarez', 'alnahir07@hotmail.com', 'aalvarez@formacion2puntocero.com', true, '2018-02-09 17:22:16', '1999-07-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 11, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (7, 'mrodriguez', '123456', 'Maria Gabriela', 'Rodriguez', 'mgrodriguez.linkgerencial@gmail.com', 'mgrodriguez@linkgerencial.com', true, '2018-02-09 17:27:02', '1999-09-19', 'VEN', NULL, NULL, NULL, NULL, 1, '', 16, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (8, 'jchiquin', '123456', 'Jeimy', 'Chiquin', '', 'jchiquin@linkgerencial.com', true, '2018-02-09 17:31:26', '1981-08-05', 'VEN', NULL, NULL, NULL, NULL, 1, '', 12, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (9, 'yavila', '123456', 'Yasaac', 'Ávila', 'yavila.linkgerencial.com@gmail.com', 'yavila@linkgerencial.com', true, '2018-02-09 17:47:49', '1999-06-30', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (10, 'rvirguez', '123456', 'Ricardo', 'Virguez', 'rvirguez.linkgerencial@gmail.com', 'rvirguez@linkgerencial.com', true, '2018-02-09 17:51:27', '1990-11-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (12, 'cgonzalez', '123456', 'Celinet', 'Gonzalez', '', 'cgonzalez@linkgerencial.com', true, '2018-02-09 17:53:49', '1995-02-08', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (13, 'jvelasquez', '123456', 'José', 'Velásquez', 'Josenriquev@gmail.com', 'jvelasquez@linkgerencial.com', true, '2018-02-09 17:55:56', '1979-01-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 16, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (14, 'minnie', '123456', 'Minnie', 'Mouse', 'minnie@mouse.com', '', true, '2018-02-13 13:38:40', '2000-02-02', 'VEN', NULL, NULL, NULL, NULL, 1, 'recursos/usuarios/g_etn_recoleccion_b.jpg', 17, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (15, 'tempmary', '123456', 'Mary Flor', 'Domínguez', 'dominguezmaryflor@gmail.com', 'marydominguez@linkgerencial.com', true, '2018-02-14 18:32:30', '1959-09-11', 'VEN', NULL, NULL, NULL, NULL, 1, 'recursos/usuarios/foto_mary_flor.jpg', 11, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (16, 'luisito', '123456', 'Luisito', 'Luisito ', 'luisito@gmail.com', 'luisito@linkgerencial.com', true, '2018-02-14 18:39:38', '2000-01-03', 'DOM', '', '', '', '', 6, '', 6, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (17, 'temppepito', '123456', 'pepito', 'pepito', 'pepito@gmail.com', 'pepito@gmail.com', true, '2018-02-14 18:49:59', '1996-01-09', 'VEN', NULL, NULL, NULL, NULL, 1, 'recursos/usuarios/flor4.jpg', 16, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (18, 'temptutorvirtual', '123456', 'TUTOR', 'VIRTUAL', 'correotutor@gmail.com', 'correocorporativo@gmail.com', true, '2018-02-17 14:22:44', '1999-01-01', 'VEN', '', '', '', '', 7, '', 15, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (19, 'ponceelrelajado', 'uprueba1', 'Jhonatan', 'Ponce', 'ponceelrelajado@gmail.com', 'ponceelrelajado@gmail.com', true, '2018-02-20 22:03:31', '1988-05-09', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (20, 'pluto', '123456', 'pluto', 'pluto', '', '', true, '2018-03-23 19:09:24', '2000-02-02', 'VEN', '', '', '', '', 2, '', 3, false, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (21, 'amst', '123456', 'Ansise', 'Segovia', '', '', true, '2018-03-27 15:12:13', '1999-06-23', 'VEN', '', '', '', '', 2, 'flor2.jpg', 3, false, NULL, NULL, '677207822');
INSERT INTO admin_usuario VALUES (22, 'arodriguez', '123456', 'Andreina', 'Rodriguez', '', 'arodriguez@linkgerencial.com', true, '2018-03-28 13:49:30', '1979-06-28', 'VEN', '', '', '', '', 1, '', 16, false, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (23, 'orojas', '123456', 'Odalis', 'Rojas', '', 'odarojas@linkgerencial.com', true, '2018-03-28 15:10:49', '1970-08-04', 'DOM', '', '', '', '', 1, 'recursos/usuarios/tutor.jpg', 12, false, NULL, NULL, NULL);



------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_rol_usuario_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol_usuario', 'id'), 34, false);

--
-- Data for Name: admin_rol_usuario; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_rol_usuario VALUES (1, 1, 1);
INSERT INTO admin_rol_usuario VALUES (2, 2, 2);
INSERT INTO admin_rol_usuario VALUES (3, 2, 3);
INSERT INTO admin_rol_usuario VALUES (4, 2, 4);
INSERT INTO admin_rol_usuario VALUES (5, 2, 5);
INSERT INTO admin_rol_usuario VALUES (6, 2, 6);
INSERT INTO admin_rol_usuario VALUES (7, 2, 7);
INSERT INTO admin_rol_usuario VALUES (8, 2, 8);
INSERT INTO admin_rol_usuario VALUES (9, 2, 9);
INSERT INTO admin_rol_usuario VALUES (10, 2, 10);
INSERT INTO admin_rol_usuario VALUES (12, 2, 12);
INSERT INTO admin_rol_usuario VALUES (13, 2, 13);
INSERT INTO admin_rol_usuario VALUES (19, 2, 14);
INSERT INTO admin_rol_usuario VALUES (20, 2, 15);
INSERT INTO admin_rol_usuario VALUES (21, 2, 16);
INSERT INTO admin_rol_usuario VALUES (22, 2, 17);
INSERT INTO admin_rol_usuario VALUES (25, 3, 18);
INSERT INTO admin_rol_usuario VALUES (26, 2, 19);
INSERT INTO admin_rol_usuario VALUES (27, 2, 20);
INSERT INTO admin_rol_usuario VALUES (28, 2, 21);
INSERT INTO admin_rol_usuario VALUES (29, 5, 18);
INSERT INTO admin_rol_usuario VALUES (30, 3, 22);
INSERT INTO admin_rol_usuario VALUES (31, 2, 22);
INSERT INTO admin_rol_usuario VALUES (32, 1, 23);
INSERT INTO admin_rol_usuario VALUES (33, 2, 23);
--
-- Name: certi_pagina_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('certi_pagina_id_seq', 37, true);


--
-- Data for Name: certi_pagina; Type: TABLE DATA; Schema: public; Owner: develo
--

INSERT INTO certi_pagina VALUES (1, 'Marketing Digital', NULL, 1, '<p>Prueba</p>', '<p>Prueba</p>', NULL, NULL, '2018-02-12 17:17:17', '2018-02-12 17:17:17', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (2, 'Emprende tu Negocio', NULL, 1, '<p>Programa prueba &quot;Emprende tu negocio&quot;</p>', '<table align="center" border="1" cellpadding="1" cellspacing="1" style="width:500px" summary="probando tabla">

	<caption>PRUEBA</caption>

	<thead>

		<tr>

			<th scope="col">&nbsp;</th>

			<th scope="col">&nbsp;</th>

		</tr>

	</thead>

	<tbody>

		<tr>

			<td>&nbsp;</td>

			<td>&nbsp;</td>

		</tr>

		<tr>

			<td>&nbsp;</td>

			<td>&nbsp;</td>

		</tr>

	</tbody>

</table>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Una de las preguntas impactante en eventos con emprendedores es cuando se pregunta &iquest;Se puede ense&ntilde;ar a emprender?; muchas personas creen que el emprendedor, nace, tiene una gen&eacute;tica de ver las oportunidades y asumir riesgos diferentes al resto de las personas, por lo cual creen equivocadamente que el emprendedor nace.</span></span></span></span><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> </span></span></span></span></p>

<hr />

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Pero la verdad es contraria, el emprendimiento se puede aprender y se puede gestionar de forma met&oacute;dica.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Podemos aprender a ser empresarios y ha construir una empresa desde una idea.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">El emprendimiento se puede aprender y se puede gestionar de forma met&oacute;dica.</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify">&nbsp;</p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Existen tres mitos habituales que deben desaparecer.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><u><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">El primer mito</span></span></u><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> es que es una sola persona la que crea la empresa. Aunque la imagen del emprendedor como un h&eacute;roe solitario es muy com&uacute;n, las investigaciones revelan una realidad muy diferente: son los equipos los que crean las empresas, y lo mas importante, cuanto mayor es el equipo m&aacute;s probabilidad de &eacute;xito tiene.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><u><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">El segundo mito</span></span></u><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> es que todos los emprendedores son personas carism&aacute;ticas y que su carisma es un de los factores de &eacute;xito. En realidad, aunque el carisma pueda ser eficaz durante un breve periodo de tiempo, es dif&iacute;cil mantener y hacer crecer un negocio solo por el carisma. </span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Las investigaciones demuestran, en cambio, que mas que tener carisma, los emprendedores necesitan ser eficaces comunicando, seleccionando personas y vendiendo.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><u><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">El tercer mito</span></span></u><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> es que hay un gen emprendedor, y que determinadas personas est&aacute;n gen&eacute;ticamente predispuestas al &eacute;xito cuando crean empresas. </span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Lo realmente demostrado es que no existe ese gen, tampoco rasgos de personalidad como la audacia o la extravagancia que condicionan el &eacute;xito.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">En realidad, lo que esta demostrado es que hay unas capacidades que aumentan las probabilidades de tener &eacute;xito, como son las de direcci&oacute;n de equipos, la capacidad comercial y la capacidad de tener una idea de un producto o servicio y llevarla a cabo.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Estas capacidades se pueden aprender. La gente puede adaptarse y aprender nuevas conductas y el proceso que conlleva emprender.</span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Este programa tiene dos m&oacute;dulos, en el primer modulo se exponen aspectos referentes del reto de emprender, los factores personales y las interrogantes que subyacen en el emprendedor, el segundo modulo es sobre el plan de ejecuci&oacute;n del proceso de crear un negocio. </span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Estamos seguros ser&aacute; de gran utilidad y te dotara de herramientas para formarte como un emprendedor de &eacute;xito.</span></span></span></span></p>', NULL, NULL, '2018-02-13 14:57:41', '2018-02-13 15:12:06', 1, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (3, 'El reto de emprender', 2, 2, '<p>PRUEBA M&Oacute;DULO 1</p>', '<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Son varios los adjetivos con los que se se&ntilde;alan a los emprendedores: pasi&oacute;n, riesgo, trascendencia, ambici&oacute;n, iniciativa, perseverancia e innovaci&oacute;n son algunos de los calificativos que podr&iacute;amos utilizar para describirlos, todos ellos con raz&oacute;n y fundamento. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Pero de todos los adjetivos que podr&iacute;amos otorgar a un emprendedor, tal vez el de &ldquo;constructor&rdquo; es el que m&aacute;s lo identifica dentro del campo de la construcci&oacute;n de una organizaci&oacute;n humana que va m&aacute;s all&aacute; de su idea, producto o servicio, o incluso la oportunidad que le dio origen al proyecto.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">El emprendedor construye una visi&oacute;n y la convierte en un negocio.</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Construir, ser arquitecto de una idea y llevarla al mundo de los negocios no es tarea sencilla; son muchos, y seguir&aacute;n siendo, los que han intentado con mayor o menor &eacute;xito construir un negocio a partir de una idea. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Pero si observamos las historias de los grandes emprendedores, existe un claro denominador com&uacute;n, algo que en todos est&aacute; presente al inicio y que se conforma como la pieza clave en el emprendimiento independientemente del m&eacute;todo o el camino utilizado. Nos referimos a los sue&ntilde;os.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">Cada emprendimiento es un viaje, a veces a lo desconocido, pero es la pasi&oacute;n de cada uno la que hace que este viaje valga la pena, por nosotros, por los nuestros.</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Dicen que el sabor del &eacute;xito es dulce y sin duda as&iacute; es, pero es tambi&eacute;n un camino complejo con cierta amargura que requiere de planificaci&oacute;n, conocimiento, aprendizaje y una inicial voluntad de evolucionar. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">No es un viaje sencillo, pero como dec&iacute;a el fil&oacute;sofo chino Lao-Ts&eacute; en el siglo IV antes de cristo: </span></span></span></p>

<blockquote>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">&ldquo;Un viaje de mil millas, comienza por el primer paso&rdquo;</span></span></strong></span></span></p>

</blockquote>', NULL, NULL, '2018-02-13 15:00:01', '2018-02-13 15:00:01', 1, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (4, 'El plan de Negocios', 2, 2, '<p>M&oacute;dulo 2</p>', '<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l Plan de Negocios (PN) se ha convertido en los</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">&uacute;ltimo</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s a&ntilde;os en un tema recurrente de la literatura para emprendedores. Hay cientos de libros escritos sobre el tema, p&aacute;ginas de internet, art&iacute;culos</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">period&iacute;stico</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s e incluso software dise&ntilde;ado exclusivamente con ese fin. </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">S</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">i se busca en Google la frase &ldquo;Plan de Negocios&rdquo;, podr&aacute; encontrarse m&aacute;s de</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">30 </span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">millone</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s de resultados, y eso solamente en espa&ntilde;ol; Y escribiendo su equivalente en ingl&eacute;s &ldquo;business plan&rdquo;, se encontrar&aacute;n, casi 140 millones</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">d</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e referencias.</span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">S</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e trata de un tema que, sin dudas, est&aacute; en la</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">agend</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a de cualquier emprendedor.</span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">Qu</span></span></strong><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">&eacute; es y para qu&eacute; sirve</span></span></strong><strong> </strong><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">u</span></span></strong><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">n Plan de Negocios</span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Ahor</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a bien, &iquest;qu&eacute; es un Plan de Negocios o un Plan de Empresa? Tratando de aproximar una definici&oacute;n, podemos definirlo as&iacute;: es un documento que describe un proyecto empresarial de manera integral. </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">S</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e trata de una hoja de ruta que indica los objetivos del</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">proyecto</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">, las estrategias para lograr esos objetivos, los recursos a utilizar, los resultados esperados (productivos, comerciales y financieros). </span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:center"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">El Plan de Negocios es el</span></span></span></strong><strong> </strong><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">qu&eacute;</span></span></span></strong><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">, el c&oacute;mo, el cu&aacute;ndo y el porqu&eacute; del negocio.</span></span></span></strong></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Crea</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">r una empresa es en s&iacute; mismo una actividad de riesgo, nadie puede asegurarnos los resultados. Es como lanzarse a emprender un largo viaje en un territorio desconocido. En un caso as&iacute;, </span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">&iquest;n</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o llevar&iacute;amos al menos un mapa del territorio que vamos a recorrer? &iquest;No har&iacute;amos c&aacute;lculos de cu&aacute;nto combustible va a consumir el veh&iacute;culo y d&oacute;nde</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">ha</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">y estaciones de servicio para reponer el tanque?, </span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">&iquest;Acas</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o no averiguar&iacute;amos los costos de hoteles</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">y comidas para saber si el presupuesto alcanza?</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Cuand</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o encaramos cualquier actividad &nbsp;nueva</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">y de alto riesgo, como un largo viaje, tomamos</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">previsiones</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">, hacemos planes, consultamos especialistas o gente que ya estuvo all&iacute;, averiguamos</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">precios</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">, &nbsp;pedimos &nbsp;consejos.</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Ahor</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a estamos a punto de iniciar una gran</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">aventura</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">. Entusiasmados con el proyecto de crear</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">nuestr</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a propia empresa. Convencidos de que el</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">product</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o o servicio va a gustar a los clientes y que</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">vamo</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s a ganar dinero con ello. Y estamos quiz&aacute;s a</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">punt</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o de invertir los ahorros de toda una vida en esta aventura. &iquest;No deber&iacute;amos detenernos un tiempo a reflexionar y evaluar la viabilidad del proyecto?</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:center"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">Arma</span></span></span></strong><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">r el Plan de Negocio es la oportunidad que debemos</span></span></span></strong><strong> </strong><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">darno</span></span></span></strong><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">s de pensar racionalmente todos los aspectos del proyecto empresarial.</span></span></span></strong></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l producto, el mercado, la competencia, los &nbsp;precios, &nbsp;los &nbsp;costos, &nbsp;la producci&oacute;n, la comercializaci&oacute;n, la financiaci&oacute;n. Y</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">podr&iacute;amo</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s seguir con otros detalles m&aacute;s.</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">L</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a planificaci&oacute;n integral de un emprendimiento es particularmente aconsejable porque los</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">emprendedore</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s suelen ser&hellip;. &iexcl;optimistas empedernidos!</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Co</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n respecto al ejercicio de planificar es posible</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">encontra</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">r dos posturas extremas que debemos</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">evita</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">r</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">. En un primer extremo est&aacute;n los esc&eacute;pticos</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">ant</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">i planificaci&oacute;n. Seguramente encontraremos argumentos como: &iquest;Para qu&eacute; planificar si el futuro es</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">impredecible?</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">, si todo es cambiante e inestable. </span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">&iexcl;De</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l futuro no sabemos nada y cualquier intento de</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">imaginarl</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o no tiene sentido! &iexcl;Apenas s&eacute; lo que har&eacute; la semana que viene!</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Per</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o esta postura equivale a salir de viaje sin</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">verifica</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">r si tenemos los documentos, si el dinero va</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a alcanzar, si encontraremos hotel, a no llevar mapa, ni averiguar si har&aacute; fr&iacute;o o calor. </span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">T</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l emprendedor invierte su capital sin saber si lo recuperar&aacute;, y</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n cu&aacute;nto tiempo. Sale al mercado sin haber estudiado a sus clientes potenciales ni a sus futuros</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">competidores</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">. No ha previsto un Plan B por si las</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">cosa</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s resultan diferentes a lo imaginado.</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n el otro extremo podemos encontrar al fan&aacute;tico de la planificaci&oacute;n. Aquel que pretende estimar</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">hast</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a los m&aacute;ximos detalles de su proyecto y que</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o entrar&aacute; en acci&oacute;n hasta que no despej&oacute; absolutamente todas sus dudas.</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:center"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">En un emprendimiento, e</span></span></span></strong><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">l riesgo es inevitable. Siempre habr&aacute; una cuota de riesgo, de sorpresas, de factores imponderables.</span></span></span></strong></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">&Eacute;st</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e ser&iacute;a el emprendedor que se toma a&ntilde;os</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">par</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a estudiar a fondo el mercado y hace c&aacute;lculos h&iacute;per detallados de todos sus costos. Invierte</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">demasiad</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o tiempo en elucubraciones abstractas</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">cuand</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o todav&iacute;a ni siquiera ha experimentado si su</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">product</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o o servicio satisface a los clientes.</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">S</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e deben evitar estas posturas extremas. Es sumamente aconsejable hacer un PN a conciencia y</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">co</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n buena informaci&oacute;n de base sin llegar a invertir</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">u</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n tiempo desmedido en ello.</span></span></span></span></span></p>', NULL, NULL, '2018-02-13 15:01:49', '2018-02-13 15:01:49', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (5, 'El emprendedor', 3, 3, '<p>Materia I</p>', '<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Son varios los adjetivos con los que se se&ntilde;alan a los emprendedores: pasi&oacute;n, riesgo, trascendencia, ambici&oacute;n, iniciativa, perseverancia e innovaci&oacute;n son algunos de los calificativos que podr&iacute;amos utilizar para describirlos, todos ellos con raz&oacute;n y fundamento. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Pero de todos los adjetivos que podr&iacute;amos otorgar a un emprendedor, tal vez el de &ldquo;constructor&rdquo; es el que m&aacute;s lo identifica dentro del campo de la construcci&oacute;n de una organizaci&oacute;n humana que va m&aacute;s all&aacute; de su idea, producto o servicio, o incluso la oportunidad que le dio origen al proyecto.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">El emprendedor construye una visi&oacute;n y la convierte en un negocio.</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Construir, ser arquitecto de una idea y llevarla al mundo de los negocios no es tarea sencilla; son muchos, y seguir&aacute;n siendo, los que han intentado con mayor o menor &eacute;xito construir un negocio a partir de una idea. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Pero si observamos las historias de los grandes emprendedores, existe un claro denominador com&uacute;n, algo que en todos est&aacute; presente al inicio y que se conforma como la pieza clave en el emprendimiento independientemente del m&eacute;todo o el camino utilizado. Nos referimos a los sue&ntilde;os.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">Cada emprendimiento es un viaje, a veces a lo desconocido, pero es la pasi&oacute;n de cada uno la que hace que este viaje valga la pena, por nosotros, por los nuestros.</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Dicen que el sabor del &eacute;xito es dulce y sin duda as&iacute; es, pero es tambi&eacute;n un camino complejo con cierta amargura que requiere de planificaci&oacute;n, conocimiento, aprendizaje y una inicial voluntad de evolucionar. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">No es un viaje sencillo, pero como dec&iacute;a el fil&oacute;sofo chino Lao-Ts&eacute; en el siglo IV antes de cristo: </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">&ldquo;Un viaje de mil millas, comienza por el primer paso&rdquo;.</span></span></strong></span></span></p>', NULL, NULL, '2018-02-13 15:04:35', '2018-02-13 15:04:35', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (6, 'El Reto del Emprendedor', 5, 4, '<p style="margin-left:0cm; margin-right:0cm"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">&iquest;Qu&eacute; distingue a un emprendedor de otros trabajadores m&aacute;s convencionales? En esta lecci&oacute;n introductoria se comienza a explorar la importancia y cualidades y competencias del emprendedor.</span></span></span></span></p>', '<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Cualquiera que haya trabajado en una empresa peque&ntilde;a o grande sabe que para lograr el objetivo de vender productos o servicios es necesaria una estructura, m&aacute;s peque&ntilde;a o m&aacute;s grande, que resuelva eficazmente las tareas de administrar, producir y comercializar requeridas para el normal desenvolvimiento del proyecto. </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Si, adem&aacute;s, la empresa est&aacute; en crecimiento, tendr&aacute; la oportunidad de experimentar el desarrollo de &aacute;reas m&aacute;s espec&iacute;ﬁcas y diversiﬁcadas como la ﬁnanciera, el marketing y la comunicaci&oacute;n, la log&iacute;stica y las operaciones, etc.</span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Cuando decidimos convertirnos en emprendedores, cuando decidimos convertir un sue&ntilde;o en realidad, tenemos por delante el desaf&iacute;o de tener que sostener con perseverancia y optimismo la tensi&oacute;n que genera el proceso de construcci&oacute;n organizacional dentro de un entorno muchas veces incierto y siempre con recursos escasos.</span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">Cuando decidimos convertir un sue&ntilde;o en realidad, tenemos por delante el desaf&iacute;o de tener que sostener con perseverancia y optimismo nuestro emprendimiento.</span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Seguramente, antes de pensar en emprender, nos hemos preguntado si el riesgo vale la pena: &iquest;voy a dejar la seguridad de mi trabajo por asumir el riesgo que conlleva emprender? </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Ser parte de la seguridad de una organizaci&oacute;n o crear una propia es una de las disyuntivas a las que las personas se enfrentan al decidir si quieren desarrollar un emprendimiento o si preﬁeren emplearse. </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Cabe decir que cuando nos planteamos esta pregunta estamos dando un primer paso, peque&ntilde;o pero importante. Si somos capaces de contestar &ldquo;s&iacute;, quiero crear mi propia empresa&rdquo;&ndash;, ya nos hemos subido al tren del emprendimiento, ya hemos tomado el primer impulso y una decisi&oacute;n de gran importancia.</span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Pero &iquest;qu&eacute; es lo que impulsa a las personas a asumir esos riesgos? &iquest;Qu&eacute; distingue a un emprendedor del resto de personas? &iquest;Qu&eacute; cualidades son las que necesitamos para ser verdaderos emprendedores?</span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Mucho se ha debatido y hablado sobre lo que se considera un emprendedor y si este nace o se hace. </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">Algunos han puesto el foco en las personas y sus cualidades, otros en su entorno y las condiciones que han permitido que el crecimiento de nuevas empresas sea m&aacute;s prol&iacute;fero. </span></span></span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">No obstante, cada vez son m&aacute;s los que piensan que se puede mejorar e incrementar la masa emprendedora de un pa&iacute;s gracias a las nuevas tecnolog&iacute;as, el entorno socio econ&oacute;mico, los cambios en los valores sociales, las nuevas oportunidades y las competencias individuales, muchas de ellas desarrolladas con el aprendizaje y la formaci&oacute;n</span></span></span></span></span></p>', 'recursos/paginas/g_etn_anteshacer_b.jpg', NULL, '2018-02-13 15:06:11', '2018-02-13 15:06:11', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (7, 'El Entorno Emprendedor', 5, 4, '<p style="margin-left:0cm; margin-right:0cm"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">El emprendedor no solo es moldeado por su entorno, tambi&eacute;n debe convertirse en un agente de cambio para poder construir y desarrollar su negocio. En esta lecci&oacute;n se mencionan los factores que estimulan al emprendedor y al emprendimiento.</span></span></span></span></p>', '<p style="margin-left:0cm; margin-right:0cm"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Son varios y diversos los factores que estimulan al emprendedor y al emprendimiento. Tres palabras son claves aqu&iacute;: motivaci&oacute;n, actitud y aptitudes.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Si todos estos factores se presentan de forma positiva, se crea el espacio y momento id&oacute;neos para que el emprendimiento generado crezca dentro del pa&iacute;s.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Como podemos ver, el emprendedor no nace, se hace, pero con unos factores que no solo dependen de &eacute;l, sino tambi&eacute;n de su entorno social, cultural y econ&oacute;mico.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Ser un emprendedor es ir m&aacute;s all&aacute; de lo que convencionalmente se considera a un artista o un artesano. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#1f3864">El emprendedor, a diferencia de otros, se propone desarrollar una estructura, un modelo de negocio, que trascienda el producto, el servicio o la t&eacute;cnica que origina el proyecto.</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">El emprendedor, por lo tanto, requiere adquirir e incorporar conocimientos, competencias y recursos para cubrir las funciones que su modelo le va a exigir: administrar, comercializar, producir de una manera diversificada con el objetivo de crecer.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Est&aacute; claro, entonces, que ya no se trata solamente de construir un producto o servicio, sino de dise&ntilde;ar un negocio capaz de introducir ese producto o servicio en el mercado y lograr que perdure, sobreviva y crezca.</span></span></span></p>', 'recursos/paginas/g_etn_competencias_b.jpg', 'recursos/paginas/g_etn_modelos.pdf', '2018-02-13 15:09:45', '2018-02-13 15:09:45', 1, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (8, 'El Plan de negocios, parte por parte', 4, 3, '<p style="margin-left:0cm; margin-right:0cm"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Materia&nbsp;</span></span></span></p>', '<p style="margin-left:0cm; margin-right:0cm"><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">Contenid</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">o de un Plan de negocio t&iacute;pico</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Com</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o indicamos anteriormente, el Plan de negocio es un documento que debe reflejar todas las dimensiones</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">clav</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e del negocio. &iquest;Qu&eacute; debe contener ese documento?</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">U</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">n &iacute;ndice orientativo de los cap&iacute;tulos que deber&iacute;a contener ese documento ser&iacute;a:</span></span></span></span></span></p>

<ol>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l resumen ejecutivo</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l mercado, el contexto y el sector</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l producto/servicio</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l plan comercial</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l plan de operaciones</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l equipo emprendedor</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">FOD</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">A y an&aacute;lisis de riesgos</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l plan financiero</span></span></span></span></span></li>

	<li><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Anexos</span></span></span></span></span></li>

</ol>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:center"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><strong><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#4472c4">En los siguientes contenidos se expondr&aacute; con m&aacute;s detalle que informaci&oacute;n debe tener cada uno de los aspectos de un plan de negocios de un emprendimiento.</span></span></span></strong></span></span></p>

<ol>

	<li>

	<h3><span style="font-size:12pt"><span style="font-family:&quot;Times New Roman&quot;,serif"><strong><span style="font-size:16.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">E</span></span></span></strong><strong><span style="font-size:16.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:black">l resumen ejecutivo</span></span></span></strong></span></span></h3>

	</li>

</ol>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">E</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">l Plan de Negocio comienza con un resumen ejecutivo que</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">deber&iacute;</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a tener no m&aacute;s de 3 p&aacute;ginas. </span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">All</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">&iacute; hay que destacar las caracter&iacute;sticas distintivas del proyecto y los principales argumentos para lograr el &eacute;xito.</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">S</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">i el plan est&aacute; dirigido a posibles inversores, estas</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">primera</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s p&aacute;ginas deben estar escritas de manera</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">atractiv</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a para captar la atenci&oacute;n del lector. Porque</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">s</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">i le interesa, seguir&aacute; leyendo el resto.</span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Est</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">a parte del plan, si bien se presenta al principio del documento, es lo &uacute;ltimo que hay que escribir. </span></span></span></span></span></p>

<p style="margin-left:5.95pt; margin-right:0cm; text-align:justify"><span style="font-size:10.5pt"><span style="font-family:Arial,sans-serif"><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">Convien</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">e escribirla despu&eacute;s de haber</span></span></span> <span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">completad</span></span></span><span style="font-size:11.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#231f20">o todos los otros cap&iacute;tulos</span></span></span></span></span></p>', 'recursos/paginas/g_etn_cuandoescribe_b.jpg', NULL, '2018-02-13 15:14:30', '2018-02-13 15:21:03', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (9, 'Competencias y Habilidades de todo emprendedor', 8, 4, '<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-size:12.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">En esta lecci&oacute;n, se muestran las competencias que todo emprendedor necesita para desarrollar su negocio. Poseerlas y desarrollarlas ayudara a que el negocio que se desea emprender tenga &eacute;xito. Te invitamos a profundizar en la siguiente lecci&oacute;n.</span></span></span></span></p>', '<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Hay una serie de competencias y habilidades personales que todo empresario o emprendedor debe asumir y cultivar, independientemente de la idea o del modelo de negocio que tengamos en mente proyectar.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">1. Comunicar:</span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> En los negocios, es innegable la necesidad de transmitir de forma adecuada. La comunicaci&oacute;n es unos de los puntos clave, ya sea con nuestros colaboradores, proveedores, clientes, socios, etc. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Empezando por transmitir cu&aacute;l es mi modelo de negocio, valores, misi&oacute;n y visi&oacute;n.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">2. Crear e innovar: </span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">todo empresario debe estar concentrado en crear nuevos proyectos, en ser innovador. Tener esta mentalidad, esta actitud de construir de forma constante, es la que mueve a las empresas a mejorar y a crecer.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">3. Trabajar en equipo:</span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> Dif&iacute;cilmente los proyectos se pueden ejecutar de forma solitaria, hay que construir tambi&eacute;n equipos; liderarlos es una tarea compleja y es innegable que las personas son el valor m&aacute;s preciado de las organizaciones.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">4. Asumir riesgos:</span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> Al emprender, ya estamos asumiendo riesgos. Las empresas de forma habitual requieren tomar decisiones y, en ocasiones, se requerir&aacute; de mucho valor para defender y seguir adelante con nuestro proyecto.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">5. Negociar:</span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> El empresario debe adquirir la habilidad de negociar en muchos sentidos, con los colaboradores, proveedores, socios y otros agentes externos e internos, para obtener resultados.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">6. Liderar:</span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> Iniciar un negocio no es empezar a ser jefe y mandar, es una oportunidad de liderar un proyecto y saber influir en las personas de nuestro alrededor para que aporten y contribuyan con lo mejor de sus capacidades y talento a fin de conseguir los objetivos trazados. Un buen l&iacute;der es seguido por sus colaboradores, esta es la relaci&oacute;n que debemos conseguir.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif">7. Ser curioso:</span></strong><span style="font-family:&quot;Century Gothic&quot;,sans-serif"> La curiosidad, a trav&eacute;s de la constante investigaci&oacute;n, hace del empresario una persona con capacidad de mejorar, corregir y vislumbrar las tendencias de futuro que le permitir&aacute;n adaptar su compa&ntilde;&iacute;a al ma&ntilde;ana.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Por &uacute;ltimo, cabe destacar que existe un proceso inicial previo a convertirse en empresario. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Todos los empresarios empezaron con una idea, sencilla o compleja, de f&aacute;cil entendimiento o incomprendida, una idea que m&aacute;s all&aacute; de su posterior transformaci&oacute;n es el producto gestado de un sue&ntilde;o de una persona que aspira a convertirla en una realidad empresarial. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Pero no hay que &ldquo;enamorarse&rdquo; de esta idea primera si realmente queremos convertirla en un negocio. </span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:center"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">&ldquo;Recordemos que una</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">idea puede ser grande,</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">pero llevarla al plano de los negocios conlleva</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">&ldquo;</span></span></span></strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">compartir&rdquo; dicha idea</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">con los usuarios</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">futuros, aquellos que la</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">convertir&aacute;n en un</span></span></span></strong><strong> </strong><strong><span style="font-size:14.0pt"><span style="font-family:&quot;Century Gothic&quot;,sans-serif"><span style="color:#2f5496">negocio.&rdquo;</span></span></span></strong></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Tenemos que validar que nuestra idea, ese sue&ntilde;o con forma de idea, sea viable, factible y deseable.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Para que cumpla estos tres criterios, debemos echar un vistazo a ese espacio que llamamos mercado, donde est&aacute;n las personas que adquieren productos, y preguntarnos si van a adquirir el nuestro: &iquest;Les gustar&aacute;? &iquest;Lo necesitar&aacute;n lo suficiente como para comprarlo? &iquest;Querr&aacute;n pagar lo que les pedir&eacute;?</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">Es por eso que es tan importante para construir un modelo de negocio que funcione, observar, indagar, conocer y entender a los que ser&aacute;n nuestros clientes.</span></span></span></p>

<p style="margin-left:0cm; margin-right:0cm; text-align:justify"><span style="font-size:11pt"><span style="font-family:Calibri,sans-serif"><span style="font-family:&quot;Century Gothic&quot;,sans-serif">As&iacute;, primero, con nuestra idea en mente, descubrimos las oportunidades que el mercado nos ofrece, permiti&eacute;ndonos moldear nuestra idea hasta que &eacute;sta cumpla con las expectativas de las personas que m&aacute;s adelante se convertir&aacute;n en nuestros clientes.</span></span></span></p>', NULL, NULL, '2018-02-13 15:24:43', '2018-02-13 15:24:43', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (10, 'INTELIGENCIA EMOCIONAL', NULL, 1, '<p>Programa de Inteligencia Emocional dirigido a todo tipo de personas, con o sin experiencia profesional para identificar las conductas necesarias que promuevan un mejor desempe&ntilde;o emocional.</p>', '<p>&iquest;que se coloca en esta secci&oacute;n 3 CONTENIDO?</p>', 'recursos/paginas/flor2.jpg', 'recursos/paginas/g_etn_modelos.pdf', '2018-02-17 17:06:33', '2018-02-17 17:06:33', 1, 1, 3, NULL);
INSERT INTO certi_pagina VALUES (11, 'Creativiad, una aproximación', 10, 2, '<p>adfadfafsaf</p>', '<p>adfadf</p>', 'recursos/paginas/flor3.jpg', 'recursos/paginas/177diferenciarseomorir%5B1%5D.pdf', '2018-02-17 17:10:40', '2018-02-17 17:10:40', 1, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (12, 'Pensamiento Estratégico', NULL, 1, '<p>El pensamiento estrat&eacute;gico es entender y conectar el trabajo cotidiano con la estrategia del negocio. Es por ello que todo pensador estrat&eacute;gico sabe a d&oacute;nde quiere llegar, d&oacute;nde se encuentra ubicado, hacia d&oacute;nde quiere ir y est&aacute; en la capacidad de corregir la direcci&oacute;n de la empresa si esto fuese necesario creando estrategias competitivas e innovadoras.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/admin/web/ckfinder/userfiles/images/AG_PE_BIENVENIDA.jpg" style="width:820px" /></p>

<p><strong><em>El pensamiento estrat&eacute;gico</em></strong>&nbsp;es entender y conectar el trabajo cotidiano con la estrategia del negocio. Es por ello que todo pensador estrat&eacute;gico sabe a d&oacute;nde quiere llegar, d&oacute;nde se encuentra ubicado, hacia d&oacute;nde quiere ir y est&aacute; en la capacidad de corregir la direcci&oacute;n de la empresa si esto fuese necesario creando estrategias competitivas e innovadoras.</p>

<p>Hoy por hoy es muy importante que dispongas de un&nbsp;<em>pensamiento estrat&eacute;gico</em>&nbsp;que te permitir&aacute; aplicarlo a tu desarrollo profesional como a la empresa en la cual trabaja.</p>

<p>Entender el entorno econ&oacute;mico y de la industria, las tendencias tecnol&oacute;gicas y empresariales as&iacute; como la posici&oacute;n competitiva de cada empresa es lo que permite dise&ntilde;ar la forma como competir.</p>

<p>El&nbsp;<strong><em>Curso de Pensamiento estrat&eacute;gico</em></strong>&nbsp;es un programa de desarrollo a distancia, que basado en las experiencias m&aacute;s exitosas de las mejores escuelas de negocios,&nbsp; ha depurado lo m&aacute;s selecto de los principales programas y pensadores e incorporado tecnolog&iacute;as de informaci&oacute;n, para generar un espacio de formaci&oacute;n permanente las 24 horas del d&iacute;a, los 7 d&iacute;as de la semana, sin barreras de lugar ni tiempo, con la finalidad de formar l&iacute;deres integrales con una visi&oacute;n global, potenciando la capacidad de pensar estrat&eacute;gicamente, con el fin de estructurar planes que conduzcan al &eacute;xito y a la sustentabilidad -a mediano y largo plazo- de la empresa.</p>

<p>Con el entrenamiento adecuado, todos somos capaces de conectar nuestras acciones diarias con los objetivos del negocio, y generar escenarios futuros junto a estrategias competitivas e innovadoras.</p>

<h3 style="text-align:center">&iexcl;Te deseamos el mayor de los &eacute;xitos!</h3>', 'recursos/paginas/g_c_pe.jpg', NULL, '2018-03-21 18:18:46', '2018-03-27 18:15:27', 2, 1, 4, NULL);

INSERT INTO certi_pagina VALUES (13, 'Pensamiento estratégico', 12, 2, '<p>DESCRIPCI&Oacute;N M&Oacute;DULO</p>', '<h2 style="text-align:center"><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_PensamientoEstrategico.jpg" style="width:820px" /></h2>

<h2 style="text-align:center">Pensamiento Estrat&eacute;gico: Competencia del L&iacute;der</h2>

<p>El tema del&nbsp;<em><strong>pensamiento estrat&eacute;gico</strong></em>&nbsp;tiene plena pertinencia en el desarrollo de las competencias gerenciales para el liderazgo, ya que &eacute;ste le posibilita al l&iacute;der&nbsp;<em><strong>definir</strong></em>&nbsp;la situaci&oacute;n actual,&nbsp;<em><strong>evaluar</strong></em>&nbsp;el entorno competitivo y definir las estrategias que le permitir&aacute;n encaminar a sus seguidores en funci&oacute;n de los objetivos planteados por la organizaci&oacute;n.</p>

<p>El&nbsp;<em><strong>pensamiento estrat&eacute;gico&nbsp;</strong></em>es una competencia que se debe desarrollar en los l&iacute;deres de las empresas, para que estos cuenten con la capacidad de asumir los retos inherentes al entorno en el que se desenvuelve la organizaci&oacute;n y sepan guiar a sus subalternos hacia el logro de las metas previstas. Un pensador estrat&eacute;gico eficaz, tiene la capacidad de conectar sus acciones diarias con los objetivos del negocio, debe crear visiones de escenarios futuros y estrategias competitivas e innovadoras para hacerle frente.</p>

<p>En este programa, conocer&aacute;s los principios del pensamiento estrat&eacute;gico. Entenderemos las diferentes herramientas que&nbsp;se utilizan&nbsp;a trav&eacute;s del pensamiento estrat&eacute;gico, para evaluar las circunstancias de la empresa y del entorno, y as&iacute; dise&ntilde;ar los planes que hacen m&aacute;s competitiva a la empresa.</p>

<h3 style="text-align:center">&iexcl;Bienvenido a este programa y esperamos que lo aproveches!</h3>', NULL, NULL, '2018-03-21 18:20:59', '2018-03-27 17:52:37', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (14, 'Importancia', 13, 3, '<p>DESCRIPCI&Oacute;N MATERIA</p>', '<p><iframe frameborder="0" height="1100" scrolling="no" src="https://recursos2puntocero.com/recursos/F_PmtoEstrag_Mo1_Mt1_M1_6elemtos/6elementos.html" width="820"></iframe></p>

<h4>A lo largo del programa contar&aacute;s con la oportunidad de capacitarte en todos estos aspectos. Te invitamos a conocerlos y aplicarlos en tu &aacute;mbito laboral</h4>', NULL, NULL, '2018-03-21 18:28:30', '2018-03-27 18:38:17', 2, 1, 1, NULL);

INSERT INTO certi_pagina VALUES (15, 'Definiciones', 14, 4, '<p>El pensamiento estrat&eacute;gico es una competencia gerencial que implica el entendimiento de las circunstancias propias de la empresa, su entorno y su relaci&oacute;n con la estrategia, concatenados con la visi&oacute;n de la organizaci&oacute;n. El pensamiento estrat&eacute;gico es el primer paso para elaborar la planificaci&oacute;n estrat&eacute;gica. En el siguiente recurso podr&aacute;s conocer m&aacute;s acerca de este tema.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_definicion2.jpg" style="width:820px" /></p>

<p>La idea del&nbsp;<strong>pensamiento estrat&eacute;gico</strong>&nbsp;proviene de Henry Mintzberg, un l&iacute;der pensador en el campo de la gesti&oacute;n estrat&eacute;gica. Mintzberg considera a la creaci&oacute;n de una estrategia como la concreci&oacute;n y realizaci&oacute;n de una&nbsp;<strong>visi&oacute;n</strong>. Es decir, el&nbsp;<strong><em>pensamiento estrat&eacute;gico</em></strong>&nbsp;<strong><em>es un proceso mediante el cual una persona aprende a convertir la&nbsp;visi&oacute;n&nbsp;de la empresa en una realidad.</em></strong></p>

<p>El pensamiento estrat&eacute;gico se trata de un&nbsp;<strong><em>proceso reflexivo</em></strong>&nbsp;que determina la intenci&oacute;n y el perfil de lo que la organizaci&oacute;n quiere llegar a ser.&nbsp;<strong><em>El pensamiento estrat&eacute;gico es quien determina la estrategia a seguir.</em></strong></p>

<h2>Pensamiento Estrat&eacute;gico</h2>

<p>El pensamiento estrat&eacute;gico determina la&nbsp;<em><strong>perspectiva futura&nbsp;</strong></em>de la empresa, a la vez que establece las bases sobre las que se asentar&aacute;n todas las decisiones de planeaci&oacute;n. Se enfoca en los&nbsp;<em><strong>procesos</strong></em>&nbsp;que dan lugar al desarrollo de la misi&oacute;n de la misma, su visi&oacute;n, sus principios, valores y estrategias.</p>

<p>Es un conjunto de herramientas &uacute;tiles que todo l&iacute;der o gerente debe cultivar, es una inversi&oacute;n de valor incalculable, principalmente porque el pensamiento estrat&eacute;gico tiene que ver con la consecuci&oacute;n de unos objetivos y la resoluci&oacute;n de sus problemas inherentes, dentro de un marco contextual concreto.</p>

<p>Poseer las t&eacute;cnicas que permiten el desarrollo de un pensamiento estrat&eacute;gico es como tener una llave para abrir la puerta que queremos. As&iacute; pues, cultivarlo es mucho m&aacute;s sencillo de lo que puede parecer. No es algo de genios, es un resultado del trabajo y el sentido com&uacute;n. Recuerda, el pensamiento estrat&eacute;gico se basa, esencialmente, en el conocimiento y el an&aacute;lisis.</p>

<p>El&nbsp;<em><strong>pensamiento estrat&eacute;gico</strong></em>&nbsp;es un proceso de razonamiento aplicado a sistemas o problemas complejos, con miras a lograr un objetivo concreto. Este tipo de razonamiento pretende reducir la incertidumbre, minimizar riesgos y maximizar oportunidades, a trav&eacute;s de un conjunto de m&uacute;ltiples procedimientos de an&aacute;lisis y aprendizaje.&nbsp;</p>

<p>Pensar estrat&eacute;gicamente significa sentirse inc&oacute;modos, insatisfechos, inquietos, atentos, viendo lo que pasa a nuestro alrededor, innovando y movi&eacute;ndonos permanentemente. Por tanto, es importante conservar&nbsp; la mente tranquila y serena, conociendo el &ldquo;hacia d&oacute;nde vamos&rdquo;. Esto no es f&aacute;cil, requiere de mucho esfuerzo y trabajo.</p>

<p>El pensamiento estrat&eacute;gico tiene como objetivo saber anticiparse a los acontecimientos, visualizar un destino, construirlo y alcanzar el futuro que se considera m&aacute;s conveniente para una persona, sociedad, empresa o naci&oacute;n.</p>

<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_Pensar_estrategicamente.jpg" style="width:820px" /></p>

<hr />
<h4>En este video identifica las competencias de pensamiento estrat&eacute;gico que muestra el protagonista quien encara al innovador y exc&eacute;ntrico Howard Hughes</h4>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/G_PE_Aviador.mp4" type="video/mp4" /></video>
</p>

<p>To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>

<p>&nbsp;</p>', 'recursos/paginas/f_definicion_b.jpg', NULL, '2018-03-21 18:36:24', '2018-03-27 18:18:08', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (16, 'Pensamiento vs. Planificación', 14, 4, '<p>El pensamiento estrat&eacute;gico se confunde frecuentemente con la planificaci&oacute;n estrat&eacute;gica. A continuaci&oacute;n, notaremos que son conceptos diferentes, aunque para hacer planificaci&oacute;n estrat&eacute;gica sea necesario el pensamiento estrat&eacute;gico. Te invitamos a conocer la diferencia entre estos dos importantes conceptos, a trav&eacute;s del siguiente recurso.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_PensamientoVsPlanificacion.jpg" style="width:820px" /></p>

<p>Es com&uacute;n toparnos con dos conceptos que suenan similares pero que denotan cosas diferentes,&nbsp;nos referimos a:&nbsp;<em><strong>pensamiento estrat&eacute;gico y planificaci&oacute;n estrat&eacute;gica.</strong></em></p>

<p>El&nbsp;<em><strong>pensamiento estrat&eacute;gico</strong></em>&nbsp;est&aacute; asociado (como bien lo dice la palabra) a pensar, so&ntilde;ar, visualizar, proyectar, idear, pensar, estimar, etc. Pero la planificaci&oacute;n estrat&eacute;gica se asocia m&aacute;s a planes, actividades, tareas, presupuestos y responsables, en el marco de una organizaci&oacute;n. Son dos espacios y actividades diferentes que si no guardan sinton&iacute;a, ocasionan los grandes problemas del entorno estrat&eacute;gico.</p>

<p>El uno sin el otro es tremendamente negativo para las empresas: tener mucho pensamiento estrat&eacute;gico sin planificaci&oacute;n, se traduce en empresas que viven so&ntilde;ando, que tienen muchas ideas de productos, servicios, promociones, pero carecen de resoluci&oacute;n y concreci&oacute;n.</p>

<p>Por otro lado, el tener planificaci&oacute;n estrat&eacute;gica sin un pensamiento estrat&eacute;gico, puede llevar a la empresa a un gran pragmatismo, una rutina incesante basada en hacer programas de producci&oacute;n, presupuestos y ejecuci&oacute;n de forma c&iacute;clica, sin la claridad de un norte y sin evaluar de forma cierta la competitividad y el entorno econ&oacute;mico. Empresas que cuentan con una planificaci&oacute;n estrat&eacute;gica llevada en forma deficiente, pueden no tener su foco competitivo claro y perder mercado.</p>

<p>El&nbsp;<em><strong>pensamiento estrat&eacute;gico&nbsp;</strong></em>es una competencia que debe ser desarrollada en los l&iacute;deres de todas las organizaciones, para que estos entiendan las condiciones del contexto en el cual se desenvuelve la empresa y el impacto que guarda la labor ejercida por cada uno de los colaboradores en la concreci&oacute;n de los objetivos propuestos. Un pensador estrat&eacute;gico eficaz tiene la capacidad de conectar sus acciones diarias con los objetivos a largo plazo previstos por el modelo de negocio, proyectando escenarios futuros y estrategias competitivas e innovadoras para hacerles frente.</p>

<p>Las competencias que debe desarrollar un pensador estrat&eacute;gico son: pensamiento anal&iacute;tico, pensamiento conceptual, b&uacute;squeda de informaci&oacute;n y orientaci&oacute;n al negocio.</p>

<p>En este sentido, es vital para el funcionamiento efectivo de una organizaci&oacute;n que los colaboradores sean capaces de responder lo siguiente:<em><strong>&nbsp;&iquest;C&oacute;mo su trabajo habitual y cotidiano tributa en el cumplimiento de las estrategias previstas por la empresa?&nbsp;</strong></em>As&iacute; pues, la respuesta a dicha interrogante implica desarrollar el pensamiento estrat&eacute;gico.</p>

<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_AbrahamLincoln.jpg" style="width:820px" /></p>

<p>En la siguiente lectura se expone un caso de la vida real, en la cual se muestra la falta de pensamiento estrat&eacute;gico por parte de la industria relojera suiza. Te invitamos a leerla en el siguiente&nbsp;<a href="https://recursos2puntocero.com/recursos/FORMACION/pdf/reloj_suizo.pdf" target="_blank">PDF</a>. Reflexiona sobre el impacto del pensamiento estrat&eacute;gico en el futuro de los negocios.</p>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/F_PensamientoVsPlanificacion.webm" type="video/webm" /></video>
</p>

<p>To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>', 'recursos/paginas/f_pensamientovs.planificacion_b.jpg', NULL, '2018-03-21 18:39:22', '2018-03-27 18:22:12', 2, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (17, 'Actividades', 14, 4, '<p>La clave de un pensador estrat&eacute;gico es vincular sus acciones cotidianas en funci&oacute;n de la visi&oacute;n, contrastando el entorno, la posici&oacute;n competitiva y los diferentes caminos que permiten el logro de las metas de la organizaci&oacute;n. A continuaci&oacute;n se presentan los 19 h&aacute;bitos de estrategias inteligentes que puedes desplegar de forma cotidiana.</p>', '<p><iframe frameborder="0" height="1300" scrolling="no" src="https://recursos2puntocero.com/recursos/F_PmtoEstrag_Mo1_Mt1_R3_19HabitIntlgte/19habitos.html" width="820"></iframe></p>', 'recursos/paginas/f_actividades_b.jpg', NULL, '2018-03-21 18:44:00', '2018-03-27 18:23:41', 2, 1, 3, NULL);
INSERT INTO certi_pagina VALUES (18, 'Capacidades para el proceso estratégico', 14, 4, '<p>Los ejecutivos y directivos son los encargados de encauzar las premisas establecidas por la visi&oacute;n de una organizaci&oacute;n, comunic&aacute;ndolas a los trabajadores de forma persistente. As&iacute; pues, los l&iacute;deres de procesos deben otorgar facultades a los participantes de niveles bajos, de tal manera que puedan crear estrategias alineadas con la visi&oacute;n de la compa&ntilde;&iacute;a, ayud&aacute;ndolos a definir acerca de qu&eacute; tan bien est&aacute;n cumpliendo sus labores. Te invitamos a conocer las cinco capacidades del proceso estrat&eacute;gico, esbozado por Warren Bennis, a trav&eacute;s del siguiente recurso.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_Capacidades.jpg" style="width:820px" /></p>

<p>Warren Bennis, conocido estudioso del&nbsp;<em><strong>liderazgo</strong></em>, asegura que los directores generales de las organizaciones que despliegan las cinco capacidades establecidas por el proceso estrat&eacute;gico, obtienen el &eacute;xito. En este sentido, te invitamos a conocer cada una de ellas.</p>

<p>La primera es la&nbsp;<strong>Visi&oacute;n.&nbsp;</strong>Capacidad para crear y expresar una visi&oacute;n obligatoria de un estado deseado para las cosas, y de impartir claridad a la misma, a trav&eacute;s de la suscripci&oacute;n de un compromiso con ella.</p>

<p>La segunda es la&nbsp;<strong>Comunicaci&oacute;n y &nbsp;alineaci&oacute;n.&nbsp;</strong>Capacidad para expresar la visi&oacute;n a los miembros de la organizaci&oacute;n, consiguiendo el apoyo de sus m&uacute;ltiples bases.</p>

<p>La tercera es la&nbsp;<strong>Persistencia, consistencia y enfoque.&nbsp;</strong>Capacidad para conservar el rumbo de la organizaci&oacute;n, sobre todo cuando las cosas se ponen dif&iacute;ciles.</p>

<p>La cuarta es la&nbsp;<strong>Delegaci&oacute;n de facultades.</strong>&nbsp;Capacidad para crear ambientes que puedan explotar y encauzar las energ&iacute;as y las facultades necesarias para producir los resultados deseados.</p>

<p>La quinta es el&nbsp;<strong>Aprendizaje de la organizaci&oacute;n.</strong>&nbsp;Capacidad para encontrar v&iacute;as que le permitan a los l&iacute;deres supervisar los procesos de la organizaci&oacute;n, relacionar los resultados con los objetivos establecidos, crear y usar informaci&oacute;n actualizada para la revisi&oacute;n de acciones pasadas y fundamentar las futuras, as&iacute; como decidir c&oacute;mo y cu&aacute;ndo la estructura de la empresa y el personal clave, deben ser sujetos a reestructuraci&oacute;n o reasignaci&oacute;n ante condiciones nuevas.</p>

<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/Podcast.jpg" style="width:100%" /></p>', 'recursos/paginas/f_capacidades_b.jpg', NULL, '2018-03-21 18:52:52', '2018-03-27 18:31:16', 2, 1, 4, NULL);
INSERT INTO certi_pagina VALUES (20, 'Corto y largo plazo', 13, 3, '<p>Prueba</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_CortoYLargoPlaza.jpg" style="width:820px" /></p>

<h2>Pensamiento estrat&eacute;gico a corto y largo plazo</h2>

<p>La capacidad de diferenciar entre el&nbsp;<em><strong>pensamiento a corto y a largo plazo</strong></em>, y de lograr un equilibrio entre los dos, es parte integral de la estrategia. Comprende la importancia de ambos tipos de pensamiento y as&iacute; podr&aacute;s obtener la combinaci&oacute;n correcta.</p>

<h2>Conocer los escollos</h2>

<p>Si te concentras por completo en el &eacute;xito a corto plazo, te arriesgaras al fracaso en el largo plazo. Por ejemplo, puedes descubrir que vendes productos obsoletos o atacas mercados cuyos requerimientos han cambiado. Si haces un indebido &eacute;nfasis en la planificaci&oacute;n a largo plazo, inevitablemente los negocios resultar&aacute;n perjudicados.</p>

<p>La clave es que<em><strong>&nbsp;te concentres en el presente y vigiles el futuro&nbsp;</strong></em>para asegurar que las buenas decisiones actuales sean ben&eacute;ficas en el ma&ntilde;ana.</p>

<ul>
	<li>Ten confianza en el futuro pero se realista con respecto a lo que puedes lograr.</li>
	<li>Esfu&eacute;rzate por cumplir las metas a largo plazo y alcanzar resultados inmediatos.</li>
</ul>

<h2>Considerar el largo plazo</h2>

<p>Algunas veces el cierre de una venta r&aacute;pida no es positivo para los intereses a largo plazo de la empresa. Aunque esto pueda significar que te toque enfrentar una situaci&oacute;n dif&iacute;cil con tu cliente a corto plazo, es m&aacute;s probable que le proporciones la m&aacute;xima satisfacci&oacute;n en el largo plazo.</p>

<h2>Lograr el equilibrio</h2>

<p>Alcanzar el equilibrio adecuado entre el pensamiento a corto y a largo plazo requiere&nbsp;<em><strong>esfuerzo</strong></em>&nbsp;y&nbsp;<em><strong>disciplina</strong></em>. No es sorprendente que, a menos que un equipo est&eacute; decidido a asignar tiempo a los asuntos estrat&eacute;gicos, las tareas operativas de corto plazo tengan siempre prioridad.</p>

<p>Programa el tiempo y los recursos apropiados para las operaciones o la estrategia, y nunca olvides respetar tu plan. Por ejemplo, puedes decidir reservar tres meses al desarrollo de una nueva estrategia, dedicando dos d&iacute;as a las reuniones de arranque y un d&iacute;a a la semana para las posteriores. Esto deja tiempo suficiente para que puedas ocuparte de los aspectos operativos.</p>

<h2>Trabajar con estrategia</h2>

<p><em><strong>La estrategia es un proceso continuo</strong></em>. Recuerda, no puedes descuidar la planificaci&oacute;n en el futuro. Programa un d&iacute;a al mes para analizar con el equipo su mantenimiento y desarrollo. Habla con los clientes regularmente y estudia los papeles de los miembros de tu equipo. Trabaja en el plan con cierta frecuencia para conservar su frescura y asegurarte de de que los miembros del equipo permanezcan comprometidos y concentrados.</p>

<ul>
	<li>Esfu&eacute;rzate por integrar el pensamiento estrat&eacute;gico en tu vida cotidiana.</li>
	<li>Toma decisiones considerando las implicaciones a largo plazo.</li>
</ul>

<hr />
<h4>Te invitamos a conocer m&aacute;s sobre los intereses de los clientes y los motivadores de la compra. En el siguiente PDF podr&aacute;s un resumen del libro&nbsp;<strong>Por qu&eacute; compramos</strong>, escrito por Paco Underhill, que te ser&aacute; de utilidad para conocer aspectos claves al momento de definir la estrategia tales como conocer al cliente y sus necesidades.<br />
<br />
En el siguiente video puedes ver la planificaci&oacute;n de una acci&oacute;n militar, al igual que en el mundo empresarial, una acci&oacute;n debe ser evaluada, y planificada con el mayor detalle. Tiene un objetivo principal y deben aprovecharse las circunstancias del entorno para que la planificaci&oacute;n d&eacute; resultado. Analiza los aspectos de una operaci&oacute;n militar, y cu&aacute;les aspectos pueden aplicarse en el marco del trabajo.</h4>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/G_PE_CLPLAZO.mp4" type="video/mp4" /></video>
</p>

<p>To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5</a></p>', NULL, 'recursos/paginas/207porquecompramos.pdf', '2018-03-23 18:34:12', '2018-03-27 18:42:51', 2, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (21, 'Analizar posición', 20, 4, '<p>sssdd</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_AnalizarPosicion.jpg" style="width:820px" /></p>

<h3>Analizar su posici&oacute;n competitiva</h3>

<p>Una estrategia firme se deriva del&nbsp;<em><strong>an&aacute;lisis del negocio</strong></em>. Eval&uacute;a las influencias del entorno,&nbsp;sus clientes, su competencia y sus capacidades internas, antes de formar el plan estrat&eacute;gico.</p>

<h3>Examinar influencias</h3>

<p>Muchos factores pueden afectar el&nbsp;<em><strong>rendimiento</strong></em>&nbsp;de la organizaci&oacute;n. Estudia la econom&iacute;a, la tecnolog&iacute;a y los cambios legales y pol&iacute;ticos relacionados con la empresa, con miras a identificar nuevas tendencias de producto y de mercado capaces de influir en la planificaci&oacute;n.</p>

<h3>Observar la econom&iacute;a</h3>

<p>La mayor&iacute;a de las estrategias dependen de lo que sucede en la&nbsp;<em><strong>econom&iacute;a&nbsp;</strong></em>local y global. En primer lugar, investiga qu&eacute; aspectos podr&iacute;an causar un impacto radical. Por ejemplo, si prev&eacute;s un aumento en las tasas de inter&eacute;s en los siguientes seis meses y su posterior estabilizaci&oacute;n, podr&iacute;as determinar c&oacute;mo y cu&aacute;ndo invertir en el desarrollo de nuevos productos. No olvides aplicar esta informaci&oacute;n en tus presupuestos.</p>

<h3>Examinar tendencias en tecnolog&iacute;a</h3>

<p>El ritmo dr&aacute;stico del&nbsp;<em><strong>cambio tecnol&oacute;gico</strong></em>&nbsp;ha tenido un enorme impacto en la mayor&iacute;a de las empresas. La fusi&oacute;n de las t&eacute;cnicas de comunicaci&oacute;n con la informaci&oacute;n digital, modifican continuamente la manera en que todos debemos trabajar. Prot&eacute;gete contra cualquier peligro o problema que introduzca la nueva tecnolog&iacute;a, analizando los desarrollos tecnol&oacute;gicos relevantes m&aacute;s recientes. Si es necesario, consulta un experto o familiar&iacute;zate con los informes de los analistas. Pide a un integrante del equipo que lea las revistas adecuadas y aporte actualizaciones regulares, breves y concisas a sus colegas.</p>

<h3>Entender los cambios legales y pol&iacute;ticos</h3>

<p>Dado que un n&uacute;mero cada vez mayor de organizaciones operan dentro de marcos regulatorios,&nbsp;es crucial entender cu&aacute;les son esos reglamentos. En este sentido, se debe brindar a los integrantes del equipo los documentos de la pol&iacute;tica interna de la organizaci&oacute;n y lo que concierne a la legislaci&oacute;n nacional que regula las actividades productivas inherentes al modelo de negocio que desarrolla la empresa.</p>

<h3>Hablar con expertos</h3>

<p>Si necesitas informaci&oacute;n acerca del posible efecto de ciertos cambios legales o pol&iacute;ticos en tu plan estrat&eacute;gico, solicita el consejo de un &nbsp;especialista o experto en la materia.</p>

<hr />
<h4 style="text-align:center">En el siguiente clip se aprecia c&oacute;mo se eval&uacute;an diferentes aspectos al momento de lanzar un negocio.<br />
Independientemente del tipo de negocio, analizar la posici&oacute;n competitiva, las tendencias y los intereses de los clientes es parte de un buen diagn&oacute;stico estrat&eacute;gico.</h4>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/G_PE_APosicion.webm" type="video/webm" /></video>
</p>

<p>To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>

<p>&nbsp;</p>', NULL, NULL, '2018-03-26 19:49:03', '2018-03-27 18:56:20', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (22, 'Entender al cliente', 20, 4, '<p>sasasas</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_EntenderCliente.jpg" style="width:820px" /></p>

<h3>Entender a los clientes</h3>

<p>Lo que el cliente desea de ti como proveedor es la fuerza impulsora de cualquier plan. Analiza&nbsp;<em><strong>por qu&eacute; sus clientes le compran y cu&aacute;l es su ideal</strong></em>. Despu&eacute;s, asigna prioridades a sus necesidades para asegurarte de que la organizaci&oacute;n brinde un mejor servicio.</p>

<h3>Identificar los criterios de compra</h3>

<p>Los clientes intercambian caracter&iacute;sticas de producto o de servicio por el precio que est&aacute;n dispuestos a pagar por ellos. Juzgan tambi&eacute;n la&nbsp;<em><strong>calidad&nbsp;</strong></em>de su relaci&oacute;n con los representantes de la empresa, y si los&nbsp;<em><strong>procesos comerciales</strong></em>&nbsp;satisfacen sus demandas. Para desarrollar la lealtad de tus clientes debes entender sus criterios de compra. Solicita las opiniones de los miembros de tu empresa que tratan directamente con ellos, y considera tanto a los clientes potenciales como a los ya existentes.</p>

<h3>Definir el ideal</h3>

<p>Averigua cu&aacute;l es la&nbsp;<em><strong>oferta ideal&nbsp;</strong></em>para tus clientes en cuatro &aacute;reas principales: producto, proceso, personas y precio. Solicita su&nbsp;<em><strong>opini&oacute;n</strong></em>&nbsp;en una entrevista, por tel&eacute;fono o invit&aacute;ndolos a asistir a&nbsp;<em>focus group</em>. Por ejemplo, un cliente interno puede informarte por qu&eacute; podr&iacute;a recurrir a otro proveedor para reemplazar su servicio. Estos puntos conforman los criterios de compra y entran en uno de los cuatro factores mencionados.</p>

<h3>Asignar prioridad a los criterios</h3>

<p>Despu&eacute;s de identificar los criterios de compra de sus clientes, el siguiente paso es decidir cu&aacute;les son m&aacute;s importantes para ellos. Las prioridades que fije ahora afectar&aacute;n sus decisiones en etapas posteriores del proceso de planificaci&oacute;n, con respecto a productos, procesos, personas y precios. Llegado el momento de decidir qu&eacute; cambios realizar para mejorar su servicio, reflexiona en torno a la importancia relativa de los criterios entre s&iacute;. Recuerda,&nbsp;<em><strong>no debes trabajar arduamente en puntos poco significativos para el cliente</strong></em>, en especial si eso significa esforzarse menos en los que considera cruciales. Si al equipo le resulta dif&iacute;cil acordar cu&aacute;les ser&aacute;n las prioridades en algunas &aacute;reas, solicita de nuevo la opini&oacute;n de sus clientes clave y, de ser necesario, utiliza la t&eacute;cnica de lluvia de ideas para analizar las posibilidades.</p>

<h3>Fijar prioridades</h3>

<p><em><strong>Identifica las prioridades de sus clientes</strong></em>&nbsp;anotando sus criterios en cuatro &aacute;reas fundamentales: producto (lo que provee la empresa); proceso (c&oacute;mo es el servicio con sus clientes); personas (calidad de quienes tratan con los clientes), y precio (costo para el cliente). Observa qu&eacute; querr&iacute;an sus clientes en cada una de ellas. Por &uacute;ltimo, califica los criterios del uno (01) al diez (10). Cuanto mayor sea la cifra, m&aacute;s alta ser&aacute; la prioridad.</p>

<hr />
<h4 style="text-align:center">A continuaci&oacute;n te invitamos a ver el siguiente video, donde conocer&aacute;s estrategias de mercadeo basadas en las t&eacute;cnicas m&aacute;s avanzadas:&nbsp;<em>El marketing sutil o los seductores ocultos para los sentidos.&nbsp;</em>Podr&aacute;s conocer la importancia y el impacto que para estas estrategias tiene el conocer, de manera profunda, los h&aacute;bitos de consumo de los clientes. &nbsp;</h4>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/PorQueCompramos.webm" type="video/webm" /></video>
</p>

<p>To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>

<p>&nbsp;</p>', NULL, NULL, '2018-03-26 19:52:30', '2018-03-27 18:48:42', 2, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (23, 'Conocer a la competencia', 20, 4, '<p>ffsfsfsfs</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_COnocerComp.jpg" style="width:820px" /></p>

<h3><strong>Conocer la competencia</strong></h3>

<p>Entender a sus clientes y satisfacer sus expectativas s&oacute;lo&nbsp;<em><strong>llevar&aacute; al &eacute;xito</strong></em>&nbsp;si tu desempe&ntilde;o es superior al de la competencia. Analiza las capacidades de los competidores para identificar las posibles oportunidades y amenazas.</p>

<h3><strong>Estudiar a los competidores</strong></h3>

<p><em><strong>Identifica los competidores m&aacute;s importantes</strong></em>. Analiza sus pr&aacute;cticas publicitarias y materiales promocionales, as&iacute; como los productos y servicios que ofrecen. Sus clientes pueden ser una fuente de conocimiento competitivo, lo mismo que los empleados provenientes de una empresa rival. Elabora una gr&aacute;fica de la capacidad de los competidores, y as&iacute; podr&aacute; saber en qu&eacute; punto se encuentran.</p>

<h3><strong>Visualizar el futuro</strong></h3>

<p>La mayor&iacute;a de las organizaciones contemplan a sus competidores como proveedores de productos o servicios similares. Pero en el futuro tal vez no sea as&iacute;. A menudo hay m&aacute;s de una forma de hacer las cosas. Piensa en cu&aacute;les son las probables demandas de los clientes en el futuro, e investiga modos adicionales para satisfacerlas. Los competidores har&aacute;n lo mismo.</p>

<h3><strong>Evaluar oportunidades</strong></h3>

<p>Una vez terminado el an&aacute;lisis competitivo, podr&aacute;s percibir con claridad d&oacute;nde se ubican las principales diferencias entre la capacidad de la organizaci&oacute;n y la de sus competidores,&nbsp;en cumplimiento de las necesidades de los clientes. Una vez que tu organizaci&oacute;n logre acercarse m&aacute;s a sus clientes que a sus competidores, tendr&aacute; la oportunidad de explotar este hecho y, al hacerlo, vender&aacute; m&aacute;s productos y servicios.</p>

<h3><strong>Identificar amenazas</strong></h3>

<p>En las &aacute;reas donde su enfoque y sus capacidades sean similares, el cliente no encontrar&aacute; ninguna ventaja particular al comprarle a tu empresa o a tus competidor. Donde tus competidores cuenten con una ventaja importante, tu empresa podr&aacute; decidir reducir tal amenaza en el momento de tomar decisiones. Considera todas las posibilidades, pues otras organizaciones har&aacute;n lo mismo al examinar sus clientes.</p>

<p>Recuerda, no emprendas acci&oacute;n alguna hasta avanzar en el proceso de planificaci&oacute;n. Utiliza tus descubrimientos para tomar decisiones.</p>', NULL, NULL, '2018-03-26 19:54:02', '2018-03-26 19:54:02', 2, 1, 3, NULL);
INSERT INTO certi_pagina VALUES (24, 'Evaluar capacidades', 20, 4, '<p>ccsscs</p>', '<h3><strong>Evaluar las habilidades y capacidades</strong></h3>

<p>Las habilidades en an&aacute;lisis de procesos, sistemas de informaci&oacute;n, recursos y equipos, te permitir&aacute;n planificar tomando en cuenta la capacidad de la organizaci&oacute;n. Identifica las &aacute;reas por mejorar e int&eacute;gralas posteriormente al plan.</p>

<h3><strong>Examinar los procesos empresariales internos</strong></h3>

<p>Los procesos empresariales deben ser eficaces. Exam&iacute;nalos en detalle y ubica cu&aacute;les requieren ser perfeccionados. Quiz&aacute; debas revisar o modificar el proceso de recepci&oacute;n y confirmaci&oacute;n de pedidos, los t&eacute;rminos y condiciones, incluyendo la distribuci&oacute;n de los productos, as&iacute; como reevaluar su servicio posventa. Busca si hay duplicaciones, vac&iacute;os y &aacute;reas con quejas frecuentes.</p>

<h3><strong>Evaluar informaci&oacute;n</strong></h3>

<p>Examina lo bien que los sistemas de informaci&oacute;n, computarizados o manuales, proporcionan a las personas la informaci&oacute;n correcta, en el formato y momento indicado. Solicita a los miembros del equipo que se&ntilde;alen casos en que su trabajo se ha atrasado por tener que dedicar un tiempo preciso a cazar informaci&oacute;n que podr&iacute;a haberse incluido en un informe o documento normal y de f&aacute;cil acceso. Quiz&aacute;s necesitas generar ideas para preparar una lista exhaustiva de vac&iacute;os de informaci&oacute;n. Por &uacute;ltimo, decide cu&aacute;l de estos vac&iacute;os impiden al equipo proveer un servicio de primera.</p>

<h3><strong>Examinar las habilidades del equipo</strong></h3>

<p>Solicita a los miembros del equipo que hablen sobre sus fortalezas y debilidades relacionadas con las necesidades de los clientes. Intenta hacer que se sientan bien acerca del proceso. Despu&eacute;s de todo, habla de formular una nueva estrategia que les aporte mayores oportunidades y &eacute;xito en el futuro. Examina los recursos para asegurarte de que nada les impide ofrecer un servicio de primera al cliente. Por ejemplo, preg&uacute;ntate si las instalaciones de oficinas, el espacio en tiendas y los almacenes, cumplen con los requerimientos actuales y ser&aacute;n adecuados en el futuro. Eval&uacute;a tambi&eacute;n las maquinarias, veh&iacute;culos y equipos de c&oacute;mputo.</p>

<ul>
	<li>Recuerda que ning&uacute;n proceso empresarial dura para siempre.</li>
	<li>En un entorno cambiante, todos debemos encontrar maneras para mejorar nuestras habilidades.</li>
</ul>

<h3><strong>Analizar fortalezas y debilidades</strong></h3>

<p>Empieza analizando la fortaleza de todos. Despu&eacute;s ser&aacute; m&aacute;s f&aacute;cil analizar las &aacute;reas por mejorar, en particular si esclareces las respuestas a las siguientes preguntas: &iquest;C&oacute;mo ha cambiado tu trabajo desde que empezaste a laborar aqu&iacute;? &iquest;C&oacute;mo se podr&iacute;an mejorar los rendimientos de la organizaci&oacute;n? &iquest;La empresa ofrece suficientes servicios de capacitaci&oacute;n? &iquest;A veces enfrentas una situaci&oacute;n dif&iacute;cil y no sabes c&oacute;mo resolverla? Etc.</p>

<p>El an&aacute;lisis genera informaci&oacute;n muy rica. Es importante extraer los elementos m&aacute;s valiosos o los que tendr&aacute;n mayor efecto en la estrategia. Utiliza &eacute;ste como el punto de arranque del proceso de planificaci&oacute;n.</p>

<p>Convierte el conjunto de informaci&oacute;n recopilada durante la etapa de an&aacute;lisis en una matriz FODA, cuyas siglas corresponden a Fortalezas, Oportunidades, Debilidades y Amenazas. Este resumen ayuda a aclarar los puntos de vista del equipo, act&uacute;a como un potente&nbsp;<em>&ldquo;impulsor&rdquo;&nbsp;</em>del plan y proporciona una manera de medir el progreso.</p>

<h3><strong>Comparte el resumen y &uacute;salo como base para hacer estrategias</strong></h3>

<p>Al generar por fin el plan estrat&eacute;gico, el resumen te aportar&aacute; diversos elementos impulsores. Busca maneras en las que puedas utilizar las fortalezas de la organizaci&oacute;n, eliminar las debilidades, explotar las oportunidades y evitar o superar las amenazas. Puesto que has dedicado tiempo a analizar lo que es importante para el cliente, y puedes confiar en que el plan estar&aacute;&nbsp;<em>&ldquo;impulsado&rdquo;&nbsp;</em>en base al cumplimiento de las necesidades de los consumidores.</p>

<hr />
<h3 style="text-align:center">Te invitamos a conocer en los siguientes m&oacute;dulos los pasos que deben llevarse a cabo para generar un efectivo proceso de planificaci&oacute;n estrat&eacute;gica.</h3>

<h3 style="text-align:center"><strong>&iexcl;&Eacute;xito!</strong></h3>

<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/Podcast.jpg" style="width:100%" /></p>', NULL, NULL, '2018-03-26 19:56:31', '2018-03-26 19:58:24', 2, 1, 4, NULL);
INSERT INTO certi_pagina VALUES (26, 'Del pensamiento a la ejecución', 13, 3, '<p>kakakaka</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/delpensamientoalaejecucion.jpg" style="width:820px" /></p>

<p>El&nbsp;<em><strong>pensamiento estrat&eacute;gico</strong></em>&nbsp;es cuando pensamos una idea, un sue&ntilde;o y la&nbsp;<em><strong>planificaci&oacute;n estrat&eacute;gica</strong></em>&nbsp;es cuando esa idea la traducimos en acci&oacute;n que conduce a un resultado.</p>

<p>En los siguientes recursos te queremos mostrar unos casos emblem&aacute;ticos de c&oacute;mo insignes visionarios, convirtieron sus sue&ntilde;os en realidades. Esos visionarios, no solo so&ntilde;aron, sino que actuaron, a veces con intuici&oacute;n, pero siempre con perseverancia, constancia y entrega.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>

<p>Te invitamos a ver estos clips de pel&iacute;culas y a identificar ese&nbsp;<em><strong>pensamiento estrat&eacute;gico</strong></em>&nbsp;y tambi&eacute;n las actividades que condujeron al &eacute;xito en los negocios.</p>

<hr />
<h3>En el siguiente video de una entrevista a Herb Kelleger, presidente fundador de Southwest Airlines, muestra con su experiencia la importancia de llevar el pensamiento estrat&eacute;gico a una ejecuci&oacute;n que se aplique a trav&eacute;s de una estrategia clara. &Eacute;l con su ejemplo habla de la importancia del liderazgo para implementar una estrategia, y c&oacute;mo una estrategia clara es garant&iacute;a de &eacute;xito en un sector altamente competitivo.</h3>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/EntrevistaHerbKelleher.webm" type="video/webm" />
<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>
</video>
</p>', NULL, NULL, '2018-03-27 12:44:41', '2018-03-27 12:44:41', 2, 1, 3, NULL);

INSERT INTO certi_pagina VALUES (27, 'Por eso es que necesitamos que lo rediseñes', 26, 4, '<p>okokok</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/clip%20Job%20%20foto%20arriba.jpg" style="width:820px" /></p>

<p>El siguiente clip se ambienta en 1976, cuando Jobs est&aacute; de vuelta en Los Altos, California, viviendo en la casa de sus padres adoptivos, Paul y Clara. Para esa fecha Steve Jobs ha formado una sociedad con su amigo de infancia,&nbsp;Steve Wozniak, despu&eacute;s de que este construyera una&nbsp;computadora personal, la Apple I.</p>

<p>A su empresa le dan el nombre de&nbsp;Apple Computer, aunque ya existe una compa&ntilde;&iacute;a llamada&nbsp;Apple Records&nbsp;que pertenece al grupo The Beatles. Wozniak hace una demostraci&oacute;n del Apple I en el&nbsp;Homebrew Computer Club, donde Jobs recibe un contrato con Paul Terrell. Jobs le pide permiso a su padre para utilizar el&nbsp;garaje familiar como base para su nuevo emprendimiento. Su padre acepta y Jobs luego contacta a Kottke, Bill Fernandez,&nbsp;Bill Atkinson, Chris Espinosa y Rod Holt para comenzar a construir computadores Apple I.</p>

<p>Este clip muestra las competencias de un&nbsp;<strong><em>pensador estrat&eacute;gico</em></strong>, Steve Jobs; quien a partir de una actitud desafiante y con una visi&oacute;n en la mente, se propone transformar al mundo haciendo las primeras computadoras. Promovi&oacute; el desarrollo de&nbsp;<strong><em>nuevas pr&aacute;cticas</em></strong>&nbsp;y entendi&oacute; las necesidades de un mercado latente al cual hab&iacute;a que servir con productos de calidad que marcaran tendencias.</p>

<p>Basado en la historia real, se muestra la agudeza de&nbsp;<strong><em>retar los paradigmas</em></strong>&nbsp;establecidos y construir un mercado haciendo historia.</p>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/222-INNO.webm" type="video/webm" />
<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>
</video>
</p>', NULL, NULL, '2018-03-27 12:46:23', '2018-03-27 12:46:23', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (28, '¿Dónde puedo comprar un Tucker?', 26, 4, '<p>dsdaadad</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/Tucker.jpg" style="width:820px" /></p>

<p>En 1948 un joven ingeniero estadounidense, Preston Tucker concibe un autom&oacute;vil de tecnolog&iacute;a revolucionaria y de bajo costo, al que bautiza con su apellido.</p>

<p>Las tres grandes empresas fabricantes de autom&oacute;viles&nbsp;General Motors,&nbsp;Chrysler&nbsp;y Ford&nbsp;se unen para oponerse legalmente al proyecto de Tucker, pero &eacute;l est&aacute; decidido a no dejarse atropellar por ellas y lucha por construir cincuenta ejemplares de su modelo.</p>

<p>Este clip muestra el despliegue de un pensador estrat&eacute;gico, desde su visi&oacute;n hasta la realizaci&oacute;n de un veh&iacute;culo diferente que transform&oacute; el mercado y desafi&oacute; los paradigmas de la industria automotriz de finales de la segunda guerra mundial.</p>

<p>Este dise&ntilde;o present&oacute; nuevas pr&aacute;cticas de fabricaci&oacute;n y novedosas metodolog&iacute;as de comercializaci&oacute;n, as&iacute; como un entendimiento de los intereses de los consumidores por un veh&iacute;culo diferente a los que se comercializaban en la &eacute;poca.</p>

<p>En este clip podr&aacute;s ver el pensamiento estrat&eacute;gico que parte de una visi&oacute;n y un entendimiento de un mercado para desarrollar un producto exitoso.</p>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/222-INNO.webm" type="video/webm" />
<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>
</video>
</p>', NULL, NULL, '2018-03-27 12:49:58', '2018-03-27 12:49:58', 2, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (29, 'La innovación aplicada a la industria automotriz', 26, 4, '<p>ssddsds</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/Imagen32INNO.jpg" style="width:820px" /></p>

<p>El dise&ntilde;o y lanzamiento del veh&iacute;culo Nano en la India es un caso de exitoso de c&oacute;mo el pensamiento estrat&eacute;gico de un l&iacute;der, Rat&aacute;n Tata, a partir de una visi&oacute;n y unas oportunidades de mercado fue capaz de desarrollar un veh&iacute;culo innovador.</p>

<p>Este caso muestra importantes &eacute;xitos en diferentes aspectos; tanto econ&oacute;micos, como su impacto social, al permitir que personas con bajos ingresos puedan adquirir un veh&iacute;culo para toda la familia.</p>

<p>En este emblem&aacute;tico&nbsp;caso, podemos ver decenas de creencias que se cuestionaron, &iquest;d&oacute;nde debe ser colocado el motor? &iquest;qu&eacute; tipo de motor? el tama&ntilde;o, el precio, etc.</p>

<p>Tambi&eacute;n se aprecia directamente del due&ntilde;o de este conglomerado industrial el Sr. Rat&aacute;n Tata, su visi&oacute;n por resolver las necesidades de transportaci&oacute;n de forma segura, que dan el impulso a desarrollar esta iniciativa.</p>

<p>Te invitamos a ver este video y reflexionar sobre el poder del pensamiento estrat&eacute;gico, la visi&oacute;n y el entendimiento del mercado.&nbsp;</p>

<p>
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/253-INNO.mp4" type="video/webm" />
<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>
</video>
</p>', NULL, NULL, '2018-03-27 12:51:34', '2018-03-27 12:51:34', 2, 1, 3, NULL);
INSERT INTO certi_pagina VALUES (30, 'Del pensamiento a la ejecución', 26, 4, '<p>ddadadad</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/G_PEM1Ma3R3_1.jpg" style="width:820px" /></p>

<p>El pensador estrat&eacute;gico normalmente es un visionario, alguien que se proyecta en el tiempo y visualiza un futuro con su producto, servicio o empresa. Sin embargo para poder tener impacto se requiere pasar del pensamiento a la acci&oacute;n y eso significa hacer actividades y tareas que permitan conseguir esa visi&oacute;n.</p>

<p>En los casos de Steve Job con la elaboraci&oacute;n del computador y el caso de Tucker, se demuestra que para lograr impactar hay que pasar al terreno espec&iacute;fico de la realizaci&oacute;n.</p>

<p>Reflexiona sobre la importancia de pasar a la realizaci&oacute;n, cuantas ideas se quedan solo en pensamiento y para convertirlas en resultados se requiere pasar a la acci&oacute;n.</p>

<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/Podcast.jpg" style="width:100%" /></p>', NULL, NULL, '2018-03-27 12:54:06', '2018-03-27 12:54:06', 2, 1, 4, NULL);
INSERT INTO certi_pagina VALUES (31, 'Pensamiento Estratégico para Competir', 13, 3, '<p>dadadadad</p>', '<p><img alt="" src="http://www.academiagerencial.com/admin/web/ckfinder/userfiles/images/cne_m4_mt1_intro.jpg" style="width:820px" /></p>

<p>Para comprender mejor el ambiente &nbsp;industrial &nbsp;y competitivo &nbsp;en el que se desenvuelve una empresa, el pensador estrat&eacute;gico se puede enfocar en&nbsp;responder las siguientes preguntas:</p>

<ol>
	<li>&iquest;Ofrece la industria &nbsp;oportunidades atractivas &nbsp;para el crecimiento?</li>
	<li>&iquest;Qu&eacute; clase de fuerzas competitivas &nbsp;enfrentan &nbsp;los miembros de la industria &nbsp;y qu&eacute; intensidad tiene cada una?</li>
	<li>&iquest;Qu&eacute; fuerzas impulsan el cambio en la industria &nbsp;y qu&eacute; efectos tendr&aacute;n &nbsp;en la intensidad competitiva &nbsp;y la rentabilidad de la industria?</li>
	<li>&iquest;Cu&aacute;les son las posiciones que ocupan &nbsp;en el mercado &nbsp;los rivales de la industria: qui&eacute;n tiene una posici&oacute;n s&oacute;lida y qui&eacute;n no?</li>
	<li>&iquest;Qu&eacute; movimientos estrat&eacute;gicos es probable &nbsp;que realicen los rivales?</li>
	<li>&iquest;Cu&aacute;les son los factores clave para el &eacute;xito futuro competitivo?</li>
	<li>&iquest;La industria &nbsp;ofrece perspectivas buenas de ganancias atractivas?</li>
</ol>

<p>Las respuestas basadas en el an&aacute;lisis a estas siete preguntas &nbsp;proporcionan a un pensador estrat&eacute;gico el conocimiento necesario para idear una estrategia &nbsp;que se ajuste a la situaci&oacute;n &nbsp;externa de la empresa.</p>

<h2><strong>Los componentes del macroambiente</strong></h2>

<p><iframe frameborder="0" height="600" scrolling="no" src="https://recursos2puntocero.com/recursos/PE_TABLA1/tabla.html" width="820"></iframe></p>', NULL, NULL, '2018-03-27 12:59:15', '2018-03-27 13:01:26', 2, 1, 4, NULL);
INSERT INTO certi_pagina VALUES (35, 'Fuerzas competitivas que enfrenta la industria', 31, 4, '<p>daadadada</p>', '<p>El car&aacute;cter y fortaleza&nbsp;de las fuerzas competitivas nunca son las mismas en cada industria.&nbsp;El&nbsp;<em>modelo de competencia de cinco fuerzas de Michael Porter&nbsp;</em>es, por mucho,&nbsp;la herramienta m&aacute;s poderosa y de mayor&nbsp;uso para&nbsp;diagnosticar de manera sistem&aacute;tica&nbsp;las principales&nbsp;presiones competitivas en un mercado y evaluar la fortaleza e importancia de cada una. Este modelo sostiene que las fuerzas competitivas&nbsp;que afectan la rentabilidad de la industria trascienden&nbsp;la rivalidad entre competidores e incluye presiones que nacen de cuatro &nbsp;fuentes coexistentes.</p>

<h3>Las cinco fuerzas competitivas&nbsp;incluyen:</h3>

<ol>
	<li>la competencia de&nbsp;<em>vendedores&nbsp;rivales</em>,</li>
	<li>la competencia de&nbsp;<em>nuevos participantes&nbsp;</em>a la industria,</li>
	<li>la competencia de los productores&nbsp;de&nbsp;<em>productos sustitutos</em>,</li>
	<li>el poder de negociaci&oacute;n de los&nbsp;<em>proveedores&nbsp;</em>y,</li>
	<li>el poder de negociaci&oacute;n de los&nbsp;<em>clientes</em>.</li>
</ol>

<p>El uso del modelo de cinco fuerzas para determinar la naturaleza y fortaleza de las presiones competitivas en una industria, implica determinar cada fuerza, su riesgo para su empresa y el impacto que puede tener en los negocios</p>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" height="740" width="680"><param name="quality" value="high" /><param name="movie" value="https://recursos2puntocero.com/recursos/AES/flash/5fuerzas_porter.swf" /><embed height="740" pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="https://recursos2puntocero.com/recursos/AES/flash/5fuerzas_porter.swf" type="application/x-shockwave-flash" width="680"></embed></object>
<hr />
<h4>En el PDF anexo podr&aacute;s leer Las Cinco Fuerzas como Herramienta Anal&iacute;tica realizado por los profesores David B. Allen y Arnaud Gorgeon del IE Business School</h4>', NULL, 'recursos/paginas/ag_aer_5fuerzasporter.pdf', '2018-03-27 13:17:36', '2018-03-27 13:23:51', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (36, 'Maniobras competitivas entre rivales', 31, 4, '<p>faafafafa</p>', '<p><img alt="" src="https://www.cifaes.com/admin/web/ckfinder/userfiles/images/cne_m4_mt1_r2_interna.png" style="width:820px" /></p>

<p>La m&aacute;s fuerte de las cinco fuerzas competitivas es casi siempre la maniobrabilidad en el mercado y la competencia por la preferencia del comprador, presentes entre los vendedores rivales de un producto o servicio. En efecto,&nbsp;<em>un mercado es un campo de batalla competitivo&nbsp;</em>en donde&nbsp;la carrera&nbsp;por el favor del comprador es vertiginosa.&nbsp;Los vendedores&nbsp;rivales son proclives a emplear cualquier&nbsp;arma que tengan en su &ldquo;arsenal&rdquo; &nbsp;de negocios para fortalecer su posici&oacute;n en el mercado y obtener&nbsp;buenas ganancias. El reto es idear una estrategia&nbsp;competitiva que al menos permita&nbsp;que una empresa mantenga&nbsp;la suya contra sus competidores y que, idealmente,&nbsp;<em>produzca una ventaja competitiva sobre los rivales</em>. Sin embargo,&nbsp;cuando una&nbsp;empresa&nbsp;hace un movimiento&nbsp;estrat&eacute;gico&nbsp;que produce buenos resultados, sus rivales suelen responder con movimientos&nbsp;ofensivos o defensivos para contrarrestarlo. Este patr&oacute;n de acci&oacute;n y reacci&oacute;n, movimiento&nbsp;y respuesta a esa estrategia,&nbsp;ajuste y reajuste,&nbsp;genera un panorama competitivo&nbsp;en continua&nbsp;evoluci&oacute;n, en el cual la batalla&nbsp;por el mercado presenta altibajos,&nbsp;en ocasiones giros y retornos, y crea ganadores y perdedores.</p>

<h2>Modelo de competencia de cinco fuerzas. Una herramienta&nbsp;anal&iacute;tica clave</h2>

<p><img alt="" src="https://www.cifaes.com/admin/web/ckfinder/userfiles/images/herramientas_analiticas_clave.jpg" style="width:820px" /></p>', NULL, NULL, '2018-03-27 13:31:48', '2018-03-27 13:31:48', 2, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (37, 'Rivalidad en la industria', 31, 4, '<p>lllllll</p>', '<p><img alt="" src="http://www.academiagerencial.com/admin/web/ckfinder/userfiles/images/AG_EEC_Rivalidad.jpg" style="width:820px" /></p>

<p>La intensidad &nbsp;de la rivalidad var&iacute;a de industria&nbsp; a industria, &nbsp;y depende de varios factores identificables. La figura resume estos factores e identifica los que intensifican o debilitan la rivalidad entre competidores directos en una industria.&nbsp;Conviene explicar brevemente por qu&eacute; estos factores afectan el grado de rivalidad.</p>

<h2>Factores que afectan el grado de rivalidad</h2>
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" height="740" width="680"><param name="quality" value="high" /><param name="movie" value="https://recursos2puntocero.com/recursos/AES/flash/porter_rivalidadinterna.swf" /><embed height="740" pluginspage="http://www.macromedia.com/go/getflashplayer" quality="high" src="https://recursos2puntocero.com/recursos/AES/flash/porter_rivalidadinterna.swf" type="application/x-shockwave-flash" width="680"></embed></object>
<p><em>La rivalidad es m&aacute;s fuerte en mercados en que la demanda del comprador crece lentamente o va a la baja, y es m&aacute;s d&eacute;bil en mercados de crecimiento r&aacute;pido.&nbsp;</em>En mercados en que la demanda &nbsp;crece s&oacute;lo 1 o 2% o se encoge, las compa&ntilde;&iacute;as por ganar negocios suelen aplicar descuentos &nbsp;de precios, promociones de ventas y otras t&aacute;cticas &nbsp;para &nbsp;impulsar &nbsp;sus vol&uacute;menes &nbsp;de ventas, algunas &nbsp;veces hasta&nbsp;el punto &nbsp;en que comienzan una feroz batalla por la participaci&oacute;n en el mercado.<br />
&nbsp;<br />
Los costos del cambio de marca incluyen no s&oacute;lo los costos monetarios, sino tambi&eacute;n &nbsp;tiempo, &nbsp;molestias &nbsp;y costos psicol&oacute;gicos.&nbsp;<br />
<br />
<em>L</em><em>a rivalidad se incrementa cuando los productos de los vendedores rivales se parecen m&aacute;s, y disminuye conforme los productos rivales se diferencian m&aacute;s.&nbsp;</em>Cuando las ofertas rivales son id&eacute;nticas o se diferencian poco, los compradores tienen menos razones para ser leales a una empresa, lo que facilita que los rivales convenzan a los compradores a cambiarse a sus compa&ntilde;&iacute;as.&nbsp;<br />
&nbsp;<br />
<em>La rivalidad es mayor cuando hay capacidad de producci&oacute;n sin utilizar, sobre todo si el producto de la industria conlleva altos costos fijos.&nbsp;</em>Siempre que hay demasiada &nbsp;oferta en un mercado, lo que resulta es un &ldquo;mercado &nbsp;de compradores&rdquo; que intensifica la rivalidad, tal vez hasta el punto de amenazar &nbsp;la supervivencia de las empresas d&eacute;biles. Asimismo, siempre que los costos fijos constituyan una gran parte del costo total de modo que los costos unitarios &nbsp;sean significativamente &nbsp;m&aacute;s bajos cuando operan &nbsp;a plena capacidad, las empresas tendr&aacute;n &nbsp;fuertes presiones para &nbsp;recortar &nbsp;precios e impulsar &nbsp;sus ventas siempre que operen por debajo de su capacidad &nbsp;m&aacute;xima. La capacidad &nbsp;sin utilizar penaliza en forma significativa a las empresas porque hay menos unidades entre las cuales distribuir los costos fijos.&nbsp;<br />
&nbsp;<br />
<em>La rivalidad se intensifica cuando se incrementa el n&uacute;mero de competidores, y conforme se asemejan en tama&ntilde;o y fuerza competitiva.&nbsp;</em>Mientras &nbsp;mayor sea el n&uacute;mero &nbsp;de competidores, mayor &nbsp;ser&aacute; la probabilidad de que una o m&aacute;s compa&ntilde;&iacute;as &nbsp;realicen activamente &nbsp;una ofensiva estrat&eacute;gica para mejorar su posici&oacute;n en el mercado, con lo que calientan la competencia e imponen presiones adicionales &nbsp;a los rivales para que respondan con medidas defensivas u ofensivas propias. &nbsp;Adem&aacute;s, cuando el tama&ntilde;o &nbsp;y la fuerza competitiva &nbsp;de los rivales son comparables, suelen estar en condiciones de competir en un campo de juego parejo, en cuyo caso la lucha tiende a ser m&aacute;s fiera que cuando&nbsp; uno o m&aacute;s miembros de la industria &nbsp;tiene una posici&oacute;n dominante en el mercado &mdash;y recursos y capacidades &nbsp;sustancialmente mayores&mdash; que sus rivales mucho m&aacute;s peque&ntilde;os.<br />
<br />
<em>La rivalidad es mayor cuando hay barreras que evitan que las empresas no rentables salgan de la industria.&nbsp;</em>En industrias &nbsp;en que los activos no se pueden &nbsp;vender o transferir &nbsp;f&aacute;cilmente a otros usos, donde los trabajadores tienen derecho a la protecci&oacute;n de su empleo o donde los due&ntilde;os est&aacute;n comprometidos a seguir en el negocio por razones personales, las empresas decadentes tienden a mantenerse &nbsp;m&aacute;s de lo que lo har&iacute;an en otras condiciones, aunque sangren tinta roja. Esto incrementa la rivalidad de dos formas. Las empresas que pierden &nbsp;terreno &nbsp;o se hallan &nbsp;en problemas &nbsp;financieros &nbsp;a menudo &nbsp;recurren &nbsp;a profundos descuentos de precios que pueden disparar &nbsp;una guerra de precios y desestabilizar &nbsp;lo que por lo dem&aacute;s es una industria &nbsp;atractiva. A ello se a&ntilde;ade que las altas barreras &nbsp;para salir provocan que una industria &nbsp;se ateste de vendedores, &nbsp;y esto impulsa la rivalidad &nbsp;y obliga a las compa&ntilde;&iacute;as &nbsp;m&aacute;s d&eacute;biles a emprender &nbsp;maniobras err&aacute;ticas &nbsp;(con frecuencia, &nbsp;movimientos desesperados de toda clase) con el fin de ganar suficientes ventas e ingresos para permanecer &nbsp;en el negocio.</p>', NULL, NULL, '2018-03-27 13:35:04', '2018-03-27 13:35:04', 2, 1, 3, NULL);


------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_thumbnail_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_thumbnail', 'id'), 4, false);

--
-- Data for Name: admin_thumbnail; Type: TABLE DATA; Schema: public;
--

INSERT INTO admin_thumbnail VALUES (1, 1, 'Dashboard', 'recursos/thumbnails/base_dashboard.png');
INSERT INTO admin_thumbnail VALUES (2, 1, 'Vista Lección', 'recursos/thumbnails/base_leccion.png');
INSERT INTO admin_thumbnail VALUES (3, 1, 'Login', 'recursos/thumbnails/base_login.png');