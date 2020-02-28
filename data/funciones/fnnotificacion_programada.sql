-- Function: fnnotificacion_programada(integer)

-- DROP FUNCTION fnnotificacion_programada(integer);

CREATE OR REPLACE FUNCTION fnnotificacion_programada(pnotificacion_programada_id integer)
  RETURNS SETOF text[] AS
$BODY$
DECLARE
  arr text[];
  reg  record;
  rst  record;
  rstp record;
  i INTEGER := 0;
  str text;
BEGIN

    SELECT INTO reg np.id as id, np.tipo_destino_id as tipo_destino_id, np.entidad_id as entidad_id, n.asunto as asunto, n.mensaje as mensaje 
    FROM admin_notificacion_programada np 
    JOIN admin_notificacion n ON np.notificacion_id = n.id 
    WHERE np.id = pnotificacion_programada_id;

    -- En caso de ser una programacion dirigida a todos los alumnos activos
    IF reg.tipo_destino_id = 1 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo as correo 
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.login NOT LIKE 'temp%' 
                AND u.rol_id = 2 
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los profesores activos
    ELSIF reg.tipo_destino_id = 2 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo as correo 
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.rol_id = 3 
                AND u.login NOT LIKE 'temp%'
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los alumnos activos de una sección
    ELSIF reg.tipo_destino_id = 3 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo as correo 
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.rol_id = 2 
                AND u.login NOT LIKE 'temp%' 
                AND u.id IN (
                    SELECT us.usuario_id FROM admin_usuario_seccion us WHERE us.seccion_id = reg.entidad_id
                )
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los profesores activos de una sección
    ELSIF reg.tipo_destino_id = 4 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo as correo 
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.rol_id = 3 
                AND u.login NOT LIKE 'temp%' 
                AND u.id IN (
                    SELECT us.usuario_id FROM admin_usuario_seccion us WHERE us.seccion_id = reg.entidad_id
                )
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;

    -- En caso de ser una programacion dirigida a todos los usuarios activos de un libro
    ELSIF reg.tipo_destino_id = 5 THEN
        FOR rst IN 
            SELECT u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo as correo 
            FROM admin_usuario u 
            WHERE u.activo = true 
                AND u.rol_id != 1 
                AND u.login NOT LIKE 'temp%' 
                AND u.id IN (
                    SELECT pu.usuario_id FROM ea_pagina_usuario pu WHERE pu.pagina_id = reg.entidad_id AND pu.activo = true AND pu.fecha_vencimiento >= now()
                )
            ORDER BY u.id ASC LOOP
            str = reg.id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;
        
    -- En caso de ser una programacion dirigida a un grupo de usuarios
    ELSE 
        FOR rst IN 
            SELECT np.id as np_id, u.id as id, u.login as login, u.clave as clave, u.nombre as nombre, u.apellido as apellido, u.correo as correo 
            FROM admin_usuario u, admin_notificacion_programada np 
            WHERE np.grupo_id = reg.id 
                AND np.entidad_id = u.id 
                AND u.activo = true 
            ORDER BY u.id ASC LOOP
            str = rst.np_id || '__' || rst.id || '__' || rst.login || '__' || rst.clave || '__' || rst.nombre || '__' || rst.apellido || '__' || rst.correo || '__' || reg.asunto || '__' || reg.mensaje;
            arr = '{}';
            arr[i] = str;
            RETURN NEXT arr;
            i = i + 1;
        END LOOP;
    
    END IF;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fnnotificacion_programada(20);