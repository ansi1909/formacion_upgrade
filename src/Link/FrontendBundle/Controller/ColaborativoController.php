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
                                          'empresa_id' => $session->get('empresa')['id']));
        $foros_bd = $query->getResult();

        $foros = array();

        foreach ($foros_bd as $foro)
        {
            $listar = 0;
            if ($foro->getFechaPublicacion()->format('Y-m-d') > date('Y-m-d') || $foro->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d'))
            {
                if ($session->get('usuario')['tutor'])
                {
                    $listar = 1;
                    if ($foro->getFechaPublicacion()->format('Y-m-d') > date('Y-m-d'))
                    {
                        $name_ft = $this->get('translator')->trans('Aún sin publicar');
                    }
                    else {
                        $name_ft = $this->get('translator')->trans('Vencido');
                    }
                    $coment_f = 0;
                    $coment_f_span = '';
                    $resp_ft = 0;
                }
            }
            else {
                $listar = 1;
                // Último comentario realizado sobre este tema
                $foro_hijo = $em->getRepository('LinkComunBundle:CertiForo')->findOneBy(array('foro' => $foro->getId()),
                                                                                        array('fechaRegistro' => 'DESC'));
                if (!$foro_hijo)
                {
                    $name_ft = $foro->getUsuario()->getNombre().' '.$foro->getUsuario()->getApellido();
                    $coment_f = 0;
                    $coment_f_span = '';
                    $resp_ft = 0;
                }
                else {
                    $query = $em->createQuery('SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f 
                                                WHERE f.foro = :foro_id')
                                ->setParameter('foro_id', $foro->getId());
                    $total_comentarios = $query->getSingleScalarResult();
                    $name_ft = $foro_hijo->getUsuario()->getNombre().' '.$foro_hijo->getUsuario()->getApellido();
                    $coment_f = 1;
                    $coment_f_span = $f->sinceTime($foro_hijo->getFechaPublicacion());
                    $resp_ft = $total_comentarios;
                }
            }
            if ($listar)
            {
                $publicacion = $foro->getFechaPublicacion()->format('d/m/Y');
                $vencimiento = $foro->getFechaVencimiento()->format('d/m/Y');
                $foros[] = array('id' => $foro->getId(),
                                 'tema' => $foro->getTema(),
                                 'name_ft' => $name_ft,
                                 'coment_f' => $coment_f,
                                 'coment_f_span' => $coment_f_span,
                                 'resp_ft' => $resp_ft,
                                 'publicacion' => $publicacion,
                                 'vencimiento' => $vencimiento,
                                 'usuario_id' => $foro->getUsuario()->getId());
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

    public function ajaxSaveForoAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();
        $html = '';

        $foro_id = $request->request->get('foro_id');
        $pagina_id = $request->request->get('pagina_id');
        $subpagina_id = $request->request->get('subpagina_id');
        $tema = $request->request->get('tema');
        $fechaPublicacion = $request->request->get('fechaPublicacion');
        $fechaVencimiento = $request->request->get('fechaVencimiento');
        $mensaje = $request->request->get('mensaje_content');

        if (!$foro_id)
        {

            $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            $foro = new CertiForo();
            $foro->setFechaRegistro(new \DateTime('now'));
            $foro->setPagina($pagina);
            $foro->setEmpresa($empresa);
            $foro->setPagina($pagina);
            $foro->setUsuario($usuario);

        }
        else {
            $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        }

        $foro->setTema($tema);
        $foro->setMensaje($mensaje);

        list($d, $m, $a) = explode("/", $fechaPublicacion);
        $fechaPublicacion = "$a-$m-$d";
        $foro->setFechaPublicacion(new \DateTime($fechaPublicacion));

        list($d, $m, $a) = explode("/", $fechaVencimiento);
        $fechaVencimiento = "$a-$m-$d";
        $foro->setFechaVencimiento(new \DateTime($fechaVencimiento));

        $em->persist($foro);
        $em->flush();

        // Generación de alarmas
        if (!$foro_id)
        {

            $descripcion = $usuario->getNombre().' '.$usuario->getApellido().' '.$this->get('translator')->trans('ha creado un tema en el espacio colaborativo del').' '.$pagina->getCategoria()->getNombre().' '.$pagina->getNombre().'.';

            $query = $em->createQuery("SELECT np FROM LinkComunBundle:CertiNivelPagina np 
                                        JOIN np.paginaEmpresa pe 
                                        WHERE pe.empresa = :empresa_id 
                                        AND pe.pagina = :pagina_id 
                                        ORDER BY np.id ASC")
                        ->setParameters(array('empresa_id' => $session->get('empresa')['id'],
                                              'pagina_id' => $pagina_id));
            $nivel_paginas = $query->getResult();

            $usuarios_id = array();
            $usuarios_arr = array();

            foreach ($nivel_paginas as $np)
            {
                $usuarios = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findByNivel($np->getNivel()->getId());
                foreach ($usuarios as $usuario_nivel)
                {
                    $query = $em->createQuery('SELECT COUNT(ru.id) FROM LinkComunBundle:AdminRolUsuario ru 
                                                WHERE ru.rol = :rol_id AND ru.usuario = :usuario_id')
                                ->setParameters(array('rol_id' => $yml['parameters']['rol']['participante'],
                                                      'usuario_id' => $usuario_nivel->getId()));
                    $participante = $query->getSingleScalarResult();
                    if (!in_array($usuario_nivel->getId(), $usuarios_id) && $participante && $usuario_nivel->getId() != $usuario->getId())
                    {
                        $usuarios_id[] = $usuario_nivel->getId();
                        $usuarios_arr[] = $usuario_nivel;
                    }
                }
            }

            foreach ($usuarios_arr as $usuario_nivel)
            {
                $f->newAlarm($yml['parameters']['tipo_alarma']['espacio_colaborativo'], $descripcion, $usuario_nivel, $foro->getId());
            }

        }

        $foro_hijo = $em->getRepository('LinkComunBundle:CertiForo')->findOneBy(array('foro' => $foro->getId()),
                                                                                array('fechaRegistro' => 'DESC'));

        if (!$foro_hijo)
        {
            $name_ft = $foro->getUsuario()->getNombre().' '.$foro->getUsuario()->getApellido();
            $coment_f = $this->get('translator')->trans('Fecha de Publicación');
            $coment_f_span = $foro->getFechaPublicacion()->format('d/m/Y');
            $resp_ft = 0;
        }
        else {
            $query = $em->createQuery('SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f 
                                        WHERE f.foro = :foro_id')
                        ->setParameter('foro_id', $foro->getId());
            $total_comentarios = $query->getSingleScalarResult();
            $name_ft = $foro_hijo->getUsuario()->getNombre().' '.$foro_hijo->getUsuario()->getApellido();
            $coment_f = $this->get('translator')->trans('Hizo un comentario');
            $coment_f_span = $f->sinceTime($foro_hijo->getFechaPublicacion());
            $resp_ft = $total_comentarios;
        }

        if (!$foro_id)
        {
            $html .= '<li class="f-table" id="liForo-'.$foro->getId().'">';
        }

        $class_tutor = $session->get('usuario')['tutor'] ? 'activar-destacado' : '';
        $html .= '<div class="row justify-content-between">
                    <div class="col-6">
                        <a href="'.$this->generateUrl('_detalleColaborativo', array('foro_id' => $foro->getId(), 'subpagina_id' => $subpagina_id)).'"><h5 class="titulo_f-table">'.$foro->getTema().'</h5></a>
                    </div>
                    <div class="col-2">
                        <div class="status_f-table '.$class_tutor.'">
                            <a href="#" class="newTopic" data="'.$foro->getId().'" id="aForo-'.$foro->getId().'"><span class="status_ft">'.$this->get('translator')->trans('Editar').'</span></a>
                        </div>
                    </div>
                </div>
                <div class="row align-items-end foo-esp_col">
                    <div class="col-auto">
                        <span class="name_ft">'.$foro->getUsuario()->getNombre().' '.$foro->getUsuario()->getApellido().'</span>
                    </div>
                    <div class="col-auto">
                        <span class="coment_f-table">'.$this->get('translator')->trans('Fecha de Publicación').': <span>'.$foro->getFechaPublicacion()->format('d/m/Y').'</span></span>
                    </div>';
        if ($resp_ft)
        {
            $html .= '<div class="col-auto">
                        <span class="resp_ft"><i class="material-icons ic-msg">message</i> '.$resp_ft.'</span>
                    </div>';
        }
        $html .= '</div>';

        if (!$foro_id)
        {
            $html .= '</li>';
        }

        $return = array('html' => $html,
                        'foro_id' => $foro->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxEditForoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        $foro_id = $request->query->get('foro_id');

        $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        
        $return = array('tema' => $foro->getTema(),
                        'fechaPublicacion' => $foro->getFechaPublicacion()->format('d/m/Y'),
                        'fechaVencimiento' => $foro->getFechaVencimiento()->format('d/m/Y'),
                        'fechaPublicacionF' => $foro->getFechaPublicacion()->format('Y-m-d'),
                        'fechaVencimientoF' => $foro->getFechaVencimiento()->format('Y-m-d'),
                        'mensaje' => $foro->getMensaje());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxSearchForoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        $term = $request->query->get('term');
        $foros = array();

        $qb = $em->createQueryBuilder();
        $qb->select('f')
           ->from('LinkComunBundle:CertiForo', 'f')
           ->where('f.foro IS NULL')
           ->andWhere('LOWER(f.tema) LIKE :term')
           ->setParameter('term', '%'.$term.'%');

        $query = $qb->getQuery();
        $foros_bd = $query->getResult();

        foreach ($foros_bd as $foro)
        {
            $foros[] = array('id' => $foro->getId(),
                             'label' => $foro->getTema(),
                             'value' => $foro->getTema());
        }

        $return = json_encode($foros);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
