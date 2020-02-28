<?php

namespace Actualidad\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\JsonResponse;
use Actualidad\ComunBundle\Entity\EaPaginaLog;
use Actualidad\ComunBundle\Entity\EaPruebaLog;

class ContenidoController extends Controller
{
    public function unidadesAction($pagina_id)
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

        $libro = $session->get('paginas')[$pagina_id];

        $libro_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                              'usuario' => $session->get('usuario')['id']));

        if (!$libro_log)
        {
            $libro_bd = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            $estatus_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($yml['parameters']['estatus_pagina']['iniciada']);
            $libro_log = new EaPaginaLog();
            $libro_log->setPagina($libro_bd);
            $libro_log->setUsuario($usuario);
            $libro_log->setFechaInicio(new \DateTime('now'));
            $libro_log->setEstatusPagina($estatus_pagina);
            $libro_log->setPorcentajeAvance(0);
        }

        $libro_log->setFechaInteraccion(new \DateTime('now'));
        $em->persist($libro_log);
        $em->flush();

        $unidad_ids = array();
        $unidades = array();
        $unidad_reciente = array();

        foreach ($session->get('paginas')[$pagina_id]['subpaginas'] as $unidad)
        {

            $unidad_ids[] = $unidad['id'];

            $status = 0;
            $status_css = '';
            $button_text = '';
            $url = '_intro';
            $parametro_tema = 0;
            $pagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $unidad['id'],
                                                                                                   'usuario' => $session->get('usuario')['id']));

            if (!$pagina_log)
            {
                $avance = 0;
                $button_text = $this->get('translator')->trans('Iniciar');
                //$url = '_intro';
                if ($session->get('usuario')['rol_id'] == $yml['parameters']['rol']['alumno'])
                {
                    $pagina_unidad = $em->getRepository('ActualidadComunBundle:EaPagina')->find($unidad['id']);
                    $acceso_prelacion = $f->accesoPrelacion($pagina_unidad, $session->get('usuario')['id'], $yml);
                    if (!$acceso_prelacion['acceso'])
                    {
                        $status_css = 'blocked';
                        $url = '';
                    }
                }
            }
            else {
                $status = $pagina_log->getEstatusPagina()->getId();
                $avance = number_format($pagina_log->getPorcentajeAvance(), 0);
                if ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'])
                {
                    $status_css = 'completa';
                    $button_text = $this->get('translator')->trans('Completada');
                    //$url = '_intro';
                }
                else {
                    $button_text = $this->get('translator')->trans('Continuar');
                    //$url = '_temas';
                    $i = 0;
                    foreach ($session->get('paginas')[$pagina_id]['subpaginas'][$unidad['id']]['subpaginas'] as $tema)
                    {
                        $i++;
                        if ($i == 1)
                        {
                            $parametro_tema = $tema['id'];
                        }
                        $tema_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $tema['id'],
                                                                                                             'usuario' => $session->get('usuario')['id'],
                                                                                                             'estatusPagina' => $yml['parameters']['estatus_pagina']['iniciada']));
                        if ($tema_log)
                        {
                            $parametro_tema = $tema['id'];
                            break;
                        }
                    }
                }
            }

            $unidades[] = array('id' => $unidad['id'],
                                'orden' => $unidad['orden'],
                                'titulo' => $unidad['titulo'],
                                'status' => $status,
                                'status_css' => $status_css,
                                'button_text' => $button_text,
                                'avance' => $avance,
                                'url' => $url,
                                'parametro_tema' => $parametro_tema);

        }

        // La unidad más reciente
        if (count($unidad_ids))
        {

            $url_reciente = '_intro';
            $parametro_tema_reciente = 0;
            $status_reciente = 0;

            $query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPaginaLog pl 
                                        WHERE pl.pagina IN (:unidad_ids) 
                                        AND pl.usuario = :usuario_id 
                                        ORDER BY pl.fechaInteraccion DESC")
                        ->setParameters(array('unidad_ids' => $unidad_ids,
                                              'usuario_id' => $session->get('usuario')['id']));
            $pls = $query->getResult();

            if (!$pls)
            {
                // Se toma la primera unidad del libro
                $ur_id = $unidad_ids[0];
                $avance = 0;
                $button_text = $this->get('translator')->trans('Iniciar');
            }
            else {
                $ur_id = $pls[0]->getPagina()->getId();
                $avance = number_format($pls[0]->getPorcentajeAvance(), 0);
                $status_reciente = $pls[0]->getEstatusPagina()->getId();
                if ($pls[0]->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'])
                {
                    $button_text = $this->get('translator')->trans('Completada');
                }
                else {
                    $url_reciente = '_temas';
                    $button_text = $this->get('translator')->trans('Continuar');
                }
            }

            $temas = array();
            $orden = 1;
            $titulo = '';
            if (array_key_exists($ur_id, $session->get('paginas')[$pagina_id]['subpaginas']))
            {
                $orden = $session->get('paginas')[$pagina_id]['subpaginas'][$ur_id]['orden'];
                $titulo = $session->get('paginas')[$pagina_id]['subpaginas'][$ur_id]['titulo'];
                foreach ($session->get('paginas')[$pagina_id]['subpaginas'][$ur_id]['subpaginas'] as $tema)
                {
                    $tema_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $tema['id'],
                                                                                                         'usuario' => $session->get('usuario')['id']));
                    if ($tema_log)
                    {
                        if ($url_reciente == '_temas' && $tema_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['iniciada'] && $parametro_tema_reciente == 0)
                        {
                            $parametro_tema_reciente = $tema['id'];
                        }
                    }
                    $temas[] = array('id' => $tema['id'],
                                     'orden' => $tema['orden'],
                                     'titulo' => $tema['titulo'],
                                     'completado' => $tema_log ? $tema_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'] ? true : false: false);
                }
            }

            $unidad_reciente = array('id' => $ur_id,
                                     'orden' => $orden,
                                     'titulo' => $titulo,
                                     'avance' => $avance,
                                     'status_reciente' => $status_reciente,
                                     'button_text' => $button_text,
                                     'temas' => $temas,
                                     'url_reciente' => $url_reciente,
                                     'parametro_tema_reciente' => $parametro_tema_reciente);
            
        }

        return $this->render('ActualidadFrontendBundle:Contenido:unidades.html.twig', array('libro' => $libro,
                                                                                            'unidades' => $unidades,
                                                                                            'unidad_reciente' => $unidad_reciente,
                                                                                            'estatus_pagina_completada' => $yml['parameters']['estatus_pagina']['completada']));
        
    }

    public function introAction($unidad_id)
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

        $unidad = $em->getRepository('ActualidadComunBundle:EaPagina')->find($unidad_id);

        // Depuración del contenido
        $unidad->setContenido(strip_tags($unidad->getContenido(), "<iframe><video><source>"));

        if (!$unidad->getContenido() || $unidad->getContenido() == '')
        {
            return $this->redirectToRoute('_temas', array('unidad_id' => $unidad_id));
        }

        $libro_id = $f->paginaRaiz($unidad);
        $libro = $session->get('paginas')[$libro_id];

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

        // También se anexa a la indexación el libro
        $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
        $libro['padre'] = 0;
        $libro['sobrinos'] = 0;
        $libro['hijos'] = count($libro['subpaginas']);
        $libro['descripcion'] = $libro_bd->getDescripcion();
        $libro['contenido'] = $libro_bd->getContenido();
        $libro['next_subpage'] = 0;
        $indexedPages[$libro['id']] = $libro;

        //return new Response(var_dump($indexedPages));

        // Generar traza
        $logs = $f->startLesson($indexedPages, $unidad_id, $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['iniciada']);

        // Botón de Saltar Intro
        $skip_intro = 0;
        foreach ($indexedPages[$unidad_id]['subpaginas'] as $tema)
        {
            $query = $em->createQuery('SELECT COUNT(pl.id) FROM ActualidadComunBundle:EaPaginaLog pl
                                       WHERE pl.pagina = :pagina_id AND pl.usuario = :usuario_id')
                        ->setParameters(array('pagina_id' => $tema['id'],
                                              'usuario_id' => $session->get('usuario')['id']));
            $tiene_log = $query->getSingleScalarResult();
            if ($tiene_log)
            {
                $skip_intro = 1;
                break;
            }
        }

        return $this->render('ActualidadFrontendBundle:Contenido:intro.html.twig', array('unidad' => $unidad,
                                                                                         'libro' => $libro,
                                                                                         'skip_intro' => $skip_intro,
                                                                                         'make_ajax' => $yml['parameters']['serviciosActivos']['interactivo'],
                                                                                         'servidor_recursos' => $this->container->getParameter('servidor_recursos')));
        
    }

    public function temasAction($unidad_id, $tema_id, $evaluacion, $continue)
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

        $unidad = $em->getRepository('ActualidadComunBundle:EaPagina')->find($unidad_id);

        $libro_id = $f->paginaRaiz($unidad);
        $libro = $session->get('paginas')[$libro_id];

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

        // También se anexa a la indexación el libro
        $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
        $libro['padre'] = 0;
        $libro['sobrinos'] = 0;
        $libro['hijos'] = count($libro['subpaginas']);
        $libro['descripcion'] = $libro_bd->getDescripcion();
        $libro['contenido'] = $libro_bd->getContenido();
        $libro['next_subpage'] = 0;
        $indexedPages[$libro['id']] = $libro;

        //return new Response(var_dump($indexedPages));

        // Contenido principal
        $tema = 0;
        if ($tema_id)
        {
            $tema = $em->getRepository('ActualidadComunBundle:EaPagina')->find($tema_id);
        }
        elseif ($evaluacion) {
            return $this->redirectToRoute('_evaluacion', array('unidad_id' => $unidad_id));
        }
        elseif ($continue) {

            // Se busca el último tema con interacción
            $tema_ids = array();
            foreach ($indexedPages[$unidad_id]['subpaginas'] as $t)
            {
                $tema_ids[] = $t['id'];
            }

            $query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPaginaLog pl 
                                        WHERE pl.pagina IN (:tema_ids) 
                                        AND pl.usuario = :usuario_id 
                                        ORDER BY pl.fechaInteraccion DESC")
                        ->setParameters(array('tema_ids' => $tema_ids,
                                              'usuario_id' => $session->get('usuario')['id']));
            $pls = $query->getResult();

            foreach ($pls as $pl)
            {
                $tema = $pl->getPagina();
                break;
            }

        }
        
        if (!$tema)
        {

            // Primer tema por defecto
            foreach ($indexedPages[$unidad_id]['subpaginas'] as $t)
            {
                $tema = $em->getRepository('ActualidadComunBundle:EaPagina')->find($t['id']);
                break;
            }
        }

        // Construcción del menú
        $elementos = $f->menuTemas($indexedPages, $unidad_id, $session->get('usuario')['id'], $yml, $tema->getId(), $evaluacion);
        $sidebar_menu = $this->renderView('ActualidadFrontendBundle:Contenido:sidebarMenu.html.twig', array('elementos' => $elementos,
                                                                                                            'unidad_id' => $unidad_id));

        // Recursos con sus screenshots
        $recursos = $f->resourceScreenshots($indexedPages[$tema->getId()]['subpaginas'], $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);
        $cards = $this->renderView('ActualidadFrontendBundle:Contenido:cards.html.twig', array('recursos' => $recursos));

        // Generar traza del tema
        $logs = $f->startLesson($indexedPages, $tema->getId(), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['iniciada']);

        return $this->render('ActualidadFrontendBundle:Contenido:temas.html.twig', array('unidad' => $unidad,
                                                                                         'libro' => $libro,
                                                                                         'tema' => $tema,
                                                                                         'cards' => $cards,
                                                                                         'recursos_length' => count($recursos),
                                                                                         'temas_length' => count($indexedPages[$unidad_id]['subpaginas']),
                                                                                         'evaluacion' => $evaluacion,
                                                                                         'tiene_evaluacion' => $indexedPages[$unidad_id]['tiene_evaluacion'] ? 1 : 0,
                                                                                         'sidebar_menu' => $sidebar_menu,
                                                                                         'make_ajax' => $yml['parameters']['serviciosActivos']['interactivo'],
                                                                                         'servidor_recursos' => $this->container->getParameter('servidor_recursos')));
        
    }

    public function ajaxResourceAction(Request $request)
    {

        try {

            $em = $this->getDoctrine()->getManager();
            $session = new Session();
            $f = $this->get('funciones');

            if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
            {   
                $return = array('ok' => 3);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }

            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $pagina_id = $request->query->get('pagina_id');
            $total_recursos = $request->query->get('total_recursos');

            $pagina = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

            $libro_id = $f->paginaRaiz($pagina);
            $libro = $session->get('paginas')[$libro_id];

            // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
            $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

            // También se anexa a la indexación el libro
            $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
            $libro['padre'] = 0;
            $libro['sobrinos'] = 0;
            $libro['hijos'] = count($libro['subpaginas']);
            $libro['descripcion'] = $libro_bd->getDescripcion();
            $libro['contenido'] = $libro_bd->getContenido();
            $libro['next_subpage'] = 0;
            $indexedPages[$libro['id']] = $libro;
            
            $logs = $f->startLesson($indexedPages, $pagina->getId(), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['iniciada']);

            $pagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                                   'usuario' => $session->get('usuario')['id'],
                                                                                                   'estatusPagina' => $yml['parameters']['estatus_pagina']['completada']));

            // Depuración del contenido
            $pagina->setContenido(strip_tags($pagina->getContenido(), "<iframe><video><source>"));
            $uploads = $this->container->getParameter('folders')['uploads'];
            
            $return = array('ok' => 1,
                            'contenido' => $pagina->getContenido(),
                            'interactivo' => $pagina->getInteractivo() ? 1 : 0,
                            'counter' => $indexedPages[$pagina->getId()]['orden'].'/'.$total_recursos,
                            'recurso_actual' => $indexedPages[$pagina->getId()]['orden'],
                            'visto' => $pagina_log ? 1 : 0,
                            'gif' => $pagina->getAyudaInteractivo() ? $uploads.$pagina->getAyudaInteractivo()->getGif() : '');
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
        
    }

    public function ajaxFinishResourceAction(Request $request)
    {
        
        try {

            $session = new Session();
            $f = $this->get('funciones');
            if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
            {   
                $return = array('ok' => 3);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $em = $this->getDoctrine()->getManager();
        
            $pagina_id = $request->request->get('pagina_id');

            $ok = 1;
            $msg = '';

            $pagina = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

            $libro_id = $f->paginaRaiz($pagina);
            $libro = $session->get('paginas')[$libro_id];

            // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
            $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

            // También se anexa a la indexación el libro
            $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
            $libro['padre'] = 0;
            $libro['sobrinos'] = 0;
            $libro['hijos'] = count($libro['subpaginas']);
            $libro['descripcion'] = $libro_bd->getDescripcion();
            $libro['contenido'] = $libro_bd->getContenido();
            $libro['next_subpage'] = 0;
            $indexedPages[$libro['id']] = $libro;

            if (count($indexedPages[$pagina_id]['subpaginas']) < 1)
            {
                // No es una intro de una página que tiene sub-páginas. Finalizar la página.
                $log_id = $f->finishLesson($indexedPages, $pagina->getId(), $session->get('usuario')['id'], $yml);
                $msg = 'Log_id procesado: '.$log_id;
            }

            $return = array('ok' => $ok,
                            'msg' => $msg);

            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json',
                                                    'Access-Control-Allow-Origin' => $this->container->getParameter('servidor_recursos')));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
        

    }

    public function ajaxRefreshMenuAction(Request $request)
    {

        try {

            $em = $this->getDoctrine()->getManager();
            $session = new Session();
            $f = $this->get('funciones');
            if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
            {   
                $return = array('ok' => 3);
                $return = json_encode($return);
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $unidad_id = $request->query->get('unidad_id');
            $tema_id = $request->query->get('tema_id');
            $evaluacion = $request->query->get('evaluacion');

            $unidad = $em->getRepository('ActualidadComunBundle:EaPagina')->find($unidad_id);

            $libro_id = $f->paginaRaiz($unidad);
            $libro = $session->get('paginas')[$libro_id];

            // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
            $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

            // También se anexa a la indexación el libro
            $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
            $libro['padre'] = 0;
            $libro['sobrinos'] = 0;
            $libro['hijos'] = count($libro['subpaginas']);
            $libro['descripcion'] = $libro_bd->getDescripcion();
            $libro['contenido'] = $libro_bd->getContenido();
            $libro['next_subpage'] = 0;
            $indexedPages[$libro['id']] = $libro;

            // Construcción del menú
            $elementos = $f->menuTemas($indexedPages, $unidad_id, $session->get('usuario')['id'], $yml, $tema_id, $evaluacion);
            $sidebar_menu = $this->renderView('ActualidadFrontendBundle:Contenido:sidebarMenu.html.twig', array('elementos' => $elementos,
                                                                                                                'unidad_id' => $unidad_id));

            if (!$evaluacion)
            {
                // Recursos con sus screenshots
                $recursos = $f->resourceScreenshots($indexedPages[$tema_id]['subpaginas'], $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);
                $cards = $this->renderView('ActualidadFrontendBundle:Contenido:cards.html.twig', array('recursos' => $recursos));
            }
            else {
                $cards = '';
            }
            
            $return = array('ok' => 1,
                            'sidebar_menu' => $sidebar_menu,
                            'cards' => $cards);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
        
    }

    public function evaluacionAction($unidad_id)
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

        $unidad = $em->getRepository('ActualidadComunBundle:EaPagina')->find($unidad_id);

        $libro_id = $f->paginaRaiz($unidad);
        $libro = $session->get('paginas')[$libro_id];

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

        // También se anexa a la indexación el libro
        $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
        $libro['padre'] = 0;
        $libro['sobrinos'] = 0;
        $libro['hijos'] = count($libro['subpaginas']);
        $libro['descripcion'] = $libro_bd->getDescripcion();
        $libro['contenido'] = $libro_bd->getContenido();
        $libro['next_subpage'] = 0;
        $indexedPages[$libro['id']] = $libro;

        // Construcción del menú
        $elementos = $f->menuTemas($indexedPages, $unidad_id, $session->get('usuario')['id'], $yml, 0, 1);
        $sidebar_menu = $this->renderView('ActualidadFrontendBundle:Contenido:sidebarMenu.html.twig', array('elementos' => $elementos,
                                                                                                            'unidad_id' => $unidad_id));

        // En teoría solo se carga una sola pregunta para estos tipos de evaluaciones
        $pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->findOneBy(array('prueba' => $indexedPages[$unidad_id]['prueba_id'],
                                                                                            'tipoElemento' => $yml['parameters']['tipo_elemento']['interactivo']),
                                                                                      array('id' => 'DESC'));

        $opcion = 0;
        $codigo = '';
        $ok = 1;
        $msg1 = $this->get('translator')->trans('Has culminado los recursos correspondientes a esta unidad. Estás a punto de comenzar los ejercicios de comprobación de aprendizaje. Si quieres refrescar los contenidos de la unidad, puedes hacerlo en este momento.');
        $msg2 = $this->get('translator')->trans('Si ya estás listo para comenzar, pulsa el botón "Comenzar".');
        $parametros = array();
        $prueba_log_id = 0;
        $pregunta_id = 0;
        $correctas = '';
        $incorrectas = '';
        $intentos = 0;
        $max_intentos = $pregunta ? $pregunta->getPrueba()->getMaxIntentos() : $yml['parameters']['max_intentos'];
        $min_correctas = $pregunta ? $pregunta->getPrueba()->getMinCorrectas() : $yml['parameters']['min_correctas'];

        if ($pregunta)
        {

            $pregunta_id = $pregunta->getId();
            $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findOneByPregunta($pregunta->getId());

            // Depuración del contenido
            $opcion = $pregunta_opcion->getOpcion();
            $opcion->setDescripcion(strip_tags($opcion->getDescripcion(), "<iframe><video><source>"));
            $codigo = $pregunta->getCodigoInteractivo();

            if (!$opcion->getDescripcion() || $opcion->getDescripcion() == '')
            {
                $msg1 = $this->get('translator')->trans('No existe el recurso de los ejercicios para la comprobación de aprendizaje').'.';
                $msg2 = '';
                $ok = 0;
            }

        }
        else {
            $msg1 = $this->get('translator')->trans('No existe el recurso de los ejercicios para la comprobación de aprendizaje').'.';
            $msg2 = '';
            $ok = 0;
        }

        if ($ok)
        {

            // Intentos a esta prueba
            $query = $em->createQuery("SELECT pl FROM ActualidadComunBundle:EaPruebaLog pl 
                                        WHERE pl.prueba = :prueba_id 
                                        AND pl.usuario = :usuario_id 
                                        ORDER BY pl.id DESC")
                        ->setParameters(array('prueba_id' => $indexedPages[$unidad_id]['prueba_id'],
                                              'usuario_id' => $session->get('usuario')['id']));
            $pls = $query->getResult();

            // Condición inicial
            if ($pls)
            {
                $correctas = $pls[0]->getPreguntasCorrectas() ? $pls[0]->getPreguntasCorrectas() : '';
                $incorrectas = $pls[0]->getPreguntasErradas() ? $pls[0]->getPreguntasErradas() : '';
                $intentos = count($pls);
                if ($pls[0]->getEstado() == $yml['parameters']['estado_prueba']['curso'])
                {
                    $prueba_log_id = $pls[0]->getId();
                    $intentos -= 1;
                }
                elseif ($pls[0]->getEstado() == $yml['parameters']['estado_prueba']['aprobado'])
                {
                    // Redirección a la pantalla de unidades
                    return $this->redirectToRoute('_unidades', array('pagina_id' => $libro_id));
                }
                else {
                    $prueba_log_id = 0;
                }
            }

            if (!$prueba_log_id)
            {
                if ($intentos < $max_intentos)
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
            }

            // Para el postMessage
            $parametros = array('prueba_log_id' => $prueba_log_id,
                                'pregunta_id' => $pregunta_id,
                                'correctas' => $correctas,
                                'incorrectas' => $incorrectas,
                                'intentos' => $intentos,
                                'make_ajax' => $yml['parameters']['serviciosActivos']['evaluacion'],
                                'url' => '_ajaxServicioEvaluacion',
                                'max_intentos' => $max_intentos,
                                'min_correctas' => $min_correctas);

        }

        return $this->render('ActualidadFrontendBundle:Contenido:evaluacion.html.twig', array('unidad' => $unidad,
                                                                                              'libro' => $libro,
                                                                                              'sidebar_menu' => $sidebar_menu,
                                                                                              'opcion' => $opcion,
                                                                                              'codigo' => $codigo,
                                                                                              'parametros' => $parametros,
                                                                                              'ok' => $ok,
                                                                                              'msg1' => $msg1,
                                                                                              'msg2' => $msg2,
                                                                                              'servidor_recursos' => $this->container->getParameter('servidor_recursos')));
        
    }

    public function ajaxTryEvaluacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $pregunta_id = $request->request->get('pregunta_id');
        $intentos = $request->request->get('intentos');
        $usuario_id = $request->request->get('usuario_id');

        $ok = 1;

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
            $ok = 0;
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
            $prueba_log->setPreguntasErradas($incorrectas);
            $prueba_log->setPreguntasCorrectas($correctas);
            $em->persist($prueba_log);
            $em->flush();
            $prueba_log_id = $prueba_log->getId();
        }

        $return = array('ok' => $ok,
                        'prueba_log_id' => $prueba_log_id,
                        'correctas' => $correctas,
                        'incorrectas' => $incorrectas);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function procesarEvaluacionAction($prueba_log_id)
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

        $prueba_log = $em->getRepository('ActualidadComunBundle:EaPruebaLog')->find($prueba_log_id);

        $unidad = $prueba_log->getPrueba()->getPagina();

        $libro_id = $f->paginaRaiz($unidad);
        $libro = $session->get('paginas')[$libro_id];

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$libro_id]);

        // También se anexa a la indexación el libro
        $libro_bd = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
        $libro['padre'] = 0;
        $libro['sobrinos'] = 0;
        $libro['hijos'] = count($libro['subpaginas']);
        $libro['descripcion'] = $libro_bd->getDescripcion();
        $libro['contenido'] = $libro_bd->getContenido();
        $libro['next_subpage'] = 0;
        $indexedPages[$libro['id']] = $libro;

        // Cantidad de intentos
        $query = $em->createQuery('SELECT COUNT(pl.id) FROM ActualidadComunBundle:EaPruebaLog pl
                                   WHERE pl.prueba = :prueba_id 
                                   AND pl.usuario = :usuario_id')
                    ->setParameters(array('prueba_id' => $prueba_log->getPrueba()->getId(),
                                          'usuario_id' => $prueba_log->getUsuario()->getId()));
        $intentos = $query->getSingleScalarResult();

        if ($prueba_log->getEstado() == $yml['parameters']['estado_prueba']['aprobado'] || $intentos >= $prueba_log->getPrueba()->getMaxIntentos())
        {
            
            // Estatus de la página Completada
            $pagina_log = $em->getRepository('ActualidadComunBundle:EaPaginaLog')->findOneBy(array('usuario' => $prueba_log->getUsuario()->getId(),
                                                                                                   'pagina' => $unidad->getId()));
            $status_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($yml['parameters']['estatus_pagina']['completada']);
            $pagina_log->setFechaFin(new \DateTime('now'));
            $pagina_log->setPorcentajeAvance(100);
            $pagina_log->setEstatusPagina($status_pagina);
            $pagina_log->setFechaInteraccion(new \DateTime('now'));
            $em->persist($pagina_log);
            $em->flush();

            // Cálculo del porcentaje de avance de toda la línea de ascendente
            $f->calculoAvance($indexedPages, $unidad->getId(), $prueba_log->getUsuario()->getId(), $yml);

            // Notificación de alumno completó los ejercicios de la unidad
            if ($pagina_log->getUsuario()->getRol()->getId() == $yml['parameters']['rol']['alumno'])
            {
                $profesor_alumno = $em->getRepository('ActualidadComunBundle:EaProfesorAlumno')->findByAlumno($pagina_log->getUsuario()->getId());
                foreach ($profesor_alumno as $pa)
                {
                    $nombre = ucwords(mb_strtolower($pa->getAlumno()->getNombre(), 'UTF-8'));
                    $apellido = ucwords(mb_strtolower($pa->getAlumno()->getApellido(), 'UTF-8'));
                    $descripcion = $nombre.' '.$apellido.' '.$this->get('translator')->trans('completó la Unidad').' '.$indexedPages[$unidad->getId()]['orden'].' '.$this->get('translator')->trans('del libro').' '.$indexedPages[$libro_id]['titulo'].' '.$indexedPages[$libro_id]['descripcion_grado'].'.';
                    $f->newAlarm($yml['parameters']['tipo_alarma']['unidad_completada'], $descripcion, $pa->getProfesor(), $unidad->getId());
                }
            }

        }

        return $this->redirectToRoute('_unidades', array('pagina_id' => $libro_id));
        
    }

}
