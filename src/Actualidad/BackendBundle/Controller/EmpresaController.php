<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminEmpresa;

class EmpresaController extends Controller
{
   public function indexAction()
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }

        $r = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminEmpresa');
        $empresas_db = $r->findAll();

        $empresas = array();
        foreach ($empresas_db as $empresa)
        {
            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'pais' => $empresa->getPais(),
                                'fechaCreacion' => $empresa->getFechaCreacion(),
                                'activo' => $empresa->getActivo(),
                                'delete_disabled' => $f->linkEliminar($empresa->getId(), 'AdminEmpresa'));
        }

        

      return $this->render('ActualidadBackendBundle:Empresa:index.html.twig',array('empresas'=>$empresas));
        
    }

    public function registroAction($empresa_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        

        if ($empresa_id) 
        {
            $empresa = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->find($empresa_id);
        }
        else {
            $empresa = new AdminEmpresa();
            //$empresa->setPais($pais);
            $empresa->setFechaCreacion(new \DateTime('now'));
        }

        // Lista de paises
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('ActualidadComunBundle:AdminPais', 'p')
           ->orderBy('p.nombre', 'ASC');
        $query = $qb->getQuery();
        $paises = $query->getResult();

        if ($request->getMethod() == 'POST')
        {

            $nombre = trim($request->request->get('nombre'));//
            $pais_id = $request->request->get('pais_id');//
            $direccion = trim($request->request->get('direccion'));
            $activo = $request->request->get('activo');
            $correo = trim($request->request->get('correo'));//
            $telefono = trim($request->request->get('telefono'));
            $rif = trim($request->request->get('rif'));

            $pais = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminPais')->find($pais_id);
            $empresa->setNombre($nombre);
            $empresa->setActivo($activo ? true : false);
            $empresa->setTelefonoPrincipal($telefono ? $telefono : null);
            $empresa->setCorreoPrincipal($correo);
            $empresa->setPais($pais);
            $empresa->setdireccion($direccion ? $direccion : null);
            $empresa->setRif($rif ? $rif: null);
            $em->persist($empresa);
            $em->flush();

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));

        }
        
        return $this->render('ActualidadBackendBundle:Empresa:registro.html.twig', array('empresa' => $empresa,
                                                                                   'paises' => $paises));

    }

    public function mostrarAction($empresa_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
      
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $empresa = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->find($empresa_id);

        return $this->render('ActualidadBackendBundle:Empresa:mostrar.html.twig', array('empresa' => $empresa));

    }

   

}