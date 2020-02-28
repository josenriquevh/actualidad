<?php

namespace Actualidad\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Actualidad\ComunBundle\Entity\AdminSesion;
use Actualidad\ComunBundle\Entity\EaPagina;
use Actualidad\ComunBundle\Entity\EaPrueba;
use Actualidad\ComunBundle\Entity\EaPregunta;
use Actualidad\ComunBundle\Entity\EaOpcion;
use Actualidad\ComunBundle\Entity\EaPreguntaOpcion;
use Actualidad\ComunBundle\Entity\EaPreguntaAsociacion;
use Actualidad\ComunBundle\Entity\EaPaginaLog;
use Actualidad\ComunBundle\Entity\AdminAlarma;


class Functions
{	
	
	protected $em;
	public $container;
	protected $mailer;
	private $templating;
	private $translator;

	var $meses=array("1"=>"Enero","2"=>"Febrero","3"=>"Marzo","4"=>"Abril","5"=>"Mayo","6"=>"Junio","7"=>"Julio","8"=>"Agosto","9"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre");

	public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container)
	{

		$this->em = $em;
		$this->container = $container;
		$this->mailer = $container->get('mailer');
        $this->templating = $container->get('templating');
        $this->translator = $container->get('translator');
	}

	// Función que valida si un registro de una tabla puede ser eliminado dependiendo de su relación con otras tablas
	// Parámteros: $id = Valor del id del registro a comparar
	//			   $entidad = Nombre de las tabla a comparar (formato Entity)
	public function linkEliminar($id, $entidad)
	{

		$em = $this->em;
		$html = '';

		// $entidades array('entidad destino' => 'atributo destino')
    	switch ($entidad)
    	{
    		case 'AdminUsuario':
    			$entidades = array('AdminSesion' => 'usuario',
    							   'AdminUsuarioSeccion' => 'usuario',
    							   'EaPaginaUsuario' => 'usuario',
    							   'EaPagina' => 'usuario',
    							   'EaPaginaLog' => 'usuario',
    							   'EaPrueba' => 'usuario',
    							   'EaPruebaLog' => 'usuario',
    							   'EaPregunta' => 'usuario',
    							   'EaOpcion' => 'usuario',
    							   'AdminNoticia' => 'usuario',
    							   'EaForo' => 'usuario',
    							   'EaForoArchivo' => 'usuario',
    							   'AdminNotificacion' => 'usuario',
    							   'AdminNotificacionProgramada' => 'usuario',
    							   'AdminCorreo' => 'usuario',
    							   'AdminAlarma' => 'usuario',
    							   'EaProfesorAlumno' => 'profesor',
    							   'EaProfesorAlumno' => 'alumno',
    							   'EaPaginaLiberada' => 'usuario');
    			break;
    		case 'AdminEmpresa':
    		     $entidades = array('EaPagina' => 'pagina');
    		    break;
            case 'EaPagina':
                $entidades = array('EaPaginaUsuario' => 'pagina',
                				   'EaPaginaLog' => 'pagina',
                                   'EaPrueba' => 'pagina',
                                   'EaForo' => 'pagina',
                                   'EaPaginaLiberada' => 'pagina');
                break;
            case 'EaPrueba':
                $entidades = array('EaPregunta' => 'prueba',
                                   'EaOpcion' => 'prueba',
                                   'EaPruebaLog' => 'prueba');
                break;
            case 'EaPregunta':
                $entidades = array('EaPregunta' => 'pregunta',
                                   'EaPreguntaOpcion' => 'pregunta',
                                   'EaPreguntaAsociacion' => 'pregunta',
                               	   'EaRespuesta' => 'pregunta');
                break;
            case 'AdminNotificacion':
                $entidades = array('AdminNotificacionProgramada' => 'notificacion');
                break;
            case 'AdminNotificacionProgramada':
                $entidades = array('AdminNotificacionProgramada' => 'grupo');
                break;
            case 'EaForo':
                $entidades = array('EaForo' => 'foro',
                				   'EaForoArchivo' => 'foro');
                break;
            case 'EaTipoPagina':
                $entidades = array('EaPagina' => 'tipoPagina');
				break;
			case 'AdminColegio':
                $entidades = array('AdminSeccion' => 'colegio');
				break;
			case 'AdminAyudaInteracticion':
                $entidades = array('EaPagina' => 'ayudaInteractivo');
                break;
            default:
    			$entidades = array();
    			break;
    	}

    	foreach ($entidades as $entity => $attr)
        {
        	$qb = $em->createQueryBuilder();
			$qb->select('COUNT(tr.id)')
	   		   ->from('ActualidadComunBundle:'.$entity, 'tr')
	   		   ->where('tr.'.$attr.' = :id')
	   		   ->setParameter('id',$id);
	   		$query = $qb->getQuery();
	   		$cuenta = $query->getSingleScalarResult();
			if ($cuenta)
			{
				$html = 'disabled';
				break;
			}
        }
        
        return $html;

	}

	public function getWebDirectory()
	{
		$request = Request::createFromGlobals();
		$url = $request->getBasePath();
		return $url;
	}

	function mb_wordwrap($str, $len = 75, $break = " ", $cut = true) 
	{
		$len = (int) $len;

		if (empty($str))
			return ""; 

		$pattern = "";

		if ($cut)
			$pattern = '/([^'.preg_quote($break).']{'.$len.'})/u'; 
		else
			return wordwrap($str, $len, $break);

		return preg_replace($pattern, "\${1}".$break, $str);
	}
	
	public function sendEmail($parametros)
	{

		$ok = 0;

		if ($this->container->getParameter('sendMail'))
		{
			
			$mailer = $this->container->get('swiftmailer.mailer.'.$parametros["mailer"]);
			// ->setBody($this->render($parametros['twig'], $parametros['datos']), 'text/html');
			$body = $this->templating->render($parametros['twig'],$parametros['datos']);
			$message = \Swift_Message::newInstance()
	            ->setSubject($parametros['asunto'])
	            ->setFrom([$parametros['remitente'] => $parametros['remitente_name']])
	            ->setTo($parametros['destinatario'])
	            ->setBody($body, 'text/html');
	        $ok = $mailer->send($message);
		}
		
        return $ok;

	}

	public function obtenerIcono($extension)
	{

        if(($extension == 'doc')||($extension == 'docx')){
        	$icono = 'fa-file-word-o';
        }
        if(($extension == 'png')||($extension == 'jpg')){
        	$icono = 'fa-file-image-o';
        }
        if(($extension == 'xls')||($extension == 'xlsx')){
        	$icono = 'fa-file-excel-o';
        }
        if($extension == 'pdf'){
        	$icono = 'fa-file-pdf-o';
        }
        if($extension == 'txt'){
        	$icono = 'fa-file-archive-o';
        }
        return $icono;
	}

	public function generarClave()
	{
        $caracteres = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789";
        $numerodeletras=8;
        $contrasena = "";
        for($i=0;$i<$numerodeletras;$i++){
            $contrasena .= substr($caracteres,rand(0,strlen($caracteres)),1);
        }
        return $contrasena;
	}

