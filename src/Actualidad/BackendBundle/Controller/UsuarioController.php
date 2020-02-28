<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminUsuario;

class UsuarioController extends Controller
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

        $query = "SELECT u FROM ActualidadComunBundle:AdminUsuario u ";

        $query = $em->createQuery($query);
        $usuariosdb = $query->getResult();

        foreach ($usuariosdb as $usuario)
        {
            if($usuario->getRol()->getId() == $yml['parameters']['rol']['administrador'])
            {
                $usuarios[] = array('id' => $usuario->getId(),
                                    'nombre' => $usuario->getNombre(),
                                    'user' => $usuario->getLogin(),
                                    'fechaRegistro' => $usuario->getFechaCreacion()->format('d/m/Y'),
                                    'delete_disabled' => $f->linkEliminar($usuario->getId(),'AdminUsuario'));
            }
        }

        return $this->render('ActualidadBackendBundle:Usuario:index.html.twig', array('usuarios' => $usuarios));
    }     

    public function registroAdministradorAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if ($usuario_id) 
        {
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
        }
        else {
            $usuario = new AdminUsuario();
            $usuario->setFechaCreacion(new \DateTime('now'));
        }
       

        if ($request->getMethod() == 'POST')
        {

            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $foto = $request->request->get('foto');
            $login = strtolower($request->request->get('login'));
            $clave = $request->request->get('clave');
            $cambiar = $request->request->get('cambiar');
            $correo = $request->request->get('correo');
            $fecha_nacimiento = $request->request->get('fecha_nacimiento');
            $activo = $request->request->get('activo');

            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setLogin($login);
            if (!$usuario_id || $cambiar)
            {
                $usuario->setClave($clave);
            }
            $usuario->setCorreo($correo);
            $usuario->setActivo($activo ? true : false);
            if ($fecha_nacimiento){
            $fn_array = explode("/", $fecha_nacimiento);
            $d = $fn_array[0];
            $m = $fn_array[1];
            $a = $fn_array[2];
            $fecha_nacimiento = "$a-$m-$d";
            $usuario->setFechaNacimiento(new \DateTime($fecha_nacimiento));
            }else{
                $fecha_nacimiento= null;
                $usuario->setFechaNacimiento($fecha_nacimiento);
            }
            if($foto){      
                $usuario->setFoto($foto);
            }else{
                $foto=null;
                $usuario->setFoto($foto);
            }
            $rol =  $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminRol')->find($yml['parameters']['rol']['administrador']);
            $usuario->setRol($rol);
            $em->persist($usuario);
            $em->flush();

            return $this->redirectToRoute('_showUsuario', array('usuario_id' => $usuario->getId()));

        }
        
        return $this->render('ActualidadBackendBundle:Usuario:registroAdministrador.html.twig', array('usuario' => $usuario));

    }

    public function ajaxValidLoginAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $login = strtolower($request->request->get('login'));
        
        $query = $em->createQuery('SELECT COUNT(u.id) FROM ActualidadComunBundle:AdminUsuario u 
                                    WHERE u.login = :login')
                    ->setParameter('login', $login);
        $ok = $query->getSingleScalarResult();
                    
        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function showUsuarioAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
        

        return $this->render('ActualidadBackendBundle:Usuario:show.html.twig', array('usuario' => $usuario));

    }

    public function pruebaAction(){

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $pagina_id= 2;
        $grado_id=2;
        $pex=$this->get('phpexcel');

        $query = $em->createQuery('SELECT pu FROM ActualidadComunBundle:EaPaginaUsuario pu 
                                    WHERE pu.pagina = :pagina_id')
                    ->setParameter('pagina_id', $pagina_id);
        $codigos = $query->getResult();

        $excel = $f->ExcelCodigos($codigos,$pagina_id,$grado_id,$yml,$pex);

        return new Response('generado');

    }

}