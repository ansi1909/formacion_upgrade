<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class DefaultController extends Controller
{
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');
        $rs = $this->get('reportes');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}
        $f->setRequest($session->get('sesion_id'));
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $reporteAprobados = false;
        
        $rolesReporteAprobados = array($yml['parameters']['rol']['administrador'],$yml['parameters']['rol']['tutor']);
       
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        
        $query = $em->createQuery('SELECT ru.id as id FROM LinkComunBundle:AdminRolUsuario ru
                                    WHERE ru.usuario = :usuario_id
                                    AND   ru.rol IN (:roles) ')
                                 ->setParameters(
                                                  array(
                                                        'roles'      => $rolesReporteAprobados,
                                                        'usuario_id' => $usuario->getId()
                                                       )
                                                );
        $rolHistAprobados = $query->getResult();
        //print_r($rolHistAprobados);die();
        if(count($rolHistAprobados)>0){
            $reporteAprobados = $rs->historicoAprobados();
        }
    

        if($session->get('administrador') == 'true' || !$usuario->getEmpresa())
        {

            $query = $em->createQuery('SELECT e.id as id,
                                              e.nombre as nombre,
                                              e.activo as activa,
                                              p.id as pais,
                                              COUNT(u.id) as usuarios
                                        FROM LinkComunBundle:AdminEmpresa e
                                         JOIN LinkComunBundle:AdminPais p WITH p.id = e.pais
                                        LEFT JOIN LinkComunBundle:AdminUsuario u WITH u.empresa = e.id
                                        GROUP BY e.id, p.id,e.nombre,e.activo
                                        ORDER BY e.nombre ASC');
            $empresas_db = $query->getResult();
            $empresasA = array();
            $empresasI = array();

            foreach($empresas_db as $empresa)
            {
                if($empresa['activa']){
                    $empresasA[] = array(
                        'id'=>$empresa['id'],
                        'nombre'=>$empresa['nombre'],
                        'usuarios'=>$empresa['usuarios']
                    );
                }else{

                    $programas = '';
                    $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                               JOIN pe.pagina p
                                               WHERE pe.empresa = :empresa_id
                                               AND p.pagina IS NULL')
                                ->setParameter('empresa_id', $empresa['id']);
                    $paginas_db = $query->getResult();

                    foreach ($paginas_db as $pagina)
                    {
                        $programas .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$pagina->getPagina()->getId().'" p_str="'.$pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre().'">'.$pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre();
                        $programas .= '</li>';
                    }
                    $empresasI[] = array(
                     'id'=>$empresa['id'],
                     'nombre'=>$empresa['nombre'],
                     'usuarios'=>$empresa['usuarios'],
                     'pais'=>$empresa['pais'],
                     'programas'=>$programas
                 );
                }

            }
            $paises = array();
            $query = $em->createQuery('SELECT p.id as id, p.nombre as nombre FROM LinkComunBundle:AdminEmpresa e
                                    JOIN e.pais p
                                    WHERE e.activo = :activo
                                    GROUP BY p.id,p.nombre')
                         ->setParameter('activo', 'TRUE');
            $paises_db = $query->getResult();

            //return new response(count($paises_db));
            foreach ($paises_db as $pais)
            {
                $paises[] = array('id' => $pais['id'],
                                  'nombre' => $pais['nombre']);
            }


            $response = $this->render('LinkBackendBundle:Default:index.html.twig', array('empresast' => count($empresasA) + count($empresasI),
                                                                                         'activas' => count($empresasA),
                                                                                         'inactivas' => count($empresasI),
                                                                                         'paises' => $paises,
                                                                                         'empresasI' => $empresasI,
                                                                                         'usuario' => $usuario,
                                                                                         'reporteAprobados' => $reporteAprobados
                                                                                        ));

            return $response;

        }
        else {

            $usuarios_activos = 0;
            $usuarios_inactivos = 0;
            $usuarios_registrados = 0;
            $usuarios_sin_acceso = 0;

            $query = $em->getConnection()->prepare('SELECT
                                            fnreporte_general2(:re, :pempresa_id) as
                                            resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':pempresa_id', $usuario->getEmpresa()->getId(), \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();

            foreach ($r as $re) {
                if($re['acceso']){
                    if($re['logueado']){
                        $usuarios_activos++;
                    }else{
                        $usuarios_inactivos++;
                    }
                }else{
                    $usuarios_sin_acceso++;
                }
            }
            $usuarios_registrados = count($r);

            $query = $em->getConnection()->prepare('SELECT
                                                    fnreporte_general(:re, :pempresa_id) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':pempresa_id', $usuario->getEmpresa()->getId(), \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();
            //var_dump($r);die();
            foreach($r as $re)
            {
                $paginas[] = array('pagina' => $re['nombre'],
                                   'fecha_i' => ($re['fecha_inicio_nivel'])? $re['fecha_inicio_nivel']:$re['fecha_inicio'],
                                   'fecha_f' => ($re['fecha_vencimiento_nivel'])? $re['fecha_vencimiento_nivel']:$re['fecha_vencimiento'],
                                   'nivel'  =>  ($re['nombre_nivel'])? $re['nombre_nivel']:'',
                                   'usuariosT' => $re['registrados'],
                                   'usuariosCur' => $re['cursando'],
                                   'usuariosF' => $re['culminado'],
                                   'usuariosN' => $re['no_iniciados'],
                                   'usuariosA' => $re['activos'],
                                   'id' => $re['id']);
            }

            $response = $this->render('LinkBackendBundle:Default:index.html.twig', array('activos' => $usuarios_activos,
                                                                                         'inactivos' => $usuarios_inactivos,
                                                                                         'sin_acceso' => $usuarios_sin_acceso,
                                                                                         'total' => $usuarios_registrados,
                                                                                         'paginas' => $paginas,
                                                                                         'usuario' => $usuario,
                                                                                         'reporteAprobados' => $reporteAprobados));

            return $response;

        }

    }

    public function ajaxEmpresasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $pais_id = $request->query->get('pais_id');
        $query = $em->createQuery('SELECT e FROM LinkComunBundle:AdminEmpresa e
                                    WHERE e.activo = TRUE
                                    AND e.pais = :pais_id')
                    ->setParameter('pais_id', $pais_id);
        $empresas_db = $query->getResult();

        $empresas = array();
        $html='<option value="0"></option>';
        foreach ($empresas_db as $empresa)
        {
            $html.='<option value="'.$empresa->getId().'" >'.$empresa->getNombre().'</option>';

        }

        $return = json_encode($html);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxProgramasDashboardAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $empresa_id = $request->query->get('empresa_id');

        $usuarios_activos = 0;
        $usuarios_inactivos = 0;
        $usuarios_registrados = 0;
        $usuarios_sin_acceso = 0;

        $query = $em->getConnection()->prepare('SELECT
                                        fnreporte_general2(:re, :pempresa_id) as
                                        resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re) {
            if($re['acceso']){
                if($re['logueado']){
                    $usuarios_activos++;
                }else{
                    $usuarios_inactivos++;
                }
            }else{
                $usuarios_sin_acceso++;
            }
        }

        $usuarios_registrados = count($r);


        $html = '<table class="table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title text-center" >'.$this->get('translator')->trans('Usuarios registrados').'</th>
                            <th class="hd__title text-center" >'.$this->get('translator')->trans('Usuarios activos').'</th>
                            <th class="hd__title text-center"  >'.$this->get('translator')->trans('Usuarios inactivos').'</th>
                            <th class="hd__title text-center" >'.$this->get('translator')->trans('Usuarios sin acceso').'</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><a href="'.$this->generateUrl('_participantesEmpresa', array('app_id' => 20, 'pagina_id' => 0, 'empresa_id' => $empresa_id)).'"><span data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Son todos los usuarios inscritos por una empresa.').'" >'. $usuarios_registrados .'<i class="fa fa-user"></i></span></a></td>
                            <td class="text-center"><span data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Son todos los usuarios inscritos por una empresa, que tienen acceso a la plataforma y que se han logueado al menos una vez.').'">'. $usuarios_activos .'<i class="fa fa-user"></i></span></td>
                            <td class="text-center"><span data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Son todos los usuarios inscritos por una empresa, que tienen acceso a la plataforma y que no se han logueado.').'">'. $usuarios_inactivos .'<i class="fa fa-user"></i></span></td>
                            <td class="text-center"><span  data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Son todos los usuarios inscritos por una empresa que no tienen acceso a la plataforma.').'">'. $usuarios_sin_acceso .'<i class="fa fa-user"></i></span></td>
                        </tr>
                    </tbody>
                </table>

            <table class="table data_table">
                <thead class="sty__title">
                    <tr>
                        <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Fecha Inicio').'</th>
                        <th class="hd__title">'.$this->get('translator')->trans('Fecha Fin').'</th>
                        <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios no iniciados').'</th>
                        <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios cursando').'</th>
                        <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios culminados').'</th>
                        <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios activos').'</th>
                        <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios registrados').'</th>
                    </tr>
                </thead>
                <tbody>';

        $query = $em->getConnection()->prepare('SELECT
                                                fnreporte_general(:re, :pempresa_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re)
        {
            //return new response($re['fecha_inicio_nivel'].' '.$re['fecha_inicio']);
            $fecha_inicio = $re['fecha_inicio_nivel']? $re['fecha_inicio_nivel']:$re['fecha_inicio'];
            $fecha_vencimiento = $re['fecha_vencimiento_nivel']? $re['fecha_vencimiento_nivel']:$re['fecha_vencimiento'];

            $html .= '<tr>
                        <td >'. $re['nombre'] .'</td>
                        <td >'. $re['nombre_nivel'] .'</td>
                        <td >'. $fecha_inicio .'</td>
                        <td >'. $fecha_vencimiento.'</td>
                        <td class="text-center"><a href="'.$this->generateUrl('_participantesNoIniciados', array('app_id' => 34, 'pagina_id' => $re['id'], 'empresa_id' => $empresa_id )).'"><span>'. $re['no_iniciados'] .' <i class="fa fa-user"></i></span></a></td>
                        <td class="text-center"><a href="'.$this->generateUrl('_participantesCursando', array('app_id' => 21, 'pagina_id' => $re['id'], 'empresa_id' => $empresa_id )).'"><span>'. $re['cursando'] .'<i class="fa fa-user"></i></span></a></td>
                        <td class="text-center"><a href="'.$this->generateUrl('_participantesAprobados', array('app_id' => 22, 'pagina_id' => $re['id'], 'empresa_id' => $empresa_id )).'"><span>'. $re['culminado'] .' <i class="fa fa-user"></i></span></a></td>
                        <td class="text-center"><span>'. $re['activos'] .'<i class="fa fa-user"></i></span></td>
                        <td class="text-center"><a href="'.$this->generateUrl('_participantesRegistrados', array('app_id' => 20, 'pagina_id' => $re['id'], 'empresa_id' => $empresa_id )).'"><span>'. $re['registrados'] .'<i class="fa fa-user"></i></span></a></td>
                      </tr>';
        }

        $html .= ' </tbody>
                </table>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-16 col-md-16 col-lg-16 col-xl-16 text-right">
                           <a href="'.$this->generateUrl('_excelReporteGeneral', array('app_id' => 20, 'empresa_id' => $empresa_id)).'"> <button type="button" class="bttn__edit" data-toggle="tooltip" data-placement="bottom" title="Descargar"><span class="fa fa-download"></span></button></a>
                        </div>
                    </div>
                </div>';


        $return = json_encode($html);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function authExceptionAction()
    {
        return $this->render('LinkBackendBundle:Default:authException.html.twig');
    }

    public function ajaxDeleteAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        if($entity=='AdminNotificacionProgramada'){
          //eliminar hijos dentro de la misma tabla
           $query = $em->createQuery("DELETE  FROM LinkComunBundle:AdminNotificacionProgramada np
                                      WHERE np.grupo = :padre_id")
                    ->setParameter('padre_id',$id);
            $query->getResult();
        }

        $ok = 1;
        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $em->remove($object);
        $em->flush();

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxActiveAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $checked = $request->request->get('checked');

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $object->setActivo($checked ? true : false);
        $em->persist($object);
        $em->flush();

        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxOrderAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');
        $orden = $request->request->get('orden');

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $object->setOrden($orden);
        $em->persist($object);
        $em->flush();

        $return = array('id' => $object->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function loginAction(Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $verificacion='';
        $em = $this->getDoctrine()->getManager();

        //validamos que exista una cookie
        if($_COOKIE && isset($_COOKIE["id_usuario"]))
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario"],
                                                                                           'cookies' => $_COOKIE["marca_aleatoria_usuario"] ) );

            if($usuario)
            {
                $recordar_datos=1;
                $login = $usuario->getLogin();
                $clave = $usuario->getClave();
                $verificacion=1;
            }
            else {
                $error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
            }
        }
        else {
            if ($request->getMethod() == 'POST')
            {
                $recordar_datos = $request->request->get('recordar');
                $login = $request->request->get('usuario');
                $clave = $request->request->get('clave');
                $verificacion = 1;
            }
        }

        if($verificacion)
        {
            $iniciarSesion = $f->iniciarSesionAdmin(array('recordar_datos' => $recordar_datos,'login' => $login,'clave' => $clave,'yml' => $yml['parameters'] ));

            if($iniciarSesion['exito']==true)
            {
                return $this->redirectToRoute('_inicioAdmin');
            }
            else {
                if($iniciarSesion['error']==true)
                {
                    $response = $this->render('LinkBackendBundle:Default:login.html.twig', array('error' => $iniciarSesion['error'] ));
                    return $response;
                }
            }
        }
        else {
            $response = $this->render('LinkBackendBundle:Default:login.html.twig', array('error' => $error ));
            return $response;
        }

    }

    public function ajaxQRAction(Request $request)
    {

        $nombre = $request->request->get('nombre');
        $contenido = $request->request->get('contenido');
        $size = $request->request->get('size');

        $nombre = $nombre.'.png';
        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
        $uploads = $this->container->getParameter('folders')['uploads'];

        \PHPQRCode\QRcode::png($contenido, $dir_uploads."recursos/qr/".$nombre, 'H', $size, 4);

        $ruta ='<img src="'.$uploads.'recursos/qr/'.$nombre.'">';

        $return = array('ruta' =>$ruta);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function reordenarAsignacionAction($empresa_id, Request $request)
    {

        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $orden = 0;
        $paginas_ordenadas = array();

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                    AND p.pagina IS NULL
                                    ORDER BY p.orden')
                    ->setParameter('empresa_id', $empresa_id);
        $pes = $query->getResult();

        foreach ($pes as $pe)
        {

            $orden++;
            $pe->setOrden($orden);
            $em->persist($pe);
            $em->flush();

            $paginas_ordenadas[] = array('orden' => $orden,
                                         'pagina_id' => $pe->getPagina()->getId(),
                                         'pagina_empresa_id' => $pe->getId(),
                                         'nombre' => $pe->getPagina()->getCategoria()->getNombre().' '.$pe->getPagina()->getNombre(),
                                         'subpaginas' => $f->reordenarSubAsignaciones($pe));

        }

        return new Response('<p><b>Páginas asignadas a la empresa '.$empresa->getNombre().':</b></p>'.var_dump($paginas_ordenadas));

    }

    public function ajaxUsuariosConectadosAction(Request $request )
    {

       $session = new Session();
       $em = $this->getDoctrine()->getManager();
       $rs = $this->get('reportes');
       $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));


       $empresa_id = (integer) $request->request->get('empresa_id');

       $listado = $rs->usuariosConectados($empresa_id, $session->get('usuario')['id']);
       $usuariosConectados = count($listado);

       $html = '<table class="table data_table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Usuario').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Dispositivo').'</th>';
                       if (!$empresa_id) {
                            $html .= '<th class="hd__title">'.$this->get('translator')->trans('Empresa').'</th>';

                        }
                        $html .= '<th class="hd__title">'.$this->get('translator')->trans('País').'</th>';
                        $html.='</tr>
                    </thead>
                    <tbody style="font-size: .7rem;">';
        foreach ($listado as $registro)
        {
            $correo = ($registro['correo_corporativo'])? strtolower($registro['correo_corporativo']) : strtolower($registro['correo_personal']);
            $html .= '<tr>
                        <td>'.$registro['login'].'</td>
                        <td>'.$registro['nombre'].' '.$registro['apellido'].'</td>
                        <td>'.$correo.'</td>
                        <td>'.$registro['dispositivo'].'</td>';
                        if (!$empresa_id) {
                            $html .= '<td>'.$registro['empresa'].'</td>';

                        }
                        $html .= '<td>'.$registro['ubicacion'].'</td>';
                    $html .= '</tr>';
        }

        $html .= '</tbody>
                </table>';

        $return = array('conectados' => $usuariosConectados, 'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function excelUsuariosConectadosAction($empresa_id, Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));


        //$empresa_id = (integer) $request->request->get('empresa_id');

        //return new response($empresa_id);

        $listado = $rs->usuariosConectados($empresa_id, $session->get('usuario')['id']);

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);



        //return new response(var_dump($listado));



        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/ListadoUsuariosConectados.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Listado de usuarios conectados'));

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
            
            //return new response(var_dump($res));

            foreach ($listado as $usuario)
            {

                $objWorksheet->getStyle("A$row:G$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:G$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:G$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:G$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:G$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(40); // Altura de la fila
                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $usuario['nombre']);
                $objWorksheet->setCellValue('B'.$row, $usuario['apellido']);
                $objWorksheet->setCellValue('C'.$row, $usuario['login']);
                $objWorksheet->setCellValue('D'.$row, $usuario['correo_corporativo']);
                $objWorksheet->setCellValue('E'.$row, $usuario['correo_personal']);
                $objWorksheet->setCellValue('F'.$row, $usuario['dispositivo']);
                $objWorksheet->setCellValue('G'.$row, $usuario['ubicacion']);
                $row++;

            }

        }

        // Crea el writer
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
        $hoy = date('y-m-d');

        // Envia la respuesta del controlador
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // Agrega los headers requeridos

            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'Usuarios conectados'.$hoy.'.xlsx'
            );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;


    }

    
    public function excelHistoricoAprobadosAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $rs = $this->get('reportes');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $listado = $rs->historicoAprobados();
        
        
        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/ListadoHistoricoAprobados.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        
        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Reporte - Histórico de aprobados por programa'));

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
            $total = 0;
            foreach ($listado as $curso)
            {

                $objWorksheet->getStyle("A$row:E$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:E$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:E$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:E$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:E$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(40); // Altura de la fila
                // Datos de las columnas comunes -- ;os valores tipo int estan dando conflicto en la version 7.4 php
                $objWorksheet->setCellValue('A'.$row, (string) $curso['id']);
                $objWorksheet->setCellValue('B'.$row, $curso['nombre']);
                $objWorksheet->setCellValue('C'.$row, $curso['estatus_pagina']);
                $objWorksheet->setCellValue('D'.$row, $curso['categoria']);
                $objWorksheet->setCellValue('E'.$row, (string) $curso['aprobados']);
                $total = $curso['aprobados'] + $total;
                $row++;
            }

        }
        
        $objWorksheet->getStyle("E$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
        $objWorksheet->getStyle("E$row")->getFont()->setSize($font_size); // Tamaño de las letras
        $objWorksheet->getStyle("E$row")->getFont()->setName($font); // Tipo de letra
        $objWorksheet->getStyle("E$row")->getFont()->setBold(true); // Tipo de letra
        $objWorksheet->getStyle("E$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
        $objWorksheet->getStyle("E$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
        $objWorksheet->setCellValue('E'.$row,'Total: '.(string) $total);
        

        // Crea el writer
        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
        $hoy = date('y-m-d');

        // Envia la respuesta del controlador
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // Agrega los headers requeridos

            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    'Historico de aprobados'.$hoy.'.xlsx'
            );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;


    }

}
