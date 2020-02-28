<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Actualidad\ComunBundle\Entity\EaPrueba;
use Actualidad\ComunBundle\Entity\EaPregunta;
use Actualidad\ComunBundle\Entity\EaOpcion;
use Actualidad\ComunBundle\Entity\EaPreguntaOpcion;
use Actualidad\ComunBundle\Entity\EaPreguntaAsociacion;
use Actualidad\ComunBundle\Entity\EaPruebaLog;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Yaml\Yaml;

class EvaluacionController extends Controller
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

        $em = $this->getDoctrine()->getManager();

        $pruebas_bd = $em->getRepository('ActualidadComunBundle:EaPrueba')->findAll();

        $pruebas = array();

        foreach ($pruebas_bd as $p)
        {

            $preguntas = array();

            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPregunta p 
                                        WHERE p.prueba = :prueba_id AND p.pregunta IS NULL
                                        ORDER BY p.orden ASC")
                        ->setParameter('prueba_id', $p->getId());
            $preguntas_bd = $query->getResult();

            foreach ($preguntas_bd as $q)
            {
                $preguntas[] = $q->getEnunciado();
            }

            $pruebas[] = array('id' => $p->getId(),
                               'nombre' => $p->getNombre(),
                               'grado' => $p->getPagina()->getGrado()->getId(),
                               'libro' => $p->getPagina()->getPagina()->getTitulo().' - '.$p->getPagina()->getPagina()->getTipoPagina()->getNombre(),
                               'pagina' => $p->getPagina()->getTitulo(),
                               'preguntas' => $preguntas,
                               'status' => $p->getEstatusContenido()->getNombre(),
                               'modificacion' => $p->getFechaModificacion()->format('d/m/Y H:i a'),
                               'delete_disabled' => $f->linkEliminar($p->getId(), 'EaPrueba'));

        }
        return $this->render('ActualidadBackendBundle:Evaluacion:index.html.twig', array('pruebas' => $pruebas));
    }

    public function editAction($prueba_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $pagina_evaluada = 0;
        $pagina_id = '';
        $libros = array();
        $unidades = array();

        if ($prueba_id) 
        {
            $prueba = $em->getRepository('ActualidadComunBundle:EaPrueba')->find($prueba_id);
            //se consulta las paginas padres
            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.pagina IS NULL AND p.empresa = :empresa_id 
                                        ORDER BY p.grado ASC, p.orden ASC")
                        ->setParameter('empresa_id', $prueba->getPagina()->getEmpresa()->getId());
            $libros = $query->getResult();
            //se consulta las paginas hijas
            $unidades = $em->getRepository('ActualidadComunBundle:EaPagina')->findByPagina($prueba->getPagina()->getPagina()->getId());
        }
        else {
            $prueba = new EaPrueba();
            $prueba->setFechaCreacion(new \DateTime('now'));
        }
       
        if ($request->getMethod() == 'POST')
        {

            $nombre = $request->request->get('nombre');
            $cantidad_preguntas = $request->request->get('cantidad_preguntas');
            $cantidad_mostrar = $request->request->get('cantidad_mostrar');
            $min_correctas = $request->request->get('min_correctas');
            $max_intentos = $request->request->get('max_intentos');
            $duracion = $request->request->get('duracion');
            $estatus_contenido_id = $request->request->get('estatus_contenido_id');
            $pagina_id = $request->request->get('pagina_id');

            $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
            $estatus_contenido = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->find($estatus_contenido_id);
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            $prueba->setNombre($nombre);
            $prueba->setPagina($pagina);
            $prueba->setCantidadPreguntas($cantidad_preguntas);
            $prueba->setCantidadMostrar($cantidad_mostrar);
            $prueba->setMinCorrectas($min_correctas);
            $prueba->setMaxIntentos($max_intentos);
            $prueba->setDuracion(\DateTime::createFromFormat('H:i', $duracion));
            $prueba->setEstatusContenido($estatus_contenido);
            $prueba->setUsuario($usuario);
            $prueba->setFechaModificacion(new \DateTime('now'));

            $em->persist($prueba);
            $em->flush();

            $query = $em->createQuery('SELECT COUNT(p.id) FROM ActualidadComunBundle:EaPregunta p 
                                        WHERE p.prueba = :prueba_id AND p.pregunta IS NULL')
                        ->setParameter('prueba_id', $prueba->getId());
            $hay_preguntas = $query->getSingleScalarResult();

            if (!$hay_preguntas)
            {
                return $this->redirectToRoute('_editPregunta', array('prueba_id' => $prueba->getId(),
                                                                     'pregunta_id' => 0,
                                                                     'cantidad' => 1,
                                                                     'total' => $prueba->getCantidadPreguntas()));
            }
            else {
                return $this->redirectToRoute('_preguntas', array('prueba_id' => $prueba->getId()));
            }
            
        }

        $estatusContenido = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->findAll();
        $empresas = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->findAll();

        return $this->render('ActualidadBackendBundle:Evaluacion:edit.html.twig', array('empresas' => $empresas,
                                                                                        'prueba' => $prueba,
                                                                                        'pagina_evaluada' => $pagina_evaluada,
                                                                                        'pagina_id' => $pagina_id,
                                                                                        'estatusContenido' => $estatusContenido,
                                                                                        'libros' => $libros,
                                                                                        'unidades' => $unidades));

    }

    public function editPreguntaAction($prueba_id, $pregunta_id, $cantidad, $total, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $prueba = $em->getRepository('ActualidadComunBundle:EaPrueba')->find($prueba_id);
        
        if ($pregunta_id) 
        {
            $pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($pregunta_id);
        }
        else {

            $pregunta = new EaPregunta();
            $pregunta->setFechaCreacion(new \DateTime('now'));

            // Establecer el orden, último por defecto
            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPregunta p 
                                        WHERE p.pregunta IS NULL AND p.prueba = :prueba_id 
                                        ORDER BY p.orden DESC")
                        ->setParameter('prueba_id', $prueba->getId());
            $preguntas = $query->getResult();

            if ($preguntas)
            {
                $orden = $preguntas[0]->getOrden()+1;
            }
            else {
                $orden = 1;
            }

            $pregunta->setOrden($orden);

        }

        $pregunta->setPrueba($prueba);
        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $pregunta->setUsuario($usuario);
        $pregunta->setFechaModificacion(new \DateTime('now'));

        $form = $this->createFormBuilder($pregunta)
            ->setAction($this->generateUrl('_editPregunta', array('prueba_id' => $prueba_id,
                                                                  'pregunta_id' => $pregunta_id,
                                                                  'cantidad' => $cantidad,
                                                                  'total' => $total)))
            ->setMethod('POST')
            ->add('enunciado', TextType::class, array('label' => $this->get('translator')->trans('Enunciado')))
            ->add('imagen', HiddenType::class, array('required' => false))
            ->add('tipoPregunta', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaTipoPregunta',
                                                           'choice_label' => 'nombre',
                                                           'expanded' => false,
                                                           'label' => $this->get('translator')->trans('Tipo de pregunta'),
                                                           'placeholder' => ''))
            ->add('tipoElemento', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaTipoElemento',
                                                           'choice_label' => 'nombre',
                                                           'expanded' => false,
                                                           'label' => $this->get('translator')->trans('Tipo de elemento'),
                                                           'placeholder' => ''))
            ->add('valor', NumberType::class, array('label' => $this->get('translator')->trans('Valor de la pregunta')))
            ->getForm();

        $form->handleRequest($request);
       
        if ($request->getMethod() == 'POST')
        {

            $imagen = trim($request->request->get('imagen'));
            $pregunta->setImagen($imagen);

            $tipo_pregunta = $request->request->get('tipo_pregunta_id');

            if($tipo_pregunta==$values['parameters']['tipo_pregunta']['simple'])// el tipo de pregunta debe ser simple por defecto
            {
                $tipo_pregunta_id = $em->getRepository('ActualidadComunBundle:EaTipoPregunta')->find($tipo_pregunta);
                $pregunta->setTipoPregunta($tipo_pregunta_id);
            }

            $em->persist($pregunta);
            $em->flush();

            if($tipo_pregunta==$values['parameters']['tipo_pregunta']['simple'])
            {

                return $this->redirectToRoute('_editInteractivo', array('pregunta_id' => $pregunta->getId(),
                                                                        'cantidad' => $cantidad,   
                                                                        'total' => $prueba->getCantidadPreguntas() ));
            }
            else {
                return $this->redirectToRoute('_opciones', array('pregunta_id' => $pregunta->getId(),
                                                                 'cantidad' => $cantidad,
                                                                 'total' => $prueba->getCantidadPreguntas()));

            }
        }

        return $this->render('ActualidadBackendBundle:Evaluacion:editPregunta.html.twig', array('form' => $form->createView(),
                                                                                                'pregunta' => $pregunta,
                                                                                                'cantidad' => $cantidad,
                                                                                                'total' => $total,
                                                                                                'parameters' => $values['parameters']));

    }

    public function editInteractivoAction($pregunta_id, $cantidad, $total, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($pregunta_id);

        $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findOneByPregunta($pregunta->getId());
        
        $actualizacion = false;

        if($pregunta_opcion)
        {
            $opcion = $em->getRepository('ActualidadComunBundle:EaOpcion')->findOneById($pregunta_opcion->getOpcion());
            $actualizacion = true;
        }
        else {

            $opcion = new EaOpcion();
            $opcion->setFechaCreacion(new \DateTime('now'));

            $pregunta_opcion = new EaPreguntaOpcion();

        }

        if ($request->getMethod() == 'POST')
        {
            $descripcion = $request->request->get('descripcion');
            $prueba_id = $request->request->get('prueba_id');
            $pregunta_id = $request->request->get('pregunta_id');
            $codigo_interactivo = $request->request->get('codigo_interactivo');

            $prueba = $em->getRepository('ActualidadComunBundle:EaPrueba')->find($prueba_id);
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            $pregunta->setCodigoInteractivo($codigo_interactivo);
            $em->persist($pregunta);
            $em->flush();
           
            $opcion->setPrueba($prueba);
            $opcion->setDescripcion($descripcion);
            $opcion->setUsuario($usuario);
            $opcion->setFechaModificacion(new \DateTime('now'));
            $em->persist($opcion);
            $em->flush();

            $pregunta_opcion->setOpcion($opcion);
            $pregunta_opcion->setPregunta($pregunta);
            $pregunta_opcion->setCorrecta(true);
            $em->persist($pregunta_opcion);
            $em->flush();

            $query = $em->createQuery('SELECT COUNT(p.id) FROM ActualidadComunBundle:EaPregunta p 
                                       WHERE p.prueba = :prueba_id AND p.pregunta IS NULL')
                        ->setParameter('prueba_id', $prueba->getId());
            $hay_preguntas = $query->getSingleScalarResult();

            if ($hay_preguntas == $prueba->getCantidadPreguntas())
            {
                return $this->redirectToRoute('_preguntas', array('prueba_id' => $prueba->getId()));
            }
            else {

                $cantidad = $hay_preguntas+1;
                return $this->redirectToRoute('_editPregunta', array('prueba_id' => $prueba->getId(),
                                                                     'pregunta_id' => 0,
                                                                     'cantidad' => $cantidad,
                                                                     'total' => $prueba->getCantidadPreguntas()));
            }

        }

        return $this->render('ActualidadBackendBundle:Evaluacion:editInteractivo.html.twig', array('pregunta' => $pregunta,
                                                                                                   'cantidad' => $cantidad,
                                                                                                   'total' => $total,
                                                                                                   'opcion' => $opcion));
        
    }

    public function preguntasAction($prueba_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pregunta_asociacion = $values['parameters']['tipo_pregunta']['asociacion'];
        $interactivo = $values['parameters']['tipo_elemento']['interactivo'];

        $em = $this->getDoctrine()->getManager();
        $prueba = $em->getRepository('ActualidadComunBundle:EaPrueba')->find($prueba_id);

        $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPregunta p 
                                    WHERE p.prueba = :prueba_id AND p.pregunta IS NULL 
                                    ORDER BY p.orden ASC")
                    ->setParameter('prueba_id', $prueba_id);
        $preguntas_bd = $query->getResult();

        $preguntas = array();

        foreach ($preguntas_bd as $p)
        {

            $opciones = array();
            $correcta = 0;

            if ($p->getTipoPregunta()->getId() != $pregunta_asociacion)
            {
                $query = $em->createQuery("SELECT po, o FROM ActualidadComunBundle:EaPreguntaOpcion po 
                                            JOIN po.opcion o 
                                            WHERE po.pregunta = :pregunta_id AND o.prueba = :prueba_id 
                                            ORDER BY o.id ASC")
                            ->setParameters(array('pregunta_id' => $p->getId(),
                                                  'prueba_id' => $prueba->getId()));
                $pos = $query->getResult();
                foreach ($pos as $po)
                {
                    $correcta_str = $po->getCorrecta() ? ' (Correcta)' : '';
                    $opciones[] = $po->getOpcion()->getDescripcion().$correcta_str;
                    if ($po->getCorrecta())
                    {
                        $correcta++;
                    }
                }
            }
            else {
                
                $correcta++;
                $asociacion = $em->getRepository('ActualidadComunBundle:EaPreguntaAsociacion')->findOneByPregunta($p->getId());

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
                                                      'pregunta_id' => $p->getId()));
                    $pos = $query->getResult();

                    foreach ($pos as $po)
                    {
                        $opciones[] = $po->getPregunta()->getEnunciado().' --> '.$po->getOpcion()->getDescripcion();
                    }

                }

            }

            $preguntas[] = array('id' => $p->getId(),
                                 'enunciado' => $p->getEnunciado(),
                                 'tipo' => $p->getTipoPregunta()->getNombre(),
                                 'tipo_elemento' => $p->getTipoElemento()->getId(),
                                 'opciones' => $opciones,
                                 'modificacion' => $p->getFechaModificacion()->format('d/m/Y H:i a'),
                                 'orden' => $p->getOrden(),
                                 'correcta' => $correcta,
                                 'delete_disabled' => $f->linkEliminar($p->getId(), 'EaPregunta'));

        }

        return $this->render('ActualidadBackendBundle:Evaluacion:preguntas.html.twig', array('prueba' => $prueba,
                                                                                             'preguntas' => $preguntas,
                                                                                             'interactivo' =>  $interactivo));

    }

    public function opcionesAction($pregunta_id, $cantidad, $total)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pregunta_asociacion = $values['parameters']['tipo_pregunta']['asociacion'];
        $pregunta_simple = $values['parameters']['tipo_pregunta']['simple'];
        $tipo_elemento_imagen = $values['parameters']['tipo_elemento']['imagen'];
        $es_asociacion = 0;
        $opciones = array();
        
        $pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($pregunta_id);

        $es_simple = $pregunta->getTipoPregunta()->getId() == $pregunta_simple ? 1 : 0;

        if ($pregunta->getTipoPregunta()->getId() != $pregunta_asociacion)
        {

            $query = $em->createQuery("SELECT po, o FROM ActualidadComunBundle:EaPreguntaOpcion po 
                                        JOIN po.opcion o 
                                        WHERE po.pregunta = :pregunta_id AND o.prueba = :prueba_id 
                                        ORDER BY o.id ASC")
                        ->setParameters(array('pregunta_id' => $pregunta_id,
                                              'prueba_id' => $pregunta->getPrueba()->getId()));
            $opciones_bd = $query->getResult();

            foreach ($opciones_bd as $po)
            {
                $query = $em->createQuery('SELECT COUNT(r.id) FROM ActualidadComunBundle:EaRespuesta r 
                                            WHERE r.opcion = :opcion_id')
                            ->setParameter('opcion_id', $po->getOpcion()->getId());
                $hay_respuesta = $query->getSingleScalarResult();
                $delete_disabled = $hay_respuesta ? 'disabled' : '';
                $opciones[] = array('id' => $po->getId(),
                                    'descripcion' => $po->getOpcion()->getDescripcion(),
                                    'imagen' => $po->getOpcion()->getImagen(),
                                    'correcta' => $po->getCorrecta(),
                                    'delete_disabled' => $delete_disabled);
            }

        }
        else {
            
            $es_asociacion = 1;
            $opciones = array();

            $asociacion = $em->getRepository('ActualidadComunBundle:EaPreguntaAsociacion')->findOneByPregunta($pregunta_id);

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
                                                  'prueba_id' => $pregunta->getPrueba()->getId(),
                                                  'pregunta_id' => $pregunta_id));
                $opciones_bd = $query->getResult();

                foreach ($opciones_bd as $po)
                {
                    $query = $em->createQuery('SELECT COUNT(r.id) FROM ActualidadComunBundle:EaRespuesta r 
                                                WHERE r.opcion = :opcion_id')
                                ->setParameter('opcion_id', $po->getOpcion()->getId());
                    $hay_respuesta_opcion = $query->getSingleScalarResult();
                    $query = $em->createQuery('SELECT COUNT(r.id) FROM ActualidadComunBundle:EaRespuesta r 
                                                WHERE r.pregunta = :pregunta_id')
                                ->setParameter('pregunta_id', $po->getPregunta()->getId());
                    $hay_respuesta_pregunta = $query->getSingleScalarResult();
                    $delete_disabled = $hay_respuesta_opcion || $hay_respuesta_pregunta ? 'disabled' : '';
                    $opciones[] = array('id' => $po->getId(),
                                        'pregunta' => $po->getPregunta()->getEnunciado(),
                                        'imagen_pregunta' => $po->getPregunta()->getImagen(),
                                        'opcion' => $po->getOpcion()->getDescripcion(),
                                        'imagen_opcion' => $po->getOpcion()->getImagen(),
                                        'delete_disabled' => $delete_disabled);
                }
            }
        }

        return $this->render('ActualidadBackendBundle:Evaluacion:opciones.html.twig', array('opciones' => $opciones,
                                                                                            'pregunta' => $pregunta,
                                                                                            'tipo_elemento_imagen' => $tipo_elemento_imagen,
                                                                                            'es_asociacion' => $es_asociacion,
                                                                                            'es_simple' => $es_simple,
                                                                                            'cantidad' => $cantidad+1,
                                                                                            'total' => $total));

    }

    public function ajaxEditOpcionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $pregunta_opcion_id = $request->query->get('pregunta_opcion_id');
        $uploads = $this->container->getParameter('folders')['uploads'];
        
        $pregunta_opcion = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->find($pregunta_opcion_id);

        $return = array('descripcion' => $pregunta_opcion->getOpcion()->getDescripcion(),
                        'imagen' => $pregunta_opcion->getOpcion()->getImagen(),
                        'url_imagen' => $pregunta_opcion->getOpcion()->getImagen() ? $uploads.$pregunta_opcion->getOpcion()->getImagen() : '',
                        'enunciado' => $pregunta_opcion->getPregunta()->getEnunciado(),
                        'imagen_enunciado' => $pregunta_opcion->getPregunta()->getImagen(),
                        'url_imagen_enunciado' => $pregunta_opcion->getPregunta()->getImagen() ? $uploads.$pregunta_opcion->getPregunta()->getImagen() : '',
                        'correcta' => $pregunta_opcion->getCorrecta());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxCorrectaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $pregunta_opcion_id = $request->request->get('pregunta_opcion_id');
        $checked = $request->request->get('checked');

        $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->find($pregunta_opcion_id);

        // Si se marca SI, primero se deben colocar las demás en false
        if ($checked)
        {
            $pos = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findByPregunta($pregunta_opcion->getPregunta()->getId());
            foreach ($pos as $po)
            {
                $po->setCorrecta(false);
                $em->persist($po);
                $em->flush();
            }
        }
        
        $pregunta_opcion->setCorrecta($checked ? true : false);
        $em->persist($pregunta_opcion);
        $em->flush();
                    
        $return = array('id' => $pregunta_opcion->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUpdateOpcionAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $tipo_elemento_imagen = $values['parameters']['tipo_elemento']['imagen'];
        $uploads = $this->container->getParameter('folders')['uploads'];
        $pregunta_simple = $values['parameters']['tipo_pregunta']['simple'];
        
        $pregunta_opcion_id = $request->request->get('pregunta_opcion_id');
        $prueba_id = $request->request->get('prueba_id');
        $pregunta_id = $request->request->get('pregunta_id');

        $descripcion = $request->request->get('descripcion');
        $imagen = $request->request->get('imagen');
        $enunciado = $request->request->get('enunciado');
        $imagen_enunciado = $request->request->get('imagen_enunciado');
        $es_asociacion = $request->request->get('es_asociacion');
        $correcta = $request->request->get('correcta');

        $pregunta_padre = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($pregunta_id);
        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $checked = '';

        if ($pregunta_opcion_id)
        {
            $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->find($pregunta_opcion_id);
            $opcion = $pregunta_opcion->getOpcion();
            if ($es_asociacion)
            {
                $pregunta = $pregunta_opcion->getPregunta();
            }
        }
        else {
            $opcion = new EaOpcion();
            $opcion->setFechaCreacion(new \DateTime('now'));
            $pregunta_opcion = new EaPreguntaOpcion();
            if ($es_asociacion)
            {
                $pregunta = new EaPregunta();
                $pregunta->setFechaCreacion(new \DateTime('now'));
            }
        }

        // Opcion
        $opcion->setDescripcion($descripcion);
        $opcion->setImagen($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen ? $imagen : null);
        $opcion->setPrueba($pregunta_padre->getPrueba());
        $opcion->setUsuario($usuario);
        $opcion->setFechaModificacion(new \DateTime('now'));
        $em->persist($opcion);
        $em->flush();

        // Pregunta (Solo si es de asociación)
        if ($es_asociacion)
        {

            $pregunta->setEnunciado($enunciado);
            $pregunta->setImagen($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen ? $imagen_enunciado : null);
            $pregunta->setPrueba($pregunta_padre->getPrueba());
            $pregunta->setTipoPregunta($pregunta_padre->getTipoPregunta());
            $pregunta->setTipoElemento($pregunta_padre->getTipoElemento());
            $pregunta->setUsuario($usuario);
            $pregunta->setValor($pregunta_padre->getValor());
            $pregunta->setPregunta($pregunta_padre);
            $pregunta->setFechaModificacion(new \DateTime('now'));
            $em->persist($pregunta);
            $em->flush();

            $pregunta_asociacion = $em->getRepository('ActualidadComunBundle:EaPreguntaAsociacion')->findOneByPregunta($pregunta_id);
            if (!$pregunta_asociacion)
            {
                $pregunta_asociacion = new EaPreguntaAsociacion();
                $preguntas_str = '';
                $opciones_str = '';
            }
            else {
                $preguntas_str = $pregunta_asociacion->getPreguntas();
                $opciones_str = $pregunta_asociacion->getOpciones();
            }
            $preguntas_arr = $preguntas_str != '' ? explode(",", $preguntas_str) : array();
            $opciones_arr = $opciones_str != '' ? explode(",", $opciones_str) : array();
            if (!in_array($pregunta->getId(), $preguntas_arr))
            {
                $preguntas_arr[] = $pregunta->getId();
            }
            if (!in_array($opcion->getId(), $opciones_arr))
            {
                $opciones_arr[] = $opcion->getId();
            }
            $preguntas_str = implode(",", $preguntas_arr);
            $opciones_str = implode(",", $opciones_arr);
            $pregunta_asociacion->setPregunta($pregunta_padre);
            $pregunta_asociacion->setPreguntas($preguntas_str);
            $pregunta_asociacion->setOpciones($opciones_str);
            $em->persist($pregunta_asociacion);
            $em->flush();

        }

        // PreguntaOpcion
        $pregunta_opcion->setPregunta($es_asociacion ? $pregunta : $pregunta_padre);
        $pregunta_opcion->setOpcion($opcion);
        if ($correcta && $pregunta_padre->getTipoPregunta()->getId() == $pregunta_simple)
        {
            $pos = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findByPregunta($pregunta_padre->getId());
            foreach ($pos as $po)
            {
                $po->setCorrecta(false);
                $em->persist($po);
                $em->flush();
            }
        }
        $pregunta_opcion->setCorrecta(!$es_asociacion ? $correcta ? true : false : true);
        $em->persist($pregunta_opcion);
        $em->flush();

        // HTML
        $html = $pregunta_opcion_id ? '' : '<tr id="tr-'.$pregunta_opcion->getId().'">';
        if (!$es_asociacion)
        {

            $query = $em->createQuery('SELECT COUNT(r.id) FROM ActualidadComunBundle:EaRespuesta r 
                                        WHERE r.opcion = :opcion_id')
                        ->setParameter('opcion_id', $pregunta_opcion->getOpcion()->getId());
            $hay_respuesta = $query->getSingleScalarResult();
            if ($hay_respuesta)
            {
                $delete_disabled = 'disabled';
                $class_delete = '';
            }
            else {
                $delete_disabled = '';
                $class_delete = 'delete';
            }

            $checked = $pregunta_opcion->getCorrecta() ? ' checked' : '';

            $html .= '<td>'.$pregunta_opcion->getOpcion()->getDescripcion().'</td>';
            if ($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen)
            {
                $img = $pregunta_opcion->getOpcion()->getImagen() ? '<img src="'.$uploads.$pregunta_opcion->getOpcion()->getImagen().'" alt="" class="img__opc">' : '';
                $html .= '<td>'.$img.'</td>';
            }
            $html .= '<td class="center">
                        <div class="can-toggle demo-rebrand-2 small">
                            <input id="f'.$pregunta_opcion->getId().'" class="cb_activo" type="checkbox"'.$checked.'>
                            <label for="f'.$pregunta_opcion->getId().'">
                                <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                            </label>
                        </div>
                    </td>
                    <td class="center">
                        <a href="#" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$pregunta_opcion->getId().'"><span class="fa fa-pencil"></span></a>
                        <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$pregunta_opcion->getId().'"><span class="fa fa-trash"></span></a>
                    </td>';

        }
        else {

            $query = $em->createQuery('SELECT COUNT(r.id) FROM ActualidadComunBundle:EaRespuesta r 
                                        WHERE r.opcion = :opcion_id')
                        ->setParameter('opcion_id', $pregunta_opcion->getOpcion()->getId());
            $hay_respuesta_opcion = $query->getSingleScalarResult();
            $query = $em->createQuery('SELECT COUNT(r.id) FROM ActualidadComunBundle:EaRespuesta r 
                                        WHERE r.pregunta = :pregunta_id')
                        ->setParameter('pregunta_id', $pregunta_opcion->getPregunta()->getId());
            $hay_respuesta_pregunta = $query->getSingleScalarResult();
            if ($hay_respuesta_opcion || $hay_respuesta_pregunta)
            {
                $delete_disabled = 'disabled';
                $class_delete = '';
            }
            else {
                $delete_disabled = '';
                $class_delete = 'delete';
            }

            if ($pregunta_padre->getTipoElemento()->getId() == $tipo_elemento_imagen)
            {
                $img_pregunta = $pregunta_opcion->getPregunta()->getImagen() ? '<img src="'.$uploads.$pregunta_opcion->getPregunta()->getImagen().'" alt="" class="img__opc">' : '';
                $img_opcion = $pregunta_opcion->getOpcion()->getImagen() ? '<img src="'.$uploads.$pregunta_opcion->getOpcion()->getImagen().'" alt="" class="img__opc">' : '';
            }
            else {
                $img_pregunta = '';
                $img_opcion = '';
            }

            $html .= '<td>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4 offset-xl-1 offset-lg-1 offset-md-1 offset-sm-1">'.$pregunta_opcion->getPregunta()->getEnunciado().'</div>
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4">'.$img_pregunta.'</div>
                            </div>
                        </td>
                        <td>
                            <div class="row">
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4 offset-xl-1 offset-lg-1 offset-md-1 offset-sm-1">'.$pregunta_opcion->getOpcion()->getDescripcion().'</div>
                                <div class="col-md-4 col-sm-4 col-lg-4 col-xl-4">'.$img_opcion.'</div>
                            </div>
                        </td>
                        <td class="center">
                            <a href="#" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$pregunta_opcion->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$pregunta_opcion->getId().'"><span class="fa fa-trash"></span></a>
                        </td>';

        }

        $html .= $pregunta_opcion_id ? '' : '</tr>';
                    
        $return = array('html' => $html,
                        'checked' => $checked,
                        'id' => $pregunta_opcion->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDeleteOpcionAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $pregunta_opcion_id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pregunta_asociacion_id = $values['parameters']['tipo_pregunta']['asociacion'];

        $pregunta_opcion = $em->getRepository('ActualidadComunBundle:'.$entity)->find($pregunta_opcion_id);
        $opcion = $pregunta_opcion->getOpcion();
        $pregunta = $pregunta_opcion->getPregunta();

        if ($pregunta->getTipoPregunta()->getId() == $pregunta_asociacion_id)
        {

            // PreguntaAsociacion
            $pregunta_asociacion = $em->getRepository('ActualidadComunBundle:EaPreguntaAsociacion')->findOneByPregunta($pregunta->getPregunta()->getId());
            $preguntas_str = $pregunta_asociacion->getPreguntas();
            $opciones_str = $pregunta_asociacion->getOpciones();
            $preguntas_arr = explode(",", $preguntas_str);
            $opciones_arr = explode(",", $opciones_str);
            $preguntas_arr_new = array();
            $opciones_arr_new = array();
            foreach ($preguntas_arr as $p_arr)
            {
                if ($p_arr != $pregunta->getId())
                {
                    $preguntas_arr_new[] = $p_arr;
                }
            }
            foreach ($opciones_arr as $o_arr)
            {
                if ($o_arr != $opcion->getId())
                {
                    $opciones_arr_new[] = $o_arr;
                }
            }
            if (!count($preguntas_arr_new) && !count($opciones_arr_new))
            {
                $em->remove($pregunta_asociacion);
                $em->flush();
            }
            else {
                $preguntas_str = implode(",", $preguntas_arr_new);
                $opciones_str = implode(",", $opciones_arr_new);
                $pregunta_asociacion->setPreguntas($preguntas_str);
                $pregunta_asociacion->setOpciones($opciones_str);
                $em->persist($pregunta_asociacion);
                $em->flush();
            }

        }
        
        // PreguntaOpcion
        $em->remove($pregunta_opcion);
        $em->flush();

        // Pregunta
        if ($pregunta->getTipoPregunta()->getId() == $pregunta_asociacion_id)
        {
            $em->remove($pregunta);
            $em->flush();
        }

        // Opcion
        $em->remove($opcion);
        $em->flush();

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxCodigoEvaluacionAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pregunta_id = $request->request->get('pregunta_id');
        $codigo_interactivo = $request->request->get('codigo_interactivo');

        $query = $em->createQuery('SELECT COUNT(p.id) FROM ActualidadComunBundle:EaPregunta p 
                                    WHERE p.codigoInteractivo = :codigo_interactivo AND 
                                    p.id != :pregunta_id')
                    ->setParameters(array('codigo_interactivo' => $codigo_interactivo,
                                          'pregunta_id' => $pregunta_id));
        $ok = $query->getSingleScalarResult();
                    
        $return = array('ok' => $ok);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function verEvaluacionInteractivoAction($pregunta_id, $reset, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findOneByPregunta($pregunta_id);

        $query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPruebaLog pl 
                                    WHERE pl.prueba = :prueba_id 
                                    AND pl.usuario = :usuario_id 
                                    ORDER BY pl.id DESC")
                    ->setParameters(array('prueba_id' => $pregunta_opcion->getPregunta()->getPrueba()->getId(),
                                          'usuario_id' => $session->get('usuario')['id']));
        $pls = $query->getResult();

        if ($reset)
        {
            // Se eliminan todos los registros de esta pregunta
            foreach ($pls as $pl)
            {
                $respuestas = $em->getRepository('ActualidadComunBundle:EaRespuesta')->findByPruebaLog($pl->getId());
                foreach ($respuestas as $respuesta)
                {
                    $em->remove($respuesta);
                    $em->flush();
                }
                $em->remove($pl);
                $em->flush();
            }
            $pls = array();
        }

        // Condición inicial
        if (!$pls)
        {
            $correctas = '';
            $incorrectas = '';
            $intentos = 0;
            $prueba_log_id = 0;
        }
        else {
            $correctas = $pls[0]->getPreguntasCorrectas() ? $pls[0]->getPreguntasCorrectas() : '';
            $incorrectas = $pls[0]->getPreguntasErradas() ? $pls[0]->getPreguntasErradas() : '';
            $intentos = count($pls);
            if ($pls[0]->getEstado() == $yml['parameters']['estado_prueba']['curso'] || $intentos >= $pregunta_opcion->getPregunta()->getPrueba()->getMaxIntentos())
            {
                $prueba_log_id = $pls[0]->getId();
                $intentos -= 1;
            }
            else {
                $prueba_log_id = 0;
            }
        }

        if (!$prueba_log_id)
        {
            // Nueva PruebaLog
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            $prueba_log = new EaPruebaLog();
            $prueba_log->setPrueba($pregunta_opcion->getPregunta()->getPrueba());
            $prueba_log->setUsuario($usuario);
            $prueba_log->setFechaInicio(new \DateTime('now'));
            $prueba_log->setPorcentajeAvance(0);
            $prueba_log->setEstado($yml['parameters']['estado_prueba']['curso']);
            $em->persist($prueba_log);
            $em->flush();
            $prueba_log_id = $prueba_log->getId();
        }

        // Caso de pruebas
        $caso = array('descripcion' => 'Simulación de la evaluación en comunicación con la plataforma',
                      'prueba_log_id' => $prueba_log_id,
                      'pregunta_id' => $pregunta_id,
                      'correctas' => $correctas,
                      'incorrectas' => $incorrectas,
                      'intentos' => $intentos > $pregunta_opcion->getPregunta()->getPrueba()->getMaxIntentos() ? 2 : $intentos,
                      'make_ajax' => $yml['parameters']['serviciosActivos']['evaluacion'],
                      //'make_ajax' => 1,
                      'url' => '_ajaxServicioEvaluacion',
                      'max_intentos' => $pregunta_opcion->getPregunta()->getPrueba()->getMaxIntentos(),
                      'min_correctas' => $pregunta_opcion->getPregunta()->getPrueba()->getMinCorrectas());
        
        return $this->render('ActualidadBackendBundle:Evaluacion:verEvaluacionInteractivo.html.twig', array('pregunta_opcion' => $pregunta_opcion,
                                                                                                            'caso' => $caso,
                                                                                                            'servidor_recursos' => $this->container->getParameter('servidor_recursos')));
        
    }

    public function ajaxTryCaseAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $pregunta_id = $request->request->get('pregunta_id');
        $intentos = $request->request->get('intentos');
        $usuario_id = $request->request->get('usuario_id');

        $pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($pregunta_id);

        $query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPruebaLog pl 
                                    WHERE pl.prueba = :prueba_id 
                                    AND pl.usuario = :usuario_id 
                                    ORDER BY pl.id DESC")
                    ->setParameters(array('prueba_id' => $pregunta->getPrueba()->getId(),
                                          'usuario_id' => $usuario_id));
        $pls = $query->getResult();

        // Condición inicial
        if ($intentos >= $pregunta->getPrueba()->getMaxIntentos())
        {
            // Se sobreescribirá el último prueba_log
            $prueba_log_id = $pls ? $pls[0]->getId() : 0;
        }
        else {
            // Se crea un prueba_log
            $prueba_log_id = 0;
        }

        $correctas = $pls ? $pls[0]->getPreguntasCorrectas() ? $pls[0]->getPreguntasCorrectas() : '' : '';
        $incorrectas = $pls ? $pls[0]->getPreguntasErradas() ? $pls[0]->getPreguntasErradas() : '' : '';

        if (!$prueba_log_id)
        {
            // Nueva PruebaLog
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
            $prueba_log = new EaPruebaLog();
            $prueba_log->setPrueba($pregunta->getPrueba());
            $prueba_log->setUsuario($usuario);
            $prueba_log->setFechaInicio(new \DateTime('now'));
            $prueba_log->setPorcentajeAvance(0);
            $prueba_log->setEstado($yml['parameters']['estado_prueba']['curso']);
            $em->persist($prueba_log);
            $em->flush();
            $prueba_log_id = $prueba_log->getId();
        }

        // Caso de pruebas
        $caso = array('descripcion' => 'Simulación de la evaluación en comunicación con la plataforma',
                      'prueba_log_id' => $prueba_log_id,
                      'pregunta_id' => $pregunta_id,
                      'correctas' => $correctas,
                      'incorrectas' => $incorrectas,
                      'intentos' => $intentos,
                      'make_ajax' => $yml['parameters']['serviciosActivos']['evaluacion'],
                      //'make_ajax' => 1,
                      'url' => '_ajaxServicioEvaluacion',
                      'max_intentos' => $pregunta->getPrueba()->getMaxIntentos());

        $html = $this->renderView('ActualidadBackendBundle:Evaluacion:ajaxTryCase.html.twig', array('caso' => $caso));
                    
        $return = array('html' => $html,
                        'prueba_log_id' => $prueba_log_id,
                        'correctas' => $correctas,
                        'incorrectas' => $incorrectas);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}