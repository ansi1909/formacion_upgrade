<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiGrupo; 


class empresasGrupoController extends Controller
{
   public function indexAction($app_id)
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

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 

        return $this->render('LinkBackendBundle:empresasGrupo:index.html.twig', array('empresas' => $empresas,
                                                                                        'usuario_empresa' => $usuario_empresa,
                                                                                        'usuario' => $usuario));;

    }

    public function ajaxEmpresasGrupoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $f = $this->get('funciones');

        $qb = $em->createQueryBuilder();
        $qb->select('g')
           ->from('LinkComunBundle:CertiGrupo', 'g');
        $qb->andWhere('g.empresa = :empresa_id');
        $parametros['empresa_id'] = $empresa_id;

        if ($empresa_id)
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $grupos_db = $query->getResult();
        $grupos = '';

        foreach ($grupos_db as $grupo) {
            $delete_disabled = $f->linkEliminar($grupo->getId(), 'CertiGrupo');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $grupos .= '<tr><td>'.$grupo->getOrden().'</td><td>'.$grupo->getId().'</td><td>'.$grupo->getNombre().'</td> <td> </td>
            <td class="center">
                <a href="'.$this->generateUrl('_ajaxUpdateGrupo', array('grupo_id' => $grupo->getId())).'" class="btn btn-link btn-sm"><span class="fa fa-pencil"></span></a>
                <a href="#" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$grupo->getId().'"><span class="fa fa-trash"></span></a>
            </td> </tr>';
        }
        
        $return = array('grupos' => $grupos);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxUpdateGrupoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $grupo_id = $request->request->get('grupo_id');
        $nombre = $request->request->get('nombre');
        $empresa_id = $request->request->get('id_empresa');

        if ($grupo_id)
        {
            $grupo = $em->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);
        }
        else {
            $grupo = new CertiGrupo();

            // Establecer el orden, último por defecto
            $qb = $em->createQueryBuilder();
            $qb->select('g')
               ->from('LinkComunBundle:CertiGrupo', 'g')
               ->orderBy('g.orden', 'DESC');

            $query = $qb->getQuery();
            $grupos = $query->getResult();
            
            if ($grupos)
            {
                $orden = $grupos[0]->getOrden()+1;
            }
            else {
                $orden = 1;
            }

        }

        $grupo->setNombre($nombre);
        $grupo->setOrden($orden);
        $grupo->setEmpresa($empresa_id);
        $em->persist($grupo);
        $em->flush();
                    
        $return = array('id' => $grupo->getId(),
                        'nombre' =>$grupo->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($grupo->getId(),'CertiGrupo'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
