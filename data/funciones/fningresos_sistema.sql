-- Function: fningresos_sistema(integer)

-- DROP FUNCTION fningresos_sistema(integer);

CREATE OR REPLACE FUNCTION fningresos_sistema(
    pusuario_id integer)
  RETURNS text AS
$BODY$
declare
    primeraConexion text;	-- Fecha y hora de la primera conexión del usuario
    ultimaConexion text;	-- Fecha y hora de la última logeado
    promedioConexion integer;	-- Promedio en minutos de conexión
    noIniciados integer;	-- Cantidad de programas no iniciados por el usuario
    enCurso integer;		-- Cantidad de programas en curso por el usuario
    finalizados integer;	-- Cantidad de programas aprobados por el usuario
    str text;			-- Cadena para debug
    reg  record;		-- Se almacena la cantidad de conexiones y el promedio de conexión
    usr  record;		-- Se almacena el registro de usuario
begin

    -- Usuario
    SELECT INTO usr * FROM admin_usuario u WHERE u.id = pusuario_id;
    
    -- Primera conexión
    SELECT TO_CHAR(s.fecha_ingreso, 'DD/MM/YYYY HH:MI AM') AS fecha_ingreso INTO primeraConexion FROM admin_sesion s 
    WHERE s.usuario_id = pusuario_id 
    ORDER BY s.id ASC LIMIT 1;

    -- Última conexión
    SELECT TO_CHAR(s.fecha_ingreso, 'DD/MM/YYYY HH:MI AM') AS fecha_ingreso INTO ultimaConexion FROM admin_sesion s 
    WHERE s.usuario_id = pusuario_id 
    ORDER BY s.id DESC LIMIT 1;

    -- Cantidad de conexiones y promedio de conexión
    SELECT INTO reg COUNT(s.id) AS conexiones, SUM(s.fecha_request - s.fecha_ingreso) AS total_conexion, TO_CHAR(SUM(s.fecha_request - s.fecha_ingreso), 'HH') AS horas, TO_CHAR(SUM(s.fecha_request - s.fecha_ingreso), 'MI') AS minutos 
    FROM admin_sesion s 
    WHERE s.usuario_id = pusuario_id;

    IF reg.conexiones > 0 THEN
        promedioConexion = ((reg.horas::integer*60)+reg.minutos::integer)/reg.conexiones;
        promedioConexion = ROUND(promedioConexion::numeric,0);
    ELSE 
        promedioConexion = reg.conexiones;
    END IF;

    -- No iniciados
    SELECT COUNT(pu.id) INTO noIniciados FROM ea_pagina_usuario pu 
    WHERE pu.usuario_id = pusuario_id
    AND pu.activo = true 
    AND pu.usuario_id NOT IN (
        SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = pu.pagina_id
    );

    -- En curso
    SELECT COUNT(pu.id) INTO enCurso FROM ea_pagina_usuario pu 
    WHERE pu.usuario_id = pusuario_id 
    AND pu.activo = true 
    AND pu.usuario_id IN (
        SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = pu.pagina_id AND pl.estatus_pagina_id != 3
    );

    -- Finalizados
    SELECT COUNT(pu.id) INTO finalizados FROM ea_pagina_usuario pu 
    WHERE pu.usuario_id = pusuario_id
    AND pu.activo = true 
    AND pu.usuario_id IN (
        SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = pu.pagina_id AND pl.estatus_pagina_id = 3
    );
    

     str = CASE WHEN primeraConexion IS NULL THEN 'Nunca se ha conectado' ELSE primeraConexion END || '__' || CASE WHEN ultimaConexion IS NULL THEN 'Nunca se ha conectado' ELSE ultimaConexion END || '__' || reg.conexiones || '__' || promedioConexion || ' minutos' || '__' || noIniciados || '__' || enCurso || '__' || finalizados;
    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fningresos_sistema(101);