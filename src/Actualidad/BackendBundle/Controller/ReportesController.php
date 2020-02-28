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

class ReportesController extends Controller
{

    public function estadisticasLibroAction()
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $paginas = array();

        // Grados para el filtro
        $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g 
                                    ORDER BY g.nombre ASC");
        $grados = $query->getResult();

        // Empresas para el filtro
        $empresas = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->findAll();

        return $this->render('ActualidadBackendBundle:Reportes:estadisticasLibro.html.twig', array('grados' => $grados,
                                                                                                   'empresas' => $empresas,
                                                                                                   'paginas' => $paginas));
    }

    public function librosActivosAction()
    {
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $paginas = array();

        // Grados para el filtro
        $query = $em->createQuery("SELECT g FROM ActualidadComunBundle:AdminGrado g 
                                    ORDER BY g.nombre ASC");
        $grados = $query->getResult();

        // Empresas para el filtro
        $empresas = $em->getRepository('ActualidadComunBundle:AdminEmpresa')->findAll();

        return $this->render('ActualidadBackendBundle:Reportes:librosActivos.html.twig', array('grados' => $grados,
                                                                                               'empresas' => $empresas,
                                                                                               'paginas' => $paginas));

    }

    public function ajaxGetLibrosAction(Request $request)
    {

        try {

            $em = $this->getDoctrine()->getManager();
            $f = $this->get('funciones');
            $grado_id = $request->query->get('grado_id');
            $empresa_id = $request->query->get('empresa_id');
            $some = 0;
            $paginas = array();

            $qb = $em->createQueryBuilder();
            $qb->select('p')
               ->from('ActualidadComunBundle:EaPagina', 'p')
               ->andWhere('p.pagina IS NULL')
               ->orderBy('p.orden', 'ASC');
            if ($grado_id)
            {
                $qb->andWhere('p.grado = :grado_id');
                $parametros['grado_id'] = $grado_id;
                $some = 1;
            }
            if ($empresa_id)
            {
                $qb->andWhere('p.empresa = :empresa_id');
                $parametros['empresa_id'] = $empresa_id;
                $some = 1;
            }
            if ($some)
            {
                $qb->setParameters($parametros);
            }
            $query = $qb->getQuery();
            $pages = $query->getResult();

            foreach ($pages as $page)
            {

                $query = $em->getConnection()->prepare('SELECT
                                                        fnejemplares_libros(:re, :plibro_id) as
                                                        resultado; fetch all from re;');
                $re = 're';
                $query->bindValue(':re', $re, \PDO::PARAM_STR);
                $query->bindValue(':plibro_id', $page->getId(), \PDO::PARAM_INT);
                $query->execute();
                $r = $query->fetchAll();

                //return new Response($returnr);

                $subpaginas = $f->subPaginas($page->getId());
                $activos = $r[0.1];
                $inactivos = $r[0.2];
                $total_ejemplares = $activos + $inactivos;

                $paginas[] = array('id' => $page->getId(),
                                   'empresa' => $page->getEmpresa()->getNombre(),
                                   'grado' => $page->getGrado()->getNombre(),
                                   'titulo' => $page->getTitulo(),
                                   'tipo' => $page->getTipoPagina()->getNombre(),
                                   'subpaginas' => $subpaginas,
                                   'ejemplares' => $r[0],
                                   'total' => $total_ejemplares);

            }

            $html = $this->renderView('ActualidadBackendBundle:Reportes:listadoLibros.html.twig', array('paginas' => $paginas));
            
            $resultado = array('html' => $html);
            $return = array('ok' => 1,
                            'msg' => 'msg',
                            'resultado' => $resultado);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage(),
                            'resultado' => '');
            return new JsonResponse($return);
        }
        
    }

    public function ajaxExcelLibrosActivosAction(Request $request)
    {

        try {

            $em = $this->getDoctrine()->getManager();
            $f = $this->get('funciones');
            $grado_id = $request->query->get('grado_id');
            $empresa_id = $request->query->get('empresa_id');
            $some = 0;
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml')); 
            $yml2 = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml')); 

            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/reporteLibrosActivos.xlsx';
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
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

            $query = $em->getConnection()->prepare('SELECT
                                                    fnlibros_activos(:re, :pgrado_id, :pempresa_id) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':pgrado_id', $grado_id, \PDO::PARAM_INT);
            $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();

            foreach($r as $re)
            {  

                $total = $re['ejemplares'] + $re['ejemplares_sin'];
                if($re['tipo'] == $yml2['parameters']['tipo_pagina']['libro_alumnos'])
                {
                    $tipo = $this->get('translator')->trans('Libro para alumnos');
                }
                elseif($re['tipo'] == $yml2['parameters']['tipo_pagina']['libro_profesores'])
                {
                    $tipo = $this->get('translator')->trans('Libro de guía para docentes');
                }
                $objWorksheet->getStyle("A$row:G$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:G$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:G$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:G$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:G$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(25); // Altura de la fila
            
                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $re['id']);
                $objWorksheet->setCellValue('B'.$row, $re['grado_id']);
                $objWorksheet->setCellValue('C'.$row, $re['titulo']);
                $objWorksheet->setCellValue('D'.$row, $tipo);
                $objWorksheet->setCellValue('E'.$row, $re['ejemplares']);
                $objWorksheet->setCellValue('F'.$row, $re['ejemplares_sin']);
                $objWorksheet->setCellValue('G'.$row, $total);
                $row++;

            }

            // Crea el writer
            $titulo = 'ReporteLibrosActivos';
            $hoy = date('d-m-Y');
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
            $path = 'reportes/'.$titulo.'_'.$hoy.'.xls';
            $xls = $yml['parameters']['folders']['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $yml['parameters']['folders']['uploads'].$path;
            $document_name = 'reporte_'.$titulo.'_'.$hoy.'.xls';
            $bytes = filesize($xls);
            $document_size = $f->fileSizeConvert($bytes);

            $html = $archivo;

            $resultado = array('html'=>$html);
            $return = array('ok' => 1,
                            'msg' => 'msg',
                            'resultado' => $resultado);
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage(),
                            'resultado' => '');
            return new JsonResponse($return);
        }

    }

    public function ajaxEstadisticaLibroAction(Request $request)
    {

        try {

            $em = $this->getDoctrine()->getManager();
            $f = $this->get('reportes');
            $pagina_id = $request->request->get('pagina_id');
            $fecha_desde = $request->request->get('fecha_desde');
            $fecha_hasta = $request->request->get('fecha_hasta');
            $desde_arr = explode(" ", $fecha_desde);
            list($d, $m, $a) = explode("/", $desde_arr[0]);
            $desdef = "$a-$m-$d";

            $hasta_arr = explode(" ", $fecha_hasta);
            list($d, $m, $a) = explode("/", $hasta_arr[0]);
            $hastaf = "$a-$m-$d";

            $reporte = $f->estadisticaLibros($pagina_id,$desdef,$hastaf);
            
            $resultado = array('reporte' => $reporte,
                               'desde' => $desdef,
                               'hasta' => $hastaf);
            $return = array('ok' => 1,
                            'msg' => '',
                            'resultado' => $resultado);
            
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));

        }
        catch (\Exception $ex) {
            $return = array('ok' => 0,
                            'msg' => $ex->getMessage(),
                            'resultado' => '');
            return new JsonResponse($return);
        }
        
    }

    public function ajaxLibrosAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $grado_id = $request->query->get('grado_id');
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery("SELECT l FROM ActualidadComunBundle:EaPagina l 
                                    WHERE l.pagina IS NULL
                                    AND l.empresa = :empresa_id
                                    AND l.grado = :grado_id
                                    ORDER BY l.pagina ASC")
                    ->setParameters(array('empresa_id' => $empresa_id,
                                          'grado_id' => $grado_id));
        $libros = $query->getResult();

        $html='<select class="form_sty_sel form-control" style="border-radius: 5px" id="pagina_id" name="pagina_id">
                <option value=""></option>';

        foreach($libros as $libro)
        {
            $html.= '
                        <option value="'.$libro->getId().'">'.$libro->getTitulo().' - '.$libro->getTipoPagina()->getNombre().' </option>
                    ';
        }

        $html.='</select>
                <span class="fa fa-sort-numeric-asc"></span>
                <span class="bttn_d"><img src="'.$f->getWebDirectory().'/img/down-arrowbck.png'.'"></span>
                ';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxSaveImgEstadisticaLibroAction(Request $request)
    {
        
        $session = new Session();
        
        $bin_data = $request->request->get('bin_data');
        $chart = $request->request->get('chart');
        
        $data = str_replace(' ', '+', $bin_data);
        $data = base64_decode($data);
        $im = imagecreatefromstring($data);
        
        $path = 'reportes/estadisticalibro'.$session->get('sesion_id').'_'.$chart.'.png';
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

    public function pdfEstadisticaLibroAction($empresa_id, $pagina_id, $desdef, $hastaf, Request $request)
    {
        
        $rs = $this->get('reportes');
        $session = new Session();
        $fun = $this->get('funciones');

        $empresa = $this->getDoctrine()->getRepository('ActualidadComunBundle:AdminEmpresa')->find($empresa_id);
        $pagina = $this->getDoctrine()->getRepository('ActualidadComunBundle:EaPagina')->find($pagina_id);

        $datetime = new \DateTime($desdef);
        $desde = $datetime->format("d/m/Y ");
        
        $datetime = new \DateTime($hastaf);
        $hasta = $datetime->format("d/m/Y ");

        $reporte = $rs->estadisticaLibros( $pagina_id, $desdef, $hastaf);

        $path1 = 'reportes/estadisticalibro'.$session->get('sesion_id').'_1.png';
        $src1 = $this->container->getParameter('folders')['dir_uploads'].$path1;

        $path2 = 'reportes/estadisticalibro'.$session->get('sesion_id').'_2.png';
        $src2 = $this->container->getParameter('folders')['dir_uploads'].$path2;

        $html1 = $this->renderView('ActualidadBackendBundle:Reportes:pdfEstadisticaLibroPage1.html.twig', array('reporte' => $reporte,
                                                                                                                'week_before' => $this->get('translator')->trans('Al').' '.$desde,
                                                                                                                'now' => $this->get('translator')->trans('Al').' '.$hasta,
                                                                                                                'libro' => $pagina->getTitulo(),
                                                                                                                'empresa' => $empresa->getNombre(),
                                                                                                                'src' =>  array('src1' => $src1,
                                                                                                                                'src2' => $src2)));

       /* $html2 = $this->renderView('ActualidadBackendBundle:Reportes:pdfEstadisticaLibroPage2.html.twig', array('reporte' => $reporte,
                                                                                                                'week_before' => $this->get('translator')->trans('Al').' '.$desde,
                                                                                                                'now' => $this->get('translator')->trans('Al').' '.$hasta,
                                                                                                                'programa' => $pagina->getTitulo(),
                                                                                                                'empresa' => $empresa->getNombre(),
                                                                                                                'src' =>  $src2 ));*/

        $logo = $this->container->getParameter('folders')['dir_project'].'web/img/logo.png';
        $header_footer = '<page_header> 
                                 <img src="'.$logo.'" width="200" height="50">
                            </page_header>
                            <page_footer>
                                <table style="width: 100%; border: solid 1px black;">
                                    <tr>
                                        <td style="text-align: left;    width: 50%">Generado el '.date('d/m/Y H:i a').'</td>
                                        <td style="text-align: right;    width: 50%">Página [[page_cu]]/[[page_nb]]</td>
                                    </tr>
                                </table>
                            </page_footer>';
        $pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 5, 5, 8));
        $pdf->pdf->SetDisplayMode('fullpage');
        $pdf->writeHtml('<page>'.$header_footer.$html1.'</page>');
       /* $pdf->writeHtml('<page pageset="old">'.$html2.'</page>');*/

        //Generamos el PDF
        $libroName = $fun->eliminarAcentos($pagina->getTitulo());
        $pdf->output('estadisticaLibro_'.$libroName.'.pdf');

    }

}
