<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Actualidad\ComunBundle\Entity\EaCertificado;
use Symfony\Component\Yaml\Yaml;
use Spipu\Html2Pdf\Html2Pdf;

class CertificadoController extends Controller
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

        $certificados = $em->getRepository('ActualidadComunBundle:EaCertificado')->findAll();

        $certificadodb= array();
        if($certificados)
        {
            foreach ($certificados as $certificado)
            {
                if($certificado->getTipoCertificado()->getId() == '1')
                {
                    $entidad = $em->getRepository('ActualidadComunBundle:EaPagina')->find($certificado->getEntidadId());
                    $entidad = $entidad->getTitulo().' '.$entidad->getTipoPagina()->getNombre();
                }else{
                    $entidad = $certificado->getGrado()->getNombre().' '.$certificado->getGrado()->getDescripcion();
                }
                $certificadodb[]= array('id' => $certificado->getId(),
                                        'grado' => $certificado->getGrado()->getNombre().' '.$certificado->getGrado()->getDescripcion(),
                                        'tipoCertificado' => $certificado->getTipoCertificado()->getNombre(),
                                        'entidad' => $entidad,
                                        'delete_disabled' => $f->linkEliminar($certificado->getId(),'EaCertificado'));
            }
        }

        return $this->render('ActualidadBackendBundle:Certificado:index.html.twig', array('certificados' => $certificadodb));

    }

    public function registroAction($certificado_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
     
        $empresas = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                               array('nombre' => 'ASC'));
     
        $tipo_certificados = $em->getRepository('ActualidadComunBundle:EaTipoCertificado')->findAll(array('nombre' => 'ASC'));
        
        $grados = $em->getRepository('ActualidadComunBundle:AdminGrado')->findAll(array('nombre' => 'ASC'));
        $hoy ='';
        //return new response($grados);

        if ($certificado_id)
        {
            $certificado = $em->getRepository('ActualidadComunBundle:EaCertificado')->find($certificado_id);
            $modificacion = date('Y-m-d');
        }
        else 
        {
            $certificado = new EaCertificado();
            $hoy = date('Y-m-d');
        }
        if ($request->getMethod() == 'POST')
        {
            $empresa_id = $request->request->get('empresa_id');
            $tipo_certificado_id = $request->request->get('tipo_certificado_id');
            $entidad = $request->request->get('libro');
            $imagen = trim($request->request->get('form_foto'));
            $encabezado = trim($request->request->get('encabezado'));
            $descripcion = trim($request->request->get('descripcion'));
            $grado_id = $request->request->get('grado_id');

            $empresa = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->find($empresa_id);
            $tipoCertificado = $em->getRepository('ActualidadComunBundle:EaTipoCertificado')->find($tipo_certificado_id);
            $grado = $em->getRepository('ActualidadComunBundle:AdminGrado')->find($grado_id);
            if($entidad)
            {
                $certificado->setEntidadId($entidad);
            }else{
                $certificado->setEntidadId($grado_id);
            }
            
            $certificado->setEmpresa($empresa);
            $certificado->setTipoCertificado($tipoCertificado);
            $certificado->setImagen($imagen);
            $certificado->setEncabezado($encabezado);
            $certificado->setDescripcion($descripcion);
            $certificado->setGrado($grado);
            if($hoy)
            {
                $certificado->setFechaCreacion(new \DateTime($hoy));
            }else{
                $certificado->setFechaModificacion(new \DateTime($modificacion));
            }

            $em->persist($certificado);
            $em->flush();

            return $this->redirectToRoute('_showCertificado', array('certificado_id' => $certificado->getId()));

        }

        return $this->render('ActualidadBackendBundle:Certificado:registro.html.twig', array('empresas' => $empresas,
                                                                                             'certificado' => $certificado,
                                                                                             'tipoCertificados' => $tipo_certificados,
                                                                                             'grados' => $grados ));

    }

    public function ajaxLibrosAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $grado = $request->query->get('grado_id');
        $certificado_id = $request->query->get('certificado_id');
        $empresa_id = $request->query->get('empresa_id');
        $tipo_certificado_id = $request->query->get('tipo_certificado_id');
        $certificado= '';
        $html = '';
       
        $empresa = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->find($empresa_id);

        $tipo_certificado = $em->getRepository('ActualidadComunBundle:EaTipoCertificado')->find($tipo_certificado_id);

        if($certificado_id)
            $certificado = $em->getRepository('ActualidadComunBundle:EaCertificado')->find($certificado_id);       

        if($tipo_certificado->getId() == 1)
        {
            //return new response('aca');
            $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:EaPagina p
                                       WHERE p.empresa= :empresa_id
                                       AND p.grado= :grado_id
                                       AND p.pagina IS NULL
                                       AND p.estatusContenido = :estatus_contenido_activo 
                                       AND p.tipoPagina IN (1 , 2)
                                       ORDER BY p.titulo ASC ')
                        ->setParameters(array('empresa_id' => $empresa->getId(),
                                              'estatus_contenido_activo' => $yml['parameters']['estatus_contenido']['activo'],
                                              'grado_id' => $grado));
            $librosGrado = $query->getResult();

            if($librosGrado)
            {
                $html .= '<label for="texto" class="col-2 col-form-label">Libros</label>';
                $html .= '<div class="col-14">
                            <select class="form-control form_sty_select" name="libro" id="libro">'; 

                foreach($librosGrado as $libro)
                {
                    $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:EaCertificado c
                                            WHERE c.entidadId = :pagina_id 
                                            ORDER BY c.id ASC")
                                ->setParameter('pagina_id' , $libro->getId());
                    $certificados = $query->getResult();

                    $nombre = ucwords(mb_strtolower( $libro->getTitulo(),"UTF-8" ));
                    if(!$certificados)
                    {
                        if($certificado)
                        {
                            if($certificado->getEntidadId() == $libro->getId())
                            {
                                $html .= '<option value="'.$libro->getId().'" selected>'.$nombre.' - '.$libro->getTipoPagina()->getNombre() .'</option>';    
                            }else{
                                $html .= '<option value="'.$libro->getId().'">'.$nombre.' - '.$libro->getTipoPagina()->getNombre() .'</option>';
                            }
                        }else{
                            $html .= '<option value="'.$libro->getId().'">'.$nombre.' - '.$libro->getTipoPagina()->getNombre() .'</option>';
                        }
                    }else{
                        if($certificado)
                        {
                            if($certificado->getEntidadId() == $libro->getId())
                                {
                                    $html .= '<option value="'.$libro->getId().'" selected>'.$nombre.' - '.$libro->getTipoPagina()->getNombre() .'</option>';    
                                }
                        }
                    }
                    
                    
                    
                }
                $html .= '  </select>
                            <span class="fa fa-industry"></span>
                            </div>';
            }else{
                $html .= '<label for="texto" class="col-6 col-form-label">No tienes libros asociados</label>';
            }
        }

        

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function mostrarAction($certificado_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
      
        
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $fecha_modificacion = '';

        $certificado = $em->getRepository('ActualidadComunBundle:EaCertificado')->find($certificado_id);
        $fecha_creacion = $f->fechaNatural($certificado->getFechaCreacion()->format('Y-m-d'));
        if($certificado->getFechaModificacion()){
            $fecha_modificacion = $f->fechaNatural($certificado->getFechaModificacion()->format('Y-m-d'));
        }
        $entidad='';

        if($certificado->getTipoCertificado()->getId() == 1)
        {
            
            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($certificado->getEntidadId());
            $entidad=$libro->getTitulo().' - '.$libro->getTipoPagina()->getNombre();
            
        }
        
        return $this->render('ActualidadBackendBundle:Certificado:mostrar.html.twig', array('certificado' => $certificado,
                                                                                            'entidad' => $entidad,
                                                                                            'fecha_creacion' => $fecha_creacion,
                                                                                            'fecha_modificacion' => $fecha_modificacion ));

    }

    public function generarPdfAction($certificado_id)
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

        $certificado = $em->getRepository('ActualidadComunBundle:EaCertificado')->find($certificado_id);

        if( $certificado->getTipoCertificado()->getId() == $yml['parameters']['tipo_certificado']['libro'] )
        {
            $file = $this->container->getParameter('folders')['dir_uploads'].$certificado->getImagen();
            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($certificado->getEntidadId());

            $comodines = array('%%libro%%', '%%grado%%');
            $reemplazos = array($libro->getTitulo(), $certificado->getGrado()->getNombre().' '.$certificado->getGrado()->getDescripcion());
            $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());
            $frase = null;
            $libro = null;
            $frase2 = null;
            $grado = null;
            $pos = strpos($descripcion, '__');
            if($pos === false)
            {
                $frase = $descripcion;
                
            }else{
                list($frase, $libro, $frase2, $grado) = explode("__", $descripcion);
            }

            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(10, 35, 0, 0));
            $certificado_pdf->writeHTML('
                                        <page title="Certificado" pageset="new" backimg="'.$file.'" backimgw="90%" backimgx="center"> 
                                            <div style="position: relative; top: 10%; font-size:22px;text-align:center; color:#9B9B9B; font-family:roboto; ">'.$certificado->getEncabezado().'</div>
                                            <div style="position: relative; top: 15%;text-align:center; font-size:40px; text-transform:uppercase; color:#00AEEF; font-family:roboto;">'.$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'].'</div>
                                            <div style="position: relative; top: 15%; text-align:center; font-size:24px; color:#9B9B9B; width: 55%; margin-left: 22%; font-family:roboto;">'.$frase.'</div> <span style="position: relative; text-align:center; font-size:24px; color:#00AEEF; margin-left: 30%;">'.$libro.'</span> <span style="position: relative; text-align:center; font-size:24px; color:#9B9B9B;">'.$frase2.'</span> <span style="position: relative; text-align:center; font-size:24px; color:#00AEEF;"> '.$grado.'</span>
                                        </page>');


            $certificado_pdf->output('certificado.pdf');

        }

    }

}