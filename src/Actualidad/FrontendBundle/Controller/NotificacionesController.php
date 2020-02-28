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

class NotificacionesController extends Controller
{
    public function notificacionesFrontAction(Request $request)
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
        $notificaciones = array();
        $paginas_total = array();
        
        $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                  WHERE a.usuario = :usuario_id 
                                  AND a.fechaCreacion <= :hoy 
                                  ORDER BY a.fechaCreacion DESC')
                    ->setMaxResults(10)
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                          'hoy' => date('Y-m-d H:i:s')));
        $notificaciones_db = $query->getResult();

        foreach($notificaciones_db as $notificacion)
        {
            $notificacion->setLeido(TRUE);
            $em->persist($notificacion);
            $em->flush();
        }
        
        $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                  WHERE a.usuario = :usuario_id 
                                  AND a.fechaCreacion <= :hoy 
                                  ORDER BY a.fechaCreacion DESC')
                    ->setMaxResults(10)
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                        'hoy' => date('Y-m-d H:i:s')));
        $notificaciones_pantalla = $query->getResult();

        $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                  WHERE a.usuario = :usuario_id 
                                  AND a.fechaCreacion <= :hoy 
                                  ORDER BY a.fechaCreacion DESC')
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                        'hoy' => date('Y-m-d H:i:s')));
        $notificaciones_total = $query->getResult();
        
        $paginas = count($notificaciones_total)/10;
        
        if(is_float($paginas))
        {
            $entero = explode(".",$paginas);
            $paginas_total = $entero[0] + 1;
        }        

        foreach($notificaciones_pantalla as $notificacion)
        {
            $notificaciones[] = array('id' => $notificacion->getId(),
                                      'descripcion' => $notificacion->getDescripcion(),
                                      'fecha' => $notificacion->getFechaCreacion()->format('d/m/Y'));
        }

        return $this->render('ActualidadFrontendBundle:Notificaciones:index.html.twig', array('notificaciones' => $notificaciones,
                                                                                              'paginas' => $paginas_total));
        
    }

    public function ajaxPaginadorAction(Request $request)
    {
        try{

            $em = $this->getDoctrine()->getManager();
            $f = $this->get('funciones');
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $session = new Session();
            if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                //return $this->redirectToRoute('_authException', array('tipo' => 'sesion'));
                //return $this->redirectToRoute('_index');
            }
            $f->setRequest($session->get('sesion_id'));

            $offset = $request->query->get('offset');
            $notificaciones = array();

            $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                    WHERE a.usuario = :usuario_id 
                                    AND a.fechaCreacion <= :hoy 
                                    ORDER BY a.fechaCreacion DESC')
                        ->setFirstResult($offset)
                        ->setMaxResults(10)
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                            'hoy' => date('Y-m-d H:i:s')));
            $notificaciones_db = $query->getResult();

            foreach($notificaciones_db as $notificacion)
            {
                $notificacion->setLeido(TRUE);
                $em->persist($notificacion);
                $em->flush();
            }
            
            $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                    WHERE a.usuario = :usuario_id 
                                    AND a.fechaCreacion <= :hoy 
                                    ORDER BY a.fechaCreacion DESC')
                        ->setFirstResult($offset)
                        ->setMaxResults(10)
                        ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                            'hoy' => date('Y-m-d H:i:s')));
            $notificaciones_pantalla = $query->getResult();

            //return new response ($paginas);

            foreach($notificaciones_pantalla as $notificacion)
            {
                $notificaciones[] = array('id' => $notificacion->getId(),
                                        'descripcion' => $notificacion->getDescripcion(),
                                        'fecha' => $notificacion->getFechaCreacion()->format('d/m/Y'));

                $html = $this->renderView('ActualidadFrontendBundle:Notificaciones:tablaNotificaciones.html.twig', array('notificaciones' => $notificaciones));
            }

            //return new response($html);

            $ok = 1;
            $return = array('ok' => 1,
                            'html' => $html,
                            'notificaciones' => $notificaciones,
                            'offset' => $offset);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

            
        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }
    }

    public function notificacionesDeleteAction(Request $request)
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

        $notificaciones = array();
        $paginas_total = array();
        //return new response($request->getMethod());
        if ($request->getMethod() == 'POST')
        {
            
            $notificaciones_id = $request->request->get('recordarme');
            
            foreach($notificaciones_id as $notificacion_id)
            {
                $notificacion_db = $em->getRepository('ActualidadComunBundle:AdminAlarma')->find($notificacion_id);

                $em->remove($notificacion_db);
                $em->flush();
                
            }
        }

        $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                  WHERE a.usuario = :usuario_id 
                                  AND a.fechaCreacion <= :hoy 
                                  ORDER BY a.id DESC')
                    ->setMaxResults(10)
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                          'hoy' => date('Y-m-d H:i:s')));
        $notificaciones_db = $query->getResult();

        foreach($notificaciones_db as $notificacion)
        {
            $notificacion->setLeido(TRUE);
            $em->persist($notificacion);
            $em->flush();
        }
        
        $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                  WHERE a.usuario = :usuario_id 
                                  AND a.fechaCreacion <= :hoy 
                                  ORDER BY a.id DESC')
                    ->setMaxResults(10)
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                        'hoy' => date('Y-m-d H:i:s')));
        $notificaciones_pantalla = $query->getResult();

        $query = $em->createQuery('SELECT a FROM ActualidadComunBundle:AdminAlarma a
                                  WHERE a.usuario = :usuario_id 
                                  AND a.fechaCreacion <= :hoy 
                                  ORDER BY a.id DESC')
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                        'hoy' => date('Y-m-d H:i:s')));
        $notificaciones_total = $query->getResult();
        
        $paginas = count($notificaciones_total)/10;
        
        if(is_float($paginas))
        {
            $entero = explode(".",$paginas);
            $paginas_total = $entero[0] + 1;
        }        

        foreach($notificaciones_pantalla as $notificacion)
        {
            $notificaciones[] = array('id' => $notificacion->getId(),
                                      'descripcion' => $notificacion->getDescripcion(),
                                      'fecha' => $notificacion->getFechaCreacion()->format('d/m/Y'));
        }

        return $this->render('ActualidadFrontendBundle:Notificaciones:index.html.twig', array('notificaciones' => $notificaciones,
                                                                                              'paginas' => $paginas_total));


    }
}
