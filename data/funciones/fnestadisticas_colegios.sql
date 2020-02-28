CREATE OR REPLACE FUNCTION fnestadisticas_colegios(resultado refcursor, pprovincia_id integer, pciudad_id integer, pterm text)
  RETURNS refcursor AS
$BODY$
   
begin

    If pterm <> '' Then 

        OPEN resultado FOR 
            SELECT co.nombre AS colegio, co.id AS id, c.nombre AS ciudad, pr.nombre AS provincia,
            (SELECT COUNT(u.id) AS profesores FROM admin_usuario u 
             LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id 
             WHERE u.activo = true 
             AND u.rol_id = 3
             AND s.colegio_id = co.id
            ) as profesores,
            (SELECT COUNT(u.id) AS alumnos FROM admin_usuario u 
             LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id 
             WHERE u.activo = true 
             AND u.rol_id = 2
             AND s.colegio_id = co.id
            ) as alumnos,
            (SELECT COUNT(p.id) AS libros FROM ea_pagina p 
             WHERE p.pagina_id IS NULL 
             AND p.estatus_contenido_id = 1 
             AND p.id IN 
                 (SELECT pu.pagina_id FROM ea_pagina_usuario pu 
                  INNER JOIN (admin_usuario u LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id) ON pu.usuario_id = u.id 
                  WHERE pu.activo = true 
                  AND u.activo = true 
                  AND s.colegio_id = co.id
                 )
            ) as libros
            FROM admin_colegio co INNER JOIN admin_ciudad c ON co.ciudad_id = c.id INNER JOIN admin_provincia pr ON c.provincia_id = pr.id
            WHERE co.nombre ILIKE '%' || pterm || '%'
            ORDER BY co.nombre ASC;
    
    ElsIf pciudad_id = 0 Then 
    
        OPEN resultado FOR 
            SELECT co.nombre AS colegio, c.nombre AS ciudad, pr.nombre AS provincia,
            (SELECT COUNT(u.id) AS profesores FROM admin_usuario u 
             LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id 
             WHERE u.activo = true 
             AND u.rol_id = 3 
             AND s.colegio_id = co.id
            ) as profesores,
            (SELECT COUNT(u.id) AS alumnos FROM admin_usuario u 
             LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id 
             WHERE u.activo = true 
             AND u.rol_id = 2 
             AND s.colegio_id = co.id
            ) as alumnos,
            (SELECT COUNT(p.id) AS libros FROM ea_pagina p 
             WHERE p.pagina_id IS NULL 
             AND p.estatus_contenido_id = 1 
             AND p.id IN 
                 (SELECT pu.pagina_id FROM ea_pagina_usuario pu 
                  INNER JOIN (admin_usuario u LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id) ON pu.usuario_id = u.id 
                  WHERE pu.activo = true 
                  AND u.activo = true 
                  AND s.colegio_id = co.id
                 )
            ) as libros
            FROM admin_colegio co INNER JOIN admin_ciudad c ON co.ciudad_id = c.id INNER JOIN admin_provincia pr ON c.provincia_id = pr.id
            WHERE c.provincia_id = pprovincia_id 
            ORDER BY co.nombre ASC;

    Else

        OPEN resultado FOR 
            SELECT co.nombre AS colegio, co.id AS id, c.nombre AS ciudad, pr.nombre AS provincia,
            (SELECT COUNT(u.id) AS profesores FROM admin_usuario u 
             LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id 
             WHERE u.activo = true 
             AND u.rol_id = 3
             AND s.colegio_id = co.id
            ) as profesores,
            (SELECT COUNT(u.id) AS alumnos FROM admin_usuario u 
             LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id 
             WHERE u.activo = true 
             AND u.rol_id = 2
             AND s.colegio_id = co.id
            ) as alumnos,
            (SELECT COUNT(p.id) AS libros FROM ea_pagina p 
             WHERE p.pagina_id IS NULL 
             AND p.estatus_contenido_id = 1 
             AND p.id IN 
                 (SELECT pu.pagina_id FROM ea_pagina_usuario pu 
                  INNER JOIN (admin_usuario u LEFT JOIN (admin_usuario_seccion us INNER JOIN admin_seccion s ON us.seccion_id = s.id) ON us.usuario_id = u.id) ON pu.usuario_id = u.id 
                  WHERE pu.activo = true 
                  AND u.activo = true 
                  AND s.colegio_id = co.id
                 )
            ) as libros
            FROM admin_colegio co INNER JOIN admin_ciudad c ON co.ciudad_id = c.id INNER JOIN admin_provincia pr ON c.provincia_id = pr.id
            WHERE co.ciudad_id = pciudad_id 
            ORDER BY co.nombre ASC;

    End If;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

 --select * from fnestadisticas_colegios('re', 2, 2, '') as resultado; fetch all from re;
 --select * from fnestadisticas_colegios('re', 0, 0, 'SAN ANTONIO') as resultado; fetch all from re;