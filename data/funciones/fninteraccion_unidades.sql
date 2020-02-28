CREATE OR REPLACE FUNCTION fninteraccion_unidades(
    resultado refcursor,
    ppagina_id integer)
  RETURNS refcursor AS
$BODY$
   
begin


    -- Para el reporte 1
    OPEN resultado FOR 
       SELECT p.id AS id, p.titulo as unidad, 
       (SELECT COUNT(pu.id) AS cursando FROM ea_pagina_usuario pu INNER JOIN admin_usuario u ON pu.usuario_id = u.id 
        WHERE pu.pagina_id = ppagina_id 
        AND pu.activo = true 
        AND u.login NOT LIKE 'temp%' 
        AND u.rol_id != 1 
        AND pu.usuario_id IN (
            SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = p.id AND pl.estatus_pagina_id != 3
        )
       ) as cursando,
       (SELECT COUNT(pu.id) AS culminado FROM ea_pagina_usuario pu INNER JOIN admin_usuario u ON pu.usuario_id = u.id 
        WHERE pu.pagina_id = ppagina_id 
        AND pu.activo = true 
        AND u.login NOT LIKE 'temp%' 
        AND u.rol_id != 1 
        AND pu.usuario_id IN (
            SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = p.id AND pl.estatus_pagina_id = 3
        )
       ) as culminado,
       (SELECT COUNT(pu.id) AS no_iniciados FROM ea_pagina_usuario pu INNER JOIN admin_usuario u ON pu.usuario_id = u.id 
        WHERE pu.pagina_id = ppagina_id 
        AND pu.activo = true 
        AND u.login NOT LIKE 'temp%' 
        AND u.rol_id != 1 
        AND pu.usuario_id NOT IN (
            SELECT pl.usuario_id FROM ea_pagina_log pl WHERE pl.pagina_id = p.id
        )
       ) as no_iniciados
       FROM ea_pagina p 
       WHERE p.pagina_id = ppagina_id 
           AND EXISTS (
            SELECT pu.pagina_id FROM ea_pagina_usuario pu 
            WHERE pu.activo = true 
            AND pu.pagina_id = p.pagina_id
           )
       ORDER BY p.orden ASC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

 --select * from fninteraccion_unidades('re', 1) as resultado; fetch all from re;