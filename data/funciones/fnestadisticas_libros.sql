CREATE OR REPLACE FUNCTION fnestadisticas_libros(resultado refcursor, pgrado_id integer)
  RETURNS refcursor AS
$BODY$
   
begin
    
    OPEN resultado FOR 
        SELECT p.titulo AS nombre, tp.nombre AS tipo, p.foto AS foto, 
        (SELECT COUNT(l.id) AS cantidad FROM ea_pagina l 
         WHERE l.pagina_id IS NULL 
         AND l.estatus_contenido_id = 1 
         AND l.grado_id = pgrado_id 
         AND l.id IN 
             (SELECT pu.pagina_id FROM ea_pagina_usuario pu 
              WHERE pu.activo = true
             )
        ) as cantidad
        FROM ea_pagina p INNER JOIN ea_tipo_pagina tp ON p.tipo_pagina_id = tp.id 
        WHERE p.grado_id = pgrado_id 
        AND p.estatus_contenido_id = 1 
        AND p.pagina_id IS NULL 
        ORDER BY p.titulo ASC;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE;

 --select * from fnestadisticas_libros('re', 1) as resultado; fetch all from re;