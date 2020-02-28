<?php

namespace Actualidad\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use Actualidad\ComunBundle\Model\UploadHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Yaml\Yaml;
use Spipu\Html2Pdf\Html2Pdf;

class ReportesJTController extends Controller
{

    public function profesoresActivosAction()
    {
        $session = new Session();
        $f = $this->get('funciones');
        $r = $this->get('reportes');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();  
        $fecha_actual = date('Y').'/'.date('m').'/'.date('d');

        $reporte = $r->profesoresActivos();

        foreach ($reporte as $resultado) {

            $htmlLibros = '';
            foreach ($resultado['libros'] as $libro) {
                $htmlLibros .= '</label><BR><li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$libro['libro_id'].'" p_str="'.$libro['tipo'].': '.$libro['libro'].'">'.$libro['tipo'].': '.$libro['libro'].'</li>';
            }
            $htmlColegios = '';

            foreach ($resultado['colegios'] as $colegio) {
                $htmlColegios .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$colegio['colegio_id'].'" p_str="'.$colegio['ciudad'].': '.$colegio['colegio'].'">'.$colegio['ciudad'].': '.$colegio['colegio'].'</li>';
            }

            $profesores[] = array(
                                    'id' => $resultado['id'],
                                    'correo' => $resultado['correo'],
                                    'nombre' => $resultado['nombre'],
                                    'apellido' => $resultado['apellido'],
                                    'cantidad_libros' =>count($resultado['libros']),
                                    'cantidad_colegios' =>count($resultado['colegios']),
                                    'libros' =>  $htmlLibros,
                                    'colegios' => $htmlColegios,
                                    'ultima_conexion' =>$resultado['conexion']);
        }
        
	        
        return $this->render('ActualidadBackendBundle:Reportes:profesoresActivos.html.twig',array('profesores'=>$profesores));
    }


