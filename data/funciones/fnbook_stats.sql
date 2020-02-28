-- Function: fnbook_stats(integer, date, date)

-- DROP FUNCTION fnbook_stats(integer, date, date);

CREATE OR REPLACE FUNCTION fnbook_stats(
    ppagina_id integer,
    pfecha_inicio date,
    pfecha_fin date)
  RETURNS text AS
$BODY$
declare
    unidades integer;		-- Cantidad de unidades del libro
    temas integer;  		-- Cantidad de temas del libro
    actividades integer;	-- Participantes registrados y que han ingresado a la plataforma
    activos integer;      	-- Cantidad de codigos activos del libro
    sin_activar integer;      	-- Cantidad de codigos sin activar del libro
    no_iniciados integer;      	-- Cantidad de codigos activos no iniciados del libro
    en_curso integer;      	-- Cantidad de codigos activos en curso del libro
    finalizados integer;      	-- Cantidad de codigos activos finalizados del libro
    str text;               	-- Cadena para debug
begin

    -- Unidades
    SELECT COUNT(p.id) INTO unidades FROM ea_pagina p 
    WHERE p.pagina_id = ppagina_id;

    -- Temas
    SELECT COUNT(p.id) INTO temas FROM ea_pagina p 
    WHERE p.pagina_id IN (
        SELECT u.id FROM ea_pagina u WHERE u.pagina_id = ppagina_id
    );

    -- Actividades
    SELECT COUNT(p.id) INTO actividades FROM ea_pagina p 
    WHERE p.pagina_id IN (
        SELECT t.id FROM ea_pagina t 
        WHERE t.pagina_id IN (
            SELECT u.id FROM ea_pagina u WHERE u.pagina_id = ppagina_id
        )
    );

    -- Activos
    SELECT COUNT(pu.id) INTO activos FROM ea_pagina_usuario pu 
    WHERE pu.pagina_id = ppagina_id 
    AND pu.activo = true 
    AND pu.fecha_inicio BETWEEN pfecha_inicio AND pfecha_fin 
    AND pu.usuario_id IS NOT NULL;

    -- Sin activar
    SELECT COUNT(pu.id) INTO sin_activar FROM ea_pagina_usuario pu 
    WHERE pu.pagina_id = ppagina_id 
    AND (pu.activo = false OR pu.activo IS NULL) 
    AND pu.fecha_inicio BETWEEN pfecha_inicio AND pfecha_fin 
    AND pu.usuario_id IS NULL;

    -- No iniciados
    SELECT COUNT(pu.id) INTO no_iniciados FROM ea_pagina_usuario pu 
    WHERE pu.pagina_id = ppagina_id 
    AND pu.activo = true 
    AND pu.fecha_inicio BETWEEN pfecha_inicio AND pfecha_fin 
    AND pu.usuario_id NOT IN (
        SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = ppagina_id
    );

    -- En curso
    SELECT COUNT(pu.id) INTO en_curso FROM ea_pagina_usuario pu 
    WHERE pu.pagina_id = ppagina_id 
    AND pu.activo = true 
    AND pu.fecha_inicio BETWEEN pfecha_inicio AND pfecha_fin 
    AND pu.usuario_id IN (
        SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id != 3
    );

    -- Finalizados
    SELECT COUNT(pu.id) INTO finalizados FROM ea_pagina_usuario pu 
    WHERE pu.pagina_id = ppagina_id 
    AND pu.activo = true 
    AND pu.fecha_inicio BETWEEN pfecha_inicio AND pfecha_fin 
    AND pu.usuario_id IN (
        SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = ppagina_id AND pl.estatus_pagina_id = 3
    );

    str = unidades || '__' || temas  || '__' || actividades || '__' || activos || '__' || sin_activar || '__' || no_iniciados || '__' || en_curso || '__' || finalizados;

    return str;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fnbook_stats(1, '2019-07-30', '2025-08-30');