	function sanear_string($string)
	{
	 
	    $string = trim($string);
	 
	    $string = str_replace(
	        array('Á', 'À', 'Â', 'Ä'),
	        array('á', 'á', 'á', 'á'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('É', 'È', 'Ê', 'Ë'),
	        array('é', 'é', 'é', 'é'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('Í', 'Ì', 'Ï', 'Î'),
	        array('í', 'í', 'í', 'í'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('Ó', 'Ò', 'Ö', 'Ô'),
	        array('ó', 'ó', 'ó', 'ó'),
	        $string
	    );
	 
	    $string = str_replace(
	        array('Ú', 'Ù', 'Û', 'Ü'),
	        array('ú', 'ú', 'ú', 'ü'),
	        $string
	    );

	    $string = str_replace(
	        array('Ñ'),
	        array('ñ'),
	        $string
	    );
	     
	    return $string;
	}

	// Recibe la fecha de nacimiento en formato AAAA-MM-DD. Retorna la edad.
	public function calcularEdad($fecha)
	{
		
		if (!$fecha)
		{
			$edad = 'Fecha de nacimiento no especificada';
		}
		else {
			$datetime1 = new \DateTime($fecha);
			$datetime2 = new \DateTime("now");
			$interval = $datetime1->diff($datetime2);
			
			if ($interval->format('%y') < 1){
				// Si es menos que un año, se contabiliza los meses
				if ($interval->format('%m') < 1)
				{
					// Si es menos que un mes, se contabilizan los días
					$edad = $interval->format('%d').' días';
				}
				else {
					$edad = $interval->format('%m').' meses';
				}
			}
			else {
				$year = $interval->format('%y')==1 ? 'año' : 'años';
				if ($interval->format('%m') == 0)
				{
					$edad = $interval->format('%y').' '.$year;
				}
				else {
					$edad = $interval->format('%y').' '.$year.' y ';
					if ($interval->format('%m') < 2){
						$edad .= $interval->format('%m').' mes';
					}
					else {
						if ($interval->format('%m') == 0)
							$edad = $interval->format('%m') ;
						else
							$edad .= $interval->format('%m').' meses';
					}
				}
			}
		}

        return $edad;

	}

	// Recibe la cantidad de días, meses o años de duración y el formato.
	// Retorna la fecha de vencimiento a partir de hoy
	public function vencimiento($cantidad, $tipo, $formato)
	{
		
		switch ($tipo)
		{
			case 'Días':
				$vencimiento = date($formato,mktime(0,0,0,date('m'),date('d')+$cantidad,date('Y')));
				break;
			case 'Meses':
				$vencimiento = date($formato,mktime(0,0,0,date('m')+$cantidad,date('d'),date('Y')));
				break;
			case 'Años':
				$vencimiento = date($formato,mktime(0,0,0,date('m'),date('d'),date('Y')+$cantidad));
				break;
			default:
				$vencimiento = date($formato);
		}
		
		return $vencimiento;

	}

	// Calcula la diferencia de tiempo entre fecha y hoy
	// Retorna la cantidad de días
	public function timeAgo($fecha)
	{

		$days_ago = 0;
		
		if ($fecha)
		{
			$datetime1 = new \DateTime($fecha);
			$datetime2 = new \DateTime("now");
			$interval = $datetime1->diff($datetime2);
			$days_ago = $interval->format('%a');
		}

        return $days_ago;

	}

	// Calcula la diferencia de tiempo entre fecha y hoy
	// Si es menor de una hora retorna la cantidad de minutos
	// Si es más de una hora y fecha es hoy retorna la hora de fecha
	// Si fecha es ayer retorna "Ayer Hora"
	// Si fecha es menor que ayer se muestra fecha formateado con la hora
	public function sinceTime($fecha)
	{

		$hoy = date('Y-m-d');
		$ayer = date('Y-m-d', strtotime('yesterday'));
		$time_ago = '';
		
		if ($fecha)
		{
			
			$datetime1 = new \DateTime($fecha);
			$datetime2 = new \DateTime(date('Y-m-d H:i:s'));
			$interval = $datetime1->diff($datetime2);

			if ($fecha < $ayer)
			{
				$time_ago = $datetime1->format('d/m/Y H:i');
			}
			elseif ($fecha >= $ayer.' 00:00:00' && $fecha < $ayer.' 23:59:59') 
			{
				$time_ago = $this->translator->trans('Ayer').' '.$datetime1->format('H:i');
			}
			elseif ($datetime1->format('Y-m-d') == $hoy) {
				if ($interval->format('%h') > 1)
				{
					$time_ago = $this->translator->trans('Hoy a las').' '.$datetime1->format('H:i');
				}
				else {
					$time_ago = 'Hace '.$interval->format('%i').' '.$this->translator->trans('minutos');
				}
			}
			
		}

        return $time_ago;

	}

	// Retorna el código de dos caracteres del país (Ej. VE)
	public function getLocaleCode()
	{

		$code = 'VE';

		if ($this->is_connected('www.geoplugin.net'))
		{

			$ip = $this->get_client_ip();
			//$ip = '186.7.104.142';
			$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
			//get ISO2 country code
			if(property_exists($ipdat, 'geoplugin_countryCode')) {
				if (trim($ipdat->geoplugin_countryCode))
				{
					$code = trim($ipdat->geoplugin_countryCode);
				}
			}

		}

        return $code;

	}

	// Optiene la IP del cliente
	public function get_client_ip() {
	    
	    $ipaddress = '';
	    if (getenv('HTTP_CLIENT_IP'))
	        $ipaddress = getenv('HTTP_CLIENT_IP');
	    else if(getenv('HTTP_X_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	    else if(getenv('HTTP_X_FORWARDED'))
	        $ipaddress = getenv('HTTP_X_FORWARDED');
	    else if(getenv('HTTP_FORWARDED_FOR'))
	        $ipaddress = getenv('HTTP_FORWARDED_FOR');
	    else if(getenv('HTTP_FORWARDED'))
	       $ipaddress = getenv('HTTP_FORWARDED');
	    else if(getenv('REMOTE_ADDR'))
	        $ipaddress = getenv('REMOTE_ADDR');
	    else
	        $ipaddress = 'UNKNOWN';
	    return $ipaddress;

	}

	// Indica si es alcanzable una web
	public function is_connected($web)
	{
	    $connected = @fsockopen($web, 80); 
	                                        //website, port  (try 80 or 443)
	    if ($connected){
	        $is_conn = true; //action when connected
	        fclose($connected);
	    }else{
	        $is_conn = false; //action in connection failure
	    }
	    return $is_conn;

	}

	// Actualiza la fecha y hora del request de la sesión actual
	public function setRequest($sesion_id)
	{

		$em = $this->em;
		$session = new Session();
		
		$admin_sesion = $em->getRepository('ActualidadComunBundle:AdminSesion')->find($sesion_id);
		if($admin_sesion){
			$admin_sesion->setFechaRequest(new \DateTime('now'));
        	$em->persist($admin_sesion);
        	$em->flush();
		}
		else{
			$session->invalidate();
        	$session->clear();
		}
		

	}

	// Retorna 0 si la fecha dada está en formato DD/MM/YYYY y es correcta
    function checkFecha($fecha){

        $ok = 1;

        $fecha_arr = explode("/", $fecha);

        if (count($fecha_arr) != 3){
            $ok = 0;
        }
        else {
            if (!checkdate($fecha_arr[1], $fecha_arr[0], $fecha_arr[2])){
                $ok = 0;
            }
        }

        return $ok;

    }

    // Formatea la fecha dada en formato DD/MM/YYYY a YYYY-MM-DD
    function formatDate($fecha){

        $fecha_arr = explode("/", $fecha);
        $new_fecha = $fecha_arr[2].'-'.$fecha_arr[1].'-'.$fecha_arr[0];

        return $new_fecha;

    }

    // Formatea la fecha dada en formato YYYY-MM-DD a formato español DD jul YYYYY
    function formatDateEs($fecha){

        $fecha_arr = explode("-", $fecha);

        switch ($fecha_arr[1]) {
        	case '01':
        		$fecha_es = $fecha_arr[2].' ene '.$fecha_arr[0];
        		break;
        	case '02':
        		$fecha_es = $fecha_arr[2].' feb '.$fecha_arr[0];
        		break;
        	case '03':
        		$fecha_es = $fecha_arr[2].' mar '.$fecha_arr[0];
        		break;
        	case '04':
        		$fecha_es = $fecha_arr[2].' abr '.$fecha_arr[0];
        		break;
        	case '05':
        		$fecha_es = $fecha_arr[2].' may '.$fecha_arr[0];
        		break;
        	case '06':
        		$fecha_es = $fecha_arr[2].' jun '.$fecha_arr[0];
        		break;
        	case '07':
        		$fecha_es = $fecha_arr[2].' jul '.$fecha_arr[0];
        		break;
        	case '08':
        		$fecha_es = $fecha_arr[2].' ago '.$fecha_arr[0];
        		break;
        	case '09':
        		$fecha_es = $fecha_arr[2].' sep '.$fecha_arr[0];
        		break;
        	case '10':
        		$fecha_es = $fecha_arr[2].' oct '.$fecha_arr[0];
        		break;
        	case '11':
        		$fecha_es = $fecha_arr[2].' nov '.$fecha_arr[0];
        		break;
        	case '12':
        		$fecha_es = $fecha_arr[2].' dic '.$fecha_arr[0];
        		break;
        }
        
        return $fecha_es;

    }

 	public function newAlarm($tipo_alarma_id, $descripcion, $usuario, $entidad_id, $fecha = 0)
	{

		$em = $this->em;

		$fecha = !$fecha ? new \DateTime('now') : $fecha;
		$tipo_alarma = $em->getRepository('ActualidadComunBundle:AdminTipoAlarma')->find($tipo_alarma_id);

		$alarma = new AdminAlarma();
		$alarma->setTipoAlarma($tipo_alarma);
		$alarma->setDescripcion($descripcion);
		$alarma->setUsuario($usuario);
		$alarma->setEntidadId($entidad_id);
		$alarma->setLeido(false);
		$alarma->setFechaCreacion($fecha);
		$em->persist($alarma);
        $em->flush();

	}

	public function iniciarSesion($datos)
    {

        $exito = false;
        $error = '';
        $usuario = 0;

		$em = $this->em;

		$query = $em->createQuery('SELECT u FROM ActualidadComunBundle:AdminUsuario u 
									WHERE LOWER(u.login) = :login 
									AND u.clave = :clave 
									AND u.rol = :rol_id')
                    ->setParameters(array('login' => strtolower($datos['login']),
                    					  'clave' => $datos['clave'],
                    					  'rol_id' => $datos['rol_id']));
        $usuarios = $query->getResult();

        if ($usuarios)
        {
        	$usuario = $usuarios[0];
        }

		if (!$usuario)
        {
        	$error = $this->translator->trans('Usuario o contraseña incorrecta').'.';
        }
        else {

        	if (!$usuario->getActivo())
        	{
        		$error = $this->translator->trans('Usuario inactivo. Contacte al administrador del sistema.');
        	}
        	else {

        		// Si tiene sessiones activas por más de 5 min, se cierra automáticamente.
	        	$sesiones_activas = $em->getRepository('ActualidadComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
	                                                                       								  'disponible' => true));
	        	$is_active = false;
	        	foreach ($sesiones_activas as $sesion_activa)
	        	{
	        		$timeFirst  = strtotime($sesion_activa->getFechaRequest()->format('Y-m-d H:i:s'));
					$timeSecond = strtotime(date('Y-m-d H:i:s'));
					$differenceInSeconds = $timeSecond - $timeFirst;
					$differenceInMinutes = number_format($differenceInSeconds/60, 0);
					if ($differenceInMinutes < 5)
					{
						$is_active = true;
					}
					else {
						$sesion_activa->setDisponible(false);
						$em->persist($sesion_activa);
	                    $em->flush();
					}
	        	}

	            if ($is_active)
	            {
	            	$error = $this->translator->trans('Tienes una sesión activa. Espera 5 minutos e intenta ingresar de nuevo.');
	            }
	            else {

	            	// Se setea la sesion
	            	$this->setSesionFront($usuario, $datos['yml']);
	                
	                if ($datos['recordar_datos'] == 1)
	                {
	                    
	                    //alimentamos el generador de aleatorios
	                    mt_srand (time());
	                    
	                    //generamos un número aleatorio para la cookie
	                    $numero_aleatorio = mt_rand(1000000,999999999);
	                    
	                    //se guarda la cookie en la tabla admin_usuario
	                    $usuario->setCookies($numero_aleatorio);
	                    $em->persist($usuario);
	                    $em->flush();
	                    
	                    //se creo la variable de las cookie con el id del usuario de manera que cuando destruya la cookie sea la del usuario activo
	                    setcookie("id_usuario_front", $usuario->getId(), time()+(60*60*24*365),'/');
	                    setcookie("cookie_front", $numero_aleatorio, time()+(60*60*24*365),'/');
	                    
	                }

					$exito = true;

	            }

        	}
            
        }

       	return array("error" => $error,
       				 "exito" => $exito);

    }

	public function iniciarSesionAdmin($datos)
    {

        $exito = false;
        $error = '';
        $usuario = 0;

		$em = $this->em;

		$query = $em->createQuery('SELECT u FROM ActualidadComunBundle:AdminUsuario u 
									WHERE LOWER(u.login) = :login AND u.clave = :clave')
                    ->setParameters(array('login' => strtolower($datos['login']),
                    					  'clave' => $datos['clave']));
        $usuarios = $query->getResult();

        if ($usuarios)
        {
        	$usuario = $usuarios[0];
        }

		if (!$usuario)
        {
        	$error = $this->translator->trans('Usuario o clave incorrecta').'.';
        }
        else {

        	// Si tiene sessiones activas por más de 5 min, se cierra automáticamente.
        	$sesiones_activas = $em->getRepository('ActualidadComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                       								  'disponible' => true));
        	$is_active = false;
        	foreach ($sesiones_activas as $sesion_activa)
        	{
        		$timeFirst  = strtotime($sesion_activa->getFechaRequest()->format('Y-m-d H:i:s'));
				$timeSecond = strtotime(date('Y-m-d H:i:s'));
				$differenceInSeconds = $timeSecond - $timeFirst;
				$differenceInMinutes = number_format($differenceInSeconds/60, 0);
				if ($differenceInMinutes < 5)
				{
					$is_active = true;
				}
				else {
					$sesion_activa->setDisponible(false);
					$em->persist($sesion_activa);
                    $em->flush();
				}
        	}

            if (!$usuario->getActivo())
            {
            	$error = $this->translator->trans('Usuario inactivo. Contacte al administrador del sistema.');
            }
            else if ($is_active)
            {
            	$error = $this->translator->trans('Tienes una sesión activa. Espera 5 minutos e intenta ingresar de nuevo.');
            }
            else {

            	if ($usuario->getRol()->getId() != $datos['yml']['rol']['administrador'])
            	{
            		$error = $this->translator->trans('El rol que tienes no es permitido para ingresar a la aplicación').'.';
            	}
            	else {

            		// Se setea la sesion
                    $datosUsuario = array('id' => $usuario->getId(),
                                          'nombre' => $usuario->getNombre(),
                                          'apellido' => $usuario->getApellido(),
                                          'correo' => $usuario->getCorreo(),
                                          'foto' => $usuario->getFoto());

                    $sesiones = $em->getRepository('ActualidadComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                                	  'disponible' => true));
                    foreach ($sesiones as $s)
                    {
                        $s->setDisponible(false);
                        $em->persist($s);
                    	$em->flush();
                    }

                    // Se crea la sesión en BD
                    $admin_sesion = new AdminSesion();
                    $admin_sesion->setFechaIngreso(new \DateTime('now'));
                    $admin_sesion->setFechaRequest(new \DateTime('now'));
                    $admin_sesion->setUsuario($usuario);
                    $admin_sesion->setDisponible(true);
                    $em->persist($admin_sesion);
                    $em->flush();

					$session = new Session();
                    $session->set('ini', true);
                    $session->set('sesion_id', $admin_sesion->getId());
                    $session->set('usuario', $datosUsuario);

                    if ($datos['recordar_datos'] == 1)
                    {
                        //alimentamos el generador de aleatorios
                        mt_srand (time());
                        //generamos un número aleatorio para la cookie
                        $numero_aleatorio = mt_rand(1000000,999999999);
                        //se guarda la cookie en la tabla admin_usuario
                        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
                        //hay que validar si el usuario hace la marca de recordar
                        $usuario->setCookies($numero_aleatorio);
                        $em->persist($usuario);
                        $em->flush();
                        //se creo la variable de las cookie con el id del usuario de manera que cuando destruya la cookie sea la del usuario activo
                        setcookie("id_usuario", $usuario->getId(), time()+(60*60*24*365),'/');
                        setcookie("marca_aleatoria_usuario", $numero_aleatorio, time()+(60*60*24*365),'/');
                    }

					$exito = true;

            	}

            }
            
        }

       	return array("error" => $error,
       				 "exito" => $exito);

    }

    // Calcula la diferencia de tiempo entre fecha_ini y fecha_venc
	// Retorna la cantidad de días
	public function timeAgoPorcentaje($fecha_ini, $fecha_venc)
	{

		$fin = new \DateTime($fecha_venc);
		$inicio = new \DateTime($fecha_ini);
		$hoy = new \DateTime("now");
		$interval_complete = $fin->diff($inicio);
		$interval_available = $fin->diff($hoy);
		$complete_days = $interval_complete->format('%a');
		$available_days = $interval_available->format('%a');

		$complete_days = (int) $complete_days;
		$available_days = (int) $available_days;

		$porcentaje = ($available_days * 100) / $complete_days;

		return $porcentaje;

	}

	// Arreglo de comentarios en el espacio colaborativo y sus respuestas
	public function forosHijos($foro_id, $offset, $limit, $usuario, $social_colaborativo)
	{

		$em = $this->em;
		$qb = $em->createQueryBuilder();
        $qb->select('f')
           ->from('LinkComunBundle:CertiForo', 'f')
           ->andWhere('f.foro = :foro_id')
           ->orderBy('f.fechaRegistro', 'ASC')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->setParameter('foro_id', $foro_id);
        $query = $qb->getQuery();
        $foros = $query->getResult();

        $foros_hijos = array();

        foreach ($foros as $foro_hijo)
        {

            $foros_nietos = array();
            $foros_nietos_bd = $em->getRepository('LinkComunBundle:CertiForo')->findBy(array('foro' => $foro_hijo->getId()),
                                                                                       array('fechaRegistro' => 'ASC'));
            foreach ($foros_nietos_bd as $foro_nieto)
            {
                $autor_nieto = $foro_nieto->getUsuario()->getId() == $usuario['id'] ? $this->translator->trans('Yo') : $foro_nieto->getUsuario()->getNombre().' '.$foro_nieto->getUsuario()->getApellido();
                $delete_link = $foro_nieto->getUsuario()->getId() != $usuario['id'] ? $usuario['tutor'] ? 1 : 0 : 1;
                $foros_nietos[] = array('id' => $foro_nieto->getId(),
                                        'usuario' => $autor_nieto,
                                        'foto' => $foro_nieto->getUsuario()->getFoto(),
                                        'timeAgo' => $this->sinceTime($foro_nieto->getFechaRegistro()->format('Y-m-d H:i:s')),
                                        'mensaje' => $foro_nieto->getMensaje(),
                                        'likes' => $this->likes($social_colaborativo, $foro_nieto->getId(), $usuario['id']),
                                        'delete_link' => $delete_link);
            }
            $autor = $foro_hijo->getUsuario()->getId() == $usuario['id'] ? $this->translator->trans('Yo') : $foro_hijo->getUsuario()->getNombre().' '.$foro_hijo->getUsuario()->getApellido();
            $delete_link = $foro_hijo->getUsuario()->getId() != $usuario['id'] ? $usuario['tutor'] ? 1 : 0 : 1;
            $foros_hijos[] = array('id' => $foro_hijo->getId(),
                                   'usuario' => $autor,
                                   'foto' => $foro_hijo->getUsuario()->getFoto(),
                                   'timeAgo' => $this->sinceTime($foro_hijo->getFechaRegistro()->format('Y-m-d H:i:s')),
                                   'mensaje' => $foro_hijo->getMensaje(),
                                   'likes' => $this->likes($social_colaborativo, $foro_hijo->getId(), $usuario['id']),
                                   'delete_link' => $delete_link,
                                   'respuestas' => $foros_nietos);
            
        }

        return $foros_hijos;

	}

	// Arreglo del archivo en el espacio colaborativo
	public function archivoForo($archivo, $usuario_id)
	{

		$extension = strtolower(substr($archivo->getArchivo(), strrpos($archivo->getArchivo(), ".")+1));

		$doc_extensions = array('doc', 'docx');
		$img_extensions = array('png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'svg');
		$excel_extensions = array('xls', 'xlsx');

		if (in_array($extension, $doc_extensions))
		{
			$img = $this->getWebDirectory().'/front/assets/img/doc.svg';
		}
		elseif (in_array($extension, $img_extensions))
		{
			$img = $this->getWebDirectory().'/front/assets/img/jpg.svg';
		}
		elseif (in_array($extension, $excel_extensions))
		{
			$img = $this->getWebDirectory().'/front/assets/img/xls.svg';
		}
		elseif ($extension == 'pdf')
		{
			$img = $this->getWebDirectory().'/front/assets/img/pdf.svg';
		}
		else {
			$img = $this->getWebDirectory().'/front/assets/img/jpg.svg';
		}

		$archivo_arr = array('id' => $archivo->getId(),
							 'descripcion' => $archivo->getDescripcion(),
							 'usuario' => $archivo->getUsuario()->getId() == $usuario_id ? $this->translator->trans('Yo') : $archivo->getUsuario()->getNombre().' '.$archivo->getUsuario()->getApellido(),
							 'archivo' => $archivo->getArchivo(),
							 'img' => $img);

        return $archivo_arr;

	}

	function delete_folder($directory, $delete_parent = null)
  	{
    	$files = glob($directory . '/{,.}[!.,!..]*',GLOB_MARK|GLOB_BRACE);
    	foreach ($files as $file) 
    	{
      		if (is_dir($file)) 
      		{
        		$this->delete_folder($file, 1);
      		} 
      		else {
        		unlink($file);
      		}
    	}
    	if ($delete_parent) 
    	{
      		rmdir($directory);
    	}
  	}

	public function sesionBloqueda($sesion_id)
	{

		$em = $this->em;
		$sesion = $em->getRepository('ActualidadComunBundle:AdminSesion')->find($sesion_id);

		return !$sesion->getDisponible();

	}

	// Retorna el tamaño de un archivo entendible
	function fileSizeConvert($bytes)
	{

	    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            )
        );

	    foreach($arBytes as $arItem)
	    {
	        if($bytes >= $arItem["VALUE"])
	        {
	            $result = $bytes / $arItem["VALUE"];
	            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
	            break;
	        }
	    }

	    return $result;

	}

	// Retorna la duración en minutos de una prueba
	public function duracionPrueba($pagina_id)
	{

		$em = $this->em;
		$duracion = 0;

		$prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina_id);

		if ($prueba)
		{
			// Duración en minutos
		    $duracion = intval($prueba->getDuracion()->format('G'))*60;
		    $duracion += intval($prueba->getDuracion()->format('i'));
		}

	    return $duracion;

	}

	// Retorna 0 si la pagina_id no tiene registro de pagina_log y se puede mover
	public function paginaMovible($pagina_id)
	{

		$em = $this->em;

		$query = $em->createQuery('SELECT COUNT(pl.id) FROM ActualidadComunBundle:EaPaginaLog pl 
		                            WHERE pl.pagina = :pagina_id')
		            ->setParameter('pagina_id', $pagina_id);
		
		return $query->getSingleScalarResult();

	}

	// Retorna un arreglo con la estructura completa de las páginas con sus sub-páginas
	public function paginas($pages)
	{

		$paginas = array();
        
        foreach ($pages as $page)
        {

        	$subpaginas = $this->subPaginas($page->getId());

            $paginas[] = array('id' => $page->getId(),
            				   'orden' => $page->getOrden(),
            				   'empresa' => $page->getEmpresa()->getNombre(),
            				   'grado' => $page->getGrado()->getNombre(),
                               'titulo' => $page->getTitulo(),
                               'tipo' => $page->getTipoPagina()->getNombre(),
                               'modificacion' => $page->getFechaModificacion()->format('d/m/Y H:i a'),
                               'usuario' => $page->getUsuario()->getNombre().' '.$page->getUsuario()->getApellido(),
                               'status' => $page->getEstatusContenido()->getNombre(),
                               'subpaginas' => $subpaginas,
                               'mover' => $this->paginaMovible($page->getId()),
                               'prelada' => $page->getPrelada() ? $this->translator->trans('Prelada por').': '.$page->getPrelada()->getTitulo() : 0,
                               'delete_disabled' => $this->linkEliminar($page->getId(), 'EaPagina'));

        }

        return $paginas;

	}

	// Retorna un arreglo multidimensional de las subpaginas dada pagina_id
	public function subPaginas($pagina_id, $paginas_asociadas = array(), $json = 0, $movimiento = array())
	{

		$em = $this->em;
		$subpaginas = array();
		$tiene = 0;
		$return = $json ? array() : '';
		
		$subpages = $em->getRepository('ActualidadComunBundle:EaPagina')->findBy(array('pagina' => $pagina_id),
																			  	 array('grado' => 'ASC',
																			  	 	   'orden' => 'ASC'));
		
		foreach ($subpages as $subpage)
		{
			$tiene++;
			if (!$json)
			{
				$incluir = 1;
				if ($movimiento)
				{
					if($movimiento['pagina_id'] == $subpage->getId())
					{
						$incluir = 0;
					}
				}
				if ($incluir)
				{				
					$check = in_array($subpage->getId(), $paginas_asociadas) ? ' <span class="fa fa-check"></span>' : '';
					$return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getId().'" p_str="'.$subpage->getTipoPagina()->getNombre().': '.$subpage->getTitulo().'">'.$subpage->getTipoPagina()->getNombre().': '.$subpage->getTitulo().$check;
					$subPaginas = $this->subPaginas($subpage->getId(), $paginas_asociadas);
					if ($subPaginas['tiene'] > 0)
					{
						$return .= '<ul>';
						$return .= $subPaginas['return'];
						$return .= '</ul>';
					}
					$return .= '</li>';
				}
			}
			else {
				// Forma json para tree
				$subPaginas = $this->subPaginas($subpage->getId(), $paginas_asociadas, 1);
				if ($subPaginas['tiene'] > 0)
				{
					$return[] = array('text' => $subpage->getTipoPagina()->getNombre().': '.$subpage->getTitulo(),
		                              'state' => array('opened' => true),
		                              'icon' => 'fa fa-angle-double-right',
		                              'children' => $subPaginas['return']);
				}
				else {
					$return[] = array('text' => $subpage->getTipoPagina()->getNombre().': '.$subpage->getTitulo(),
		                              'state' => array('opened' => true),
		                              'icon' => 'fa fa-angle-double-right');
				}
			}
		}

		$subpaginas = array('tiene' => $tiene,
							'return' => $return);

		return $subpaginas;

	}

	// Duplicación de una página
	public function duplicarPagina($pagina_id, $titulo, $tipo_pagina_id, $estatus_contenido_id, $usuario_id)
	{

		$em = $this->em;
		$c = 0;

		$usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
		$pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
		$estatus_contenido = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->find($estatus_contenido_id);
		$tipo_pagina = $em->getRepository('ActualidadComunBundle:EaTipoPagina')->find($tipo_pagina_id);

		// Orden para el nuevo registro
		if ($pagina->getPagina())
		{
			$query = $em->createQuery('SELECT MAX(p.orden) FROM ActualidadComunBundle:EaPagina p 
                                    	WHERE p.pagina = :pagina_id 
                                    	AND p.empresa = :empresa_id')
						->setParameters(array('pagina_id' => $pagina->getPagina()->getId(),
											  'empresa_id' => $pagina->getEmpresa()->getId()));
		}
		else {
			$query = $em->createQuery('SELECT MAX(p.orden) FROM ActualidadComunBundle:EaPagina p 
                                    	WHERE p.pagina IS NULL 
                                    	AND p.grado = :grado_id 
                                    	AND p.empresa = :empresa_id')
						->setParameters(array('grado_id' => $pagina->getGrado()->getId(),
                                              'empresa_id' => $pagina->getEmpresa()->getId()));
		}
        $orden = $query->getSingleScalarResult();
        $orden++;

        $new_pagina = new EaPagina();
        $new_pagina->setTitulo($titulo);
        $new_pagina->setSubtitulo($pagina->getSubtitulo());
        $new_pagina->setPagina($pagina->getPagina());
        $new_pagina->setDescripcion($pagina->getDescripcion());
        $new_pagina->setContenido($pagina->getContenido());
        $new_pagina->setFoto($pagina->getFoto());
        $new_pagina->setPdf($pagina->getPdf());
        $new_pagina->setOrden($orden);
        $new_pagina->setInteractivo($pagina->getInteractivo());
        $new_pagina->setEstatusContenido($estatus_contenido);
        $new_pagina->setTipoPagina($tipo_pagina);
        $new_pagina->setGrado($pagina->getGrado());
        $new_pagina->setEmpresa($pagina->getEmpresa());
        $new_pagina->setPaginaReferencia($pagina->getPaginaReferencia());
        $new_pagina->setPrelada($pagina->getPrelada());
        $new_pagina->setUsuario($usuario);
        $new_pagina->setFechaCreacion(new \DateTime('now'));
        $new_pagina->setFechaModificacion(new \DateTime('now'));
        $em->persist($new_pagina);
        $em->flush();
        $c++;

        $prelaciones = array($pagina->getId() => $new_pagina->getId());

        // Duplicación de la prueba
        $c += $this->duplicarPrueba($pagina_id, $new_pagina->getId(), $usuario_id);

        // Duplicar sub-páginas
        $duplicacion = $this->duplicarSubPaginas($pagina_id, $new_pagina->getId(), $estatus_contenido_id, $usuario_id, $prelaciones);
        $c += $duplicacion['c'];
	    $prelaciones += $duplicacion['prelaciones'];

	    foreach ($prelaciones as $old_prelacion => $new_prelada_id)
	    {
	    	$old_prelaciones = $em->getRepository('ActualidadComunBundle:EaPagina')->findByPrelada($old_prelacion);
	    	$new_prelada = $em->getRepository('ActualidadComunBundle:EaPagina')->find($new_prelada_id);
	    	foreach ($old_prelaciones as $old)
	    	{
	    		if (array_key_exists($old->getId(), $prelaciones))
	    		{
	    			$new_prelacion = $em->getRepository('ActualidadComunBundle:EaPagina')->find($prelaciones[$old->getId()]);
	    			$new_prelacion->setPrelada($new_prelada);
	    			$em->persist($new_prelacion);
        			$em->flush();
	    		}
	    	}
	    }

        return array('inserts' => $c,
        			 'id' => $new_pagina->getId());

	}

	// Duplicación de la evaluación de una página
	public function duplicarPrueba($pagina_id, $new_pagina_id, $usuario_id)
	{

		$em = $this->em;
		$c = 0;	// Cantidad de registros insertados

		$prueba = $em->getRepository('ActualidadComunBundle:EaPrueba')->findOneByPagina($pagina_id);

		if ($prueba)
		{

			$new_pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($new_pagina_id);
			$usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);

			$new_prueba = new EaPrueba();
			$new_prueba->setNombre($prueba->getNombre());
			$new_prueba->setPagina($new_pagina);
			$new_prueba->setCantidadPreguntas($prueba->getCantidadPreguntas());
			$new_prueba->setCantidadMostrar($prueba->getCantidadMostrar());
			$new_prueba->setDuracion($prueba->getDuracion());
			$new_prueba->setEstatusContenido($prueba->getEstatusContenido());
			$new_prueba->setUsuario($usuario);
			$new_prueba->setFechaCreacion(new \DateTime('now'));
			$new_prueba->setFechaModificacion(new \DateTime('now'));
        	$em->persist($new_prueba);
        	$em->flush();
        	$c++;

        	// Preguntas
        	$par_preguntas = array();	// $par_preguntas[$pregunta_id] = $new_pregunta_id
        	$orden = 0;
        	$query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPregunta p 
	                                    WHERE p.prueba = :prueba_id 
	                                    AND p.pregunta IS NULL 
	                                    ORDER BY p.orden ASC")
	                    ->setParameter('prueba_id', $prueba->getId());
	        $preguntas = $query->getResult();

        	foreach ($preguntas as $pregunta)
        	{

        		$orden++;
        		$new_pregunta = new EaPregunta();
        		$new_pregunta->setEnunciado($pregunta->getEnunciado());
        		$new_pregunta->setImagen($pregunta->getImagen());
        		$new_pregunta->setPrueba($new_prueba);
        		$new_pregunta->setTipoPregunta($pregunta->getTipoPregunta());
        		$new_pregunta->setTipoElemento($pregunta->getTipoElemento());
        		$new_pregunta->setValor($pregunta->getValor());
        		$new_pregunta->setOrden($orden);
        		$new_pregunta->setCodigoInteractivo($pregunta->getCodigoInteractivo());
        		$new_pregunta->setUsuario($usuario);
        		$new_pregunta->setFechaCreacion(new \DateTime('now'));
				$new_pregunta->setFechaModificacion(new \DateTime('now'));
	        	$em->persist($new_pregunta);
	        	$em->flush();
	        	$c++;

	        	$par_preguntas[$pregunta->getId()] = $new_pregunta->getId();

	        	// Sub-preguntas
	        	$sub_preguntas = $em->getRepository('ActualidadComunBundle:EaPregunta')->findByPregunta($pregunta->getId());
	        	foreach ($sub_preguntas as $sub_pregunta)
	        	{

	        		$new_sub_pregunta = new EaPregunta();
	        		$new_sub_pregunta->setEnunciado($sub_pregunta->getEnunciado());
	        		$new_sub_pregunta->setImagen($sub_pregunta->getImagen());
	        		$new_sub_pregunta->setPrueba($new_prueba);
	        		$new_sub_pregunta->setTipoPregunta($sub_pregunta->getTipoPregunta());
	        		$new_sub_pregunta->setTipoElemento($sub_pregunta->getTipoElemento());
	        		$new_sub_pregunta->setValor($sub_pregunta->getValor());
	        		$new_sub_pregunta->setPregunta($new_pregunta);
	        		$new_sub_pregunta->setCodigoInteractivo($sub_pregunta->getCodigoInteractivo());
	        		$new_sub_pregunta->setUsuario($usuario);
	        		$new_sub_pregunta->setFechaCreacion(new \DateTime('now'));
					$new_sub_pregunta->setFechaModificacion(new \DateTime('now'));
		        	$em->persist($new_sub_pregunta);
		        	$em->flush();
		        	$c++;

		        	$par_preguntas[$sub_pregunta->getId()] = $new_sub_pregunta->getId();

	        	}

        	}

        	// Opciones
        	$par_opciones = array();	// $par_opciones[$opcion_id] = $new_opcion_id
        	$opciones = $em->getRepository('ActualidadComunBundle:EaOpcion')->findByPrueba($prueba->getId());

        	foreach ($opciones as $opcion)
        	{

        		$new_opcion = new EaOpcion();
        		$new_opcion->setDescripcion($opcion->getDescripcion());
        		$new_opcion->setImagen($opcion->getImagen());
        		$new_opcion->setPrueba($new_prueba);
        		$new_opcion->setUsuario($usuario);
        		$new_opcion->setFechaCreacion(new \DateTime('now'));
				$new_opcion->setFechaModificacion(new \DateTime('now'));
				$em->persist($new_opcion);
	        	$em->flush();
	        	$c++;

	        	$par_opciones[$opcion->getId()] = $new_opcion->getId();

        	}

        	// Preguntas/Opciones y de Asociación
        	$query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPregunta p 
	                                    WHERE p.prueba = :prueba_id 
	                                    ORDER BY p.orden ASC")
	                    ->setParameter('prueba_id', $prueba->getId());
	        $preguntas = $query->getResult();

	        foreach ($preguntas as $pregunta)
	        {

	        	$pos = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findByPregunta($pregunta->getId());
	        	foreach ($pos as $po)
	        	{

	        		$new_pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($par_preguntas[$po->getPregunta()->getId()]);
	        		$new_opcion = $em->getRepository('ActualidadComunBundle:EaOpcion')->find($par_opciones[$po->getOpcion()->getId()]);
	        		$pregunta_opcion = new EaPreguntaOpcion();
	        		$pregunta_opcion->setPregunta($new_pregunta);
	        		$pregunta_opcion->setOpcion($new_opcion);
	        		$pregunta_opcion->setCorrecta($po->getCorrecta());
	        		$em->persist($pregunta_opcion);
	        		$em->flush();
	        		$c++;
	        	}

	        	$pas = $em->getRepository('ActualidadComunBundle:EaPreguntaAsociacion')->findByPregunta($pregunta->getId());
	        	foreach ($pas as $pa)
	        	{

	        		$new_pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($par_preguntas[$pa->getPregunta()->getId()]);
	        		
	        		$preguntas_asociadas = explode(",", $pa->getPreguntas());
	        		$new_preguntas_asociadas = array();
	        		foreach ($preguntas_asociadas as $pregunta_asociada)
	        		{
	        			$new_preguntas_asociadas[] = $par_preguntas[$pregunta_asociada];
	        		}
	        		$new_preguntas_asociadas_str = implode(",", $new_preguntas_asociadas);

	        		$opciones_asociadas = explode(",", $pa->getOpciones());
	        		$new_opciones_asociadas = array();
	        		foreach ($opciones_asociadas as $opcion_asociada)
	        		{
	        			$new_opciones_asociadas[] = $par_opciones[$opcion_asociada];
	        		}
	        		$new_opciones_asociadas_str = implode(",", $new_opciones_asociadas);

	        		$pregunta_asociacion = new EaPreguntaAsociacion();
	        		$pregunta_asociacion->setPregunta($new_pregunta);
	        		$pregunta_asociacion->setPreguntas($new_preguntas_asociadas_str);
	        		$pregunta_asociacion->setOpciones($new_opciones_asociadas_str);
	        		$em->persist($pregunta_asociacion);
	        		$em->flush();
	        		$c++;

	        	}

	        }
		
		}

		return $c;

	}

	// Duplicación de sub-páginas dada la página
	public function duplicarSubPaginas($pagina_id, $pagina_padre_id, $estatus_contenido_id, $usuario_id, $prelaciones)
	{

		$em = $this->em;
		$c = 0;

		$estatus_contenido = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->find($estatus_contenido_id);
		$usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
		
		$paginas = $em->getRepository('ActualidadComunBundle:EaPagina')->findByPagina($pagina_id);
		$pagina_padre = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_padre_id);

		foreach ($paginas as $pagina)
		{

			$query = $em->createQuery('SELECT MAX(p.orden) FROM ActualidadComunBundle:EaPagina p 
                                    	WHERE p.pagina = :pagina_id 
                                    	AND p.empresa = :empresa_id')
						->setParameters(array('pagina_id' => $pagina_padre_id,
											  'empresa_id' => $pagina_padre->getEmpresa()->getId()));
			$orden = $query->getSingleScalarResult();
        	$orden++;

        	$new_pagina = new EaPagina();
	        $new_pagina->setTitulo($pagina->getTitulo());
	        $new_pagina->setSubtitulo($pagina->getSubtitulo());
	        $new_pagina->setPagina($pagina_padre);
	        $new_pagina->setDescripcion($pagina->getDescripcion());
	        $new_pagina->setContenido($pagina->getContenido());
	        $new_pagina->setFoto($pagina->getFoto());
	        $new_pagina->setPdf($pagina->getPdf());
	        $new_pagina->setOrden($orden);
	        $new_pagina->setInteractivo($pagina->getInteractivo());
	        $new_pagina->setEstatusContenido($estatus_contenido);
	        $new_pagina->setTipoPagina($pagina->getTipoPagina());
	        $new_pagina->setGrado($pagina->getGrado());
	        $new_pagina->setEmpresa($pagina->getEmpresa());
	        $new_pagina->setPaginaReferencia($pagina->getPaginaReferencia());
	        $new_pagina->setUsuario($usuario);
	        $new_pagina->setFechaCreacion(new \DateTime('now'));
	        $new_pagina->setFechaModificacion(new \DateTime('now'));
	        $em->persist($new_pagina);
	        $em->flush();
	        $c++;

	        $prelaciones[$pagina->getId()] = $new_pagina->getId();

	        // Duplicación de la prueba
	        $c += $this->duplicarPrueba($pagina->getId(), $new_pagina->getId(), $usuario_id);

	        // Duplicar sub-páginas
	        $duplicacion = $this->duplicarSubPaginas($pagina->getId(), $new_pagina->getId(), $estatus_contenido_id, $usuario_id, $prelaciones);
	        $c += $duplicacion['c'];
	        $prelaciones += $duplicacion['prelaciones'];

		}

        return array('c' => $c,
        			 'prelaciones' => $prelaciones);

	}

	// Retorna el id de la página padre de todas
	public function paginaRaiz($pagina)
	{

		if ($pagina->getPagina())
		{
			return $this->paginaRaiz($pagina->getPagina());
		}
		else {
			return $pagina->getId();
		}

	}

	public function startLesson($indexedPages, $pagina_id, $usuario_id, $pagina_iniciada)
	{

		$em = $this->em;
		$logs = array();

		$pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
		$usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
		$estatus_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($pagina_iniciada);

		$pagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                               'usuario' => $usuario_id));

        if (!$pagina_log)
        {

        	//Revisar antes si el padre ya tiene log
        	if ($indexedPages[$pagina_id]['padre'] > 0)
        	{
        		$logs_padre = $this->startLesson($indexedPages, $indexedPages[$pagina_id]['padre'], $usuario_id, $pagina_iniciada);
        		if (count($logs_padre))
        		{
        			$logs += $logs_padre;
        		}
        	}

            $pagina_log = new EaPaginaLog();
            $pagina_log->setPagina($pagina);
            $pagina_log->setUsuario($usuario);
            $pagina_log->setFechaInicio(new \DateTime('now'));
            $pagina_log->setEstatusPagina($estatus_pagina);
            $pagina_log->setPorcentajeAvance(0);
            $em->persist($pagina_log);
        	$em->flush();

        	$logs[] = $pagina_log->getId();

        }

        $pagina_log->setFechaInteraccion(new \DateTime('now'));
        $em->persist($pagina_log);
    	$em->flush();

		return $logs;

	}

	public function finishLesson($indexedPages, $pagina_id, $usuario_id, $yml)
	{

		$em = $this->em;
		$log_id = 0;

		$pagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                        	   'usuario' => $usuario_id,
                                                                                        	   'estatusPagina' => $yml['parameters']['estatus_pagina']['iniciada']));

        if ($pagina_log)
        {

        	// Revisar antes si tiene sub-páginas iniciadas
        	$subpaginas_iniciadas = $this->subpaginasIniciadas($indexedPages, $pagina_id, $usuario_id, $yml['parameters']['estatus_pagina']['completada']);
        	if (!$subpaginas_iniciadas)
        	{
        		// Se completa o se coloca en evaluación la lección
        		if ($indexedPages[$pagina_id]['tiene_evaluacion'])
        		{
        			$estatus = $yml['parameters']['estatus_pagina']['en_evaluacion'];
        			$total = count($indexedPages[$pagina_id]['subpaginas']) + 1; // Sub-páginas + evaluación
        			if ($total == 1)
        			{
        				$total = 2; // La página + la evaluación
        			}
        			$avance = (($total-1)/$total)*100;
        			//$avance = (1 - $yml['parameters']['ponderacion']['evaluacion'])*100;
        			$avance = round($avance, 2);
        		}
        		else {
        			$estatus = $yml['parameters']['estatus_pagina']['completada'];
        			$avance = 100;
        			$pagina_log->setFechaFin(new \DateTime('now'));
        		}
        		$status_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($estatus);
	            $pagina_log->setEstatusPagina($status_pagina);
	            $pagina_log->setPorcentajeAvance($avance);
	            $pagina_log->setFechaInteraccion(new \DateTime('now'));
	            $em->persist($pagina_log);
	        	$em->flush();

	        	// Cálculo del porcentaje de avance de toda la línea de ascendente
	        	$this->calculoAvance($indexedPages, $pagina_id, $usuario_id, $yml);

	        	// Notificación de alumno completó los ejercicios de la unidad
	        	if ($estatus == $yml['parameters']['estatus_pagina']['completada'] && 
	        		$pagina_log->getPagina()->getTipoPagina()->getId() == $yml['parameters']['tipo_pagina']['unidad'] && 
	        		$pagina_log->getUsuario()->getRol()->getId() == $yml['parameters']['rol']['alumno'])
	        	{
	        		$profesor_alumno = $em->getRepository('ActualidadComunBundle:EaProfesorAlumno')->findByAlumno($usuario_id);
	        		$pagina_padre_id = $indexedPages[$pagina_id]['padre'];
	        		foreach ($profesor_alumno as $pa)
	        		{
	        			$nombre = ucwords(mb_strtolower($pa->getAlumno()->getNombre(), 'UTF-8'));
						$apellido = ucwords(mb_strtolower($pa->getAlumno()->getApellido(), 'UTF-8'));
						$descripcion = $nombre.' '.$apellido.' '.$this->translator->trans('completó la Unidad').' '.$indexedPages[$pagina_id]['orden'].' '.$this->translator->trans('del libro').' '.$indexedPages[$pagina_padre_id]['titulo'].' '.$indexedPages[$pagina_padre_id]['descripcion_grado'].'.';
	        			$this->newAlarm($yml['parameters']['tipo_alarma']['unidad_completada'], $descripcion, $pa->getProfesor(), $pagina_id);
	        		}
	        	}

        	}

        	$log_id = $pagina_log->getId();

        }

		return $log_id;

	}

	public function subpaginasIniciadas($indexedPages, $pagina_id, $usuario_id, $estatus_completada)
	{

		$em = $this->em;
		$iniciada = 0;
		$completadas = 0;

		if (count($indexedPages[$pagina_id]['subpaginas']))
		{
			foreach ($indexedPages[$pagina_id]['subpaginas'] as $subpagina)
			{
				$qb = $em->createQueryBuilder();
		        $qb->select('pl')
		           ->from('ActualidadComunBundle:EaPaginaLog', 'pl')
		           ->andWhere('pl.pagina = :pagina_id')
		           ->andWhere('pl.usuario = :usuario_id')
		           ->orderBy('pl.id', 'DESC')
		           ->setParameters(array('pagina_id' => $subpagina['id'],
	            					  	 'usuario_id' => $usuario_id));
		        $query = $qb->getQuery();
		        $subpagina_iniciada = $query->getResult();
				if ($subpagina_iniciada)
				{
					if ($subpagina_iniciada[0]->getEstatusPagina()->getId() != $estatus_completada)
					{
						$iniciada = 1;
						break;
					}
					else {
						$completadas++;
					}
				}
			}
			if (!$iniciada && count($indexedPages[$pagina_id]['subpaginas']) != $completadas)
			{
				$iniciada = 1;
			}
		}

        return $iniciada;

	}

	public function calculoAvance($indexedPages, $pagina_id, $usuario_id, $yml)
	{

		$em = $this->em;

		if ($indexedPages[$pagina_id]['padre'])
		{

			$pagina_padre_id = $indexedPages[$pagina_id]['padre'];
			$pagina_padre_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $pagina_padre_id,
		                                                                                        		 'usuario' => $usuario_id));

			if ($pagina_padre_log)
			{

				$n = count($indexedPages[$pagina_padre_id]['subpaginas']);
				if ($indexedPages[$pagina_padre_id]['tiene_evaluacion'])
				{
					$n = $n+1;
				}
				//$max_porcentaje = $indexedPages[$pagina_padre_id]['tiene_evaluacion'] ? (1 - $yml['parameters']['ponderacion']['evaluacion']) : 1;
				$max_porcentaje = 1;
				$avance_total = 0;
				$avance_parcial = 0;
				$subpaginas_completadas = 1;

				foreach ($indexedPages[$pagina_padre_id]['subpaginas'] as $subpagina)
				{
					$subpagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $subpagina['id'],
			                                                                                        		  'usuario' => $usuario_id));
					if ($subpagina_log)
					{
						$avance_parcial += $subpagina_log->getPorcentajeAvance();
						if ($subpagina_log->getEstatusPagina()->getId() != $yml['parameters']['estatus_pagina']['completada'])
						{
							$subpaginas_completadas = 0;
						}
					}
					else {
						$subpaginas_completadas = 0;
					}
				}

				if ($indexedPages[$pagina_padre_id]['tiene_evaluacion'])
				{
					$avance_prueba = 0;
					$query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPruebaLog pl 
			                                    JOIN pl.prueba p 
			                                    WHERE pl.usuario = :usuario_id 
			                                    AND p.pagina = :pagina_id 
			                                    AND pl.estado != :estado 
			                                    ORDER BY pl.id DESC")
			                    ->setParameters(array('usuario_id' => $usuario_id,
			                    					  'pagina_id' => $pagina_padre_id,
			                    					  'estado' => $yml['parameters']['estado_prueba']['reprobado']));
			        $pruebas_log = $query->getResult();
			        if ($pruebas_log)
			        {
			        	$avance_prueba = $pruebas_log[0]->getPorcentajeAvance();
			        }
			        //$avance_total += $avance_prueba*$yml['parameters']['ponderacion']['evaluacion'];
			        $avance_parcial += $avance_prueba;
				}

				$avance_total = ($avance_parcial/$n)*$max_porcentaje;

				// Finalmente se almacena el avance calculado en la página padre
				$avance_total = round($avance_total, 2);
				$pagina_padre_log->setPorcentajeAvance($avance_total > 100 ? 100 : $avance_total);
				if ($avance_total >= 100)
				{
					$estatus_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($yml['parameters']['estatus_pagina']['completada']);
					$pagina_padre_log->setEstatusPagina($estatus_pagina);
					$pagina_padre_log->setFechaFin(new \DateTime('now'));
				}
				else {
					if ($subpaginas_completadas && $indexedPages[$pagina_padre_id]['tiene_evaluacion'])
					{
						$estatus_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($yml['parameters']['estatus_pagina']['en_evaluacion']);
						$pagina_padre_log->setEstatusPagina($estatus_pagina);
					}
				}

				$pagina_padre_log->setFechaInteraccion(new \DateTime('now'));
				$em->persist($pagina_padre_log);
	        	$em->flush();

	        	// Notificación de alumno completó los ejercicios de la unidad
	        	if ($pagina_padre_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'] && 
	        		$pagina_padre_log->getPagina()->getTipoPagina()->getId() == $yml['parameters']['tipo_pagina']['unidad'] && 
	        		$pagina_padre_log->getUsuario()->getRol()->getId() == $yml['parameters']['rol']['alumno'])
	        	{
	        		$profesor_alumno = $em->getRepository('ActualidadComunBundle:EaProfesorAlumno')->findByAlumno($usuario_id);
	        		$libro_id = $indexedPages[$pagina_padre_id]['padre'];
	        		foreach ($profesor_alumno as $pa)
	        		{
	        			$nombre = ucwords(mb_strtolower($pa->getAlumno()->getNombre(), 'UTF-8'));
						$apellido = ucwords(mb_strtolower($pa->getAlumno()->getApellido(), 'UTF-8'));
						$descripcion = $nombre.' '.$apellido.' '.$this->translator->trans('completó la Unidad').' '.$indexedPages[$pagina_padre_id]['orden'].' '.$this->translator->trans('del libro').' '.$indexedPages[$libro_id]['titulo'].' '.$indexedPages[$libro_id]['descripcion_grado'].'.';
	        			$this->newAlarm($yml['parameters']['tipo_alarma']['unidad_completada'], $descripcion, $pa->getProfesor(), $pagina_padre_id);
	        		}
	        	}

			}

			// Calcular el avance del abuelo
			$this->calculoAvance($indexedPages, $pagina_padre_id, $usuario_id, $yml);

		}

	}

