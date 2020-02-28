---Funcion para generar los codigos aleatorios para un la cantidad de libros que se indiquen.
---Retorna una cadena con la lista de los id de los codigos que se generaron
---cantidadr: cantidad de renovaciones 
---ccodigos: la cantidad de codigos que se desean generar
---ccaracteres: la cantidad de caracteres adicionales al prefijo que tendra cada codigo generado
---cintentos: numero de veces maxima en que se intenta guardar un nuevo codigo 
---tcodigos : cantidad general de codigos que pueden generarse 
---Retorno: 
----	1) Retorna la lista de ids de los codigos creados + la cantidad de codigos creada como elemento final de la cadena retorno
--- 	2) Retorna un unico elemento en la cadena de retorno , en este caso cero (0) si ya se genero la cantidad general de codigos definida en el archivo de parametros 
--- 	3) En caso de que aun queden codigos disponibles por generar pero en la ejecucion no se realice ninguna insercion de codigos  la cadena estara compuesta por dos elementos , a la izquierda de '-' se retorna la cantidad de codigos disponibles y a la derecha (elemento final) un cero.

CREATE OR REPLACE FUNCTION fngenerar_codigos(ppagina_id integer,prenovable boolean,pcantidadr integer,pccodigos integer,pccaracteres integer,pcintentos integer,ptcodigos INTEGER,pprefijo varchar,pinicio timestamp without time zone,pvencimiento timestamp without time zone) 
  RETURNS TEXT AS $$
DECLARE
c INTEGER := 0;
disponibles INTEGER;
creados INTEGER;
cCrear INTEGER;
registrados INTEGER;
cAscii INTEGER;
cAsciiAnt INTEGER;
intentos INTEGER;
tipo INTEGER;
codigoid INTEGER;
codigon varchar(20); 
break boolean := FALSE;
registrado boolean;

retorno TEXT :='';




BEGIN
     SELECT COUNT(p.id) FROM ea_pagina_usuario as p WHERE p.codigo LIKE pprefijo||'%' INTO creados;
     disponibles :=  ptcodigos - creados; 
	 IF (disponibles<=0) THEN -- Se llego a la maxima cantidad de codigos para ese codigo
		RETURN '0';
	 ELSE
	     -- Verificamos si la cantidad de codigos requerida se puede generar , de no ser asi se generara la cantidad de codigos disponibles 
		 IF pccodigos <= disponibles THEN
			cCrear := pccodigos;
		 ELSE
			cCrear := disponibles;
		 END IF;
		 -- Ciclo principal de generacion de codigos, va de 1 a la cantidad de codigos que se puenden generar
	     WHILE c < cCrear AND NOT break LOOP
		    codigon := pprefijo||'-';
			registrado := FALSE;
			intentos := 0;
			-- Ciclo para intentar registrar un codigo 
			WHILE NOT registrado AND intentos < pcintentos LOOP
				 -- Ciclo que genera caracter por caracter el codigo
				 FOR caracter IN 1..pccaracteres LOOP
					tipo := cast(floor(random()* (2-1 + 1) + 1) AS INTEGER);--Genera un numero aleatorio entre 1 y 2 para saber si el nuevo caracter del codigo sera una letra mayuscula o un numero
					CASE tipo
							WHEN 1 THEN 
							    cAscii := cast(floor(random()* (57-49 + 1) + 49) AS INTEGER);
							    while (cAscii - cAsciiAnt = 1 ) LOOP --Revisa  si el el ascci que se genera es el siguiente al ascci anterior
							       cAscii := cast(floor(random()* (57-49 + 1) + 49) AS INTEGER);
							    END LOOP;
							    cAsciiAnt := cAscii;
								codigon := codigon||chr(cAscii); --Numeros del 1 al 9 
							WHEN 2 THEN
								cAscii := cast(floor(random()* (90-65 + 1) + 65) AS INTEGER); --Revisa  si el el ascci que se genera es el siguiente al ascci anterior
								while (cAscii - cAsciiAnt = 1 ) LOOP
							       cAscii := cast(floor(random()* (90-65 + 1) + 65) AS INTEGER);
							    END LOOP;
							    cAsciiAnt := cAscii;
								codigon := codigon||chr(cAscii); --Letras de la A a la Z
									
						END CASE;
				 END LOOP;
				 -- Fin FOR
				 IF (SELECT COUNT(p.id) FROM ea_pagina_usuario as p WHERE p.codigo=codigon)=0 THEN
							INSERT INTO ea_pagina_usuario
														 (
														  pagina_id,
														  usuario_id,
														  codigo,
														  activo,
														  fecha_activacion,
														  fecha_vencimiento,
														  renovable,
														  max_renovaciones,
														  renovaciones,
														  fecha_inicio
														 )
												   values(
														   ppagina_id,
														   NULL,
														   codigon,
														   FALSE,
														   NULL,
														   pvencimiento,
														   prenovable,
														   pcantidadr,
														   0,
														   pinicio
														 ) RETURNING id INTO codigoid ;
							
							IF LENGTH(retorno) = 0 THEN
								retorno := codigoid;
						    ELSE 
						       retorno := retorno||'-'||codigoid;
							END IF;
							registrado := TRUE;--Indica que el codigo generado se inserto en la tabla ya que no esta duplicado
							c := c+1;
						ELSE
							registrado:=FALSE;--Indica que elcodigo se encuentra en la base de datos, se procede a generar uno nuevo
							codigon := pprefijo||'-';
							intentos := intentos+1;
							IF intentos = pcintentos THEN	
								break := TRUE;
							END IF;	
						END IF;
			END LOOP;
			-- Fin ciclo para intentar registrar un codigo 
		 END LOOP;
         -- Fin ciclo principal de generacion de codigos 		 
	 END IF; 
	 IF LENGTH(retorno) = 0 THEN
	    retorno := disponibles||'-0'; -- No se ha cumplido la cantidad de codigos correspondiente pero no se han realizado inserciones 
	 ELSE
	    retorno := retorno||'-'||c; -- Retorna la lista de ids y la cantidad de registros creados 
	 END IF;
	 
	 RETURN retorno;
END 

$$ language 'plpgsql' STRICT;



---SELECT fngenerar_codigos(1,TRUE,3,50,1,20,35,'LN 1','2019-08-01 00:00:00','2020-06-01 00:00:00');