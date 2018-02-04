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
        
        if (!$session->get('ini'))
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

        if ($request->getMethod() == 'POST')
        {
            $rol_id = $request->request->get('rol_id');
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

        $qb = $em->createQueryBuilder();
        $qb->select('u')
           ->from('LinkComunBundle:AdminUsuario', 'u');
        
        if ($empresa_id)
        {

            $qb->andWhere('u.empresa = :empresa_id');
            $parametros['empresa_id'] = $empresa_id;

            // Niveles de esta empresa
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_id),
                                                                                                 array('nombre' => 'ASC'));

        }

        if ($nivel_id)
        {
            $qb->andWhere('u.nivel = :nivel_id');
            $parametros['nivel_id'] = $nivel_id;
        }

        if ($nombre != ''){
            $qb->andWhere('LOWER(u.nombre) LIKE :nombre');
            $parametros['nombre'] = '%'.$nombre.'%';
        }

        if ($apellido != ''){
            $qb->andWhere('LOWER(u.apellido) LIKE :apellido');
            $parametros['apellido'] = '%'.$apellido.'%';
        }

        if ($login != ''){
            $qb->andWhere('LOWER(u.login) LIKE :login');
            $parametros['login'] = '%'.$login.'%';
        }
        
        $qb->orderBy('u.nombre', 'ASC')
           ->orderBy('u.apellido', 'ASC');
        
        if ($empresa_id || $nivel_id || $nombre || $apellido || $login)
        {
            $qb->setParameters($parametros);
        }
        
        $query = $qb->getQuery();
        $usuarios_db = $query->getResult();

        $usuarios = array();
        $incluir = 0;
        
        foreach ($usuarios_db as $usuario)
        {

            // Filtro por rol
            if ($rol_id)
            {
                $query = $em->createQuery('SELECT COUNT(ru.id) FROM LinkComunBundle:AdminRolUsuario ru 
                                            WHERE ru.usuario = :usuario_id AND ru.rol = :rol_id')
                            ->setParameters(array('usuario_id' => $usuario->getId(),
                                                  'rol_id' => $rol_id));
                $incluir = $query->getSingleScalarResult();

            }
            else {
                $incluir = 1;
            }

            if ($incluir)
            {

                // Roles asignados
                $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru 
                                            WHERE ru.usuario = :usuario_id')
                            ->setParameter('usuario_id', $usuario->getId());
                $roles_usuario = $query->getResult();

                $usuarios[] = array('id' => $usuario->getId(),
                                    'nombre' => $usuario->getNombre(),
                                    'apellido' => $usuario->getApellido(),
                                    'login' => $usuario->getLogin(),
                                    'activo' => $usuario->getActivo(),
                                    'empresa' => $usuario->getEmpresa() ? $usuario->getEmpresa()->getNombre() : '',
                                    'nivel' => $usuario->getNivel() ? $usuario->getNivel()->getNombre() : '',
                                    'roles' => $roles_usuario,
                                    'delete_disabled' => $f->linkEliminar($usuario->getId(), 'AdminUsuario'));

            }

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
      
        if (!$session->get('ini'))
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
           ->andWhere('r.id != :administrador')
           ->setParameter('administrador', $yml['parameters']['rol']['administrador']);
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
            $ciudad = $request->request->get('ciudad');
            $region = $request->request->get('region');
            $empresa_id = $request->request->get('empresa_id');
            $nivel_id = $request->request->get('nivel_id');
            $division_funcional = $request->request->get('division_funcional');
            $cargo = $request->request->get('cargo');
            $roles_seleccionados = $request->request->get('roles');

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
            $fn_array = explode("/", $fecha_nacimiento);
            $d = $fn_array[0];
            $m = $fn_array[1];
            $a = $fn_array[2];
            $fecha_nacimiento = "$a-$m-$d";
            $usuario->setFechaNacimiento(new \DateTime($fecha_nacimiento));
            $usuario->setPais($pais);
            $usuario->setCiudad($ciudad);
            $usuario->setRegion($region);
            $usuario->setEmpresa($empresa);
            $usuario->setFoto($foto);
            $usuario->setDivisionFuncional($division_funcional);
            $usuario->setCargo($cargo);
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
                                                                                  'roles_empresa_str' => $roles_empresa_str));

    }

    public function showUsuarioAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
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

    public function participantesAction($app_id, Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini'))
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
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
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
      
        if (!$session->get('ini'))
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
            $ciudad = $request->request->get('ciudad');
            $region = $request->request->get('region');
            $empresa_id = $request->request->get('empresa_id');
            $nivel_id = $request->request->get('nivel_id');
            $division_funcional = $request->request->get('division_funcional');
            $cargo = $request->request->get('cargo');
            
            $pais = $pais_id ? $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id) : null;
            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);

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
            $fn_array = explode("/", $fecha_nacimiento);
            $d = $fn_array[0];
            $m = $fn_array[1];
            $a = $fn_array[2];
            $fecha_nacimiento = "$a-$m-$d";
            $usuario->setFechaNacimiento(new \DateTime($fecha_nacimiento));
            $usuario->setPais($pais);
            $usuario->setCiudad($ciudad);
            $usuario->setRegion($region);
            $usuario->setEmpresa($empresa);
            $usuario->setFoto($foto);
            $usuario->setDivisionFuncional($division_funcional);
            $usuario->setCargo($cargo);
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
                                                                                            'niveles' => $niveles));

    }


    public function showParticipanteAction($usuario_id, Request $request){

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
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

        if (!$session->get('ini'))
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
        $errores = array();
        $nuevos_registros = 0;
        
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

         return $this->render('LinkBackendBundle:Usuario:uploadParticipantes.html.twig', array('empresa' => $empresa,
                                                                                      'errores' => $errores,
                                                                                      'nuevos_registros' => $nuevos_registros));

    }

}
