<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminNotificacion;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Actualidad\ComunBundle\Entity\AdminNotificacionProgramada;
use Actualidad\ComunBundle\Entity\AdminCorreo;

class NotificacionesController extends Controller
{
   public function indexAction()
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();
        $noticias = array();

        $query = "SELECT n FROM ActualidadComunBundle:AdminNotificacion n ";

        $query = $em->createQuery($query);
        $notificacionesdb = $query->getResult();

        $notificaciones = array();

        foreach ($notificacionesdb as $notificacion)
        {
                $notificaciones[] = array('id' => $notificacion->getId(),
                                          'asunto' => $notificacion->getAsunto(),
                                          'delete_disabled' => $f->linkEliminar($notificacion->getId(),'AdminNotificacion'));
            
        }

        return $this->render('ActualidadBackendBundle:Notificacion:index.html.twig', array('notificaciones' => $notificaciones));
    }

    public function editAction(Request $request, $notificacion_id)
    {
                
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        
        $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        
        $f->setRequest($session->get('sesion_id'));

        if ($notificacion_id)
        {
            $notificacion = $em->getRepository('ActualidadComunBundle:AdminNotificacion')->find($notificacion_id);
        }
        else {
            $notificacion = new AdminNotificacion();
        }
        
        $notificacion->setUsuario($usuario);

        $form = $this->createFormBuilder($notificacion)
                     ->setAction($this->generateUrl('_editNotificacion', array('notificacion_id' => $notificacion_id)))
                     ->setMethod('POST')
                     ->add('asunto', TextType::class, array('label' => $this->get('translator')->trans('Asunto')))
                     ->add('mensaje', TextareaType::class, array('label' => $this->get('translator')->trans('Mensaje')))
                     ->getForm();


        $form->handleRequest($request);
        
        if ($request->getMethod() == 'POST')
        {

            $em->persist($notificacion);
            $em->flush();

            return $this->redirectToRoute('_showNotificacion', array('notificacion_id' => $notificacion->getId(), 'save'=> 1));
            
        }
        
        return $this->render('ActualidadBackendBundle:Notificacion:edit.html.twig', array('form' => $form->createView(),
                                                                                          'notificacion' => $notificacion,
                                                                                          'usuario' => $usuario));
        
    }

    public function showAction(Request $request, $notificacion_id, $save)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }

        $f->setRequest($session->get('sesion_id'));

        $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $notificacion = $em->getRepository('ActualidadComunBundle:AdminNotificacion')->find($notificacion_id);

        return $this->render('ActualidadBackendBundle:Notificacion:show.html.twig', array('notificacion' => $notificacion,
                                                                                          'usuario' => $usuario,
                                                                                          'save' => $save));

    }

    public function programadosAction()
    {

        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
       
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $notificaciones = array();
    
        $query = $em->createQuery("SELECT n FROM ActualidadComunBundle:AdminNotificacion n 
                                   ORDER BY n.id ASC");
        $notificacionesdb = $query->getResult();
        

        foreach ($notificacionesdb as $notificacion)
        {

            $query = $em->createQuery('SELECT COUNT(np.id) FROM ActualidadComunBundle:AdminNotificacionProgramada np 
                                        WHERE np.notificacion = :notificacion_id')
                        ->setParameter('notificacion_id', $notificacion->getId());
            $tiene_programados = $query->getSingleScalarResult();

            $notificaciones[] = array('id' => $notificacion->getId(),
                                      'asunto' => $notificacion->getAsunto(),
                                      'tiene_programados' => $tiene_programados);
        }

        return $this->render('ActualidadBackendBundle:Notificacion:programados.html.twig', array('notificaciones' => $notificaciones,
                                                                                                 'usuario' => $usuario));

    }

    public function editNotificacionProgramadaAction(Request $request, $notificacion_id, $notificacion_programada_id)
    {
                
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
    
        $f->setRequest($session->get('sesion_id'));

        if ($notificacion_programada_id)
        {
            $notificacion_programada = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);
        }
        else {
            $notificacion_programada = new AdminNotificacionProgramada();
            $notificacion = $em->getRepository('ActualidadComunBundle:AdminNotificacion')->find($notificacion_id);
            $notificacion_programada->setNotificacion($notificacion);
        }

        if ($request->getMethod() == 'POST')
        {

            $fecha_difusion = $request->request->get('fecha_difusion');
            $tipo_destino_id = $request->request->get('tipo_destino_id');
            $entidades_seleccionadas = $request->request->get('entidades');

            $tipo_destino = $em->getRepository('ActualidadComunBundle:AdminTipoDestino')->find($tipo_destino_id);
            $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            list($d, $m, $a) = explode("/", $fecha_difusion);
            $fecha_difusion = "$a-$m-$d";

            $entidades_incluidas = array();

            // Si estamos editando una notificación programada del tipo destino Grupo de alumnos o profesores, hay que eliminar primero el grupo
            if ($notificacion_programada_id)
            {
                $grupos = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                foreach ($grupos as $grupo)
                {
                    if ($tipo_destino_id == $yml['parameters']['tipo_destino']['grupo_alumnos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo_profesores'])
                    {
                        if (!in_array($grupo->getEntidadId(), $entidades_seleccionadas))
                        {
                            $em->remove($grupo);
                            $em->flush();
                        }
                        else {
                            $entidades_incluidas[] = $grupo->getEntidadId();
                        }
                    }
                    else {
                        $em->remove($grupo);
                        $em->flush();
                    }
                }
            }

            $notificacion_programada->setTipoDestino($tipo_destino);
            if ($tipo_destino_id == $yml['parameters']['tipo_destino']['alumnos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['profesores'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo_alumnos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo_profesores'])
            {
                $notificacion_programada->setEntidadId(null);
            }
            else {
                $notificacion_programada->setEntidadId($entidades_seleccionadas);
            }
            $notificacion_programada->setUsuario($usuario);
            $notificacion_programada->setFechaDifusion(new \DateTime($fecha_difusion));
            $notificacion_programada->setGrupo(null);
            $em->persist($notificacion_programada);
            $em->flush();

            if ($tipo_destino_id == $yml['parameters']['tipo_destino']['grupo_alumnos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo_profesores'])
            {
                // Creación del grupo de participantes seleccionados
                foreach ($entidades_seleccionadas as $entidad)
                {
                    if (!in_array($entidad, $entidades_incluidas))
                    {
                        $np = new AdminNotificacionProgramada();
                        $np->setNotificacion($notificacion_programada->getNotificacion());
                        $np->setTipoDestino($tipo_destino);
                        $np->setEntidadId($entidad);
                        $np->setUsuario($usuario);
                        $np->setFechaDifusion(new \DateTime($fecha_difusion));
                        $np->setGrupo($notificacion_programada);
                        $em->persist($np);
                        $em->flush();
                    }
                    else {
                        $np = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->findOneBy(array('notificacion' => $notificacion_programada->getNotificacion()->getId(),
                                                                                                                       'entidadId' => $entidad,
                                                                                                                       'grupo' => $notificacion_programada->getId()));
                        $np->setFechaDifusion(new \DateTime($fecha_difusion));
                        $em->persist($np);
                        $em->flush();
                    }
                }
            }

            if ($notificacion_programada->getFechaDifusion()->format('Y-m-d') == date('Y-m-d'))
            {
                
                // Se envía la notificación de una vez
                $query = $em->getConnection()->prepare('SELECT fnnotificacion_programada(:pnotificacion_programada_id) AS resultado;');
                $query->bindValue(':pnotificacion_programada_id', $notificacion_programada->getId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();

                $titulo = 'Notificación';
                $footer = $yml['parameters']['folders']['uploads'].'footernewsletter.png';
                $logo = $yml['parameters']['folders']['uploads'].'logo-actualidad-light.png';
                $url_plataforma = $this->container->getParameter('url_plataforma');
                $j = 0; // Contador de correos exitosos
                $np_id = 0; // notificacion_programada_id

                for ($i = 0; $i < count($r); $i++) 
                {

                    if ($j == 100)
                    {
                        // Cantidad tope de correos en una corrida
                        break;
                    }

                    // Limpieza de resultados
                    $reg = substr($r[$i]['resultado'], strrpos($r[$i]['resultado'], '{"')+2);
                    $reg = str_replace('"}', '', $reg);

                    // Se descomponen los elementos del resultado
                    list($np_id, $usuario_id, $login, $clave, $nombre, $apellido, $correo, $asunto, $mensaje) = explode("__", $reg);

                    if ($correo != '')
                    {

                        // Validar que no se haya enviado el correo a este destinatario
                        $correo_bd = $em->getRepository('ActualidadComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml['parameters']['tipo_correo']['notificacion_programada'],
                                                                                                              'entidadId' => $np_id,
                                                                                                              'usuario' => $usuario_id,
                                                                                                              'correo' => $correo));

                        if (!$correo_bd)
                        {
                            
                            // Sustitución de variables en el texto
                            $comodines = $yml['parameters']['comodines_correo'];
                            $reemplazos = array($login, $clave, $nombre, $apellido);
                            $mensaje = str_replace($comodines, $reemplazos, $mensaje);

                            $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                                       'datos' => array('nombre' => $nombre,
                                                                        'apellido' => $apellido,
                                                                        'mensaje' => $mensaje,
                                                                        'footer' => $footer,
                                                                        'logo' => $logo,
                                                                        'titulo' => $titulo,
                                                                        'url_plataforma' => $url_plataforma),
                                                       'asunto' => $asunto,
                                                       'remitente' => $this->container->getParameter('mailer_user_info'),
                                                       'remitente_name' => $this->container->getParameter('mailer_user_info_name'),
                                                       'destinatario' => $correo,
                                                       'mailer' => 'info_mailer');
                            $ok = $f->sendEmail($parametros_correo);

                            if ($ok)
                            {
                                
                                $j++;

                                // Registro del correo recien enviado
                                $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['notificacion_programada']);
                                $r_usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
                                $email = new AdminCorreo();
                                $email->setTipoCorreo($tipo_correo);
                                $email->setEntidadId($np_id);
                                $email->setUsuario($r_usuario);
                                $email->setCorreo($correo);
                                $email->setFecha(new \DateTime('now'));
                                $em->persist($email);
                                $em->flush();

                            }

                        }

                    }
                    
                }

            }

            return $this->redirectToRoute('_showNotificacionProgramada', array('notificacion_programada_id' => $notificacion_programada->getId()));

        }
        
        // Tipos de destino
        $tds = $em->getRepository('ActualidadComunBundle:AdminTipoDestino')->findAll();
        
        return $this->render('ActualidadBackendBundle:Notificacion:editNotificacionProgramada.html.twig', array('notificacion_programada' => $notificacion_programada,
                                                                                                                'tds' => $tds));
        
    }

    public function ajaxGrupoSeleccionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $tipo_destino_id = $request->query->get('tipo_destino_id');
        $notificacion_programada_id = $request->query->get('notificacion_programada_id');

        if ($notificacion_programada_id)
        {
            $notificacion_programada = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);
        }
        
        switch ($tipo_destino_id)
        {
            case $yml['parameters']['tipo_destino']['alumnos']:
                $entidades = array('tipo' => 'text',
                                   'multiple' => false,
                                   'valor' => $this->get('translator')->trans('Todos los alumnos'));
                break;
            case $yml['parameters']['tipo_destino']['profesores']:
                $entidades = array('tipo' => 'text',
                                   'multiple' => false,
                                   'valor' => $this->get('translator')->trans('Todos los profesores'));
                break;
            case $yml['parameters']['tipo_destino']['grupo_alumnos']:
                $usuarios_id = array();
                if ($notificacion_programada_id)
                {
                    $nps = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    foreach ($nps as $np)
                    {
                        $usuarios_id[] = $np->getEntidadId();
                    }
                }
                $qb = $em->createQueryBuilder();
                $qb->select('u')
                   ->from('ActualidadComunBundle:AdminUsuario', 'u')
                   ->andWhere('u.rol = :participante')
                   ->andWhere('u.activo = :activo')
                   ->orderBy('u.nombre', 'ASC')
                   ->setParameters(array('participante' => $yml['parameters']['rol']['alumno'],
                                         'activo' => true));
                $query = $qb->getQuery();
                $rus = $query->getResult();
                $valores = array();
                foreach ($rus as $ru)
                {
                    $correo = !$ru->getCorreo() ? $this->get('translator')->trans('Sin correo') : $ru->getCorreo();
                    $valores[] = array('id' => $ru->getId(),
                                       'nombre' => $ru->getNombre().' '.$ru->getApellido().' ('.$correo.')',
                                       'selected' => in_array($ru->getId(), $usuarios_id) ? 'selected' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => true,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['grupo_profesores']:
                $usuarios_id = array();
                if ($notificacion_programada_id)
                {
                    $nps = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    foreach ($nps as $np)
                    {
                        $usuarios_id[] = $np->getEntidadId();
                    }
                }
                $qb = $em->createQueryBuilder();
                $qb->select('u')
                   ->from('ActualidadComunBundle:AdminUsuario', 'u')
                   ->andWhere('u.rol = :participante')
                   ->andWhere('u.activo = :activo')
                   ->orderBy('u.nombre', 'ASC')
                   ->setParameters(array('participante' => $yml['parameters']['rol']['profesor'],
                                         'activo' => true));
                $query = $qb->getQuery();
                $rus = $query->getResult();
                $valores = array();
                foreach ($rus as $ru)
                {
                    $correo = !$ru->getCorreo() ? $this->get('translator')->trans('Sin correo') : $ru->getCorreo();
                    $valores[] = array('id' => $ru->getId(),
                                       'nombre' => $ru->getNombre().' '.$ru->getApellido().' ('.$correo.')',
                                       'selected' => in_array($ru->getId(), $usuarios_id) ? 'selected' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => true,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['alumnos_seccion']:
            case $yml['parameters']['tipo_destino']['profesores_seccion']:
                if ($notificacion_programada_id)
                {
                    $seccion = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminSeccion')->find($notificacion_programada->getEntidadId());
                }

                // Grados
                $qb = $em->createQueryBuilder();
                $qb->select('g')
                    ->from('ActualidadComunBundle:AdminGrado', 'g')
                    ->orderBy('g.id', 'ASC');
                $query = $qb->getQuery();
                $grados_bd = $query->getResult();
                foreach ($grados_bd as $grado)
                {
                    $grados[] = array('id' => $grado->getId(),
                                      'nombre' => $grado->getNombre(),
                                      'selected' => $notificacion_programada_id ? $seccion->getGrado()->getId() == $grado->getId() ? 'selected' : '' : '');
                }

                // Provincias
                $qb = $em->createQueryBuilder();
                $qb->select('p')
                    ->from('ActualidadComunBundle:AdminProvincia', 'p')
                    ->orderBy('p.nombre', 'ASC');
                $query = $qb->getQuery();
                $provincias_bd = $query->getResult();
                foreach ($provincias_bd as $provincia)
                {
                    $provincias[] = array('id' => $provincia->getId(),
                                          'nombre' => $provincia->getNombre(),
                                          'selected' => $notificacion_programada_id ? $seccion->getColegio()->getCiudad()->getProvincia()->getId() == $provincia->getId() ? 'selected' : '' : '');
                }

                // Ciudades
                $ciudades = array();
                if ($notificacion_programada_id)
                {
                    $query = $em->createQuery('SELECT c FROM ActualidadComunBundle:AdminCiudad c 
                                                WHERE c.provincia = :provincia_id
                                                ORDER BY c.nombre ASC')
                                ->setParameter('provincia_id', $seccion->getColegio()->getCiudad()->getProvincia()->getId());
                    $ciudades_bd = $query->getResult();
                    foreach ($ciudades_bd as $ciudad)
                    {
                        $ciudades[] = array('id' => $ciudad->getId(),
                                            'nombre' => $ciudad->getNombre(),
                                            'selected' => $seccion->getColegio()->getCiudad()->getId() == $ciudad->getId() ? 'selected' : '');
                    }
                }

                // Colegios
                $colegios = array();
                if ($notificacion_programada_id)
                {
                    $query = $em->createQuery('SELECT c FROM ActualidadComunBundle:AdminColegio c 
                                                WHERE c.ciudad = :ciudad_id
                                                ORDER BY c.nombre ASC')
                                ->setParameter('ciudad_id', $seccion->getColegio()->getCiudad()->getId());
                    $colegios_bd = $query->getResult();
                    foreach ($colegios_bd as $colegio)
                    {
                        $colegios[] = array('id' => $colegio->getId(),
                                            'nombre' => $colegio->getNombre(),
                                            'selected' => $seccion->getColegio()->getId() == $colegio->getId() ? 'selected' : '');
                    }
                }

                // Secciones
                $secciones = array();
                if ($notificacion_programada_id)
                {
                    $query = $em->createQuery('SELECT s FROM ActualidadComunBundle:AdminSeccion s 
                                                WHERE s.colegio = :colegio_id 
                                                AND s.grado = :grado_id 
                                                ORDER BY s.nombre ASC')
                                ->setParameters(array('colegio_id' => $seccion->getColegio()->getId(),
                                                      'grado_id' => $seccion->getGrado()->getId()));
                    $secciones_bd = $query->getResult();
                    foreach ($secciones_bd as $s)
                    {
                        $secciones[] = array('id' => $s->getId(),
                                             'nombre' => $s->getNombre(),
                                             'selected' => $seccion->getId() == $s->getId() ? 'selected' : '');
                    }
                }

                break;
            case $yml['parameters']['tipo_destino']['libro']:
                if ($notificacion_programada_id)
                {
                    $libro = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($notificacion_programada->getEntidadId());
                }

                // Grados
                $qb = $em->createQueryBuilder();
                $qb->select('g')
                    ->from('ActualidadComunBundle:AdminGrado', 'g')
                    ->orderBy('g.id', 'ASC');
                $query = $qb->getQuery();
                $grados_bd = $query->getResult();
                foreach ($grados_bd as $grado)
                {
                    $grados[] = array('id' => $grado->getId(),
                                      'nombre' => $grado->getNombre(),
                                      'selected' => $notificacion_programada_id ? $libro->getGrado()->getId() == $grado->getId() ? 'selected' : '' : '');
                }

                // Libros
                $libros = array();
                if ($notificacion_programada_id)
                {
                    $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
                                                WHERE p.grado = :grado_id 
                                                AND p.estatusContenido = :activo
                                                AND p.pagina IS NULL 
                                                ORDER BY p.orden ASC')
                                ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                      'grado_id' => $libro->getGrado()->getId()));
                    $libros_bd = $query->getResult();
                    foreach ($libros_bd as $l)
                    {
                        $libros[] = array('id' => $l->getId(),
                                          'nombre' => $l->getTitulo(),
                                          'selected' => $libro->getId() == $l->getId() ? 'selected' : '');
                    }
                }
                break;
    
        }

        if ($tipo_destino_id == $yml['parameters']['tipo_destino']['alumnos_seccion'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['profesores_seccion'])
        {
            $html = $this->renderView('ActualidadBackendBundle:Notificacion:grupoSeleccionSeccion.html.twig', array('grados' => $grados,
                                                                                                                    'provincias' => $provincias,
                                                                                                                    'ciudades' => $ciudades,
                                                                                                                    'colegios' => $colegios,
                                                                                                                    'secciones' => $secciones,
                                                                                                                    'tipo_destino' => $tipo_destino_id));
        }
        elseif($tipo_destino_id == $yml['parameters']['tipo_destino']['libro'])
        {
            $html = $this->renderView('ActualidadBackendBundle:Notificacion:grupoSeleccionSeccion.html.twig', array('grados' => $grados,
                                                                                                                    'libros' => $libros,
                                                                                                                    'tipo_destino' => $tipo_destino_id));
        }
        else {
            $html = $this->renderView('ActualidadBackendBundle:Notificacion:grupoSeleccion.html.twig', array('entidades' => $entidades));
        }

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxSelectSeccionAction(Request $request)
    {

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
        
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxSelectLibroAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $grado_id = $request->query->get('grado_id_l');

        $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
                                    WHERE p.pagina IS NULL
                                    AND p.grado = :grado_id
                                    ORDER BY p.titulo ASC')
                    ->setParameter('grado_id' , $grado_id);
        $objects = $query->getResult();

        if ($objects)
        {
            $options = '<option value=""></option>';
            foreach ($objects as $object)
            {
                $options .= '<option value="'.$object->getId().'">'.$object->getTitulo().'</option>';
            }
        }
        else
        {
            $options = '<option value="">'.$this->get('translator')->trans('No existen registros de libros para el grado seleccionado').'</option>';
        }
        
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function showNotificacionProgramadaAction(Request $request, $notificacion_programada_id)
    {
                
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        
        $f->setRequest($session->get('sesion_id'));

        $notificacion_programada = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

        switch ($notificacion_programada->getTipoDestino()->getId())
        {
            case $yml['parameters']['tipo_destino']['alumnos']:
                $entidades = array('tipo' => 'text',
                                   'valor' => $this->get('translator')->trans('Todos los alumnos'));
                break;
            case $yml['parameters']['tipo_destino']['profesores']:
                $entidades = array('tipo' => 'text',
                                   'valor' => $this->get('translator')->trans('Todos los profesores'));
                break;
            case $yml['parameters']['tipo_destino']['grupo_alumnos']:
                $nps = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($np->getEntidadId());
                    $valores[] = $usuario->getNombre().' '.$usuario->getApellido().' ('.$usuario->getCorreo().')';
                }
                $entidades = array('tipo' => 'table',
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['grupo_profesores']:
                $nps = $em->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($np->getEntidadId());
                    $valores[] = $usuario->getNombre().' '.$usuario->getApellido().' ('.$usuario->getCorreo().')';
                }
                $entidades = array('tipo' => 'table',
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['libro']:
                $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($notificacion_programada->getEntidadId());
                $entidades = array('tipo' => 'text',
                                   'valor' => $this->get('translator')->trans('Todos los usuarios del libro').' '.$libro->getTitulo().' - '.$this->get('translator')->trans('Grado').': '.$libro->getGrado()->getNombre());
                break;
            case $yml['parameters']['tipo_destino']['alumnos_seccion']:
            case $yml['parameters']['tipo_destino']['profesores_seccion']:
                $entidad = $em->getRepository('ActualidadComunBundle:AdminSeccion')->find($notificacion_programada->getEntidadId());
                $entidades = array('tipo' => 'multiple',
                                   'valor' => $entidad);
                break;
    
        }

        return $this->render('ActualidadBackendBundle:Notificacion:showNotificacionProgramada.html.twig', array('notificacion_programada' => $notificacion_programada,
                                                                                                                'entidades' => $entidades));
        
    }

    public function ajaxProgramadosAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $notificacion_id = $request->query->get('notificacion_id');

        $notificacion = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminNotificacion')->find($notificacion_id);
        
        $query = $em->createQuery("SELECT np FROM ActualidadComunBundle:AdminNotificacionProgramada np 
                                    WHERE np.notificacion = :notificacion_id 
                                    AND np.grupo IS NULL 
                                    AND np.fechaDifusion >= :hoy 
                                    ORDER BY np.fechaDifusion ASC")
                    ->setParameters(array('notificacion_id' => $notificacion_id,
                                          'hoy' => date('Y-m-d')));
        $nps = $query->getResult();

        $html = $this->renderView('ActualidadBackendBundle:Notificacion:notificacionesProgramadas.html.twig', array('nps' => $nps));

        $return = array('html' => $html,
                        'notificacion' => $notificacion->getAsunto());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTreeGrupoProgramadoAction($notificacion_programada_id, Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $notificacion_programada = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

        $return = array();
        switch ($notificacion_programada->getTipoDestino()->getId())
        {
            case $yml['parameters']['tipo_destino']['alumnos']:
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['alumnos'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', 0, \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $this->get('translator')->trans('Todos los alumnos'),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['profesores']:
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['profesores'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getEntidadId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $this->get('translator')->trans('Todos los profesores'),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['alumnos_seccion']:
                $entidad = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminSeccion')->find($notificacion_programada->getEntidadId());
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['alumnos_seccion'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getEntidadId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['profesores_seccion']:
                $entidad = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminSeccion')->find($notificacion_programada->getEntidadId());
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['profesores_seccion'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getEntidadId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['libro']:
                $entidad = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($notificacion_programada->getEntidadId());
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['libro'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getEntidadId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $entidad->getTitulo(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['grupo_alumnos']:
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['grupo_alumnos'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                              'state' => array('opened' => true),
                                              'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['grupo_profesores']:
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pentidad_id) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['grupo_profesores'], \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                              'state' => array('opened' => true),
                                              'icon' => 'fa fa-angle-double-right');
                break;
        }

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxSelectColegiosAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $ciudad_id = $request->query->get('ciudad_id');
        
        
        $query = $em->createQuery('SELECT c FROM ActualidadComunBundle:AdminColegio c
                                    WHERE c.ciudad = :ciudad_id
                                    ORDER BY c.nombre ASC')
                    ->setParameter('ciudad_id', $ciudad_id);
        $objects = $query->getResult();
        $options = '<select class="form_sty_sel form-control chosen-select"  style="border-radius: 5px ;" id="colegio_id" name="colegio_id" reset="2">';
        $options .= '<option value=""></option>';
        foreach ($objects as $object)
        {
            $options .= '<option value="'.$object->getId().'">'.$object->getNombre().'</option>';
        }

        $options .= '<img id="loader-colegio_id" class="img-loader" src="{{ asset("img/ui-anim_basic_16x16.gif") }}" style="display:none;">';
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}