	public function ExcelCodigos($codigos,$pagina_id,$grado_id,$yml,$pex){

		$em = $this->em;
		$libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
        $grado = $em->getRepository('ActualidadComunBundle:AdminGrado')->find($grado_id);

		$fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/reporteCodigos.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

		// Encabezado
        $objWorksheet->setCellValue('A1', $this->translator->trans('Libro').': '.$libro->getTitulo().'.');
        $objWorksheet->setCellValue('A2', $this->translator->trans('Grado').': '.$grado->getNombre().'.'.$grado->getDescripcion());

        if (!count($codigos))
        {
            $objWorksheet->mergeCells('A5:C5');
            $objWorksheet->setCellValue('A5', $this->translator->trans('No existen registros para esta consulta'));
        }
        else {

            $row = 5;
            $i = 0;
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );
            $font_size = 11;
            $font = 'Arial';
            $horizontal_aligment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
            $vertical_aligment = \PHPExcel_Style_Alignment::VERTICAL_CENTER;

            foreach ($codigos as $codigo)
            {
				if($codigo->getActivo()){
					$status = $this->translator->trans('En uso');
				}else{
					$status = $this->translator->trans('Disponible');
				}
                
                $objWorksheet->getStyle("A$row:C$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:C$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:CC$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:C$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:C$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(25); // Altura de la fila
            
                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $codigo->getCodigo());
                $objWorksheet->setCellValue('B'.$row, $status);
                $objWorksheet->setCellValue('C'.$row, $codigo->getFechaVencimiento()->format('d/m/Y'));
                $row++;

            }

        }

