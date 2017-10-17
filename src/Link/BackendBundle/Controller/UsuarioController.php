<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminAplicacion;

class UsuarioController extends Controller
{
    public function indexAction($app_id, $rol_id, $empresa_id, $nivel_id, Request $request)
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

        // Roles asignados
        $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru 
                                    WHERE ru.usuario = :usuario_id AND ru.rol = :rol_id')
                    ->setParameters(array('usuario_id' => 1,
                                          'rol_id' => $rol_id));
        $roles_usuario = $query->getResult();
        return new Response(var_dump($roles_usuario));

        $qb = $em->createQueryBuilder();
        $qb->select('u')
           ->from('LinkComunBundle:AdminUsuario', 'u');
        
        if ($empresa_id)
        {
            $qb->andWhere('u.empresa = :empresa_id');
            $parametros['empresa_id'] = $empresa_id;
        }

        if ($nivel_id)
        {
            $qb->andWhere('u.nivel = :nivel_id');
            $parametros['nivel_id'] = $nivel_id;
        }
        
        $qb->orderBy('u.nombre', 'ASC')
           ->orderBy('u.apellido', 'ASC');
        
        if ($empresa_id || $nivel_id)
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
                /*$query = $em->createQuery('SELECT COUNT(ru.id) FROM LinkComunBundle:AdminRolUsuario ru 
                                            WHERE ru.usuario = :usuario_id AND ru.rol = :rol_id')
                            ->setParameters(array('usuario_id' => $usuario->getId(),
                                                  'rol_id' => $rol_id));
                $incluir = $query->getSingleScalarResult();*/

            }
            else {
                $incluir = 1;
            }

            if ($incluir)
            {

                return new Response('usuario_id: '.$usuario->getId());
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

        //$roles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->findAll();
        //$empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        return new Response(var_dump($usuarios));

        return $this->render('LinkBackendBundle:Usuario:index.html.twig', array('usuarios' => $usuarios,
        																	    'roles' =>$roles,
                                                                                'empresas' => $empresas));

    }

}
