<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    public function indexAction()
    {

    	$em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}
        $f->setRequest($session->get('sesion_id'));

        // ESTADÍSTICAS BÁSICAS PARA EL DASHBOARD
        $query = $em->getConnection()->prepare('SELECT
                                                fnactivos() as
                                                resultado;');
        $query->execute();
        $r = $query->fetchAll();

        // La respuesta viene separada por __
        $activos = array();
        $r_arr = explode("__", $r[0]['resultado']);
        $activos['colegios'] = (int) $r_arr[0];
        $activos['profesores'] = (int) $r_arr[1];
        $activos['alumnos'] = (int) $r_arr[2];

        // Lista de provincias
        $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:AdminProvincia p 
                                    ORDER BY p.nombre ASC');
        $provincias = $query->getResult();

        // Lista de grados
        $grados = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminGrado')->findAll();

        return $this->render('ActualidadBackendBundle:Default:index.html.twig', array('activos' => $activos,
                                                                                      'provincias' => $provincias,
                                                                                      'grados' => $grados));

    }

    public function loginAction(Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $verificacion = '';
        $em = $this->getDoctrine()->getManager();

        //validamos que exista una cookie
        if($_COOKIE && isset($_COOKIE["id_usuario"]))
        {
            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario"],
                                                                                                 'cookies' => $_COOKIE["marca_aleatoria_usuario"] ) );
            
            if ($usuario)
            {
                $recordar_datos = 1;
                $login = $usuario->getLogin();
                $clave = $usuario->getClave(); 
                $verificacion = 1;
            }
            else {
                $error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
            }
        }
        else {
            if ($request->getMethod() == 'POST')
            {
                $recordar_datos = $request->request->get('recordar');
                $login = $request->request->get('usuario');
                $clave = $request->request->get('clave');
                $verificacion = 1;
            }
        }

        if ($verificacion)
        {
            $iniciarSesion = $f->iniciarSesionAdmin(array('recordar_datos' => $recordar_datos,
                                                          'login' => $login,
                                                          'clave' => $clave,
                                                          'yml' => $yml['parameters']));

            if($iniciarSesion['exito'] == true)
            {
                return $this->redirectToRoute('_inicioAdmin');
            }
            else {
                $response = $this->render('ActualidadBackendBundle:Default:login.html.twig', array('error' => $iniciarSesion['error'] )); 
                return $response;
            }                    
        }
        else {
            $response = $this->render('ActualidadBackendBundle:Default:login.html.twig', array('error' => $error)); 
            return $response;
        }
            
    }

    public function ajaxDeleteAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;

        $object = $em->getRepository('ActualidadComunBundle:'.$entity)->find($id);
        $em->remove($object);
        $em->flush();
        $return = array('ok' => $ok);
        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxActiveAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;

        $object = $em->getRepository('ActualidadComunBundle:'.$entity)->find($id);
        $active = $object->getActivo() ? false : true;
        $object->setActivo($active);
        $em->persist($object);
        $em->flush();
        
        $return = array('ok' => $ok);
        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxGetSelectAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->query->get('id');
        $entity = $request->query->get('entity');
        $reference = $request->query->get('reference');
        $orderBy = $request->query->get('orderBy');
        
        $query = $em->createQuery('SELECT e FROM ActualidadComunBundle:'.$entity.' e 
                                    WHERE e.'.$reference.' = :id
                                    ORDER BY e.'.$orderBy.' ASC')
                    ->setParameter('id', $id);
        $objects = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($objects as $object)
        {
            $options .= '<option value="'.$object->getId().'">'.$object->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxGetSelectPaginaAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $prueba_id = $request->query->get('prueba_id');
        $entity = $request->query->get('entity');
        $orderBy = $request->query->get('orderBy');

        $pagina_evaluada = 0;
        $nombre = '';
        $prueba_id = $prueba_id == '' ? 0 : $prueba_id;
        
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('ActualidadComunBundle:'.$entity, 'p')
           ->andWhere('p.empresa = :empresa_id')
           ->orderBy('p.grado', 'ASC')
           ->orderBy('p.'.$orderBy, 'ASC');
        $parametros['empresa_id'] = $empresa_id;
        
        if ($pagina_id != "")
        {
            $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:'.$entity.' p 
                                        WHERE p.empresa = :empresa_id 
                                        AND p.pagina = :pagina_id 
                                        ORDER BY p.grado ASC, p.tipoPagina ASC, p.'.$orderBy.' ASC')
                        ->setParameters(array('empresa_id' => $empresa_id,
                                              'pagina_id' => $pagina_id));
        }
        else {
            $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:'.$entity.' p 
                                        WHERE p.empresa = :empresa_id 
                                        AND p.pagina IS NULL 
                                        ORDER BY p.grado ASC, p.tipoPagina ASC, p.'.$orderBy.' ASC')
                        ->setParameter('empresa_id', $empresa_id);
        } 
        $objects = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($objects as $object)
        {
            if ($pagina_id != "")
            {

                // Es una unidad. Se verifica si tiene prueba asociada
                $qb = $em->createQueryBuilder();
                $qb->select('COUNT(p.id)')
                   ->from('ActualidadComunBundle:EaPrueba', 'p')
                   ->where('p.pagina = :pagina_id');
                $parameters['pagina_id'] = $object->getId();
                if ($prueba_id)
                {
                    $qb->andWhere('p.id != :prueba_id');
                    $parameters['prueba_id'] = $prueba_id;
                }
                $qb->setParameters($parameters);
                $query = $qb->getQuery();
                $pagina_evaluada = $query->getSingleScalarResult();

                $nombre = $object->getTitulo();
            }
            else {
                $nombre = $object->getGrado()->getNombre().' '.$this->get('translator')->trans('Grado').' - '.$object->getTitulo().' - '.$object->getTipoPagina()->getNombre();
            }
            if (!$pagina_evaluada)
            {
                $options .= '<option value="'.$object->getId().'">'.$nombre.'</option>';
            }
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxOrderAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $orden = $request->request->get('orden');

        $object = $em->getRepository('ActualidadComunBundle:'.$entity)->find($id);
        $object->setOrden($orden);
        $em->persist($object);
        $em->flush();
                    
        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEstadisticasColegiosAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $provincia_id = $request->query->get('provincia_id');
        $ciudad_id = $request->query->get('ciudad_id');
        
        $query = $em->getConnection()->prepare('SELECT
                                                fnestadisticas_colegios(:re, :pprovincia_id, :pciudad_id, :pterm) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pprovincia_id', $provincia_id, \PDO::PARAM_INT);
        $query->bindValue(':pciudad_id', $ciudad_id, \PDO::PARAM_INT);
        $query->bindValue(':pterm', '', \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        $colegios = array();
        foreach($r as $re)
        {   
            $colegios[] = array('colegio' => $re['colegio'],
                                'profesores' => $re['profesores'],
                                'alumnos' => $re['alumnos'],
                                'libros' => $re['libros']);
        }

        $html = $this->renderView('ActualidadBackendBundle:Default:colegios.html.twig', array('colegios' => $colegios));
        
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEstadisticasLibrosAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $grado_id = $request->query->get('grado_id');
        
        $query = $em->getConnection()->prepare('SELECT
                                                fnestadisticas_libros(:re, :pgrado_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pgrado_id', $grado_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        $libros = array();
        foreach($r as $re)
        {   
            $libros[] = array('nombre' => $re['nombre'],
                              'tipo' => $re['tipo'],
                              'cantidad' => $re['cantidad'],
                              'foto' => $re['foto']);
        }

        $html = $this->renderView('ActualidadBackendBundle:Default:libros.html.twig', array('libros' => $libros));
        
        $return = array('html' => $html);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
}
