-- Function: fncerrar_libros(date)

-- DROP FUNCTION fncerrar_libros(date);

CREATE OR REPLACE FUNCTION fncerrar_libros(pfecha date)
  RETURNS integer AS
$BODY$
declare
    i INTEGER := 0;          -- Contador de arr
    rst  record;             -- Cursor para el SELECT de la página
begin

    FOR rst IN 

        SELECT * FROM ea_pagina_usuario WHERE fecha_vencimiento = pfecha AND activo = TRUE ORDER BY id ASC LOOP

        i = i+1;
  
        -- Actualizando activo en false
        UPDATE ea_pagina_usuario SET activo = FALSE WHERE id = rst.id;
         
    END LOOP;
   
    RETURN i;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fncerrar_libros('2019-06-04') as resultado;
