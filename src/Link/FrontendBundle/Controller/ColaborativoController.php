<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiForo;
use Symfony\Component\Yaml\Yaml;

class ColaborativoController extends Controller
{
    public function indexAction($programa_id, $subpagina_id, Request $request)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("SELECT f FROM LinkComunBundle:CertiForo f 
                                    WHERE f.pagina = :programa_id 
                                    AND f.empresa = :empresa_id 
                                    AND f.foro IS NULL
                                    ORDER BY f.fechaPublicacion DESC")
                    ->setParameters(array('programa_id' => $programa_id,
                                          'usuario_id' => $session->get('empresa')['id']));
        $foros_bd = $query->getResult();

        $foros = array();

        foreach ($foros_bd as $foro)
        {
            if ($foro->getFechaPublicacion()->format('Y-m-d') >= date('Y-m-d') || $foro->getFechaVencimiento()->format('Y-m-d') <= date('Y-m-d'))
            {
                if ($session->get('usuario')['tutor'])
                {
                    if ($foro->getFechaPublicacion()->format('Y-m-d') >= date('Y-m-d'))
                    {
                        $name_ft = $this->get('translator')->trans('Aún sin publicar');
                        $coment_f = $this->get('translator')->trans('Fecha de Publicación');
                        $coment_f_span = $foro->getFechaPublicacion()->format('d/m/Y');
                    }
                    else {
                        $name_ft = $this->get('translator')->trans('Vencido');
                        $coment_f = $this->get('translator')->trans('Fecha de Vencimiento');
                        $coment_f_span = $foro->getFechaVencimiento()->format('d/m/Y');
                    }
                }
            }
        }

        // Indexado de páginas descomponiendo estructuras de páginas cada uno en su arreglo
        $indexedPages = $f->indexPages($session->get('paginas')[$programa_id]);

        // También se anexa a la indexación el programa padre
        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);
        $pagina = $session->get('paginas')[$programa_id];
        $pagina['padre'] = 0;
        $pagina['sobrinos'] = 0;
        $pagina['hijos'] = count($pagina['subpaginas']);
        $pagina['descripcion'] = $programa->getDescripcion();
        $pagina['contenido'] = $programa->getContenido();
        $pagina['foto'] = $programa->getFoto();
        $pagina['pdf'] = $programa->getPdf();
        $pagina['next_subpage'] = 0;
        $indexedPages[$pagina['id']] = $pagina;
        $espacio_colaborativo = $indexedPages[$programa_id]['espacio_colaborativo'];

        //return new Response(var_dump($indexedPages));

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($indexedPages, $session->get('paginas')[$programa_id], $subpagina_id, $this->generateUrl('_lecciones', array('programa_id' => $programa_id)), $session->get('usuario')['id'], $yml['parameters']['estatus_pagina']['completada']);

        return $this->render('LinkFrontendBundle:Colaborativo:index.html.twig', array('programa' => $programa,
                                                                                      'foros' => $foros,
                                                                                      'subpagina_id' => $subpagina_id,
                                                                                      'menu_str' => $menu_str,));

    }

}
