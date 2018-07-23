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
use Spipu\Html2Pdf\Html2Pdf;

class ReportesJEController extends Controller
{
    public function horasConexionAction($app_id, Request $request)
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

        return $this->render('LinkBackendBundle:Reportes:horasConexion.html.twig', array('usuario' => $usuario,
                                                                                         'empresas' => $empresas));

    }

    public function ajaxHorasConexionAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $fn = $this->get('funciones');
        
        $empresa_id = $request->request->get('empresa_id');
        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');
        $excel = $request->request->get('excel');
        $pdf = $request->request->get('pdf');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";

        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";

        $reporte = $fn->horasConexion($empresa_id, $desde, $hasta);
        $conexiones = $reporte['conexiones'];
        $celda_mayor = $reporte['celda_mayor'];

        if ($excel)
        {

            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/horasConexion.xlsx';
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Horas de conexión de la empresa').' '.$empresa->getNombre().' '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'.');

            // Primera columna
            for ($f=0; $f<=8; $f++)
            {
                $r = $f+3;
                $objWorksheet->setCellValue('A'.$r, $conexiones[$f][0]);
            }

            // Data calculada
            for ($f=1; $f<=8; $f++)
            {
                $row = $f+3;
                for ($c=1; $c<=25; $c++)
                {
                    $col = $columnNames[$c];
                    $objWorksheet->setCellValue($col.$row, $conexiones[$f][$c]);
                }
            }

            // Resaltar las celdas mayores
            foreach ($celda_mayor as $cm)
            {
                $m = explode("_", $cm);
                $col = $columnNames[$m[1]];
                $row = $m[0]+3;
                $objPHPExcel->getActiveSheet()->getStyle($col.$row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('8FC9F0');
            }

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
            $path = 'recursos/reportes/horasConexion'.$session->get('sesion_id').'.xls';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;

        }
        else {
            $archivo = '';
        }
        
        $return = array('conexiones' => $conexiones,
                        'celda_mayor' => $celda_mayor,
                        'archivo' => $archivo,
                        'desdef' => $desde,
                        'hastaf' => $hasta);

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
        
        $f = $this->get('funciones');
        $session = new Session();
        
        $reporte = $f->horasConexion($empresa_id, $desde, $hasta);
        $conexiones = $reporte['conexiones'];
        $celda_mayor = $reporte['celda_mayor'];

        $filas_mayor = array();
        $columnas_mayor = array();

        $desde_arr = explode(" ", $desde);
        list($a, $m, $d) = explode("-", $desde_arr[0]);
        $desde = "$d/$m/$a";

        $hasta_arr = explode(" ", $hasta);
        list($a, $m, $d) = explode("-", $hasta_arr[0]);
        $hasta = "$d/$m/$a";

        foreach ($celda_mayor as $cm)
        {
            $cm_arr = explode("_", $cm);
            $filas_mayor[] = $cm_arr[0];
            $columnas_mayor[] = $cm_arr[1];
        }

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $tabla = $this->renderView('LinkBackendBundle:Reportes:horasConexionTabla.html.twig', array('conexiones' => $conexiones,
                                                                                                    'filas_mayor' => $filas_mayor,
                                                                                                    'columnas_mayor' => $columnas_mayor,
                                                                                                    'empresa' => $empresa,
                                                                                                    'desde' => $desde,
                                                                                                    'hasta' => $hasta));

        $path = 'recursos/reportes/horasConexion'.$session->get('sesion_id').'.png';
        $src = $this->container->getParameter('folders')['dir_uploads'].$path;

        $grafica = $this->renderView('LinkBackendBundle:Reportes:horasConexionGrafica.html.twig', array('src' => $src));

        $logo = $this->container->getParameter('folders')['dir_project'].'web/img/logo_formacion.png';
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

        //Generamos el PDF
        $pdf->output('horas_conexion.pdf');

    }

    
}

// Manos de piedra: 34:57, 37:45