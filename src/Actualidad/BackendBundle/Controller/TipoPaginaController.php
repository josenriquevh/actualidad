<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\EaTipoPagina;

class TipoPaginaController extends Controller
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

        $query = "SELECT tp FROM ActualidadComunBundle:EaTipoPagina tp ";

        $query = $em->createQuery($query);
        $tipospaginasdb = $query->getResult();

        foreach ($tipospaginasdb as $tipopagina)
        {
                $tipospaginas[] = array('id' => $tipopagina->getId(),
                                    'nombre' => $tipopagina->getNombre(),
                                    'delete_disabled' => $f->linkEliminar($tipopagina->getId(),'EaTipoPagina'));
            
        }

        return $this->render('ActualidadBackendBundle:TipoPagina:index.html.twig', array('tipospaginas' => $tipospaginas));
    }

    public function ajaxUpdateTiposPaginaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $tipopagina_id = $request->request->get('tipopagina_id');
        $nombre = $request->request->get('tipopagina');

        if ($tipopagina_id)
        {
            $tipopagina = $em->getRepository('ActualidadComunBundle:EaTipoPagina')->find($tipopagina_id);
        }
        else {
            $tipopagina = new EaTipoPagina();
        }

        $tipopagina->setNombre($nombre);
                
        $em->persist($tipopagina);
        $em->flush();
                    
        $return = array('id' => $tipopagina->getId(),
                        'nombre' => $tipopagina->getNombre(),
                        'delete_disabled' => $f->linkEliminar($tipopagina->getId(),'EaTipoPagina'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditTiposPaginaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $tipopagina_id = $request->query->get('tipopagina_id');
                
        $tipopagina = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaTipoPagina')->find($tipopagina_id);

        $return = array('nombre' => $tipopagina->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}