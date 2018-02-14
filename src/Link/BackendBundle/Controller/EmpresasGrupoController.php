<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiGrupo; 


class EmpresasGrupoController extends Controller
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
        $gruposdb= array();
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 

            $query= $em->createQuery('SELECT g FROM LinkComunBundle:CertiGrupo g
                                        WHERE g.empresa = :empresa_id
                                        ORDER BY g.orden ASC')
                                    ->setParameter('empresa_id', $usuario->getEmpresa()->getId());
            $grupos=$query->getResult();

            foreach ($grupos as $grupo)
            {
                $gruposdb[]= array('id'=>$grupo->getId(),
                              'nombre'=>$grupo->getNombre(),
                              'orden'=>$grupo->getOrden(),
                              'delete_disabled'=>$f->linkEliminar($grupo->getId(),'CertiGrupo'));

            }
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 


        return $this->render('LinkBackendBundle:empresasGrupo:index.html.twig', array('empresas' => $empresas,
                                                                                      'usuario_empresa' => $usuario_empresa,
                                                                                      'usuario' => $usuario,
                                                                                      'grupos' => $gruposdb,
                                                                                      'app_id' => $app_id));

    }

    public function ajaxEmpresasGrupoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $app_id = $request->query->get('app_id');
        $f = $this->get('funciones');

        $qb = $em->createQueryBuilder();
        $qb->select('g')
           ->from('LinkComunBundle:CertiGrupo', 'g')
           ->orderBy('g.orden', 'ASC');
        $qb->andWhere('g.empresa = :empresa_id');
        $parametros['empresa_id'] = $empresa_id;


        if ($empresa_id)
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $grupos_db = $query->getResult();
        $grupos = '<table class="table" id="dt">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title columorden">'.$this->get('translator')->trans('Orden').'</th>
                            <th class="hd__title">Id</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Prog. asociados').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Acciones').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($grupos_db as $grupo) {
            $delete_disabled = $f->linkEliminar($grupo->getId(), 'CertiGrupo');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $grupos .= '<tr><td class="columorden">'.$grupo->getOrden().'</td><td>'.$grupo->getId().'</td><td>'.$grupo->getNombre().'</td> <td> </td>
            <td class="center">
                <a href="#" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$grupo->getId().'"><span class="fa fa-pencil"></span></a>
                <a href="'.$this->generateUrl('_grupoPaginas', array('app_id' => $app_id,'grupo_id' => $grupo->getId(), 'empresa_id' => $grupo->getEmpresa()->getId())).'" title="'.$this->get('translator')->trans('Asignar').'" class="btn btn-link btn-sm "><span class="fa fa-sitemap"></span></a>
                <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$grupo->getId().'"><span class="fa fa-trash"></span></a>
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
        $orden = $request->request->get('orden');

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
            $qb->andWhere('g.empresa = :empresa_id')
               ->setParameter('empresa_id', $empresa_id);


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

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $grupo->setNombre($nombre);
        $grupo->setOrden($orden);
        $grupo->setEmpresa($empresa);
        $em->persist($grupo);
        $em->flush();
                    
        $return = array('id' => $grupo->getId(),
                        'nombre' =>$grupo->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($grupo->getId(),'CertiGrupo'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditGrupoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $grupo_id = $request->query->get('grupo_id');
                
        $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);


        $return = array('nombre' => $grupo->getNombre(),
                        'empresa_id' => $grupo->getEmpresa()->getId(),
                        'orden' => $grupo->getOrden());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDeleteGrupoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $id = $request->request->get('id');
        $entity = $request->request->get('entity');

        $ok = 1;
        
        $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->find($id);

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $em->remove($object);
        $em->flush();

        $query = $em->createQuery('SELECT g FROM LinkComunBundle:CertiGrupo g WHERE g.empresa = :empresa_id')
                    ->setParameter('empresa_id', $grupo->getEmpresa()->getId());
        $grupos_empresa = $query->getResult();
        $orden = 0;

        foreach ($grupos_empresa as $grupo_empresa) {
            $orden = $orden + 1;
            $grupo_empresa->setOrden($orden);
            $em->persist($grupo_empresa);
            $em->flush();
        }

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function grupoPaginasAction($app_id,$grupo_id,$empresa_id)
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
        $paginas_db = array();

        $query = $em->createQuery("SELECT c FROM LinkComunBundle:CertiPagina c 
                                    WHERE c.pagina IS NULL ");

        $paginas = $query->getResult();

        $query = $em->createQuery("SELECT g FROM LinkComunBundle:CertiGrupo g 
                                    WHERE g.empresa = :empresa_id ")
                    ->setParameter('empresa_id', $empresa_id);

        $grupos = $query->getResult();

        foreach ($paginas as $pagina)
        {
            $query = $em->createQuery('SELECT p FROM LinkComunBundle:CertiPaginaEmpresa p 
                                       WHERE p.empresa = :empresa_id AND p.pagina = :pagina_id')
                        ->setParameters(array('empresa_id' => $empresa_id, 
                                              'pagina_id' => $pagina->getId()));

            $paginas_empresa = $query->getResult();

            if ($paginas_empresa) 
            {
                $query = $em->createQuery('SELECT gp FROM LinkComunBundle:CertiGrupoPagina gp 
                                          WHERE gp.grupo = :grupo_id AND gp.pagina = :pagina_id')
                            ->setParameters(array('grupo_id' => $grupo_id, 
                                                  'pagina_id' => $pagina->getId()));

                $paginas_grupo = $query->getResult();
                
                if (!$paginas_grupo) 
                {
                     $paginas_db[] = array('id'=>$pagina->getId(),
                                           'nombre'=>$pagina->getNombre());   
                }
            }       
        }

        return $this->render('LinkBackendBundle:empresasGrupo:asignar_pagina.html.twig', array('paginas' =>$paginas_db));

    }

}
