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

class ReportesJTController extends Controller
{
    public function conexionesUsuarioAction($app_id, Request $request)
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
        $em = $this->getDoctrine()->getManager();

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }

        // Lógica inicial de la pantalla de este reporte
        //$datos = 'Foo';

        return $this->render('LinkBackendBundle:Reportes:conexionesUsuario.html.twig', array(
                                                                                                'usuario' => $usuario,
                                                                                                'empresas' => $empresas));

    }

    public function avanceProgramasAction($app_id, Request $request)
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

        return $this->render('LinkBackendBundle:Reportes:avanceProgramas.html.twig', array(
                                                                                            'usuario' => $usuario,
                                                                                            'empresas' => $empresas));

    }

    public function ajaxAvanceProgramasAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        
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
        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $listado = $rs->avanceProgramas($empresa_id, $pagina_id, $desde, $hasta);
        
        if($excel==1) 
        {
           $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/avanceProgramas.xlsx';
           $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
           $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
           $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Evaluaciones por módulo').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'.');
                $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$this->get('translator')->trans('Programa').': '.$pagina->getNombre().'.');
            
            if (!count($listado))
            {
                $objWorksheet->mergeCells('A5:S5');
                $objWorksheet->setCellValue('A5', $this->get('translator')->trans('No existen registros para esta consulta'));
            }
            else
            {
                $row = 5;
                $last_row=($row+count($listado))-1;
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

                 // Estilizar las celdas antes de insertar los datos
                for ($f=$row; $f<=$last_row; $f++)
                {
                        $objWorksheet->getStyle("A$f:S$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:S$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:S$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:S$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:S$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                        $objWorksheet->getStyle("A$f:S$f")->getAlignment()->setWrapText(true);//ajustar texto a la columna
                        $objWorksheet->getRowDimension($f)->setRowHeight(35); // Altura de la fila
                }
                
                foreach ($listado as $participante)
                {


                    // Datos de las columnas del reporte
                    $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $participante['login']);
                    $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $participante['fecha_registro']);
                    $objWorksheet->setCellValue('F'.$row, $participante['correo_corporativo']);
                    $objWorksheet->setCellValue('G'.$row, $participante['pais']);
                    $objWorksheet->setCellValue('H'.$row, $participante['nivel']);
                    $objWorksheet->setCellValue('I'.$row, $participante['campo1']);
                    $objWorksheet->setCellValue('J'.$row, $participante['campo2']);
                    $objWorksheet->setCellValue('K'.$row, $participante['campo3']);
                    $objWorksheet->setCellValue('L'.$row, $participante['campo4']);
                    $objWorksheet->setCellValue('M'.$row, $participante['modulos']);
                    $objWorksheet->setCellValue('N'.$row, $participante['materias']);
                    $objWorksheet->setCellValue('O'.$row, $participante['promedio']);
                    $objWorksheet->setCellValue('P'.$row, $participante['fecha_inicio_programa']);
                    $objWorksheet->setCellValue('Q'.$row, $participante['hora_inicio_programa']);
                    $objWorksheet->setCellValue('R'.$row, $participante['fecha_fin_programa']);
                    $objWorksheet->setCellValue('S'.$row, $participante['hora_fin_programa']);

                  
                    $row++;
                }
            }
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
            $path = 'recursos/reportes/avanceProgramas'.$session->get('sesion_id').'.xls';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;
            $html = '';

           
        }
        else
        {

        $archivo = '';
        $html = $this->renderView('LinkBackendBundle:Reportes:avanceProgramasTabla.html.twig', 
                                array('listado' => $listado,
                                      'empresa' => $empresa->getNombre(),
                                      'programa' => $pagina->getNombre()
                                      ));
        }


                                                                                              

        $return = array('archivo' => $archivo,
                        'html' => $html);


        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));


    }


    public function ajaxConexionesUsuarioAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        
        $empresa_id = $request->request->get('empresa_id');
        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');
        $excel = $request->request->get('excel');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";


        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
       

        $listado = $rs->conexionesUsuario($empresa_id,$desde,$hasta);//

      
        if($excel==1) 
        {
           $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/conexionesUsuarios.xlsx';
           $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
           $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
           $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Conexiones por usuario').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'.');
            $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre());
            
            if (!count($listado))
            {
                $objWorksheet->mergeCells('A5:S5');
                $objWorksheet->setCellValue('A5', $this->get('translator')->trans('No existen registros para esta consulta'));
            }
            else
            {
                $row = 5;
                $last_row=($row+count($listado))-1;
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

                 // Estilizar las celdas antes de insertar los datos
                for ($f=$row; $f<=$last_row; $f++)
                {
                        $objWorksheet->getStyle("A$f:N$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:N$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:N$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:N$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:N$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                        $objWorksheet->getStyle("A$f:N$f")->getAlignment()->setWrapText(true);//ajustar texto a la columna
                        $objWorksheet->getRowDimension($f)->setRowHeight(35); // Altura de la fila
                }
                
                foreach ($listado as $participante)
                {


                    // Datos de las columnas del reporte
                    $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $participante['login']);
                    $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $participante['fecha_registro']);
                    $objWorksheet->setCellValue('F'.$row, $participante['correo_corporativo']);
                    $objWorksheet->setCellValue('G'.$row, $participante['pais']);
                    $objWorksheet->setCellValue('H'.$row, $participante['nivel']);
                    $objWorksheet->setCellValue('I'.$row, $participante['campo1']);
                    $objWorksheet->setCellValue('J'.$row, $participante['campo2']);
                    $objWorksheet->setCellValue('K'.$row, $participante['campo3']);
                    $objWorksheet->setCellValue('L'.$row, $participante['campo4']);
                    $objWorksheet->setCellValue('M'.$row, $participante['promedio']);
                    $objWorksheet->setCellValue('N'.$row, $participante['visitas']);

                  
                    $row++;
                }
            }
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
            $path = 'recursos/reportes/conexionesUsuario'.$session->get('sesion_id').'.xls';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;
            $html = '';

           
        }
        else
        {

        $archivo = '';
        $html = $this->renderView('LinkBackendBundle:Reportes:conexionesUsuarioTable.html.twig', 
                                array('listado' => $listado,
                                      'empresa' => $empresa->getNombre()
                                      ));
        }


                                                                                              

        $return = array('archivo' => $archivo,
                        'html' => $html);


        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));


    }

   

}