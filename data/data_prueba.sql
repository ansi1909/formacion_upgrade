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

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_permiso', 'id'), 38, false);

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


------------------------------------------------------------------------------------------------------------
-- Name: admin_empresa_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('admin_empresa_id_seq', 9, true);


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

SELECT pg_catalog.setval('admin_usuario_id_seq', 20, true);


--
-- Data for Name: admin_usuario; Type: TABLE DATA;
--
INSERT INTO admin_usuario VALUES (1, 'admin', 'admin', 'Administrador', 'Sistema', 'soporte_link_gerencial@gmail.com', NULL, true, '2017-09-21 10:28:00', '1981-01-29', 'VEN', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (2, 'asegovia', '123456', 'Ansise', 'Segovia', 'ansi79@gmail.com', 'ansiisesegovia@linkgerencial.com', true, '2018-02-09 16:51:59', '1979-09-19', 'VEN', NULL, NULL, NULL, NULL, 1, '', 12, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (3, 'mdominguez', '123456', 'Mary Flor', 'Dominguez', '', 'marydominguez@linkgerencial.com', true, '2018-02-09 16:53:31', '1960-09-13', 'VEN', NULL, NULL, NULL, NULL, 1, '', 12, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (4, 'eherrera', '123456', 'Elgui', 'Herrera', '', 'eherrera@linkgerencial.com', true, '2018-02-09 16:55:34', '1957-02-13', 'VEN', '1', 'recursos/usuarios/flor2.jpg', NULL, NULL, NULL, NULL, 12, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (5, 'mdaza', '123456', 'Marianella', 'Daza', '', 'mdaza@linkgerencial.com', true, '2018-02-09 16:57:14', '1979-07-27', 'VEN', '1', '', '11', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (6, 'aalvarez', '123456', 'Alnahir', 'Alvarez', 'alnahir07@hotmail.com', 'aalvarez@formacion2puntocero.com', true, '2018-02-09 17:22:16', '1999-07-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 11, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (7, 'mrodriguez', '123456', 'Maria Gabriela', 'Rodriguez', 'mgrodriguez.linkgerencial@gmail.com', 'mgrodriguez@linkgerencial.com', true, '2018-02-09 17:27:02', '1999-09-19', 'VEN', NULL, NULL, NULL, NULL, 1, '', 16, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (8, 'jchiquin', '123456', 'Jeimy', 'Chiquin', '', 'jchiquin@linkgerencial.com', true, '2018-02-09 17:31:26', '1981-08-05', 'VEN', NULL, NULL, NULL, NULL, 1, '', 12, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (9, 'yavila', '123456', 'Yasaac', 'Ávila', 'yavila.linkgerencial.com@gmail.com', 'yavila@linkgerencial.com', true, '2018-02-09 17:47:49', '1999-06-30', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (10, 'rvirguez', '123456', 'Ricardo', 'Virguez', 'rvirguez.linkgerencial@gmail.com', 'rvirguez@linkgerencial.com', true, '2018-02-09 17:51:27', '1990-11-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (11, 'rvirguez', '123456', 'Ricardo', 'Virguez', 'rvirguez.linkgerencial@gmail.com', 'rvirguez@linkgerencial.com', true, '2018-02-09 17:51:27', '1990-11-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (12, 'cgonzalez', '123456', 'Celinet', 'Gonzalez', '', 'cgonzalez@linkgerencial.com', true, '2018-02-09 17:53:49', '1995-02-08', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (13, 'jvelasquez', '123456', 'José', 'Velásquez', 'Josenriquev@gmail.com', 'jvelasquez@linkgerencial.com', true, '2018-02-09 17:55:56', '1979-01-29', 'VEN', NULL, NULL, NULL, NULL, 1, '', 16, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (14, 'minnie', '123456', 'Minnie', 'Mouse', 'minnie@mouse.com', '', true, '2018-02-13 13:38:40', '2000-02-02', 'VEN', NULL, NULL, NULL, NULL, 1, 'recursos/usuarios/g_etn_recoleccion_b.jpg', 17, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (15, 'tempmary', '123456', 'Mary Flor', 'Domínguez', 'dominguezmaryflor@gmail.com', 'marydominguez@linkgerencial.com', true, '2018-02-14 18:32:30', '1959-09-11', 'VEN', NULL, NULL, NULL, NULL, 1, 'recursos/usuarios/foto_mary_flor.jpg', 11, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (16, 'luisito', '123456', 'Luisito', 'Luisito ', 'luisito@gmail.com', 'luisito@linkgerencial.com', true, '2018-02-14 18:39:38', '2000-01-03', 'DOM', NULL, NULL, NULL, NULL, 6, 'recursos/usuarios/g_etn_recoleccion_b.jpg', 6, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (17, 'temppepito', '123456', 'pepito', 'pepito', 'pepito@gmail.com', 'pepito@gmail.com', true, '2018-02-14 18:49:59', '1996-01-09', 'VEN', NULL, NULL, NULL, NULL, 1, 'recursos/usuarios/flor4.jpg', 16, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (18, 'temptutorvirtual', '123456', 'TUTOR', 'VIRTUAL', 'correotutor@gmail.com', 'correocorporativo@gmail.com', true, '2018-02-17 14:22:44', '1999-01-01', 'VEN', NULL, NULL, NULL, NULL, 7, '', 15, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (19, 'ponceelrelajado', 'uprueba1', 'Jhonatan', 'Ponce', 'ponceelrelajado@gmail.com', 'ponceelrelajado@gmail.com', true, '2018-02-20 22:03:31', '1988-05-09', 'VEN', NULL, NULL, NULL, NULL, 1, '', 17, NULL, NULL, NULL);
INSERT INTO admin_usuario VALUES (20, 'pluto', '123456', 'pluto', 'pluto', '', '', true, '2018-03-23 19:09:24', '2000-02-02', 'VEN', '', '', '', '', 2, '', 3, false, NULL, NULL);



------------------------------------------------------------------------------------------------------------------------
-- Name: idadmin_rol_usuario_seq;; Type: SEQUENCE SET; Schema: public;
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol_usuario', 'id'), 28, false);

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
INSERT INTO admin_rol_usuario VALUES (11, 2, 11);
INSERT INTO admin_rol_usuario VALUES (12, 2, 12);
INSERT INTO admin_rol_usuario VALUES (13, 2, 13);
INSERT INTO admin_rol_usuario VALUES (19, 2, 14);
INSERT INTO admin_rol_usuario VALUES (20, 2, 15);
INSERT INTO admin_rol_usuario VALUES (21, 2, 16);
INSERT INTO admin_rol_usuario VALUES (22, 2, 17);
INSERT INTO admin_rol_usuario VALUES (23, 2, 18);
INSERT INTO admin_rol_usuario VALUES (24, 4, 18);
INSERT INTO admin_rol_usuario VALUES (25, 3, 18);
INSERT INTO admin_rol_usuario VALUES (26, 2, 19);
INSERT INTO admin_rol_usuario VALUES (27, 2, 20);


--
-- Name: certi_pagina_id_seq; Type: SEQUENCE SET; Schema: public; Owner: develo
--

SELECT pg_catalog.setval('certi_pagina_id_seq', 20, true);


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
INSERT INTO certi_pagina VALUES (12, 'Pensamiento estratégico', NULL, 1, '<p>El pensamiento estrat&eacute;gico es entender y conectar el trabajo cotidiano con la estrategia del negocio. Es por ello que todo pensador estrat&eacute;gico sabe a d&oacute;nde quiere llegar, d&oacute;nde se encuentra ubicado, hacia d&oacute;nde quiere ir y est&aacute; en la capacidad de corregir la direcci&oacute;n de la empresa si esto fuese necesario creando estrategias competitivas e innovadoras.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/admin/web/ckfinder/userfiles/images/AG_PE_BIENVENIDA.jpg" style="width:820px" /></p>

<p><strong><em>El pensamiento estrat&eacute;gico</em></strong>&nbsp;es entender y conectar el trabajo cotidiano con la estrategia del negocio. Es por ello que todo pensador estrat&eacute;gico sabe a d&oacute;nde quiere llegar, d&oacute;nde se encuentra ubicado, hacia d&oacute;nde quiere ir y est&aacute; en la capacidad de corregir la direcci&oacute;n de la empresa si esto fuese necesario creando estrategias competitivas e innovadoras.</p>

<p>Hoy por hoy es muy importante que dispongas de un&nbsp;<em>pensamiento estrat&eacute;gico</em>&nbsp;que te permitir&aacute; aplicarlo a tu desarrollo profesional como a la empresa en la cual trabaja.</p>

<p>Entender el entorno econ&oacute;mico y de la industria, las tendencias tecnol&oacute;gicas y empresariales as&iacute; como la posici&oacute;n competitiva de cada empresa es lo que permite dise&ntilde;ar la forma como competir.</p>

<p>El&nbsp;<strong><em>Curso de Pensamiento estrat&eacute;gico</em></strong>&nbsp;es un programa de desarrollo a distancia, que basado en las experiencias m&aacute;s exitosas de las mejores escuelas de negocios,&nbsp; ha depurado lo m&aacute;s selecto de los principales programas y pensadores e incorporado tecnolog&iacute;as de informaci&oacute;n, para generar un espacio de formaci&oacute;n permanente las 24 horas del d&iacute;a, los 7 d&iacute;as de la semana, sin barreras de lugar ni tiempo, con la finalidad de formar l&iacute;deres integrales con una visi&oacute;n global, potenciando la capacidad de pensar estrat&eacute;gicamente, con el fin de estructurar planes que conduzcan al &eacute;xito y a la sustentabilidad -a mediano y largo plazo- de la empresa.</p>

<p>Con el entrenamiento adecuado, todos somos capaces de conectar nuestras acciones diarias con los objetivos del negocio, y generar escenarios futuros junto a estrategias competitivas e innovadoras.</p>

<h3 style="text-align:center"><strong>&iexcl;Te deseamos el mayor de los &eacute;xitos!</strong></h3>', NULL, NULL, '2018-03-21 18:18:46', '2018-03-23 18:02:30', 2, 1, 4, NULL);

INSERT INTO certi_pagina VALUES (13, 'Pensamiento estratégico', 12, 2, '<p>DESCRIPCI&Oacute;N M&Oacute;DULO</p>', '<h2 style="text-align:center"><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_PensamientoEstrategico.jpg" style="width:820px" /></h2>

<h2 style="text-align:center">Pensamiento Estrat&eacute;gico: Competencia del L&iacute;der</h2>

<p>El tema del&nbsp;<em><strong>pensamiento estrat&eacute;gico</strong></em>&nbsp;tiene plena pertinencia en el desarrollo de las competencias gerenciales para el liderazgo, ya que &eacute;ste le posibilita al l&iacute;der&nbsp;<em><strong>definir</strong></em>&nbsp;la situaci&oacute;n actual,&nbsp;<em><strong>evaluar</strong></em>&nbsp;el entorno competitivo y definir las estrategias que le permitir&aacute;n encaminar a sus seguidores en funci&oacute;n de los objetivos planteados por la organizaci&oacute;n.</p>

<p>El&nbsp;<em><strong>pensamiento estrat&eacute;gico&nbsp;</strong></em>es una competencia que se debe desarrollar en los l&iacute;deres de las empresas, para que estos cuenten con la capacidad de asumir los retos inherentes al entorno en el que se desenvuelve la organizaci&oacute;n y sepan guiar a sus subalternos hacia el logro de las metas previstas. Un pensador estrat&eacute;gico eficaz, tiene la capacidad de conectar sus acciones diarias con los objetivos del negocio, debe crear visiones de escenarios futuros y estrategias competitivas e innovadoras para hacerle frente.</p>

<p>En este programa, conocer&aacute;s los principios del pensamiento estrat&eacute;gico. Entenderemos las diferentes herramientas que&nbsp;se utilizan&nbsp;a trav&eacute;s del pensamiento estrat&eacute;gico, para evaluar las circunstancias de la empresa y del entorno, y as&iacute; dise&ntilde;ar los planes que hacen m&aacute;s competitiva a la empresa.</p>

<h3 style="text-align:center"><strong>&iexcl;Bienvenido a este programa y esperamos que lo aproveches!</strong></h3>', NULL, NULL, '2018-03-21 18:20:59', '2018-03-23 18:03:28', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (14, 'Importancia', 13, 3, '<p>DESCRIPCI&Oacute;N MATERIA</p>', '<div id="caja">
<div id="top">
<div id="bannerInt"><img src="https://recursos2puntocero.com/recursos/F_PmtoEstrag_Mo1_Mt1_M1_6elemtos/jQueryAssets/images/banner.jpg" /></div>
</div>
<!--<span class="segun">Según la regulación pueden venderse:</span>-->

<div id="acordeon">
<div id="Accordion1">
<h3><a href="#">Anticipar</a></h3>

<div>
<p>La mayor&iacute;a de los l&iacute;deres se centran en el presente, pero la investigaci&oacute;n muestra que el futuro no sigue una l&iacute;nea recta. Los l&iacute;deres estrat&eacute;gicos deben supervisar de forma proactiva el entorno para prever los cambios de la industria - incluso en la periferia - para que puedan prepararse para las amenazas y oportunidades resultantes.</p>
</div>

<h3><a href="#">Retar</a></h3>

<div>
<p>Si bien la sabidur&iacute;a convencional es tentadora, los pensadores estrat&eacute;gicos cuestionan todo en lugar de aceptar la informaci&oacute;n a su valor nominal. Es necesario replantear problemas para comprender las causas profundas, desafiar las creencias y las mentalidades actuales, y as&iacute; descubrir la hipocres&iacute;a, la manipulaci&oacute;n y el sesgo.</p>
</div>

<h3><a href="#">Interpretar</a></h3>

<div>
<p>Se debe interpretar anticipando el cambio y desafiando las convenciones sobre datos y cifras que deben ser analizadas cuidadosamente para producir resultados viables y valiosos. Los l&iacute;deres estrat&eacute;gicos deben comparar y contrastar estos puntos de datos de manera poco convencional, y probar m&uacute;ltiples hip&oacute;tesis antes de llegar a conclusiones.</p>
</div>

<h3><a href="#">Decidir</a></h3>

<div>
<p>La indecisi&oacute;n, tambi&eacute;n conocida como la par&aacute;lisis del an&aacute;lisis, a menudo impide que los l&iacute;deres act&uacute;en con rapidez, dando lugar a la p&eacute;rdida de oportunidades. Los l&iacute;deres estrat&eacute;gicos se valen de los procesos para una efectiva toma de decisiones. Equilibran la velocidad, el rigor, la calidad y la agilidad para actuar con ejecutividad.</p>
</div>

<h3><a href="#">Alinear</a></h3>

<div>
<p>Bienvenidos los l&iacute;deres estrat&eacute;gicos a la diversidad de opiniones y puntos de vista, pero tambi&eacute;n deben saber c&oacute;mo y cu&aacute;ndo alinear agendas divergentes para trabajar hacia un objetivo com&uacute;n. Es importante la participaci&oacute;n activa de las partes interesadas para fomentar el di&aacute;logo abierto que ayuda a construir la confianza y llegar a un consenso.</p>
</div>

<h3><a href="#">Saber</a></h3>

<div>
<p>Los l&iacute;deres animan a sus subalternos a aprender y aceptar comentarios, as&iacute; como ver el &eacute;xito y el fracaso como fuentes de informaci&oacute;n cr&iacute;tica. Apoyan las autocr&iacute;ticas rigurosas, son &aacute;giles, corrigen r&aacute;pidamente y celebran las fallas y el &eacute;xito.</p>
</div>
</div>
</div>
</div>

<hr />
<h3 style="text-align:center">A lo largo del programa contar&aacute;s con la oportunidad de capacitarte en todos estos aspectos. Te invitamos a conocerlos y aplicarlos en tu &aacute;mbito laboral.</h3>

<hr />', NULL, NULL, '2018-03-21 18:28:30', '2018-03-23 18:19:53', 2, 1, 1, NULL);
INSERT INTO certi_pagina VALUES (15, 'Definiciones', 14, 4, '<p>El pensamiento estrat&eacute;gico es una competencia gerencial que implica el entendimiento de las circunstancias propias de la empresa, su entorno y su relaci&oacute;n con la estrategia, concatenados con la visi&oacute;n de la organizaci&oacute;n. El pensamiento estrat&eacute;gico es el primer paso para elaborar la planificaci&oacute;n estrat&eacute;gica. En el siguiente recurso podr&aacute;s conocer m&aacute;s acerca de este tema.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_definicion2.jpg" style="width:820px" /></p>

<p>La idea del&nbsp;<strong>pensamiento estrat&eacute;gico</strong>&nbsp;proviene de Henry Mintzberg, un l&iacute;der pensador en el campo de la gesti&oacute;n estrat&eacute;gica. Mintzberg considera a la creaci&oacute;n de una estrategia como la concreci&oacute;n y realizaci&oacute;n de una&nbsp;<strong>visi&oacute;n</strong>. Es decir, el&nbsp;<strong><em>pensamiento estrat&eacute;gico</em></strong>&nbsp;<strong><em>es un proceso mediante el cual una persona aprende a convertir la&nbsp;visi&oacute;n&nbsp;de la empresa en una realidad.</em></strong></p>

<p>El pensamiento estrat&eacute;gico se trata de un&nbsp;<strong><em>proceso reflexivo</em></strong>&nbsp;que determina la intenci&oacute;n y el perfil de lo que la organizaci&oacute;n quiere llegar a ser.&nbsp;<strong><em>El pensamiento estrat&eacute;gico es quien determina la estrategia a seguir.</em></strong></p>

<h3><strong>Pensamiento Estrat&eacute;gico</strong></h3>

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

<p>&nbsp;</p>', 'recursos/paginas/f_definicion_b.jpg', NULL, '2018-03-21 18:36:24', '2018-03-23 18:22:22', 2, 1, 1, NULL);

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
<video class="video-js vjs-fluid" controls="" data-setup="{&quot;playbackRates&quot;: [1, 1.5, 2], &quot;fluid&quot;: true, &quot;controlBar&quot;: { &quot;muteToggle&quot;: false }}" id="my-player" poster="../../web/img/OBDL960.jpg" preload="auto"><source src="https://recursos2puntocero.com/recursos/video/F_PensamientoVsPlanificacion.mp4" type="video/mp4" />
<p class="vjs-no-js">To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5 </a></p>
</video>
</p>', 'recursos/paginas/f_pensamientovs.planificacion_b.jpg', NULL, '2018-03-21 18:39:22', '2018-03-21 18:39:22', 2, 1, 2, NULL);
INSERT INTO certi_pagina VALUES (17, 'Actividades', 14, 4, '<p>La clave de un pensador estrat&eacute;gico es vincular sus acciones cotidianas en funci&oacute;n de la visi&oacute;n, contrastando el entorno, la posici&oacute;n competitiva y los diferentes caminos que permiten el logro de las metas de la organizaci&oacute;n. A continuaci&oacute;n se presentan los 19 h&aacute;bitos de estrategias inteligentes que puedes desplegar de forma cotidiana.</p>', '<p><iframe frameborder="0" height="1300" scrolling="no" src="https://recursos2puntocero.com/recursos/F_PmtoEstrag_Mo1_Mt1_R3_19HabitIntlgte/19habitos.html" width="820"></iframe></p>

<p>jjh</p>', 'recursos/paginas/f_actividades_b.jpg', NULL, '2018-03-21 18:44:00', '2018-03-21 19:57:08', 2, 1, 3, NULL);
INSERT INTO certi_pagina VALUES (18, 'Capacidades para el proceso estratégico', 14, 4, '<p>Los ejecutivos y directivos son los encargados de encauzar las premisas establecidas por la visi&oacute;n de una organizaci&oacute;n, comunic&aacute;ndolas a los trabajadores de forma persistente. As&iacute; pues, los l&iacute;deres de procesos deben otorgar facultades a los participantes de niveles bajos, de tal manera que puedan crear estrategias alineadas con la visi&oacute;n de la compa&ntilde;&iacute;a, ayud&aacute;ndolos a definir acerca de qu&eacute; tan bien est&aacute;n cumpliendo sus labores. Te invitamos a conocer las cinco capacidades del proceso estrat&eacute;gico, esbozado por Warren Bennis, a trav&eacute;s del siguiente recurso.</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_Capacidades.jpg" style="width:820px" /></p>

<p>Warren Bennis, conocido estudioso del&nbsp;<em><strong>liderazgo</strong></em>, asegura que los directores generales de las organizaciones que despliegan las cinco capacidades establecidas por el proceso estrat&eacute;gico, obtienen el &eacute;xito. En este sentido, te invitamos a conocer cada una de ellas.</p>

<p>La primera es la&nbsp;<strong>Visi&oacute;n.&nbsp;</strong>Capacidad para crear y expresar una visi&oacute;n obligatoria de un estado deseado para las cosas, y de impartir claridad a la misma, a trav&eacute;s de la suscripci&oacute;n de un compromiso con ella.</p>

<p>La segunda es la&nbsp;<strong>Comunicaci&oacute;n y &nbsp;alineaci&oacute;n.&nbsp;</strong>Capacidad para expresar la visi&oacute;n a los miembros de la organizaci&oacute;n, consiguiendo el apoyo de sus m&uacute;ltiples bases.</p>

<p>La tercera es la&nbsp;<strong>Persistencia, consistencia y enfoque.&nbsp;</strong>Capacidad para conservar el rumbo de la organizaci&oacute;n, sobre todo cuando las cosas se ponen dif&iacute;ciles.</p>

<p>La cuarta es la&nbsp;<strong>Delegaci&oacute;n de facultades.</strong>&nbsp;Capacidad para crear ambientes que puedan explotar y encauzar las energ&iacute;as y las facultades necesarias para producir los resultados deseados.</p>

<p>La quinta es el&nbsp;<strong>Aprendizaje de la organizaci&oacute;n.</strong>&nbsp;Capacidad para encontrar v&iacute;as que le permitan a los l&iacute;deres supervisar los procesos de la organizaci&oacute;n, relacionar los resultados con los objetivos establecidos, crear y usar informaci&oacute;n actualizada para la revisi&oacute;n de acciones pasadas y fundamentar las futuras, as&iacute; como decidir c&oacute;mo y cu&aacute;ndo la estructura de la empresa y el personal clave, deben ser sujetos a reestructuraci&oacute;n o reasignaci&oacute;n ante condiciones nuevas.</p>

<div class="audio-player"><img alt="" src="../../web/img/Podcast.jpg" style="width:100%" /></div>', 'recursos/paginas/f_capacidades_b.jpg', NULL, '2018-03-21 18:52:52', '2018-03-21 18:52:52', 2, 1, 4, NULL);
INSERT INTO certi_pagina VALUES (20, 'Corto y largo plazo', 13, 3, '<p>Prueba</p>', '<p><img alt="" src="https://www.formacion2puntocero.com/produccion/admini/ckfinder/userfiles/images/F_CortoYLargoPlaza.jpg" style="width:820px" /></p>

<h3>Pensamiento estrat&eacute;gico a corto y largo plazo</h3>

<p>La capacidad de diferenciar entre el&nbsp;<em><strong>pensamiento a corto y a largo plazo</strong></em>, y de lograr un equilibrio entre los dos, es parte integral de la estrategia. Comprende la importancia de ambos tipos de pensamiento y as&iacute; podr&aacute;s obtener la combinaci&oacute;n correcta.</p>

<h3>Conocer los escollos</h3>

<p>Si te concentras por completo en el &eacute;xito a corto plazo, te arriesgaras al fracaso en el largo plazo. Por ejemplo, puedes descubrir que vendes productos obsoletos o atacas mercados cuyos requerimientos han cambiado. Si haces un indebido &eacute;nfasis en la planificaci&oacute;n a largo plazo, inevitablemente los negocios resultar&aacute;n perjudicados.</p>

<p>La clave es que<em><strong>&nbsp;te concentres en el presente y vigiles el futuro&nbsp;</strong></em>para asegurar que las buenas decisiones actuales sean ben&eacute;ficas en el ma&ntilde;ana.</p>

<ul>
	<li>Ten confianza en el futuro pero se realista con respecto a lo que puedes lograr.</li>
	<li>Esfu&eacute;rzate por cumplir las metas a largo plazo y alcanzar resultados inmediatos.</li>
</ul>

<h3>Considerar el largo plazo</h3>

<p>Algunas veces el cierre de una venta r&aacute;pida no es positivo para los intereses a largo plazo de la empresa. Aunque esto pueda significar que te toque enfrentar una situaci&oacute;n dif&iacute;cil con tu cliente a corto plazo, es m&aacute;s probable que le proporciones la m&aacute;xima satisfacci&oacute;n en el largo plazo.</p>

<h3>Lograr el equilibrio</h3>

<p>Alcanzar el equilibrio adecuado entre el pensamiento a corto y a largo plazo requiere&nbsp;<em><strong>esfuerzo</strong></em>&nbsp;y&nbsp;<em><strong>disciplina</strong></em>. No es sorprendente que, a menos que un equipo est&eacute; decidido a asignar tiempo a los asuntos estrat&eacute;gicos, las tareas operativas de corto plazo tengan siempre prioridad.</p>

<p>Programa el tiempo y los recursos apropiados para las operaciones o la estrategia, y nunca olvides respetar tu plan. Por ejemplo, puedes decidir reservar tres meses al desarrollo de una nueva estrategia, dedicando dos d&iacute;as a las reuniones de arranque y un d&iacute;a a la semana para las posteriores. Esto deja tiempo suficiente para que puedas ocuparte de los aspectos operativos.</p>

<h3>Trabajar con estrategia</h3>

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

<p>To view this video please enable JavaScript, and consider upgrading to a web browser that / Para ver este video por favor habilite JavaScript, y considere actualizar el navegador web que utiliza. <a href="http://videojs.com/html5-video-support/" target="_blank"> Para soporte del video HTML5</a></p>

<p>&nbsp;</p>', NULL, 'https://recursos2puntocero.com/recursos/FORMACION/pdf/207PorQueCompramos.pdf', '2018-03-23 18:34:12', '2018-03-23 18:43:35', 2, 1, 2, NULL);

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