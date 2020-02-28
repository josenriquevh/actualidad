-- Function: fnactivos()

-- DROP FUNCTION fnactivos();

CREATE OR REPLACE FUNCTION fnactivos()
  RETURNS text AS
$BODY$
declare
    colegios integer;	-- Colegios que ya tiene registrado alumnos asociados
    profesores integer;	-- Usuarios activos con rol de Profesor
    alumnos integer;	-- Usuarios activos con rol de Alumno
    str text;		-- Cadena para debug
begin

    -- Colegios
    SELECT COUNT(c.id) INTO colegios FROM admin_colegio c 
    WHERE c.id IN 
        (SELECT s.colegio_id FROM admin_seccion s
        WHERE s.id IN 
            (SELECT us.seccion_id FROM admin_usuario_seccion us)
        );

    -- Profesores
    SELECT COUNT(u.id) INTO profesores FROM admin_usuario u 
    WHERE u.activo = true 
    AND u.rol_id = 3;

    -- Alumnos
    SELECT COUNT(u.id) INTO alumnos FROM admin_usuario u 
    WHERE u.activo = true 
    AND u.rol_id = 2;

    str = colegios || '__' || profesores  || '__' || alumnos;

    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fnactivos();