<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminUsuario;

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

        $em = $this->getDoctrine()->getManager();
        $niveles = array();

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
            $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $rol_id = $values['parameters']['rol']['administrador'];
            $empresa_id = 0;
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

        $roles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->findAll();
        $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        return $this->render('LinkBackendBundle:Usuario:index.html.twig', array('usuarios' => $usuarios,
        																	    'roles' => $roles,
                                                                                'rol_id' => $rol_id,
                                                                                'empresas' => $empresas,
                                                                                'empresa_id' => $empresa_id,
                                                                                'niveles' => $niveles,
                                                                                'nivel_id' => $nivel_id,
                                                                                'nombre' => $nombre,
                                                                                'apellido' => $apellido,
                                                                                'login' => $login));

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

        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $empresa_asignada = $f->rolEmpresa($session->get('usuario')['id'], $session->get('usuario')['roles'], $yml);
        $roles_asignados = array();

        $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->findOneById2($session->get('code'));

        if ($usuario_id) 
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
            $roles_usuario = $em->getRepository('LinkComunBundle:AdminRolUsuario')->findByUsuario($usuario_id);
            foreach ($roles_usuario as $ru)
            {
                $roles_asignados[] = $ru->getRol()->getId();
            }
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
        if ($empresa_asignada)
        {
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_asignada),
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

        if ($request->getMethod() == 'POST')
        {

            /*$nombre = $request->request->get('nombre');
            $pais_id = $request->request->get('pais_id');
            $bienvenida = $request->request->get('bienvenida');
            $activo = $request->request->get('activo');

            $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id);

            $empresa->setNombre($nombre);
            $empresa->setActivo($activo ? true : false);
            $empresa->setBienvenida($bienvenida);
            $empresa->setPais($pais);
            $em->persist($empresa);
            $em->flush();

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));*/

        }
        
        return $this->render('LinkBackendBundle:Usuario:usuario.html.twig', array('usuario' => $usuario,
                                                                                  'paises' => $paises,
                                                                                  'empresas' => $empresas,
                                                                                  'empresa_asignada' => $empresa_asignada,
                                                                                  'niveles' => $niveles,
                                                                                  'roles' => $roles,
                                                                                  'roles_asignados' => $roles_asignados));

    }

}
