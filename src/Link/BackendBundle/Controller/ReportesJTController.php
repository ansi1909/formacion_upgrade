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
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\AdminZonaHoraria;
use Link\ComunBundle\Entity\AdminPais;
use Spipu\Html2Pdf\Html2Pdf;

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

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }

        // Lógica inicial de la pantalla de este reporte
        //return new response(var_dump($usuario));

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
    public function ajaxUrlpaginaEmpresaAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $timeZone = $yml['parameters']['time_zone']['local'];
        $fun = $this->get('funciones');
        $empresa_id = $request->request->get('empresa_id');
        $pagina_id = $request->request->get('pagina_id');
        $entidad = 'LinkComunBundle:'.(string)$request->request->get('entidad');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        if ($entidad == 'LinkComunBundle:CertiPaginaEmpresa') {
            $entidad_fecha = $this->getDoctrine()->getRepository($entidad)->findOneBy(array('empresa'=>$empresa_id,'pagina'=>$pagina_id));
            $entidad_fecha = $entidad_fecha->getFechaInicio();
            //print_r($entidad_fecha);exit();
            //$entidad_fecha ->setTimeZone(new \DateTimeZone($timeZone));

            $entidad_fecha = $entidad_fecha->format('d/m/Y');

        }else if($entidad =='LinkComunBundle:AdminSesion'){
            $query = $em->createQuery("SELECT MIN(ase.fechaIngreso) as ingreso FROM LinkComunBundle:AdminUsuario au
                                        INNER JOIN LinkComunBundle:AdminEmpresa ae WITH ae.id = au.empresa
                                        INNER JOIN LinkComunBundle:AdminSesion ase WITH ase.usuario = au.id
                                        WHERE ae.id = :empresa_id ")
                    ->setParameter('empresa_id', $empresa_id);

        $entidad_fecha = $query->getResult();
        $entidad_fecha = $entidad_fecha[0]['ingreso'];
        if ($entidad_fecha) {
            $entidad_fecha = new \DateTime($entidad_fecha);
            $entidad_fecha ->setTimeZone(new \DateTimeZone($timeZone));
            $entidad_fecha = $entidad_fecha->format('d/m/Y');
        }

        }

        $timeZone = $yml['parameters']['time_zone']['local'];
        $fecha_actual = new \DateTime('now');
        $fecha_actual->setTimeZone(new \DateTimeZone($timeZone));
        $return = array('fecha_inicio'=>$entidad_fecha,'fecha_fin'=> $fecha_actual->format('d/m/Y'));

        $return =  json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxAvanceProgramasAction(Request $request)
    {

        $estatusProragama = ['No Iniciado','En curso','En evaluación','Aprobado'];
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fun = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $empresa_id = $request->request->get('empresa_id');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneReport = $fun->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml);
        $pagina_id = $request->request->get('pagina_id');
        $desdef = $request->request->get('desde');
        $hastaf = $request->request->get('hasta');
        $excel = $request->request->get('excel');
        $filtro = $request->request->get('check_filtro');

        list($d, $m, $a) = explode("/", $desdef);
        $desde = "$a-$m-$d 00:00:00";
        $desdeUtc = $fun->converDate($desde,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $desde = $desdeUtc->fecha.' '.$desdeUtc->hora;

        list($d, $m, $a) = explode("/", $hastaf);
        $hasta = "$a-$m-$d 23:59:59";
        $hastaUtc = $fun->converDate($hasta,$timeZoneEmpresa,$yml['parameters']['time_zone']['default'],false);
        $hasta = $hastaUtc->fecha.' '.$hastaUtc->hora;



        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        $isPrograma = (in_array($pagina->getCategoria()->getId(),array($yml['parameters']['categoria']['programa'],$yml['parameters']['categoria']['curso'])))? 1:0;
        $auxTitulo  = ($isPrograma)? 'Avance en programas':'Avance en competencias';
        $listado = $rs->avanceProgramas($empresa_id, $pagina_id, $desde, $hasta);
        if ($excel==1)
        {

           $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
           $fileWithPath = ($isPrograma)? $this->container->getParameter('folders')['dir_project'].'docs/formatos/avanceProgramas.xlsx':$this->container->getParameter('folders')['dir_project'].'docs/formatos/avanceCompetencias.xlsx';
           $spreadsheet = $readerXlsx->load($fileWithPath);
           $objWorksheet = $spreadsheet->setActiveSheetIndex(0);
           $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans($auxTitulo).'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'. '.$this->get('translator')->trans('Huso horario').': '.$timeZoneReport);
                $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$this->get('translator')->trans($pagina->getCategoria()->getNombre()).': '.$pagina->getNombre().'.');

            if (!count($listado))
            {
                $objWorksheet->mergeCells('A5:S5');
                $objWorksheet->setCellValue('A5', $this->get('translator')->trans('No existen registros para esta consulta'));
            }
            else {
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
                        $objWorksheet->getStyle("A$f:X$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:X$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:X$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:X$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:X$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                        $objWorksheet->getStyle("A$f:X$f")->getAlignment()->setWrapText(true);//ajustar texto a la columna
                        $objWorksheet->getRowDimension($f)->setRowHeight(35); // Altura de la fila
                }

                //return new response(var_dump($listado[2]));

                foreach ($listado as $participante)
                {

                    if ($participante['status'])
                    {
                        $status = $participante['status'];
                    }
                    else {
                        if (trim($participante['fecha_inicio_programa']))
                        {
                            $status = 1;
                        }
                        else {
                            $status = 0;
                        }
                    }

                    $promedio = $participante['promedio'] ? $participante['promedio'] : 0;
                    $acceso = $participante['activo']? 'Sí' : 'No';
                   // $fecha = explode(" ", $participante['fecha_registro']);
                    $fecha_registro = $fun->converDate($participante['fecha_registro'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                    $fecha_inicio = $fun->converDate($participante['fecha_inicio_programa'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                    $fecha_fin = $fun->converDate($participante['fecha_fin_programa'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                    if($status == 3){
                        $totalTime = $fun->AvancetotalTime($participante['fecha_inicio_programa'],$participante['fecha_fin_programa'],$participante['id']);
                        //return new response(var_dump($totalTime)); 
                    }

                    // Datos de las columnas del reporte
                    $correo = trim($participante['correo_corporativo']) != '' ? $participante['correo_corporativo'] : $participante['correo_personal'];
                    $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $participante['login']);
                    $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $fecha_registro->fecha);
                    $objWorksheet->setCellValue('F'.$row, $fecha_registro->hora);
                    $objWorksheet->setCellValue('G'.$row, $acceso);
                    $objWorksheet->setCellValue('H'.$row, ($participante['logueado']>0)? $this->get('translator')->trans('SI'):$this->get('translator')->trans('NO'));
                    $objWorksheet->setCellValue('I'.$row, $correo);
                    $objWorksheet->setCellValue('J'.$row, $participante['pais']);
                    $objWorksheet->setCellValue('K'.$row, $participante['nivel']);
                    $objWorksheet->setCellValue('L'.$row, $participante['campo1']);
                    $objWorksheet->setCellValue('M'.$row, $participante['campo2']);
                    $objWorksheet->setCellValue('N'.$row, $participante['campo3']);
                    $objWorksheet->setCellValue('O'.$row, $participante['campo4']);
                    $objWorksheet->setCellValue('P'.$row, trim($participante['modulos']));
                    //Columnas que varian segun la categoria 
                    $objWorksheet->setCellValue('Q'.$row,($isPrograma)?  trim($participante['materias']):trim($promedio));
                    $objWorksheet->setCellValue('R'.$row, ($isPrograma)? trim($promedio):trim($estatusProragama[$status]));
                    $objWorksheet->setCellValue('S'.$row, ($isPrograma)? trim($estatusProragama[$status]):($status != 0)? $fecha_inicio->fecha:'');
                    $objWorksheet->setCellValue('T'.$row, ($isPrograma)? ($status != 0)? $fecha_inicio->fecha:'' : ($status!= 0)? $fecha_inicio->hora:'');
                    $objWorksheet->setCellValue('U'.$row, ($isPrograma)? ($status!= 0)? $fecha_inicio->hora:'' : ($status == 3)? $fecha_fin->fecha:'');
                    $objWorksheet->setCellValue('V'.$row, ($isPrograma)? ($status == 3)? $fecha_fin->fecha:'' : ($status == 3)? $fecha_fin->hora:'');
                    $objWorksheet->setCellValue('W'.$row, ($isPrograma)? ($status == 3)? $fecha_fin->hora:'' : ($status == 3)? $totalTime:'');
                   if ($isPrograma){
                        $objWorksheet->setCellValue('X'.$row, ($status == 3)? $totalTime:'');
                   }
                    $row++;

                }
            }

            $writer = $this->get('phpoffice.spreadsheet')->createWriter($spreadsheet, 'Xlsx');
            $empresaName = $fun->eliminarAcentos($empresa->getNombre());
            $empresaName = strtoupper($empresaName);
            $hoy = date('y-m-d h i');
            $paginaName =  $fun->eliminarAcentos($pagina->getNombre());
            $paginaName = strtoupper($paginaName);
            $path = 'recursos/reportes/AVANCE '.$paginaName.' '.$empresaName.' '.$hoy.'.xlsx';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;
            $html = '';


        }
        else {

            $archivo = '';
            $auxModulos  = ($isPrograma)? $this->get('translator')->trans('Módulos vistos'):$this->get('translator')->trans('Recursos vistos');
            $auxPromedio = ($isPrograma)? $this->get('translator')->trans('Promedio evaluación módulo'):$this->get('translator')->trans('Promedio evaluación recursos');
            $auxEstatus  = ($isPrograma)? $this->get('translator')->trans('Estatus del programa'): $this->get('translator')->trans('Estatus de la competencia');
            $auxVisibility = (!$isPrograma)?'style="display:none"':'';
            $html = '<table class="table" id="dt">
                <thead class="sty__title">
                    <tr>
                        <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Usuario').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                        <th class="hd__title">'.$auxModulos.'</th>
                        <th class="hd__title" '.$auxVisibility.'>'.$this->get('translator')->trans('Materias vistas').'</th>
                        <th class="hd__title">'.$auxPromedio.'</th>
                        <th class="hd__title">'.$auxEstatus.'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Fecha inicio').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Fecha fin').'</th>
                    </tr>
                </thead>
                <tbody style="font-size: .7rem;">';

            foreach ($listado as $registro)
            {
                $correo = trim($registro['correo_corporativo']) != '' ? $registro['correo_corporativo'] : $registro['correo_personal'];
                if ($registro['status'])
                {
                    $status = $registro['status'];
                }
                else {
                    if (trim($registro['fecha_inicio_programa']))
                    {
                        $status = 1;
                    }
                    else {
                        $status = 0;
                    }
                }
                //$status = $registro['status'] ? $registro['status'] : 0;
                //$status = $registro['status'] ? $registro['status'] : $registro['fecha_inicio_programa'] ? 1 : 0;
                $fecha_inicio = $fun->converDate($registro['fecha_inicio_programa'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $fecha_fin = $fun->converDate($registro['fecha_fin_programa'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $promedio=($registro['promedio'])? $registro['promedio']:0;
                $fecha_in = ($status!=0)? $fecha_inicio->fecha.' '.$fecha_inicio->hora:'';
                $fecha_fin = ($status==3)? $fecha_fin->fecha.' '.$fecha_fin->hora:'';
                $html .= '<tr>
                            <td><a class="detail" data-toggle="modal" data-target="#detailModal" data="'.$registro['login'].'" empresa_id="'.$empresa_id.'" href="#">'.$registro['nombre'].' '.$registro['apellido'].'</a></td>
                            <td>'.$registro['login'].'</td>
                            <td>'.$registro['nivel'].'</td>
                            <td>'.$correo.'</td>
                            <td>'.$registro['modulos'].'</td>
                            <td '.$auxVisibility.'>'.$registro['materias'].'</td>
                            <td>'.$promedio.'</td>
                            <td>'.$this->get('translator')->trans($estatusProragama[$status]).'</td>
                            <td>'.$fecha_in.'</td>
                            <td>'.$fecha_fin.'</td>
                        </tr>';
            }

            $html .= '</tbody>
                    </table>';
            $archivo = '';
        }

        $return = array('archivo' => $archivo,
                        'html' => $html);


        $return =  json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));


    }

    public function ajaxConexionesUsuarioAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fun = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->request->get('empresa_id');

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneReport = $fun->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml);
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

        $listado = $rs->conexionesUsuario($empresa_id,$desde,$hasta);//


        if($excel==1)
        {

            $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/conexionesUsuarios.xlsx';
            $spreadsheet = $readerXlsx->load($fileWithPath);
            $objWorksheet = $spreadsheet->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Conexiones por usuario').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'. '.$this->get('translator')->trans('Huso horario').' : '.$timeZoneReport);
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

                        $objWorksheet->getStyle("A$f:P$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:P$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:P$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:P$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:P$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                        $objWorksheet->getStyle("A$f:P$f")->getAlignment()->setWrapText(true);//ajustar texto a la columna
                        $objWorksheet->getRowDimension($f)->setRowHeight(35); // Altura de la fila
                }

                foreach ($listado as $participante)
                {
                    $fecha = $fun->converDate($participante['fecha_registro'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                    if ($participante['correo_corporativo']!='') {
                        $correo = $participante['correo_corporativo'];
                    }else{
                        if ($participante['correo_personal']!='') {
                            $correo = $participante['correo_personal'];
                        }
                        else{
                            $correo = '';
                        }
                    }
                    if($participante['promedio'] == '00:00:00'){
                        $promedio = '00:01:00';
                    }else{
                        $promedio = $participante['promedio'];
                    }
                    $acceso = $participante['activo']? 'Sí' : 'No';
                    // Datos de las columnas del reporte
                    $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $participante['login']);
                    $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $fecha->fecha);
                    $objWorksheet->setCellValue('F'.$row, $fecha->hora);
                    $objWorksheet->setCellValue('G'.$row, $correo);
                    $objWorksheet->setCellValue('H'.$row, $acceso);
                    $objWorksheet->setCellValue('I'.$row, $participante['pais']);
                    $objWorksheet->setCellValue('J'.$row, $participante['nivel']);
                    $objWorksheet->setCellValue('K'.$row, $participante['campo1']);
                    $objWorksheet->setCellValue('L'.$row, $participante['campo2']);
                    $objWorksheet->setCellValue('M'.$row, $participante['campo3']);
                    $objWorksheet->setCellValue('N'.$row, $participante['campo4']);
                    $objWorksheet->setCellValue('O'.$row, $promedio);
                    $objWorksheet->setCellValue('P'.$row, $participante['visitas']);


                    $row++;
                }
            }

            $empresaName = $fun->eliminarAcentos($empresa->getNombre());
            $empresaName = strtoupper($empresaName);
            $hoy = date('y-m-d h i');
            $writer = $this->get('phpoffice.spreadsheet')->createWriter($spreadsheet, 'Xlsx');
            $path = 'recursos/reportes/CONEXIONES POR USUARIO '.$empresaName.' '.$hoy.'.xlsx';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;
            $html = '';


        }
        else
        {



                  $html = '<table class="table" id="dt">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Usuario').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de registro').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Cantidad de conexiones').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Tiempo de conexion acumulado').'</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: .7rem;">';

        foreach ($listado as $registro)
        {
            if ($registro['correo_corporativo']!='') {
                $correo = $registro['correo_corporativo'];
            }else{
                if ($registro['correo_personal']!='') {
                    $correo = $registro['correo_personal'];
                }
                else{
                    $correo = '';
                }
            }
            if($registro['promedio'] == '00:00:00'){
                $promedio = '00:01:00';
            }else{
                $promedio = $registro['promedio'];
            }
            $html .= '<tr>

                        <td><a class="detail" data-toggle="modal" data-target="#detailModal" data="'.$registro['login'].'" empresa_id="'.$empresa_id.'" href="#">'.$registro['nombre'].' '.$registro['apellido'].'</a></td>
                        <td>'.$registro['login'].'</td>
                        <td>'.$registro['nivel'].'</td>
                        <td>'.$correo.'</td>
                        <td>'.$registro['fecha_registro'].'</td>
                        <td>'.$registro['visitas'].'</td>
                        <td>'.$promedio.'</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';
        $archivo = '';
        }




        $return = array('archivo' => $archivo,
                        'html' => $html);


        $return = json_encode($return);

        return new Response($return, 200, array('Content-Type' => 'application/json'));


    }

    public function detalleParticipanteAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fun = $this->get('funciones');

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();
        if (!$usuario->getEmpresa())
        {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
        }

        return $this->render('LinkBackendBundle:Reportes:detalleParticipante.html.twig', array('usuario' => $usuario,
                                                                                               'empresas' => $empresas));

    }

    public function ajaxUsernamesEmpresaAction(Request $request)
    {

        $data = [];
        $empresa_id = $request->query->get('empresa_id');
        $term =  $request->query->get('term');
        $term = '%'.$term.'%';

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                                    WHERE u.login LIKE :term AND u.empresa = :empresa_id')
                    ->setParameters(array( 'term' => $term, 'empresa_id' => $empresa_id));
        $usuarios = $query->getResult();

        foreach ($usuarios as $usuario) {
            array_push($data, $usuario->getLogin());
        }

        $return = json_encode($data);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDetalleParticipanteAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fn = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->request->get('empresa_id');
        $login = $request->request->get('username');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneReport = $fn->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml);

        // Condiciones iniciales
        $data_found = 0;
        $dataUsuario = array();
        $html = '';

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $login,
                                                                                                        'empresa' => $empresa_id));

        if ($usuario)
        {

            $data_found = 1;
            $nivel_id = $usuario->getNivel() ? $usuario->getNivel()->getId() : 0;
            $reporte = $rs->detalleParticipanteProgramas($usuario->getId(), $empresa_id, $nivel_id, $yml, $pdfdetalle = 0);
            //return new response(var_dump($reporte));
            //tomar los valores devueltos por la consulta, transformarlos segun la zona horaria y actualizarlos en el array si tiene
            if($reporte['ingresos']['primeraConexion']!='Nunca se ha conectado') {
                $primeraConexion = $fn->converDate($reporte['ingresos']['primeraConexion'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $ultimaConexion = $fn->converDate($reporte['ingresos']['ultimaConexion'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $reporte['ingresos']['primeraConexion'] = $primeraConexion->fecha.' '.$primeraConexion->hora;
                $reporte['ingresos']['ultimaConexion'] = $ultimaConexion->fecha.' '.$ultimaConexion->hora;
            }

            $dataUsuario = array('foto' => trim($usuario->getFoto()) ? trim($usuario->getFoto()) : 0,
                                 'login' => $usuario->getLogin(),
                                 'nombre' => $usuario->getNombre(),
                                 'apellido' => $usuario->getApellido(),
                                 'clave'   => $usuario->getClave(),
                                 'correoPersonal' => $usuario->getCorreoPersonal(),
                                 'fechaNacimiento' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('d/m/Y') : '',
                                 'activo' => $usuario->getActivo() ? $this->get('translator')->trans('Sí') : 'No',
                                 'correoCorporativo' => $usuario->getCorreoCorporativo(),
                                 'campo1' => $usuario->getCampo1(),
                                 'campo2' => $usuario->getCampo2(),
                                 'campo3' => $usuario->getCampo3(),
                                 'campo4' => $usuario->getCampo4(),
                                 'nivel' => $usuario->getNivel() ? $usuario->getNivel()->getNombre() : '',
                                 'ingresos' => $reporte['ingresos'],
                                 'timeZone' => $timeZoneReport);

            $html = $this->renderView('LinkBackendBundle:Reportes:detalleParticipanteProgramas.html.twig', array('programas' => $reporte['programas']));

        }

        $return = array('usuario' => $dataUsuario,
                        'data_found' => $data_found,
                        'html' => $html);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function pdfDetalleParticipanteAction($empresa_id,$login,Request $request)
    {

        /*$html = $this->renderView('LinkBackendBundle:Reportes:pdfDetalleParticipante1.html.twig');
        $pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 5, 5, 8));
        $pdf->pdf->SetDisplayMode('fullpage');
        $pdf->writeHtml('<page>'.$html.'</page>');
        //Generamos el PDF
        $pdf->output('HORAS CONEXION.pdf');*/
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $fn = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

       // $empresa_id = $request->request->get('empresa_id');
        //$login = $request->request->get('username');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $timeZoneEmpresa = ($empresa->getZonaHoraria())? $empresa->getZonaHoraria()->getNombre():$yml['parameters']['time_zone']['default'];
        $timeZoneReport = $fn->clearNameTimeZone($timeZoneEmpresa,$empresa->getPais()->getNombre(),$yml);

        // Condiciones iniciales
        $data_found = 0;
        $dataUsuario = array();
        $html = '';

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $login,
                                                                                                        'empresa' => $empresa_id));

        if ($usuario)
        {

            $data_found = 1;
            $nivel_id = $usuario->getNivel() ? $usuario->getNivel()->getId() : 0;
            $reporte = $rs->detalleParticipanteProgramas($usuario->getId(), $empresa_id, $nivel_id, $yml,$pdfdetalle=1);
            //tomar los valores devueltos por la consulta, transformarlos segun la zona horaria y actualizarlos en el array si tiene
            if($reporte['ingresos']['primeraConexion']!='Nunca se ha conectado') {
                $primeraConexion = $fn->converDate($reporte['ingresos']['primeraConexion'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $ultimaConexion = $fn->converDate($reporte['ingresos']['ultimaConexion'],$yml['parameters']['time_zone']['default'],$timeZoneEmpresa);
                $reporte['ingresos']['primeraConexion'] = $primeraConexion->fecha.' '.$primeraConexion->hora;
                $reporte['ingresos']['ultimaConexion'] = $ultimaConexion->fecha.' '.$ultimaConexion->hora;
            }

            $dataUsuario = array('foto' => trim($usuario->getFoto()) ? $this->container->getParameter('folders')['uploads'].trim($usuario->getFoto()) : $this->container->getParameter('folders')['dir_project'].'web/img/user.png',
                                 'login' => $usuario->getLogin(),
                                 'nombre' => $usuario->getNombre(),
                                 'apellido' => $usuario->getApellido(),
                                 'clave'   => $usuario->getClave(),
                                 'correoPersonal' => $usuario->getCorreoPersonal(),
                                 'fechaNacimiento' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('d/m/Y') : '',
                                 'activo' => $usuario->getActivo() ? $this->get('translator')->trans('Sí') : 'No',
                                 'correoCorporativo' => $usuario->getCorreoCorporativo(),
                                 'campo1' => $usuario->getCampo1(),
                                 'campo2' => $usuario->getCampo2(),
                                 'campo3' => $usuario->getCampo3(),
                                 'campo4' => $usuario->getCampo4(),
                                 'nivel' => $usuario->getNivel() ? $usuario->getNivel()->getNombre() : '',
                                 'ingresos' => $reporte['ingresos'],
                                 'timeZone' => $timeZoneReport,
                                 'empresa' => $empresa->getNombre());
            $logo = $this->container->getParameter('folders')['dir_project'].'web/img/logo_formacion_smart.png';
            $html = $this->renderView('LinkBackendBundle:Reportes:pdfDetalleParticipante1.html.twig', array('programas' => $reporte['programas'],
                                                                                                            'datausuario'=> $dataUsuario,
                                                                                                            'logo' => $logo));
            $html1 = $this->renderView('LinkBackendBundle:Reportes:pdfDetalleParticipante2.html.twig', array('programas' => $reporte['programas'],
                                                                                                             'datausuario'=> $dataUsuario,
                                                                                                             'logo' => $logo));                                                                                               
            $pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 5, 5, 8));
            $pdf->pdf->SetDisplayMode('fullpage');
            $pdf->writeHtml('<page>'.$html.'</page>');
            $pdf->writeHtml('<page>'.$html1.'</page>');
            //Generamos el PDF
            
            $return = json_encode($pdf->output('Detalle del participante.pdf'));
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

    }


}
