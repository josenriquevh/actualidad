-- Function: fnaviso_vencimiento(refcursor, date)

-- DROP FUNCTION fnaviso_vencimiento(refcursor, date);

CREATE OR REPLACE FUNCTION fnaviso_vencimiento(resultado refcursor, pfecha date)
  RETURNS refcursor AS
$BODY$

begin

    

    OPEN resultado FOR
        SELECT * FROM ea_pagina_usuario 
        WHERE activo = TRUE 
        AND fecha_vencimiento = pfecha
        ORDER BY id ASC;
   
    RETURN resultado;

end;
$BODY$
  LANGUAGE 'plpgsql' VOLATILE;

--select * from fnaviso_vencimiento('re','2019-06-04', 1) as resultado; fetch all from re;
