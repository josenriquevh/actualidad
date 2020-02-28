<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Actualidad\ComunBundle\Entity\AdminTutorial; 
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;
use Actualidad\ComunBundle\Model\UploadHandler;


class TutorialController extends Controller
{
   public function indexAction()
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        

       return $this->render('ActualidadBackendBundle:Tutorial:index.html.twig' );

    }

    protected function cleanRootDirectory($directorio,$removeDir=false)
    {
        /*
            Elimina los archivos dentro del directorio indicado, sin consultar si estan registrados en la 
            base de datos, cuando el argumento: removeDir es true se procede a borrar la carpeta una vez
            se encuentre vacía. Se utiliza cuando se elimina un tutorial del sistema y para limpiar 
            el directorio : recursos/tutoriales/ luego de realizar una insercion. elimina la carpeta thumbnail
        */

        $existe = file_exists($directorio);

        if($existe)
        {

            $archivos = scandir($directorio);
            
            for ($i=0; $i <count($archivos) ; $i++) 
            { 
                if(!is_dir($directorio.'/'.$archivos[$i]) )
                {
                  unlink($directorio.'/'.$archivos[$i]);
                }
                else
                {
                    if ($archivos[$i] == 'thumbnail') {
                        $thumbnails = scandir($directorio.'/'.$archivos[$i].'/');
                        for ($j=2; $j<count($thumbnails) ; $j++) { 
                            unlink($directorio.'/'.$archivos[$i].'/'.$thumbnails[$j]);
                        }
                        rmdir($directorio.'/'.$archivos[$i]);
                    }
                }
            }

            if ($removeDir) {
                rmdir($directorio);
            }

        }

        return 0;

    }

    protected function deleteOrphanFilesTutorial($directorio,$objeto)
    {
        /*
            Elimina los archivos dentro de una carpeta de un tutorial, que no se encuentren 
            registrados en la base de datos. Se utiliza al momento de modificar un tutorial, para evitar 
            que queden archivos huerfanos luego de modificar el tutorial.
        */
        
        $archivos = scandir($directorio);
        for ($i=0; $i <count($archivos); $i++) 
        { 
            if (!is_dir($directorio.'/'.$archivos[$i])) {
                if($objeto->getPdf() != $archivos[$i] && $objeto->getImagen() != $archivos[$i] && $objeto->getVideo() != $archivos[$i])
                {
                    unlink($directorio.'/'.$archivos[$i]);
                }
            }
            else {
                if ($archivos[$i]=='thumbnail') 
                {
                    $thumbnails=scandir($directorio.'/'.$archivos[$i].'/');
                    for ($j=2; $j <count($thumbnails) ; $j++) 
                    { 
                        unlink($directorio.'/'.$archivos[$i].'/'.$thumbnails[$j]);
                    }
                    rmdir($directorio.'/'.$archivos[$i]);
                }
            }
        }

        return 0;

    }
    
    protected function moverArchivo($pathInicio,$pathTutorial,$nombreArchivo)
    {
        if ($nombreArchivo != '') 
        {
            $existe = file_exists($pathInicio.$nombreArchivo);
            if($existe)
            {
                rename($pathInicio.$nombreArchivo,$pathTutorial.$nombreArchivo);
            }
        }

        return 0;
    }

    public function ajaxRefreshTableAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $data = ['data'=>[]];
        $ruta = $this->container->getParameter('folders')['uploads'].'tutoriales/';
        $f = $this->get('funciones');

        $query = $em->createQuery('SELECT t FROM ActualidadComunBundle:AdminTutorial t
                                    ORDER BY t.id ASC');
        $tutoriales = $query->getResult();
                
        foreach ($tutoriales as $tutorial)
        {
            $enlacePdf = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getPdf().'" target="_blank">'.$tutorial->getPdf().' </a>';
            $enlaceVideo = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getVideo().'" target="_blank">'.$tutorial->getVideo().' </a>';
            $enlaceImagen = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getImagen().'" target="_blank">'.$tutorial->getImagen().' </a>';
            $acciones = '<td class="center" >
                            <a href="'.$this->generateUrl('_registroTutorial', array( 'tutorial_id' => $tutorial->getId())).'" title="'.$this->get('translator')->trans('Editar').'"  class="btn btn-link btn-sm"><span class="fa fa-pencil"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm delete" data="'.$tutorial->getId().'" data-ubicacion="1"><span class="fa fa-trash"></span></a>
                        </td>';
            array_push($data['data'],[$tutorial->getId(),$tutorial->getNombre(),$enlacePdf,$enlaceVideo,$enlaceImagen,$acciones]);
        }

        $return = json_encode($data);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDeleteTutorialAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $directorio = $this->container->getParameter('folders')['dir_uploads'].'tutoriales/'.$id;

        $ok = 1;
        $object = $em->getRepository('ActualidadComunBundle:AdminTutorial')->find($id);
        $em->remove($object);
        $em->flush();
        $this->cleanRootDirectory($directorio,true);

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxUploadFileTutorialAction(Request $request)
    {
        $session = new Session();
        $auxTut=$request->request->get('tutorial_id');
        $tutorial_id = ($auxTut>0) ? $auxTut.'/' : ''; 

        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
        $uploads = $this->container->getParameter('folders')['uploads'];
        $upload_dir = $dir_uploads.'tutoriales/'.$tutorial_id;
        $upload_url = $uploads.'tutoriales/'.$tutorial_id;
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);

        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function registroTutorialAction($tutorial_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'].'tutoriales';
        $uploads = $this->container->getParameter('folders')['uploads'].'tutoriales';
        $ruta= '';
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        if ($tutorial_id)
        {
            $tutorial = $em->getRepository('ActualidadComunBundle:AdminTutorial')->find($tutorial_id);
            $ruta = $uploads.'/'.$tutorial_id.'/';
            
        }
        else {
            $tutorial = new AdminTutorial();
        }

        if ($request->getMethod() == 'POST')
        {
            $nombre = trim($request->request->get('nombre'));
            $descripcion = trim($request->request->get('descripcion'));
            $imagen = trim($request->request->get('imagen'));
            $pdf = trim($request->request->get('pdf'));
            $video = trim($request->request->get('video'));

            $tutorial->setUsuario($usuario);
            $tutorial->setNombre($nombre);
            $tutorial->setDescripcion($descripcion);
            $tutorial->setPdf($pdf);
            $tutorial->setVideo($video);
            $tutorial->setImagen($imagen);

            $em->persist($tutorial);
            $em->flush();

            if (!$tutorial_id)//si es nuevo
            {
                // Hacer el movimiento de archivos en caso de que sea nuevo tutorial
                mkdir($dir_uploads.'/'.$tutorial->getId(),0777);
                $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getPdf());
                $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getImagen());
                $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getVideo());
                $this->cleanRootDirectory($dir_uploads);//elimina los archivos huerfanos       
            }
            else{
                $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getPdf());
                $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getImagen());
                $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getVideo());
                $this->cleanRootDirectory($dir_uploads);//elimina los archivos huerfanos 

                $this->deleteOrphanFilesTutorial($dir_uploads.'/'.$tutorial_id,$tutorial);//elimina los archivos huerfanos de la carpeta del tutorial
            }

            return $this->redirectToRoute('_showTutorial', array('tutorial_id' => $tutorial->getId()));

        }
        //return new response (var_dump($ruta));
        return $this->render('ActualidadBackendBundle:Tutorial:registroTutorial.html.twig', array('tutorial' => $tutorial,
                                                                                                  'ruta' => $ruta));
    }

    public function mostrarTutorialAction($tutorial_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        
        $f->setRequest($session->get('sesion_id'));
        $dir_uploads = $this->container->getParameter('folders')['uploads'].'tutoriales';
        $ruta= '';

        $em = $this->getDoctrine()->getManager();

        $tutorial = $em->getRepository('ActualidadComunBundle:AdminTutorial')->find($tutorial_id);

        $ruta = $dir_uploads.'/'.$tutorial_id.'/';
        
        return $this->render('ActualidadBackendBundle:Tutorial:mostrarTutorial.html.twig', array('tutorial' => $tutorial,
                                                                                                 'ruta' => $ruta));

    }

}
