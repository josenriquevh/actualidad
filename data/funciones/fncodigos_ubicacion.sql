CREATE OR REPLACE FUNCTION fncodigos_ubicacion(
    resultado refcursor,
    ppagina_id integer,
    pprovincia_id integer)
  RETURNS refcursor AS
$BODY$
   
begin


    IF pprovincia_id = 0 THEN 

        -- Listado de las provincias con más códigos activados
        OPEN resultado FOR 
            SELECT p.id as id, p.nombre as nombre, count(pu.id) as cantidad 
            FROM ea_pagina_usuario pu INNER JOIN (admin_usuario u INNER JOIN admin_provincia p ON u.provincia_id = p.id) ON pu.usuario_id = u.id 
            WHERE pu.pagina_id = ppagina_id 
            AND pu.activo = true 
            GROUP BY p.id 
            ORDER BY cantidad DESC;

    ELSE

        -- Listado de las ciudades con más códigos activados
        OPEN resultado FOR 
            SELECT c.id as id, c.nombre as nombre, count(pu.id) as cantidad 
            FROM ea_pagina_usuario pu INNER JOIN (admin_usuario u INNER JOIN admin_ciudad c ON u.ciudad_id = c.id) ON pu.usuario_id = u.id 
            WHERE pu.pagina_id = ppagina_id 
            AND pu.activo = true 
            AND c.provincia_id = pprovincia_id
            GROUP BY c.id 
            ORDER BY cantidad DESC;

    END IF;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE

 --select * from fncodigos_ubicacion('re', 2, 0) as resultado; fetch all from re;
 --select * from fncodigos_ubicacion('re', 2, 12) as resultado; fetch all from re;