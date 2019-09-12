<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;


class ReportesController extends Controller
{
    public function indexAction($app_id, $r, $pagina_id, $empresa_id, Request $request)
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

        if ($request->getMethod() == 'POST')
        {

            $empresa_id = $request->request->get('empresa_id');
            $reporte = $request->request->get('reporte');
            $pagina_id = $request->request->get('programa_id');
            $nivel_id = $request->request->get('nivel_id');
            $nivel_id = $nivel_id ? $nivel_id : 0;
            $pagina_id = $pagina_id ? $pagina_id : 0;
            $i = 1;
            $pagina = array();
            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);


            if($reporte == 1)
            {
                $encabezado = 'empresa';
                
            }
            elseif($reporte == 2)
            {
                $encabezado = 'registrados';

            }
            elseif($reporte == 3)
            {
                $encabezado = 'cursando';
            }
            elseif($reporte == 4)
            {
                $encabezado = 'aprobados';
            }
            elseif($reporte == 5)
            {
                $encabezado = 'no iniciados';
            }

            if ($pagina_id)
            {
                $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            }
            $query = $em->getConnection()->prepare('SELECT
                                                    fnlistado_participantes(:re, :preporte, :pempresa_id, :pnivel_id, :ppagina_id) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':preporte', $reporte, \PDO::PARAM_INT);
            $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
            $query->bindValue(':pnivel_id', $nivel_id, \PDO::PARAM_INT);
            $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
            $query->execute();
            $res = $query->fetchAll();