        // Crea el writer
        $libroTitulo = $this->eliminarAcentos($libro->getTitulo());
        $longitud = strlen($libroTitulo);
        $libroTitulo = ($longitud<=4) ? $libroTitulo : substr($libroTitulo,0,4);
        $grado = $this->eliminarAcentos($grado->getNombre());
        $hoy = date('d-m-Y');
        $writer = $pex->createWriter($objPHPExcel, 'Excel5');
        $path = 'codigos/codigos_'.$libroTitulo.'_'.$hoy.'.xls';
        $xls = $yml['parameters']['folders']['dir_uploads'].$path;
        $writer->save($xls);

        $archivo = $yml['parameters']['folders']['uploads'].$path;
        $document_name = 'codigos_'.$libroTitulo.'_'.$hoy.'.xls';
        $bytes = filesize($xls);
        $document_size = $this->fileSizeConvert($bytes);

		$return = array('archivo' => $archivo,
                        'document_name' => $document_name,
                        'document_size' => $document_size);

		return $return;
		
	}

	public function eliminarAcentos($text)
    {

        $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        $text = strtolower($text);
        $patron = array (
            // Espacios, puntos y comas por guion
            //'/[\., ]+/' => ' ',
 
            // Vocales
            '/\+/' => '',
            '/&agrave;/' => 'a',
            '/&egrave;/' => 'e',
            '/&igrave;/' => 'i',
            '/&ograve;/' => 'o',
            '/&ugrave;/' => 'u',
 
            '/&aacute;/' => 'a',
            '/&eacute;/' => 'e',
            '/&iacute;/' => 'i',
            '/&oacute;/' => 'o',
            '/&uacute;/' => 'u',
 
            '/&acirc;/' => 'a',
            '/&ecirc;/' => 'e',
            '/&icirc;/' => 'i',
            '/&ocirc;/' => 'o',
            '/&ucirc;/' => 'u',
 
            '/&atilde;/' => 'a',
            '/&etilde;/' => 'e',
            '/&itilde;/' => 'i',
            '/&otilde;/' => 'o',
            '/&utilde;/' => 'u',
 
            '/&auml;/' => 'a',
            '/&euml;/' => 'e',
            '/&iuml;/' => 'i',
            '/&ouml;/' => 'o',
            '/&uuml;/' => 'u',
 
            '/&auml;/' => 'a',
            '/&euml;/' => 'e',
            '/&iuml;/' => 'i',
            '/&ouml;/' => 'o',
            '/&uuml;/' => 'u',
 
            // Otras letras y caracteres especiales
            '/&aring;/' => 'a',
            '/&ntilde;/' => 'n',
 
            // Agregar aqui mas caracteres si es necesario
 
        );
 
        $text = preg_replace(array_keys($patron),array_values($patron),$text);
        return $text;
    }

    // Setear el mismo grado de la página a sus sub-páginas
	public function setGradoSubPages($pagina_id, $grado)
	{

		$em = $this->em;
		
		$paginas = $em->getRepository('ActualidadComunBundle:EaPagina')->findByPagina($pagina_id);

		foreach ($paginas as $pagina)
		{

			$pagina->setGrado($grado);
	        $em->persist($pagina);
	        $em->flush();
	        
	        $this->setGradoSubPages($pagina->getId(), $grado);

		}

	}

	// Eliminación en cascada de la estructura de la página
	public function deletePaginas($pagina, $yml)
	{

		$em = $this->em;
		
		$subpaginas = $em->getRepository('ActualidadComunBundle:EaPagina')->findByPagina($pagina->getId());

		foreach ($subpaginas as $subpagina)
		{

			$this->deletePaginas($subpagina, $yml);

			// Eliminación de la evaluación asociada
			$prueba = $em->getRepository('ActualidadComunBundle:EaPrueba')->findOneByPagina($subpagina->getId());

			if ($prueba)
			{

				// Eliminación de preguntas
				$query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPregunta p 
		                                    WHERE p.prueba = :prueba_id 
		                                    AND p.pregunta IS NULL 
		                                    ORDER BY p.orden ASC")
			                ->setParameter('prueba_id', $prueba->getId());
			    $preguntas = $query->getResult();

				foreach ($preguntas as $pregunta)
				{

					// Eliminación de pregunta_opcion
					if ($pregunta->getTipoPregunta()->getId() != $yml['parameters']['tipo_pregunta']['asociacion'])
		            {

		                $query = $em->createQuery("SELECT po FROM ActualidadComunBundle:EaPreguntaOpcion po 
		                                            JOIN po.opcion o 
		                                            WHERE po.pregunta = :pregunta_id 
		                                            AND o.prueba = :prueba_id 
		                                            ORDER BY o.id ASC")
		                            ->setParameters(array('pregunta_id' => $pregunta->getId(),
		                                                  'prueba_id' => $prueba->getId()));
		                $pos = $query->getResult();

		                foreach ($pos as $po)
		                {

		                	$opcion = $po->getOpcion();

		                	// Eliminación de pregunta_opcion
		                    $em->remove($po);
                			$em->flush();

                			// Eliminación de la opción
                			$em->remove($opcion);
					        $em->flush();

		                }

		            }
		            else {
		                
		                $asociacion = $em->getRepository('ActualidadComunBundle:EaPreguntaAsociacion')->findOneByPregunta($pregunta->getId());

		                if ($asociacion)
		                {

		                    $preguntas_asociadas = explode(",", $asociacion->getPreguntas());

		                    $query = $em->createQuery("SELECT po, o, p FROM ActualidadComunBundle:EaPreguntaOpcion po 
		                                                JOIN po.opcion o JOIN po.pregunta p 
		                                                WHERE po.pregunta IN (:preguntas_id) 
		                                                AND o.prueba = :prueba_id 
		                                                AND p.pregunta = :pregunta_id 
		                                                ORDER BY po.id ASC")
		                                ->setParameters(array('preguntas_id' => $preguntas_asociadas,
		                                                      'prueba_id' => $prueba->getId(),
		                                                      'pregunta_id' => $pregunta->getId()));
		                    $pos = $query->getResult();

		                    foreach ($pos as $po)
		                    {

		                    	$opcion = $po->getOpcion();
		                    	$p = $po->getPregunta();

		                    	// Eliminación de pregunta_opcion
			                    $em->remove($po);
	                			$em->flush();

	                			// Eliminación de la opción
	                			$em->remove($opcion);
						        $em->flush();

						        // Eliminación de la sub-pregunta
						        $em->remove($p);
						        $em->flush();

		                    }

		                    $em->remove($asociacion);
						    $em->flush();

		                }

		            }

		            $em->remove($pregunta);
					$em->flush();

				}

				$em->remove($prueba);
				$em->flush();

			}

			$em->remove($subpagina);
			$em->flush();

		}

		$em->remove($pagina);
		$em->flush();

	}

	public function retornarComentariosForo($coments,$archivoUsuarios=0)
	{
		  $em = $this->em;
		  $comentarios = array();
          foreach ($coments as $coment)
          {
               $archivos = FALSE;
               if($archivoUsuarios == 1){
               	$consulta = $em->createQuery("SELECT file FROM ActualidadComunBundle:EaForoArchivo file
                                   WHERE file.foro = :foro_id
                                   AND file.usuario = :usuario_id
                                   ORDER BY file.id ASC")
                     ->setParameters(array('foro_id' => $coment->getId(), 'usuario_id'=> $coment->getUsuario()->getId()));
            	$archivos = $consulta->getResult();

               }
               

               $comentarios[]= array('id'=>$coment->getId(),
                                      'asunto'=>$coment->getTema(),
                                      'mensaje'=>$coment->getMensaje(),
                                      'usuarioId'=>$coment->getUsuario()->getId(),
                                      'nombre'=>$coment->getUsuario()->getNombre(),
                                      'apellido'=>$coment->getUsuario()->getApellido(),
                                      'fecharegistro'=>$coment->getFechaPublicacion()->format("d/m/Y"),
                                      'delete_disabled'=>$this->linkEliminar($coment->getId(),'EaForo'),
                                      'archivos'=> ($archivos)? 1:0
                                     );

         }



        return $comentarios;
	}

	// Retorna un arreglo con la estructura completa de las páginas con sus sub-páginas desde el punto de vista de un usuario
	public function paginasUsuario($usuario_id, $tipo_pagina_id, $empresas, $yml)
	{

		$em = $this->em;
		$hoy = date('Y-m-d');

		// Usuario
		$usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);

		if ($usuario->getRol()->getId() != $yml['parameters']['rol']['revisor'])
		{
			$query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
	                                   WHERE p.pagina IS NULL 
	                                   AND p.tipoPagina = :tipo_pagina_id 
	                                   AND p.estatusContenido = :activo 
	                                   AND p.empresa IN (:empresas) 
	                                   ORDER BY p.grado ASC, p.orden ASC')
	                    ->setParameters(array('tipo_pagina_id' => $tipo_pagina_id,
	                                          'activo' => $yml['parameters']['estatus_contenido']['activo'],
	                                          'empresas' => $empresas));
		}
		else {
			$query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
	                                   WHERE p.pagina IS NULL 
	                                   AND p.tipoPagina = :tipo_pagina_id 
	                                   AND p.empresa IN (:empresas) 
	                                   ORDER BY p.grado ASC, p.orden ASC')
	                    ->setParameters(array('tipo_pagina_id' => $tipo_pagina_id,
	                                          'empresas' => $empresas));
		}
        $paginas_bd = $query->getResult();

		// Estructura de páginas
        $paginas = array();
        $orden = 0;
        foreach ($paginas_bd as $pagina)
        {

            $orden++;

            // Tiene evaluación
            $tiene_evaluacion = $em->getRepository('ActualidadComunBundle:EaPrueba')->findOneBy(array('pagina' => $pagina->getId(),
                                                                                       				  'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']));

            // Código del libro
            $pagina_usuario = $em->getRepository('ActualidadComunBundle:EaPaginaUsuario')->findOneBy(array('usuario' => $usuario_id,
                                                                                       					   'pagina' => $pagina->getId(),
                                                                                       					   'activo' => true));

            $razon_vigencia = '';
            if ($pagina_usuario)
            {
            	if ($pagina_usuario->getFechaVencimiento()->format('Y-m-d') < $hoy)
            	{
            		$razon_vigencia = $this->translator->trans('Expiró el').' '.$this->formatDateEs($pagina_usuario->getFechaVencimiento()->format('Y-m-d'));
            	}
            	elseif ($pagina_usuario->getFechaInicio()->format('Y-m-d') > $hoy) {
            		$razon_vigencia = $this->translator->trans('Inicia el').' '.$this->formatDateEs($pagina_usuario->getFechaInicio()->format('Y-m-d'));
            	}
            }

            $subPaginas = $this->subPaginasUsuario($usuario, $pagina, $yml);

            $paginas[$pagina->getId()] = array('id' => $pagina->getId(),
            								   'grado' => $pagina->getGrado()->getId(),
            								   'descripcion_grado' => $pagina->getGrado()->getDescripcion(),
                                               'orden' => $orden,
                                               'titulo' => $pagina->getEstatusContenido()->getId() == $yml['parameters']['estatus_contenido']['activo'] ? $pagina->getTitulo() : $pagina->getTitulo().' (inactivo)',
                                               'tipo_pagina' => $pagina->getTipoPagina()->getNombre(),
                                               'foto' => $pagina->getFoto(),
                                               'css' => $pagina->getEstilo()->getNombre(),
                                               'interactivo' => $pagina->getInteractivo(),
                                               'tiene_evaluacion' => $tiene_evaluacion ? true : false,
                                               'prueba_id' => $tiene_evaluacion ? $tiene_evaluacion->getId() : 0,
                                               'codigo_activo' => $pagina_usuario ? true : false,
                                               'codigo_vigente' => $pagina_usuario ? ($pagina_usuario->getFechaInicio()->format('Y-m-d') <= $hoy && $pagina_usuario->getFechaVencimiento()->format('Y-m-d') >= $hoy) ? true : false : '',
                                               'razon_vigencia' => $razon_vigencia,
                                               'codigo_inicio' => $pagina_usuario ? $pagina_usuario->getFechaInicio()->format('d/m/Y') : '',
                                               'codigo_vencimiento' => $pagina_usuario ? $pagina_usuario->getFechaVencimiento()->format('d/m/Y') : '',
                                               'codigo_activacion' => $pagina_usuario ? $pagina_usuario->getFechaActivacion()->format('d/m/Y') : '',
                                               'subpaginas' => $subPaginas);

        }

        return $paginas;

	}

	// Retorna un arreglo con el id de las empresas que tienen acceso a la interfaz de frontend
	public function sistemaEmpresas($sistema_empresas)
	{

		$em = $this->em;
		$empresas = array();
		
		if ($sistema_empresas['multiempresas']) 
        {
            $exclusion = $sistema_empresas['exclusion'];
            $query = $em->createQuery("SELECT e FROM ActualidadComunBundle:AdminEmpresa e 
                                        WHERE e.id NOT IN (:exclusion) 
                                        AND e.activo = :activo 
                                        ORDER BY e.id ASC")
                        ->setParameters(array('exclusion' => $exclusion,
                                              'activo' => true));
            $empresas_bd = $query->getResult();
            foreach ($empresas_bd as $empresa)
    		{
    			$empresas[] = $empresa->getId();
    		}
        }
        else {
        	$empresas[] = $sistema_empresas['piloto'];
        }

        return $empresas;

	}

	// Retorna true si la pagina es liberada o si la pagina que la prela ya fue aprobada
	public function accesoPrelacion($pagina, $usuario_id, $yml)
	{

		$em = $this->em;
		$acceso = false;
		$razon = '';

		if ($pagina->getPrelada())
		{

			// Debe estar completada su prelatoria
			$pagina_log_prelada = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                       					   'pagina' => $pagina->getPrelada()->getId(),
                                                                                       					   'estatusPagina' => $yml['parameters']['estatus_pagina']['completada']));

			if ($pagina_log_prelada)
			{
				$acceso = true;
			}
			else {

				$razon = 'Es prelada por '.$pagina->getPrelada()->getTipoPagina()->getNombre().' '.$pagina->getPrelada()->getTitulo().' y su contenido aún no ha sido completado.';

				// Se verifica si es una unidad y si está liberada por el profesor
				if ($pagina->getTipoPagina()->getId() == $yml['parameters']['tipo_pagina']['unidad'])
				{

					$usuario_seccion = $em->getRepository('ActualidadComunBundle:AdminUsuarioSeccion')->findOneByUsuario($usuario_id);
					if ($usuario_seccion)
					{
						$seguimiento = $em->getRepository('ActualidadComunBundle:EaProfesorAlumno')->findOneBy(array('alumno' => $usuario_id,
	                                                                                       					   		 'seccion' => $usuario_seccion->getSeccion()->getId()));
						if ($seguimiento)
						{
							$query = $em->createQuery('SELECT COUNT(pl.id) FROM ActualidadComunBundle:EaPaginaLiberada pl
				                                       WHERE pl.pagina = :pagina_id 
				                                       AND pl.seccion = :seccion_id 
				                                       AND pl.fecha_vencimiento >= :hoy')
				                        ->setParameters(array('pagina_id' => $pagina->getId(),
				                                              'seccion_id' => $usuario_seccion->getSeccion()->getId(),
				                                              'hoy' => date('Y-m-d')));
				            $liberada = $query->getSingleScalarResult();
				            if ($liberada)
				            {
				            	$acceso = true;
				            }
						}
					}
					
				}

			}

		}
		else {
			$acceso = true;
		}

        return array('acceso' => $acceso,
        			 'razon' => $razon);

	}

	// Retorna un arreglo con la estructura completa de las sub-páginas desde el punto de vista de un usuario
	public function subPaginasUsuario($usuario, $pagina, $yml)
	{

		$em = $this->em;
		$hoy = date('Y-m-d');

		$query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
                                   WHERE p.pagina = :pagina_id 
                                   AND p.estatusContenido = :activo 
                                   ORDER BY p.grado ASC, p.orden ASC')
                    ->setParameters(array('pagina_id' => $pagina->getId(),
                                          'activo' => $yml['parameters']['estatus_contenido']['activo']));
        $subpaginas_bd = $query->getResult();

		// Estructura de páginas
        $subpaginas = array();
        $orden = 0;
        foreach ($subpaginas_bd as $subpagina)
        {

            $orden++;

            // Tiene evaluación
            $tiene_evaluacion = $em->getRepository('ActualidadComunBundle:EaPrueba')->findOneBy(array('pagina' => $subpagina->getId(),
                                                                                       				  'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']));

            $subpaginas[$subpagina->getId()] = array('id' => $subpagina->getId(),
                                            		 'orden' => $orden,
                                               		 'titulo' => $subpagina->getTitulo(),
                                               		 'tipo_pagina' => $subpagina->getTipoPagina()->getNombre(),
                                               		 'foto' => $subpagina->getFoto(),
                                               		 'interactivo' => $subpagina->getInteractivo(),
                                               		 'tiene_evaluacion' => $tiene_evaluacion ? true : false,
                                               		 'prueba_id' => $tiene_evaluacion ? $tiene_evaluacion->getId() : 0,
                                               		 'subpaginas' => $this->subPaginasUsuario($usuario, $subpagina, $yml));

        }

        return $subpaginas;

	}

	public function indexPages($pagina)
	{

		$indexedPages = array();
		$sobrinos = 0;

		// Recorrido inicial de las sub-páginas para determinar si a este nivel tienen sobrinos (sub-páginas de los hermanos)
		foreach ($pagina['subpaginas'] as $subpagina)
		{
			if (count($subpagina['subpaginas']))
			{
				$sobrinos++;
			}
		}

		// Indexar las sub-páginas
		foreach ($pagina['subpaginas'] as $subpagina)
		{
			$subpagina['padre'] = $pagina['id'];
			$subpagina['sobrinos'] = $sobrinos;
			$subpagina['hijos'] = count($subpagina['subpaginas']);
			$indexedPages[$subpagina['id']] = $subpagina;
			if (count($subpagina['subpaginas']))
			{
				$indexedPages += $this->indexPages($subpagina);
			}
		}

		return $indexedPages;

	}

	// Setea los datos de la sesión del usuario para el frontend
	public function setSesionFront($usuario, $yml)
	{

		$em = $this->em;

		$nombre = ucwords(mb_strtolower($usuario->getNombre(), 'UTF-8'));
		$apellido = ucwords(mb_strtolower($usuario->getApellido(), 'UTF-8'));

		// Por si acaso el nombre y apellido están compuestos de más de una palabra
		$nombre_arr = explode(" ", $nombre);
		$nombre = $nombre_arr[0];

		$apellido_arr = explode(" ", $apellido);
		$apellido = $apellido_arr[0];

		$datosUsuario = array('id' => $usuario->getId(),
                              'login' => $usuario->getLogin(),
                              'nombre' => $nombre,
                              'apellido' => $apellido,
                              'correo' => trim($usuario->getCorreo()),
                              'fecha_nacimiento' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('Y-m-d') : '',
                              'fecha_nacimiento_formateada' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('d/m/Y') : '',
                              'foto' => $usuario->getFoto(),
                              'rol_id' => $usuario->getRol()->getId(),
                              'rol' => $usuario->getRol()->getNombre(),
                              'grado_id' => $usuario->getGrado() ? $usuario->getGrado()->getId() : 0);

		// Tipo de libro
		switch ($usuario->getRol()->getId())
		{

			case $yml['parameters']['rol']['alumno']:
				$tipo_pagina_id = $yml['parameters']['tipo_pagina']['libro_alumnos'];
				break;

			case $yml['parameters']['rol']['profesor']:
				$tipo_pagina_id = $yml['parameters']['tipo_pagina']['libro_profesores'];
				break;

			default:
				$tipo_pagina_id = $yml['parameters']['tipo_pagina']['libro_alumnos'];
				break;

		}
		
		// Estructura de páginas que tengan códigos generados
        $empresas = $this->sistemaEmpresas($yml['parameters']['sistema_empresas']);
        $paginas = $this->paginasUsuario($usuario->getId(), $tipo_pagina_id, $empresas, $yml);

        // Cierre de sesiones activas
        $sesiones = $em->getRepository('ActualidadComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                    	  'disponible' => true));
        foreach ($sesiones as $s)
        {
            $s->setDisponible(false);
            $em->persist($s);
        	$em->flush();
        }

        // Se crea la sesión en BD
        $admin_sesion = new AdminSesion();
        $admin_sesion->setFechaIngreso(new \DateTime('now'));
        $admin_sesion->setFechaRequest(new \DateTime('now'));
        $admin_sesion->setUsuario($usuario);
        $admin_sesion->setDisponible(true);
        $em->persist($admin_sesion);
        $em->flush();

        $session = new Session();
        $session->set('iniFront', true);
        $session->set('sesion_id', $admin_sesion->getId());
        $session->set('code', $yml['parameters']['search_locale'] ? $this->getLocaleCode() : 'DO');
        $session->set('usuario', $datosUsuario);
        $session->set('paginas', $paginas);

	}

	// Retorna un arreglo de los libros seteados en la sección de un usuario categorizados por grado
	public function librosGrados($paginas, $usuario_id)
	{

		$em = $this->em;
		$libros = array();
		$i = 0;
		$grado = 0;

		foreach ($paginas as $pagina)
		{

			$i++;

			// Interacción con la página
			$pagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                       		 	   'pagina' => $pagina['id']));
			$pagina['estatus_pagina_id'] = $pagina_log ? $pagina_log->getEstatusPagina()->getId() : 0;
			$pagina['estatus_pagina'] = $pagina_log ? $pagina_log->getEstatusPagina()->getNombre() : '';
			$pagina['porcentaje_avance'] = $pagina_log ? round($pagina_log->getPorcentajeAvance()) : 0;

			if ($i != 1)
			{
				if ($grado != $pagina['grado'])
				{
					$grado = $pagina['grado'];
					$libros[$pagina['grado']] = array('grado_id' => $pagina['grado'],
													  'descripcion' => $pagina['descripcion_grado'],
													  'libros' => array(0 => $pagina));
				}
				else {
					array_push($libros[$pagina['grado']]['libros'], $pagina);
				}
			}
			else {
				$grado = $pagina['grado'];
				$libros[$pagina['grado']] = array('grado_id' => $pagina['grado'],
												  'descripcion' => $pagina['descripcion_grado'],
												  'libros' => array(0 => $pagina));
			}

		}

		return $libros;

	}

	// Retorna un arreglo de elementos para el menú de los temas
	public function menuTemas($indexedPages, $unidad_id, $usuario_id, $yml, $tema_id, $evaluacion)
	{

		$em = $this->em;
		$elementos = array();
		$i = 0;
		$activar_evaluacion = 1;
		
		foreach ($indexedPages[$unidad_id]['subpaginas'] as $tema)
		{

			$i++;

			// Interacción con la página
			$tema_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                       		 	 'pagina' => $tema['id']));
			
			$elementos[] = array('id' => $tema['id'],
								 'orden' => $tema['orden'],
								 'titulo' => ucfirst(mb_strtolower($tema['titulo'], 'UTF-8')),
								 'completado' => $tema_log ? $tema_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'] ? true : false: false,
								 'es_evaluacion' => false,
								 'activa' => $tema['id'] == $tema_id ? true : $i==1 && $tema_id==0 && $evaluacion==0 ? true : false,
								 'blocked' => false,
								 'locked' => false,
								 'unlocked' => false,
								 'forbidden' => false);

			if (!$tema_log)
			{
				$activar_evaluacion = 0;
			}
			else {
				if ($tema_log->getEstatusPagina()->getId() != $yml['parameters']['estatus_pagina']['completada'])
				{
					$activar_evaluacion = 0;
				}
			}

		}

		if ($indexedPages[$unidad_id]['tiene_evaluacion'])
		{

			// Interacción con la prueba
			$query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPruebaLog pl 
                                        WHERE pl.prueba = :prueba_id 
                                        AND pl.usuario = :usuario_id 
                                        ORDER BY pl.id DESC")
                        ->setParameters(array('prueba_id' => $indexedPages[$unidad_id]['prueba_id'],
                                              'usuario_id' => $usuario_id));
            $pls = $query->getResult();

            // Condición inicial
            $completado = false;
            $activa = $evaluacion ? true : false;
            $locked = true;
            $unlocked = false;
            $forbidden = false;
            if ($pls)
            {
                $intentos = count($pls);
                if ($pls[0]->getEstado() == $yml['parameters']['estado_prueba']['curso'])
                {
                	$intentos -= 1;
                }
                if ($intentos >= $pls[0]->getPrueba()->getMaxIntentos())
                {
                    $activar_evaluacion = 0;
                    $locked = false;
                    $forbidden = true;
                }
                elseif ($pls[0]->getEstado() == $yml['parameters']['estado_prueba']['aprobado'])
                {
                    $activar_evaluacion = 0;
                    $completado = true;
                    $locked = false;
                }
                else {
                	$locked = false;
                	$unlocked = true;
                }
            }
            else {
            	if ($activa)
            	{
            		$locked = false;
            		$unlocked = true;
            	}
            	else {
            		if ($activar_evaluacion)
            		{
            			$locked = false;
            			$unlocked = true;
            		}
            	}
            }

			$elementos[] = array('id' => $indexedPages[$unidad_id]['prueba_id'],
								 'titulo' => $this->translator->trans('Compruebo mis aprendizajes'),
								 'completado' => $completado,
								 'es_evaluacion' => true,
								 'activa' => $activa,
								 'blocked' => $activar_evaluacion ? false : true,
								 'locked' => $locked,
								 'unlocked' => $unlocked,
								 'forbidden' => $forbidden);

		}

		return $elementos;

	}

	function resourceScreenshots($subpaginas, $usuario_id, $estatus_completada)
	{

		$em = $this->em;
		$recursos = array();
        $recurso_log_anterior = 0;
        $status_css_anterior = '';
        foreach ($subpaginas as $r)
        {

            $recurso_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $r['id'],
                                                                                                    'usuario' => $usuario_id));

            $status_css = 'active';
            $button_text = '';

            if (!$recurso_log)
            {
                $button_text = $this->translator->trans('Iniciar');
                if ($recurso_log_anterior)
                {
                    if ($recurso_log_anterior->getEstatusPagina()->getId() != $estatus_completada)
                    {
                        $status_css = 'blocked';
                    }
                }
                else {
                    if ($status_css_anterior == 'active' || $status_css_anterior == 'blocked')
                    {
                        $status_css = 'blocked';
                    }
                }
                $recurso_log_anterior = 0;
            }
            else {
                if ($recurso_log->getEstatusPagina()->getId() == $estatus_completada)
                {
                    $status_css = 'hecho';
                    $button_text = $this->translator->trans('Hecho');
                }
                else {
                    $button_text = $this->translator->trans('Iniciar');
                }
                $recurso_log_anterior = $recurso_log;
            }

            $status_css_anterior = $status_css;

            $recursos[] = array('id' => $r['id'],
                                'orden' => $r['orden']<10 ? '0'.$r['orden'] : $r['orden'],
                                'foto' => $r['foto'],
                                'status_css' => $status_css,
                                'button_text' => $button_text);

        }

        return $recursos;

	}

	//requiere formato 2001-12-11 hora, retorna 'dia de mes de año'
    public function fechaNatural($fecha)
    {
        if($fecha!="")
        {
            $arreglo=explode(" ",$fecha);
            $arreglo=$arreglo[0];
            $arreglo=explode("-",$arreglo);
            return $arreglo[2]." de ".$this->meses[(int)$arreglo[1]]." de ".$arreglo[0];
        }else
        {
            return "";
        }
    }

    // Retorna el nombre del mes traducido. Recibe el valor del mes en entero sin el cero inicial
    public function mesTraducido($mes)
    {

    	$meses = array(1 => $this->translator->trans('Enero'),
    				   2 => $this->translator->trans('Febrero'),
    				   3 => $this->translator->trans('Marzo'),
    				   4 => $this->translator->trans('Abril'),
    				   5 => $this->translator->trans('Mayo'),
    				   6 => $this->translator->trans('Junio'),
    				   7 => $this->translator->trans('Julio'),
    				   8 => $this->translator->trans('Agosto'),
    				   9 => $this->translator->trans('Septiembre'),
    				   10 => $this->translator->trans('Octubre'),
    				   11 => $this->translator->trans('Noviembre'),
    				   12 => $this->translator->trans('Diciembre'));
        
        return $meses[$mes];
        
    }

}
