-- Function: fnlibros_activos(refcursor, integer, integer)

-- DROP FUNCTION fnlibros_activos(refcursor, integer, integer);

CREATE OR REPLACE FUNCTION fnlibros_activos(
    resultado refcursor,
    pgrado_id integer,
    pempresa_id integer)
  RETURNS refcursor AS
$BODY$
   
begin

    IF pgrado_id = 0 AND pempresa_id <> 0 THEN
    		
        OPEN resultado FOR 
            SELECT p.id AS id, p.grado_id AS grado_id, p.titulo AS titulo, p.tipo_pagina_id AS tipo,
            (SELECT COUNT(pu.id) AS ejemplares FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND pu.activo = true 
             AND pu.usuario_id IS NOT NULL
            ) as ejemplares,
            (SELECT COUNT(pu.id) FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND (pu.activo = false OR pu.activo IS NULL) 
             AND pu.usuario_id IS NULL
            ) AS ejemplares_sin
            FROM ea_pagina p 
            WHERE p.empresa_id = pempresa_id 
            AND p.pagina_id IS NULL 
            ORDER BY p.grado_id ASC, p.titulo ASC;

    ELSIF pgrado_id <> 0 AND pempresa_id = 0 THEN 

        OPEN resultado FOR 
            SELECT p.id AS id, p.grado_id AS grado_id, p.titulo AS titulo, p.tipo_pagina_id AS tipo,
            (SELECT COUNT(pu.id) AS ejemplares FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND pu.activo = true 
             AND pu.usuario_id IS NOT NULL
            ) as ejemplares,
            (SELECT COUNT(pu.id) FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND (pu.activo = false OR pu.activo IS NULL) 
             AND pu.usuario_id IS NULL
            ) AS ejemplares_sin
            FROM ea_pagina p 
            WHERE p.grado_id = pgrado_id
            AND p.pagina_id IS NULL 
            ORDER BY p.grado_id ASC, p.titulo ASC;

    ELSIF pgrado_id = 0 AND pempresa_id = 0 THEN 

        OPEN resultado FOR 
            SELECT p.id AS id, p.grado_id AS grado_id, p.titulo AS titulo, p.tipo_pagina_id AS tipo,
            (SELECT COUNT(pu.id) AS ejemplares FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND pu.activo = true 
             AND pu.usuario_id IS NOT NULL
            ) as ejemplares,
            (SELECT COUNT(pu.id) FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND (pu.activo = false OR pu.activo IS NULL) 
             AND pu.usuario_id IS NULL
            ) AS ejemplares_sin
            FROM ea_pagina p 
            WHERE p.pagina_id IS NULL 
            ORDER BY p.grado_id ASC, p.titulo ASC;

    ELSE 

        OPEN resultado FOR 
            SELECT p.id AS id, p.grado_id AS grado_id, p.titulo AS titulo, p.tipo_pagina_id AS tipo,
            (SELECT COUNT(pu.id) AS ejemplares FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND pu.activo = true 
             AND pu.usuario_id IS NOT NULL
            ) as ejemplares,
            (SELECT COUNT(pu.id) FROM ea_pagina_usuario pu 
             WHERE pu.pagina_id = p.id 
             AND (pu.activo = false OR pu.activo IS NULL) 
             AND pu.usuario_id IS NULL
            ) AS ejemplares_sin
            FROM ea_pagina p 
            WHERE p.empresa_id = pempresa_id 
            AND p.grado_id = pgrado_id
            AND p.pagina_id IS NULL 
            ORDER BY p.grado_id ASC, p.titulo ASC;

    END IF;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION fnlibros_activos(refcursor, integer, integer)
  OWNER TO postgres;

--select * from fnlibros_activos('re', 0, 0) as resultado; fetch all from re;
--select * from fnlibros_activos('re', 1, 0) as resultado; fetch all from re;
--select * from fnlibros_activos('re', 0, 1) as resultado; fetch all from re;
--select * from fnlibros_activos('re', 1, 1) as resultado; fetch all from re;