CREATE TABLE admin_rol(
-- Attributes --
id serial,
nombre varchar(50),
descripcion text,
 PRIMARY KEY (id));

CREATE TABLE admin_provincia(
-- Attributes --
id serial,
nombre varchar(100),
 PRIMARY KEY (id));

CREATE TABLE admin_ciudad(
-- Attributes --
id serial,
nombre varchar(100),
provincia_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (provincia_id) REFERENCES admin_provincia (id));

CREATE TABLE admin_colegio(
-- Attributes --
id serial,
nombre varchar(100),
ciudad_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (ciudad_id) REFERENCES admin_ciudad (id));

CREATE TABLE admin_grado(
-- Attributes --
id serial,
nombre varchar(50),
descripcion text,
 PRIMARY KEY (id));

CREATE TABLE admin_seccion(
-- Attributes --
id serial,
colegio_id integer,
grado_id integer,
nombre varchar(10),
 PRIMARY KEY (id),
 FOREIGN KEY (colegio_id) REFERENCES admin_colegio (id),
 FOREIGN KEY (grado_id) REFERENCES admin_grado (id));

CREATE TABLE admin_usuario(
-- Attributes --
id serial,
login varchar(50),
clave varchar(50),
nombre varchar(50),
apellido varchar(50),
correo varchar(100),
activo boolean,
fecha_nacimiento date,
ciudad_id integer,
provincia_id integer,
grado_id integer,
sector varchar(100),
zona varchar(100),
rol_id integer,
foto varchar(500),
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
cookies varchar(100),
 PRIMARY KEY (id),
 FOREIGN KEY (ciudad_id) REFERENCES admin_ciudad (id),
 FOREIGN KEY (provincia_id) REFERENCES admin_provincia (id),
 FOREIGN KEY (grado_id) REFERENCES admin_grado (id),
 FOREIGN KEY (rol_id) REFERENCES admin_rol (id));

CREATE TABLE admin_usuario_seccion(
-- Attributes --
id serial,
usuario_id integer,
seccion_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (seccion_id) REFERENCES admin_seccion (id));

