<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiCategoria;
use Link\ComunBundle\Entity\CertiEstatusContenido;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PaginaController extends Controller
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

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p 
                                    WHERE p.pagina IS NULL
                                    ORDER BY p.orden ASC");
        $pages = $query->getResult();

        $paginas = $f->paginas($pages);

        //return new Response(var_dump($paginas));

        return $this->render('LinkBackendBundle:Pagina:index.html.twig', array('paginas' => $paginas));

    }

    public function paginaAction($pagina_id)
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
        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findByPagina($pagina_id);
        $subpaginas = $f->paginas($subpages);

        return $this->render('LinkBackendBundle:Pagina:pagina.html.twig', array('pagina' => $pagina,
                                                                                'subpaginas' => $subpaginas));

    }

    public function editAction($pagina_padre_id, $pagina_id, $categoria_id, $estatus_contenido_id, $cantidad, $total, Request $request){

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
        
        if ($pagina_id) 
        {
            $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        }
        else {

            $pagina = new CertiPagina();
            $pagina->setFechaCreacion(new \DateTime('now'));

            // Establecer el orden, último por defecto
            $qb = $em->createQueryBuilder();
            $qb->select('p')
               ->from('LinkComunBundle:CertiPagina', 'p')
               ->orderBy('p.orden', 'DESC');
            
            if ($pagina_padre_id)
            {
                $qb->andWhere('p.pagina = :pagina_id')
                   ->setParameter('pagina_id', $pagina_padre_id);
            }
            else {
                $qb->andWhere('p.pagina IS NULL');
            }

            $query = $qb->getQuery();
            $paginas = $query->getResult();
            
            if ($paginas)
            {
                $orden = $paginas[0]->getOrden()+1;
            }
            else {
                $orden = 1;
            }

            $pagina->setOrden($orden);

        }

        if ($pagina_padre_id)
        {
            $pagina_padre = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_padre_id);
            $pagina->setPagina($pagina_padre);
        }

        if ($categoria_id)
        {
            $categoria = $em->getRepository('LinkComunBundle:CertiCategoria')->find($categoria_id);
            $pagina->setCategoria($categoria);
        }

        if ($estatus_contenido_id)
        {
            $estatus_contenido = $em->getRepository('LinkComunBundle:CertiEstatusContenido')->find($estatus_contenido_id);
            $pagina->setEstatusContenido($estatus_contenido);
        }

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $pagina->setUsuario($usuario);
        $pagina->setFechaModificacion(new \DateTime('now'));

        $form = $this->createFormBuilder($pagina)
            ->setAction($this->generateUrl('_editPagina', array('pagina_padre_id' => $pagina_padre_id,
                                                                'pagina_id' => $pagina_id,
                                                                'categoria_id' => $categoria_id,
                                                                'estatus_contenido_id' => $estatus_contenido_id,
                                                                'cantidad' => $cantidad,
                                                                'total' => $total)))
            ->setMethod('POST')
            ->add('nombre', TextType::class, array('label' => $this->get('translator')->trans('Nombre')))
            ->add('categoria', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiCategoria',
                                                        'choice_label' => 'nombre',
                                                        'expanded' => false,
                                                        'label' => $this->get('translator')->trans('Categoría'),
                                                        'placeholder' => ''))
            ->add('descripcion', TextareaType::class, array('label' => $this->get('translator')->trans('Descripción')))
            ->add('contenido', TextareaType::class, array('label' => $this->get('translator')->trans('Contenido')))
            ->add('foto', HiddenType::class, array('label' => $this->get('translator')->trans('Foto de la página'),
                                                   'required' => false))
            ->add('pdf', TextType::class, array('label' => $this->get('translator')->trans('Material complementario'),
                                                'required' => false))
            ->add('estatusContenido', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->getForm();

        $form->handleRequest($request);
       
        if ($request->getMethod() == 'POST')
        {

            $em->persist($pagina);
            $em->flush();

            if ($cantidad < $total)
            {
                $cantidad++;
                return $this->redirectToRoute('_editPagina', array('pagina_padre_id' => $pagina_padre_id,
                                                                   'pagina_id' => 0,
                                                                   'categoria_id' => $categoria_id,
                                                                   'estatus_contenido_id' => $estatus_contenido_id,
                                                                   'cantidad' => $cantidad,
                                                                   'total' => $total));
            }
            else {
                return $this->redirectToRoute('_pagina', array('pagina_id' => $pagina_padre_id ? $pagina_padre_id : $pagina->getId()));
            }
            
        }
        
        return $this->render('LinkBackendBundle:Pagina:edit.html.twig', array('form' => $form->createView(),
                                                                              'pagina' => $pagina,
                                                                              'cantidad' => $cantidad,
                                                                              'total' => $total));

    }

    public function newAction(Request $request){

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
        
        $pagina = new CertiPagina();
        $pagina->setFechaCreacion(new \DateTime('now'));

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $pagina->setUsuario($usuario);
        $pagina->setFechaModificacion(new \DateTime('now'));

        // Establecer el orden, último por defecto
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('LinkComunBundle:CertiPagina', 'p')
           ->where('p.pagina IS NULL')
           ->orderBy('p.orden', 'DESC');
        $query = $qb->getQuery();
        $paginas = $query->getResult();
        
        if ($paginas)
        {
            $orden = $paginas[0]->getOrden()+1;
        }
        else {
            $orden = 1;
        }

        $pagina->setOrden($orden);

        $form = $this->createFormBuilder($pagina)
            ->setAction($this->generateUrl('_newPagina'))
            ->setMethod('POST')
            ->add('nombre', TextType::class, array('label' => $this->get('translator')->trans('Nombre')))
            ->add('categoria', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiCategoria',
                                                        'choice_label' => 'nombre',
                                                        'expanded' => false,
                                                        'label' => $this->get('translator')->trans('Categoría'),
                                                        'placeholder' => ''))
            ->add('descripcion', TextareaType::class, array('label' => $this->get('translator')->trans('Descripción')))
            ->add('contenido', TextareaType::class, array('label' => $this->get('translator')->trans('Contenido')))
            ->add('foto', HiddenType::class, array('label' => $this->get('translator')->trans('Foto de la página'),
                                                   'required' => false))
            ->add('pdf', TextType::class, array('label' => $this->get('translator')->trans('Material complementario'),
                                                'required' => false))
            ->add('estatusContenido', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiEstatusContenido',
                                                               'choice_label' => 'nombre',
                                                               'expanded' => false,
                                                               'label' => $this->get('translator')->trans('Estatus')))
            ->getForm();

        $form->handleRequest($request);
       
        if ($request->getMethod() == 'POST')
        {

            $em->persist($pagina);
            $em->flush();

            $subpaginas = $request->request->get('subpaginas');
            $categoria_subpaginas = $request->request->get('categoria_subpaginas');
            $status_subpaginas = $request->request->get('status_subpaginas');

            if ($subpaginas > 0)
            {
                $cantidad = 1;
                $total = $subpaginas;
                return $this->redirectToRoute('_editPagina', array('pagina_padre_id' => $pagina->getId(),
                                                                   'pagina_id' => 0,
                                                                   'categoria_id' => $categoria_subpaginas,
                                                                   'estatus_contenido_id' => $status_subpaginas,
                                                                   'cantidad' => $cantidad,
                                                                   'total' => $total));
            }
            else {
                return $this->redirectToRoute('_pagina', array('pagina_id' => $pagina->getId()));
            }
            
        }

        $categorias = $em->getRepository('LinkComunBundle:CertiCategoria')->findAll();
        $status = $em->getRepository('LinkComunBundle:CertiEstatusContenido')->findAll();
        
        return $this->render('LinkBackendBundle:Pagina:new.html.twig', array('form' => $form->createView(),
                                                                             'categorias' => $categorias,
                                                                             'status' => $status));

    }

    public function ajaxGetPageAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $pagina_id = $request->query->get('pagina_id');
        
        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        
        $return = array('nombre' => $pagina->getNombre().' ('.$this->get('translator')->trans('Copia').')');
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTreePaginasAction($pagina_id, Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $paginas_asociadas = array(); // Solo para pasar un arreglo vacío en el segundo en parámetro
        $subPaginas = $f->subPaginas($pagina->getId(), $paginas_asociadas, 1);

        $return = array();

        if ($subPaginas['tiene'] > 0)
        {
            $return[] = array('text' => $pagina->getCategoria()->getNombre().': '.$pagina->getNombre(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right',
                              'children' => $subPaginas['return']);
        }
        else {
            $return[] = array('text' => $pagina->getCategoria()->getNombre().': '.$pagina->getNombre(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right');
        }

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDuplicatePageAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina_id = $request->request->get('pagina_id');
        $nombre = $request->request->get('nombre');

        // Llamada a la función de BD que duplica la página
        $query = $em->getConnection()->prepare('SELECT
                                                fnduplicar_pagina(:ppagina_id, :pnombre, :pusuario_id, :pfecha) as
                                                resultado;');
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pnombre', $nombre, \PDO::PARAM_STR);
        $query->bindValue(':pusuario_id', $session->get('usuario')['id'], \PDO::PARAM_INT);
        $query->bindValue(':pfecha', date('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        // La respuesta viene formada por Inserts__newIdPaginaPadre
        $r_arr = explode("__", $r[0]['resultado']);
        
        $return = array('id' => $r_arr[1],
                        'inserts' => $r_arr[0]);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function empresasPaginasAction($app_id)
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

        $empresas = array();
        
        // Todas las empresas 
        $empresas_bd = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        foreach ($empresas_bd as $empresa)
        {

            $query = $em->createQuery('SELECT COUNT(pe.id) FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                        WHERE pe.empresa = :empresa_id')
                        ->setParameter('empresa_id', $empresa->getId());
            $tiene_paginas = $query->getSingleScalarResult();

            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'rif' => $empresa->getRif(),
                                'tiene_paginas' => $tiene_paginas);

        }

        return $this->render('LinkBackendBundle:Pagina:empresasPaginas.html.twig', array('empresas' => $empresas));

    }

    public function ajaxPaginasEmpresaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $empresa_id = $request->query->get('empresa_id');

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        
        $query = $em->createQuery("SELECT pe, p FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                    JOIN pe.pagina p 
                                    WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                    ORDER BY p.orden ASC")
                    ->setParameter('empresa_id', $empresa_id);
        $pes = $query->getResult();

        $html = '<table class="table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Páginas').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Vencimiento').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Activo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Acceso').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Acciones').'</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pages">';

        foreach ($pes as $pe)
        {
            $activo = $pe->getActivo() ? 'checked' : '';
            $acceso = $pe->getAcceso() ? 'checked' : '';
            // Se verifica la página tiene sub-páginas
            $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:CertiPagina p 
                                        WHERE p.pagina = :pagina_id')
                        ->setParameter('pagina_id', $pe->getPagina()->getId());
            $tiene_subpaginas = $query->getSingleScalarResult();
            $html .= '<tr>
                        <td id="td-'.$pe->getId().'">&nbsp;</td>
                        <td>'.$pe->getFechaVencimiento()->format('d/m/Y').'</td>
                        <td class="center">
                            <div class="can-toggle demo-rebrand-2 small">
                                <input id="f_active'.$pe->getId().'" class="cb_activo" type="checkbox" '.$activo.'>
                                <label for="f_active'.$pe->getId().'">
                                    <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                                </label>
                            </div>
                        </td>
                        <td class="center">
                            <div class="can-toggle demo-rebrand-2 small">
                                <input id="f_access'.$pe->getId().'" class="cb_acceso" type="checkbox" '.$acceso.'>
                                <label for="f_access'.$pe->getId().'">
                                    <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                                </label>
                            </div>
                        </td>
                        <td class="center">';
            if ($tiene_subpaginas)
            {
                $html .= '<a href="'.$this->generateUrl('_asignarSubpaginas', array('empresa_id' => $pe->getEmpresa()->getId(), 'pagina_padre_id' => $pe->getPagina()->getId())).'" title="'.$this->get('translator')->trans('Asignar sub-páginas').'" class="btn btn-link btn-sm"><span class="fa fa-sitemap"></span></a>';
            }
            else {
                $html .= '&nbsp;';
            }
            $html .= '</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';

        $return = array('html' => $html,
                        'empresa' => $empresa->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTreePaginasEmpresaAction($pagina_empresa_id, Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->find($pagina_empresa_id);

        $subPaginas = $f->subPaginasEmpresa($pagina_empresa->getPagina()->getId(), $pagina_empresa->getEmpresa()->getId(), 1);

        $return = array();

        if ($subPaginas['tiene'] > 0)
        {
            $return[] = array('text' => $pagina_empresa->getPagina()->getCategoria()->getNombre().': '.$pagina_empresa->getPagina()->getNombre(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right',
                              'children' => $subPaginas['return']);
        }
        else {
            $return[] = array('text' => $pagina_empresa->getPagina()->getCategoria()->getNombre().': '.$pagina_empresa->getPagina()->getNombre(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right');
        }

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
