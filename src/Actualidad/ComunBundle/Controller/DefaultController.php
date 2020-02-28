<?php

namespace Actualidad\ComunBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use Actualidad\ComunBundle\Model\UploadHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\EaRespuesta;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ActualidadComunBundle:Default:index.html.twig');
    }

    public function logoutAction($ruta)
    {

    	$session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        if ($session->get('sesion_id'))
        {

            $sesion = $em->getRepository('ActualidadComunBundle:AdminSesion')->find($session->get('sesion_id'));
            if ($sesion)
            {

                $usuario_id = $session->get('usuario')['id'];
                $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);

                // Borra la cookie que almacena la sesión del usuario logueado
                if(isset($_COOKIE['id_usuario'])) 
                {
                    $usuario->setCookies(null);
                    $em->persist($usuario);
                    $em->flush();

                    setcookie('id_usuario', '', time() - 42000, '/'); 
                    setcookie('marca_aleatoria_usuario', '', time() - 42000, '/');
                }

                // Borra la cookie que almacena la sesión del usuario logueado en el frontend
                if(isset($_COOKIE['id_usuario_front'])) 
                {
                    $usuario->setCookies(null);
                    $em->persist($usuario);
                    $em->flush();

                    setcookie('id_usuario_front', '', time() - 42000, '/'); 
                    setcookie('cookie_front', '', time() - 42000, '/');
                }

                $sesion->setDisponible(false);
                $em->persist($sesion);
                $em->flush();
                $f->setRequest($session->get('sesion_id'));
                
            }

        }
        
        $session->invalidate();
        $session->clear();
        
        return $this->redirectToRoute($ruta);

    }

    public function ajaxUploadAction(Request $request)
    {
        
        // Parámetro adicional
        $base_upload = $request->request->get('base_upload');      

        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
        $uploads = $this->container->getParameter('folders')['uploads'];
        $upload_dir = $dir_uploads.$base_upload;
        $upload_url = $uploads.$base_upload;
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);

        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxServicioInteractivoAction(Request $request)
    {
        
        try {

            $f = $this->get('funciones');
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $em = $this->getDoctrine()->getManager();
        
            $codigo = trim($request->request->get('codigo'));
            $visto = $request->request->get('visto');
            $usuario_id = $request->request->get('usuario_id');
            $pagina_id = $request->request->get('pagina_id');

            $ok = 1;
            $msg = $this->get('translator')->trans('Parámetros recibidos').': codigo='.$codigo.', visto='.$visto.', usuario_id='.$usuario_id.', pagina_id='.$pagina_id;

            if ($yml['parameters']['serviciosActivos']['interactivo'])
            {

                if (isset($codigo) && isset($visto) && isset($usuario_id) && isset($pagina_id))
                {
                    if ($visto && ($visto == 1 || $visto == '1'))
                    {

                        /* OJO: SE COMENTA TODO EL BLOQUE PARA NO DEPENDER DEL CODIGO DEL INTERACTIVO, SINO CON EL PARAMETRO pagina_id */
                        /*$recursos = $em->getRepository('ActualidadComunBundle:EaPagina')->findByCodigoInteractivo($codigo);

                        if (!$recursos)
                        {
                            $ok = 0;
                            $msg = $this->get('translator')->trans('Código').' '.$codigo.' '.$this->get('translator')->trans('inexistente');
                        }
                        else {

                            $ok = 0;
                            $msg = $this->get('translator')->trans('Código').' '.$codigo.' '.$this->get('translator')->trans('no válido para este usuario');
                            foreach ($recursos as $recurso)
                            {
                                if ($recurso->getId() == $pagina_id)
                                {
                                    $libro_id = $f->paginaRaiz($recurso);
                                    $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
                                    $tipo_pagina_id = $libro->getTipoPagina()->getId();
                                    $ok = 1;
                                    $msg = '';
                                    $pagina_recurso = $recurso;
                                    break;
                                }
                            }

                            if ($ok)
                            {

                                // Estructura de páginas que tengan códigos generados
                                $empresas = $f->sistemaEmpresas($yml['parameters']['sistema_empresas']);
                                $paginas = $f->paginasUsuario($usuario_id, $tipo_pagina_id, $empresas, $yml);

                                // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
                                $indexedPages = $f->indexPages($paginas[$libro_id]);

                                // También se anexa a la indexación la página padre
                                $pagina = $paginas[$libro_id];
                                $pagina['padre'] = 0;
                                $pagina['sobrinos'] = 0;
                                $pagina['hijos'] = count($pagina['subpaginas']);
                                $indexedPages[$pagina['id']] = $pagina;

                                if (count($indexedPages[$pagina_recurso->getId()]['subpaginas']) < 1)
                                {
                                    // No es una intro de una página que tiene sub-páginas. Finalizar la página.
                                    $log_id = $f->finishLesson($indexedPages, $pagina_recurso->getId(), $usuario_id, $yml);
                                    $msg = 'Log_id procesado: '.$log_id;
                                }

                            }

                        }*/

                        $pagina_recurso = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

                        if (!$pagina_recurso)
                        {
                            $ok = 0;
                            $msg = $this->get('translator')->trans('Recurso').' '.$pagina_id.' '.$this->get('translator')->trans('inexistente');
                        }
                        else {

                            $libro_id = $f->paginaRaiz($pagina_recurso);
                            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
                            $tipo_pagina_id = $libro->getTipoPagina()->getId();
                            $ok = 1;
                            $msg = '';

                            // Estructura de páginas que tengan códigos generados
                            $empresas = $f->sistemaEmpresas($yml['parameters']['sistema_empresas']);
                            $paginas = $f->paginasUsuario($usuario_id, $tipo_pagina_id, $empresas, $yml);

                            // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
                            $indexedPages = $f->indexPages($paginas[$libro_id]);

                            // También se anexa a la indexación la página padre
                            $pagina = $paginas[$libro_id];
                            $pagina['padre'] = 0;
                            $pagina['sobrinos'] = 0;
                            $pagina['hijos'] = count($pagina['subpaginas']);
                            $indexedPages[$pagina['id']] = $pagina;

                            if (count($indexedPages[$pagina_recurso->getId()]['subpaginas']) < 1)
                            {
                                // No es una intro de una página que tiene sub-páginas. Finalizar la página.
                                $log_id = $f->finishLesson($indexedPages, $pagina_recurso->getId(), $usuario_id, $yml);
                                $msg = 'Log_id procesado: '.$log_id;
                            }

                        }


                    }
                }
                else {
                    $ok = 0;
                    $msg = $this->get('translator')->trans('Parámetros "codigo", "visto", "usuario_id" y/o "pagina_id" no definidos');
                }

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

    public function ajaxServicioEvaluacionAction(Request $request)
    {
        
        try {

            $f = $this->get('funciones');
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $em = $this->getDoctrine()->getManager();
        
            $codigo = trim($request->request->get('codigo'));
            $visto = $request->request->get('visto');
            $prueba_log_id = $request->request->get('prueba_log_id');
            $pregunta_id = $request->request->get('pregunta_id');
            $correctas = $request->request->get('correctas');
            $incorrectas = $request->request->get('incorrectas');
            $nro = 1; // Siempre será la pregunta 1 para una evaluación de este tipo

            $ok = 1;
            $msg = $this->get('translator')->trans('Parámetros recibidos').': codigo='.$codigo.', visto='.$visto.', prueba_log_id='.$prueba_log_id.', pregunta_id='.$pregunta_id.', correctas='.$correctas.', incorrectas='.$incorrectas;

            if ($yml['parameters']['serviciosActivos']['evaluacion'])
            {

                if (isset($codigo) && isset($visto) && isset($prueba_log_id) && isset($pregunta_id) && isset($correctas) && isset($incorrectas))
                {
                    if ($visto && ($visto == 1 || $visto == '1'))
                    {

                        
                        $prueba_log = $em->getRepository('ActualidadComunBundle:EaPruebaLog')->find($prueba_log_id);
                        /* OJO: SE COMENTA TODO EL BLOQUE PARA NO DEPENDER DEL CODIGO DEL INTERACTIVO, SINO CON EL PARAMETRO pregunta_id */
                        /*$preguntas = $em->getRepository('ActualidadComunBundle:EaPregunta')->findByCodigoInteractivo($codigo);

                        if (!$preguntas)
                        {
                            $ok = 0;
                            $msg = $this->get('translator')->trans('Código').' '.$codigo.' '.$this->get('translator')->trans('inexistente');
                        }
                        else {

                            $ok = 0;
                            $msg = $this->get('translator')->trans('Código').' '.$codigo.' '.$this->get('translator')->trans('no válido para esta evaluación');
                            foreach ($preguntas as $p)
                            {
                                if ($p->getId() == $pregunta_id)
                                {
                                    $ok = 1;
                                    $msg = '';
                                    $pregunta = $p;
                                    break;
                                }
                            }

                            if ($ok)
                            {

                                $respuesta = $em->getRepository('ActualidadComunBundle:EaRespuesta')->findOneBy(array('pruebaLog' => $prueba_log_id,
                                                                                                                      'nro' => $nro,
                                                                                                                      'pregunta' => $pregunta_id));

                                if (!$respuesta)
                                {
                                    $respuesta = new EaRespuesta();
                                    $respuesta->setNro($nro);
                                    $respuesta->setPregunta($pregunta);
                                    $respuesta->setPruebaLog($prueba_log);
                                    $respuesta->setFechaCreacion(new \DateTime('now'));
                                }

                                if ($correctas == '' && $incorrectas == '')
                                {
                                    // No contestó
                                    $respuesta->setOpcion(null);
                                    $em->persist($respuesta);
                                    $em->flush();
                                }
                                else {

                                    // Se guardan las nuevas respuestas
                                    $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findOneByPregunta($pregunta_id);
                                    $respuesta->setFechaCreacion(new \DateTime('now'));
                                    $respuesta->setOpcion($pregunta_opcion->getOpcion());
                                    $em->persist($respuesta);
                                    $em->flush();

                                }

                                $correctas_arr = explode(",", $correctas);
                                $erradas_arr = explode(",", $incorrectas);

                                if (count($erradas_arr) == 0 && count($correctas_arr) != 0)
                                {
                                    $nota = 100;
                                    $estado = $yml['parameters']['estado_prueba']['aprobado'];
                                }
                                else {
                                    $total = count($correctas_arr) + count($erradas_arr);
                                    if ($total != 0)
                                    {
                                        $nota = (count($correctas_arr)*100)/$total;
                                        $nota = round($nota, 2, PHP_ROUND_HALF_UP);
                                        if ($nota < $yml['parameters']['ponderacion']['porcentaje_aprueba'])
                                        {
                                            $estado = $yml['parameters']['estado_prueba']['reprobado'];
                                        }
                                        else {
                                            $estado = $yml['parameters']['estado_prueba']['aprobado'];
                                        }
                                    }
                                    else {
                                        $nota = 0;
                                        $estado = $yml['parameters']['estado_prueba']['reprobado'];
                                    }
                                }

                                $prueba_log->setFechaFin(new \DateTime('now'));
                                $prueba_log->setPorcentajeAvance(100);
                                $prueba_log->setCorrectas(count($correctas_arr));
                                $prueba_log->setErradas(count($erradas_arr));
                                $prueba_log->setNota($nota);
                                $prueba_log->setEstado($estado);
                                $prueba_log->setPreguntasErradas($incorrectas);
                                $prueba_log->setPreguntasCorrectas($correctas);
                                $em->persist($prueba_log);
                                $em->flush();

                            }

                        }*/

                        $pregunta = $em->getRepository('ActualidadComunBundle:EaPregunta')->find($pregunta_id);

                        if (!$pregunta)
                        {
                            $ok = 0;
                            $msg = $this->get('translator')->trans('Pregunta').' '.$pregunta_id.' '.$this->get('translator')->trans('inexistente');
                        }
                        else {

                            $ok = 1;
                            $msg = '';
                            $estado = '';
                            $correctas_arr = array();
                            $erradas_arr = array();

                            $respuesta = $em->getRepository('ActualidadComunBundle:EaRespuesta')->findOneBy(array('pruebaLog' => $prueba_log_id,
                                                                                                                  'nro' => $nro,
                                                                                                                  'pregunta' => $pregunta_id));

                            if (!$respuesta)
                            {
                                $respuesta = new EaRespuesta();
                                $respuesta->setNro($nro);
                                $respuesta->setPregunta($pregunta);
                                $respuesta->setPruebaLog($prueba_log);
                                $respuesta->setFechaCreacion(new \DateTime('now'));
                            }

                            if ($correctas == '' && $incorrectas == '')
                            {
                                // No contestó
                                $respuesta->setOpcion(null);
                                $em->persist($respuesta);
                                $em->flush();
                                $estado = $yml['parameters']['estado_prueba']['reprobado'];
                                $nota = 0;
                            }
                            else {

                                // Se guardan las nuevas respuestas
                                $pregunta_opcion = $em->getRepository('ActualidadComunBundle:EaPreguntaOpcion')->findOneByPregunta($pregunta_id);
                                $respuesta->setFechaCreacion(new \DateTime('now'));
                                $respuesta->setOpcion($pregunta_opcion->getOpcion());
                                $em->persist($respuesta);
                                $em->flush();

                            }

                            if ($estado == '')
                            {

                                $correctas_arr = explode(",", $correctas);
                                $erradas_arr = explode(",", $incorrectas);

                                if (count($erradas_arr) == 0 && count($correctas_arr) != 0)
                                {
                                    $nota = 100;
                                    $estado = $yml['parameters']['estado_prueba']['aprobado'];
                                }
                                else {
                                    $total = count($correctas_arr) + count($erradas_arr);
                                    if ($total != 0)
                                    {
                                        $nota = (count($correctas_arr)*100)/$total;
                                        $nota = round($nota, 2, PHP_ROUND_HALF_UP);
                                        $min_correctas = $prueba_log->getPrueba()->getMinCorrectas() ? $prueba_log->getPrueba()->getMinCorrectas() : 0;
                                        if (count($correctas_arr) < $min_correctas)
                                        {
                                            $estado = $yml['parameters']['estado_prueba']['reprobado'];
                                        }
                                        else {
                                            $estado = $yml['parameters']['estado_prueba']['aprobado'];
                                        }
                                    }
                                    else {
                                        $nota = 0;
                                        $estado = $yml['parameters']['estado_prueba']['reprobado'];
                                    }
                                }

                            }

                            $prueba_log->setFechaFin(new \DateTime('now'));
                            $prueba_log->setPorcentajeAvance(100);
                            $prueba_log->setCorrectas(count($correctas_arr));
                            $prueba_log->setErradas(count($erradas_arr));
                            $prueba_log->setNota($nota);
                            $prueba_log->setEstado($estado);
                            $prueba_log->setPreguntasErradas($incorrectas);
                            $prueba_log->setPreguntasCorrectas($correctas);
                            $em->persist($prueba_log);
                            $em->flush();

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
                                                                                                                       'pagina' => $prueba_log->getPrueba()->getPagina()->getId()));
                                $status_pagina = $em->getRepository('ActualidadComunBundle:EaEstatusPagina')->find($yml['parameters']['estatus_pagina']['completada']);
                                $pagina_log->setFechaFin(new \DateTime('now'));
                                $pagina_log->setPorcentajeAvance(100);
                                $pagina_log->setEstatusPagina($status_pagina);
                                $pagina_log->setFechaInteraccion(new \DateTime('now'));
                                $em->persist($pagina_log);
                                $em->flush();

                                $libro_id = $f->paginaRaiz($prueba_log->getPrueba()->getPagina());
                                $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
                                $tipo_pagina_id = $libro->getTipoPagina()->getId();
                                
                                // Estructura de páginas que tengan códigos generados
                                $empresas = $f->sistemaEmpresas($yml['parameters']['sistema_empresas']);
                                $paginas = $f->paginasUsuario($prueba_log->getUsuario()->getId(), $tipo_pagina_id, $empresas, $yml);

                                // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
                                $indexedPages = $f->indexPages($paginas[$libro_id]);

                                // También se anexa a la indexación la página padre
                                $pagina = $paginas[$libro_id];
                                $pagina['padre'] = 0;
                                $pagina['sobrinos'] = 0;
                                $pagina['hijos'] = count($pagina['subpaginas']);
                                $indexedPages[$pagina['id']] = $pagina;

                                // Cálculo del porcentaje de avance de toda la línea de ascendente
                                $f->calculoAvance($indexedPages, $prueba_log->getPrueba()->getPagina()->getId(), $prueba_log->getUsuario()->getId(), $yml);

                                // Notificación de alumno aprobó la evaluación de la unidad
                                if ($prueba_log->getEstado() == $yml['parameters']['estado_prueba']['aprobado'] && 
                                    $pagina_log->getUsuario()->getRol()->getId() == $yml['parameters']['rol']['alumno'])
                                {
                                    $profesor_alumno = $em->getRepository('ActualidadComunBundle:EaProfesorAlumno')->findByAlumno($pagina_log->getUsuario()->getId());
                                    foreach ($profesor_alumno as $pa)
                                    {
                                        $nombre = ucwords(mb_strtolower($pa->getAlumno()->getNombre(), 'UTF-8'));
                                        $apellido = ucwords(mb_strtolower($pa->getAlumno()->getApellido(), 'UTF-8'));
                                        $descripcion = $nombre.' '.$apellido.' '.$this->get('translator')->trans('aprobó la prueba de la Unidad').' '.$indexedPages[$pagina_log->getPagina()->getId()]['orden'].' '.$this->get('translator')->trans('del libro').' '.$indexedPages[$libro_id]['titulo'].' '.$indexedPages[$libro_id]['descripcion_grado'].'.';
                                        $f->newAlarm($yml['parameters']['tipo_alarma']['evaluacion_aprobada'], $descripcion, $pa->getProfesor(), $pagina_log->getPagina()->getId());
                                    }
                                }

                            }

                        }

                    }
                }
                else {
                    $ok = 0;
                    $msg = $this->get('translator')->trans('Parámetros "codigo", "visto", "prueba_log_id", "pregunta_id", "correctas" y/o "incorrectas" no definidos');
                }

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

    public function authExceptionAction($tipo)
    {

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        switch ($tipo) {
            case 'sesion':
                $mensaje = array('principal' => $this->get('translator')->trans('La sesión ha expirado'),
                                 'indicaciones' => $this->get('translator')->trans('Por favor, ingresa nuevamente con tu usuario y contraseña.'));
                $boton = '<a href="'.$this->generateUrl('_index').'" class="btn btn-primary text-uppercase">'.$this->get('translator')->trans('Ingresar').'</a>';
                $imagen = 'front/dist/img/icon-mens-sesion.svg';
                break;
            
            case 'domain':
                $mensaje = array('principal' => $this->get('translator')->trans('IP no válida'),
                                 'indicaciones' => $this->get('translator')->trans('Estás ingresando desde una IP fuera de Venezuela, Argentina o Dominicana.'));
                $boton = '';
                $imagen = 'front/dist/img/icon-mens-info.svg';
                break;

        }

        return $this->render('ActualidadComunBundle:Default:authException.html.twig', array('mensaje' => $mensaje,
                                                                                            'imagen' => $imagen,
                                                                                            'boton' => $boton,
                                                                                            'servidor_mantenimiento' => $yml['parameters']['servidor_mantenimiento']));

    }

}