CREATE TABLE admin_sesion(
-- Attributes --
id serial,
fecha_ingreso timestamp without time zone,
fecha_request timestamp without time zone,
usuario_id integer,
disponible boolean,
 PRIMARY KEY (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_tipo_pagina(
-- Attributes --
id serial,
nombre varchar(50),
 PRIMARY KEY (id));

CREATE TABLE ea_estatus_contenido(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

CREATE TABLE admin_estilo(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

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
direccion text,
bienvenida text,
pais_id character(3),
fecha_creacion timestamp without time zone, 
fecha_modificacion timestamp without time zone, 
 PRIMARY KEY (id),
 FOREIGN KEY (pais_id) REFERENCES admin_pais (id));

CREATE TABLE admin_ayuda_interactivo(
-- Attributes --
id serial,
nombre varchar(500),
mensaje varchar(500),
gif varchar(500),
 PRIMARY KEY (id));

CREATE TABLE ea_pagina(
-- Attributes --
id serial,

subtitulo varchar(100),
pagina_id integer,
descripcion text,
contenido text,
foto varchar(500),
pdf varchar(500),
orden integer,
interactivo boolean,
codigo_interactivo varchar(50),
estatus_contenido_id integer, 
tipo_pagina_id integer,
grado_id integer,
empresa_id integer,
pagina_referencia_id integer,
prelada integer,
estilo_id integer,
usuario_id integer,
ayuda_interactivo_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (estatus_contenido_id) REFERENCES ea_estatus_contenido (id),
 FOREIGN KEY (tipo_pagina_id) REFERENCES ea_tipo_pagina (id),
 FOREIGN KEY (grado_id) REFERENCES admin_grado (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id),
 FOREIGN KEY (pagina_referencia_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (prelada) REFERENCES ea_pagina (id),
 FOREIGN KEY (estilo_id) REFERENCES admin_estilo (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (ayuda_interactivo_id) REFERENCES admin_ayuda_interactivo (id));

CREATE TABLE ea_pagina_usuario(
-- Attributes --
id serial,
pagina_id integer,
usuario_id integer,
codigo varchar(20),
activo boolean,
fecha_activacion date,
fecha_inicio date,
fecha_vencimiento date,
renovable boolean,
max_renovaciones integer,
renovaciones integer,
token varchar(20),
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));


CREATE TABLE ea_estatus_pagina(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

CREATE TABLE ea_pagina_log(
-- Attributes --
id serial,
pagina_id integer,
usuario_id integer,
fecha_inicio timestamp without time zone,
fecha_fin timestamp without time zone,
porcentaje_avance numeric(5,2),
estatus_pagina_id integer,
fecha_interaccion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (estatus_pagina_id) REFERENCES ea_estatus_pagina (id));

CREATE TABLE ea_prueba(
-- Attributes --
id serial,
nombre varchar(350),
pagina_id integer,
cantidad_preguntas integer,
cantidad_mostrar integer,
duracion time without time zone,
estatus_contenido_id integer,
usuario_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (estatus_contenido_id) REFERENCES ea_estatus_contenido (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_prueba_log(
-- Attributes --
id serial,
prueba_id integer,
usuario_id integer,
fecha_inicio timestamp without time zone,
fecha_fin timestamp without time zone,
porcentaje_avance numeric(5,2),
correctas integer,
erradas integer,
nota numeric(5,2),
estado varchar(15),
preguntas_erradas varchar(100),
preguntas_correctas varchar(100),
 PRIMARY KEY (id),
 FOREIGN KEY (prueba_id) REFERENCES ea_prueba (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_tipo_elemento(
-- Attributes --
id serial,
nombre varchar(50),
 PRIMARY KEY (id));

CREATE TABLE ea_tipo_pregunta(
-- Attributes --
id serial,
nombre varchar(50),
 PRIMARY KEY (id));

CREATE TABLE ea_pregunta(
-- Attributes --
id serial,
enunciado varchar(500),
imagen varchar(500),
prueba_id integer,
tipo_pregunta_id integer,
tipo_elemento_id integer,
valor numeric(10,2),
pregunta_id integer,
orden integer,
codigo_interactivo varchar(50),
usuario_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (prueba_id) REFERENCES ea_prueba (id),
 FOREIGN KEY (tipo_pregunta_id) REFERENCES ea_tipo_pregunta (id),
 FOREIGN KEY (tipo_elemento_id) REFERENCES ea_tipo_elemento (id),
 FOREIGN KEY (pregunta_id) REFERENCES ea_pregunta (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_opcion(
-- Attributes --
id serial,
descripcion text,
imagen varchar(500),
prueba_id integer,
usuario_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (prueba_id) REFERENCES ea_prueba (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_pregunta_opcion(
-- Attributes --
id serial,
pregunta_id integer,
opcion_id integer,
correcta boolean,
 PRIMARY KEY (id),
 FOREIGN KEY (pregunta_id) REFERENCES ea_pregunta (id),
 FOREIGN KEY (opcion_id) REFERENCES ea_opcion (id));

CREATE TABLE ea_pregunta_asociacion(
-- Attributes --
id serial,
pregunta_id integer,
preguntas varchar(50),
opciones varchar(50),
 PRIMARY KEY (id),
 FOREIGN KEY (pregunta_id) REFERENCES ea_pregunta (id));


CREATE TABLE ea_respuesta(
-- Attributes --
id serial,
nro integer,
pregunta_id integer,
opcion_id integer,
prueba_log_id integer,
fecha_creacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pregunta_id) REFERENCES ea_pregunta (id),
 FOREIGN KEY (opcion_id) REFERENCES ea_opcion (id),
 FOREIGN KEY (prueba_log_id) REFERENCES ea_prueba_log (id));

CREATE TABLE admin_tipo_noticia(
-- Attributes --
id serial,
nombre varchar(20),
 PRIMARY KEY (id));

CREATE TABLE admin_noticia(
-- Attributes --
id serial,
tipo_noticia_id integer,
titulo varchar(500),
resumen text,
contenido text,
fecha_publicacion date,
fecha_vencimiento date,
autor varchar(250),
pdf varchar(250),
imagen varchar(250),
usuario_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (tipo_noticia_id) REFERENCES admin_tipo_noticia (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_tipo_foro(
-- Attributes --
id serial,
nombre varchar(50),
 PRIMARY KEY (id));


CREATE TABLE ea_foro(
-- Attributes --
id serial,
tema varchar(350),
mensaje text,
pagina_id integer,
usuario_id integer,
foro_id integer,
pdf varchar(250),
fecha_publicacion date,
fecha_vencimiento date,
tipo_foro_id integer,
fecha_creacion timestamp without time zone,
fecha_modificacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (foro_id) REFERENCES ea_foro (id),
 FOREIGN KEY (tipo_foro_id) REFERENCES ea_tipo_foro (id));

CREATE TABLE ea_foro_archivo(
-- Attributes --
id serial,
descripcion text,
foro_id integer,
archivo varchar(250),
usuario_id integer,
fecha_creacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (foro_id) REFERENCES ea_foro (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_notificacion(
-- Attributes --
id serial,
asunto varchar(500),
mensaje text,
usuario_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_tipo_destino(
-- Attributes --
id serial,
nombre varchar(100),
 PRIMARY KEY (id));

CREATE TABLE admin_notificacion_programada(
-- Attributes --
id serial,
notificacion_id integer,
tipo_destino_id integer,
entidad_id integer,
usuario_id integer,
grupo_id integer,
fecha_difusion date,
 PRIMARY KEY (id),
 FOREIGN KEY (notificacion_id) REFERENCES admin_notificacion (id),
 FOREIGN KEY (tipo_destino_id) REFERENCES admin_tipo_destino (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (grupo_id) REFERENCES admin_notificacion_programada (id));

CREATE TABLE admin_tipo_correo(
-- Attributes --
id serial,
nombre varchar(100),
 PRIMARY KEY (id));

CREATE TABLE admin_correo(
-- Attributes --
id serial,
tipo_correo_id integer,
entidad_id integer,
usuario_id integer,
correo varchar(100),
fecha timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (tipo_correo_id) REFERENCES admin_tipo_correo (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_tipo_alarma(
-- Attributes --
id serial,
nombre varchar(100),
icono varchar(250),
css varchar(100),
 PRIMARY KEY (id));

CREATE TABLE admin_alarma(
-- Attributes --
id serial,
tipo_alarma_id integer,
descripcion text,
usuario_id integer,
entidad_id integer,
leido boolean,
fecha_creacion timestamp without time zone,
 PRIMARY KEY (id),
 FOREIGN KEY (tipo_alarma_id) REFERENCES admin_tipo_alarma (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE ea_profesor_alumno(
-- Attributes --
id serial,
profesor_id integer,
alumno_id integer,
seccion_id integer,
fecha_seguimiento date,
 PRIMARY KEY (id),
 FOREIGN KEY (profesor_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (alumno_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (seccion_id) REFERENCES admin_seccion (id));

CREATE TABLE ea_pagina_liberada(
-- Attributes --
id serial,
pagina_id integer,
seccion_id integer,
fecha_vencimiento date,
usuario_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (pagina_id) REFERENCES ea_pagina (id),
 FOREIGN KEY (seccion_id) REFERENCES admin_seccion (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_tutorial(
-- Attributes --
id serial,
nombre varchar (250),
pdf varchar(250),
video varchar(250),
imagen varchar(250),
descripcion varchar(1000),
fecha date,
usuario_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id));

CREATE TABLE admin_usuario_colegio(
-- Attributes --
id serial,
usuario_id integer,
colegio_id integer,
 PRIMARY KEY (id),
 FOREIGN KEY (usuario_id) REFERENCES admin_usuario (id),
 FOREIGN KEY (colegio_id) REFERENCES admin_colegio (id));

CREATE TABLE ea_tipo_certificado(
-- Attributes --
id serial NOT NULL,
nombre character varying(20),
 PRIMARY KEY (id));

CREATE TABLE ea_certificado(
-- Attributes --
id serial,
empresa_id integer,
entidad_id integer,
tipo_certificado_id integer,
grado_id integer,
imagen character varying(250),
encabezado text,
descripcion text,
fecha_creacion date,
fecha_modificacion date,
 PRIMARY KEY (id),
 FOREIGN KEY (empresa_id) REFERENCES admin_empresa (id),
 FOREIGN KEY (tipo_certificado_id) REFERENCES ea_tipo_certificado (id),
 FOREIGN KEY (grado_id) REFERENCES admin_grado (id));

