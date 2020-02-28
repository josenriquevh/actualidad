<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminNoticia;

class NoticiaController extends Controller
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
        $noticias = array();

        $query = "SELECT n FROM ActualidadComunBundle:AdminNoticia n ";

        $query = $em->createQuery($query);
        $noticiasdb = $query->getResult();

        foreach ($noticiasdb as $noticia)
        {
            $noticias[] = array('id' => $noticia->getId(),
                                'tipoNoticia' => $noticia->getTipoNoticia()->getNombre(),
                                'titulo' => $noticia->getTitulo(),
                                'fechaRegistro' => $noticia->getFechaCreacion()->format('d/m/Y'),
                                'delete_disabled' => $f->linkEliminar($noticia->getId(),'AdminNoticia'));
        }

        return $this->render('ActualidadBackendBundle:Noticia:index.html.twig', array('noticias' => $noticias));
    } 

    public function registroNoticiaAction($noticia_id, Request $request)
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
        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $query = $em->createQuery('SELECT tn FROM ActualidadComunBundle:AdminTipoNoticia tn');
        $tipoNoticias = $query->getResult();

        if ($noticia_id)
        {
            $noticia = $em->getRepository('ActualidadComunBundle:AdminNoticia')->find($noticia_id);
        }
        else {
            $noticia = new AdminNoticia();
            $noticia->setFechaCreacion(new \DateTime('now'));
        }

        if ($request->getMethod() == 'POST')
        {

            $tipo_noticia_id = $request->request->get('tipo_noticia_id');
            $tipoNoticia = $em->getRepository('ActualidadComunBundle:AdminTipoNoticia')->find($tipo_noticia_id);

            $titulo = trim($request->request->get('titulo'));

            $fecha_vencimiento = trim($request->request->get('fecha_vencimiento'));
            $fv = explode("/", $fecha_vencimiento);
            $vencimiento = $fv[2].'-'.$fv[1].'-'.$fv[0];
            
            $fecha_publicacion = trim($request->request->get('fecha_publicacion'));
            $fp = explode("/", $fecha_publicacion);
            $publicacion = $fp[2].'-'.$fp[1].'-'.$fp[0];

            $pdf = trim($request->request->get('pdf'));
            $imagen = trim($request->request->get('imagen'));

            $contenido = trim($request->request->get('contenido'));
            $resumen = trim($request->request->get('resumen'));

            $noticia->setUsuario($usuario);
            $noticia->setTipoNoticia($tipoNoticia);
            $noticia->setTitulo($titulo);
            $noticia->setPdf($pdf);
            $noticia->setImagen($imagen);
            $noticia->setResumen($resumen);
            $noticia->setContenido($contenido);
            $noticia->setFechaVencimiento(new \DateTime($vencimiento));
            $noticia->setFechaPublicacion(new \DateTime($publicacion));
            $em->persist($noticia);
            $em->flush();

            $query = $em->createQuery('SELECT u FROM ActualidadComunBundle:AdminUsuario u
                                       WHERE u.activo = :activo')
                        ->setParameter('activo' , true);
            $usuarios = $query->getResult();

            

           /*foreach($usuarios as $usuario){

                if ($tipoNoticia->getId() == $yml['parameters']['tipo_noticias']['noticia'] ) {
                   
                   $descripcion= 'Ha sido publicado una nueva noticia:  '. $titulo;
                   $f->newAlarm($yml['parameters']['tipo_alarma']['noticia'], $descripcion, $usuario, $noticia->getId(), $noticia->getFechaPublicacion()); 
                }
                elseif ($tipoNoticia->getId() == $yml['parameters']['tipo_noticias']['novedad'] ) {
                    
                    $descripcion= 'Ha sido publicado una nueva novedad:  '. $titulo;
                    $f->newAlarm($yml['parameters']['tipo_alarma']['novedad'], $descripcion, $usuario, $noticia->getId(), $noticia->getFechaPublicacion()); 
                }
                
            }*/

            return $this->redirectToRoute('_showNovedad', array('noticia_id' => $noticia->getId()));

        }
        
        return $this->render('ActualidadBackendBundle:Noticia:registroNoticia.html.twig', array('tipoNoticias' => $tipoNoticias,
                                                                                                'noticia' => $noticia));
    }

    public function mostrarNoticiaNovedadAction($noticia_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $noticia = $em->getRepository('ActualidadComunBundle:AdminNoticia')->find($noticia_id);
        
        return $this->render('ActualidadBackendBundle:Noticia:mostrarNoticia.html.twig', array('noticia' => $noticia));

    }

}