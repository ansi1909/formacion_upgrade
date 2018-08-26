<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNotificacion;
use Link\ComunBundle\Entity\AdminTipoNotificacion;
use Link\ComunBundle\Entity\AdminNotificacionProgramada;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Yaml\Yaml;

class NotificacionController extends Controller
{
    public function indexAction($app_id, Request $request)
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
        $notificaciones = array();
        
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        if ($usuario->getEmpresa()) 
        {
            $notificacionesdb = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->findByEmpresa($usuario->getEmpresa());
        }
        else {

            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findByActivo(true); 
            
            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNotificacion n
                                       JOIN n.empresa e 
                                       WHERE e.activo = :activo 
                                       ORDER BY n.id ASC")
                         ->setParameter('activo', true);
            $notificacionesdb = $query->getResult();

        }

        foreach ($notificacionesdb as $notificacion)
        {
            $notificaciones[] = array('id' => $notificacion->getId(),
                                      'asunto' => $notificacion->getAsunto(),
                                      'empresa' => $notificacion->getEmpresa()->getNombre(),
                                      'tipo_notificacion' => $notificacion->getTipoNotificacion()->getNombre(),
                                      'delete_disabled' => $f->linkEliminar($notificacion->getId(),'AdminNotificacion'));
        }

        return $this->render('LinkBackendBundle:Notificacion:index.html.twig', array('empresas' => $empresas,
                                                                                     'notificaciones' => $notificaciones,
                                                                                     'usuario' => $usuario));

    }

    public function ajaxNotificacionesAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $empresa_id = $request->query->get('empresa_id');

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 
        $notificaciones = array();

        $qb = $em->createQueryBuilder();

        if ($empresa_id)
        {
            $qb->select('u')
               ->from('LinkComunBundle:AdminNotificacion', 'u');
            $qb->andWhere('u.empresa = :empresa_id');
            $parametros['empresa_id'] = $empresa_id;
            $qb->setParameters($parametros);
        }
        else{
            $qb->select('u')
               ->from('LinkComunBundle:AdminNotificacion', 'u');
        }

        $query = $qb->getQuery();
        $notificacionesdb = $query->getResult();

        foreach ($notificacionesdb as $notificacion)
        {
            $notificaciones[] = array('id' => $notificacion->getId(),
                                      'asunto' => $notificacion->getAsunto(),
                                      'empresa' => $notificacion->getEmpresa()->getNombre(),
                                      'tipo_notificacion' => $notificacion->getTipoNotificacion()->getNombre(),
                                      'delete_disabled' => $f->linkEliminar($notificacion->getId(),'AdminNotificacion'));
        }

        $html = $this->renderView('LinkBackendBundle:Notificacion:notificaciones.html.twig', array('notificaciones' => $notificaciones,
                                                                                                   'usuario' => $usuario));

        $return = array('html' => $html);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function showAction(Request $request, $notificacion_id, $save)
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

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);

        return $this->render('LinkBackendBundle:Notificacion:show.html.twig', array('notificacion' => $notificacion,
                                                                                    'usuario' => $usuario,
                                                                                    'save' => $save));

    }

    public function editAction(Request $request, $notificacion_id)
    {
                
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

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

        if ($notificacion_id)
        {
            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        }
        else {
            $notificacion = new AdminNotificacion();
        }
        
        $notificacion->setUsuario($usuario);

        $form = $this->createFormBuilder($notificacion)
                     ->setAction($this->generateUrl('_editNotificacion', array('notificacion_id' => $notificacion_id)))
                     ->setMethod('POST')
                     ->add('asunto', TextType::class, array('label' => $this->get('translator')->trans('Asunto')))
                     ->add('mensaje', TextareaType::class, array('label' => $this->get('translator')->trans('Mensaje')))
                     ->add('tipoNotificacion', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminTipoNotificacion',
                                                                        'choice_label' => 'nombre',
                                                                        'expanded' => false,
                                                                        'label' => $this->get('translator')->trans('Tipo de notificación'),
                                                                        'placeholder' => ''))
                     ->getForm();

        if ($usuario->getEmpresa()) {
            $notificacion->setEmpresa($usuario->getEmpresa());            
        }
        else {
            $form->add('empresa', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminEmpresa',
                                                           'choice_label' => 'nombre',
                                                           'expanded' => false,
                                                           'label' => $this->get('translator')->trans('Empresa'),
                                                           'placeholder' => '',
                                                           'query_builder' => function(EntityRepository $er){
                                                                return $er->createQueryBuilder('e')
                                                                          ->where('e.activo = ?1')
                                                                          ->setParameter(1, true)
                                                                          ->orderBy('e.nombre', 'ASC');
                                                         }));
        }

        $form->handleRequest($request);
        
        if ($request->getMethod() == 'POST')
        {

            $em->persist($notificacion);
            $em->flush();

            return $this->redirectToRoute('_showNotificacion', array('notificacion_id' => $notificacion->getId(), 'save'=> 1));
            
        }
        
        return $this->render('LinkBackendBundle:Notificacion:edit.html.twig', array('form' => $form->createView(),
                                                                                    'notificacion' => $notificacion,
                                                                                    'usuario' => $usuario));
        
    }

    public function programadosAction($app_id)
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

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $notificaciones = array();
        
        if ($usuario->getEmpresa()) 
        {
            $notificacionesdb = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->findByEmpresa($usuario->getEmpresa());
        }
        else {
            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNotificacion n
                                       JOIN n.empresa e 
                                       WHERE e.activo = :activo 
                                       ORDER BY n.id ASC")
                         ->setParameter('activo', true);
            $notificacionesdb = $query->getResult();
        }

        foreach ($notificacionesdb as $notificacion)
        {

            $query = $em->createQuery('SELECT COUNT(np.id) FROM LinkComunBundle:AdminNotificacionProgramada np 
                                        WHERE np.notificacion = :notificacion_id')
                        ->setParameter('notificacion_id', $notificacion->getId());
            $tiene_programados = $query->getSingleScalarResult();

            $notificaciones[] = array('id' => $notificacion->getId(),
                                      'asunto' => $notificacion->getAsunto(),
                                      'empresa' => $notificacion->getEmpresa()->getNombre(),
                                      'tipo' => $notificacion->getTipoNotificacion()->getNombre(),
                                      'tiene_programados' => $tiene_programados);
        }

        return $this->render('LinkBackendBundle:Notificacion:programados.html.twig', array('notificaciones' => $notificaciones,
                                                                                           'usuario' => $usuario));

    }

    public function ajaxProgramadosAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $notificacion_id = $request->query->get('notificacion_id');

        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        
        $query = $em->createQuery("SELECT np FROM LinkComunBundle:AdminNotificacionProgramada np 
                                    WHERE np.notificacion = :notificacion_id AND np.grupo IS NULL 
                                    ORDER BY np.fechaDifusion ASC")
                    ->setParameter('notificacion_id', $notificacion_id);
        $nps = $query->getResult();

        $html = $this->renderView('LinkBackendBundle:Notificacion:notificacionesProgramadas.html.twig', array('nps' => $nps));

        $return = array('html' => $html,
                        'notificacion' => $notificacion->getAsunto());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTreeGrupoProgramadoAction($notificacion_programada_id, Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $notificacion_programada = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

        $return = array();
        switch ($notificacion_programada->getTipoDestino()->getId())
        {
            case $yml['parameters']['tipo_destino']['todos']:
                $return[] = array('text' => 'N/A',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['nivel']:
                $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($notificacion_programada->getEntidadId());
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['programa']:
                $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['grupo']:
                $nps = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                foreach ($nps as $np)
                {
                    $participante = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($np->getEntidadId());
                    $return[] = array('text' => $participante->getNombre().' '.$participante->getApellido(),
                                      'state' => array('opened' => true),
                                      'icon' => 'fa fa-angle-double-right');
                }
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado']:
                $return[] = array('text' => 'N/A',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado_programa']:
                $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['aprobados']:
                $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
        }

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function editNotificacionProgramadaAction(Request $request, $notificacion_id, $notificacion_programada_id)
    {
                
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
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

        if ($notificacion_programada_id)
        {

            $notificacion_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

            switch ($notificacion_programada->getTipoDestino()->getId())
            {
                case $yml['parameters']['tipo_destino']['todos']:
                    $entidades = array('tipo' => 'text',
                                       'multiple' => false,
                                       'valor' => $this->get('translator')->trans('Todos los empleados de la empresa').' '.$notificacion_programada->getNotificacion()->getEmpresa()->getNombre());
                    break;
                case $yml['parameters']['tipo_destino']['nivel']:
                    $niveles = $em->getRepository('LinkComunBundle:AdminNivel')->findByEmpresa($notificacion_programada->getNotificacion()->getEmpresa()->getId());
                    $valores = array();
                    foreach ($niveles as $nivel)
                    {
                        $valores[] = array('id' => $nivel->getId(),
                                           'nombre' => $nivel->getNombre(),
                                           'selected' => $nivel->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '');
                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => false,
                                       'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['programa']:
                    $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                                JOIN pe.pagina p 
                                                WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                                ORDER BY pe.orden ASC")
                                ->setParameter('empresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId());
                    $pes = $query->getResult();
                    $valores = array();
                    foreach ($pes as $pe)
                    {
                        $valores[] = array('id' => $pe->getPagina()->getId(),
                                           'nombre' => $pe->getPagina()->getNombre(),
                                           'selected' => $pe->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '');
                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => false,
                                       'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['grupo']:
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    $usuarios_id = array();
                    foreach ($nps as $np)
                    {
                        $usuarios_id[] = $np->getEntidadId();
                    }
                    $qb = $em->createQueryBuilder();
                    $qb->select('ru, u')
                       ->from('LinkComunBundle:AdminRolUsuario', 'ru')
                       ->leftJoin('ru.usuario', 'u')
                       ->andWhere('u.empresa = :empresa_id')
                       ->andWhere('ru.rol = :participante')
                       ->setParameters(array('empresa_id' => $notificacion_programada->getNotificacion()->getEmpresa()->getId(),
                                             'participante' => $yml['parameters']['rol']['participante']));
                    $query = $qb->getQuery();
                    $rus = $query->getResult();
                    $valores = array();
                    foreach ($rus as $ru)
                    {
                        $valores[] = array('id' => $ru->getUsuario()->getId(),
                                           'nombre' => $ru->getUsuario()->getNombre().' '.$ru->getUsuario()->getApellido(),
                                           'selected' => in_array($ru->getUsuario()->getId(), $usuarios_id) ? 'selected' : '');
                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => true,
                                       'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['no_ingresado']:
                    $entidades = array('tipo' => 'text',
                                       'multiple' => false,
                                       'valor' => $this->get('translator')->trans('Aquellos empleados de la empresa').' '.$notificacion_programada->getNotificacion()->getEmpresa()->getNombre().' '.$this->get('translator')->trans('que aún no han ingresado a la plataforma').'.');
                    break;
                case $yml['parameters']['tipo_destino']['no_ingresado_programa']:
                    $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                                JOIN pe.pagina p 
                                                WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                                ORDER BY pe.orden ASC")
                                ->setParameter('empresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId());
                    $pes = $query->getResult();
                    $valores = array();
                    foreach ($pes as $pe)
                    {
                        $valores[] = array('id' => $pe->getPagina()->getId(),
                                           'nombre' => $pe->getPagina()->getNombre(),
                                           'selected' => $pe->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '');
                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => false,
                                       'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['aprobados']:
                    $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                                JOIN pe.pagina p 
                                                WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                                ORDER BY pe.orden ASC")
                                ->setParameter('empresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId());
                    $pes = $query->getResult();
                    $valores = array();
                    foreach ($pes as $pe)
                    {
                        $valores[] = array('id' => $pe->getPagina()->getId(),
                                           'nombre' => $pe->getPagina()->getNombre(),
                                           'selected' => $pe->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '');
                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => false,
                                       'valores' => $valores);
                    break;
            }

        }
        else {

            $notificacion_programada = new AdminNotificacionProgramada();

            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
            $notificacion_programada->setNotificacion($notificacion);
            $entidades = array();

        }

        if ($request->getMethod() == 'POST')
        {

            $fecha_difusion = $request->request->get('fecha_difusion');
            $tipo_destino_id = $request->request->get('tipo_destino_id');
            $entidades_seleccionadas = $request->request->get('entidades');

            $tipo_destino = $em->getRepository('LinkComunBundle:AdminTipoDestino')->find($tipo_destino_id);
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            list($d, $m, $a) = explode("/", $fecha_difusion);
            $fecha_difusion = "$a-$m-$d";

            $entidades_incluidas = array();

            // Si estamos editando una notificación programada del tipo destino Grupo de participantes, hay que eliminar primero el grupo
            if ($notificacion_programada_id)
            {
                $grupos = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                foreach ($grupos as $grupo)
                {
                    if ($tipo_destino_id == $yml['parameters']['tipo_destino']['grupo'])
                    {
                        if (!in_array($grupo->getEntidadId(), $entidades_seleccionadas))
                        {
                            $em->remove($grupo);
                            $em->flush();
                        }
                        else {
                            $entidades_incluidas[] = $grupo->getEntidadId();
                        }
                    }
                    else {
                        $em->remove($grupo);
                        $em->flush();
                    }
                }
            }

            $notificacion_programada->setTipoDestino($tipo_destino);
            if ($tipo_destino_id == $yml['parameters']['tipo_destino']['todos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['no_ingresado'])
            {
                $notificacion_programada->setEntidadId(null);
            }
            else {
                $notificacion_programada->setEntidadId($entidades_seleccionadas);
            }
            $notificacion_programada->setUsuario($usuario);
            $notificacion_programada->setFechaDifusion(new \DateTime($fecha_difusion));
            $notificacion_programada->setGrupo(null);
            $notificacion_programada->setEnviado(false);
            $em->persist($notificacion_programada);
            $em->flush();

            if ($tipo_destino_id == $yml['parameters']['tipo_destino']['grupo'])
            {
                // Creación del grupo de participantes seleccionados
                foreach ($entidades_seleccionadas as $entidad)
                {
                    if (!in_array($entidad, $entidades_incluidas))
                    {
                        $np = new AdminNotificacionProgramada();
                        $np->setNotificacion($notificacion_programada->getNotificacion());
                        $np->setTipoDestino($tipo_destino);
                        $np->setEntidadId($entidad);
                        $np->setUsuario($usuario);
                        $np->setFechaDifusion(new \DateTime($fecha_difusion));
                        $np->setGrupo($notificacion_programada);
                        $np->setEnviado(false);
                        $em->persist($np);
                        $em->flush();
                    }
                }
            }

            return $this->redirectToRoute('_showNotificacionProgramada', array('notificacion_programada_id' => $notificacion_programada->getId()));

        }
        
        // Tipos de destino
        $tds = $em->getRepository('LinkComunBundle:AdminTipoDestino')->findAll();
        
        return $this->render('LinkBackendBundle:Notificacion:editNotificacionProgramada.html.twig', array('notificacion_programada' => $notificacion_programada,
                                                                                                          'tds' => $tds,
                                                                                                          'entidades' => $entidades));
        
    }

    public function ajaxGrupoSeleccionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $tipo_destino_id = $request->query->get('tipo_destino_id');
        $notificacion_id = $request->query->get('notificacion_id');
        $notificacion_programada_id = $request->query->get('notificacion_programada_id');

        if ($notificacion_programada_id)
        {
            $notificacion_programada = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);
            $notificacion = $notificacion_programada->getNotificacion();
        }
        else {
            $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        }
        
        switch ($tipo_destino_id)
        {
            case $yml['parameters']['tipo_destino']['todos']:
                $entidades = array('tipo' => 'text',
                                   'multiple' => false,
                                   'valor' => $this->get('translator')->trans('Todos los empleados de la empresa').' '.$notificacion->getEmpresa()->getNombre());
                break;
            case $yml['parameters']['tipo_destino']['nivel']:
                $niveles = $em->getRepository('LinkComunBundle:AdminNivel')->findByEmpresa($notificacion->getEmpresa()->getId());
                $valores = array();
                foreach ($niveles as $nivel)
                {
                    $valores[] = array('id' => $nivel->getId(),
                                       'nombre' => $nivel->getNombre(),
                                       'selected' => $notificacion_programada_id ? $nivel->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => false,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['programa']:
                $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                            JOIN pe.pagina p 
                                            WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                            ORDER BY pe.orden ASC")
                            ->setParameter('empresa_id', $notificacion->getEmpresa()->getId());
                $pes = $query->getResult();
                $valores = array();
                foreach ($pes as $pe)
                {
                    $valores[] = array('id' => $pe->getPagina()->getId(),
                                       'nombre' => $pe->getPagina()->getNombre(),
                                       'selected' => $notificacion_programada_id ? $pe->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => false,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['grupo']:
                $usuarios_id = array();
                if ($notificacion_programada_id)
                {
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    foreach ($nps as $np)
                    {
                        $usuarios_id[] = $np->getEntidadId();
                    }
                }
                $qb = $em->createQueryBuilder();
                $qb->select('ru, u')
                   ->from('LinkComunBundle:AdminRolUsuario', 'ru')
                   ->leftJoin('ru.usuario', 'u')
                   ->andWhere('u.empresa = :empresa_id')
                   ->andWhere('ru.rol = :participante')
                   ->setParameters(array('empresa_id' => $notificacion->getEmpresa()->getId(),
                                         'participante' => $yml['parameters']['rol']['participante']));
                $query = $qb->getQuery();
                $rus = $query->getResult();
                $valores = array();
                foreach ($rus as $ru)
                {
                    $valores[] = array('id' => $ru->getUsuario()->getId(),
                                       'nombre' => $ru->getUsuario()->getNombre().' '.$ru->getUsuario()->getApellido(),
                                       'selected' => in_array($ru->getUsuario()->getId(), $usuarios_id) ? 'selected' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => true,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado']:
                $entidades = array('tipo' => 'text',
                                   'multiple' => false,
                                   'valor' => $this->get('translator')->trans('Aquellos empleados de la empresa').' '.$notificacion->getEmpresa()->getNombre().' '.$this->get('translator')->trans('que aún no han ingresado a la plataforma').'.');
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado_programa']:
                $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                            JOIN pe.pagina p 
                                            WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                            ORDER BY pe.orden ASC")
                            ->setParameter('empresa_id', $notificacion->getEmpresa()->getId());
                $pes = $query->getResult();
                $valores = array();
                foreach ($pes as $pe)
                {
                    $valores[] = array('id' => $pe->getPagina()->getId(),
                                       'nombre' => $pe->getPagina()->getNombre(),
                                       'selected' => $notificacion_programada_id ? $pe->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => false,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['aprobados']:
                $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                            JOIN pe.pagina p 
                                            WHERE pe.empresa = :empresa_id AND p.pagina IS NULL 
                                            ORDER BY pe.orden ASC")
                            ->setParameter('empresa_id', $notificacion->getEmpresa()->getId());
                $pes = $query->getResult();
                $valores = array();
                foreach ($pes as $pe)
                {
                    $valores[] = array('id' => $pe->getPagina()->getId(),
                                       'nombre' => $pe->getPagina()->getNombre(),
                                       'selected' => $notificacion_programada_id ? $pe->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => false,
                                   'valores' => $valores);
                break;
        }

        $html = $this->renderView('LinkBackendBundle:Notificacion:grupoSeleccion.html.twig', array('entidades' => $entidades));

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function showNotificacionProgramadaAction(Request $request, $notificacion_programada_id)
    {
                
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
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

        $notificacion_programada = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

        switch ($notificacion_programada->getTipoDestino()->getId())
        {
            case $yml['parameters']['tipo_destino']['todos']:
                $entidades = array('tipo' => 'text',
                                   'valor' => $this->get('translator')->trans('Todos los empleados de la empresa').' '.$notificacion_programada->getNotificacion()->getEmpresa()->getNombre());
                break;
            case $yml['parameters']['tipo_destino']['nivel']:
                $entidad = $em->getRepository('LinkComunBundle:AdminNivel')->find($notificacion_programada->getEntidadId());
                $entidades = array('tipo' => 'text',
                                   'valor' => $entidad->getNombre());
                break;
            case $yml['parameters']['tipo_destino']['programa']:
                $entidad = $em->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidades = array('tipo' => 'text',
                                   'valor' => $entidad->getNombre());
                break;
            case $yml['parameters']['tipo_destino']['grupo']:
                $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($np->getEntidadId());
                    $valores[] = $usuario->getNombre().' '.$usuario->getApellido();
                }
                $entidades = array('tipo' => 'table',
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado']:
                $entidades = array('tipo' => 'text',
                                   'valor' => $this->get('translator')->trans('Aquellos empleados de la empresa').' '.$notificacion_programada->getNotificacion()->getEmpresa()->getNombre().' '.$this->get('translator')->trans('que aún no han ingresado a la plataforma').'.');
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado_programa']:
                $entidad = $em->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidades = array('tipo' => 'text',
                                   'valor' => $entidad->getNombre());
                break;
            case $yml['parameters']['tipo_destino']['aprobados']:
                $entidad = $em->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidades = array('tipo' => 'text',
                                   'valor' => $entidad->getNombre());
                break;
        }

        return $this->render('LinkBackendBundle:Notificacion:showNotificacionProgramada.html.twig', array('notificacion_programada' => $notificacion_programada,
                                                                                                          'entidades' => $entidades));
        
    }

}
