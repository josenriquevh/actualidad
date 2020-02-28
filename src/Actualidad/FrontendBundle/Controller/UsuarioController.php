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
use Actualidad\ComunBundle\Entity\AdminCorreo;
use Actualidad\ComunBundle\Entity\AdminUsuarioColegio;
use Actualidad\ComunBundle\Entity\AdminUsuarioSeccion;

class UsuarioController extends Controller
{
    public function registroAction(Request $request, $rol_id)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        // Por ahora solo el rol de alumno es permitido
        if ($rol_id != $yml['parameters']['rol']['alumno'])
        {
            return $this->redirectToRoute('_index');
        }

        if ($request->getMethod() == 'POST')
        {

            // Parámetros del request
            $pu_ids_str = $request->request->get('pu_ids');
            $grado_id = $request->request->get('grado_id');
            $login = $request->request->get('login');
            $correo = $request->request->get('correo');
            $clave = $request->request->get('clave');
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $provincia_id = $request->request->get('provincia_id');
            $ciudad_id = $request->request->get('ciudad_id');
            $colegio_id = $request->request->get('colegio_id');
            $seccion_id = $request->request->get('seccion_id');

            $grado = $em->getRepository('ActualidadComunBundle:AdminGrado')->find($grado_id);
            $rol = $em->getRepository('ActualidadComunBundle:AdminRol')->find($rol_id);

            if ($provincia_id)
            {
                $provincia = $em->getRepository('ActualidadComunBundle:AdminProvincia')->find($provincia_id);
            }

            if ($ciudad_id)
            {
                $ciudad = $em->getRepository('ActualidadComunBundle:AdminCiudad')->find($ciudad_id);
            }

            if ($colegio_id)
            {
                $colegio = $em->getRepository('ActualidadComunBundle:AdminColegio')->find($colegio_id);
            }

            if ($seccion_id)
            {
                $seccion = $em->getRepository('ActualidadComunBundle:AdminSeccion')->find($seccion_id);
            }

            // Almacenamiento en tablas
            $usuario = new AdminUsuario();
            $usuario->setLogin($login);
            $usuario->setClave($clave);
            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setCorreo($correo);
            $usuario->setActivo(true);
            $usuario->setCiudad($ciudad_id ? $ciudad : null);
            $usuario->setProvincia($provincia_id ? $provincia : null);
            $usuario->setGrado($grado);
            $usuario->setRol($rol);
            $usuario->setFechaCreacion(new \DateTime('now'));
            $usuario->setFechaModificacion(new \DateTime('now'));
            $em->persist($usuario);
            $em->flush();

            // Activación de códigos
            $pu_ids = explode("-", $pu_ids_str);
            foreach ($pu_ids as $pu_id)
            {
                $pagina_usuario = $em->getRepository('ActualidadComunBundle:EaPaginaUsuario')->find($pu_id);
                $pagina_usuario->setUsuario($usuario);
                $pagina_usuario->setActivo(true);
                $pagina_usuario->setToken(null);
                $em->persist($pagina_usuario);
                $em->flush();
            }

            if ($colegio_id)
            {
                $usuario_colegio = new AdminUsuarioColegio();
                $usuario_colegio->setUsuario($usuario);
                $usuario_colegio->setColegio($colegio);
                $em->persist($usuario_colegio);
                $em->flush();
            }

            if ($seccion_id)
            {
                $usuario_seccion = new AdminUsuarioSeccion();
                $usuario_seccion->setUsuario($usuario);
                $usuario_seccion->setSeccion($seccion);
                $em->persist($usuario_seccion);
                $em->flush();
            }
            $nombre = ucwords(mb_strtolower($usuario->getNombre(), 'UTF-8'));
		    $apellido = ucwords(mb_strtolower($usuario->getApellido(), 'UTF-8'));

            // Envío de correo de bienvenida
            $mensaje ='<p><span style="color:#808080">'. $this->get('translator')->trans('Tu registro en Actualidad Digital fue completado con éxito, tus datos de acceso son los siguientes').':</span></p>
                       <p><span style="color:#808080">'.$this->get('translator')->trans('Usuario').': %%usuario%% </span> </p> 
                       <p><span style="color:#808080">'.$this->get('translator')->trans('Contraseña').': %%clave%% </span></p>';
            $comodines = $yml['parameters']['comodines_correo'];
            $reemplazos = array($usuario->getLogin(), $usuario->getClave(), $usuario->getNombre(), $usuario->getApellido());
            $mensaje = str_replace($comodines, $reemplazos, $mensaje);
            $titulo = $this->get('translator')->trans('Bienvenido');
            $footer = $this->container->getParameter('folders')['uploads'].'footernewsletter.png';
            $logo = $this->container->getParameter('folders')['uploads'].'logo-actualidad-light.png';
            $base = $this->container->getParameter('url_plataforma');

            $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                       'datos' => array('nombre' => $nombre,
                                                        'apellido' => $apellido,
                                                        'mensaje' => $mensaje,
                                                        'footer' => $footer,
                                                        'logo' => $logo,
                                                        'titulo' => $titulo,
                                                        'url_plataforma' => $base),
                                       'asunto' => $this->get('translator')->trans('Bienvenido a Editorial Actualidad'),
                                       'remitente' => $this->container->getParameter('mailer_user_info'),
                                       'remitente_name' => $this->container->getParameter('mailer_user_info_name'),
                                       'destinatario' => $usuario->getCorreo(),
                                       'mailer' => 'info_mailer');
            $ok = $f->sendEmail($parametros_correo);

