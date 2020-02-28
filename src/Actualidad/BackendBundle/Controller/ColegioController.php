<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminColegio;
use Actualidad\ComunBundle\Entity\AdminCiudad;

class ColegioController extends Controller
{
   public function indexAction($colegio_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:AdminProvincia p 
                                    ORDER BY p.nombre ASC");
        $provincias = $query->getResult();

       if ($colegio_id)
        {

            // Filtro prellenado
            $colegio = $em->getRepository('ActualidadComunBundle:AdminColegio')->find($colegio_id);

            // Lista de ciudades
            $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:AdminCiudad c
                                        WHERE c.id = :ciudad_id 
                                        ORDER BY l.nombre ASC")
                        ->setParameter('ciudad_id', $colegio->getCiudad()->getId());
            $ciudades = $query->getResult();

            
            
            $header = 'Lista de colegios ';

        }
        else {
            $colegio = new AdminColegio();
            $ciudades = array();
            $header = 'Buscando...';
        }

        return $this->render('ActualidadBackendBundle:Colegio:index.html.twig', array('provincias' => $provincias,
                                                                                      'ciudades' => $ciudades,
                                                                                      'header' => $header,
                                                                                      'colegio' => $colegio));
    } 

    public function ajaxGetColegiosAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $colegio_nombre = trim($request->query->get('colegio_nombre'));
        $ciudad_id = $request->query->get('ciudad_id');
        $provincia_id = $request->query->get('provincia_id');

        if($colegio_nombre != '')
        {
            $ciudad_id=0;
            $provincia_id=0;
        }

