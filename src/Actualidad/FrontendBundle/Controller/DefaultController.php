<?php

namespace Actualidad\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\JsonResponse;
use Actualidad\ComunBundle\Entity\AdminUsuario;
use Actualidad\ComunBundle\Entity\AdminUsuarioColegio;
use Actualidad\ComunBundle\Entity\AdminUsuarioSeccion;
use Actualidad\ComunBundle\Entity\AdminCorreo;
use Spipu\Html2Pdf\Html2Pdf;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $f = $this->get('funciones');
        
        $code = $f->getLocaleCode();
        if (!($code == 'VE' || $code == 'DO' || $code == 'AR'))
        {
            return $this->redirectToRoute('_authException', array('tipo' => 'domain'));
        }

        return $this->render('ActualidadFrontendBundle:Default:index.html.twig');
        
    }

    public function olvidoPassAction()
    {

        return $this->render('ActualidadFrontendBundle:Default:olvido_pass.html.twig');
        
    }

    public function ajaxValidarAction(Request $request)
    {
        try{
            $em = $this->getDoctrine()->getManager();

            $login = $request->query->get('login');
            
            $codLibro = $request->query->get('codLibro');

            $validar = "";

            $query = $em->createQuery('SELECT u FROM ActualidadComunBundle:AdminUsuario u
                                        WHERE u.login = :login_usuario')
                        ->setParameter('login_usuario', $login);
            $usuarios = $query->getResult();

            $usuario_id = '';

            if($usuarios){
                foreach( $usuarios as $usuario)
                {
                   // return new response ($usuario->getId().' hola '.$codLibro );
                    $query = $em->createQuery('SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                                WHERE pu.usuario = :usuario_id
                                                AND pu.codigo = :codigo
                                                AND pu.activo = :activo')
                                ->setParameters(array('usuario_id' => $usuario->getId(),
                                                    'codigo' => $codLibro,
                                                    'activo' => TRUE));
                    $usuarios_libros = $query->getResult();

                    if($usuarios_libros)
                    {
                        foreach($usuarios_libros as $usuario_libro)
                        {
                            $validar = 1;
                            $usuario_id = $usuario_libro->getUsuario()->getId();
                            
                        }
                       
                    }
                }
            }
            
            $ok = 1;
            $return = array('ok' => 1,
                            'validar' => $validar,
                            'usuario_id' => $usuario_id);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
        

    }

    public function ajaxContrasenaAction(Request $request)
    {
        try{

            $em = $this->getDoctrine()->getManager();
            $f = $this->get('funciones');

            $contrasena1 = $request->query->get('contrasena1');

            $usuario_id = $request->query->get('usuario_id');

            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml')); 
            $yml2 = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml')); 
            $base = $yml['parameters']['url_plataforma'];
            $footer = $yml['parameters']['folders']['uploads'].'footernewsletter.png';
            $logo = $yml['parameters']['folders']['uploads'].'logo-actualidad-light.png';
            $titulo = 'Cambio de contraseña';
            $exito = "";

            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);

            if ($usuario)
            {
                
                $usuario->setClave($contrasena1);
                $em->persist($usuario);
                $em->flush();

                $mensaje = '<p><span style="color:#808080">'.$this->get('translator')->trans('Tu contraseña ha sido actualizada satisfactoriamente. Tus datos de acceso son los siguientes').': </span></p>
                            <p><span style="color:#808080">'.$this->get('translator')->trans('Usuario').': %%usuario%% </span> </p>
                            <p><span style="color:#808080">'.$this->get('translator')->trans('Contraseña').': %%clave%% </span></p>';
              
              
                
                // Sustitución de variables en el texto
                $comodines = $yml2['parameters']['comodines_correo'];
                $reemplazos = array($usuario->getLogin(), $usuario->getClave(), $usuario->getNombre(), $usuario->getApellido());
                $mensaje = str_replace($comodines, $reemplazos, $mensaje);

                $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                           'datos' => array('nombre' => $usuario->getNombre(),
                                                            'apellido' => $usuario->getApellido(),
                                                            'mensaje' => $mensaje,
                                                            'footer' => $footer,
                                                            'logo' => $logo,
                                                            'url_plataforma' => $base,
                                                            'titulo' => $titulo),
                                           'asunto' => $this->get('translator')->trans('Cambio de contraseña'),
                                           'remitente' => $yml['parameters']['mailer_user_info'],
                                           'remitente_name' =>$yml['parameters']['mailer_user_info_name'],
                                           'destinatario' => $usuario->getCorreo(),
                                           'mailer' => 'info_mailer');
                $ok = $f->sendEmail($parametros_correo);
                
                if ($ok)
                {

                    // Registro del correo recien enviado
                    $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml2['parameters']['tipo_correo']['cambio_clave']);
                    $email = new AdminCorreo();
                    $email->setTipoCorreo($tipo_correo);
                    $email->setEntidadId($usuario->getId());
                    $email->setUsuario($usuario);
                    $email->setCorreo($usuario->getCorreo());
                    $email->setFecha(new \DateTime('now'));
                    $em->persist($email);
                    $em->flush();

                    $exito = 1;

                }

            }
            
            $ok = 1;
            $return = array('ok' => 1,
                            'exito' => $exito);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
            
        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
        
    }

    public function loginAction(Request $request, $rol_id)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $verificacion = '';
        $em = $this->getDoctrine()->getManager();

        // Solo roles permitidos
        if (!($rol_id == $yml['parameters']['rol']['alumno'] || $rol_id == $yml['parameters']['rol']['profesor'] || $rol_id == $yml['parameters']['rol']['revisor']))
        {
            return $this->redirectToRoute('_index');
        }

        //validamos que exista una cookie
        if($_COOKIE && isset($_COOKIE["id_usuario_front"]))
        {
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario_front"],
                                                                                                 'cookies' => $_COOKIE["cookie_front"] ) );            
            if ($usuario)
            {
                $recordar_datos = 0;
                $login = $usuario->getLogin();
                $clave = $usuario->getClave(); 
                $verificacion = 1;
            }
            else {
                $error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
            }
        }
        else {
            if ($request->getMethod() == 'POST')
            {
                $recordar_datos = $request->request->get('recordar');
                $login = $request->request->get('username');
                $clave = $request->request->get('passLogin');
                $verificacion = 1;
            }
        }

        if ($verificacion)
        {
            $iniciarSesion = $f->iniciarSesion(array('recordar_datos' => $recordar_datos,
                                                     'login' => $login,
                                                     'clave' => $clave,
                                                     'rol_id' => $rol_id,
                                                     'yml' => $yml));

            if($iniciarSesion['exito'] == true)
            {
                return $this->redirectToRoute('_inicio', array('rol_id' => $rol_id));
            }
            else {
                $response = $this->render('ActualidadFrontendBundle:Default:login.html.twig', array('error' => $iniciarSesion['error'],
                                                                                                    'rol_id' => $rol_id)); 
                return $response;
            }                    
        }
        else {
            $response = $this->render('ActualidadFrontendBundle:Default:login.html.twig', array('error' => $error,
                                                                                                'rol_id' => $rol_id)); 
            return $response;
        }
            
    }

    public function inicioAction($rol_id)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $session = new Session();

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_login', array('rol_id' => $rol_id));
        }
        $f->setRequest($session->get('sesion_id'));

        // Estructura separadas por grado
        $libros = $f->librosGrados($session->get('paginas'), $session->get('usuario')['id']);

        $recientes = array();
        $biblioteca = array();
        $filtro = array();
        $i = 0;

        // Unidades recientes
        $query = $em->getConnection()->prepare('SELECT
                                                fnunidades_recientes(:re, :pusuario_id, :phoy) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pusuario_id', $session->get('usuario')['id'], \PDO::PARAM_INT);
        $query->bindValue(':phoy', date('Y-m-d'), \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $unidad)
        {

            if (($session->get('usuario')['rol_id'] == $yml['parameters']['rol']['alumno'] && $session->get('usuario')['grado_id'] == $unidad['grado_id']) || $session->get('usuario')['rol_id'] == $yml['parameters']['rol']['profesor'])
            {
                $i++;
                $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($unidad['libro_id']);
                $unidad['css'] = $libro->getEstilo()->getNombre();
                $unidad['materia'] = $libro->getTitulo();
                $unidad['total_unidades'] = count($session->get('paginas')[$unidad['libro_id']]['subpaginas']);
                $recientes[] = $unidad;
            }

            if ($i == 4)
            {
                break;
            }

        }

        // Biblioteca
        foreach ($libros as $grado_id => $libro)
        {

            if (($session->get('usuario')['rol_id'] == $yml['parameters']['rol']['alumno'] && $session->get('usuario')['grado_id'] == $grado_id) || $session->get('usuario')['rol_id'] == $yml['parameters']['rol']['profesor'])
            {
                $grade = $grado_id < 10 ? '0'.$grado_id : $grado_id;
                $biblioteca[$grade] = $libro;
                $filtro[$grado_id] = $libro['descripcion'];
            }

        }

        ksort($filtro);

        //return new Response(var_dump($recientes));

        
        return $this->render('ActualidadFrontendBundle:Default:inicio.html.twig', array('recientes' => $recientes,
                                                                                        'biblioteca' => $biblioteca,
                                                                                        'filtro' => $filtro));
        
    }

    public function ajaxAddCodeAction(Request $request)
    {
        
        try {

            $session = new Session();
            $em = $this->getDoctrine()->getManager();
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $f = $this->get('funciones');
            $ok = 1;
            $msg = '';

            $pagina_id = $request->request->get('pagina_id');
            $codigo = trim(strtoupper($request->request->get('codigo')));

            $paginas = array();
            
            $pagina_usuario = $em->getRepository('ActualidadComunBundle:EaPaginaUsuario')->findOneBy(array('pagina' => $pagina_id,
                                                                                                           'codigo' => $codigo));

            if (!$pagina_usuario)
            {
                $ok = 0;
                $msg = $this->get('translator')->trans('Código inexistente');
            }
            else {
                if ($pagina_usuario->getActivo())
                {
                    $ok = 0;
                    $msg = $this->get('translator')->trans('Código ya utilizado');
                }
                else {
                    if ($pagina_usuario->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d'))
                    {
                        $ok = 0;
                        $msg = $this->get('translator')->trans('Código vencido');
                    }
                    else {

                        if ($pagina_usuario->getFechaInicio()->format('Y-m-d') > date('Y-m-d'))
                        {
                            $ok = 0;
                            $msg = $this->get('translator')->trans('Este código puedes activarlo a partir del'.' '.$pagina_usuario->getFechaInicio()->format('d/m/Y'));
                        }

                        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

                        switch ($usuario->getRol()->getId())
                        {
                            case $yml['parameters']['rol']['alumno']:
                                if ($pagina_usuario->getPagina()->getTipoPagina()->getId() != $yml['parameters']['tipo_pagina']['libro_alumnos'])
                                {
                                    $ok = 0;
                                    $msg = $this->get('translator')->trans('Código no válido para alumnos');
                                    break;
                                }
                                else {
                                    break;
                                }
                            case $yml['parameters']['rol']['profesor']:
                                if ($pagina_usuario->getPagina()->getTipoPagina()->getId() != $yml['parameters']['tipo_pagina']['libro_profesores'])
                                {
                                    $ok = 0;
                                    $msg = $this->get('translator')->trans('Código no válido para docentes');
                                    break;
                                }
                                else {
                                    break;
                                }
                            default:
                                $ok = 0;
                                $msg = $this->get('translator')->trans('Código no válido para tu rol');
                                break;
                        }

                        if ($ok)
                        {
                            $pagina_usuario->setUsuario($usuario);
                            $pagina_usuario->setActivo(true);
                            $pagina_usuario->setFechaActivacion(new \DateTime('now'));
                            $em->persist($pagina_usuario);
                            $em->flush();

                            // Actualizar la variable de sesión paginas
                            $hoy = date('Y-m-d');
                            $razon_vigencia = '';
                            if ($pagina_usuario->getFechaVencimiento()->format('Y-m-d') < $hoy)
                            {
                                $razon_vigencia = $this->get('translator')->trans('Expiró el').' '.$f->formatDateEs($pagina_usuario->getFechaVencimiento()->format('Y-m-d'));
                            }
                            elseif ($pagina_usuario->getFechaInicio()->format('Y-m-d') > $hoy) {
                                $razon_vigencia = $this->get('translator')->trans('Inicia el').' '.$f->formatDateEs($pagina_usuario->getFechaInicio()->format('Y-m-d'));
                            }
                            $paginas = $session->get('paginas');
                            $paginas[$pagina_id]['codigo_activo'] = true;
                            $paginas[$pagina_id]['codigo_vigente'] = ($pagina_usuario->getFechaInicio()->format('Y-m-d') <= $hoy && $pagina_usuario->getFechaVencimiento()->format('Y-m-d') >= $hoy) ? true : false;
                            $paginas[$pagina_id]['razon_vigencia'] = $razon_vigencia;
                            $paginas[$pagina_id]['codigo_inicio'] = $pagina_usuario->getFechaInicio()->format('d/m/Y');
                            $paginas[$pagina_id]['codigo_vencimiento'] = $pagina_usuario->getFechaVencimiento()->format('d/m/Y');
                            $paginas[$pagina_id]['codigo_activacion'] = $pagina_usuario->getFechaActivacion()->format('d/m/Y');
                            $session = new Session();
                            $session->set('paginas', $paginas);

                        }

                    }
                }
            }
            
            $return = array('ok' => $ok,
                            'msg' => $msg,
                            'paginas' => $paginas);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }

    }

    public function actualizacionUsuarioAction(Request $request, $rol_id)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $session = new Session();

        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authException', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        if ($request->getMethod() == 'POST')
        {
            $correo = trim($request->request->get('correo'));
            $clave = trim($request->request->get('passLoginA'));
    
            $nombre = trim($request->request->get('nombre'));
            $apellido = trim($request->request->get('apellido'));

            $foto = trim($request->request->get('foto'));

            $provincia_id = $request->request->get('provincia');
            
            if($provincia_id)
            {
                $provincia = $em->getRepository('ActualidadComunBundle:AdminProvincia')->find($provincia_id);
            }else{
                $provincia = null;
            }

            $ciudad_id = $request->request->get('ciudad');
            
            if($ciudad_id)
            {
                $ciudad = $em->getRepository('ActualidadComunBundle:AdminCiudad')->find($ciudad_id);
            }else{
                $ciudad = null;
            }
            

            $colegio_id = $request->request->get('colegio');
            if($colegio_id)
            {
                $colegio = $em->getRepository('ActualidadComunBundle:AdminColegio')->find($colegio_id);
                
            }else{
                $colegio = null;
            }

            $usuario_colegio_id = $request->request->get('usuario_colegio_id');
            if($usuario_colegio_id)
            {
                $usuario_colegio = $em->getRepository('ActualidadComunBundle:AdminUsuarioColegio')->find($usuario_colegio_id);

            }else{
                $usuario_colegio = new AdminUsuarioColegio();
            }
            

            

            $grado_id = $request->request->get('grado_id');
            
            $grado = $em->getRepository('ActualidadComunBundle:AdminGrado')->find($grado_id);

            $seccion_id = $request->request->get('seccion');
            if($seccion_id)
            {                
                $seccion = $em->getRepository('ActualidadComunBundle:AdminSeccion')->find($seccion_id);

            }else{
                $seccion = null;
            }

            $usuario_seccion_id = $request->request->get('usuario_seccion_id');
            if($usuario_seccion_id)
            {
                $usuario_seccion = $em->getRepository('ActualidadComunBundle:AdminUsuarioSeccion')->find($usuario_seccion_id);
            }else{
                $usuario_seccion = new AdminUsuarioSeccion();
            }
            
            

            $usuario->setCorreo($correo);
            if($clave)
            {   
               
                $usuario->setClave($clave);
            }
            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setFoto($foto);
            $usuario->setProvincia($provincia);
            $usuario->setCiudad($ciudad);
            $usuario->setGrado($grado);
            $em->persist($usuario);
            $em->flush();
            
            if($colegio_id)
            {
                $usuario_colegio->setColegio($colegio);
                $usuario_colegio->setUsuario($usuario);
                $em->persist($usuario_colegio);
                $em->flush();
            }
            

            if($seccion_id)
            {
                $usuario_seccion->setSeccion($seccion);
                $usuario_seccion->setUsuario($usuario);
                $em->persist($usuario_seccion);
                $em->flush();
            }
            
            // Inicio de sesión
            $f->setSesionFront($usuario, $yml);

            // Ir a la pantalla de inicio
            return $this->redirectToRoute('_inicio', array('rol_id' => $rol_id));

        }

        $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:AdminProvincia p 
                                    ORDER BY p.nombre ASC");
        $provincias_db = $query->getResult();
        $provincias = array();
        $ciudades = array();
        $colegios = array();
        $usuario_colegio_id = null;
        $secciones = array();
        $usuario_seccion_id = null;
        
        foreach( $provincias_db as $provincia)
        {   
            $selected_provincia = '';
            if ($usuario->getProvincia() != null)
            {
                if($provincia->getId() == $usuario->getProvincia()->getId())
                {
                    $selected_provincia = 'selected';
                    $query = $em->createQuery("SELECT cu FROM ActualidadComunBundle:AdminCiudad cu 
                                            WHERE cu.provincia = :provincia_id
                                            ORDER BY cu.nombre ASC")
                                ->setParameter('provincia_id',$usuario->getProvincia()->getId());
                    $ciudad_db = $query->getResult();
                    $ciudades = array();
                    
                    foreach( $ciudad_db as $ciudad)
                    {   
                        $selected_ciudad = '';
                        if($usuario->getCiudad() != null )
                        {
                            if($ciudad->getId() == $usuario->getCiudad()->getId())
                            {
                                $selected_ciudad = 'selected';
                                $query = $em->createQuery("SELECT uc FROM ActualidadComunBundle:AdminUsuarioColegio uc 
                                                            WHERE uc.usuario = :usuario_id
                                                            ORDER BY uc.id ASC")
                                            ->setParameter('usuario_id',$usuario->getId());
                                $usuario_colegio_db = $query->getResult();

                                $query = $em->createQuery("SELECT co FROM ActualidadComunBundle:AdminColegio co 
                                                        WHERE co.ciudad = :ciudad_id
                                                            ORDER BY co.nombre ASC")
                                            ->setParameter('ciudad_id',$usuario->getCiudad()->getId());
                                $colegios_db = $query->getResult();
                                $colegios = array();
                                foreach( $colegios_db as $colegio)
                                {   
                                    $selected_colegio = '';
                                    foreach($usuario_colegio_db as $usuario_colegio )
                                    {
                                        if($usuario_colegio->getColegio() != null)
                                        {
                                            if($colegio->getId() == $usuario_colegio->getColegio()->getId())
                                            {
                                                $selected_colegio = 'selected';
                                                $usuario_colegio_id = $usuario_colegio->getId();

                                                $query = $em->createQuery("SELECT us FROM ActualidadComunBundle:AdminUsuarioSeccion us 
                                                                            WHERE us.usuario = :usuario_id
                                                                            ORDER BY us.id ASC")
                                                            ->setParameter('usuario_id',$usuario->getId());
                                                $usuario_seccion_db = $query->getResult();

                                                $query = $em->createQuery("SELECT s FROM ActualidadComunBundle:AdminSeccion s 
                                                                        WHERE s.colegio = :colegio_id
                                                                        ORDER BY s.nombre ASC")
                                                            ->setParameter('colegio_id', $colegio->getId());
                                                $seccion_db = $query->getResult();

                                                $secciones = array();
                                                $usuario_seccion_id ='';
                                                foreach( $seccion_db as $seccion)
                                                {    
                                                    $selected_seccion = '';
                                                    foreach($usuario_seccion_db as $usuario_seccion )
                                                    {
                                                        
                                                        if($seccion->getId() == $usuario_seccion->getSeccion()->getId())
                                                        {
                                                            $selected_seccion = 'selected';
                                                            $usuario_seccion_id = $usuario_seccion->getId();
                                                        }
                                                    }
                                                    $secciones[] = array('id' => $seccion->getId(),
                                                                        'nombre' => $seccion->getNombre(),
                                                                        'selected' => $selected_seccion);
                                                }
                                            }
                                        }                                    
                                    }
                                    $colegios[] = array('id' => $colegio->getId(),
                                                        'nombre' => $colegio->getNombre(),
                                                        'selected' => $selected_colegio);
                                }
                            }
                        }
                        
                        $ciudades[] = array('id' => $ciudad->getId(),
                                            'nombre' => $ciudad->getNombre(),
                                            'selected' => $selected_ciudad);
                    }

                }
            }
            $provincias[] = array('id' => $provincia->getId(),
                                  'nombre' => $provincia->getNombre(),
                                  'selected' => $selected_provincia);
        }

        $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g 
                                    ORDER BY g.nombre ASC");
        $grados_db = $query->getResult();
        $grados = array();
        
        foreach( $grados_db as $grado)
        {   
            $selected = '';
            if($grado->getId() == $usuario->getGrado()->getId())
            {
                $selected = 'selected';
            }
            $grados[] = array('id' => $grado->getId(),
                              'nombre' => $grado->getNombre(),
                              'selected' => $selected);
        }

        $query = $em->createQuery("SELECT pa FROM ActualidadComunBundle:EaPaginaUsuario pa
                                   WHERE pa.usuario = :usuario_id 
                                   ORDER BY pa.id ASC")
                    ->setParameter('usuario_id' , $session->get('usuario')['id'] );
        $usuario_libros = $query->getResult(); 
       
        $libros = array();
        foreach( $usuario_libros as $usuario_libro)
        {
            $estatusPagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($yml['parameters']['estatus_pagina']['completada']);
           
            $query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPaginaLog pl
                                       WHERE pl.usuario = :usuario_id 
                                       AND pl.pagina = :pagina_id
                                       AND pl.estatusPagina = :estatusPagina_id 
                                       ORDER BY pl.id ASC")
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                              'pagina_id' => $usuario_libro->getPagina()->getId(),
                                              'estatusPagina_id' => $estatusPagina));
            $libro_db = $query->getResult();
            foreach($libro_db as $libro)
            {
                $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:EaCertificado c
                                           WHERE c.entidadId = :pagina_id 
                                           ORDER BY c.id ASC")
                            ->setParameter('pagina_id' , $libro->getPagina()->getId());
                $certificados = $query->getResult();
                
                if($certificados)
                {
                    foreach($certificados as $certificado)
                    {
                        $libros[] = array('portada' => $uploads = $this->container->getParameter('folders')['uploads'].$libro->getPagina()->getFoto(),
                                        'certificado_id' => $certificado->getId());
                        
                    }
                }
            }
        }

        return $this->render('ActualidadFrontendBundle:Usuario:actualizacionUsuario.html.twig', array('login' => $session->get('usuario')['login'],
                                                                                                      'foto' => $usuario->getFoto(),
                                                                                                      'correo' => $session->get('usuario')['correo'],
                                                                                                      'nombre' => $session->get('usuario')['nombre'],
                                                                                                      'apellido' => $session->get('usuario')['apellido'],
                                                                                                      'provincias' => $provincias,
                                                                                                      'ciudades' => $ciudades,
                                                                                                      'grados' => $grados,
                                                                                                      'colegios' => $colegios,
                                                                                                      'usuario_colegio_id' => $usuario_colegio_id,
                                                                                                      'secciones' => $secciones,
                                                                                                      'usuario_seccion_id' => $usuario_seccion_id,
                                                                                                      'libros' => $libros));
        
    }

    public function certificadoPdfAction($certificado_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authException', array('tipo' => 'sesion'));
        }
      
        
        $f->setRequest($session->get('sesion_id'));
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();

        $certificado = $em->getRepository('ActualidadComunBundle:EaCertificado')->find($certificado_id);

        if( $certificado->getTipoCertificado()->getId() == $yml['parameters']['tipo_certificado']['libro'] )
        {
            $file = $this->container->getParameter('folders')['dir_uploads'].$certificado->getImagen();
            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($certificado->getEntidadId());

            $comodines = array('%%libro%%', '%%grado%%');
            $reemplazos = array($libro->getTitulo(), $certificado->getGrado()->getNombre().' '.$certificado->getGrado()->getDescripcion());
            $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());
            $frase = null;
            $libro_certificado = null;
            $frase2 = null;
            $grado = null;
            $pos = strpos($descripcion, '__');
            if($pos === false)
            {
                $frase = $descripcion;
                
            }else{
                list($frase, $libro_certificado, $frase2, $grado) = explode("__", $descripcion);
            }
            $nombre = $libro->getTitulo();
            $nombre_libro = explode(" ", $nombre);
            $siglas = null;
            foreach( $nombre_libro as $nombre){
                $siglas = $siglas.$nombre[0];
            }
            $siglas = $siglas.'_'.$certificado->getGrado()->getId();
            //return new response($siglas);

            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(10, 35, 0, 0));
            $certificado_pdf->writeHTML('
                                        <page title="Certificado" pageset="new" backimg="'.$file.'" backimgw="90%" backimgx="center"> 
                                            <div style="position: relative; top: 10%; font-size:22px;text-align:center; color:#9B9B9B; font-family:roboto; ">'.$certificado->getEncabezado().'</div>
                                            <div style="position: relative; top: 15%;text-align:center; font-size:40px; text-transform:uppercase; color:#00AEEF; font-family:roboto;">'.$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'].'</div>
                                            <div style="position: relative; top: 15%; text-align:center; font-size:24px; color:#9B9B9B; width: 55%; margin-left: 22%; font-family:roboto;">'.$frase.'</div> <span style="position: relative; text-align:center; font-size:24px; color:#00AEEF; margin-left: 30%;">'.$libro_certificado.'</span> <span style="position: relative; text-align:center; font-size:24px; color:#9B9B9B;">'.$frase2.'</span> <span style="position: relative; text-align:center; font-size:24px; color:#00AEEF;"> '.$grado.'</span>
                                        </page>');


            $certificado_pdf->output('certificado'.$siglas.'.pdf');

        }

    }

    public function ajaxSeccionAction(Request $request)
    {
        try{
            $em = $this->getDoctrine()->getManager();

            $colegio_id = $request->query->get('colegio_id');
            
            $grado_id = $request->query->get('grado_id');

            $query = $em->createQuery('SELECT s FROM ActualidadComunBundle:AdminSeccion s 
                                        WHERE s.colegio = :colegio_id
                                        AND s.grado = :grado_id
                                        ORDER BY s.nombre ASC')
                        ->setParameters(array('colegio_id' => $colegio_id,
                                              'grado_id' => $grado_id));
            $secciones = $query->getResult();
            
            if ($secciones)
            {
                $options = '<option value=""></option>';
                foreach ($secciones as $seccion)
                {
                    $options .= '<option value="'.$seccion->getId().'">'.$seccion->getNombre().'</option>';
                }
            }
            else
            {
                $options = '<option value="">'.$this->get('translator')->trans('No existen registros de secciones para el colegio seleccionado').'</option>';
            }
            
            $ok = 1;
            $return = array('ok' => 1,
                            'options' => $options);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
        

    }

    public function ajaxNotiAction(Request $request)
    {
        try{

            $session = new Session();
            $em = $this->getDoctrine()->getManager();
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

            $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                    WHERE a.usuario = :usuario_id 
                                    AND a.fechaCreacion <= :hoy 
                                    AND a.leido = :leido
                                    ORDER BY a.fechaCreacion DESC')
                        ->setMaxResults(5)
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                            'hoy' => date('Y-m-d H:i:s'),
                                            'leido' => FALSE));
            $notificaciones = $query->getResult();

            $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                    WHERE a.usuario = :usuario_id 
                                    AND a.fechaCreacion <= :hoy 
                                    AND a.leido = :leido
                                    ORDER BY a.fechaCreacion DESC')
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                            'hoy' => date('Y-m-d H:i:s'),
                                            'leido' => FALSE));
            $notificaciones_total = $query->getResult();

            $sonar = 0;
            $html = '';
            
            $result = count($notificaciones);
            $notificaciones_totales = count($notificaciones_total);

            if($notificaciones_totales > 99)
            {
                $sonar = '+99';
            }else{
                $sonar = $notificaciones_totales;
            }

            if($result == 5)
            {
                foreach($notificaciones as $notificacion )
                {
                    $fecha = $notificacion->getFechaCreacion();
                    $fecha_valida = $fecha->format('d M');
                    
                    $html .= '<a class="dropdown-item " >
                                <div class="noti-title no-read">
                                '. $notificacion->getDescripcion() .'   
                                </div>
                                <span>'. $fecha_valida .'</span>
                            </a>';
                    
                }
            }else{

                foreach($notificaciones as $notificacion )
                {
                    $fecha = $notificacion->getFechaCreacion();
                    $fecha_valida = $fecha->format('d M');
                    
                    $html .= '<a class="dropdown-item " href="#">
                                <div class="noti-title no-read">
                                '. $notificacion->getDescripcion() .'   
                                </div>
                                <span>'. $fecha_valida .'</span>
                            </a>';
                    
                }

                $maxResults = 5 - $result;

                $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                            WHERE a.usuario = :usuario_id 
                                            AND a.fechaCreacion <= :hoy 
                                            AND a.leido = :leido
                                            ORDER BY a.fechaCreacion DESC')
                            ->setMaxResults($maxResults)
                            ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                    'hoy' => date('Y-m-d H:i:s'),
                                                    'leido' => TRUE));
                $notificaciones = $query->getResult();

                foreach($notificaciones as $notificacion )
                {
                    $fecha = $notificacion->getFechaCreacion();
                    $fecha_valida = $fecha->format('d M');
                    
                    $html .= '<a class="dropdown-item " href="#">
                                <div class="noti-title ">
                                '. $notificacion->getDescripcion() .'   
                                </div>
                                <span>'. $fecha_valida .'</span>
                            </a>';
                    
                }
                
            }
            
            $html .= '<div class="dropdown-divider"></div>
                    <a class="dropdown-item justify-content-center" href="'.$this->generateUrl('_notificacionesFront').'">
                        <span class="fs-16">
                            Ver todas
                        </span>
                    </a>';


            $return = json_encode(array('ok' => 1,
                                        'html' => $html,
                                        'sonar' => $sonar));

            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }

    }
    
}
