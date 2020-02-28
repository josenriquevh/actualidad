<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminGrado;
use Symfony\Component\HttpFoundation\JsonResponse;

class ForoController extends Controller
{
   public function indexAction(Request $request)
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

            $query = $em->createQuery("SELECT e FROM ActualidadComunBundle:AdminEmpresa e 
                                        WHERE e.activo = :activo 
                                        ORDER BY e.nombre ASC")
                        ->setParameter('activo', true);
            $empresas = $query->getResult();

            $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g ORDER BY g.id ASC");
            $grados = $query->getResult();

            $query = $em->createQuery("SELECT f FROM ActualidadComunBundle:EaTipoForo f ORDER BY f.nombre DESC");
            $tipoForo = $query->getResult();

            $query = $em->createQuery("SELECT f FROM ActualidadComunBundle:EaForo f
                                        WHERE   f.foro IS NULL
                                        ORDER BY f.fechaPublicacion DESC");
            $themes = $query->getResult();
            $temas= $f->retornarComentariosForo($themes);

            
            return $this->render('ActualidadBackendBundle:Foro:index.html.twig', array('empresas'=>$empresas,'grados'=>$grados,'tipoForo'=>$tipoForo,'usuario'=>$usuario,'temas'=>$temas));

    }

    public   function ajaxLibrosForoAction(Request $request)
    {
        
        try {
            
            $session = new Session();
            $f = $this->get('funciones');
            
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $f->setRequest($session->get('sesion_id'));
            $em = $this->getDoctrine()->getManager();

            $empresa_id = $request->request->get('empresa_id');
            $grado_id = $request->request->get('grado_id');
            $query = $em->createQuery("SELECT l FROM ActualidadComunBundle:EaPagina l 
                                        WHERE l.empresa = :empresa_id
                                        AND l.grado = :grado_id  
                                        AND l.pagina IS NULL
                                        ORDER BY l.id ASC")
                        ->setParameters(array('empresa_id' => $empresa_id, 'grado_id' => $grado_id));
            $libros = $query->getResult();
            $html = $this->renderView('ActualidadBackendBundle:Foro:optionsLibros.html.twig', array('opciones' => $libros));

            $return = json_encode(array('libros' => $html,
                                        'ok' => 1,
                                        'cnt' => count($libros)));
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage());
            return new JsonResponse($return);
        }

    }

    public   function ajaxUnidadesForoAction(Request $request)
    {
            try{
                $session = new Session();
                $f = $this->get('funciones');
                
                if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
                {
                    return $this->redirectToRoute('_loginAdmin');
                }
                $f->setRequest($session->get('sesion_id'));
                $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                $em = $this->getDoctrine()->getManager();
                
                $libro_id = $request->request->get('libro_id');
                $query = $em->createQuery("SELECT l FROM ActualidadComunBundle:EaPagina l 
                                            WHERE l.tipoPagina = :tipo_pagina
                                            AND l.pagina = :libro_id  
                                            ORDER BY l.id ASC")
                            ->setParameters(array('tipo_pagina' => $yml['parameters']['tipo_pagina']['unidad'], 'libro_id' => $libro_id ));
                $unidades = $query->getResult();
                $html = $this->renderView('ActualidadBackendBundle:Foro:optionsLibros.html.twig', array('opciones' => $unidades));

                $return = json_encode(array('unidades'=>$html,'ok'=>1,'cnt'=>count($unidades)));
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
           catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }
    }

    public function ajaxListaTemasAction(Request $request)
    {
            try{
                $session = new Session();
                $f = $this->get('funciones');
                
                if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
                {
                    return $this->redirectToRoute('_loginAdmin');
                }
                $f->setRequest($session->get('sesion_id'));
                $em = $this->getDoctrine()->getManager();

                $tipoForo_id = $request->request->get('tipo_foro_id');
                $unidad_id = $request->request->get('unidad_id');
                $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

                $query = $em->createQuery("SELECT f FROM ActualidadComunBundle:EaForo f
                                            WHERE f.tipoForo =:tipoForo_id
                                            AND   f.foro IS NULL
                                            AND   f.pagina =:unidad_id")
                            ->setParameters(array('tipoForo_id' => $tipoForo_id, 'unidad_id' => $unidad_id ));
                $themes = $query->getResult();
                $temas = $f->retornarComentariosForo($themes);
                
                $tabla1= $this->renderView('ActualidadBackendBundle:Foro:tablaTemas.html.twig', array('temas' => $temas,'usuario' =>$usuario));
                $tabla2= $this->renderView('ActualidadBackendBundle:Foro:tablaComentarios.html.twig', array('comentarios' => array(),'usuario' =>$usuario));

                $return = json_encode(array('temas'=>$tabla1,'comentarios'=>$tabla2,'ok'=>1));
                return new Response($return, 200, array('Content-Type' => 'application/json'));
            }
            catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }


    }

    public function ajaxListaComentariosAction(Request $request)
    {
         try{


            $session = new Session();
            $f = $this->get('funciones');
                
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
             {
                return $this->redirectToRoute('_loginAdmin');
             }
             $f->setRequest($session->get('sesion_id'));
             $em = $this->getDoctrine()->getManager();
             $foro_id = $request->request->get('foro_id');
             $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

             $query = $em->createQuery("SELECT f FROM ActualidadComunBundle:EaForo f
                                            WHERE f.foro =:foro_id")
                            ->setParameter('foro_id',$foro_id);
             $coments = $query->getResult();

             $comentarios = $f->retornarComentariosForo($coments,1);


            $html= $this->renderView('ActualidadBackendBundle:Foro:tablaComentarios.html.twig', array('comentarios' => $comentarios,'usuario' =>$usuario ));
            
            $return = json_encode(array('comentarios'=>$html,'ok'=>1));
                return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }

             

    }

     public function ajaxFilesForoListAction(Request $request)
    {
           
        try{
           $em = $this->getDoctrine()->getManager();
           $f = $this->get('funciones');
           $usuario_id = $request->request->get('usuario_id');
           $foro_id = $request->request->get('foro_id');
           $usuario = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
           $html ='';
           

           $consulta = $em->createQuery("SELECT file FROM ActualidadComunBundle:EaForoArchivo file
                                       WHERE file.foro = :foro_id
                                       AND file.usuario = :usuario_id
                                       ORDER BY file.id ASC")
                         ->setParameters(array('foro_id' => $foro_id, 'usuario_id'=> $usuario_id));
           $archivos = $consulta->getResult();
           
           if($archivos)
           {
              $total = count($archivos);
              $mod = $total%2;
              $filas = ($mod==0)? ($total/2):(($total-1)/2)+1;
              $e = 0;

             foreach ($archivos as $archivo) 
             {
                $ruta = $this->container->getParameter('folders')['uploads'].$archivo->getArchivo();
                $extension = explode('.',$archivo->getArchivo());
                $iconoExtension = $f->getWebDirectory().'/front/assets/img/'.$extension[1].'.svg';

                $html .= ($e%2==0)? ($e==0)? '<div class="row" style="margin-top: 15px;  border-bottom: 2px solid #EEE8E7;">':'</div><div class="row" style="margin-top: 15px; border-bottom: 2px solid #EEE8E7;">':'';

                $html .= '
                          <div class ="col-md-1" style="margin-bottom:5px"> <img src="'.$iconoExtension.'" width=35  height=35 > </div>
                          <div class ="col-md-7" style="margin-bottom:5px"><a href ="'.$ruta.'"  class="btn btn-link btn-sm " download>'.$archivo->getDescripcion().'</a></div>
                          ';

                $e++;
              
             }
             
             $html .= '</div>';

           }
           
           $usuario_id = $request->request->get('usuario_id');
           $foro_id = $request->request->get('foro_id');

           $return = array('html'=>$html,'usuario' => $usuario->getNombre().' '.$usuario->getApellido(),'ok'=>1);
           $return = json_encode($return);
           return new Response($return, 200, array('Content-Type' => 'application/json'));
       }
       catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }

    }

    


   

}