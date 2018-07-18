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

        // Acumuladores
        $mayor = 0;
        $celda_mayor = array();
        $total = 0;

        // ESTRUCTURA de $conexiones:
        // $conexiones[0][0] => Día/Hora
        // $conexiones[0][1] => Etiqueta 00:00
        // $conexiones[0][2] => Etiqueta 01:00
        // ...
        // $conexiones[0][24] => Etiqueta 23:00
        // $conexiones[0][25] => Etiqueta Total
        // $conexiones[1][0] => Etiqueta Domingo
        // $conexiones[1][1] => Domingo a las 00:00
        // $conexiones[1][2] => Domingo a las 01:00
        // ...
        // $conexiones[1][24] => Domingo a las 23:00
        // $conexiones[1][25] => Total Domingo
        // $conexiones[2][0] => Etiqueta Lunes
        // $conexiones[2][1] => Lunes a las 00:00
        // $conexiones[2][2] => Lunes a las 01:00
        // ...
        // $conexiones[2][24] => Lunes a las 23:00
        // $conexiones[2][25] => Total Lunes
        // ...
        // $conexiones[7][0] => Etiqueta Sábado
        // $conexiones[7][1] => Sábado a las 00:00
        // $conexiones[7][2] => Sábado a las 01:00
        // ...
        // $conexiones[7][24] => Sábado a las 23:00
        // $conexiones[7][25] => Total Sábado
        // $conexiones[8][0] => Etiqueta Total
        // $conexiones[8][1] => Total a las 00:00
        // $conexiones[8][2] => Total a las 01:00
        // ...
        // $conexiones[8][24] => Total a las 23:00
        // $conexiones[8][25] => Total de totales
        $conexiones[0][0] = $this->get('translator')->trans('Día/Hora');

        // Etiquetas de horas
        $c = 1;
        for ($h=0; $h<24; $h++)
        {
            $hora = $h<=9 ? '0'.$h : $h;
            $conexiones[0][$c] = $hora.':00';
            $c++;
        }

        $conexiones[0][25] = 'Total';

        // Columna de Total con valor cero
        for ($f=1; $f<=8; $f++)
        {
            $conexiones[$f][25] = 0;
        }

        for ($c=0; $c<=24; $c++)
        {
            if ($c==0)
            {
                for ($f=1; $f<=8; $f++)
                {
                    switch ($f)
                    {
                        case 1:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Domingo');
                            break;
                        case 2:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Lunes');
                            break;
                        case 3:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Martes');
                            break;
                        case 4:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Miércoles');
                            break;
                        case 5:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Jueves');
                            break;
                        case 6:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Viernes');
                            break;
                        case 7:
                            $conexiones[$f][$c] = $this->get('translator')->trans('Sábado');
                            break;
                        case 8:
                            $conexiones[$f][$c] = 'Total';
                            break;
                    }
                }
            }
            else {

                $h = $c-1;
                $hora1 = $h<=9 ? '0'.$h.':00:00' : $h.':00:00';
                $hora2 = $h<=9 ? '0'.$h.':59:59' : $h.':59:59';

                // Cálculos desde la función de BD
                $query = $em->getConnection()->prepare('SELECT
                                                        fnhoras_conexion(:pempresa_id, :pdesde, :phasta, :phora1, :phora2) as
                                                        resultado;');
                $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                $query->bindValue(':pdesde', $desde, \PDO::PARAM_STR);
                $query->bindValue(':phasta', $hasta, \PDO::PARAM_STR);
                $query->bindValue(':phora1', $hora1, \PDO::PARAM_STR);
                $query->bindValue(':phora2', $hora2, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();

                // La respuesta viene formada por las cantidades de registros por día de semana separado por __
                $r_arr = explode("__", $r[0]['resultado']);
                $f = 0;
                $total_hora = 0;
                
                foreach ($r_arr as $r)
                {

                    $f++;
                    $conexiones[$f][$c] = $r;
                    $total_hora += $r;
                    $total += $r;
                    $conexiones[$f][25] = $conexiones[$f][25] + $r;

                    if ($r >= $mayor)
                    {
                        if ($r == $mayor && $mayor > 0)
                        {
                            // Varias celdas mayor
                            $celda_mayor[] = $f.'_'.$c;
                        }
                        else {
                            // Nueva celda mayor
                            $celda_mayor = array($f.'_'.$c);
                        }
                        $mayor = $r;
                    }

                }
                $conexiones[8][$c] = $total_hora;

            }
        }

        // Total de totales
        $conexiones[8][25] = $total;

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
                        'archivo' => $archivo);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    
}