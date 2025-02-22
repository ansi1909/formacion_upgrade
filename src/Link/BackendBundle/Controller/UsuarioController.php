<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminUsuario;
use Link\ComunBundle\Entity\AdminRolUsuario;

class UsuarioController extends Controller
{
    public function indexAction($app_id, Request $request)
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
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $niveles = array();
        $empresa_asignada = $f->rolEmpresa($session->get('usuario')['id'], $session->get('usuario')['roles'], $values);
        $parameters = array();
        if ($request->getMethod() == 'POST')
        {
            $rol_id = $request->request->get('rol_id');
            //return new response($rol_id);
            $empresa_id = $request->request->get('empresa_id');
            $nivel_id = $request->request->get('nivel_id');
            $nombre = trim(strtolower($request->request->get('nombre')));
            $apellido = trim(strtolower($request->request->get('apellido')));
            $login = trim(strtolower($request->request->get('login')));
        }
        else {
            $rol_id = $values['parameters']['rol']['empresa'];
            $empresa_id = $empresa_asignada;
            $nivel_id = 0;
            $nombre = '';
            $apellido = '';
            $login = '';
        }
        $query_base = 'SELECT * FROM view_usuarios_roles';
        $query_final = 'SELECT * FROM view_usuarios_roles';
        $where = ' WHERE ';
        $and = ' AND ';

        if ($rol_id){
            if($rol_id == '8')
            {
                $rol =  $em->getRepository('LinkComunBundle:AdminRol')->find($rol_id);
                //return new response ($rol->getNombre());
                $rol = explode(" ",$rol->getNombre());
                $rol = 'rol_tutor_'.strtolower($rol[1]);
                //return new response ($rol);
            }else{
                $rol =  $em->getRepository('LinkComunBundle:AdminRol')->find($rol_id);
                //return new response ($rol->getNombre());
                $rol = explode(" ",$rol->getNombre());
                $rol = 'rol_'.strtolower($rol[0]);
                //return new response ($rol);
            }

           $query_final.= ($query_final==$query_base)?  $where."$rol <> 0":$and."$rol <> 0";
        }
        if ($empresa_id)
        {
           $query_final.= ($query_final==$query_base)?  $where."empresa_id = $empresa_id":$and."empresa_id = $empresa_id";
        }
        if ($nivel_id)
        {
            $query_final.= ($query_final==$query_base)?  $where."nivel_id = $nivel_id":$and."nivel_id = $nivel_id";
        }
        if ($nombre != ''){
            $query_final.= ($query_final==$query_base)?  $where."nombre ILIKE '".$nombre."%')":$and." nombre  ILIKE '".$nombre."%'";
        }
       if ($apellido != ''){
             $query_final.= ($query_final==$query_base)?  $where."apellido ILIKE '".$apellido."%')":$and." apellido  ILIKE '".$apellido."%'";
       }
       if ($login != ''){
            $query_final.= ($query_final==$query_base)?  $where."login ILIKE '".$login."%')":$and." login  ILIKE '".$login."%'";
       }
       $query = $em->getConnection()->prepare($query_final);
       $query->execute();
       $usuarios_db = $query->fetchAll();
       $usuarios = array();
       $roles_viewSql = array('rol_administrador','rol_participante','rol_tutor','rol_reporte','rol_empresa','rol_tutor_empresa');
       foreach ($usuarios_db as $usuario)
        {
            $roles_usuario = array();
            foreach ($roles_viewSql as $rol_vista) {
                if ($usuario[$rol_vista]) {
                    array_push($roles_usuario,$values['parameters']['rol_convertir'][$rol_vista]);
                }
            }

            $usuarios[] = array('id' => $usuario['id'],
                                'nombre' => $usuario['nombre'],
                                'apellido' => $usuario['apellido'],
                                'login' => $usuario['login'],
                                'password' => $usuario['clave'],
                                'activo' => $usuario['activo'],
                                'empresa' => $usuario['empresa'] ? $usuario['empresa'] : '',
                                'nivel' => $usuario['nivel'] ?  $usuario['nivel'] : '',
                                'roles' => $roles_usuario);

        }



        if ($empresa_asignada && !$session->get('administrador'))
        {

            // Select de roles
            $query = $em->createQuery('SELECT r FROM LinkComunBundle:AdminRol r
                                        WHERE r.id != :administrador')
                        ->setParameter('administrador', $values['parameters']['rol']['administrador']);
            $roles = $query->getResult();

            // Select de empresas
            $query = $em->createQuery('SELECT e FROM LinkComunBundle:AdminEmpresa e
                                        WHERE e.id = :empresa_asignada')
                        ->setParameter('empresa_asignada', $empresa_asignada);
            $empresas = $query->getResult();
        }
        else {
            $roles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->findAll();
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        }

        return $this->render('LinkBackendBundle:Usuario:index.html.twig', array('usuarios' => $usuarios,
                                                                                'roles' => $roles,
                                                                                'rol_id' => $rol_id,
                                                                                'empresas' => $empresas,
                                                                                'empresa_id' => $empresa_id,
                                                                                'niveles' => $niveles,
                                                                                'nivel_id' => $nivel_id,
                                                                                'nombre' => $nombre,
                                                                                'apellido' => $apellido,
                                                                                'login' => $login,
                                                                                'empresa_asignada' => $empresa_asignada));

    }



    public function usuarioAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $roles_usuario = array();
        $roles_asignados = array();
        $empresa_asignada = 0;

        $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->findOneById2($session->get('code'));

        if ($usuario_id)
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
            $roles_usuario = $em->getRepository('LinkComunBundle:AdminRolUsuario')->findByUsuario($usuario_id);
            foreach ($roles_usuario as $ru)
            {
                $roles_asignados[] = $ru->getRol()->getId();
            }
            if ($usuario->getEmpresa())
            {
                $empresa_asignada = $usuario->getEmpresa()->getId();
            }
        }
        else {
            $usuario = new AdminUsuario();
            $usuario->setPais($pais);
            $usuario->setFechaRegistro(new \DateTime('now'));
            $empresa_asignada = $f->rolEmpresa($session->get('usuario')['id'], $session->get('usuario')['roles'], $yml);
        }
        // Lista de paises
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('LinkComunBundle:AdminPais', 'p')
           ->orderBy('p.nombre', 'ASC');
        $query = $qb->getQuery();
        $paises = $query->getResult();