    public function ajaxExcelProfesoresActivosAction(Request $request)
    {
        try{  
            $em = $this->getDoctrine()->getManager();
            $fs = $this->get('funciones');
            $r = $this->get('reportes');
            $reporte = $r->profesoresActivos();
            $session = new Session();

            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/reporteProfesoresActivos.xlsx';
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            if (!count($reporte))
            {
                $objWorksheet->mergeCells('A5:C5');
                $objWorksheet->setCellValue('A5', $this->translator->trans('No existen registros para esta consulta'));
            }
            else {

                $row = 5;
                $i = 0;
                $styleThinBlackBorderOutline = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                );
                $font_size = 11;
                $font = 'Arial';
                $horizontal_aligment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                $vertical_aligment = \PHPExcel_Style_Alignment::VERTICAL_CENTER;

                foreach ($reporte as $profesor)
                {
                    $cnt_colegios = count($profesor['colegios']);
                    $cnt_libros = count($profesor['libros']);
                    $colegios_text =($cnt_colegios>0)? '('.$cnt_colegios.'): ':'';
                    $limit_iterations = $cnt_libros-1;
                    $limit_row = $row+$limit_iterations;
                    $i=0;

                    foreach ($profesor['colegios'] as $colegio) {
                        if ($i==0){
                             $colegios_text = $colegios_text.$colegio['colegio'];
                        }
                        else{
                              $colegios_text = $colegios_text.', '.$colegio['colegio'];
                        }
                        $i++;
                    }

                    //Estilizar la celdas antes de un posible merge
                    for ($f=$row; $f<=$limit_row; $f++)
                    {
                        $objWorksheet->getStyle("A$f:J$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:J$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:J$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:J$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:J$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                        $objWorksheet->getRowDimension($f)->setRowHeight(20); // Altura de la fila
                    }

                    if ($limit_iterations > 0)
                    {
                        // Merge de las celdas
                        for ($c=0; $c<=8; $c++)
                        {
                            $col = $columnNames[$c];
                            $objWorksheet->mergeCells($col.$row.':'.$col.$limit_row);
                        }
                    }

                    $objWorksheet->getStyle("A$row:C$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                    $objWorksheet->getStyle("A$row:C$row")->getFont()->setSize($font_size); // Tamaño de las letras
                    $objWorksheet->getStyle("A$row:CC$row")->getFont()->setName($font); // Tipo de letra
                    $objWorksheet->getStyle("A$row:C$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                    $objWorksheet->getStyle("A$row:C$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                    $objWorksheet->getRowDimension($row)->setRowHeight(20); // Altura de la fila
                    // Datos de las columnas comunes
                    $objWorksheet->setCellValue('A'.$row, $profesor['usuario']);
                    $objWorksheet->setCellValue('B'.$row, $profesor['nombre']);
                    $objWorksheet->setCellValue('C'.$row, $profesor['apellido']);
                    $objWorksheet->setCellValue('D'.$row, $profesor['fecha_registro']->format('d/m/y H:i:s'));
                    $objWorksheet->setCellValue('E'.$row, $profesor['correo']);
                    $objWorksheet->setCellValue('F'.$row, $profesor['ciudad']);
                    $objWorksheet->setCellValue('G'.$row, $profesor['provincia']);
                    $objWorksheet->setCellValue('H'.$row, $profesor['conexion']);
                    $objWorksheet->setCellValue('I'.$row, $colegios_text);

                    if ($profesor['libros']) {
                       $item = 1;
                       foreach ($profesor['libros'] as $libro)
                        {
                            $objWorksheet->setCellValue('J'.$row, $item.'- '.$libro['tipo'].': '. $libro['libro']);
                            $item++;
                            $row++;
                        }                    

                    }else{
                        $row++;
                    }
                    
                   
                }

            }

            // Crea el writer
            $titulo = 'docentes_fecha_de_hoy_sesionId.xls';

            $hoy = date('d-m-Y');

            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
            $path = 'reportes/'.'docentes_'.$hoy.'_'.$session->get('sesion_id').'.xls';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;
            $document_name = 'profesores_'.$hoy.'_'.$session->get('sesion_id').'.xls';
            $bytes = filesize($xls);

            $document_size = $fs->fileSizeConvert($bytes);
            $return = array('archivo' => $archivo,
                            'document_name' => $document_name,
                            'document_size' => $document_size,
                            'ok' => 1);
            $return =  json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
        catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }
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

    public function detalleUsuario($usuario_id, $yml)
    {

        $em = $this->getDoctrine()->getManager();
        $resultados = array();

        // INGRESOS AL SISTEMA
        $query = $em->getConnection()->prepare('SELECT
                                                fningresos_sistema(:pusuario_id) as
                                                resultado;');
        $query->bindValue(':pusuario_id', $usuario_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        // La respuesta viene separada por __
        $r_arr = explode("__", $r[0]['resultado']);
        $resultados['ingresos']['primeraConexion'] = $r_arr[0];
        $resultados['ingresos']['ultimaConexion'] = $r_arr[1];
        $resultados['ingresos']['cantidadConexiones'] = (int) $r_arr[2];
        $resultados['ingresos']['promedioConexion'] = $r_arr[3];
        $resultados['ingresos']['noIniciados'] = (int) $r_arr[4];
        $resultados['ingresos']['enCurso'] = (int) $r_arr[5];
        $resultados['ingresos']['finalizados'] = (int) $r_arr[6];
        
        return $resultados;

    }

    public function ajaxDetalleParticipanteAction(Request $request)
    {

        
        $em = $this->getDoctrine()->getManager();
        //$rs = $this->get('reportes');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

      
        $usuario_id = $request->request->get('usuario_id');
        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
        $reporte = $this->detalleUsuario($usuario->getId(),$yml);

        $query = $em->createQuery("SELECT c.nombre as colegio, c.id as colegio_id, p.nombre as ciudad FROM ActualidadComunBundle:AdminUsuarioSeccion us
                                          INNER JOIN ActualidadComunBundle:AdminSeccion s WITH s.id = us.seccion
                                          INNER JOIN ActualidadComunBundle:AdminColegio c WITH c.id = s.colegio
                                          INNER JOIN ActualidadComunBundle:AdminCiudad  p WITH p.id = c.ciudad
                                          WHERE us.usuario = :profesor_id
                                          GROUP BY c.id,c.nombre,p.nombre")
                    ->setParameter('profesor_id', $usuario_id);
        $colegios = $query->getResult();

       
       
        $cntColegios = count($colegios);
        $text = '';
        if ($cntColegios>0) {
            foreach ($colegios as $colegio) {
                if ($text=='') {
                    $text.=  '('.$cntColegios.'): '. $colegio['colegio'];
                }
                else{
                     $text.= ', '. $colegio['colegio'];
                }
               
            }
        }
        

        $dataUsuario = array('foto' => trim($usuario->getFoto()) ? trim($usuario->getFoto()) : 0,
                             'login' => $usuario->getLogin(),
                             'nombre' => $usuario->getNombre(),
                             'apellido' => $usuario->getApellido(),
                             'correoPersonal' => $usuario->getCorreo(),
                             'fechaNacimiento' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('d/m/Y') : '',
                             'activo' => $usuario->getActivo() ? $this->get('translator')->trans('Sí') : 'No',
                             'rol' => $usuario->getRol() ? $usuario->getRol()->getNombre() : '',
                             'provincia' => $usuario->getProvincia()? $usuario->getProvincia()->getNombre():'',
                             'ciudad' => $usuario->getCiudad()? $usuario->getCiudad()->getNombre():'',
                             'colegios' => $text,
                             'ingresos' => $reporte['ingresos']
                            );

        $return = array('usuario' => $dataUsuario);

        $html = $this->renderView('ActualidadBackendBundle:Reportes:detalleUsuario.html.twig', array('programas' => ''));

        $return['html'] = $html;
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }


    public function estadisticasAlumnosAction(Request $request)
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

            return $this->render('ActualidadBackendBundle:Reportes:estadisticasAlumno.html.twig', array('empresas' => $empresas, 
                                                                                                        'grados' => $grados));
    }



    public function ajaxInteraccionesUnidadAction(Request $request){
        
        try{ 
            
            $session = new Session();
            $f = $this->get('funciones');
            
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $f->setRequest($session->get('sesion_id'));

            $em = $this->getDoctrine()->getManager();   

            $libro_id =  $request->request->get('libro_id');
            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);

            $query = $em->getConnection()->prepare('SELECT
                                                fninteraccion_unidades(:re,:ppagina_id) as
                                                resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':ppagina_id', $libro_id, \PDO::PARAM_INT);
            $query->execute();
            $resultado = $query->fetchAll();
            $iniciados = array();
            $finalizados = array();
            $pendientes = array();
            $labels = array();

            foreach ($resultado as $rs) {
                array_push($iniciados,$rs['cursando']);
                array_push($finalizados,$rs['culminado']);
                array_push($pendientes,$rs['no_iniciados']);
                array_push($labels,$rs['unidad']);
               
            }

            $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:EaPaginaUsuario c
                                        WHERE c.activo = :activo 
                                        AND c.pagina = :libro_id
                                        ")
                        ->setParameters(array('activo'=> true,'libro_id'=>$libro_id));
            $codigos = $query->getResult();
             
              
            
            $return = json_encode(array('labels' => $labels,
                                        'iniciados' => $iniciados,
                                        'finalizados' => $finalizados,
                                        'pendientes' => $pendientes,
                                        'codigos_activos' => count($codigos),
                                        'titulo_libro'=>$libro->getTitulo(),
                                        'ok'=>1));
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
         catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }


    }

    public function codigosActivosProvinciaCiudadAction(Request $request){
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

        $query = $em->createQuery("SELECT p FROM ActualidadComunBundle:AdminProvincia p 
                                        ORDER BY p.id ASC");
        $provincias = $query->getResult();

        
        return $this->render('ActualidadBackendBundle:Reportes:codigosActivosProvinciaCiudad.html.twig', array('empresas' =>$empresas, 'grados' => $grados, 'provincias'=>$provincias));

    }

    public function ajaxCodigosActivosProvinciaCiudadAction(Request $request){
        
        try{ 
            
            $colores =array('#6384ee','#15d4be','#ff6262','#f0ad4e','#fd19da','#088d06','#fdfd5d','#0b399d','#7dfc90','#bfbfbf');

            $session = new Session();
            $f = $this->get('funciones');
                
            if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
            {
                return $this->redirectToRoute('_loginAdmin');
            }
            $f->setRequest($session->get('sesion_id'));
            $em = $this->getDoctrine()->getManager(); 

            $libro_id =  $request->request->get('libro_id');
            $provincia_id = ($request->request->get('provincia_id')>0) ? $request->request->get('provincia_id'):0; 

            $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
            $query = $em->getConnection()->prepare('SELECT
                                                    fncodigos_ubicacion(:re,:ppagina_id,:pprovincia_id) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':ppagina_id', $libro_id, \PDO::PARAM_INT);
            $query->bindValue(':pprovincia_id', $provincia_id, \PDO::PARAM_INT);
            $query->execute();
            $resultado = $query->fetchAll();
            $cantidad_resultados = count($resultado);
            $limite_resultados = ($cantidad_resultados>10)? 10:$cantidad_resultados;
            $data = array();
            $labels = array();
            $colors = array();
            for ($i=0; $i < $limite_resultados ; $i++) { 
                array_push($data,$resultado[$i]["cantidad"]);
                array_push($labels,$resultado[$i]["nombre"].' ('.$resultado[$i]["cantidad"].') ');
                array_push($colors,$colores[$i]);
                // $data[] = array("data"=>[[$i,(integer)$resultado[$i]["cantidad"]]],"color"=>$colores[$i]);
                // $ubicaciones[] = array($i,$resultado[$i]["nombre"].' ('.$resultado[$i]["cantidad"].') ');
            }

            $titulo = ($provincia_id>0)? $this->get('translator')->trans('Estadísticas de códigos activos por ciudad'):  $this->get('translator')->trans('Estadísticas de códigos activos por provincia');
            $encabezado = ($provincia_id>0)? 'Top '.count($labels).' '.$this->get('translator')->trans('de ciudades con mas  códigos activos').', '.$this->get('translator')->trans('para el libro').': '.$libro->getTitulo():
            'Top '.count($labels).' '.$this->get('translator')->trans('de provincias con mas  códigos activos').', '.$this->get('translator')->trans('para el libro').': '.$libro->getTitulo();

            $return = json_encode(array('data'=>$data,'codigos_activos'=>$limite_resultados,'labels'=>$labels,'ok'=>1,'titulo'=>$titulo,'label'=>$encabezado,'colors'=>$colores));
     
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }
         catch (\Exception $ex) {
           $return = array('ok' => 0,
                           'msg' => $ex->getMessage());
           return new JsonResponse($return);
       }
        

    }

    public function ajaxSaveImgAction(Request $request)
    {
        
        $session = new Session();
        
        $bin_data = $request->request->get('bin_data');
        $reporte = $request->request->get('reporte');
      
        
        $data = str_replace(' ', '+', $bin_data);
        $data = base64_decode($data);
        $im = imagecreatefromstring($data);
        
        $path = 'reportes/'.$reporte.$session->get('sesion_id').'.png';
        $fileName = $this->container->getParameter('folders')['dir_uploads'].$path;

        if ($im !== false) {
            // Save image in the specified location
            imagepng($im, $fileName);
            imagedestroy($im);
        }
        else {
            $fileName = 'An error occurred.';
        }

        $return = array('fileName' => $fileName);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }



    public function exportarReportePdfAction($libro_id,$titulo_reporte,$grafico_name,$adicional=null){

        $titulos =  array($this->get('translator')->trans('Estadísticas de códigos por ubicación'),
                          $this->get('translator')->trans('Estadísticas de interacción por unidad'));
        $graficoName = array('codigosUbicacion','estadisticasUnidades');
        $session = new Session();
        $f = $this->get('funciones');
            
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));
        
        $em = $this->getDoctrine()->getManager(); 
        $libro = $em->getRepository('ActualidadComunBundle:EaPagina')->find($libro_id);
        $titulo = $titulos[$titulo_reporte].': '.$libro->getTitulo().' - '.$libro->getGrado()->getNombre();
        $total = ($adicional)? $this->get('translator')->trans('Total de códigos activos').' : '.$adicional:null;
        //$empresa =  $em->getRepository('ActualidadComunBundle:AdminEmpresa')->find($libro->getEmpresa()->getId());
        $grafico_nm = 'reportes/'.$graficoName[$grafico_name].$session->get('sesion_id').'.png';
        $grafico = $this->container->getParameter('folders')['dir_uploads'].$grafico_nm;
        
        $html = $this->renderView('ActualidadBackendBundle:Reportes:pdfGraficosBarra.html.twig',array('titulo'=>$titulo,'grafico'=>$grafico,'total'=>$total));

        $logo = $this->container->getParameter('folders')['dir_project'].'web/img/logo.png';
        $header_footer = '<page_header> 
                                 <img src="'.$logo.'" width="200" height="70">
                            </page_header>
                            <page_footer>
                                <table style="width: 100%; border: solid 1px black;">
                                    <tr>
                                        <td style="text-align: left;    width: 50%">Generado el '.date('d/m/Y H:i a').'</td>
                                        <td style="text-align: right;    width: 50%">Página [[page_cu]]/[[page_nb]]</td>
                                    </tr>
                                </table>
                            </page_footer>';
        $pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 5, 5, 8),false);
        $pdf->pdf->SetDisplayMode('fullpage');
        $pdf->writeHtml('<page>'.$header_footer.$html.'</page>');
       /* $pdf->writeHtml('<page pageset="old">'.$html2.'</page>');*/

        //Generamos el PDF
        $libroName = $f->eliminarAcentos($libro->getTitulo());
        $pdf->output($titulo.'.pdf');
    }
   

}