            if ($ok)
            {

                // Registro del correo recien enviado
                $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['registro_usuario']);
                $email = new AdminCorreo();
                $email->setTipoCorreo($tipo_correo);
                $email->setEntidadId($usuario->getId());
                $email->setUsuario($usuario);
                $email->setCorreo($usuario->getCorreo());
                $email->setFecha(new \DateTime('now'));
                $em->persist($email);
                $em->flush();

            }

            // Inicio de sesión
            $f->setSesionFront($usuario, $yml);

            // Ir a la pantalla de inicio
            return $this->redirectToRoute('_inicio', array('rol_id' => $rol_id));

        }

        // Provincias
        $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:AdminProvincia p 
                                    ORDER BY p.nombre ASC");
        $provincias = $query->getResult();

        // Grados
        $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g 
                                    ORDER BY g.nombre ASC");
        $grados = $query->getResult();

        // Tipo de libro
        switch ($rol_id)
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

        // Desactivar códigos agregados sin concluir el registro
        $token = $this->get('security.token_storage')->getToken()->getSecret();
        $query = $em->createQuery("SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                    WHERE pu.activo = :activo 
                                    AND pu.usuario IS NULL 
                                    AND (pu.fechaActivacion < :hoy OR pu.token = :token)")
                    ->setParameters(array('activo' => true,
                                          'hoy' => date('Y-m-d'),
                                          'token' => $token));
        $codigos_activos = $query->getResult();

        foreach ($codigos_activos as $codigo_activo)
        {
            $codigo_activo->setActivo(false);
            $codigo_activo->setFechaActivacion(null);
            $codigo_activo->setToken(null);
            $em->persist($codigo_activo);
            $em->flush();
        }

        return $this->render('ActualidadFrontendBundle:Usuario:registro.html.twig', array('provincias' => $provincias,
                                                                                          'grados' => $grados,
                                                                                          'rol_id' => $rol_id,
                                                                                          'tipo_pagina_id' => $tipo_pagina_id));
        
    }

    public function exitRegistroAction($rol_id)
    {

        $em = $this->getDoctrine()->getManager();
        
        // Desactivar códigos agregados sin concluir el registro
        $token = $this->get('security.token_storage')->getToken()->getSecret();
        $query = $em->createQuery("SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                    WHERE pu.activo = :activo 
                                    AND pu.usuario IS NULL 
                                    AND (pu.fechaActivacion < :hoy OR pu.token = :token)")
                    ->setParameters(array('activo' => true,
                                          'hoy' => date('Y-m-d'),
                                          'token' => $token));
        $codigos_activos = $query->getResult();

        foreach ($codigos_activos as $codigo_activo)
        {
            $codigo_activo->setActivo(false);
            $codigo_activo->setFechaActivacion(null);
            $codigo_activo->setToken(null);
            $em->persist($codigo_activo);
            $em->flush();
        }

        // Ir a la pantalla de login
        return $this->redirectToRoute('_login', array('rol_id' => $rol_id));
        
    }

    public function ajaxAddCodeUserAction(Request $request)
    {
        
        try {

            $session = new Session();
            $em = $this->getDoctrine()->getManager();
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $f = $this->get('funciones');
            $ok = 1;
            $msg = '';
            $card = '';

            $grado_id = $request->request->get('grado_id');
            $codigo = trim(strtoupper($request->request->get('codigo')));
            $tipo_pagina_id = $request->request->get('tipo_pagina_id');
            $pu_ids_str = $request->request->get('pu_ids');

            if ($grado_id)
            {
                $query = $em->createQuery("SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                            JOIN pu.pagina p 
                                            WHERE pu.codigo = :codigo 
                                            AND p.grado = :grado_id 
                                            AND p.tipoPagina = :tipo_pagina_id")
                            ->setParameters(array('codigo' => $codigo,
                                                  'grado_id' => $grado_id,
                                                  'tipo_pagina_id' => $tipo_pagina_id));
            }
            else {
                $query = $em->createQuery("SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                            JOIN pu.pagina p 
                                            WHERE pu.codigo = :codigo 
                                            AND p.tipoPagina = :tipo_pagina_id")
                            ->setParameters(array('codigo' => $codigo,
                                                  'tipo_pagina_id' => $tipo_pagina_id));
            }
            $paginas_usuarios = $query->getResult();

            if (!$paginas_usuarios)
            {
                $ok = 0;
                $msg = $this->get('translator')->trans('Código inexistente');
            }
            else {

                $pagina_usuario = $paginas_usuarios[0];

                if ($pagina_usuario->getActivo() || $pagina_usuario->getUsuario())
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
                        else {

                            // No permitir agregar códigos de una materia ya agregada
                            if ($pu_ids_str != '')
                            {
                                $pu_ids = explode("-", $pu_ids_str);
                                $query = $em->createQuery("SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                                            WHERE pu.id IN (:pu_ids)")
                                            ->setParameter('pu_ids', $pu_ids);
                                $pus = $query->getResult();
                                foreach ($pus as $pu)
                                {
                                    if ($pagina_usuario->getPagina()->getId() == $pu->getPagina()->getId())
                                    {
                                        $ok = 0;
                                        $msg = $this->get('translator')->trans('Código de una materia ya agregada');
                                        break;
                                    }
                                }
                            }
                            else {
                                $pu_ids = array();
                            }

                        }

                    }
                }

                if ($ok)
                {

                    // Activación momentánea del libro (sin usuario)
                    $token = $this->get('security.token_storage')->getToken()->getSecret();
                    $pagina_usuario->setActivo(true);
                    $pagina_usuario->setFechaActivacion(new \DateTime('now'));
                    $pagina_usuario->setToken($token);
                    $em->persist($pagina_usuario);
                    $em->flush();

                    // Tapa del libro
                    $card = $this->renderView('ActualidadFrontendBundle:Usuario:card.html.twig', array('pagina_usuario' => $pagina_usuario));

                    $grado_id = $pagina_usuario->getPagina()->getGrado()->getId();

                    $pu_ids[] = $pagina_usuario->getId();
                    $pu_ids_str = implode("-", $pu_ids);

                }

            }
            
            $return = array('ok' => $ok,
                            'error' => $msg,
                            'card' => $card,
                            'grado_id' => $grado_id,
                            'pu_ids' => $pu_ids_str);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'error' => $ex->getMessage());
            return new JsonResponse($return);
        }

    }

    public function ajaxGetSelectSeccionAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $colegio_id = $request->query->get('colegio_id');
        $grado_id = $request->query->get('grado_id');
        $entity = $request->query->get('entity');
        $orderBy = $request->query->get('orderBy');
        
        $query = $em->createQuery('SELECT e FROM ActualidadComunBundle:'.$entity.' e 
                                    WHERE e.colegio = :colegio_id 
                                    AND e.grado = :grado_id 
                                    ORDER BY e.'.$orderBy.' ASC')
                    ->setParameters(array('colegio_id' => $colegio_id,
                                          'grado_id' => $grado_id));
        $objects = $query->getResult();

        if (count($objects))
        {
            $options = '<option value=""></option>';
            foreach ($objects as $object)
            {
                $options .= '<option value="'.$object->getId().'">'.$object->getNombre().'</option>';
            }
        }
        else {
            $options = '<option value="">'.$this->get('translator')->trans('No existen secciones').'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
