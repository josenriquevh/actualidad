-- Function: fncantidad_programados(integer, integer)

-- DROP FUNCTION fncantidad_programados(integer, integer);

CREATE OR REPLACE FUNCTION fncantidad_programados(ptipo_destino_id integer,
    pentidad_id integer)
  RETURNS integer AS
$BODY$
declare
    c INTEGER := 0;          -- Contador de arr
begin

    -- En caso de ser una programacion dirigida a todos los alumnos activos
    IF ptipo_destino_id = 1 THEN
        SELECT COUNT(*) INTO c 
        FROM admin_usuario u 
        WHERE u.activo = true 
            AND u.rol_id = 2 
            AND u.login NOT LIKE 'temp%';

    -- En caso de ser una programacion dirigida a todos los profesores activos
    ELSIF ptipo_destino_id = 2 THEN
        SELECT COUNT(*) INTO c 
        FROM admin_usuario u 
        WHERE u.activo = true 
            AND u.rol_id = 3 
            AND u.login NOT LIKE 'temp%';

    -- En caso de ser una programacion dirigida a todos los alumnos activos de una sección
    ELSIF ptipo_destino_id = 3 THEN
        SELECT COUNT(*) INTO c 
        FROM admin_usuario u 
        WHERE u.activo = true 
            AND u.rol_id = 2 
            AND u.login NOT LIKE 'temp%' 
            AND u.id IN (
                    SELECT us.usuario_id FROM admin_usuario_seccion us WHERE us.seccion_id = pentidad_id
                );

    -- En caso de ser una programacion dirigida a todos los profesores activos de una sección
    ELSIF ptipo_destino_id = 4 THEN
        SELECT COUNT(*) INTO c 
        FROM admin_usuario u 
        WHERE u.activo = true 
            AND u.rol_id = 3 
            AND u.login NOT LIKE 'temp%' 
            AND u.id IN (
                    SELECT us.usuario_id FROM admin_usuario_seccion us WHERE us.seccion_id = pentidad_id
                );

    -- En caso de ser una programacion dirigida a todos los usuarios activos de un libro
    ELSIF ptipo_destino_id = 5 THEN
        SELECT COUNT(*) INTO c 
        FROM admin_usuario u 
        WHERE u.activo = true 
            AND u.rol_id != 1 
            AND u.login NOT LIKE 'temp%' 
            AND u.id IN (
                    SELECT pu.usuario_id FROM ea_pagina_usuario pu WHERE pu.pagina_id = pentidad_id AND pu.activo = true AND pu.fecha_vencimiento >= now()
                );

    -- En caso de ser una programacion dirigida a un grupo de usuarios
    ELSE 
        SELECT COUNT(*) INTO c 
        FROM admin_notificacion_programada 
        WHERE grupo_id = pentidad_id;
    
    END IF;
  
    RETURN c;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

  --select * from fncantidad_programados(2,1) as resultado;
