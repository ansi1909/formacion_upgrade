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
    public function indexAction($app_id,$r,$pagina_id,$empresa_id,Request $request)
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


            // Solicita el servicio de excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("formacion")
               ->setLastModifiedBy($session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'])
               ->setTitle("Listado de participantes")
               ->setSubject("Listado de participantes")
               ->setDescription("Listado de participantes")
               ->setKeywords("office 2005 openxml php")
               ->setCategory("Reportes");
            foreach ($r as $re) {
            
                $i++;
                $activo = $re['activo'] ? 'Sí' : 'No';
                $logueado = $re['logueado'] > 0 ? 'Sí' : 'No';
                $phpExcelObject->setActiveSheetIndex(0)
                               ->setCellValue('A1', 'Nombre')
                               ->setCellValue('B1', 'Apellido')
                               ->setCellValue('C1', 'Login')
                               ->setCellValue('D1', 'Correo Personal')
                               ->setCellValue('E1', 'Correp Corporativo')
                               ->setCellValue('F1', 'Activo')
                               ->setCellValue('G1', 'Logueado')
                               ->setCellValue('H1', 'Fecha de registro')
                               ->setCellValue('I1', 'Fecha de nacimiento')
                               ->setCellValue('J1', 'País')
                               ->setCellValue('K1', 'Nivel')
                               ->setCellValue('L1', 'campo1')
                               ->setCellValue('M1', 'campo2')
                               ->setCellValue('N1', 'campo3')
                               ->setCellValue('O1', 'campo4')
                               ->setCellValue('A'.$i, $re['nombre'])
                               ->setCellValue('B'.$i, $re['apellido'])
                               ->setCellValue('C'.$i, $re['login'])
                               ->setCellValue('D'.$i, $re['correo'])
                               ->setCellValue('E'.$i, $re['correo2'])
                               ->setCellValue('F'.$i, $activo)
                               ->setCellValue('G'.$i, $logueado)
                               ->setCellValue('H'.$i, $re['fecha_registro'])
                               ->setCellValue('I'.$i, $re['fecha_nacimiento'])
                               ->setCellValue('J'.$i, $re['pais'])
                               ->setCellValue('K'.$i, $re['nivel'])
                               ->setCellValue('L'.$i, $re['campo1'])
                               ->setCellValue('M'.$i, $re['campo2'])
                               ->setCellValue('N'.$i, $re['campo3'])
                               ->setCellValue('O'.$i, $re['campo4']);
            }
            $phpExcelObject->getActiveSheet()->setTitle('Participantes');

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            //$writer->setUseBOM(true);
            //$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            //$writer->save($this->container->getParameter('folders')['dir_uploads'].'recursos/participantes/data.csv');
            
            // Envia la respuesta del controlador
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // Agrega los headers requeridos
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'ListadoDeParticipantes.xlsx'
            );

            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;


                //return new Response('Archivo creado...');

            //return new Response (var_dump($r));

        }

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 

        return $this->render('LinkBackendBundle:Reportes:index.html.twig', array('empresas' => $empresas,
                                                                                 'usuario_empresa' => $usuario_empresa,
                                                                                 'usuario' => $usuario,
                                                                                 'reporte'=>$r,
                                                                                 'pagina_id'=>$pagina_id,
                                                                                 'empresa_dashboard'=>$empresa_id));    
    }

    public function ajaxProgramasEAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_previa');

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina IS NULL')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            if ($pagina->getPagina()->getId() == $pagina_id) 
            {
                $options .= '<option value="'.$pagina->getPagina()->getId().'" selected >'.$pagina->getPagina()->getNombre().'  </option>';
            }
            else
            {
                 $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getNombre().' </option>';
            }
           
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
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
                            <th class="hd__title">'.$this->get('translator')->trans('Apellido').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Login').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Activo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de registro').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de nacimiento').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('País').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: .7rem;">';
        
        foreach ($r as $ru)
        {
            $activo = $ru['activo'] ? $this->get('translator')->trans('Sí') : 'No';
            $html .= '<tr>
                        <td>'.$ru['nombre'].'</td>
                        <td>'.$ru['apellido'].'</td>
                        <td>'.$ru['login'].'</td>
                        <td>'.$ru['correo'].'</td>
                        <td>'.$activo.'</td>
                        <td>'.$ru['fecha_registro'].'</td>
                        <td>'.$ru['fecha_nacimiento'].'</td>
                        <td>'.$ru['pais'].'</td>
                        <td>'.$ru['nivel'].'</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';
        
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

        // Lógica inicial de la pantalla de este reporte
        $datos = 'Foo';

        return $this->render('LinkBackendBundle:Reportes:interaccionColaborativo.html.twig', array('datos' => $datos));

    }

    public function interaccionMuroAction($app_id, $empresa_id, $desde, $hasta, Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        
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
                                        ORDER BY p.id ASC")
                        ->setParameter('empresa_id', $empresa_id);
            $pages = $query->getResult();

            foreach ($pages as $page)
            {
                $tiene++;
                $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getPagina()->getId().'" p_str="'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre().'">'.$page->getPagina()->getCategoria()->getNombre().': '.$page->getPagina()->getNombre();
                $subPaginas = $f->subPaginasEmpresa($page->getPagina()->getId(), $empresa_id);
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

    public function ajaxInteraccionMuroAction(Request $request)
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

        $listado = $rs->interaccionMuro($empresa_id, $pagina_id, $desde, $hasta);

        if ($excel)
        {

            $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/interaccionMuro.xlsx';
            $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
            $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
            $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            // Encabezado
            $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Interacciones de muro').'. '.$this->get('translator')->trans('Desde').': '.$desdef.'. '.$this->get('translator')->trans('Hasta').': '.$hastaf.'.');
            $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre().'. '.$this->get('translator')->trans('Programa').': '.$pagina->getNombre().'.');

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

                    $limit_iterations = count($participante['muro'])-1;
                    $limit_row = $row+$limit_iterations;

                    // Estilizar las celdas antes de un posible merge
                    for ($f=$row; $f<=$limit_row; $f++)
                    {
                        $objWorksheet->getStyle("A$f:S$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:S$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:S$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:S$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:S$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
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

                    // Datos de las columnas comunes
                    $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $participante['login']);
                    $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $participante['fecha_registro']);
                    $objWorksheet->setCellValue('F'.$row, $participante['correo']);
                    $objWorksheet->setCellValue('G'.$row, $participante['pais']);
                    $objWorksheet->setCellValue('H'.$row, $participante['nivel']);
                    $objWorksheet->setCellValue('I'.$row, $participante['campo1']);
                    $objWorksheet->setCellValue('J'.$row, $participante['campo2']);
                    $objWorksheet->setCellValue('K'.$row, $participante['campo3']);
                    $objWorksheet->setCellValue('L'.$row, $participante['campo4']);

                    // Datos de los mensajes
                    foreach ($participante['muros'] as $muro)
                    {
                        $objWorksheet->setCellValue('M'.$row, $muro['fecha_mensaje']);
                        $objWorksheet->setCellValue('N'.$row, $muro['mensaje']);
                        $row++;
                    }

                }

            }

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
            $path = 'recursos/reportes/interaccionMuro'.$session->get('sesion_id').'.xls';
            $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
            $writer->save($xls);

            $archivo = $this->container->getParameter('folders')['uploads'].$path;
            $html = '';
        }
        else{

            $archivo = '';
            $html = $this->renderView('LinkBackendBundle:Reportes:interaccionMuroTabla.html.twig', array('listado' => $listado,
                                                                                                         'empresa' => $empresa->getNombre(),
                                                                                                         'programa' => $pagina->getNombre()));
        }
        
        $return = array('archivo' => $archivo,
                        'html' => $html);

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

        $query = $em->getConnection()->prepare('SELECT
                                                fnreporte_general(:re, :pempresa_id) as
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
           
        }

        $query2 = $em->getConnection()->prepare('SELECT
                                                fnreporte_general2(:re, :pempresa_id) as
                                                resultado; fetch all from re;');
        $re1 = 're';
        $query2->bindValue(':re', $re1, \PDO::PARAM_STR);
        $query2->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query2->execute();
        $r1 = $query2->fetchAll();


         // Solicita el servicio de excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("formacion")
               ->setLastModifiedBy($session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'])
               ->setTitle("Listado de participantes")
               ->setSubject("Listado de participantes")
               ->setDescription("Listado de participantes")
               ->setKeywords("office 2005 openxml php")
               ->setCategory("Reportes");
            
            //return new Response (var_dump($r1));
            $usuarios_registrados = $usuarios_activos + $usuarios_inactivos;
             foreach ($r1 as $r2) {
                 $i++;
                 $query3 = $em->getConnection()->prepare('SELECT
                                                         fnreporte_general3(:re, :pempresa_id, :ppagina_id) as
                                                         resultado; fetch all from re;');
                 $re2 = 're';
                 $query3->bindValue(':re', $re2, \PDO::PARAM_STR);
                 $query3->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                 $query3->bindValue(':ppagina_id', $r2['id'], \PDO::PARAM_INT);
                 $query3->execute();
                 $r3 = $query3->fetchAll();

                 foreach ($r3 as $r4) {
                    $usuarios_c = $r4['usuarios'];
                 }

                 $query4 = $em->getConnection()->prepare('SELECT
                                                         fnreporte_general4(:re, :pempresa_id, :ppagina_id) as
                                                         resultado; fetch all from re;');
                 $re3 = 're';
                 $query4->bindValue(':re', $re3, \PDO::PARAM_STR);
                 $query4->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                 $query4->bindValue(':ppagina_id', $r2['id'], \PDO::PARAM_INT);
                 $query4->execute();
                 $r4 = $query4->fetchAll();

                 foreach ($r4 as $r5) {
                    $usuarios_f = $r5['usuarios'];
                 }
                 $usuarios_r = $r2['usuarios'];
                 $usuarios_n = $usuarios_r - ($usuarios_f + $usuarios_c);
                 

                 $phpExcelObject->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'Usuarios registrados')
                                   ->setCellValue('B1', 'Usuarios activos')
                                   ->setCellValue('C1', 'Usuarios inactivos')
                                   ->setCellValue('A2', $usuarios_registrados)
                                   ->setCellValue('B2', $usuarios_activos)
                                   ->setCellValue('C2', $usuarios_inactivos)
                                   ->setCellValue('A5', 'Programas')
                                   ->setCellValue('B5', 'Fecha inicio')
                                   ->setCellValue('C5', 'Fecha fin')
                                   ->setCellValue('D5', 'Usuarios registrados')
                                   ->setCellValue('E5', 'Usuarios cursando')
                                   ->setCellValue('F5', 'Usuarios finalizado')
                                   ->setCellValue('G5', 'Usuarios no iniciados')
                                   ->setCellValue('A'.$i, $r2['programa'])
                                   ->setCellValue('B'.$i, $r2['fecha_inicio'])
                                   ->setCellValue('C'.$i, $r2['fecha_fin'])
                                   ->setCellValue('D'.$i, $r2['usuarios'])
                                   ->setCellValue('E'.$i, $usuarios_c)
                                   ->setCellValue('F'.$i, $usuarios_f)
                                   ->setCellValue('G'.$i, $usuarios_n);
            }
            $phpExcelObject->getActiveSheet()->setTitle('Participantes');

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            //$writer->setUseBOM(true);
            //$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            //$writer->save($this->container->getParameter('folders')['dir_uploads'].'recursos/participantes/data.csv');
            
            // Envia la respuesta del controlador
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // Agrega los headers requeridos
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'ListadoDeParticipantes.xlsx'
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
                                    ORDER BY pe.orden')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    
}