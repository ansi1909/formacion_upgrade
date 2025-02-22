<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiCategoria;
use Link\ComunBundle\Entity\CertiEstatusContenido;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Yaml\Yaml;

class PaginaController extends Controller
{
    public function indexAction($app_id)
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
        $paginas = $f->obtenerCursos();

        return $this->render('LinkBackendBundle:Pagina:index.html.twig', array('paginas' =>$paginas));

    }

    public function ajaxObtenerEstructuraAction(Request $request){
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pagina_id = $request->get('pagina_id');
        $array = json_decode($f->obtenerEstructuraJson($pagina_id),true);
        $html = $f->obtenerEstructuraHtml($array,$yml);
        $return = json_encode(['html' => $html]);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
    public function paginaAction($pagina_id)
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
        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findByPagina($pagina_id);
        $subpaginas = $f->paginas($subpages);

        return $this->render('LinkBackendBundle:Pagina:pagina.html.twig', array('pagina' => $pagina,
                                                                                'subpaginas' => $subpaginas));

    }

    public function editAction($pagina_padre_id, $pagina_id, $categoria_id, $estatus_contenido_id, $cantidad, $total, Request $request){

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
                                                        'label' => $this->get('translator')->trans('Categoría')))
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

        if (!$pagina->getPagina())
        {
            $form->add('encuesta', TextType::class, array('label' => $this->get('translator')->trans('Enlace de la encuesta'),
                                                          'required' => false))
                 ->add('horasAcademicas', IntegerType::class, array('label' => $this->get('translator')->trans('Horas académicas')));
        }

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
            ->add('horasAcademicas', IntegerType::class, array('label' => $this->get('translator')->trans('Horas académicas')))
            ->add('puntuacion', IntegerType::class, array('label' => $this->get('translator')->trans('puntuacion')))
            ->add('categoria', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\CertiCategoria',
                                                        'choice_label' => 'nombre',
                                                        'expanded' => false,
                                                        'label' => $this->get('translator')->trans('Categoría')))
            ->add('descripcion', TextareaType::class, array('label' => $this->get('translator')->trans('Descripción')))
            ->add('contenido', TextareaType::class, array('label' => $this->get('translator')->trans('Contenido'),
                                                          'required' => false))
            ->add('foto', HiddenType::class, array('label' => $this->get('translator')->trans('Foto de la página'),
                                                   'required' => false))
            ->add('pdf', TextType::class, array('label' => $this->get('translator')->trans('Material complementario'),
                                                'required' => false))
            ->add('encuesta', TextType::class, array('label' => $this->get('translator')->trans('Enlace de la encuesta'),
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
                                                                             'status' => $status,
                                                                             'pagina' => $pagina));

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
        $evaluacion = $request->request->get('duplica_evaluacion');
        $evaluacion = $evaluacion ? 1: 0;


        $return = $f->duplicarPagina($pagina_id, $nombre, $session->get('usuario')['id'],$evaluacion);

        /*$return = array('id' => $r_arr[1],
                        'inserts' => $r_arr[0]);*/

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function empresasPaginasAction($app_id)
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
                                    ORDER BY pe.orden ASC")
                    ->setParameter('empresa_id', $empresa_id);
        $pes = $query->getResult();

        $html = '<table class="table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Páginas').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Vencimiento').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Orden').'</th>
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
            $html .= '<tr id="tr-'.$pe->getId().'">
                        <td id="td-'.$pe->getId().'">'.$pe->getPagina()->getCategoria()->getNombre().': '.$pe->getPagina()->getNombre().'</td>
                        <td>'.$pe->getFechaVencimiento()->format('d/m/Y').'</td>
                        <td>'.$pe->getOrden().'<input type="hidden" name="orden-'.$pe->getId().'" id="orden-'.$pe->getId().'" value="'.$pe->getOrden().'"></td>
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
                $html .= '<a href="'.$this->generateUrl('_asignarSubpaginas', array('empresa_id' => $pe->getEmpresa()->getId(), 'pagina_padre_id' => $pe->getPagina()->getId())).'" title="'.$this->get('translator')->trans('Configurar asignación de sub-páginas').'" class="btn btn-link btn-sm"><span class="fa fa-sitemap"></span></a>';
            }
            $html .= '<a href="'.$this->generateUrl('_editAsignacion', array('pagina_empresa_id' => $pe->getId())).'" title="'.$this->get('translator')->trans('Editar asignación').'" class="btn btn-link btn-sm"><span class="fa fa-pencil"></span></a>';

            $html.= '<a href="'.'#'.'" title="'.$this->get('translator')->trans('Editar orden').'" class="btn btn-link btn-sm orden" id="'.$pe->getId().'"><span class="fa fa-list-ol"></span></a>';
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

    public function ajaxEditarOrdenAction(Request $request){
        $session = new Session();
        $f = $this->get('funciones');
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
         $em = $this->getDoctrine()->getManager();
         $pagina_empresa_id = $request->request->get('pagina_empresa_id');
         $pagina_empresa_orden = $request->request->get('input_orden');

         $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->find($pagina_empresa_id);

         $pagina_empresa->setOrden($pagina_empresa_orden);
         $em->persist($pagina_empresa);
         $em->flush();
         $return = array(
            'ok'=>1
         );
         $return = json_encode($return);
         return new Response($return, 200, array('Content-Type' => 'application/json'));

        }

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

    public function empresaPaginasAction($empresa_id, Request $request)
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

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if ($request->getMethod() == 'POST')
        {

            // Se guardan las páginas seleccionadas
            $asignaciones = $request->request->get('asignar') ? $request->request->get('asignar') : array();
            $activaciones = $request->request->get('activar') ? $request->request->get('activar') : array();
            $accesos = $request->request->get('acceso') ? $request->request->get('acceso') : array();
            $orden = 0;

            foreach ($asignaciones as $pagina_id)
            {

                $orden++;
                $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
                $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $pagina_id,
                                                                                                            'empresa' => $empresa_id));

                if (!$pagina_empresa)
                {
                    // Asignación de página padre
                    $pagina_empresa = new CertiPaginaEmpresa();
                    $pagina_empresa->setEmpresa($empresa);
                    $pagina_empresa->setPagina($pagina);
                    $date = new \DateTime();
                    //$date->modify('next monday');
                    //$next_monday = $date->format('Y-m-d');
                    $date->modify('+1 year');
                    $next_year = $date->format('Y-m-d');
                    $pagina_empresa->setFechaInicio(new \DateTime('now'));
                    $pagina_empresa->setFechaVencimiento(new \DateTime($next_year)); // Fecha de vencimiento un año después
                    $pagina_empresa->setActivo(!in_array($pagina_id, $activaciones) ? false : true);
                    $pagina_empresa->setAcceso(!in_array($pagina_id, $accesos) ? false : true);
                    $pagina_empresa->setOrden($orden);
                    $em->persist($pagina_empresa);
                    $em->flush();

                    // Asignación de sub-páginas
                    $f->asignacionSubPaginas($pagina_empresa, $yml);

                }
                else {

                    // Solo actualizamos las páginas padres
                    $pagina_empresa->setActivo(!in_array($pagina_id, $activaciones) ? false : true);
                    $pagina_empresa->setAcceso(!in_array($pagina_id, $accesos) ? false : true);
                    $pagina_empresa->setOrden($orden);
                    $em->persist($pagina_empresa);
                    $em->flush();

                    // Asignación de sub-páginas, en caso de que existan nuevas sub'páginas
                    $f->asignacionSubPaginas($pagina_empresa, $yml);

                }

            }

            return $this->redirectToRoute('_showAsignacion', array('empresa_id' => $empresa_id,
                                                                   'pagina_id' => 0));

        }
        else {

            $paginas = array();
            $i = 0;

            // Todas las páginas padres activas
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                        WHERE p.pagina IS NULL AND p.estatusContenido = :activo
                                        ORDER BY p.orden ASC")
                        ->setParameter('activo', $yml['parameters']['estatus_contenido']['activo']);
            $pages = $query->getResult();

            foreach ($pages as $page)
            {

                $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $page->getId(),
                                                                                                            'empresa' => $empresa_id));

                $paginas[] = array('id' => $page->getId(),
                                   'nombre' => $page->getNombre(),
                                   'asignada' => $pagina_empresa ? 1 : 0,
                                   'categoria'=> $page->getCategoria()->getNombre(),
                                   'activar' => $pagina_empresa ? $pagina_empresa->getActivo() ? true : false : true,
                                   'acceso' => $pagina_empresa ? $pagina_empresa->getAcceso() ? true : false : true);

            }

            return $this->render('LinkBackendBundle:Pagina:empresaPaginas.html.twig', array('empresa' => $empresa,
                                                                                            'paginas' => $paginas));

        }

    }

    public function showAsignacionAction($empresa_id, $pagina_id, Request $request)
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

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        // Páginas asignadas
        $qb = $em->createQueryBuilder();
        $qb->select('pe')
           ->from('LinkComunBundle:CertiPaginaEmpresa', 'pe')
           ->leftJoin('pe.pagina', 'p')
           ->andWhere('pe.empresa = :empresa_id')
           ->orderBy('pe.orden', 'ASC');
        $parametros['empresa_id'] = $empresa_id;
        if ($pagina_id)
        {

            $qb->andWhere('p.pagina = :pagina_id');
            $parametros['pagina_id'] = $pagina_id;

            // Página padre asignada
            $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $pagina_padre_id = $pagina->getPagina() ? $pagina->getPagina()->getId() : 0;

        }
        else {
            $qb->andWhere('p.pagina IS NULL');
            $pagina = new CertiPagina();
            $pagina_padre_id = 0;
        }
        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $pes = $query->getResult();

        $asignaciones = array();

        foreach ($pes as $pe)
        {

            // Permisos
            $check_activo = $pe->getActivo() ? ' <span class="fa fa-check"></span>' : '';
            $check_acceso = $pe->getAcceso() ? ' <span class="fa fa-check"></span>' : '';
            $check_muro = $pe->getMuroActivo() ? ' <span class="fa fa-check"></span>' : '';
            $check_prueba = $pe->getPruebaActiva() ? ' <span class="fa fa-check"></span>' : '';
            $check_colaborativo = $pe->getColaborativo() ? ' <span class="fa fa-check"></span>' : '';
            if (!$pe->getPagina()->getPagina()){
                $check_ranking = $pe->getRanking()? ' <span class="fa fa-check"></span>':'';
            }
            
            $permisos = '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="activo'.$pe->getPagina()->getId().'" p_str="'.$this->get('translator')->trans('Asignación habilitada').'">'.$this->get('translator')->trans('Activo').$check_activo.'</li>';
            $permisos .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="acceso'.$pe->getPagina()->getId().'" p_str="'.$this->get('translator')->trans('Acceso a la página').'">'.$this->get('translator')->trans('Acceso').$check_acceso.'</li>';
            $permisos .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="muro'.$pe->getPagina()->getId().'" p_str="'.$this->get('translator')->trans('Tiene muro').'">'.$this->get('translator')->trans('Muro').$check_muro.'</li>';
            $permisos .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="colaborativo'.$pe->getPagina()->getId().'" p_str="'.$this->get('translator')->trans('Espacio colaborativo').'">'.$this->get('translator')->trans('Espacio colaborativo').$check_colaborativo.'</li>';
            $permisos .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="prueba'.$pe->getPagina()->getId().'" p_str="'.$this->get('translator')->trans('Evaluación activa').'">'.$this->get('translator')->trans('Evaluación').$check_prueba.'</li>';
            if (!$pe->getPagina()->getPagina()){
                $permisos .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="ranking'.$pe->getPagina()->getId().'" p_str="'.$this->get('translator')->trans('Ranking activo').'">'.$this->get('translator')->trans('Ranking').$check_ranking.'</li>';
            }
            

            $asignaciones[] = array('id' => $pe->getId(),
                                    'pagina_id' => $pe->getPagina()->getId(),
                                    'pagina' => $pe->getPagina()->getCategoria()->getNombre().': '.$pe->getPagina()->getNombre(),
                                    'prelacion' => $pe->getPrelacion() ? $this->get('translator')->trans('Prelada por').': '.$pe->getPrelacion()->getNombre() : 0,
                                    'permisos' => $permisos,
                                    'inicio' => $pe->getFechaInicio()->format('d/m/Y'),
                                    'vencimiento' => $pe->getFechaVencimiento()->format('d/m/Y'));


        }
        //return new response (var_dump($asignaciones));
        return $this->render('LinkBackendBundle:Pagina:showAsignacion.html.twig', array('empresa' => $empresa,
                                                                                        'asignaciones' => $asignaciones,
                                                                                        'pagina' => $pagina,
                                                                                        'pagina_padre_id' => $pagina_padre_id));

    }

    public function editAsignacionAction($pagina_empresa_id, Request $request)
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

        $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->find($pagina_empresa_id);

        if ($request->getMethod() == 'POST')
        {

            $activo = $request->request->get('activo');
            $acceso = $request->request->get('acceso');
            $muro = $request->request->get('muro');
            $applyMuro = $request->request->get('applyMuro');
            $colaborativo = $request->request->get('colaborativo');
            $fechaInicio = $request->request->get('fechaInicio');
            $fechaVencimiento = $request->request->get('fechaVencimiento');
            $apply = $request->request->get('apply');
            $evaluacion = $request->request->get('evaluacion');
            $puntajeAprueba = $request->request->get('puntajeAprueba');
            $maxIntentos = $request->request->get('maxIntentos');
            $prelacion = $request->request->get('prelacion');
            $colaborativoSubp = $request->request->get('colaborativo_subpaginas');
            $ranking = $request->request->get('ranking');

            // Reformateo de fecha de inicio
            $fi = explode("/", $fechaInicio);
            $fechaInicio = $fi[2].'-'.$fi[1].'-'.$fi[0];

            // Reformateo de fecha de vencimiento
            $fv = explode("/", $fechaVencimiento);
            $fechaVencimiento = $fv[2].'-'.$fv[1].'-'.$fv[0];

            if ($prelacion)
            {
                $pagina_prelacion = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($prelacion);
                $pagina_empresa->setPrelacion($pagina_prelacion);
            }
            else {
                $pagina_empresa->setPrelacion(null);
            }

            $pagina_empresa->setActivo($activo ? true : false);
            $pagina_empresa->setFechaInicio(new \DateTime($fechaInicio));
            $pagina_empresa->setFechaVencimiento(new \DateTime($fechaVencimiento));
            $pagina_empresa->setPruebaActiva($evaluacion ? true : false);
            $pagina_empresa->setMaxIntentos($maxIntentos ? $maxIntentos : null);
            $pagina_empresa->setPuntajeAprueba($puntajeAprueba ? $puntajeAprueba : null);
            $pagina_empresa->setMuroActivo($muro ? true : false);
            $pagina_empresa->setAcceso($acceso ? true : false);
            $pagina_empresa->setColaborativo($colaborativo ? true : false);
            $pagina_empresa->setRanking($ranking ? true : false);
            $em->persist($pagina_empresa);
            $em->flush();

            // Si apply es true se setean la fecha de inicio y de vencimiento para las sub-páginas
            $onlyDates = $apply ? 1 : 0;

            // Si applyMuro es true se activa el muro para las sub-páginas
            $onlyMuro = $applyMuro ? 1 : 0;

            // colaborativoSubp es true activa el colaborativo para las sub paginas
            $onlyColaborativo = $colaborativoSubp ? 1 : 0;
            //return new response (var_dump($estructura));

            $f->asignacionSubPaginas($pagina_empresa, $yml, $onlyDates, $onlyMuro, $onlyColaborativo);

            //asginar evaluaciones
            $estructura = $f->obtenerEstructura($pagina_empresa->getPagina()->getId(),$yml);
            foreach ($estructura as $pagina ) {
                //buscar si existe prueba activa
                 $prueba = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $pagina));
                 if($prueba && $prueba->getEstatusContenido()->getId() == $yml['parameters']['estatus_contenido']['activo']){
                    $certi_pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $pagina,'empresa'=>$pagina_empresa->getEmpresa()->getId()));
                    $certi_pagina_empresa->setPruebaActiva(true);
                    $certi_pagina_empresa->setPuntajeAprueba((int)$yml['parameters']['evaluaciones']['puntaje_minimo']);
                    $certi_pagina_empresa->setMaxIntentos($yml['parameters']['evaluaciones']['intentos']);
                    $em->persist($certi_pagina_empresa);
                    $em->flush();
                 }

            }

            return $this->redirectToRoute('_showAsignacion', array('empresa_id' => $pagina_empresa->getEmpresa()->getId(),
                                                                   'pagina_id' => $pagina_empresa->getPagina() ? $pagina_empresa->getPagina()->getId() : 0));

        }

        // Prueba activa
        $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $pagina_empresa->getPagina()->getId(),
                                                                                     'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']));

        // Páginas hermanas para la prelación
        $prelaciones = array();
        $qb = $em->createQueryBuilder();
        $qb->select('pe')
           ->from('LinkComunBundle:CertiPaginaEmpresa', 'pe')
           ->leftJoin('pe.pagina', 'p')
           ->andWhere('pe.empresa = :empresa_id')
           ->andWhere('p.id != :pagina_id')
           ->orderBy('pe.orden', 'ASC');
        $parametros['empresa_id'] = $pagina_empresa->getEmpresa()->getId();
        $parametros['pagina_id'] = $pagina_empresa->getPagina()->getId();
        if ($pagina_empresa->getPagina()->getPagina())
        {
            $qb->andWhere('p.pagina = :pagina_hermana_id');
            $parametros['pagina_hermana_id'] = $pagina_empresa->getPagina()->getPagina()->getId();
        }
        else {
            $qb->andWhere('p.pagina IS NULL');
        }
        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $pes = $query->getResult();

        foreach ($pes as $pe)
        {
            $prelaciones[] = array('id' => $pe->getPagina()->getId(),
                                   'nombre' => $pe->getPagina()->getNombre());
        }
        // return new response(var_dump($pagina_empresa->getPagina()->getId()));
        $estructura = $f->obtenerEstructura($pagina_empresa->getPagina()->getId(),$yml);
        //return new response(var_dump($estructura));
        $status = $f->statusChecksHerencia($pagina_empresa,$estructura);
        //return new response(var_dump($status));

        return $this->render('LinkBackendBundle:Pagina:editAsignacion.html.twig', array('pagina_empresa' => $pagina_empresa,
                                                                                        'prueba' => $prueba,
                                                                                        'days_ago' => $f->timeAgo($pagina_empresa->getEmpresa()->getFechaCreacion()->format('Y-m-d H:i:s')),
                                                                                        'prelaciones' => $prelaciones,
                                                                                        'status'=>$status));

    }

    public function ajaxAccesoPaginaAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $pagina_empresa_id = $request->request->get('pagina_empresa_id');
        $checked = $request->request->get('checked');

        $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->find($pagina_empresa_id);
        $pagina_empresa->setAcceso($checked ? true : false);
        $em->persist($pagina_empresa);
        $em->flush();

        $return = array('id' => $pagina_empresa->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function asignarSubpaginasAction($empresa_id, $pagina_padre_id, Request $request)
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

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_padre_id);

        if ($request->getMethod() == 'POST')
        {

            // Se guardan las páginas seleccionadas
            $asignaciones = $request->request->get('asignar') ? $request->request->get('asignar') : array();
            $activaciones = $request->request->get('activar') ? $request->request->get('activar') : array();
            $accesos = $request->request->get('acceso') ? $request->request->get('acceso') : array();
            $orden = 0;

            foreach ($asignaciones as $pagina_id)
            {

                $orden++;
                $p = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
                $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $pagina_id,
                                                                                                            'empresa' => $empresa_id));

                if (!$pagina_empresa)
                {
                    // Asignación de página padre
                    $pagina_empresa = new CertiPaginaEmpresa();
                    $pagina_empresa->setEmpresa($empresa);
                    $pagina_empresa->setPagina($p);
                    $date = new \DateTime();
                    //$date->modify('next monday');
                    //$next_monday = $date->format('Y-m-d');
                    $date->modify('+1 year');
                    $next_year = $date->format('Y-m-d');
                    $pagina_empresa->setFechaInicio(new \DateTime('now'));
                    $pagina_empresa->setFechaVencimiento(new \DateTime($next_year)); // Fecha de vencimiento un año después
                    $pagina_empresa->setActivo(!in_array($pagina_id, $activaciones) ? false : true);
                    $pagina_empresa->setAcceso(!in_array($pagina_id, $accesos) ? false : true);
                    $pagina_empresa->setOrden($orden);
                    $em->persist($pagina_empresa);
                    $em->flush();

                    // Asignación de sub-páginas
                    $f->asignacionSubPaginas($pagina_empresa, $yml);

                }
                else {

                    // Solo actualizamos las páginas padres
                    $pagina_empresa->setActivo(!in_array($pagina_id, $activaciones) ? false : true);
                    $pagina_empresa->setAcceso(!in_array($pagina_id, $accesos) ? false : true);
                    $pagina_empresa->setOrden($orden);
                    $em->persist($pagina_empresa);
                    $em->flush();

                }

            }

            return $this->redirectToRoute('_showAsignacion', array('empresa_id' => $empresa_id,
                                                                   'pagina_id' => $pagina_padre_id));

        }
        else {

            $paginas = array();
            $i = 0;

            // Todas las sub-páginas activas
            $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                        WHERE p.pagina = :pagina_id AND p.estatusContenido = :activo
                                        ORDER BY p.orden ASC")
                        ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
                                              'pagina_id' => $pagina_padre_id));
            $pages = $query->getResult();

            foreach ($pages as $page)
            {

                $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $page->getId(),
                                                                                                            'empresa' => $empresa_id));

                $paginas[] = array('id' => $page->getId(),
                                   'nombre' => $page->getNombre(),
                                   'asignada' => $pagina_empresa ? 1 : 0,
                                   'activar' => $pagina_empresa ? $pagina_empresa->getActivo() ? true : false : true,
                                   'acceso' => $pagina_empresa ? $pagina_empresa->getAcceso() ? true : false : true);

            }

            return $this->render('LinkBackendBundle:Pagina:asignarSubpaginas.html.twig', array('empresa' => $empresa,
                                                                                               'paginas' => $paginas,
                                                                                               'pagina' => $pagina));

        }

    }

    public function moverAction($pagina_id, Request $request)
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

        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        if ($request->getMethod() == 'POST')
        {

            $pagina_padre_id = $request->request->get('pagina_padre_id');
            $pagina_padre = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_padre_id);

            // Reordenar su anterior grupo
            if ($pagina->getPagina())
            {
                $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                            WHERE p.pagina = :pagina_id
                                            AND p.id != :id
                                            ORDER BY p.orden ASC")
                            ->setParameters(array('pagina_id' => $pagina->getPagina()->getId(),
                                                  'id' => $pagina_id));
            }
            else {
                $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p
                                            WHERE p.pagina IS NULL
                                            AND p.id != :id
                                            ORDER BY  p.orden ASC")
                            ->setParameter('id', $pagina_id);
            }
            $paginas = $query->getResult();

            $orden = 0;
            foreach ($paginas as $p)
            {
                $orden++;
                $p->setOrden($orden);
                $em->persist($p);
                $em->flush();
            }

            // Quedará de último en el orden
            $query = $em->createQuery('SELECT MAX(p.orden) FROM LinkComunBundle:CertiPagina p
                                        WHERE p.pagina = :pagina_id')
                        ->setParameter('pagina_id', $pagina_padre_id);
            $orden = $query->getSingleScalarResult();
            $orden++;

            $pagina->setPagina($pagina_padre);
            $pagina->setOrden($orden);
            $em->persist($pagina);
            $em->flush();

            $programa_id = $f->paginaRaiz($pagina_padre);

            return $this->redirectToRoute('_paginaMovida', array('pagina_id' => $pagina->getId(), 'programa_id' => $programa_id));

        }

        // Páginas hermanas
        $str = '';

        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('LinkComunBundle:CertiPagina', 'p')
           ->andWhere('p.id != :me')
           ->andWhere('p.pagina IS NULL')
           ->orderBy('p.orden', 'ASC');
        $parametros['me'] = $pagina_id;
        $qb->setParameters($parametros);
        $query = $qb->getQuery();
        $pages = $query->getResult();

        $movimiento = array('pagina_id' => $pagina_id);
        $paginas_asociadas = array();
        foreach ($pages as $page)
        {
            $str .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$page->getId().'" p_str="'.$page->getCategoria()->getNombre().': '.$page->getNombre().'">'.$page->getCategoria()->getNombre().': '.$page->getNombre();
            $subPaginas = $f->subPaginas($page->getId(), $paginas_asociadas, 0, $movimiento);
            if ($subPaginas['tiene'] > 0)
            {
                $str .= '<ul>';
                $str .= $subPaginas['return'];
                $str .= '</ul>';
            }
            $str .= '</li>';
        }

        return $this->render('LinkBackendBundle:Pagina:mover.html.twig', array('pagina' => $pagina,
                                                                               'pagina_str' => $str));

    }

    public function paginaMovidaAction($pagina_id, $programa_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();

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

        $programa = $em->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $paginas_asociadas[] = $pagina_id;

        $str = '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$programa->getId().'" p_str="'.$programa->getCategoria()->getNombre().': '.$programa->getNombre().'">'.$programa->getCategoria()->getNombre().': '.$programa->getNombre();
        $subPaginas = $f->subPaginas($programa->getId(), $paginas_asociadas);
        if ($subPaginas['tiene'] > 0)
        {
            $str .= '<ul>';
            $str .= $subPaginas['return'];
            $str .= '</ul>';
        }
        $str .= '</li>';

        return $this->render('LinkBackendBundle:Pagina:paginaMovida.html.twig', array('pagina_str' => $str));

    }

}
