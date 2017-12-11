CREATE TABLE admin_rol(
-- Attributes --
id serial,
nombre varchar(50),
descripcion text,
 PRIMARY KEY (id));

CREATE TABLE admin_aplicacion(
-- Attributes --
id serial,
nombre varchar(100),
url varchar(50),
icono varchar(20),
activo boolean,
aplicacion_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (aplicacion_id) REFERENCES admin_aplicacion (id));

CREATE TABLE admin_permiso(
-- Attributes --
id serial,
aplicacion_id integer,
rol_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (aplicacion_id) REFERENCES admin_aplicacion (id),
 FOREIGN KEY (rol_id) REFERENCES admin_rol (id));

CREATE TABLE admin_pais(
-- Attributes --
id character(3),
nombre character(52),
continente character(100),
region character(26),
nombre_local character(45),
capital integer,
id2 character(2),
 PRIMARY KEY (id));

CREATE TABLE admin_empresa(
-- Attributes --
id serial,
nombre varchar(100),
rif varchar(20),
correo_principal varchar(100),
activo boolean,
telefono_principal varchar(20),
fecha_creacion timestamp without time zone,
direccion text,
bienvenida text,
pais_id character(3), 
 PRIMARY KEY (id),
 FOREIGN KEY (pais_id) REFERENCES admin_pais (id));

CREATE TABLE admin_nivel(
-- Attributes --
id serial,
nombre varchar(50),
empresa_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id));

CREATE TABLE admin_usuario(
-- Attributes --
id serial,
login varchar(50),
clave varchar(50),
nombre varchar(50),
apellido varchar(50),
correo_personal varchar(100),
correo_corporativo varchar(100),
activo boolean,
fecha_registro timestamp without time zone,
fecha_nacimiento date,
pais_id varchar(3),
ciudad varchar(50),
region varchar(50),
empresa_id integer,
foto varchar(250),
division_funcional varchar(100),
cargo varchar(100),
nivel_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id),
 FOREIGN KEY (nivel_id) REFERENCES admin_nivel (id),
 FOREIGN KEY (pais_id) REFERENCES admin_pais (id));

