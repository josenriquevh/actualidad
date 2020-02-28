-- Function: fnactualizar_secuencias()

-- DROP FUNCTION fnactualizar_secuencias();

CREATE OR REPLACE FUNCTION fnactualizar_secuencias()
  RETURNS text AS
$BODY$
declare
    seq integer;	-- Secuencia actual
    new_seq integer;	-- Nuevo valor de secuencia
    i INTEGER := 0;	-- Cantidad de tablas
    reg  record;
    str text;		-- Return
begin

    SELECT MAX(id) INTO seq from admin_alarma;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_alarma', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_alarma: %', reg;

    SELECT MAX(id) INTO seq from admin_ayuda_interactivo;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_ayuda_interactivo', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_ayuda_interactivo: %', reg;

    SELECT MAX(id) INTO seq from admin_ciudad;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_ciudad', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_ciudad: %', reg;

    SELECT MAX(id) INTO seq from admin_colegio;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_colegio', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_colegio: %', reg;

    SELECT MAX(id) INTO seq from admin_correo;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_correo', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_correo: %', reg;

    SELECT MAX(id) INTO seq from admin_empresa;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_empresa', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_empresa: %', reg;

    SELECT MAX(id) INTO seq from admin_estilo;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_estilo', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_estilo: %', reg;

    SELECT MAX(id) INTO seq from admin_grado;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_grado', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_grado: %', reg;

    SELECT MAX(id) INTO seq from admin_noticia;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_noticia', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_noticia: %', reg;

    SELECT MAX(id) INTO seq from admin_notificacion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_notificacion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_notificacion: %', reg;

    SELECT MAX(id) INTO seq from admin_notificacion_programada;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_notificacion_programada', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_notificacion_programada: %', reg;

    SELECT MAX(id) INTO seq from admin_provincia;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_provincia', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_provincia: %', reg;

    SELECT MAX(id) INTO seq from admin_rol;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_rol', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_rol: %', reg;

    SELECT MAX(id) INTO seq from admin_seccion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_seccion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_seccion: %', reg;

    SELECT MAX(id) INTO seq from admin_sesion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_sesion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_sesion: %', reg;

    SELECT MAX(id) INTO seq from admin_tipo_alarma;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_alarma', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_tipo_alarma: %', reg;

    SELECT MAX(id) INTO seq from admin_tipo_correo;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_correo', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_tipo_correo: %', reg;

    SELECT MAX(id) INTO seq from admin_tipo_destino;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_destino', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_tipo_destino: %', reg;

    SELECT MAX(id) INTO seq from admin_tipo_noticia;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tipo_noticia', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_tipo_noticia: %', reg;

    SELECT MAX(id) INTO seq from admin_tutorial;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_tutorial', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_tutorial: %', reg;

    SELECT MAX(id) INTO seq from admin_usuario;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_usuario', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_usuario: %', reg;

    SELECT MAX(id) INTO seq from admin_usuario_colegio;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_usuario_colegio', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_usuario_colegio: %', reg;

    SELECT MAX(id) INTO seq from admin_usuario_seccion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('admin_usuario_seccion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para admin_usuario_seccion: %', reg;

    SELECT MAX(id) INTO seq from ea_certificado;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_certificado', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_certificado: %', reg;

    SELECT MAX(id) INTO seq from ea_estatus_contenido;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_estatus_contenido', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_estatus_contenido: %', reg;

    SELECT MAX(id) INTO seq from ea_estatus_pagina;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_estatus_pagina', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_estatus_pagina: %', reg;

    SELECT MAX(id) INTO seq from ea_foro;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_foro', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_foro: %', reg;

    SELECT MAX(id) INTO seq from ea_foro_archivo;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_foro_archivo', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_foro_archivo: %', reg;

    SELECT MAX(id) INTO seq from ea_opcion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_opcion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_opcion: %', reg;

    SELECT MAX(id) INTO seq from ea_pagina;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pagina', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pagina: %', reg;

    SELECT MAX(id) INTO seq from ea_pagina_liberada;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pagina_liberada', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pagina_liberada: %', reg;

    SELECT MAX(id) INTO seq from ea_pagina_log;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pagina_log', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pagina_log: %', reg;

    SELECT MAX(id) INTO seq from ea_pagina_usuario;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pagina_usuario', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pagina_usuario: %', reg;

    SELECT MAX(id) INTO seq from ea_pregunta;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pregunta', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pregunta: %', reg;

    SELECT MAX(id) INTO seq from ea_pregunta_asociacion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pregunta_asociacion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pregunta_asociacion: %', reg;

    SELECT MAX(id) INTO seq from ea_pregunta_opcion;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_pregunta_opcion', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_pregunta_opcion: %', reg;

    SELECT MAX(id) INTO seq from ea_profesor_alumno;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_profesor_alumno', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_profesor_alumno: %', reg;

    SELECT MAX(id) INTO seq from ea_prueba;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_prueba', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_prueba: %', reg;

    SELECT MAX(id) INTO seq from ea_prueba_log;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_prueba_log', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_prueba_log: %', reg;

    SELECT MAX(id) INTO seq from ea_respuesta;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_respuesta', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_respuesta: %', reg;

    SELECT MAX(id) INTO seq from ea_tipo_certificado;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_tipo_certificado', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_tipo_certificado: %', reg;

    SELECT MAX(id) INTO seq from ea_tipo_elemento;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_tipo_elemento', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_tipo_elemento: %', reg;

    SELECT MAX(id) INTO seq from ea_tipo_foro;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_tipo_foro', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_tipo_foro: %', reg;

    SELECT MAX(id) INTO seq from ea_tipo_pagina;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_tipo_pagina', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_tipo_pagina: %', reg;

    SELECT MAX(id) INTO seq from ea_tipo_pregunta;
    IF seq IS NULL THEN 
        new_seq = 1;
    ELSE 
        new_seq = seq+1;
    END IF;
    SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('ea_tipo_pregunta', 'id'), new_seq, false) INTO reg;
    i = i+1;
    raise notice 'Nueva secuencia para ea_tipo_pregunta: %', reg;

    str = 'Cantidad de secuencias actualizadas: ' || i;

    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fnactualizar_secuencias();