            // Solicita el servicio de excel
            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/ListadoParticipantes.xlsx';
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Listado de participantes').' '.$this->get('translator')->trans($encabezado).'.');
            if ($pagina_id)
            {
                $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$pagina->getCategoria()->getNombre().': '.$pagina->getNombre().'.');
            }else
            {
                $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. ');
            }
            if (!count($res))
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

                foreach ($res as $re)
                {

                    $correo = trim($re['correo']) ? trim($re['correo']) : trim($re['correo2']);
                    $acceso = $re['activo'] = "TRUE" ? 'Sí' : 'No';
                    $logueado = $re['logueado'] > 0 ? 'Sí' : 'No';
                    $fecha = explode(" ", $re['fecha_registro']);
                    
                    $objWorksheet->getStyle("A$row:O$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                    $objWorksheet->getStyle("A$row:O$row")->getFont()->setSize($font_size); // Tamaño de las letras
                    $objWorksheet->getStyle("A$row:O$row")->getFont()->setName($font); // Tipo de letra
                    $objWorksheet->getStyle("A$row:O$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                    $objWorksheet->getStyle("A$row:O$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                    $objWorksheet->getRowDimension($row)->setRowHeight(40); // Altura de la fila
                

                    // Datos de las columnas comunes
                    $objWorksheet->setCellValue('A'.$row, $re['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $re['login']);
                    $objWorksheet->setCellValue('C'.$row, $re['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $re['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $fecha['0']);
                    $objWorksheet->setCellValue('F'.$row, $fecha['1'].$fecha[2]);
                    $objWorksheet->setCellValue('G'.$row, $correo);
                    $objWorksheet->setCellValue('H'.$row, $acceso);
                    $objWorksheet->setCellValue('I'.$row, $logueado);
                    $objWorksheet->setCellValue('J'.$row, $re['pais']);
                    $objWorksheet->setCellValue('K'.$row, $re['nivel']);
                    $objWorksheet->setCellValue('L'.$row, $re['campo1']);
                    $objWorksheet->setCellValue('M'.$row, $re['campo2']);
                    $objWorksheet->setCellValue('N'.$row, $re['campo3']);
                    $objWorksheet->setCellValue('O'.$row, $re['campo4']);
                    $row++;

                }

            }
           

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
            $empresaName = $f->eliminarAcentos($empresa->getNombre());
            $empresaName = strtoupper($empresaName);
            $encabezado = strtoupper($encabezado);
            $hoy = date('y-m-d');
            
            // Envia la respuesta del controlador
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // Agrega los headers requeridos
            if($pagina)
            {   
                $programaName = $f->eliminarAcentos($pagina->getNombre());
                $programaName = strtoupper($programaName);
                $dispositionHeader = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        $encabezado.' '.$programaName.' '.$empresaName.'.xlsx'
                );
            }else{
                $dispositionHeader = $response->headers->makeDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                        $encabezado.' '.$empresaName.'.xlsx'
                );
            }

            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;

        }

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                   array('nombre' => 'ASC' ));
        } 

        return $this->render('LinkBackendBundle:Reportes:index.html.twig', array('empresas' => $empresas,
                                                                                 'usuario_empresa' => $usuario_empresa,
                                                                                 'usuario' => $usuario,
                                                                                 'reporte' => $r,
                                                                                 'pagina_id' => $pagina_id,
                                                                                 'empresa_dashboard' => $empresa_id));    
    }

    public function ajaxProgramasEAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_selected');
        $reporte_id = $request->query->get('reporte_id');

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina IS NULL
                                   ORDER BY p.nombre ASC')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        if($empresa_id == 0)
        {
            $options = '<option value="0">Debes elegir una empresa</option>';
        }
        else{

            $options = '<option value=" "></option>';
            if($reporte_id){
                $options .= '<option value="0">Todos los programas</option>';
            }
            
            foreach ($paginas as $pagina)
            {
                if ($pagina->getPagina()->getId() == $pagina_id) 
                {
                    $options .= '<option value="'.$pagina->getPagina()->getId().'" selected >'.$pagina->getPagina()->getCategoria()->getNombre().': ' .$pagina->getPagina()->getNombre().'  </option>';
                }
                else
                {
                    $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getCategoria()->getNombre().': ' .$pagina->getPagina()->getNombre().' </option>';
                }
            
            }
        }

        
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxGrupoSeleccionAAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_selected = $request->query->get('pagina_selected');

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina IS NULL
                                   ORDER BY p.nombre ASC')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $valores = array();
        foreach ($paginas as $pe)
        {
            $valores[] = array('id' => $pe->getPagina()->getId(),
                               'nombre' => $pe->getPagina()->getCategoria()->getNombre().': '.$pe->getPagina()->getNombre(),
                               'selected' => $pe->getPagina()->getId() == $pagina_selected ? 'selected' : '');
        }
        
        $html = $this->renderView('LinkBackendBundle:Reportes:grupoSeleccion.html.twig', array('valores' => $valores));

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxListadoAprobadosAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $rs = $this->get('reportes');

        $empresa_id = $request->request->get('empresa_id');
        $paginas_id = $request->request->get('programas');
        $pagina_selected = $request->request->get('pagina_selected');
        $preseleccion = $request->request->get('preseleccion');

        if ($preseleccion == '1')
        {
            $paginas_id = array($pagina_selected);
        }

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $listado = $rs->participantesAprobados($empresa_id, $paginas_id);

        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/ListadoParticipantesAprobados.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $abecederario = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        // Carga inicial de los nombres de las columnas
        $columnNames = $abecederario;
        $maxV = 1; // Cantidad de vueltas adicionales sobre el alfabeto para cargar el arreglo de columnNames
        $v = 0;
        while ($v < $maxV)
        {
            foreach ($abecederario as $abc)
            {
                $columnNames[] = $abecederario[$v].$abc;
            }
            $v++;
        }

        $programCells = count($paginas_id)*5;
        $lastColumnIndex = 14 + $programCells; // 13 es el índice de la columna N + 1 de la columna APROBADOS
        $lastColumn = $columnNames[$lastColumnIndex];

        // Encabezado
        $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre());

        $font_size = 11;
        $font = 'Arial';
        $horizontal_aligment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
        $vertical_aligment = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
        $horizontal_left = \PHPExcel_Style_Alignment::HORIZONTAL_LEFT;

        // Fila de nombres de programas
        $row = 3;
        $styleProgramHeader = array(
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                )
            ),
            'font' => array('bold' => true)
        );

        // Estilizar las celdas de los nombres de los programas antes del merge
        $p = 0;
        $col = 15; // Columna O

        foreach ($listado['paginas'] as $pagina_id => $pagina)
        {
            if ($p == 0)
            {
                $objWorksheet->setCellValue($columnNames[$col].$row, $pagina['nombre']);
            }
            else {

                $col += 5;

                // Ancho de las columnas
                $objWorksheet->getColumnDimension($columnNames[$col])->setWidth(12);
                $objWorksheet->getColumnDimension($columnNames[$col+1])->setWidth(16);
                $objWorksheet->getColumnDimension($columnNames[$col+2])->setWidth(14);
                $objWorksheet->getColumnDimension($columnNames[$col+3])->setWidth(16);
                $objWorksheet->getColumnDimension($columnNames[$col+4])->setWidth(14);

                // Fila de nombre de programas
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->applyFromArray($styleProgramHeader); //bordes
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($pagina['bgcolor']);
                $objWorksheet->mergeCells($columnNames[$col].$row.":".$columnNames[$col+4].$row);
                $objWorksheet->setCellValue($columnNames[$col].$row, $pagina['nombre']);

                // Header interno de cada programa
                $inHeaderRow = $row+1;
                $objWorksheet->getStyle($columnNames[$col].$inHeaderRow.":".$columnNames[$col+4].$inHeaderRow)->applyFromArray($styleProgramHeader); //bordes
                $objWorksheet->getStyle($columnNames[$col].$inHeaderRow.":".$columnNames[$col+4].$inHeaderRow)->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle($columnNames[$col].$inHeaderRow.":".$columnNames[$col+4].$inHeaderRow)->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle($columnNames[$col].$inHeaderRow.":".$columnNames[$col+4].$inHeaderRow)->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle($columnNames[$col].$inHeaderRow.":".$columnNames[$col+4].$inHeaderRow)->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getStyle($columnNames[$col].$inHeaderRow.":".$columnNames[$col+4].$inHeaderRow)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($pagina['bgcolor']);
                $objWorksheet->setCellValue($columnNames[$col].$inHeaderRow, $this->get('translator')->trans('Promedio'));
                $objWorksheet->setCellValue($columnNames[$col+1].$inHeaderRow, $this->get('translator')->trans('Fecha inicio'));
                $objWorksheet->setCellValue($columnNames[$col+2].$inHeaderRow, $this->get('translator')->trans('Hora inicio'));
                $objWorksheet->setCellValue($columnNames[$col+3].$inHeaderRow, $this->get('translator')->trans('Fecha fin'));
                $objWorksheet->setCellValue($columnNames[$col+4].$inHeaderRow, $this->get('translator')->trans('Hora fin'));

            }
            $p++;
        }

        // Columna de APROBADOS
        $col += 5;
        $inHeaderRow = $row+1;
        $objWorksheet->getColumnDimension($columnNames[$col])->setWidth(14);
        $objWorksheet->getStyle($columnNames[$col].$inHeaderRow)->applyFromArray($styleProgramHeader); //bordes
        $objWorksheet->getStyle($columnNames[$col].$inHeaderRow)->getFont()->setSize($font_size); // Tamaño de las letras
        $objWorksheet->getStyle($columnNames[$col].$inHeaderRow)->getFont()->setName($font); // Tipo de letra
        $objWorksheet->getStyle($columnNames[$col].$inHeaderRow)->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
        $objWorksheet->getStyle($columnNames[$col].$inHeaderRow)->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
        $objWorksheet->setCellValue($columnNames[$col].$inHeaderRow, $this->get('translator')->trans('Aprobados'));

        if (!count($listado['participantes']))
        {
            $objWorksheet->mergeCells('A5:'.$lastColumn.'5');
            $objWorksheet->setCellValue('A5', $this->get('translator')->trans('No existen registros para esta consulta'));
        }
        else {

            $i = 0;
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );

            $row = 5;

            foreach ($listado['participantes'] as $participante)
            {
                $fecha = explode(" ", $participante['fecha_registro']);
                $acceso = $re['activo'] = "TRUE" ? 'Sí' : 'No';
                // Estilizar toda la fila, excepto el bgcolor de las celdas de los programas
                $objWorksheet->getStyle("A".$row.":".$lastColumn.$row)->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A".$row.":".$lastColumn.$row)->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A".$row.":".$lastColumn.$row)->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A".$row.":".$lastColumn.$row)->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A".$row.":".$lastColumn.$row)->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(35); // Altura de la fila

                // Datos del listado
                $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                $objWorksheet->setCellValue('B'.$row, $participante['login']);
                $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                $objWorksheet->setCellValue('E'.$row, $fecha[0]);
                $objWorksheet->setCellValue('F'.$row, $fecha[1].$fecha[2]);
                $objWorksheet->setCellValue('G'.$row, $participante['correo']);
                $objWorksheet->setCellValue('H'.$row, $acceso);
                $objWorksheet->setCellValue('I'.$row, $participante['logueado']);
                $objWorksheet->setCellValue('J'.$row, $participante['pais']);
                $objWorksheet->setCellValue('K'.$row, $participante['nivel']);
                $objWorksheet->setCellValue('L'.$row, $participante['campo1']);
                $objWorksheet->setCellValue('M'.$row, $participante['campo2']);
                $objWorksheet->setCellValue('N'.$row, $participante['campo3']);
                $objWorksheet->setCellValue('O'.$row, $participante['campo4']);
                
                // Datos de cada programa
                $col = 15; // Columna O
                $aprobados = 0;
                foreach ($listado['paginas'] as $pagina_id => $pagina)
                {
                    $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($pagina['bgcolor']);
                    if (array_key_exists($pagina_id, $participante['paginas']))
                    {
                        $aprobados++;
                        $objWorksheet->setCellValue($columnNames[$col].$row, $participante['paginas'][$pagina_id]['promedio']);
                        $objWorksheet->setCellValue($columnNames[$col+1].$row, $participante['paginas'][$pagina_id]['fecha_inicio']);
                        $objWorksheet->setCellValue($columnNames[$col+2].$row, $participante['paginas'][$pagina_id]['hora_inicio']);
                        $objWorksheet->setCellValue($columnNames[$col+3].$row, $participante['paginas'][$pagina_id]['fecha_fin']);
                        $objWorksheet->setCellValue($columnNames[$col+4].$row, $participante['paginas'][$pagina_id]['hora_fin']);
                    }
                    $col += 5;
                }

                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col].$row)->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col].$row)->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col].$row)->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col].$row)->getAlignment()->setHorizontal($horizontal_left); // Alineado horizontal
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col].$row)->getAlignment()->setVertical($vertical_aligment); // Alineado vertical

                $objWorksheet->setCellValue($columnNames[$col].$row, $aprobados);

                $row++;

            }

            // Fila de totales
            $styleProgramTotal = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    )
                ),
                'font' => array('bold' => true)
            );

            // Estilizar las celdas de los totales de los programas antes del merge
            $col = 15; // Columna O

            foreach ($listado['paginas'] as $pagina_id => $pagina)
            {
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->applyFromArray($styleProgramTotal); //bordes
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getAlignment()->setHorizontal($horizontal_left); // Alineado horizontal
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getStyle($columnNames[$col].$row.":".$columnNames[$col+4].$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($pagina['bgcolor']);
                $objWorksheet->mergeCells($columnNames[$col].$row.":".$columnNames[$col+4].$row);
                $objWorksheet->setCellValue($columnNames[$col].$row, $this->get('translator')->trans('Total aprobados').': '.$pagina['total']);
                $col += 5;
            }

        }

        // Crea el writer
        $empresaName = $f->eliminarAcentos($empresa->getNombre());
        $empresaName = strtoupper($empresaName);
        $hoy = date('y-m-d h i');
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
        $path = 'recursos/reportes/APROBADOS '.$empresaName.' '.$hoy.'.xls';
        $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
        $writer->save($xls);

        $archivo = $this->container->getParameter('folders')['uploads'].$path;
        $document_name = 'APROBADOS '.$empresaName.' '.$hoy.'.xls';
        $bytes = filesize($xls);
        $document_size = $f->fileSizeConvert($bytes);
        
        $return = array('archivo' => $archivo,
                        'document_name' => $document_name,
                        'document_size' => $document_size);

        $return =  json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxListadoParticipantesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $empresa_id = $request->query->get('empresa_id');
        $nivel_id = $request->query->get('nivel_id');
        $pagina_id = $request->query->get('pagina_id');
        $reporte = $request->query->get('reporte');

        if($pagina_id == " ")
        {
            $html = 'Debe elegir un programa';
        }else
        {

            // Llamada a la función de BD que trae el listado de participantes
            $query = $em->getConnection()->prepare('SELECT
                                                    fnlistado_participantes(:re, :preporte, :pempresa_id, :pnivel_id, :ppagina_id) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':preporte', $reporte, \PDO::PARAM_INT);
            $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
            $query->bindValue(':pnivel_id', $nivel_id, \PDO::PARAM_INT);
            $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();
            
            $html = '<table class="table" id="dt">
                        <thead class="sty__title">
                            <tr>
                                <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                                <th class="hd__title">'.$this->get('translator')->trans('Login').'</th>
                                <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                                <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                                <th class="hd__title">'.$this->get('translator')->trans('Fecha de registro').'</th>
                                <th class="hd__title">'.$this->get('translator')->trans('País').'</th>
                                <th class="hd__title">'.$this->get('translator')->trans('Logueado').'</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: .7rem;">';
            
            foreach ($r as $ru)
            {
                $activo = $ru['logueado'] > 0 ? $this->get('translator')->trans('Sí') : 'No';
                $html .= '<tr>
                            <td><a class="detail" data-toggle="modal" data-target="#detailModal" data="'.$ru['login'].'" empresa_id="'.$empresa_id.'" href="#">'.$ru['nombre'].' '.$ru['apellido'] .'</a></td>
                            <td>'.$ru['login'].'</td>
                            <td>'.$ru['nivel'].'</td>
                            <td>'.$ru['correo'].'</td>
                            <td>'.$ru['fecha_registro'].'</td>
                            <td>'.$ru['pais'].'</td>
                            <td>'.$activo.'</td>
                        </tr>';
            }

            $html .= '</tbody>
                    </table>';
        }
        $return = array('html' => $html);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function interaccionColaborativoAction($app_id, Request $request)
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

        return $this->render('LinkBackendBundle:Reportes:interaccionColaborativo.html.twig', array('usuario' => $usuario,
                                                                                                   'empresas' => $empresas));

    }

    public function ajaxInteraccionColaborativoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fn = $this->get('funciones');
        
        $empresa_id = $request->request->get('empresa_id');
        $pagina_id = $request->request->get('pagina_id');
        $tema_id = $request->request->get('tema_id');
        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');
        $excel = $request->request->get('excel');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";

        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        $tema = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($tema_id);

        $listado = $rs->interaccionColaborativo($empresa_id, $pagina_id, $tema_id, $desde, $hasta);


        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/interaccionColaborativo.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Interacciones de espacio colaborativo').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'.');
        $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$this->get('translator')->trans('Programa').': '.$pagina->getNombre().'.');
        $objWorksheet->setCellValue('A3', $this->get('translator')->trans('Tema').': '.$tema->getTema() );
        if (!count($listado))
        {
            $objWorksheet->mergeCells('A6:S6');
            $objWorksheet->setCellValue('A6', $this->get('translator')->trans('No existen registros para esta consulta'));
        }
        else {

            $row = 6;
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

                $correo = trim($participante['correo_personal']) ? trim($participante['correo_personal']) : trim($participante['correo_corporativo']);
                $mensaje = strip_tags($participante['mensaje']);
                $fecha = explode(" ",$participante['fecha_mensaje']);
                $objWorksheet->getStyle("A$row:O$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:O$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:O$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:O$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:O$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(40); // Altura de la fila
            

                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                $objWorksheet->setCellValue('B'.$row, $participante['login']);
                $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                $objWorksheet->setCellValue('E'.$row, $participante['fecha_registro']);
                $objWorksheet->setCellValue('F'.$row, $correo);
                $objWorksheet->setCellValue('G'.$row, $participante['pais']);
                $objWorksheet->setCellValue('H'.$row, $participante['nivel']);
                $objWorksheet->setCellValue('I'.$row, $participante['campo1']);
                $objWorksheet->setCellValue('J'.$row, $participante['campo2']);
                $objWorksheet->setCellValue('K'.$row, $participante['campo3']);
                $objWorksheet->setCellValue('L'.$row, $participante['campo4']);
                $objWorksheet->setCellValue('M'.$row, $fecha[0]);
                $objWorksheet->setCellValue('N'.$row, $fecha[1]);
                $objWorksheet->setCellValue('O'.$row, $mensaje);
                $row++;

            }

        }

        // Crea el writer
        $empresaName = $fn->eliminarAcentos($empresa->getNombre());
        $empresaName = strtoupper($empresaName);
        $paginaName = $fn->eliminarAcentos($pagina->getNombre());
        $paginaName = strtoupper($paginaName);
        $hoy = date('y-m-d h i');
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
        $path = 'recursos/reportes/ESPACIO COLABORATIVO '.$paginaName.' '.$empresaName.' '.$hoy.'.xls';
        $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
        $writer->save($xls);

        $archivo = $this->container->getParameter('folders')['uploads'].$path;
        $document_name = 'ESPACIO COLABORATIVO '.$empresaName.' '.$paginaName.' '.$hoy.'.xls';
        $bytes = filesize($xls);
        $document_size = $fn->fileSizeConvert($bytes);
        
        $return = array('archivo' => $archivo,
                        'document_name' => $document_name,
                        'document_size' => $document_size);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));  
    }

    public function interaccionMuroAction($app_id, $empresa_id, $desde, $hasta, Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $hoy = date('Y-m-d h:i:s');
        //return new response($hoy);
        
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

        
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }
        else {
            $empresa_id = $usuario->getEmpresa()->getId();
        }

        $paginas = array();

        if ($empresa_id)
        {

            $str = '';
            $tiene = 0;
            $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                        JOIN pe.pagina p
                                        WHERE p.pagina IS NULL
                                        AND pe.empresa = :empresa_id 
                                        ORDER BY p.nombre ASC")
                        ->setParameter('empresa_id', $empresa_id);
            $pages = $query->getResult();

            foreach ($pages as $page)
            {
                $tiene++;
                $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getPagina()->getId().'" p_str="'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().'">'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre();
                $subPaginas = $this->subPaginasEmpresa($page->getPagina()->getId(), $empresa_id);
                if ($subPaginas['tiene'] > 0)
                {
                    $str .= '<ul>';
                    $str .= $subPaginas['return'];
                    $str .= '</ul>';
                }
                $str .= '</li>';
            }

            $paginas = array('tiene' => $tiene,
                             'str' => $str);

        }

        $desde = $desde ? str_replace('-', '/', $desde) : '';
        $hasta = $hasta ? str_replace('-', '/', $hasta) : '';

        return $this->render('LinkBackendBundle:Reportes:interaccionMuro.html.twig', array('empresas' => $empresas,
                                                                                           'usuario' => $usuario,
                                                                                           'paginas' => $paginas,
                                                                                           'empresa_id' => $empresa_id,
                                                                                           'desde' => $desde,
                                                                                           'hasta' => $hasta));

    }

    public function subPaginasEmpresa($pagina_id, $empresa_id)
	{

        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
		$subpaginas = array();
		$tiene = 0;
		$return = null;
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $hoy = date('Y-m-d 23:59:59');
        //return new response($hoy);
		$query = $em->createQuery("SELECT pe, p FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                    JOIN pe.pagina p 
                                    WHERE pe.empresa = :empresa_id AND p.pagina = :pagina_id 
                                    ORDER BY p.orden ASC")
                    ->setParameters(array('empresa_id' => $empresa_id,
                    					  'pagina_id' => $pagina_id));
        $subpages = $query->getResult();
		
		foreach ($subpages as $subpage)
		{
			$tiene++;
                if($subpage->getPagina()->getCategoria()->getId() == $yml['parameters']['categoria']['leccion'])
                {   
                    $desde = $subpage->getPagina()->getFechaCreacion();
                    $desde = $desde->format('Y-m-d h:i:s');
                    
                    $listado = $rs->interaccionMuro($subpage->getEmpresa()->getId(), $subpage->getPagina()->getId(),$desde , $hoy);
                    
                    $cantidad = count($listado);
                    $return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getPagina()->getId().'" p_str="'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().'" tipo_recurso_id="'. $subpage->getPagina()->getCategoria()->getId() .'" >'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().' ('.$cantidad.')';
                }else{
                    $return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getPagina()->getId().'" p_str="'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().'" tipo_recurso_id="'. $subpage->getPagina()->getCategoria()->getId() .' " >'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre();
                }
				$subPaginas = $this->subPaginasEmpresa($subpage->getPagina()->getId(), $subpage->getEmpresa()->getId());
				if ($subPaginas['tiene'] > 0)
				{
					$return .= '<ul>';
					$return .= $subPaginas['return'];
					$return .= '</ul>';
				}
				$return .= '</li>';
			
		}

		$subpaginas = array('tiene' => $tiene,
							'return' => $return);

		return $subpaginas;

	}

    public function ajaxInteraccionMuroAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fn = $this->get('funciones');
        
        $empresa_id = $request->request->get('empresa_id');
        $pagina_id = $request->request->get('pagina_id');
        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');
        $excel = $request->request->get('excel');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";

        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $leccion = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        $materia = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($leccion->getPagina());
        $modulo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($materia->getPagina());
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($modulo->getPagina());
        
        $listado = $rs->interaccionMuro($empresa_id, $pagina_id, $desde, $hasta);


        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/interaccionMuro.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Interacciones de muro').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'.');
        $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$this->get('translator')->trans('Programa').': '.$programa->getNombre() .'.');
        $objWorksheet->setCellValue('A3', $this->get('translator')->trans('Módulo').': '.$modulo->getNombre().'. '.$this->get('translator')->trans('Materia').': '.$materia->getNombre() .'.');
        $objWorksheet->setCellValue('A4', $this->get('translator')->trans('Lección').': '.$leccion->getNombre().'.');
        if (!count($listado))
        {
            $objWorksheet->mergeCells('A8:O8');
            $objWorksheet->setCellValue('A8', $this->get('translator')->trans('No existen registros para esta consulta'));
        }
        else {

            $row = 8;
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
               $correo = trim($participante['correo_personal']) ? trim($participante['correo_personal']) : trim($participante['correo_corporativo']);
               $fecha = explode(" ",$participante['fecha_mensaje']);

                
                $objWorksheet->getStyle("A$row:O$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:O$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:O$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:O$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:O$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(40); // Altura de la fila
            

                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                $objWorksheet->setCellValue('B'.$row, $participante['login']);
                $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                $objWorksheet->setCellValue('E'.$row, $participante['fecha_registro']);
                $objWorksheet->setCellValue('F'.$row, $correo);
                $objWorksheet->setCellValue('G'.$row, $participante['pais']);
                $objWorksheet->setCellValue('H'.$row, $participante['nivel']);
                $objWorksheet->setCellValue('I'.$row, $participante['campo1']);
                $objWorksheet->setCellValue('J'.$row, $participante['campo2']);
                $objWorksheet->setCellValue('K'.$row, $participante['campo3']);
                $objWorksheet->setCellValue('L'.$row, $participante['campo4']);
                $objWorksheet->setCellValue('M'.$row, $fecha[0]);
                $objWorksheet->setCellValue('N'.$row, $fecha[1]);
                $objWorksheet->setCellValue('o'.$row, $participante['mensaje']);
                $row++;

            }

        }

        // Crea el writer
        $empresaName = $fn->eliminarAcentos($empresa->getNombre());
        $empresaName = strtoupper($empresaName);
        $paginaName = $fn->eliminarAcentos($programa->getNombre());
        $paginaName = strtoupper($paginaName);
        $hoy = date('y-m-d h i');
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
        $path = 'recursos/reportes/MURO '.$paginaName.' '.$empresaName.' '.$hoy.'.xls';
        $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
        $writer->save($xls);

        $archivo = $this->container->getParameter('folders')['uploads'].$path;
        $document_name = 'MURO '.$paginaName.' '.$empresaName.' '.$hoy.'.xls';
        $bytes = filesize($xls);
        $document_size = $fn->fileSizeConvert($bytes);
        
        $return = array('archivo' => $archivo,
                        'document_name' => $document_name,
                        'document_size' => $document_size);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));    
    }

    public function reporteGeneralAction($app_id, $empresa_id, Request $request)
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
        $usuarios_activos=0;
        $usuarios_inactivos=0;
        $usuarios_registrados=0;
        $i= 5;
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $query = $em->getConnection()->prepare('SELECT
                                                fnreporte_general2(:re, :pempresa_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re) {
            if ($re['logueado'] > 0) {
                $usuarios_activos++;
            }else{
                $usuarios_inactivos++;
            }
           $usuarios_registrados = $usuarios_activos + $usuarios_inactivos;
        }

        $query2 = $em->getConnection()->prepare('SELECT
                                                fnreporte_general(:re, :pempresa_id) as
                                                resultado; fetch all from re;');
        $re1 = 're';
        $query2->bindValue(':re', $re1, \PDO::PARAM_STR);
        $query2->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query2->execute();
        $r1 = $query2->fetchAll();


         // Solicita el servicio de excel
        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/reporteGeneral.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Cantidad de participantes por programa').'. ');
        $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. ');
        
        if (!count($r))
        {
            $objWorksheet->mergeCells('A5:S5');
            $objWorksheet->setCellValue('A5', $this->get('translator')->trans('No existen registros para esta consulta'));
        }
        else {

            $row = 9;
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

            foreach ($r1 as $re)
            {
                $objWorksheet->getStyle("A$row:H$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A8:H8")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:H$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:H$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:H$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:H$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(30); // Altura de la fila
                $objWorksheet->getStyle("A6:C6")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A5:C5")->applyFromArray($styleThinBlackBorderOutline); //bordes

                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A6', $usuarios_registrados);
                $objWorksheet->setCellValue('B6', $usuarios_activos);
                $objWorksheet->setCellValue('C6', $usuarios_inactivos);
                $objWorksheet->setCellValue('A'.$row, $re['nombre']);
                $objWorksheet->setCellValue('B'.$row, $re['fecha_inicio']);
                $objWorksheet->setCellValue('C'.$row, $re['fecha_vencimiento']);
                $objWorksheet->setCellValue('D'.$row, $re['no_iniciados']);
                $objWorksheet->setCellValue('E'.$row, $re['cursando']);
                $objWorksheet->setCellValue('F'.$row, $re['culminado']);
                $objWorksheet->setCellValue('G'.$row, $re['activos']);
                $objWorksheet->setCellValue('H'.$row, $re['registrados']);
                $row++;

            }

        }

        // Crea el writer
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
        $empresaName = $f->eliminarAcentos($empresa->getNombre());
        $empresaName = strtoupper($empresaName);
        $hoy = date('d-m-Y');
        
        // Envia la respuesta del controlador
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // Agrega los headers requeridos
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'RESUMEN PROGRAMAS '.$empresaName.'.xlsx'
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;


    }

    public function ajaxFiltroProgramasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        
        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                    JOIN pe.pagina p 
                                    WHERE pe.empresa = :empresa_id
                                    AND p.pagina IS NULL 
                                    ORDER BY p.nombre ASC')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getCategoria()->getNombre().': ' .$pagina->getPagina()->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxFiltroTemasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        
        $query = $em->createQuery('SELECT f FROM LinkComunBundle:CertiForo f  
                                    WHERE f.empresa = :empresa_id
                                    AND f.foro IS NULL 
                                    AND f.pagina = :pagina_id
                                    ORDER BY f.tema ASC')
                    ->setParameters(array('empresa_id' => $empresa_id,
                                          'pagina_id' => $pagina_id));
        $temas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($temas as $tema)
        {
            $options .= '<option value="'.$tema->getId().'">'.$tema->getTema().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
    
}