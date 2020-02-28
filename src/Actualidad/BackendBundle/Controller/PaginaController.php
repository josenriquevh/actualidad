<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Actualidad\ComunBundle\Entity\EaPagina;
use Actualidad\ComunBundle\Entity\EaTipoPagina;
use Actualidad\ComunBundle\Entity\AdminGrado;
use Actualidad\ComunBundle\Entity\AdminAyudaInteractivo;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityRepository;

class PaginaController extends Controller
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

        // Limpiar sesión de pages_setting
        $session->set('pages_setting', '');

        $em = $this->getDoctrine()->getManager();

        // Grados para el filtro
        $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g 
                                    ORDER BY g.nombre ASC");
        $grados = $query->getResult();

        // Empresas para el filtro
        $empresas = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->findAll();

        return $this->render('ActualidadBackendBundle:Pagina:index.html.twig', array('grados' => $grados,
                                                                                     'empresas' => $empresas));

    }

    public function ajaxGetPaginasAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $grado_id = $request->query->get('grado_id');
        $empresa_id = $request->query->get('empresa_id');
        $some = 0;

        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('ActualidadComunBundle:EaPagina', 'p')
           ->andWhere('p.pagina IS NULL')
           ->orderBy('p.orden', 'ASC');
        if ($grado_id)
        {
            $qb->andWhere('p.grado = :grado_id');
            $parametros['grado_id'] = $grado_id;
            $some = 1;
        }
        if ($empresa_id)
        {
            $qb->andWhere('p.empresa = :empresa_id');
            $parametros['empresa_id'] = $empresa_id;
            $some = 1;
        }
        if ($some)
        {
            $qb->setParameters($parametros);
        }
        $query = $qb->getQuery();
        $pages = $query->getResult();

        // Estructura de las páginas
        $paginas = $f->paginas($pages);

        $html = $this->renderView('ActualidadBackendBundle:Pagina:paginas.html.twig', array('paginas' => $paginas));
        
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function newAction(Request $request){

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        // Limpiar sesión de pages_setting
        $session->set('pages_setting', '');

        $em = $this->getDoctrine()->getManager();
        
        $pagina = new EaPagina();
        $pagina->setInteractivo(false);
        $pagina->setCodigoInteractivo(null);
        $pagina->setFechaCreacion(new \DateTime('now'));
        $pagina->setFechaModificacion(new \DateTime('now'));

        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $pagina->setUsuario($usuario);

        $form = $this->createFormBuilder($pagina)
            ->setAction($this->generateUrl('_newPagina'))
            ->setMethod('POST')
            ->add('titulo', TextType::class, array('label' => $this->get('translator')->trans('Título')))
            ->add('subtitulo', TextType::class, array('label' => $this->get('translator')->trans('Sub-título'),
                                                      'required' => false))
            ->add('tipoPagina', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaTipoPagina',
                                                         'choice_label' => 'nombre',
                                                         'query_builder' => function (EntityRepository $er) {
                                                            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                                                            return $er->createQueryBuilder('tp')
                                                                      ->where('tp.id IN (:libros)')
                                                                      ->setParameter('libros', array($yml['parameters']['tipo_pagina']['libro_alumnos'], $yml['parameters']['tipo_pagina']['libro_profesores']));
                                                         },
                                                         'expanded' => false,
                                                         'label' => $this->get('translator')->trans('Tipo'),
                                                         'placeholder' => ''))
            ->add('grado', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\AdminGrado',
                                                    'choice_label' => 'nombre',
                                                    'query_builder' => function (EntityRepository $er) {
                                                        return $er->createQueryBuilder('g')
                                                                  ->orderBy('g.nombre', 'ASC');
                                                    },
                                                    'expanded' => false,
                                                    'label' => $this->get('translator')->trans('Grado'),
                                                    'placeholder' => ''))
            ->add('empresa', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\AdminEmpresa',
                                                         'choice_label' => 'nombre',
                                                         'expanded' => false,
                                                         'label' => $this->get('translator')->trans('Empresa'),
                                                         'placeholder' => ''))
            ->add('descripcion', TextareaType::class, array('label' => $this->get('translator')->trans('Descripción'),
                                                            'required' => false))
            ->add('contenido', TextareaType::class, array('label' => $this->get('translator')->trans('Contenido'),
                                                          'required' => false))
            ->add('foto', HiddenType::class, array('label' => $this->get('translator')->trans('Foto principal')))
            ->add('pdf', TextType::class, array('label' => $this->get('translator')->trans('Material complementario'),
                                                'required' => false))
            ->add('estatusContenido', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->add('estilo', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\AdminEstilo',
                                                                'choice_label' => 'nombre',
                                                                'expanded' => false,
                                                                'label' => 'CSS'))
            ->getForm();

        $form->handleRequest($request);
       
        if ($request->getMethod() == 'POST')
        {

            $pagina_referencia_id = $request->request->get('pagina_referencia_id');
            $subpaginas = $request->request->get('subpaginas');
            $tipo_subpaginas = $request->request->get('tipo_subpaginas');
            $status_subpaginas = $request->request->get('status_subpaginas');

            // En caso de que el libro sea la guía del profesor, se debe indicar a cuál libro hace referencia
            if ($pagina->getTipoPagina()->getId() == $yml['parameters']['tipo_pagina']['libro_profesores'])
            {
                $pagina_referencia = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_referencia_id);
                $pagina->setPaginaReferencia($pagina_referencia);
            }
            else {
                $pagina->setPaginaReferencia(null);
            }

            // Establecer el orden, último por defecto
            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.pagina IS NULL 
                                        AND p.grado = :grado_id 
                                        AND p.empresa = :empresa_id 
                                        ORDER BY p.orden DESC")
                        ->setParameters(array('grado_id' => $pagina->getGrado()->getId(),
                                              'empresa_id' => $pagina->getEmpresa()->getId()));
            $paginas = $query->getResult();
            
            if ($paginas)
            {
                $orden = $paginas[0]->getOrden()+1;
            }
            else {
                $orden = 1;
            }

            $pagina->setOrden($orden);

            $em->persist($pagina);
            $em->flush();

            if ($subpaginas > 0)
            {

                // Se setea el wizard en la sesión
                $pages_setting = array();
                $pages_setting[$pagina->getId()] = array('cantidad' => 1,
                                                         'total' => $subpaginas,
                                                         'tipo_pagina_id' => $tipo_subpaginas,
                                                         'estatus_contenido_id' => $status_subpaginas);
                $session->set('pages_setting', $pages_setting);
                return $this->redirectToRoute('_editPagina', array('pagina_padre_id' => $pagina->getId(),
                                                                   'pagina_id' => 0));
            }
            else {
                return $this->redirectToRoute('_pagina', array('pagina_id' => $pagina->getId()));
            }
            
        }

        $query = $em->createQuery("SELECT tp FROM ActualidadComunBundle:EaTipoPagina tp 
                                    WHERE tp.id NOT IN (:libros)")
                    ->setParameter('libros', array($yml['parameters']['tipo_pagina']['libro_alumnos'], $yml['parameters']['tipo_pagina']['libro_profesores']));
        $tipos = $query->getResult();

        $status = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->findAll();
        
        return $this->render('ActualidadBackendBundle:Pagina:new.html.twig', array('form' => $form->createView(),
                                                                                   'tipos' => $tipos,
                                                                                   'status' => $status,
                                                                                   'pagina' => $pagina));

    }

    public function ajaxPaginaReferenciaAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');

        if ($pagina_id != 0)
        {
            $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.empresa = :empresa_id 
                                        AND p.tipoPagina = :libro_alumno 
                                        AND p.pagina IS NULL 
                                        AND p.id != :pagina_id 
                                        ORDER BY p.grado ASC, p.orden ASC')
                        ->setParameters(array('empresa_id' => $empresa_id,
                                              'libro_alumno' => $yml['parameters']['tipo_pagina']['libro_alumnos'],
                                              'pagina_id' => $pagina_id));
        }
        else {
            $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.empresa = :empresa_id 
                                        AND p.tipoPagina = :libro_alumno 
                                        AND p.pagina IS NULL 
                                        ORDER BY p.grado ASC, p.orden ASC')
                        ->setParameters(array('empresa_id' => $empresa_id,
                                              'libro_alumno' => $yml['parameters']['tipo_pagina']['libro_alumnos']));
        }

        $paginas = $query->getResult();

        $html = '<div class="col-sm-16 col-md-16 col-lg-16">
            <label for="pagina_referencia_id" class="col-sm-16 col-md-16 col-lg-16 col-form-label">'.$this->get('translator')->trans('Libro de referencia a la guía del profesor').'</label>
            <div class="col-sm-16 col-md-16 col-lg-16">
                <select class="form-control form_sty_select" name="pagina_referencia_id" id="pagina_referencia_id">
                    <option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $html .= '<option value="'.$pagina->getId().'">'.$pagina->getTitulo().' - '.$this->get('translator')->trans('Grado').' '.$pagina->getGrado()->getNombre().'</option>';
        }
        $html .= '</select>
                <span class="fa fa-book"></span>
            </div>
        </div>';
        
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function editAction($pagina_padre_id, $pagina_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        // Para el wizard
        $tipo_pagina_id = 0;
        $estatus_contenido_id = 0;
        $cantidad = 1;
        $total = 1;
        $wizard = 0;
        if ($pages_setting = $session->get('pages_setting'))
        {
            if ($pages_setting[$pagina_padre_id])
            {
                $tipo_pagina_id = $pages_setting[$pagina_padre_id]['tipo_pagina_id'];
                $estatus_contenido_id = $pages_setting[$pagina_padre_id]['estatus_contenido_id'];
                $cantidad = $pages_setting[$pagina_padre_id]['cantidad'];
                $total = $pages_setting[$pagina_padre_id]['total'];
                $wizard = 1;
            }
        }
        
        if ($pagina_id) 
        {
            $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
        }
        else {
            $pagina = new EaPagina();
            $pagina->setFechaCreacion(new \DateTime('now'));
        }

        if ($pagina_padre_id)
        {
            $pagina_padre = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_padre_id);
            $pagina->setPagina($pagina_padre);
            $pagina->setGrado($pagina_padre->getGrado());
            $pagina->setEmpresa($pagina_padre->getEmpresa());
        }
        else {
            $pagina_padre = 0;
        }

        if ($tipo_pagina_id)
        {
            $tipo_pagina = $em->getRepository('ActualidadComunBundle:EaTipoPagina')->find($tipo_pagina_id);
            $pagina->setTipoPagina($tipo_pagina);
        }

        if ($estatus_contenido_id)
        {
            $estatus_contenido = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->find($estatus_contenido_id);
            $pagina->setEstatusContenido($estatus_contenido);
        }

        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $pagina->setUsuario($usuario);
        $pagina->setFechaModificacion(new \DateTime('now'));

        $form = $this->createFormBuilder($pagina)
            ->setAction($this->generateUrl('_editPagina', array('pagina_padre_id' => $pagina_padre_id,
                                                                'pagina_id' => $pagina_id)))
            ->setMethod('POST')
            ->add('titulo', TextType::class, array('label' => $this->get('translator')->trans('Título')))
            ->add('subtitulo', TextType::class, array('label' => $this->get('translator')->trans('Sub-título'),
                                                      'required' => false))
            ->add('descripcion', TextareaType::class, array('label' => $this->get('translator')->trans('Descripción'),
                                                            'required' => false))
            ->add('contenido', TextareaType::class, array('label' => $this->get('translator')->trans('Contenido'),
                                                          'required' => false))
            ->add('foto', HiddenType::class, array('label' => $this->get('translator')->trans('Foto principal')))
            ->add('pdf', TextType::class, array('label' => $this->get('translator')->trans('Material complementario'),
                                                'required' => false))
            ->add('estatusContenido', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->getForm();
        if (!$pagina_padre_id)
        {
            $form->add('grado', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\AdminGrado',
                                                         'choice_label' => 'nombre',
                                                         'query_builder' => function (EntityRepository $er) {
                                                            return $er->createQueryBuilder('g')
                                                                      ->orderBy('g.nombre', 'ASC');
                                                         },
                                                         'expanded' => false,
                                                         'label' => $this->get('translator')->trans('Grado'),
                                                         'placeholder' => ''))
                 ->add('tipoPagina', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaTipoPagina',
                                                              'choice_label' => 'nombre',
                                                              'query_builder' => function (EntityRepository $er) {
                                                                $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                                                                return $er->createQueryBuilder('tp')
                                                                          ->where('tp.id IN (:libros)')
                                                                          ->setParameter('libros', array($yml['parameters']['tipo_pagina']['libro_alumnos'], $yml['parameters']['tipo_pagina']['libro_profesores']));
                                                              },
                                                              'expanded' => false,
                                                              'label' => $this->get('translator')->trans('Tipo'),
                                                              'placeholder' => ''))
                 ->add('estilo', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\AdminEstilo',
                                                          'choice_label' => 'nombre',
                                                          'expanded' => false,
                                                          'label' => 'CSS'));
        }
        else {
            $form->add('interactivo', CheckboxType::class, array('label' => $this->get('translator')->trans('Interactivo'),
                                                                 'required' => false))
                 ->add('codigoInteractivo', TextType::class, array('label' => $this->get('translator')->trans('Código del interactivo'),
                                                                   'required' => false))
                 ->add('ayudaInteractivo', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\AdminAyudaInteractivo',
                                                                    'choice_label' => 'nombre',
                                                                    'expanded' => false,
                                                                    'label' => $this->get('translator')->trans('Ayuda')))
                 ->add('tipoPagina', EntityType::class, array('class' => 'Actualidad\\ComunBundle\\Entity\\EaTipoPagina',
                                                              'choice_label' => 'nombre',
                                                              'query_builder' => function (EntityRepository $er) {
                                                                $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                                                                return $er->createQueryBuilder('tp')
                                                                          ->where('tp.id NOT IN (:libros)')
                                                                          ->setParameter('libros', array($yml['parameters']['tipo_pagina']['libro_alumnos'], $yml['parameters']['tipo_pagina']['libro_profesores']));
                                                              },
                                                              'expanded' => false,
                                                              'label' => $this->get('translator')->trans('Tipo'),
                                                              'placeholder' => ''));
        }

        $form->handleRequest($request);
       
        if ($request->getMethod() == 'POST')
        {

            $pagina_referencia_id = $request->request->get('pagina_referencia_id');
            $subpaginas = $request->request->get('subpaginas');
            $tipo_subpaginas = $request->request->get('tipo_subpaginas');
            $status_subpaginas = $request->request->get('status_subpaginas');
            
            // En caso de que el libro sea la guía del profesor, se debe indicar a cuál libro hace referencia
            if ($pagina->getTipoPagina()->getId() == $yml['parameters']['tipo_pagina']['libro_profesores'] && !$pagina->getPagina())
            {
                $pagina_referencia = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_referencia_id);
                $pagina->setPaginaReferencia($pagina_referencia);
            }
            else {
                $pagina->setPaginaReferencia(null);
            }

            if (!$pagina_id)
            {
                // Establecer el orden, último por defecto
                $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                            WHERE p.pagina = :pagina_padre_id 
                                            AND p.grado = :grado_id 
                                            AND p.empresa = :empresa_id 
                                            ORDER BY p.orden DESC")
                            ->setParameters(array('pagina_padre_id' => $pagina_padre_id, 
                                                  'grado_id' => $pagina->getGrado()->getId(),
                                                  'empresa_id' => $pagina->getEmpresa()->getId()));
                $paginas = $query->getResult();
                if ($paginas)
                {
                    $orden = $paginas[0]->getOrden()+1;
                }
                else {
                    $orden = 1;
                }
                $pagina->setOrden($orden);
            }

            if (!$pagina->getInteractivo())
            {
                $pagina->setCodigoInteractivo(null);
            }

            // El grado de la página padre es el mismo para las sub-paginas
            if (!$pagina_padre_id)
            {
                $f->setGradoSubPages($pagina->getId(), $pagina->getGrado());
            }

            $em->persist($pagina);
            $em->flush();

            if ($subpaginas > 0)
            {
                // Se setea el wizard en la sesión
                $pages_setting[$pagina->getId()] = array('cantidad' => 1,
                                                         'total' => $subpaginas,
                                                         'tipo_pagina_id' => $tipo_subpaginas,
                                                         'estatus_contenido_id' => $status_subpaginas);
                $session->set('pages_setting', $pages_setting);
            }

            if ($cantidad < $total)
            {
                $cantidad++;
                $pages_setting[$pagina_padre_id] = array('cantidad' => $cantidad,
                                                         'total' => $total,
                                                         'tipo_pagina_id' => $tipo_pagina_id,
                                                         'estatus_contenido_id' => $estatus_contenido_id);
                $session->set('pages_setting', $pages_setting);
                return $this->redirectToRoute('_editPagina', array('pagina_padre_id' => $pagina_padre_id,
                                                                   'pagina_id' => 0));
            }
            else {

                // Se verifica si quedan sub-páginas a crear
                $pages_setting = $session->get('pages_setting');
                if ($pages_setting && $pages_setting != '')
                {
                    ksort($pages_setting);
                    $i = 0;
                    $page_master_id = 0;
                    foreach ($pages_setting as $page_id => $page_setting)
                    {
                        $i++;
                        if ($i == 1)
                        {
                            $page_master_id = $page_id;
                        }
                        $query = $em->createQuery('SELECT COUNT(p.id) FROM ActualidadComunBundle:EaPagina p 
                                                    WHERE p.pagina = :pagina_id')
                                    ->setParameter('pagina_id', $page_id);
                        $cantidad = $query->getSingleScalarResult();
                        if ($cantidad < $page_setting['total'])
                        {
                            $pages_setting[$page_id]['cantidad'] = $cantidad+1;
                            $session->set('pages_setting', $pages_setting);
                            return $this->redirectToRoute('_editPagina', array('pagina_padre_id' => $page_id,
                                                                               'pagina_id' => 0));
                            break;
                        }
                    }
                    return $this->redirectToRoute('_pagina', array('pagina_id' => $page_master_id));
                }
                else {
                    return $this->redirectToRoute('_pagina', array('pagina_id' => $pagina_padre_id ? $pagina_padre_id : $pagina->getId()));
                }

            }
            
        }

        $libros = array();
        if ($pagina->getEmpresa())
        {
            // Libros de referencia en caso de que el tipo de página sea Guía del profesor
            $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.empresa = :empresa_id 
                                        AND p.tipoPagina = :libro_alumno 
                                        AND p.pagina IS NULL 
                                        ORDER BY p.grado ASC, p.orden ASC')
                        ->setParameters(array('empresa_id' => $pagina->getEmpresa()->getId(),
                                              'libro_alumno' => $yml['parameters']['tipo_pagina']['libro_alumnos']));
            $libros = $query->getResult();
        }

        $query = $em->createQuery("SELECT tp FROM ActualidadComunBundle:EaTipoPagina tp 
                                    WHERE tp.id NOT IN (:libros)")
                    ->setParameter('libros', array($yml['parameters']['tipo_pagina']['libro_alumnos'], $yml['parameters']['tipo_pagina']['libro_profesores']));
        $tipos = $query->getResult();

        $status = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->findAll();

        if($pagina->getAyudaInteractivo())
        {
            $ayuda = 1;
        }else{
            $ayuda = 0;
        }
        
        return $this->render('ActualidadBackendBundle:Pagina:edit.html.twig', array('form' => $form->createView(),
                                                                                    'pagina' => $pagina,
                                                                                    'cantidad' => $cantidad,
                                                                                    'total' => $total,
                                                                                    'wizard' => $wizard,
                                                                                    'pagina_padre' => $pagina_padre,
                                                                                    'libros' => $libros,
                                                                                    'libro_profesores' => $yml['parameters']['tipo_pagina']['libro_profesores'],
                                                                                    'pagina_padre' => $pagina_padre,
                                                                                    'tipos' => $tipos,
                                                                                    'status' => $status,
                                                                                    'ayuda' => $ayuda));

    }

    public function paginaAction($pagina_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        // Limpiar sesión de pages_setting
        $session->set('pages_setting', '');

        $em = $this->getDoctrine()->getManager();
        $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

        $subpages = $em->getRepository('ActualidadComunBundle:EaPagina')->findBy(array('pagina' => $pagina_id),
                                                                                 array('orden' => 'ASC'));
        $subpaginas = $f->paginas($subpages);

        return $this->render('ActualidadBackendBundle:Pagina:pagina.html.twig', array('pagina' => $pagina,
                                                                                      'subpaginas' => $subpaginas));

    }

    public function ajaxGetPageAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pagina_id = $request->query->get('pagina_id');

        $pagina = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
        $tipos = $em->getRepository('ActualidadComunBundle:EaTipoPagina')->findAll();
        $status = $em->getRepository('ActualidadComunBundle:EaEstatusContenido')->findAll();

        $titulo = $pagina->getTitulo().' ('.$this->get('translator')->trans('Copia').')';

        $tipos_str = '';
        foreach ($tipos as $tipo)
        {
            if (!($pagina->getPagina() && ($tipo->getId() == $yml['parameters']['tipo_pagina']['libro_alumnos'] || $tipo->getId() == $yml['parameters']['tipo_pagina']['libro_profesores'])))
            {
                $selected = $tipo->getId() == $pagina->getTipoPagina()->getId() ? 'selected' : '';
                $tipos_str .= '<option value="'.$tipo->getId().'" '.$selected.'>'.$tipo->getNombre().'</option>';
            }
        }

        $status_str = '';
        foreach ($status as $status)
        {
            $selected = $status->getId() == $pagina->getEstatusContenido()->getId() ? 'selected' : '';
            $status_str .= '<option value="'.$status->getId().'" '.$selected.'>'.$status->getNombre().'</option>';
        }
        
        $return = array('titulo' => $titulo,
                        'tipos_str' => $tipos_str,
                        'status_str' => $status_str);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTreePaginasAction($pagina_id, Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

        $paginas_asociadas = array(); // Solo para pasar un arreglo vacío en el segundo en parámetro
        $subPaginas = $f->subPaginas($pagina->getId(), $paginas_asociadas, 1);

        $return = array();

        if ($subPaginas['tiene'] > 0)
        {
            $return[] = array('text' => $pagina->getTipoPagina()->getNombre().': '.$pagina->getTitulo(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right',
                              'children' => $subPaginas['return']);
        }
        else {
            $return[] = array('text' => $pagina->getTipoPagina()->getNombre().': '.$pagina->getTitulo(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right');
        }

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDuplicatePageAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina_id = $request->request->get('pagina_id');
        $titulo = $request->request->get('titulo');
        $tipo_pagina_id = $request->request->get('tipo_pagina_id');
        $estatus_contenido_id = $request->request->get('estatus_contenido_id');

        $return = $f->duplicarPagina($pagina_id, $titulo, $tipo_pagina_id, $estatus_contenido_id, $session->get('usuario')['id']);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function moverAction($pagina_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        
        $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

        if ($request->getMethod() == 'POST')
        {

            $pagina_padre_id = $request->request->get('pagina_padre_id');
            $pagina_padre = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_padre_id);

            // Reordenar su anterior grupo
            if ($pagina->getPagina())
            {
                $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                            WHERE p.pagina = :pagina_id 
                                            AND p.id != :id
                                            ORDER BY p.orden ASC")
                            ->setParameters(array('pagina_id' => $pagina->getPagina()->getId(),
                                                  'id' => $pagina_id));
            }
            else {
                $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                            WHERE p.pagina IS NULL 
                                            AND p.id != :id
                                            ORDER BY p.grado ASC, p.orden ASC")
                            ->setParameter('id', $pagina_id);
            }
            $paginas = $query->getResult();

            $orden = 0;
            foreach ($paginas as $p)
            {
                $orden++;
                $p->setOrden($orden);
                $em->persist($p);
                $em->flush();
            }

            // Quedará de último en el orden
            $query = $em->createQuery('SELECT MAX(p.orden) FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.pagina = :pagina_id')
                        ->setParameter('pagina_id', $pagina_padre_id);
            $orden = $query->getSingleScalarResult();
            $orden++;

            $pagina->setPagina($pagina_padre);
            $pagina->setOrden($orden);
            $em->persist($pagina);
            $em->flush();

            $libro_id = $f->paginaRaiz($pagina_padre);

            return $this->redirectToRoute('_paginaMovida', array('pagina_id' => $pagina->getId(), 'libro_id' => $libro_id));

        }

        $str = '';

        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('ActualidadComunBundle:EaPagina', 'p')
           ->andWhere('p.id != :me')
           ->andWhere('p.pagina IS NULL')
           ->orderBy('p.orden', 'ASC');
        $parametros['me'] = $pagina_id;
        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $pages = $query->getResult();

        $movimiento = array('pagina_id' => $pagina_id);
        $paginas_asociadas = array();
        foreach ($pages as $page)
        {
            $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getId().'" p_str="'.$page->getTipoPagina()->getNombre().': '.$page->getTitulo().'">'.$page->getTipoPagina()->getNombre().': '.$page->getTitulo();
            $subPaginas = $f->subPaginas($page->getId(), $paginas_asociadas, 0, $movimiento);
            if ($subPaginas['tiene'] > 0)
            {
                $str .= '<ul>';
                $str .= $subPaginas['return'];
                $str .= '</ul>';
            }
            $str .= '</li>';
        }

        return $this->render('ActualidadBackendBundle:Pagina:mover.html.twig', array('pagina' => $pagina,
                                                                                     'pagina_str' => $str));

    }

    public function paginaMovidaAction($pagina_id, $libro_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
        $paginas_asociadas[] = $pagina_id;
        
        $str = '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$libro->getId().'" p_str="'.$libro->getTipoPagina()->getNombre().': '.$libro->getTitulo().'">'.$libro->getTipoPagina()->getNombre().': '.$libro->getTitulo();
        $subPaginas = $f->subPaginas($libro->getId(), $paginas_asociadas);
        if ($subPaginas['tiene'] > 0)
        {
            $str .= '<ul>';
            $str .= $subPaginas['return'];
            $str .= '</ul>';
        }
        $str .= '</li>';

        return $this->render('ActualidadBackendBundle:Pagina:paginaMovida.html.twig', array('pagina_str' => $str));

    }

    public function ajaxCodigoInteractivoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina_id = $request->request->get('pagina_id');
        $codigo_interactivo = $request->request->get('codigo_interactivo');

        if ($pagina_id)
        {
            $query = $em->createQuery('SELECT COUNT(p.id) FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.codigoInteractivo = :codigo_interactivo AND 
                                        p.id != :pagina_id')
                        ->setParameters(array('codigo_interactivo' => $codigo_interactivo,
                                              'pagina_id' => $pagina_id));
        }
        else {
            $query = $em->createQuery('SELECT COUNT(p.id) FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.codigoInteractivo = :codigo_interactivo')
                        ->setParameter('codigo_interactivo', $codigo_interactivo);
        }
        $ok = $query->getSingleScalarResult();
                    
        $return = array('ok' => $ok);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function prelacionesAction()
    {

        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        // Empresas para el filtro
        $empresas = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->findAll();

        return $this->render('ActualidadBackendBundle:Pagina:prelaciones.html.twig', array('empresas' => $empresas));

    }

    public function ajaxGetUnidadesPreladasAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $pagina_padre_id = $request->query->get('pagina_padre_id');
        
        if ($pagina_padre_id)
        {
            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                        WHERE p.pagina = :pagina_id
                                        ORDER BY p.orden ASC")
                        ->setParameter('pagina_id', $pagina_padre_id);
            $pages = $query->getResult();

            // Estructura de las páginas
            $paginas = $f->paginas($pages);
        }
        else {
            $paginas = array();
        }

        $html = $this->renderView('ActualidadBackendBundle:Pagina:paginasPreladas.html.twig', array('paginas' => $paginas));
        
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxGetUnidadesPrelarAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $pagina_id = $request->query->get('pagina_id');
        $html = '<option value="">'.$this->get('translator')->trans('Sin prelación').'</option>';
        
        // Páginas hermanas para la prelación
        $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
        $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPagina p 
                                    WHERE p.pagina = :pagina_id 
                                    AND p.id != :id 
                                    ORDER BY p.orden ASC")
                    ->setParameters(array('pagina_id' => $pagina->getPagina()->getId(),
                                          'id' => $pagina_id));
        $hermanas = $query->getResult();

        foreach ($hermanas as $hermana)
        {
            $selected = $pagina->getPrelada() ? $pagina->getPrelada()->getId() == $hermana->getId() ? 'selected' : '' : '';
            $html .= '<option value="'.$hermana->getId().'" '.$selected.'>'.$hermana->getTitulo().'</option>';
        }
        
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxPrelarAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();

        $pagina_id = $request->request->get('pagina_id');
        $prelada = $request->request->get('prelada');

        $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
        $pagina_prelacion = $prelada ? $em->getRepository('ActualidadComunBundle:EaPagina')->find($prelada) : null;

        $pagina->setPrelada($pagina_prelacion);
        $em->persist($pagina);
        $em->flush();

        $html = $prelada ? $pagina->getTitulo().'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small class="text-muted">'.$this->get('translator')->trans('Prelada por').' '.$pagina->getPrelada()->getTitulo().'</small>' : $pagina->getTitulo();
                    
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDeletePaginaAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $pagina_id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;
        
        $pagina = $em->getRepository('ActualidadComunBundle:'.$entity)->find($pagina_id);

        // Eliminación en cascada de la estructura de la página
        $f->deletePaginas($pagina, $yml);

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function verInteractivoAction($pagina_id, Request $request)
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

        $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

        // Para las pruebas de comunicación entre el interactivo y la plataforma
        $prueba = array('make_ajax' => $yml['parameters']['serviciosActivos']['interactivo'],
                        'servidor_recursos' => $this->container->getParameter('servidor_recursos'));

        // Caso de prueba
        $caso = array('descripcion' => 'Simulación del interactivo en comunicación con la plataforma',
                      'usuario_id' => $session->get('usuario')['id'],
                      'url' => '_ajaxServicioInteractivo',
                      'make_ajax' => $yml['parameters']['serviciosActivos']['interactivo'],
                      'pagina_id' => $pagina_id);
        
        return $this->render('ActualidadBackendBundle:Pagina:verInteractivo.html.twig', array('pagina' => $pagina,
                                                                                              'caso' => $caso,
                                                                                              'servidor_recursos' => $this->container->getParameter('servidor_recursos')));
        
    }

}