CREATE TABLE admin_rol_usuario(
-- Attributes --
id serial,
rol_id integer,
usuario_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (rol_id) REFERENCES admin_rol (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_sesion(
-- Attributes --
id serial,
fecha_ingreso timestamp without time zone,
fecha_request timestamp without time zone,
usuario_id integer,
disponible boolean,
 PRIMARY KEY (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE certi_grupo(
-- Attributes --
id serial,
nombre varchar(100),
orden integer,
empresa_id integer,
imagen_certificado varchar(250),
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id));

CREATE TABLE certi_categoria(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

CREATE TABLE certi_estatus_contenido(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

CREATE TABLE certi_pagina(
-- Attributes --
id serial,
nombre varchar(100),
pagina_id integer,
categoria_id integer,
descripcion text,
contenido text,
foto varchar(250),
pdf varchar(250),
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
estatus_contenido_id integer,
usuario_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id),
 FOREIGN KEY (categoria_id) REFERENCES certi_categoria (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (estatus_contenido_id) REFERENCES certi_estatus_contenido (id));

CREATE TABLE certi_pagina_empresa(
-- Attributes --
id serial,
empresa_id integer,
pagina_id integer,
activo boolean,
fecha_inicio date,
fecha_vencimiento date,
prueba_activa boolean,
max_intentos integer,
puntaje_aprueba numeric(3,2),
muro_activo boolean,
imagen_certificado varchar(250),
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id));

CREATE TABLE certi_prueba(
-- Attributes --
id serial,
nombre varchar(350),
pagina_id integer,
cantidad_preguntas integer,
cantidad_mostrar integer,
duracion time without time zone,
usuario_id integer,
estatus_contenido_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (estatus_contenido_id) REFERENCES certi_estatus_contenido (id));

CREATE TABLE certi_grupo_pagina(
-- Attributes --
id serial,
grupo_id integer,
pagina_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (grupo_id) REFERENCES certi_grupo (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id));

CREATE TABLE certi_tipo_elemento(
-- Attributes --
id serial,
nombre varchar(50),
 PRIMARY KEY (id));

CREATE TABLE certi_tipo_pregunta(
-- Attributes --
id serial,
nombre varchar(50),
 PRIMARY KEY (id));

CREATE TABLE certi_pregunta(
-- Attributes --
id serial,
enunciado varchar(500),
imagen varchar(500),
prueba_id integer,
tipo_pregunta_id integer,
tipo_elemento_id integer,
usuario_id integer,
estatus_contenido_id integer,
valor numeric(10,2),
pregunta_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (prueba_id) REFERENCES certi_prueba (id),
 FOREIGN KEY (tipo_pregunta_id) REFERENCES certi_tipo_pregunta (id),
 FOREIGN KEY (tipo_elemento_id) REFERENCES certi_tipo_elemento (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (estatus_contenido_id) REFERENCES certi_estatus_contenido (id),
 FOREIGN KEY (pregunta_id) REFERENCES certi_pregunta (id));

CREATE TABLE certi_opcion(
-- Attributes --
id serial,
descripcion varchar(500),
imagen varchar(500),
prueba_id integer,
usuario_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (prueba_id) REFERENCES certi_prueba (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE certi_pregunta_opcion(
-- Attributes --
id serial,
pregunta_id integer,
opcion_id integer,
correcta boolean,
 PRIMARY KEY (id),
 FOREIGN KEY (pregunta_id) REFERENCES certi_pregunta (id),
 FOREIGN KEY (opcion_id) REFERENCES certi_opcion (id));

CREATE TABLE certi_pregunta_asociacion(
-- Attributes --
id serial,
pregunta_id integer,
preguntas varchar(50),
opciones varchar(50),
 PRIMARY KEY (id),
 FOREIGN KEY (pregunta_id) REFERENCES certi_pregunta (id));

CREATE TABLE certi_respuesta(
-- Attributes --
id serial,
pregunta_id integer,
opcion_id integer,
usuario_id integer,
fecha_registro timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pregunta_id) REFERENCES certi_pregunta (id),
 FOREIGN KEY (opcion_id) REFERENCES certi_opcion (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE certi_estatus_pagina(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

CREATE TABLE certi_pagina_log(
-- Attributes --
id serial,
pagina_id integer,
usuario_id integer,
fecha_inicio timestamp without time zone,
fecha_fin timestamp without time zone,
porcentaje_avance numeric(3,2),
estatus_pagina_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (estatus_pagina_id) REFERENCES certi_estatus_pagina (id));

CREATE TABLE certi_prueba_log(
-- Attributes --
id serial,
prueba_id integer,
usuario_id integer,
fecha_inicio timestamp without time zone,
fecha_fin timestamp without time zone,
porcentaje_avance numeric(3,2),
correctas integer,
erradas integer,
nota numeric(3,2),
estado varchar(15),
 PRIMARY KEY (id),
 FOREIGN KEY (prueba_id) REFERENCES certi_prueba (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_noticia(
-- Attributes --
id serial,
empresa_id integer,
tipo varchar(20),
usuario_id integer,
fecha_registro timestamp without time zone,
resumen text,
contenido text,
fecha_publicacion date,
fecha_vencimiento date,
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE certi_muro(
-- Attributes --
id serial,
mensaje varchar(350),
pagina_id integer,
usuario_id integer,
muro_id integer,
fecha_registro timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (muro_id) REFERENCES certi_muro (id));

CREATE TABLE certi_foro(
-- Attributes --
id serial,
mensaje varchar(350),
pagina_id integer,
usuario_id integer,
foro_id integer,
fecha_registro timestamp without time zone,
pdf varchar(250),
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES certi_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (foro_id) REFERENCES certi_foro (id));

CREATE TABLE admin_tipo_notificacion(
-- Attributes --
id serial,
nombre varchar(100),
 PRIMARY KEY (id));

CREATE TABLE admin_notificacion(
-- Attributes --
id serial,
tipo_notificacion_id integer,
valor_notificacion integer,
usuario_id integer,
leido boolean,
usuario_tutor_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (tipo_notificacion_id) REFERENCES admin_tipo_notificacion (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (usuario_tutor_id) REFERENCES admin_usuario (id));

CREATE TABLE certi_nivel_pagina(
-- Attributes --
id serial,
nivel_id integer,
pagina_empresa_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (nivel_id) REFERENCES admin_nivel (id),
 FOREIGN KEY (pagina_empresa_id) REFERENCES certi_pagina_empresa (id));

CREATE TABLE admin_layout(
-- Attributes --
id serial,
twig varchar(100),
 PRIMARY KEY (id));

CREATE TABLE admin_preferencia(
-- Attributes --
id serial,
empresa_id integer,
layout_id integer,
title varchar(200),
css varchar(100),
logo varchar(250),
favicon varchar(250),
usuario_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (layout_id) REFERENCES admin_layout (id));

