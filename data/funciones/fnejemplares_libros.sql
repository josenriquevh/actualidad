-- Function: fnejemplares_libros(refcursor, integer)

-- DROP FUNCTION fnejemplares_libros(refcursor, integer);

CREATE OR REPLACE FUNCTION fnejemplares_libros(
    resultado refcursor,
    plibro_id integer)
  RETURNS refcursor AS
$BODY$
   
begin
    
    OPEN resultado FOR 
        SELECT COUNT (pu.id) as ejemplares,
        ( SELECT COUNT (pu.id)
	  FROM ea_pagina_usuario pu
	  WHERE pu.pagina_id = plibro_id
	  AND pu.activo = false) as ejemplares_sin
	FROM ea_pagina_usuario pu
	WHERE pu.pagina_id = plibro_id
	AND pu.activo = true;
    
    RETURN resultado;

end;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION fnejemplares_libros(refcursor, integer)
  OWNER TO postgres;