        // Lista de empresas
        $qb = $em->createQueryBuilder();
        $qb->select('e')
           ->from('LinkComunBundle:AdminEmpresa', 'e');
        if ($empresa_asignada)
        {
            $qb->where('e.id = :empresa_asignada')
               ->setParameter('empresa_asignada', $empresa_asignada);
        }
        else {
            $qb->where('e.activo = :activo')
               ->setParameter('activo', true);
        }
        $qb->orderBy('e.nombre', 'ASC');
        $query = $qb->getQuery();
        $empresas = $query->getResult();

        // Niveles de la empresa asignada
        $niveles = array();
        if ($empresa_asignada || $usuario->getEmpresa())
        {
            $empresa_usuario = $usuario->getEmpresa() ? $usuario->getEmpresa()->getId() : $empresa_asignada;
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_usuario),
                                                                                                 array('nombre' => 'ASC'));
        }

        // Lista de roles
        $qb = $em->createQueryBuilder();
        $qb->select('r')
           ->from('LinkComunBundle:AdminRol', 'r');

        if ($empresa_asignada){
            $qb->andWhere('r.id != :administrador');
            $parametros['administrador'] = $yml['parameters']['rol']['administrador'];
        }

        $qb->orderBy('r.nombre', 'ASC');

        if ($empresa_asignada)
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $roles = $query->getResult();

        // Lista de roles de empresa
        $qb = $em->createQueryBuilder();
        $qb->select('r')
           ->from('LinkComunBundle:AdminRol', 'r')
           ->andWhere('r.empresa = :empresa')
           ->setParameter('empresa', true);
        $query = $qb->getQuery();
        $roles_empresa_bd = $query->getResult();
        $roles_empresa = array();
        foreach ($roles_empresa_bd as $rol_empresa)
        {
            $roles_empresa[] = $rol_empresa->getId();
        }
        $roles_empresa_str = implode(",", $roles_empresa);


        if ($request->getMethod() == 'POST')
        {

            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $foto = $request->request->get('foto');
            $login = strtolower($request->request->get('login'));
            $clave = $request->request->get('clave');
            $cambiar = $request->request->get('cambiar');
            $correo_personal = $request->request->get('correo_personal');
            $fecha_nacimiento = $request->request->get('fecha_nacimiento');
            $activo = $request->request->get('activo');
            $correo_corporativo = $request->request->get('correo_corporativo');
            $pais_id = $request->request->get('pais_id');
            $campo1 = $request->request->get('campo1');
            $campo2 = $request->request->get('campo2');
            $empresa_id = $request->request->get('empresa_id');
            $nivel_id = $request->request->get('nivel_id');
            $campo3 = $request->request->get('campo3');
            $campo4 = $request->request->get('campo4');
            $roles_seleccionados = $request->request->get('roles');

            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
    
            if($empresa->getLimiteUsuarios())
            {
                $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminRolUsuario ru
                                        JOIN ru.usuario u
                                        join u.nivel n
                                        WHERE u.empresa = :empresa_id
                                        AND  u.activo = :activo
                                        AND ru.rol = :rol_id
                                        and LOWER(n.nombre) not like :revisor
                                        and LOWER(n.nombre) not like :tutor')
                            ->setParameters(array('empresa_id' => $empresa->getId(),
                                            'activo' => 'true',
                                            'rol_id' => $yml['parameters']['rol']['participante'],
                                            'revisor' => 'revisor%',
                                            'tutor' => 'tutor%'));
                $usuarios_activos = $query->getSingleScalarResult();
                return new response($usuarios_activos);
                foreach( $roles_seleccionados as $rol)
                {
                    if($rol == $yml['parameters']['rol']['participante'] && $activo )
                    {
                        
                            if($usuarios_activos >= $empresa->getLimiteUsuarios())
                            {
                                //return new response(var_dump($roles));
                                $limite_usuarios = $this->get('translator')->trans('Se ha excedido el límite de usuarios con acceso permitido para la empresa').'. '.$usuarios_activos.'/'.$empresa->getlimiteUsuarios();
                                return $this->render('LinkBackendBundle:Usuario:usuario.html.twig', array('usuario' => $usuario,
                                                                                                        'paises' => $paises,
                                                                                                        'empresas' => $empresas,
                                                                                                        'empresa_asignada' => $empresa_asignada,
                                                                                                        'niveles' => $niveles,
                                                                                                        'roles' => $roles,
                                                                                                        'roles_asignados' => $roles_asignados,
                                                                                                        'roles_empresa_str' => $roles_empresa_str,
                                                                                                        'limite_usuarios' => $limite_usuarios));
                            }
                    }
                }
            }

            $pais = $pais_id ? $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id) : null;
            $empresa = $empresa_id ? $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id) : null;
            $nivel = $nivel_id ? $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id) : null;

            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setLogin($login);
            if (!$usuario_id || $cambiar)
            {
                $usuario->setClave($clave);
            }
            $usuario->setCorreoPersonal($correo_personal);
            $usuario->setCorreoCorporativo($correo_corporativo);
            $usuario->setActivo($activo ? true : false);
            if ($fecha_nacimiento)
            {
                $fn_array = explode("/", $fecha_nacimiento);
                $d = $fn_array[0];
                $m = $fn_array[1];
                $a = $fn_array[2];
                $fecha_nacimiento = "$a-$m-$d";
                $usuario->setFechaNacimiento(new \DateTime($fecha_nacimiento));
            }
            else {
                $usuario->setFechaNacimiento(null);
            }

            $query = $em->getConnection()->prepare("SELECT MAX(CAST(codigo AS INTEGER)) FROM admin_usuario where empresa_id = ".$empresa_id." AND codigo ~ '^\d+$'");
            $query->execute();
            $r = $query->fetchAll();
            $max_codigo = $r[0]['max'] != '' ? $r[0]['max'] : 0;
            $max_codigo++;
            if ($usuario_id) {
                if ($usuario->getCodigo()=='') {
                    $usuario->setCodigo($max_codigo);
                }
            }else{
                $usuario->setCodigo($max_codigo);
            }
            $usuario->setPais($pais);
            $usuario->setCampo1($campo1);
            $usuario->setCampo2($campo2);
            $usuario->setEmpresa($empresa);
            $usuario->setFoto($foto);
            $usuario->setCampo3($campo3);
            $usuario->setCampo4($campo4);
            $usuario->setNivel($nivel);
            $em->persist($usuario);
            $em->flush();

            // Se buscan los roles asignados para eliminar los que no fueron seleccionados
            foreach ($roles_usuario as $ru)
            {
                if (!in_array($ru->getRol()->getId(), $roles_seleccionados))
                {
                    $em->remove($ru);
                    $em->flush();
                }
            }

            foreach ($roles_seleccionados as $rs)
            {

                $rol_asignado = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRolUsuario')->findOneBy(array('rol' => $rs,
                                                                                                                        'usuario' => $usuario->getId()));

                if (!$rol_asignado)
                {

                    $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($rs);

                    $rol_usuario = new AdminRolUsuario();
                    $rol_usuario->setRol($rol);
                    $rol_usuario->setUsuario($usuario);
                    $em->persist($rol_usuario);
                    $em->flush();

                }

            }

            return $this->redirectToRoute('_showUsuario', array('usuario_id' => $usuario->getId()));

        }

        return $this->render('LinkBackendBundle:Usuario:usuario.html.twig', array('usuario' => $usuario,
                                                                                  'paises' => $paises,
                                                                                  'empresas' => $empresas,
                                                                                  'empresa_asignada' => $empresa_asignada,
                                                                                  'niveles' => $niveles,
                                                                                  'roles' => $roles,
                                                                                  'roles_asignados' => $roles_asignados,
                                                                                  'roles_empresa_str' => $roles_empresa_str,
                                                                                  'limite_usuarios' => ' '));

    }

    public function showUsuarioAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $roles_asignados = array();
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

        $roles_usuario = $em->getRepository('LinkComunBundle:AdminRolUsuario')->findByUsuario($usuario_id);
        foreach ($roles_usuario as $ru)
        {
            $roles_asignados[] = $ru->getRol()->getId();
        }

        // Lista de roles
        $qb = $em->createQueryBuilder();
        $qb->select('r')
           ->from('LinkComunBundle:AdminRol', 'r');

        if ($usuario->getEmpresa() && !$session->get('administrador')){
            $qb->andWhere('r.id != :administrador');
            $parametros['administrador'] = $yml['parameters']['rol']['administrador'];
        }

        $qb->orderBy('r.nombre', 'ASC');

        if ($usuario->getEmpresa() && !$session->get('administrador'))
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $roles = $query->getResult();

        return $this->render('LinkBackendBundle:Usuario:show.html.twig', array('usuario' => $usuario,
                                                                               'roles' => $roles,
                                                                               'roles_asignados' => $roles_asignados));

    }

    public function ajaxValidLoginAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $login = strtolower($request->request->get('login'));

        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminUsuario u
                                    WHERE u.login = :login')
                    ->setParameter('login', $login);
        $ok = $query->getSingleScalarResult();

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxValidQueryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $html ='';
        $login = strtolower(trim($request->request->get('login')));
        $usuario_id = (int)$request->request->get('usuario_id');
        $correo_corporativo =  strtolower(trim($request->request->get('correo_corporativo')));
        $correo_personal =  strtolower(trim($request->request->get('correo_personal')));
        $empresa_id = $request->request->get('empresa_id');
        $uid = ($usuario_id != 0)? $usuario_id:0;
        //validar si existe el login cuando se agrega un nuevo usuario
        if ($usuario_id == 0 ) {
             $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminUsuario u
                                    WHERE u.login = :login')
                    ->setParameter('login', $login);
             $lg = $query->getSingleScalarResult();
             if ($lg!=0) {
                $html .= '<li> -'.$this->get('translator')->trans('El login ya existe').'.</li>';
             }
        }
        //validar si los correos ingresados existen
        if($correo_corporativo!=''){
            $cc = $f->searchMail($correo_corporativo,$empresa_id,$uid);
            if($cc!=0){
              $html .= '<li> -'.$this->get('translator')->trans('El correo corporativo ya existe para la empresa seleccionada').'.</li>';
            }
        }
        if($correo_personal!=''){
            $cp = $f->searchMail($correo_personal,$empresa_id,$uid);
            if($cp!=0){
              $html .= '<li> -'.$this->get('translator')->trans('El correo personal ya existe para la empresa seleccionada').'.</li>';
            }
        }
        $return = json_encode(array('html' => $html));
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }


    public function participantesAction($app_id, Request $request)
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

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
        }
        else {
            $query = $em->createQuery('SELECT e FROM LinkComunBundle:AdminEmpresa e
                                        ORDER BY e.nombre ASC');
            $empresas = $query->getResult();
        }

        return $this->render('LinkBackendBundle:Usuario:participantes.html.twig', array('empresas' => $empresas,
                                                                                        'usuario_empresa' => $usuario_empresa,
                                                                                        'usuario' => $usuario));

    }

    public function ajaxParticipantesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $nivel_id = $request->query->get('nivel_id');
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $qb = $em->createQueryBuilder();
        $qb->select('ru, u')
           ->from('LinkComunBundle:AdminRolUsuario', 'ru')
           ->leftJoin('ru.usuario', 'u')
           ->andWhere('u.empresa = :empresa_id')
           ->andWhere('ru.rol = :participante');
        $parametros['empresa_id'] = $empresa_id;
        $parametros['participante'] = $yml['parameters']['rol']['participante'];

        if ($nivel_id)
        {
            $qb->andWhere('u.nivel = :nivel_id');
            $parametros['nivel_id'] = $nivel_id;
        }

        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $rus = $query->getResult();

        $html = '<table class="table" id="dt">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Apellido').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Login').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Password').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Acciones').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($rus as $ru)
        {
            $delete_disabled = $f->linkEliminar($ru->getUsuario()->getId(), 'AdminUsuario');
            $delete = $delete_disabled == '' ? 'delete' : '';
            $html .= '<tr>
                        <td>'.$ru->getUsuario()->getNombre().'</td>
                        <td>'.$ru->getUsuario()->getApellido().'</td>
                        <td>'.$ru->getUsuario()->getNivel()->getNombre().'</td>
                        <td>'.$ru->getUsuario()->getLogin().'</td>
                        <td>'.$ru->getUsuario()->getClave().'</td>
                        <td class="center">
                            <a href="'.$this->generateUrl('_nuevoParticipante', array('usuario_id' => $ru->getUsuario()->getId())).'" class="btn btn-link btn-sm"><span class="fa fa-pencil"></span></a>
                            <a href="#" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$ru->getUsuario()->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function nuevoParticipanteAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $empresa_asignada = $f->rolEmpresa($session->get('usuario')['id'], $session->get('usuario')['roles'], $yml);

        $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->findOneById2($session->get('code'));

        if ($usuario_id)
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
        }
        else {
            $usuario = new AdminUsuario();
            $usuario->setPais($pais);
            $usuario->setFechaRegistro(new \DateTime('now'));
        }

        // Lista de paises
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('LinkComunBundle:AdminPais', 'p')
           ->orderBy('p.nombre', 'ASC');
        $query = $qb->getQuery();
        $paises = $query->getResult();

        // Lista de empresas
        $qb = $em->createQueryBuilder();
        $qb->select('e')
           ->from('LinkComunBundle:AdminEmpresa', 'e');
        if ($empresa_asignada)
        {
            $qb->where('e.id = :empresa_asignada')
               ->setParameter('empresa_asignada', $empresa_asignada);
        }
        $qb->orderBy('e.nombre', 'ASC');
        $query = $qb->getQuery();
        $empresas = $query->getResult();

        // Niveles de la empresa asignada
        $niveles = array();
        if ($empresa_asignada || $usuario->getEmpresa())
        {
            $empresa_usuario = $usuario->getEmpresa() ? $usuario->getEmpresa()->getId() : $empresa_asignada;
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_usuario),
                                                                                                 array('nombre' => 'ASC'));
        }

        if ($request->getMethod() == 'POST')
        {

            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $foto = $request->request->get('foto');
            $login = strtolower($request->request->get('login'));
            $clave = $request->request->get('clave');
            $cambiar = $request->request->get('cambiar');
            $correo_personal = $request->request->get('correo_personal');
            $fecha_nacimiento = $request->request->get('fecha_nacimiento');
            $activo = $request->request->get('activo');
            $correo_corporativo = $request->request->get('correo_corporativo');
            $pais_id = $request->request->get('pais_id');
            $campo1 = $request->request->get('campo1');
            $campo2 = $request->request->get('campo2');
            $empresa_id = $request->request->get('empresa_id');
            $nivel_id = $request->request->get('nivel_id');
            $campo3 = $request->request->get('campo3');
            $campo4 = $request->request->get('campo4');

            $pais = $pais_id ? $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id) : null;
            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);

            $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminRolUsuario ru
                                    JOIN ru.usuario u
                                    join u.nivel n
                                    WHERE u.empresa = :empresa_id
                                    AND  u.activo = :activo
                                    AND ru.rol = :rol_id
                                    and LOWER(n.nombre) not like :revisor
                                    and LOWER(n.nombre) not like :tutor')
                        ->setParameters(array('empresa_id' => $empresa->getId(),
                                        'activo' => 'true',
                                        'rol_id' => $yml['parameters']['rol']['participante'],
                                        'revisor' => 'revisor%',
                                        'tutor' => 'tutor%'));
            $usuarios_activos = $query->getSingleScalarResult();

            //return new response($empresa->getlimiteUsuarios().'   '.$usuarios_activos);
            if($empresa->getLimiteUsuarios())
            {
                if($activo)
                {
                    if($usuarios_activos >= $empresa->getLimiteUsuarios())
                    {
                        $limite_usuarios = $this->get('translator')->trans('Se ha excedido el límite de usuarios con acceso permitido para la empresa').'. '.$usuarios_activos.'/'.$empresa->getlimiteUsuarios();
                        //return new response($limite_usuarios);
                        return $this->render('LinkBackendBundle:Usuario:nuevoParticipante.html.twig', array('usuario' => $usuario,
                                                                                                            'paises' => $paises,
                                                                                                            'empresas' => $empresas,
                                                                                                            'empresa_asignada' => $empresa_asignada,
                                                                                                            'niveles' => $niveles,
                                                                                                            'limite_usuarios' => $limite_usuarios));
                    }
                }
            }

            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setLogin($login);
            if (!$usuario_id || $cambiar)
            {
                $usuario->setClave($clave);
            }
            $usuario->setCorreoPersonal($correo_personal);
            $usuario->setCorreoCorporativo($correo_corporativo);
            $usuario->setActivo($activo ? true : false);
            if ($fecha_nacimiento)
            {
                $fn_array = explode("/", $fecha_nacimiento);
                $d = $fn_array[0];
                $m = $fn_array[1];
                $a = $fn_array[2];
                $fecha_nacimiento = "$a-$m-$d";
                $usuario->setFechaNacimiento(new \DateTime($fecha_nacimiento));
            }
            else {
                $usuario->setFechaNacimiento(null);
            }

            $query = $em->getConnection()->prepare("SELECT MAX(CAST(codigo AS INTEGER)) FROM admin_usuario where empresa_id = ".$empresa_id." AND codigo ~ '^\d+$'");
            $query->execute();
            $r = $query->fetchAll();
            $max_codigo = $r[0]['max'] != '' ? $r[0]['max'] : 0;
            $max_codigo++;
            if ($usuario_id) {
                if ($usuario->getCodigo()=='') {
                    $usuario->setCodigo($max_codigo);
                }
            }else{
                $usuario->setCodigo($max_codigo);
            }
            $usuario->setPais($pais);
            $usuario->setCampo1($campo1);
            $usuario->setCampo2($campo2);
            $usuario->setEmpresa($empresa);
            $usuario->setFoto($foto);
            $usuario->setCampo3($campo3);
            $usuario->setCampo4($campo4);
            $usuario->setNivel($nivel);
            $em->persist($usuario);
            $em->flush();

            $rol_asignado = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRolUsuario')->findOneBy(array('rol' => $yml['parameters']['rol']['participante'],
                                                                                                                    'usuario' => $usuario->getId()));

            if (!$rol_asignado)
            {

                $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($yml['parameters']['rol']['participante']);

                $rol_usuario = new AdminRolUsuario();
                $rol_usuario->setRol($rol);
                $rol_usuario->setUsuario($usuario);
                $em->persist($rol_usuario);
                $em->flush();

            }

            return $this->redirectToRoute('_showParticipante', array('usuario_id' => $usuario->getId()));

        }

        return $this->render('LinkBackendBundle:Usuario:nuevoParticipante.html.twig', array('usuario' => $usuario,
                                                                                            'paises' => $paises,
                                                                                            'empresas' => $empresas,
                                                                                            'empresa_asignada' => $empresa_asignada,
                                                                                            'niveles' => $niveles,
                                                                                            'limite_usuarios' => ' '));

    }


    public function showParticipanteAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

        return $this->render('LinkBackendBundle:Usuario:showParticipante.html.twig', array('usuario' => $usuario));

    }

    public function uploadParticipantesAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {

            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $empresa_asignada = $f->rolEmpresa($session->get('usuario')['id'], $session->get('usuario')['roles'], $yml);
        $filas_analizadas = 0;
        $file = '';
        $empresa_id = 0;

        // Estructura de los errores
        /*
            array('general' => $error_msg,
                  'particulares' => array('linea' => array('columna' => $error_msg)))
        */
        $errores = array();

        // Lista de empresas
        $qb = $em->createQueryBuilder();
        $qb->select('e')
           ->from('LinkComunBundle:AdminEmpresa', 'e')
           ->andWhere('e.activo = :activo');
        $parametros['activo'] = true;
        if ($empresa_asignada)
        {
            $qb->andWhere('e.id = :empresa_asignada');
            $parametros['empresa_asignada'] = $empresa_asignada;
        }
        $qb->setParameters($parametros);
        $qb->orderBy('e.nombre', 'ASC');
        $query = $qb->getQuery();
        $empresas = $query->getResult();

        if ($request->getMethod() == 'POST')
        {

            $empresa_id = $request->request->get('empresa_id');
            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $file = $request->request->get('file');
            $fileWithPath = $this->container->getParameter('folders')['dir_uploads'].$file;

            

            //return new response(var_dump($usuarios_activos));

            if(!file_exists($fileWithPath))
            {
                $errores['general'] = $this->get('translator')->trans('El archivo').' '.$fileWithPath.' '.$this->get('translator')->trans('no existe');
            }
            else {

                $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');

                $spreadsheet = $readerXlsx->load($fileWithPath);

                // Se obtienen las hojas, el nombre de las hojas y se pone activa la primera hoja
                $total_sheets = $spreadsheet->getSheetCount();
                $allSheetName = $spreadsheet->getSheetNames();
                $objWorksheet = $spreadsheet->setActiveSheetIndex(0);

                // Se obtiene el número máximo de filas y columnas
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                //return new Response($highestRow);

                if ($highestRow < 1)
                {
                    $errores['general'] = $this->get('translator')->trans('El archivo debe tener al menos una fila con datos').'.';
                }
                else {


                    //Se recorre toda la hoja excel desde la fila 2
                    $hay_data = 0;
                    $particulares = array();
                    $codigos = array(); // No pueden existir códigos repetidos
                    $logins = array(); // No pueden existir logins repetidos
                    $correos = array(); // No pueden existir correos repetidos
                    
                    for ($row=2; $row<=$highestRow; ++$row)
                    {

                       $filas_analizadas++;

                        // Código del empleado
                        $col = 1;
                        $col_name = 'A';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $codigo = trim($cell->getValue());
                        if ($codigo)
                        {
                            $hay_data++;
                            if (in_array($codigo, $codigos))
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Código del empleado repetido').'.';
                            }
                            else {
                                $codigos[] = $codigo;
                            }
                        }

                        // Login
                        $col++;
                        $col_name = 'B';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $login = trim($cell->getValue());
                        if ($login)
                        {
                            $hay_data++;
                            if (in_array($login, $logins))
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Login repetido').'.';
                            }
                            else {
                                $logins[] = $login;
                            }
                        }
                        else {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Login requerido').'.';
                        }

                        // Nombres
                        $col++;
                        $col_name = 'C';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $nombres = trim($cell->getValue());
                        if ($nombres)
                        {
                            $hay_data++;
                            if (preg_match('/[^A-Za-záÁéÉíÍóÓúÚñÑ ]/', $nombres))
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Nombres deben ser solo alfabético').'.';
                            }
                        }
                        else {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Nombre requerido').'.';
                        }

                        // Apellidos
                        $col++;
                        $col_name = 'D';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $apellidos = trim($cell->getValue());
                        if ($apellidos)
                        {
                            $hay_data++;
                            if (preg_match('/[^A-Za-záÁéÉíÍóÓúÚñÑ ]/', $apellidos))
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Apellidos deben ser solo alfabéticos').'.';
                            }
                        }
                        else {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Apellido requerido').'.';
                        }

                        // Fecha de registro
                        $col++;
                        $col_name = 'E';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $fecha_registro = trim($cell->getValue());
                        if ($fecha_registro)
                        {
                            
                            $hay_data++;
                            if ($cell->getDataType() != 's')
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La fecha de registro debe ser del tipo texto en formato DD/MM/AAAA').'.';
                            }
                            else {
                                if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell))
                                {
                                    $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La fecha de registro debe ser del tipo texto en formato DD/MM/AAAA').'.';
                                }
                                else {
                                    if (!$f->checkFecha($fecha_registro))
                                    {
                                        $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La fecha de registro debe ser en formato DD/MM/AAAA y ser válida').'.';
                                    }else{
                                        
                                        #Es necesario transformar la hora que obtenemos (UTC) del servidor a la hora de venezuela
                                        #Ya que a las 8:00 pm de venezuela en UTC serian las 12:00 am del dia siguiente
                                        
                                        $hoy = new \DateTime('now');
                                        $reg = new \DateTime(str_replace("/","-",$fecha_registro));
                                        $hoy = $f->converDate($hoy->format('d-m-Y H:i:s'),$yml['parameters']['time_zone']['utc'],$yml['parameters']['time_zone']['local']);
                                        $hoy = new \Datetime(str_replace("/","-",$hoy->fecha));

                                        if($reg > $hoy ){    
                                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La fecha de registro debe ser menor o igual a la fecha actual').'.';
                                        }
                                    }
                                }
                            }
                        }

                        // Contraseña
                        $col++;
                        $col_name = 'F';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $clave = trim($cell->getValue());
                        if (!$clave)
                        {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La contraseña es requerida').'.';
                        }
                        else {
                            $hay_data++;
                        }

                        // Correo
                        $col++;
                        $col_name = 'G';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $correo = trim($cell->getValue());
                        //print_r($correo);exit();
                        if ($correo)
                        {
                            $hay_data++;
                            //print_r(filter_var($correo,FILTER_VALIDATE_EMAIL));exit();
                            if ( filter_var($correo,FILTER_VALIDATE_EMAIL))
                            {
                                 if (in_array($correo, $correos))
                                {
                                    $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Correo electrónico repetido').'.';
                                }
                                else {
                                    $correos[] = $correo;
                                }
                            }else
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Formato de correo inválido').'.';
                            }


                            // if (!filter_var($correo,FILTER_VALIDATE_EMAIL))
                            // {
                                // $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Formato de correo inválido').'.';
                            // }
                            // else {

                                // if (in_array($correo, $correos))
                                // {
                                    // $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Correo electrónico repetido').'.';
                                // }
                                // else {
                                    // $correos[] = $correo;
                                // }
                            // }
                        }
                        else {
                            // $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Correo electrónico requerido').'.';
                            $correos[] = null;
                        }

                        // Competencia
                        $col++;
                        $col_name = 'H';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $competencia = trim($cell->getValue());
                        if ($competencia === null || $competencia == '')
                        {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Competencia requerida. Valores válidos 0 o 1.');
                        }
                        else {
                            $hay_data++;
                            if ($cell->getDataType() != 'n')
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Competencia debe ser un valor entero').'.';
                            }
                            else {
                                if (!($competencia == 0 || $competencia == 1))
                                {
                                    $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Competencia debe tener como valor 0 o 1').'.';
                                }
                            }
                        }

                        // País
                        $col++;
                        $col_name = 'I';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $pais = trim($cell->getValue());
                        if ($pais)
                        {
                            $hay_data++;
                            $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:AdminPais p
                                                        WHERE LOWER(TRIM(p.nombre)) = LOWER(:pais)')
                                        ->setParameter('pais', $pais);
                            $existe_pais = $query->getSingleScalarResult();
                            if (!$existe_pais)
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('El país no existe en la base de datos').'.';
                            }
                        }
                        else {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('El país es requerido.');
                        }

                        // Nivel
                        $col = $col+5;
                        $col_name = 'N';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $nivel = trim($cell->getValue());
                        if ($nivel)
                        {
                            $hay_data++;
                            $query = $em->createQuery('SELECT COUNT(n.id) FROM LinkComunBundle:AdminNivel n
                                                        WHERE LOWER(TRIM(n.nombre)) = LOWER(:nivel) AND n.empresa = :empresa_id')
                                        ->setParameters(array('nivel' => $nivel,
                                                              'empresa_id' => $empresa_id));
                            $existe_nivel = $query->getSingleScalarResult();
                            if (!$existe_nivel)
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Nivel no existente para esta empresa').'.';
                            }
                        }
                        else {
                            $query = $em->createQuery('SELECT COUNT(n.id) FROM LinkComunBundle:AdminNivel n
                                                        WHERE n.empresa = :empresa_id')
                                        ->setParameter('empresa_id', $empresa_id);
                            $existe_nivel = $query->getSingleScalarResult();
                            if (!$existe_nivel)
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La empresa aún no tiene configurado ningún nivel').'.';
                            }
                        }

                        // Activo
                        $col++;
                        $col_name = 'O';
                        $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                        $activo = trim($cell->getValue());
                        if ($activo === null || $activo == '')
                        {
                            $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('Indique si el usuario es activo. Valores válidos 0 o 1.');
                        }
                        else {
                            $hay_data++;
                            if ($cell->getDataType() != 'n')
                            {
                                $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La columna Activo debe ser un valor entero').'.';
                            }
                            else {
                                if (!($activo == 0 || $activo == 1))
                                {
                                    $particulares[$this->get('translator')->trans('Línea').' '.$row][$this->get('translator')->trans('Columna').' '.$col_name] = $this->get('translator')->trans('La columna Activo debe tener como valor 0 o 1').'.';
                                    
                                }
                            }
                        }

                        /*if (array_key_exists($this->get('translator')->trans('Línea').' '.$row, $particulares))
                        {
                            // Si existen errores en esta fila, se anexan al conjunto del arreglo de errores
                        }*/

                    }

                    if ($hay_data == 0)
                    {
                        $errores['general'] = $this->get('translator')->trans('El archivo debe tener al menos una fila con datos').'.';
                    }

                    $errores['particulares'] = $particulares;
                    //return new Response(var_dump($errores));

                }


            }

            //return new Response(var_dump($limite_usuarios));

        }

        return $this->render('LinkBackendBundle:Usuario:uploadParticipantes.html.twig', array('empresas' => $empresas,
                                                                                              'empresa_asignada' => $empresa_asignada,
                                                                                              'empresa_id' => $empresa_id,
                                                                                              'errores' => $errores,
                                                                                              'file' => $file,
                                                                                              'filas_analizadas' => $filas_analizadas));

    }

    public function procesarParticipantesAction($empresa_id, $archivo, Request $request){

        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $file = str_replace(",", "/", $archivo);
        $fileWithPath = $this->container->getParameter('folders')['dir_uploads'].$file;
        $transaccion = $f->generarClave();
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        // Ultimó código entero para la empresa
        $query = $em->getConnection()->prepare("SELECT MAX(CAST(codigo AS INTEGER)) FROM admin_usuario where empresa_id = ".$empresa_id." AND codigo ~ '^\d+$'");
        $query->execute();
        $r = $query->fetchAll();
        $max = $r[0]['max'] != '' ? $r[0]['max'] : 0;

        $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
        $spreadsheet = $readerXlsx->load($fileWithPath);

        // Se obtienen las hojas, el nombre de las hojas y se pone activa la primera hoja
        $total_sheets = $spreadsheet->getSheetCount();
        $allSheetName = $spreadsheet->getSheetNames();
        $objWorksheet = $spreadsheet->setActiveSheetIndex(0);

        // Se obtiene el número máximo de filas y columnas
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        // Nuevo objeto Excel para el CSV
        $phpExcelObject = $this->get('phpoffice.spreadsheet')->createSpreadsheet();
        $phpExcelObject->getProperties()->setCreator("formacion")
                       ->setLastModifiedBy($usuario->getNombre().' '.$usuario->getApellido())
                       ->setTitle("CSV Autogenerado")
                       ->setSubject("CSV Autogenerado")
                       ->setDescription("Documento generado para la importación del XLS a formato CSV y posteriormente a la tabla temporal de BD.")
                       ->setKeywords("office 2005 openxml php")
                       ->setCategory("Archivo temporal");
        $phpExcelObject->setActiveSheetIndex(0);

        for ($row=2; $row<=$highestRow; ++$row)
        {

            $r = $row-1; // Se empieza desde la fila 1 el archivo CSV

            // Código del empleado
            $col = 1;
            $col_name = 'A';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $codigo = trim($cell->getValue());
            if (!$codigo)
            {
                // Se autogenera de acuerdo al último registro de usuario de esta empresa
                $max++;
                $codigo = $max;
            }
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $codigo);

            // Login
            $col++;
            $col_name = 'B';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $login = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $login);

            // Nombres
            $col++;
            $col_name = 'C';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $nombres = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $nombres);

            // Apellidos
            $col++;
            $col_name = 'D';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $apellidos = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $apellidos);

            // Fecha de registro
            $col++;
            $col_name = 'E';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $fecha_registro = trim($cell->getValue());
            $fecha_registro = $f->formatDate($fecha_registro);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $fecha_registro);

            // Contraseña
            $col++;
            $col_name = 'F';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $clave = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $clave);

            // Correo
            $col++;
            $col_name = 'G';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $correo = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $correo);

            // Competencia
            $col++;
            $col_name = 'H';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $competencia = trim($cell->getValue());
            $competencia = $competencia ? 1 : 0;
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $competencia);

            // País
            $col++;
            $col_name = 'I';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $pais = trim($cell->getValue());
            $query = $em->createQuery('SELECT p FROM LinkComunBundle:AdminPais p
                                        WHERE LOWER(TRIM(p.nombre)) = LOWER(:pais)')
                        ->setParameter('pais', $pais);
            $paises = $query->getResult();
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $paises[0]->getId());

            // Campo1
            $col++;
            $col_name = 'J';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $campo1 = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $campo1);

            // Campo2
            $col++;
            $col_name = 'K';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $campo2 = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $campo2);

            // Campo3
            $col++;
            $col_name = 'L';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $campo3 = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $campo3);

            // Campo4
            $col++;
            $col_name = 'M';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $campo4 = trim($cell->getValue());
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $campo4);

            // Nivel
            $col++;
            $col_name = 'N';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $nivel = trim($cell->getValue());
            if ($nivel)
            {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNivel n
                                            WHERE LOWER(TRIM(n.nombre)) = LOWER(:nivel) AND n.empresa = :empresa_id')
                            ->setParameters(array('nivel' => $nivel,
                                                  'empresa_id' => $empresa_id));
                $niveles = $query->getResult();
                if (!$niveles)
                {
                    $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNivel n
                                                WHERE n.empresa = :empresa_id
                                                ORDER BY n.id ASC')
                                ->setParameter('empresa_id', $empresa_id);
                    $niveles = $query->getResult();
                }
                $nivel_id = $niveles[0]->getId();
            }
            else {
                $query = $em->createQuery('SELECT n FROM LinkComunBundle:AdminNivel n
                                            WHERE n.empresa = :empresa_id
                                            ORDER BY n.id ASC')
                            ->setParameter('empresa_id', $empresa_id);
                $niveles = $query->getResult();
                $nivel_id = $niveles[0]->getId();
            }
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $nivel_id);

            // Activo
            $col++;
            $col_name = 'O';
            $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
            $activo = trim($cell->getValue());
            $activo = $activo ? 1 : 0;
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $activo);

            // Las últimas 2 columnas son para la empresa_id y transaccion
            $col_name = 'P';
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $empresa_id);

            $col_name = 'Q';
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($col_name.$r, $transaccion);

        }

        // Crea el writer
        $writer = $this->get('phpoffice.spreadsheet')->createWriter($phpExcelObject, 'Csv')
                                        ->setDelimiter('|')
                                        ->setEnclosure('');
        $writer->setUseBOM(true);
        //$csv = $this->container->getParameter('folders')['dir_uploads'].'recursos/participantes/'.$transaccion.'.csv';
        $csv = $this->container->getParameter('folders')['tmp'].$transaccion.'.csv';
        $writer->save($csv);

        if (file_exists($csv))
        {

            chmod($csv,0755);
            // Llamada a la función que importa el CSV a la BD
            $query = $em->getConnection()->prepare('SELECT
                                                    fnimportar_participantes(:pcsv) as
                                                    resultado;');
            $query->bindValue(':pcsv', $csv, \PDO::PARAM_STR);
            $query->execute();
            $r = $query->fetchAll();

            if($empresa->getLimiteUsuarios())
            {
                $query = $em->getConnection()->prepare('SELECT
                                                        fnvalidar_participantes(:pempresa_id, :ptransaccion) as
                                                        resultado;');
                $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                $query->bindValue(':ptransaccion', $transaccion, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();
                
                if($r[0]['resultado'] > $empresa->getLimiteUsuarios())
                {
                    $usuarios_totales = $r[0]['resultado'];
                    $error = $this->get('translator')->trans('Se ha excedido el límite de usuarios con acceso permitido para la empresa').'. '.$usuarios_totales.'/'.$empresa->getlimiteUsuarios();
                    return $this->render('LinkBackendBundle:Usuario:procesarParticipantes.html.twig', array('empresa' => $empresa,
                                                                                                            'return' => null,
                                                                                                            'error' => $error));
                }
            }

            // Llamada a la función de BD que realiza la carga de participantes
            $query = $em->getConnection()->prepare('SELECT
                                                    fncarga_participantes(:pempresa_id, :ptransaccion) as
                                                    resultado;');
            $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
            $query->bindValue(':ptransaccion', $transaccion, \PDO::PARAM_STR);
            $query->execute();
            $r = $query->fetchAll();

            // La respuesta viene Inserts__Updates
            $r_arr = explode("__", $r[0]['resultado']);

            $return = array('inserts' => $r_arr[0],
                            'updates' => $r_arr[1]);

            // Se borra el CSV
            unlink($csv);

        }

        return $this->render('LinkBackendBundle:Usuario:procesarParticipantes.html.twig', array('empresa' => $empresa,
                                                                                                'return' => $return,
                                                                                                'error' => null));

    }

}
