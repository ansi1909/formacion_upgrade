<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\AdminEmpresa;
use Link\ComunBundle\Entity\AdminPais;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Spipu\Html2Pdf\Html2Pdf;

class ReportesJEController extends Controller
{
    public function horasConexionAction($app_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }


        return $this->render('LinkBackendBundle:Reportes:horasConexion.html.twig', array('usuario' => $usuario,
                                                                                         'empresas' => $empresas));

    }

    public function ajaxHorasConexionAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fun = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->request->get('empresa_id');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneEmpresaView = ($timeZoneEmpresa != $yml['parameters']['time_zone']['utc'])? $fun->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml):$timeZoneEmpresa;

        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');
        $excel = $request->request->get('excel');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";
        $desdeUtc = $fun->converDate($desde,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $desde = $desdeUtc->fecha.' '.$desdeUtc->hora;

        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";
        $hastaUtc = $fun->converDate($hasta,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $hasta = $hastaUtc->fecha.' '.$hastaUtc->hora;

        $reporte = $rs->horasConexion($empresa_id, $desde, $hasta, $yml);
        $conexiones = $reporte['conexiones'];
        $columnas_mayores = $reporte['columnas_mayores'];
        $filas_mayores = $reporte['filas_mayores'];
        $reporte_dispositivos = $reporte['reporte_dispositivos'];

        if ($excel)
        {


            //return new response($hastaf);
            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/horasConexion.xlsx';
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Horas de conexión de la empresa').' '.$empresa->getNombre().' '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'. '.$this->get('translator')->trans('Huso horario').': '.$timeZoneEmpresaView);

            // Primera columna
            for ($f=0; $f<=8; $f++)
            {
                $r = $f+3;
                $objWorksheet->setCellValue('A'.$r, $conexiones[$f][0]);
                if (in_array($f, $filas_mayores))
                {
                    $objPHPExcel->getActiveSheet()->getStyle('A'.$r)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('8FC9F0');
                }
            }

            // Data calculada
            for ($f=1; $f<=8; $f++)
            {
                $row = $f+3;
                for ($c=1; $c<=25; $c++)
                {
                    $col = $columnNames[$c];
                    $objWorksheet->setCellValue($col.$row, $conexiones[$f][$c]);
                    if (in_array($f, $filas_mayores) || in_array($c, $columnas_mayores))
                    {
                        $objPHPExcel->getActiveSheet()->getStyle($col.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('8FC9F0');
                        if (in_array($c, $columnas_mayores))
                        {
                            $objPHPExcel->getActiveSheet()->getStyle($col.'3')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('8FC9F0');
                        }
                    }
                }
            }

            // Crea el writer
            $empresaName = $fun->eliminarAcentos($empresa->getNombre());
            $empresaName = strtoupper($empresaName);
            $hoy = date('y-m-d h i');
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
            $path = 'recursos/reportes/HORAS CONEXION '.$empresaName.''.$hoy.'.xlsx';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;

        }
        else {
            $archivo = '';
        }

        $return = array('conexiones' => $conexiones,
                        'columnas_mayores'     => $columnas_mayores,
                        'filas_mayores'        => $filas_mayores,
                        'archivo'              => $archivo,
                        'desdef'               => $desde,
                        'hastaf'               => $hasta,
                        'timeZone'             => $timeZoneEmpresaView,
                        'labels_dispositivos'  => array_keys($reporte_dispositivos['dispositivos']),
                        'values_dispositivos'  => array_values($reporte_dispositivos['dispositivos']),
                        'labels_original'      => array_keys($reporte_dispositivos['dispositivos_original']),
                        'values_original'      => array_values($reporte_dispositivos['dispositivos_original']),
                    );

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxSaveImgHorasConexionAction(Request $request)
    {

        $session = new Session();

        $bin_data = $request->request->get('bin_data');

        $data = str_replace(' ', '+', $bin_data);
        $data = base64_decode($data);
        $im = imagecreatefromstring($data);

        $path = 'recursos/reportes/horasConexion'.$session->get('sesion_id').'.png';
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

    public function pdfHorasConexionAction($empresa_id, $desde, $hasta, Request $request)
    {

        $rs = $this->get('reportes');
        $session = new Session();
        $fun = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        //return new response($hasta);
        $reporte = $rs->horasConexion($empresa_id, $desde, $hasta, $yml);
        $conexiones = $reporte['conexiones'];
        $columnas_mayores = $reporte['columnas_mayores'];
        $filas_mayores = $reporte['filas_mayores'];

        $desde_arr = explode(" ", $desde);
        list($a, $m, $d) = explode("-", $desde_arr[0]);
        $desde = "$d/$m/$a";

        $hasta_arr = explode(" ", $hasta);
        list($a, $m, $d) = explode("-", $hasta_arr[0]);
        $hasta = "$d/$m/$a";
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneEmpresaView = ($timeZoneEmpresa != $yml['parameters']['time_zone']['utc'])? $fun->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml):$timeZoneEmpresa;

        $tabla = $this->renderView('LinkBackendBundle:Reportes:horasConexionTabla.html.twig', array('conexiones' => $conexiones,
                                                                                                    'filas_mayores' => $filas_mayores,
                                                                                                    'columnas_mayores' => $columnas_mayores,
                                                                                                    'empresa' => $empresa,
                                                                                                    'desde' => $desde,
                                                                                                    'hasta' => $hasta,
                                                                                                    'timeZone' => $timeZoneEmpresaView));

        $path = 'recursos/reportes/horasConexion'.$session->get('sesion_id').'.png';
        $src = $this->container->getParameter('folders')['dir_uploads'].$path;

        $grafica = $this->renderView('LinkBackendBundle:Reportes:horasConexionGrafica.html.twig', array('src' => $src));

        $logo = $this->container->getParameter('folders')['dir_project'].'web/img/logo_formacion_smart.png';
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
        $pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(5, 5, 5, 8));
        $pdf->pdf->SetDisplayMode('fullpage');
        $pdf->writeHtml('<page>'.$header_footer.$tabla.'</page>');
        $pdf->writeHtml('<page pageset="old">'.$grafica.'</page>');
        $empresaName = $fun->eliminarAcentos($empresa->getNombre());
        $empresaName = strtoupper($empresaName);

        //Generamos el PDF
        $pdf->output('HORAS CONEXION '.$empresaName.'.pdf');

    }

    public function evaluacionesModuloAction($app_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }

        return $this->render('LinkBackendBundle:Reportes:evaluacionesModulo.html.twig', array('usuario' => $usuario,
                                                                                              'empresas' => $empresas));

    }

    public function ajaxEvaluacionesModuloAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fn = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->request->get('empresa_id');
        $pagina_id = $request->request->get('pagina_id');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneReport = $fn->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml);
        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";
        $desdeUtc = $fn->converDate($desde,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $desde = $desdeUtc->fecha.' '.$desdeUtc->hora;

        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";
        $hastaUtc = $fn->converDate($hasta,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $hasta = $hastaUtc->fecha.' '.$hastaUtc->hora;


        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $listado = $rs->evaluacionesModulo($empresa_id, $pagina_id, $desde, $hasta);

        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/evaluacionesModulo.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Evaluaciones por módulo').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'. '.$this->get('translator')->trans('Huso horario').': '.$timeZoneReport);
        $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$pagina->getCategoria()->getNombre().': '.$pagina->getNombre().'.');

        if (!count($listado))
        {
            $objWorksheet->mergeCells('A5:S5');
            $objWorksheet->setCellValue('A5', $this->get('translator')->trans('No existen registros para esta consulta'));
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

            foreach ($listado as $participante)
            {


                $c_evaluaciones = count($participante['evaluaciones']);
                $limit_iterations = $c_evaluaciones-1;
                $limit_row = $row+$limit_iterations;

                // Estilizar las celdas antes de un posible merge
                for ($f=$row; $f<=$limit_row; $f++)
                {
                    $objWorksheet->getStyle("A$f:U$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                    $objWorksheet->getStyle("A$f:U$f")->getFont()->setSize($font_size); // Tamaño de las letras
                    $objWorksheet->getStyle("A$f:U$f")->getFont()->setName($font); // Tipo de letra
                    $objWorksheet->getStyle("A$f:U$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                    $objWorksheet->getStyle("A$f:U$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                    $objWorksheet->getRowDimension($f)->setRowHeight(35); // Altura de la fila
                }

                if ($limit_iterations > 0)
                {
                    // Merge de las celdas
                    for ($c=0; $c<=13; $c++)
                    {
                        $col = $columnNames[$c];
                        $objWorksheet->mergeCells($col.$row.':'.$col.$limit_row);
                    }
                }
                $fecha_registro = $fn->converDate($participante['fecha_registro'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $fecha_inicio = $fn->converDate($participante['fecha_inicio_programa'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                $objWorksheet->setCellValue('B'.$row, $participante['login']);
                $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                $objWorksheet->setCellValue('E'.$row, $fecha_registro->fecha);
                $objWorksheet->setCellValue('F'.$row, $fecha_registro->hora);
                $objWorksheet->setCellValue('G'.$row, $participante['correo']);
                $objWorksheet->setCellValue('H'.$row, $participante['activo']);
                $objWorksheet->setCellValue('I'.$row, $participante['pais']);
                $objWorksheet->setCellValue('J'.$row, $participante['nivel']);
                $objWorksheet->setCellValue('K'.$row, $participante['campo1']);
                $objWorksheet->setCellValue('L'.$row, $participante['campo2']);
                $objWorksheet->setCellValue('M'.$row, $participante['campo3']);
                $objWorksheet->setCellValue('N'.$row, $participante['campo4']);
                $objWorksheet->mergeCells("O".$row.":O".$limit_row);
                $objWorksheet->setCellValue('O'.$row, $fecha_inicio->fecha);
                $objWorksheet->mergeCells("P".$row.":P".$limit_row);
                $objWorksheet->setCellValue('P'.$row, $fecha_inicio->hora);

                // Datos de las evaluaciones
                foreach ($participante['evaluaciones'] as $evaluacion)
                {
                    $fecha_evaluacion = $fn->converDate($evaluacion['fecha_inicio_prueba'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                    $objWorksheet->setCellValue('Q'.$row, $evaluacion['evaluacion']);
                    $objWorksheet->setCellValue('R'.$row, $evaluacion['estado']);
                    $objWorksheet->setCellValue('S'.$row, $evaluacion['nota']);
                    $objWorksheet->setCellValue('T'.$row, $fecha_evaluacion->fecha);
                    $objWorksheet->setCellValue('U'.$row, $fecha_evaluacion->hora);
                    $row++;
                }

            }

        }

        // Crea el writer
        $empresaName = $fn->eliminarAcentos($empresa->getNombre());
        $paginaName =  $fn->eliminarAcentos($pagina->getnombre());
        $empresaName = strtoupper($empresaName);
        $paginaName = strtoupper($paginaName);
        $hoy = date('y-m-d h i');
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
        $path = 'recursos/reportes/EVALUACIONES '.$paginaName.' '.$empresaName.' '.$hoy.'.xlsx';
        $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
        $writer->save($xls);

        $archivo = $this->container->getParameter('folders')['uploads'].$path;
        $document_name = 'EVALUACIONES '.$paginaName.'_'.$empresaName.'.xls';
        $bytes = filesize($xls);
        $document_size = $fn->fileSizeConvert($bytes);

        $return = array('archivo' => $archivo,
                        'document_name' => $document_name,
                        'document_size' => $document_size);

        $return =  json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function resumenRegistrosAction($app_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }

        return $this->render('LinkBackendBundle:Reportes:resumenRegistros.html.twig', array('usuario' => $usuario,
                                                                                            'empresas' => $empresas));

    }

    public function ajaxResumenRegistrosAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fn = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->request->get('empresa_id');
        $pagina_id = $request->request->get('pagina_id');
        $desde = $request->request->get('desde');
        $hasta = $request->request->get('hasta');
        $excel = $request->request->get('excel');

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneEmpresaView = ($timeZoneEmpresa != $yml['parameters']['time_zone']['utc'])? $fn->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml):$timeZoneEmpresa;
        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $desde_arr = explode(" ", $desde);
        list($d, $m, $a) = explode("/", $desde_arr[0]);
        $time = explode(":", $desde_arr[1]);
        $h = (int) $time[0];
        $min = $time[1];
        if ($desde_arr[2] == 'pm')
        {
            if ($h != 12)
            {
                // Se le suman 12 horas
                $h += 12;
            }
        }
        else {
            if ($h == 12)
            {
                // Es la hora 0
                $h = '00';
            }
            elseif ($h < 10) {
                // Valor en dos caracteres
                $h = '0'.$h;
            }
        }
        $desdef = "$a-$m-$d $h:$min:59";

        $hasta_arr = explode(" ", $hasta);
        list($d, $m, $a) = explode("/", $hasta_arr[0]);
        $time = explode(":", $hasta_arr[1]);
        $h = (int) $time[0];
        $min = $time[1];
        if ($hasta_arr[2] == 'pm')
        {
            if ($h != 12)
            {
                // Se le suman 12 horas
                $h += 12;
            }
        }
        else {
            if ($h == 12)
            {
                // Es la hora 0
                $h = '00';
            }
            elseif ($h < 10) {
                // Valor en dos caracteres
                $h = '0'.$h;
            }
        }
        $hastaf = "$a-$m-$d $h:$min:59";

        $desdeUtc = $fn->converDate($desdef,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $desdeUtc = $desdeUtc->fecha.' '.$desdeUtc->hora;

        $hastaUtc = $fn->converDate($hastaf,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $hastaUtc = $hastaUtc->fecha.' '.$hastaUtc->hora;

        //print_r($desdef.' '.$hastaf.' '.$desdeUtc.' '.$hastaUtc);
         $reporte = $rs->resumenRegistros($empresa_id, $pagina_id, $desdeUtc, $hastaUtc);
        //$reporte = $rs->resumenRegistros($empresa_id, $pagina_id, $desdef, $hastaf); --parametros anteriores

        $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $pagina_id,
                                                                                                    'empresa' => $empresa_id));

        $return = array('reporte' => $reporte,
                        'week_before' => $this->get('translator')->trans('Al').' '.$desde,
                        'now' => $this->get('translator')->trans('Al').' '.$hasta,
                        'week_beforef' => $desdeUtc,
                        'timeZone' =>$timeZoneEmpresaView,
                        'nowf' => $hastaUtc,
                        'empresa' => $empresa->getNombre(),
                        'programa' => $pagina->getCategoria()->getNombre().' '.$pagina->getNombre(),
                        'fecha_inicio' => $pagina_empresa->getFechaInicio()->format('d/m/Y'),
                        'fecha_vencimiento' => $pagina_empresa->getFechaVencimiento()->format('d/m/Y'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxSaveImgResumenRegistrosAction(Request $request)
    {

        $session = new Session();

        $bin_data = $request->request->get('bin_data');
        $chart = $request->request->get('chart');

        $data = str_replace(' ', '+', $bin_data);
        $data = base64_decode($data);
        $im = imagecreatefromstring($data);

        $path = 'recursos/reportes/resumenRegistros'.$session->get('sesion_id').'_'.$chart.'.png';
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

    public function pdfResumenRegistrosAction($empresa_id,$pagina_id,$desdef,$hastaf, Request $request)
    {

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $rs = $this->get('reportes');
        $session = new Session();
        $fun = $this->get('funciones');

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneEmpresaView = ($timeZoneEmpresa != $yml['parameters']['time_zone']['utc'])? $fun->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml):$timeZoneEmpresa;

        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $datetime = new \DateTime($desdef);
        $desde = $datetime->format("d/m/Y h:i a");

        $datetime = new \DateTime($hastaf);
        $hasta = $datetime->format("d/m/Y h:i a");
        date_default_timezone_set ( $timeZoneEmpresa ) ;

        $reporte = $rs->resumenRegistros($empresa_id, $pagina_id, $desdef, $hastaf);

        $path1 = 'recursos/reportes/resumenRegistros'.$session->get('sesion_id').'_1.png';
        $src1 = $this->container->getParameter('folders')['dir_uploads'].$path1;

        $path2 = 'recursos/reportes/resumenRegistros'.$session->get('sesion_id').'_2.png';
        $src2 = $this->container->getParameter('folders')['dir_uploads'].$path2;

        $path3 = 'recursos/reportes/resumenRegistros'.$session->get('sesion_id').'_3.png';
        $src3 = $this->container->getParameter('folders')['dir_uploads'].$path3;

        $path4 = 'recursos/reportes/resumenRegistros'.$session->get('sesion_id').'_4.png';
        $src4 = $this->container->getParameter('folders')['dir_uploads'].$path4;

        $html1 = $this->renderView('LinkBackendBundle:Reportes:pdfResumenRegistrosPage1.html.twig', array('reporte' => $reporte,
                                                                                                          'week_before' => $this->get('translator')->trans('Al').' '.$desde,
                                                                                                          'now' => $this->get('translator')->trans('Al').' '.$hasta,
                                                                                                          'programa' => $pagina->getCategoria()->getNombre().' '.$pagina->getNombre(),
                                                                                                          'empresa' => $empresa->getNombre(),
                                                                                                          'timeZone'=>$timeZoneEmpresaView,
                                                                                                          'src' => array('src1' => $src1,
                                                                                                                         'src2' => $src2)));

        $html2 = $this->renderView('LinkBackendBundle:Reportes:pdfResumenRegistrosPage2.html.twig', array('reporte' => $reporte,
                                                                                                          'week_before' => $this->get('translator')->trans('Al').' '.$desde,
                                                                                                          'now' => $this->get('translator')->trans('Al').' '.$hasta,
                                                                                                          'programa' => $pagina->getCategoria()->getNombre().' '.$pagina->getNombre(),
                                                                                                          'empresa' => $empresa->getNombre(),
                                                                                                          'src' => array('src3' => $src3,
                                                                                                                         'src4' => $src4)));

        $logo = $this->container->getParameter('folders')['dir_project'].'web/img/logo_formacion_smart.png';
        $header_footer = '<page_header>
                                 <img src="'.$logo.'" width="200" height="50">
                            </page_header>
                            <page_footer>
                                <table style="width: 100%; border: solid 1px black;">
                                    <tr>

                                        <td style="text-align: right;    width: 50%">Página [[page_cu]]/[[page_nb]]</td>
                                    </tr>
                                </table>
                            </page_footer>';
        $pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 5, 5, 8));
        $pdf->pdf->SetDisplayMode('fullpage');
        $pdf->writeHtml('<page>'.$header_footer.$html1.'</page>');
        $pdf->writeHtml('<page pageset="old">'.$html2.'</page>');

        //Generamos el PDF
        $empresaName = $fun->eliminarAcentos($empresa->getNombre());
        $pdf->output('resumen_registros_'.$empresa->getNombre().'.pdf');

    }


}
