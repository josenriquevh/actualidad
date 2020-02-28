<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Actualidad\ComunBundle\Entity\AdminAyudaInteractivo; 
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;
use Actualidad\ComunBundle\Model\UploadHandler;


class AyudaInteractivoController extends Controller
{
   public function indexAction()
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }

       return $this->render('ActualidadBackendBundle:AyudaInteractivo:index.html.twig' );

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

    protected function deleteOrphanFilesAyuda($directorio,$objeto)
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
                if($objeto->getGif() != $archivos[$i])
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
            rename($pathInicio.$nombreArchivo,$pathTutorial.$nombreArchivo);
        }

        return 0;
    }
    
    public function ajaxUpdateAyudaInteractivoAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'].'ayuda/';
        $em = $this->getDoctrine()->getManager();


        $ayuda_interactivo_id = $request->request->get('ayuda_interactivo_id');
        $nombre = $request->request->get('nombre');
        $gif = $request->request->get('gif');
        $gif = 'ayuda/'.$gif;
        $mensaje = $request->request->get('mensaje');
       
        if ($ayuda_interactivo_id)
        {
            $ayuda_interactivo = $em->getRepository('ActualidadComunBundle:AdminAyudaInteractivo')->find($ayuda_interactivo_id);
        }
        else {
            $ayuda_interactivo = new AdminAyudaInteractivo();
        }
        
        $ayuda_interactivo->setNombre($nombre);
        $ayuda_interactivo->setGif($gif);

        $ayuda_interactivo->setMensaje($mensaje);
        $em->persist($ayuda_interactivo);
        $em->flush();

        
  
        $return = array('id' => $ayuda_interactivo->getId(),
                        'nombre' => $ayuda_interactivo->getNombre(),
                        'gif' => $ayuda_interactivo->getGif(),
                        'mensaje' => $ayuda_interactivo->getMensaje());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }


    public function ajaxEditAyudaInteractivoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $ayuda_interactivo_id = $request->query->get('ayuda_interactivo_id');
                
        $tutorial = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminAyudaInteractivo')->find($ayuda_interactivo_id);

        $return = array('nombre' => $tutorial->getNombre(),
                        'gif' => $tutorial->getGif(),
                        'mensaje'=>$tutorial->getMensaje());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxRefreshTableAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $data = ['data'=>[]];
        $ruta = $this->container->getParameter('folders')['uploads'];
        $f = $this->get('funciones');

        $query = $em->createQuery('SELECT ai FROM ActualidadComunBundle:AdminAyudaInteractivo ai
                                    ORDER BY ai.id ASC');
        $ayudainteractivos = $query->getResult();
                
        foreach ($ayudainteractivos as $ayuda)
        {
            $enlaceVideo = '<a href="'.$ruta.$ayuda->getGif().'" target="_blank">'.$ayuda->getGif().' </a>';
            $acciones = '<td class="center" >
                            <a href="#" title="'.$this->get('translator')->trans('Editar').'"  class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$ayuda->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm delete" data="'.$ayuda->getId().'" data-ubicacion="1"><span class="fa fa-trash"></span></a>
                        </td>';
            array_push($data['data'],[$ayuda->getNombre(),$enlaceVideo,$ayuda->getMensaje(),$acciones]);
        }

        $return = json_encode($data);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDeleteAyudaInteractivoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');

        $ok = 1;
        $object = $em->getRepository('ActualidadComunBundle:AdminAyudaInteractivo')->find($id);
        
        $em->remove($object);
        $em->flush();
        

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxUploadFileAyudaInteractivoAction(Request $request)
    {
        $session = new Session();
        $auxTut=$request->request->get('ayuda_interactivo_id');
        $ayuda_interactivo_id = ($auxTut>0) ? $auxTut.'/' : ''; 

        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'].'ayuda/';
        $uploads = $this->container->getParameter('folders')['uploads'].'ayuda/';
        $upload_dir = $dir_uploads;
        $upload_url = $uploads;
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);

        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}
