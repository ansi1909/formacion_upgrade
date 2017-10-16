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

        return new Response('por aca');

        $qb = $em->createQueryBuilder();
        $qb->select('u, ru')
           ->from('LinkComunBundle:AdminRolUsuario', 'ru')
           ->leftJoin('ru.usuario', 'u');
        
        if ($rol_id)
        {
            $qb->andWhere('ru.rol = :rol_id');
            $parametros['rol_id'] = $rol_id;
        }

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
        
        if ($rol_id || $empresa_id || $nivel_id)
        {
            $qb->setParameters($parametros);
        }
        
        $query = $qb->getQuery();
        $usuarios_db = $query->getResult();

        return new Response(var_dump($usuarios_db));

        $usuarios = array();
        
        /*foreach ($usuarios_db as $usuario)
        {

            // Roles asignados
        	$query = $em->createQuery("SELECT ru FROM LinkComunBundle:AdminRolUsuario ru 
                                        WHERE ru.usuario = :usuario_id  
                                        ORDER BY ru.id ASC")
                        ->setParameter('usuario_id', $usuario->getUsuario()->getId());
            $roles_usuario = $query->getResult();

            $usuarios[] = array('id' => $usuario->getUsuario()->getId(),
                                'nombre' => $usuario->getUsuario()->getNombre(),
                                'apellido' => $usuario->getUsuario()->getApellido(),
                                'login' => $usuario->getUsuario()->getLogin(),
                                'activo' => $usuario->getUsuario()->getActivo(),
                                'empresa' => $usuario->getUsuario()->getEmpresa() ? $usuario->getUsuario()->getEmpresa()->getNombre() : '',
                                'nivel' => $usuario->getUsuario()->getNivel() ? $usuario->getUsuario()->getNivel()->getNombre() : '',
                                'roles' => $roles_usuario,
                                'delete_disabled' => $f->linkEliminar($usuario->getUsuario()->getId(), 'AdminUsuario'));

        }

        $roles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->findAll();
        $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        return new Response(var_dump($usuarios));*/

        return $this->render('LinkBackendBundle:Usuario:index.html.twig', array('usuarios' => $usuarios,
        																	    'roles' =>$roles,
                                                                                'empresas' => $empresas));

    }

}
