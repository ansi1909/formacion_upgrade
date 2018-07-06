<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiGrupo; 
use Link\ComunBundle\Entity\CertiGrupoPagina;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\CertiPagina;
use Symfony\Component\Yaml\Yaml;


class GrupoController extends Controller
{
   public function indexAction($app_id, $empresa_id)
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

        $gruposdb = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 
        $empresa_id = $empresa_id || $usuario->getEmpresa() ? $empresa_id ? $empresa_id : $usuario->getEmpresa()->getId() : 0;

        if ($empresa_id) 
        {

            $query = $em->createQuery('SELECT g FROM LinkComunBundle:CertiGrupo g
                                        WHERE g.empresa = :empresa_id
                                        ORDER BY g.orden ASC')
                        ->setParameter('empresa_id', $empresa_id);
            $grupos = $query->getResult();

            foreach ($grupos as $grupo)
            {
                $gruposdb[] = array('id' => $grupo->getId(),
                                    'nombre' => $grupo->getNombre(),
                                    'orden' => $grupo->getOrden(),
                                    'delete_disabled' => $f->linkEliminar($grupo->getId(),'CertiGrupo'));
            }

        }
        
        $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        return $this->render('LinkBackendBundle:Grupo:index.html.twig', array('empresas' => $empresas,
                                                                              'usuario' => $usuario,
                                                                              'grupos' => $gruposdb,
                                                                              'empresa_id' => $empresa_id));

    }

    public function ajaxEmpresasGrupoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $f = $this->get('funciones');

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $qb = $em->createQueryBuilder();
        $qb->select('g')
           ->from('LinkComunBundle:CertiGrupo', 'g')
           ->andWhere('g.empresa = :empresa_id')
           ->orderBy('g.orden', 'ASC')
           ->setParameter('empresa_id', $empresa_id);
        $query = $qb->getQuery();
        $grupos_db = $query->getResult();
        
        $html = '<table class="table" id="dt">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title columorden">'.$this->get('translator')->trans('Orden').'</th>
                            <th class="hd__title">Id</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Acciones').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($grupos_db as $grupo) {
            $delete_disabled = $f->linkEliminar($grupo->getId(), 'CertiGrupo');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $html .= '<tr>
                            <td class="columorden">'.$grupo->getOrden().'</td>
                            <td>'.$grupo->getId().'</td>
                            <td>'.$grupo->getNombre().'</td>
                            <td class="center">
                                <a href="#subPanel" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$grupo->getId().'"><span class="fa fa-pencil"></span></a>
                                <a href="#subPanel" class="see" data="'. $grupo->getId() .'" title="'.$this->get('translator')->trans('Asignar').'" class="btn btn-link btn-sm "><span class="fa fa-sitemap"></span></a>
                                <a href="#subPanel" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$grupo->getId().'"><span class="fa fa-trash"></span></a>
                            </td>
                        </tr>';
        }

        $html .='</tbody>
                </table>';
        
        $return = array('html' => $html,
                        'empresa' =>$empresa->getNombre());
 
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
               ->andWhere('g.empresa = :empresa_id')
               ->orderBy('g.orden', 'DESC')
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

            $grupo->setOrden($orden);

        }

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $grupo->setNombre($nombre);
        $grupo->setEmpresa($empresa);
        $em->persist($grupo);
        $em->flush();
                    
        $return = array('id' => $grupo->getId(),
                        'nombre' => $grupo->getNombre(),
                        'delete_disabled' => $f->linkEliminar($grupo->getId(), 'CertiGrupo'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditGrupoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $grupo_id = $request->query->get('grupo_id');
                
        $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);


        $return = array('nombre' => $grupo->getNombre(),
                        'empresa_id' => $grupo->getEmpresa()->getId());

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
        $empresa_id = $grupo->getEmpresa()->getId();

        $object = $em->getRepository('LinkComunBundle:'.$entity)->find($id);
        $em->remove($object);
        $em->flush();

        $query = $em->createQuery('SELECT g FROM LinkComunBundle:CertiGrupo g 
                                    WHERE g.empresa = :empresa_id
                                    ORDER BY g.orden ASC')
                    ->setParameter('empresa_id', $empresa_id);
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

    public function ajaxGrupoPaginasAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $paginas = array();
        $grupo_id = $request->query->get('grupo_id');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $html = '<table class="table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Asignar').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);

        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                    JOIN pe.pagina p
                                    WHERE pe.empresa = :empresa_id
                                    AND p.pagina IS NULL
                                    AND pe.activo = :activo
                                    AND p.estatusContenido = :contenido_activo
                                    ORDER BY p.orden ASC')
                    ->setParameters(array('empresa_id'=> $grupo->getEmpresa()->getId(),
                                          'activo'=> true,
                                          'contenido_activo'=> $yml['parameters']['estatus_contenido']['activo'] ));

        $pes = $query->getResult();

        $query = $em->createQuery('SELECT gp FROM LinkComunBundle:CertiGrupoPagina gp
                                   JOIN gp.grupo g
                                   WHERE g.id != :grupo_id
                                   AND g.empresa = :empresa_id ')
                    ->setParameters(array('grupo_id'=> $grupo->getId(),
                                          'empresa_id'=> $grupo->getEmpresa()->getId()));

        $pes_grupos = $query->getResult();

        $paginas_id = array();

        foreach($pes_grupos as $pes_g)
        {
            $paginas_id[] = $pes_g->getPagina()->getId();
        }

        foreach($pes as $pe)
        {
            if (!in_array($pe->getPagina()->getId(), $paginas_id)) 
            {

                $query = $em->createQuery('SELECT COUNT(pg.id) FROM LinkComunBundle:CertiGrupoPagina pg
                                            WHERE pg.pagina = :pagina_id
                                            AND pg.grupo = :grupo_id ')
                        ->setParameters(array('pagina_id'=> $pe->getPagina()->getId(),
                                              'grupo_id'=> $grupo->getId()));
                $c = $query->getSingleScalarResult();

                $checked = $c ? 'checked' : '';

                $html .= '<tr>
                            <td>'.$pe->getPagina()->getNombre().'</td>
                            <td>
                                <div class="can-toggle demo-rebrand-2 small">
                                    <input id="f'.$pe->getPagina()->getId().'" class="cb_activo" type="checkbox" '.$checked.' >
                                    <input type="hidden" id="grupo_id'.$pe->getPagina()->getId().'" name="grupo_id'.$pe->getPagina()->getId().'" value="'.$grupo->getId().'">
                                    <label for="f'.$pe->getPagina()->getId().'">
                                        <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                                    </label>
                                </div>
                            </td>
                        </tr>';
            }
        }

        $html .='</tbody>
                </table>';
        
        $return = array('html' => $html,
                        'nombre' => $grupo->getNombre());
                   
        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxAsignarAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $pagina_id = $request->request->get('pagina_id');
        $grupo_id = $request->request->get('grupo_id');
        $checked = $request->request->get('checked'); 

        if ($checked == '0') 
        {
            $grupo_p = $em->getRepository('LinkComunBundle:CertiGrupoPagina')->findOneBy(array('grupo' => $grupo_id,
                                                                                               'pagina' => $pagina_id));
            $grupo_p_id = $grupo_p->getId();
            $em->remove($grupo_p);
            $em->flush();
        }
        else {
            $grupo_p = new CertiGrupoPagina();
            $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $grupo = $em->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);
            $grupo_p->setGrupo($grupo);
            $grupo_p->setPagina($pagina);
            $em->persist($grupo_p);
            $em->flush();
            $grupo_p_id = $grupo_p->getId();
        }

        $return = array('id' => $grupo_p_id);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

}
