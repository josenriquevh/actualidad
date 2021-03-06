ALTER TABLE admin_alarma ALTER COLUMN id SET DEFAULT nextval('admin_alarma_id_seq');
ALTER TABLE admin_ciudad ALTER COLUMN id SET DEFAULT nextval('admin_ciudad_id_seq');
ALTER TABLE admin_colegio ALTER COLUMN id SET DEFAULT nextval('admin_colegio_id_seq');
ALTER TABLE admin_correo ALTER COLUMN id SET DEFAULT nextval('admin_correo_id_seq');
ALTER TABLE admin_grado ALTER COLUMN id SET DEFAULT nextval('admin_grado_id_seq');
ALTER TABLE admin_noticia ALTER COLUMN id SET DEFAULT nextval('admin_noticia_id_seq');
ALTER TABLE admin_notificacion ALTER COLUMN id SET DEFAULT nextval('admin_notificacion_id_seq');
ALTER TABLE admin_notificacion_programada ALTER COLUMN id SET DEFAULT nextval('admin_notificacion_programada_id_seq');
ALTER TABLE admin_provincia ALTER COLUMN id SET DEFAULT nextval('admin_provincia_id_seq');
ALTER TABLE admin_rol ALTER COLUMN id SET DEFAULT nextval('admin_rol_id_seq');
ALTER TABLE admin_seccion ALTER COLUMN id SET DEFAULT nextval('admin_seccion_id_seq');
ALTER TABLE admin_sesion ALTER COLUMN id SET DEFAULT nextval('admin_sesion_id_seq');
ALTER TABLE admin_tipo_alarma ALTER COLUMN id SET DEFAULT nextval('admin_tipo_alarma_id_seq');
ALTER TABLE admin_tipo_correo ALTER COLUMN id SET DEFAULT nextval('admin_tipo_correo_id_seq');
ALTER TABLE admin_tipo_destino ALTER COLUMN id SET DEFAULT nextval('admin_tipo_destino_id_seq');
ALTER TABLE admin_tipo_noticia ALTER COLUMN id SET DEFAULT nextval('admin_tipo_noticia_id_seq');
ALTER TABLE admin_usuario ALTER COLUMN id SET DEFAULT nextval('admin_usuario_id_seq');
ALTER TABLE admin_usuario_colegio ALTER COLUMN id SET DEFAULT nextval('admin_usuario_colegio_id_seq');
ALTER TABLE ea_estatus_contenido ALTER COLUMN id SET DEFAULT nextval('ea_estatus_contenido_id_seq');
ALTER TABLE ea_estatus_pagina ALTER COLUMN id SET DEFAULT nextval('ea_estatus_pagina_id_seq');
ALTER TABLE ea_foro ALTER COLUMN id SET DEFAULT nextval('ea_foro_id_seq');
ALTER TABLE ea_foro_archivo ALTER COLUMN id SET DEFAULT nextval('ea_foro_archivo_id_seq');
ALTER TABLE ea_libro ALTER COLUMN id SET DEFAULT nextval('ea_libro_id_seq');
ALTER TABLE ea_libro_usuario ALTER COLUMN id SET DEFAULT nextval('ea_libro_usuario_id_seq');
ALTER TABLE ea_opcion ALTER COLUMN id SET DEFAULT nextval('ea_opcion_id_seq');
ALTER TABLE ea_pagina ALTER COLUMN id SET DEFAULT nextval('ea_pagina_id_seq');
ALTER TABLE ea_pagina_log ALTER COLUMN id SET DEFAULT nextval('ea_pagina_log_id_seq');
ALTER TABLE ea_pregunta ALTER COLUMN id SET DEFAULT nextval('ea_pregunta_id_seq');
ALTER TABLE ea_pregunta_asociacion ALTER COLUMN id SET DEFAULT nextval('ea_pregunta_asociacion_id_seq');
ALTER TABLE ea_pregunta_opcion ALTER COLUMN id SET DEFAULT nextval('ea_pregunta_opcion_id_seq');
ALTER TABLE ea_prueba ALTER COLUMN id SET DEFAULT nextval('ea_prueba_id_seq');
ALTER TABLE ea_prueba_log ALTER COLUMN id SET DEFAULT nextval('ea_prueba_log_id_seq');
ALTER TABLE ea_respuesta ALTER COLUMN id SET DEFAULT nextval('ea_respuesta_id_seq');
ALTER TABLE ea_tipo_elemento ALTER COLUMN id SET DEFAULT nextval('ea_tipo_elemento_id_seq');
ALTER TABLE ea_tipo_libro ALTER COLUMN id SET DEFAULT nextval('ea_tipo_libro_id_seq');
ALTER TABLE ea_tipo_pregunta ALTER COLUMN id SET DEFAULT nextval('ea_tipo_pregunta_id_seq');
ALTER TABLE ea_unidad ALTER COLUMN id SET DEFAULT nextval('ea_unidad_id_seq');