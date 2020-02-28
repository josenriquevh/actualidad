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

class CodigoController extends Controller
{
   public function indexAction($empresa_id,$grado_id)
    {
            $session = new Session();
            $f = $this->get('funciones');
            
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $f->setRequest($session->get('sesion_id'));

            $em = $this->getDoctrine()->getManager();    

            $query = $em->createQuery("SELECT e FROM ActualidadComunBundle:AdminEmpresa e 
                                        WHERE e.activo = :activo 
                                        ORDER BY e.nombre ASC")
                        ->setParameter('activo', true);
            $empresas = $query->getResult();

            $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g ORDER BY g.id ASC");
            $grados = $query->getResult();
       
            return $this->render('ActualidadBackendBundle:Codigo:index.html.twig', array('empresas' => $empresas, 
                                                                                         'grados' => $grados,
                                                                                         'empresa_selected' => $empresa_id,
                                                                                         'grado_selected' => $grado_id));

    }

    public function ajaxListaConfigurarCodigosAction(Request $request)
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
            $empresa_id = $request->request->get('empresa_id');
            $grado_id = $request->request->get('grado_id');
            $grado = $em->getRepository('ActualidadComunBundle:AdminGrado')->find($grado_id);
           
            
            $query = $em->createQuery("SELECT p.id as id,p.titulo as titulo,p.orden as orden,t.nombre as tipo,g.nombre as grado, (SELECT COUNT(c.pagina) FROM ActualidadComunBundle:EaPaginaUsuario c WHERE c.pagina = p) as codigos FROM ActualidadComunBundle:EaPagina p        
                JOIN p.grado g
                JOIN p.tipoPagina t
                WHERE p.pagina IS NULL
                AND p.empresa = :empresa_id
                AND p.grado = :grado_id
                ORDER BY  p.titulo DESC,p.orden DESC ");

            $query->setParameters(array('empresa_id' => $empresa_id,'grado_id' => $grado_id));

            $libros = $query->getResult();

            $html = $this->renderView('ActualidadBackendBundle:Codigo:tablaLibros.html.twig', array('libros' => $libros,'grado_id' => $grado_id));

            $return = array('html' => $html,
                            'header' => $this->get('translator')->trans('Libros de').' '.$grado->getDescripcion(),'ok'=> 1);

            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
          }
          catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }
    }


    public function listaCodigosViewAction($pagina_id)
    {
        
            $session = new Session();
            $f = $this->get('funciones');
            
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $f->setRequest($session->get('sesion_id'));

            $em = $this->getDoctrine()->getManager(); 

            $codigos = $em->getRepository('ActualidadComunBundle:EaPaginaUsuario')->findBy(array('pagina'=>$pagina_id));

            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

            

            return $this->render('ActualidadBackendBundle:Codigo:gestionarCodigos.html.twig',array('codigos'=>$codigos,'libro'=>$libro));


    }

    public function isVocal($letter){
        $compare = array('A','E','I','O','U');
        $i = 0;
        $return = 0;

        while ($i<count($compare) && $return == false ) {
            $return = ($letter == $compare[$i])? 1:0;
            $i = $i+1;
        }

        return $return;
    }

    public function cargaMasivaViewAction($pagina_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $cnt_pre = $yml['parameters']['codigo']['caracteres_sim'];
        $cnt_pal = $yml['parameters']['codigo']['palabra_valida'];
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager(); 
        $fecha_desde = date('d').'/'.date('m').'/'.date('Y');
        $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
        $iniciales = explode(" ",strtoupper ($f->eliminarAcentos($libro->getTitulo())));
        $prefijo = "";

        if (count($iniciales) == 1 ){//si el titulo del libro contiene una unica palabra
            $titulo_libro = $iniciales[0];
            if(strlen($titulo_libro)>2){ //si el titulo del libro
               $i = 1;
               $prefijo = $prefijo.$titulo_libro[0];
               while ( $i<strlen($titulo_libro) && strlen($prefijo)<$cnt_pre) {
                   $prefijo = ($this->isVocal($titulo_libro[$i]) == 0 )? $prefijo.=$titulo_libro[$i]:$prefijo;
                   $i = $i+1;
               }
                
            }
            else{
                $prefijo = $titulo_libro;
            }
        }
        else{ // si el titulo del libro contiene mas de una palabra
            foreach ($iniciales as $inicial) {
                if (strlen($inicial)>=$cnt_pal && strlen($prefijo)<$cnt_pre) {
                    $prefijo = $prefijo.strtoupper($inicial[0]);
                }
            }
        }
       
        $prefijo = $prefijo.'-'.$libro->getGrado()->getId().'';
        
         return $this->render('ActualidadBackendBundle:Codigo:generarCodigos.html.twig',array('libro'=>$libro,'fecha_desde'=>$fecha_desde,'prefijo'=>$prefijo));


    }

    public function formatearFecha($fecha)
    {
        $fn_array = explode("/", $fecha);
        $d = $fn_array[0];
        $m = $fn_array[1];
        $a = $fn_array[2];
        $fecha = "$a-$m-$d";

        return $fecha;
    }

    public function ajaxCargaMasivaGenerarAction(Request $request)
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
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

            
            $libroId = $request->request->get('libro_id');
            $fechaDesde = $this->formatearFecha($request->request->get('fecha_desde'));
            $fechaHasta = $this->formatearFecha($request->request->get('fecha_hasta'));
            $renovable = $request->request->get('renovable');
            $cantRenovaciones = $request->request->get('cantidad_renovaciones');
            $cantEjemplares = $request->request->get('cantidad_ejemplares');
            $prefijo = trim($request->request->get('prefijo'));
            
            $query = $em->getConnection()->prepare('SELECT
                                                    fngenerar_codigos
                                                    (:ppagina_id,
                                                     :prenovable,
                                                     :pcantidadr,
                                                     :pccodigos, 
                                                     :pccaracteres,
                                                     :pcintentos,
                                                     :ptcodigos,
                                                     :pprefijo,
                                                     :pinicio,
                                                     :pvencimiento) as
                                                    resultado;');
           
            $query->bindValue(':ppagina_id', $libroId, \PDO::PARAM_INT);
            $query->bindValue(':prenovable', $renovable, \PDO::PARAM_BOOL);
            $query->bindValue(':pcantidadr', $cantRenovaciones, \PDO::PARAM_INT);
            $query->bindValue(':pccodigos', $cantEjemplares, \PDO::PARAM_INT);
            $query->bindValue(':pccaracteres', $yml['parameters']['codigo']['caracteres'] , \PDO::PARAM_INT);
            $query->bindValue(':pcintentos', $yml['parameters']['codigo']['intentos_codigo'] , \PDO::PARAM_INT);
            $query->bindValue(':ptcodigos', $yml['parameters']['codigo']['disponibles'] , \PDO::PARAM_INT);
            $query->bindValue(':pprefijo', $prefijo, \PDO::PARAM_STR);
            $query->bindValue(':pinicio', $fechaDesde, \PDO::PARAM_STR);
            $query->bindValue(':pvencimiento', $fechaHasta, \PDO::PARAM_STR);
            $query->execute();
            $gc = $query->fetchAll();
            $indices = $gc[0]['resultado'];
            $respuesta = explode("-",$gc[0]['resultado']);
            $n = count($respuesta);
            $last = array_pop($respuesta);

            if ($n==1){
                $return = array('case'=>1,'codCant'=>0,'ok'=>1);
            }
            elseif($n==2 && $last ==0){
                $return = array('case'=>2,'codCant'=>0,'disponibles'=>$respuesta[0],'ok'=>1);
            }
            elseif($n>=2 && $last>0){
                
                $return = array('case'=>3,'codCant'=>$last,'indices'=>$indices,'ok'=>1);
                
            }

            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
         catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }
            
            


    }
    public function generalExcelCargaAction(Request  $request)
    {
        try{    
            $session = new Session();
            $f = $this->get('funciones');
            
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $f->setRequest($session->get('sesion_id'));
        
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));

            $em = $this->getDoctrine()->getManager(); 
            $libroId = $request->request->get('libro_id');
            $indices = $request->request->get('indices');
            $ids = explode("-",$indices);
            $last = array_pop($ids);

            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libroId) ;
                
            $pex=$this->get('phpexcel');

            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPaginaUsuario p        
                    WHERE p.id IN (:indices)
                    ORDER BY  p.id ASC ");

            $query->setParameters(array('indices' => $ids));
            $codigos = $query->getResult();

                
            $excel = $f->ExcelCodigos($codigos,$libroId,$libro->getGrado()->getId(),$yml,$pex);
            if($excel){
                 $return = array('ok'=>1,'excel'=>$excel['archivo']);
            }
            else{
                $return = array('ok'=>0);
            }
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
                 catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }

       
    }

    public function ajaxGenerarExcelCodigosAction(Request $request)
    {
        try{

            
            $session = new Session();
            $f = $this->get('funciones');
            
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $pagina_id = $request->request->get('libro_id');
            $status = $request->request->get('status');
            $fecha_in = $this->formatearFecha($request->request->get('fecha_in'));
            $fecha_out = $this->formatearFecha($request->request->get('fecha_out'));

            $f->setRequest($session->get('sesion_id'));
            $em = $this->getDoctrine()->getManager(); 
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
            $pex=$this->get('phpexcel');
            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);
           
            $estado = ($status == 1)? TRUE:FALSE;
            $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:EaPaginaUsuario p        
                WHERE p.pagina =:pagina_id
                   AND p.activo=:estado
                   AND p.fechaInicio BETWEEN :fechaIn AND :fechaOut
                ORDER BY  p.id ASC ");

            $query->setParameters(array('pagina_id' => $pagina_id,'estado'=>$estado,'fechaIn'=>$fecha_in,'fechaOut'=>$fecha_out));
            $codigos = $query->getResult();
            $cnt = count($codigos);

            if ($cnt>0){
                $excel = $f->ExcelCodigos($codigos,$pagina_id,$libro->getGrado()->getId(),$yml,$pex);
                $return=array('ok'=>1,'archivo'=>$excel['archivo'],'cantidad'=>$cnt);
                
            }
            else{
                $return=array('ok'=>2);
            }
        
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