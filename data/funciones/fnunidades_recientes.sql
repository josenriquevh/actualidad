CREATE OR REPLACE FUNCTION fnunidades_recientes(
    resultado refcursor,
    pusuario_id integer,
    phoy date)
  RETURNS refcursor AS
$BODY$
   
begin


    OPEN resultado FOR 
       SELECT pl.id AS log_id,
              p.id as pagina_id,  
              p.orden as orden, 
              p.titulo as unidad, 
              p.pagina_id as libro_id, 
              p.grado_id as grado_id, 
              pl.porcentaje_avance as avance, 
              pl.estatus_pagina_id as status, 
              pl.fecha_interaccion as fecha_interaccion 
       FROM ea_pagina_log pl INNER JOIN ea_pagina p ON pl.pagina_id = p.id 
       WHERE pl.usuario_id = pusuario_id 
           AND p.pagina_id IS NOT NULL 
           AND p.pagina_id IN (
               SELECT l.id FROM ea_pagina l WHERE l.pagina_id IS NULL 
                   AND l.estatus_contenido_id = 1
           )
           AND EXISTS (
               SELECT pu.pagina_id FROM ea_pagina_usuario pu 
               WHERE pu.activo = true 
               AND pu.pagina_id = p.pagina_id 
               AND pu.usuario_id = pusuario_id 
               AND pu.fecha_vencimiento >= phoy
           )
       ORDER BY pl.fecha_interaccion DESC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

 --select * from fnunidades_recientes('re', 5, '2019-04-10') as resultado; fetch all from re;