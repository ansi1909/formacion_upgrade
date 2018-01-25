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

}
