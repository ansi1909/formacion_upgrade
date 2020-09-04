<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{
    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
      	{
        	return $this->redirectToRoute('_loginAdmin');
      	}
        $f->setRequest($session->get('sesion_id'));

      	$usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

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
                                        GROUP BY e.id, p.id
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
                                    GROUP BY p.id')
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
                                                                                         'usuario' => $usuario));

            return $response;

        }
        else {

            $usuarios_activos = 0;
            $usuarios_inactivos = 0;
            $usuarios_registrados = 0;

            $query = $em->getConnection()->prepare('SELECT
                                            fnreporte_general2(:re, :pempresa_id) as
                                            resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':pempresa_id', $usuario->getEmpresa()->getId(), \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();

            foreach ($r as $re) {
                if ($re['logueado'] > 0) {
                    $usuarios_activos++;
                }
                else {
                    $usuarios_inactivos++;
                }
            }

            $usuarios_registrados = $usuarios_activos + $usuarios_inactivos;

            $query = $em->getConnection()->prepare('SELECT
                                                    fnreporte_general(:re, :pempresa_id) as
                                                    resultado; fetch all from re;');
            $re = 're';
            $query->bindValue(':re', $re, \PDO::PARAM_STR);
            $query->bindValue(':pempresa_id', $usuario->getEmpresa()->getId(), \PDO::PARAM_INT);
            $query->execute();
            $r = $query->fetchAll();

            foreach($r as $re)
            {
                $paginas[] = array('pagina' => $re['nombre'],
                                   'fecha_i' => $re['fecha_inicio'],
                                   'fecha_f' => $re['fecha_vencimiento'],
                                   'usuariosT' => $re['registrados'],
                                   'usuariosCur' => $re['cursando'],
                                   'usuariosF' => $re['culminado'],
                                   'usuariosN' => $re['no_iniciados'],
                                   'usuariosA' => $re['activos'],
                                   'id' => $re['id']);
            }

            $response = $this->render('LinkBackendBundle:Default:index.html.twig', array('activos' => $usuarios_activos,
                                                                                         'inactivos' => $usuarios_inactivos,
                                                                                         'total' => $usuarios_registrados,
                                                                                         'paginas' => $paginas,
                                                                                         'usuario' => $usuario));

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

        $query = $em->getConnection()->prepare('SELECT
                                        fnreporte_general2(:re, :pempresa_id) as
                                        resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re)
        {
            if ($re['logueado'] > 0) {
                $usuarios_activos++;
            }else{
                $usuarios_inactivos++;
            }
        }
        $usuarios_registrados = $usuarios_activos + $usuarios_inactivos;

        $html = '<table class="table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios registrados').'</th>
                            <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios activos').'</th>
                            <th class="hd__title text-center">'.$this->get('translator')->trans('Usuarios inactivos').'</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center"><a href="'.$this->generateUrl('_participantesEmpresa', array('app_id' => 20, 'pagina_id' => 0, 'empresa_id' => $empresa_id)).'"><span>'. $usuarios_registrados .'<i class="fa fa-user"></i></span></a></td>
                            <td class="text-center"><span>'. $usuarios_activos .'<i class="fa fa-user"></i></span></td>
                            <td class="text-center"><span>'. $usuarios_inactivos .'<i class="fa fa-user"></i></span></td>
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

       $empresa_id = (integer) $request->request->get('empresa_id');

       $listado = $rs->usuariosConectados($empresa_id, $session->get('usuario')['id']);
       $usuariosConectados = count($listado);

       $html = '<table class="table data_table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Usuario').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: .7rem;">';
        foreach ($listado as $registro)
        {
            $correo = ($registro['correo_corporativo'])? strtolower($registro['correo_corporativo']) : strtolower($registro['correo_personal']);
            $html .= '<tr>
                        <td>'.$registro['login'].'</td>
                        <td>'.$registro['nombre'].' '.$registro['apellido'].'</td>
                        <td>'.$correo.'</td>
                        <td>'.$registro['nivel'].'</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';

        $return = array('conectados' => $usuariosConectados, 'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}