        $query = $em->getConnection()->prepare('SELECT
                                                fnestadisticas_colegios(:re, :pprovincia_id, :pciudad_id, :pterm) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pprovincia_id', $provincia_id, \PDO::PARAM_INT);
        $query->bindValue(':pciudad_id', $ciudad_id, \PDO::PARAM_INT);
        $query->bindValue(':pterm', $colegio_nombre, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        $colegios = array();
        foreach($r as $re)
        {   
            $colegios[] = array('id' => $re['id'],
                                'colegio' => $re['colegio'],
                                'provincia' => $re['provincia'],
                                'ciudad' => $re['ciudad'],
                                'profesores' => $re['profesores'],
                                'alumnos' => $re['alumnos'],
                                'libros' => $re['libros'],
                                'delete_disabled' => $f->linkEliminar($re['id'],'AdminColegio'));
        }


        $html = $this->renderView('ActualidadBackendBundle:Colegio:colegios.html.twig', array('colegios' => $colegios));
        
        $return = array('html' => $html,
                        'header' => 'Lista de colegios ');
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }


    public function ajaxUpdateColegioAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $colegio_id = $request->request->get('colegio_id');
        $nombre = $request->request->get('nombre');
        $provincia_id = $request->request->get('m_provincia_id');
        $ciudad_id = $request->request->get('m_ciudad_id');

        if ($colegio_id)
        {
            $colegio = $em->getRepository('ActualidadComunBundle:AdminColegio')->find($colegio_id);
            $colegio->setNombre($nombre);
        }
        else {
            $colegio = new AdminColegio();
            
            $ciudad = $em->getRepository('ActualidadComunBundle:AdminCiudad')->find($ciudad_id);

            $colegio->setNombre($nombre);
            $colegio->setCiudad($ciudad);
        }
                
        $em->persist($colegio);
        $em->flush();
                    
        $return = array('id' => $colegio->getId(),
                        'nombre' => $colegio->getNombre(),
                        'delete_disabled' => $f->linkEliminar($colegio->getId(),'AdminColegio'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditColegioAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $colegio_id = $request->query->get('colegio_id');
                
        $colegio = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminColegio')->find($colegio_id);

        $return = array('nombre' => $colegio->getNombre(),
                        'id' => $colegio->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function CargaColegioAction(Request $request, $file)
    {
        
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));
        
        $em = $this->getDoctrine()->getManager();
        
        $errores = array();

        //$file = 'centros_educativos_republica.xlsx';
        $fileWithPath = $this->container->getParameter('folders')['dir_uploads'].$file;
        
        if(!file_exists($fileWithPath)) 
        {
            $errores['general'] = $this->get('translator')->trans('El archivo').' '.$fileWithPath.' '.$this->get('translator')->trans('no existe');
            $filas_insertadas = 0;
        } 
        else {
            
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            
            // Se obtienen las hojas, el nombre de las hojas y se pone activa la primera hoja
            $total_sheets = $objPHPExcel->getSheetCount();
            $allSheetName = $objPHPExcel->getSheetNames();
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            
            // Se obtiene el número máximo de filas y columnas
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            //return new Response($highestRow);
            
            if ($highestRow < 1)
            {
                $errores['general'] = $this->get('translator')->trans('El archivo debe tener al menos una fila con datos').'.';
                $filas_insertadas = 0;
            }
            else {

                //Se recorre toda la hoja excel desde la fila 2
                $filas_insertadas = 0;
                $errores = array(); // No pueden existir correos repetidos
                $ultima_provincia = '';
                $ultima_ciudad = '';
                for ($row=2; $row<=$highestRow; ++$row) 
                {

                    // Provincia
                    $col = 7;
                    $col_name = 'H';
                    $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                    $provincia = trim($cell->getValue());
                    $provincia = $f->sanear_string($provincia);
                    $provincia = mb_strtolower($provincia, 'UTF-8');
                    if ($provincia)
                    {
                        $query = $em->createQuery('SELECT p FROM ActualidadComunBundle:AdminProvincia p 
                                                    WHERE LOWER(TRIM(p.nombre)) = LOWER(:provincia)')
                                    ->setParameter('provincia' , $provincia);
                        $existe_provincia = $query->getResult();
                        if (!$existe_provincia)
                        {
                            if($provincia != $ultima_provincia)
                            {
                                $errores[] = array('nombre' => $provincia,
                                                    'fila' => $row);
                                $ultima_provincia = $provincia;
                            }
                        }else{

                            // Ciudad
                            $col = 8;
                            $col_name = 'I';
                            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                            $ciudad = trim($cell->getValue());
                            $ciudad = $f->sanear_string($ciudad);
                            $ciudad = mb_strtolower($ciudad, 'UTF-8');
                            $provincia_id = $existe_provincia[0]->getId();
                            
                            if ($ciudad)
                            {
                                $query = $em->createQuery('SELECT c FROM ActualidadComunBundle:AdminCiudad c 
                                                            WHERE LOWER(TRIM(c.nombre)) = LOWER(:ciudad)
                                                            AND  c.provincia = :provincia_id') 
                                            ->setParameters(array('ciudad' => $ciudad,
                                                                  'provincia_id' => $provincia_id));
                                $existe_ciudad = $query->getResult();
                                if (!$existe_ciudad)
                                {
                                    if($ciudad != $ultima_ciudad)
                                    {
                                        $errores[] = array('nombre' => $ciudad,
                                                           'fila' => $row);
                                        $ultima_ciudad = $ciudad;
                                    }
                                }else{

                                    // Nombres
                                    $col = 3;
                                    $col_name = 'D';
                                    $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                                    $nombre_co = trim($cell->getValue());
                                    $nombre_co = $f->sanear_string($nombre_co);
                                    $ciudad_id = $existe_ciudad[0]->getId();
                                    if ($nombre_co)
                                    {
                                        $query = $em->createQuery('SELECT co.id FROM ActualidadComunBundle:AdminColegio co 
                                                                    WHERE LOWER(TRIM(co.nombre)) = LOWER(:colegio)
                                                                    AND  co.ciudad = :ciudad_id') 
                                                    ->setParameters(array('colegio' => $nombre_co,
                                                                          'ciudad_id' => $ciudad_id));
                                        $existe_colegio = $query->getResult();
                                        if(!$existe_colegio)
                                        {
                                            $ciudad = $em->getRepository('ActualidadComunBundle:AdminCiudad')->find($ciudad_id);
                                            $colegio = new AdminColegio();
                                            $colegio->setNombre($nombre_co);
                                            $colegio->setCiudad($ciudad);
                
                                            $em->persist($colegio);
                                            $em->flush();
                                            $filas_insertadas = $filas_insertadas +1;

                                        }
                                    }
                                    
                                }
                            }

                        }
                    }

                } 
                

            }
            

        }

        
        

        return $this->render('ActualidadBackendBundle:Colegio:registros.html.twig', array('registros' => $filas_insertadas,
                                                                                          'errores' => $errores));

    }
    

}