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
use Link\ComunBundle\Entity\AdminCorreo;
use Link\ComunBundle\Entity\AdminCorreoFallido;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;



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

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
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

    public function failedEmails($notificaciones){
        $em = $this->getDoctrine()->getManager();
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
        $notificacionesId = array ();
        foreach ($notificaciones as $notificacion) {
            $chijos = 0;
            $query = $em->createQuery("SELECT cf.id FROM LinkComunBundle:AdminCorreoFallido cf
                                      WHERE cf.reenviado = :reenviado
                                      AND cf.entidadId = :notificacion_id")
                    ->setParameters(array('notificacion_id' => $notificacion->getId(),'reenviado'=>FALSE));
            $cfll = $query->getResult();

            $hijos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion->getId());

            if(count($hijos)>0)
            {
                foreach ($hijos as $hijo){
                    $mails = $this->getDoctrine()->getRepository('LinkComunBundle:AdminCorreoFallido')->findBy(array('entidadId'=>$hijo->getId()));
                    $chijos = $chijos + count($mails);
                }
            }

            $total = $chijos +count($cfll);
            if($total>0){
                $notificacionesId[$notificacion->getId()] = $total;
            }

        }
        return $notificacionesId;
    }


    public function ajaxExcelCorreosAction(Request $request)
    {
        try{
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
            $em = $this->getDoctrine()->getManager();
            $f = $this->get('funciones');
            $session = new Session();
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
            $npId = (int)$request->request->get('notificacion_id');
            $ids = array($npId);
            $np = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($npId);
            $npChilds = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($np->getId());

            $pex=$this->get('phpexcel');

            foreach ($npChilds as $child) {
                array_push($ids,$child->getId());
            }

            $query = $em->createQuery("SELECT cf FROM LinkComunBundle:AdminCorreoFallido cf
                                      WHERE cf.reenviado = :reenviado
                                      AND cf.entidadId IN (:indices)")
                    ->setParameters(array('indices' => $ids,'reenviado'=>FALSE));
            $mails = $query->getResult();
            $encabezado = array('titulo'=>'Correos no entregados ('.count($mails).') : '.$np->getNotificacion()->getAsunto(),'fecha'=>$np->getFechaDifusion()->format('d/m/Y'),'empresa'=>$np->getNotificacion()->getEmpresa()->getNombre());
            $excel = $f->ExcelMails($mails,$encabezado,$pex,$yml,$np->getId());
            $return = json_encode($excel);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        } catch(\Exception $ex){
            $return = array('ok'=>0,'msg'=>$ex->getMessage());
            return new JsonResponse($return);
        }


    }

    public function notificacionProgramadasAction($id,Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
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
        $notificacion_id = $request->query->get('notificacion_id');

        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($id);

        $query = $em->createQuery("SELECT np FROM LinkComunBundle:AdminNotificacionProgramada np
                                    WHERE np.notificacion = :notificacion_id
                                    AND np.grupo IS NULL
                                    ORDER BY np.fechaDifusion DESC")
                    ->setParameters(array('notificacion_id' => $id));
        $nps = $query->getResult();
        $failed = $this->failedEmails($nps);

        //$html = $this->renderView('LinkBackendBundle:Notificacion:notificacionesProgramadas.html.twig', array('nps' => $nps,'failed'=>$failed));

        //return = array('html' => $html,
                        //'notificacion' => $notificacion->getAsunto());

        return $this->render('LinkBackendBundle:Notificacion:notificacionProgramadas.html.twig', array('nps' => $nps,
                                                                                                        'failed' => $failed));

        //$return = json_encode($return);
        //eturn new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxTreeGrupoProgramadoAction($notificacion_programada_id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
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
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $notificacion_programada = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

        $return = array();
        switch ($notificacion_programada->getTipoDestino()->getId())
        {
            case $yml['parameters']['tipo_destino']['todos']:
                $hoy = date('Y-m-d');
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['todos'], \PDO::PARAM_INT);
                $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', 0, \PDO::PARAM_INT);
                $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $this->get('translator')->trans('Todos los empleados de la empresa').' '.$notificacion_programada->getNotificacion()->getEmpresa()->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['nivel']:
                $hoy = date('Y-m-d');
                $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($notificacion_programada->getEntidadId());
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['nivel'], \PDO::PARAM_INT);
                $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getEntidadId(), \PDO::PARAM_INT);
                $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('usuarios').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['programa']:
                $hoy = date('Y-m-d');
                $nps = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                foreach ($nps as $np)
                {
                    $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($np->getEntidadId());
                    $query = $em->getConnection()->prepare('SELECT
                                                            fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                            resultado;');
                    $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['programa'], \PDO::PARAM_INT);
                    $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                    $query->bindValue(':pentidad_id', $np->getEntidadId(), \PDO::PARAM_INT);
                    $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                    $query->execute();
                    $r = $query->fetchAll();
                    $cantidad = $r[0]['resultado'];
                    $return[] = array('text' => $programa->getNombre().' ('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                      'state' => array('opened' => true),
                                      'icon' => 'fa fa-angle-double-right');
                }
                break;
            case $yml['parameters']['tipo_destino']['grupo']:
                $hoy = date('Y-m-d');
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['grupo'], \PDO::PARAM_INT);
                $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getId(), \PDO::PARAM_INT);
                $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado']:
                $hoy = date('Y-m-d');
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['no_ingresado'], \PDO::PARAM_INT);
                $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', 0, \PDO::PARAM_INT);
                $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $this->get('translator')->trans('Aquellos empleados de la empresa').' '.$notificacion_programada->getNotificacion()->getEmpresa()->getNombre().' '.$this->get('translator')->trans('que aún no han ingresado a la plataforma').'.',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['no_ingresado_programa']:
                $hoy = date('Y-m-d');
                $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $query = $em->getConnection()->prepare('SELECT
                                                        fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                        resultado;');
                $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['no_ingresado_programa'], \PDO::PARAM_INT);
                $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                $query->bindValue(':pentidad_id', $notificacion_programada->getEntidadId(), \PDO::PARAM_INT);
                $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                $query->execute();
                $r = $query->fetchAll();
                $cantidad = $r[0]['resultado'];
                $return[] = array('text' => $entidad->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                $return[] = array('text' => '('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
                break;
            case $yml['parameters']['tipo_destino']['aprobados']:
                $hoy = date('Y-m-d');
                $nps = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                foreach ($nps as $np)
                {
                    $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($np->getEntidadId());
                    $query = $em->getConnection()->prepare('SELECT
                                                            fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                            resultado;');
                    $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['aprobados'], \PDO::PARAM_INT);
                    $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                    $query->bindValue(':pentidad_id', $np->getEntidadId(), \PDO::PARAM_INT);
                    $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                    $query->execute();
                    $r = $query->fetchAll();
                    $cantidad = $r[0]['resultado'];
                    $return[] = array('text' => $programa->getNombre().' ('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                      'state' => array('opened' => true),
                                      'icon' => 'fa fa-angle-double-right');
                }
                break;
            case $yml['parameters']['tipo_destino']['en_curso']:
                $hoy = date('Y-m-d');
                $nps = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                foreach ($nps as $np)
                {
                    $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($np->getEntidadId());
                    $query = $em->getConnection()->prepare('SELECT
                                                            fncantidad_programados(:ptipo_destino_id, :pempresa_id, :pentidad_id, :pfecha_hoy) as
                                                            resultado;');
                    $query->bindValue(':ptipo_destino_id', $yml['parameters']['tipo_destino']['en_curso'], \PDO::PARAM_INT);
                    $query->bindValue(':pempresa_id', $notificacion_programada->getNotificacion()->getEmpresa()->getId(), \PDO::PARAM_INT);
                    $query->bindValue(':pentidad_id', $np->getEntidadId(), \PDO::PARAM_INT);
                    $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                    $query->execute();
                    $r = $query->fetchAll();
                    $cantidad = $r[0]['resultado'];
                    $return[] = array('text' => $programa->getNombre().' ('.$cantidad.' '.$this->get('translator')->trans('participantes').')',
                                      'state' => array('opened' => true),
                                      'icon' => 'fa fa-angle-double-right');
                }
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
                    $hoy = date('Y-m-d');
                    $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                                WHERE n.empresa = :empresa_id
                                                ORDER BY n.nombre ASC")
                                ->setParameter('empresa_id',$notificacion_programada->getNotificacion()->getEmpresa()->getId());
                    $niveles = $query->getResult();

                    $valores = array();
                    foreach ($niveles as $nivel)
                    {
                        //return new response('aqui1');
                    if($nivel->getFechaFin())
                    {
                        //return new response('aqui2');
                            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                                    WHERE n.id = :nivel_id
                                                    AND n.fechaFin >= :hoy
                                                    ORDER BY n.nombre ASC")
                                    ->setParameters(array('nivel_id'=> $nivel->getId(),
                                                        'hoy' => $hoy));
                            $niveles_f = $query->getResult();
                            if($niveles_f)
                            {
                                foreach($niveles_f as $nivel_f)
                                {
                                    $valores[] = array('id' => $nivel->getId(),
                                                    'nombre' => $nivel->getNombre(),
                                                    'selected' => $notificacion_programada_id ? $nivel->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                                }
                            }
                    }else{
                        //return new response('aqui3');
                            $query = $em->createQuery("SELECT np, pe FROM LinkComunBundle:CertiNivelPagina np
                                                        JOIN np.paginaEmpresa pe
                                                        WHERE np.nivel = :nivel_id
                                                        AND pe.fechaVencimiento >= :hoy
                                                        AND pe.empresa = :empresa_id")
                                        ->setParameters(array('nivel_id'=> $nivel->getId(),
                                                            'hoy' => $hoy,
                                                            'empresa_id' => $notificacion_programada->getNotificacion()->getEmpresa()->getId()));
                            $niveles_f = $query->getResult();
                            //return new response(var_dump($niveles_f));
                            if($niveles_f)
                            {
                                //return new response('aqui4');

                                foreach($niveles_f as $nivel_f)
                                {
                                    $valores[] = array('id' => $nivel->getId(),
                                                    'nombre' => $nivel->getNombre(),
                                                    'selected' => $notificacion_programada_id ? $nivel->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                                }
                            }
                    }

                    }
                    $entidades = array('tipo' => 'select',
                                    'multiple' => false,
                                    'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['programa']:
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    $programas_id = array();
                    foreach ($nps as $np)
                    {
                        $programas_id[] = $np->getEntidadId();
                    }
                    $hoy = date('Y-m-d');
                    $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                                JOIN np.paginaEmpresa pe
                                                JOIN pe.pagina p
                                                JOIN np.nivel n
                                                WHERE pe.empresa = :empresa_id
                                                AND p.pagina IS NULL
                                                AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                                ORDER BY pe.orden ASC")
                                ->setParameters(array('empresa_id'=> $notificacion_programada->getNotificacion()->getEmpresa()->getId(),
                                                    'hoy' => $hoy));
                    $pes = $query->getResult();
                    //return new response(count($pes));
                    $valores = array();
                    $id_pagina = '';
                    foreach ($pes as $pe)
                    {
                        if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                        {
                            $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                            'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                            'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                            $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                        }


                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => true,
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
                       ->orderBy('u.nombre', 'ASC')
                       ->setParameters(array('empresa_id' => $notificacion_programada->getNotificacion()->getEmpresa()->getId(),
                                             'participante' => $yml['parameters']['rol']['participante']));
                    $query = $qb->getQuery();
                    $rus = $query->getResult();
                    $valores = array();
                    foreach ($rus as $ru)
                    {
                        if($ru->getUsuario()->getCorreoPersonal() OR $ru->getUsuario()->getCorreoCorporativo())
                    {
                        if($ru->getUsuario()->getNivel()->getFechaFin())
                        {
                            $hoy = date('Y-m-d');
                            if($ru->getUsuario()->getNivel()->getFechaFin() > $hoy)
                            {
                                $correo = !$ru->getUsuario()->getCorreoPersonal() ? !$ru->getUsuario()->getCorreoCorporativo() ? $this->get('translator')->trans('Sin correo') : $ru->getUsuario()->getCorreoCorporativo() : $ru->getUsuario()->getCorreoPersonal();
                                $valores[] = array('id' => $ru->getUsuario()->getId(),
                                                'nombre' => $ru->getUsuario()->getNombre().' '.$ru->getUsuario()->getApellido().' ('.$correo.')',
                                                'selected' => in_array($ru->getUsuario()->getId(), $usuarios_id) ? 'selected' : '');
                            }
                        }else{
                            $correo = !$ru->getUsuario()->getCorreoPersonal() ? !$ru->getUsuario()->getCorreoCorporativo() ? $this->get('translator')->trans('Sin correo') : $ru->getUsuario()->getCorreoCorporativo() : $ru->getUsuario()->getCorreoPersonal();
                            $valores[] = array('id' => $ru->getUsuario()->getId(),
                                            'nombre' => $ru->getUsuario()->getNombre().' '.$ru->getUsuario()->getApellido().' ('.$correo.')',
                                            'selected' => in_array($ru->getUsuario()->getId(), $usuarios_id) ? 'selected' : '');
                        }
                    }

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
                    $programas_id = array();
                    $hoy = date('Y-m-d');
                    $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                                JOIN np.paginaEmpresa pe
                                                JOIN pe.pagina p
                                                JOIN np.nivel n
                                                WHERE pe.empresa = :empresa_id
                                                AND p.pagina IS NULL
                                                AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                                ORDER BY pe.orden ASC")
                                ->setParameters(array('empresa_id'=> $notificacion_programada->getNotificacion()->getEmpresa()->getId(),
                                                    'hoy' => $hoy));
                    $pes = $query->getResult();
                    //return new response(count($pes));
                    $valores = array();
                    $id_pagina = '';
                    foreach ($pes as $pe)
                    {
                        if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                        {
                            $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                            'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                            'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                            $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                        }


                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => false,
                                       'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['aprobados']:
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    $programas_id = array();
                    foreach ($nps as $np)
                    {
                        $programas_id[] = $np->getEntidadId();
                    }
                    $hoy = date('Y-m-d');
                    $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                                JOIN np.paginaEmpresa pe
                                                JOIN pe.pagina p
                                                JOIN np.nivel n
                                                WHERE pe.empresa = :empresa_id
                                                AND p.pagina IS NULL
                                                AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                                ORDER BY pe.orden ASC")
                                ->setParameters(array('empresa_id'=> $notificacion_programada->getNotificacion()->getEmpresa()->getId(),
                                                    'hoy' => $hoy));
                    $pes = $query->getResult();
                    //return new response(count($pes));
                    $valores = array();
                    $id_pagina = '';
                    foreach ($pes as $pe)
                    {
                        if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                        {
                            $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                            'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                            'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                            $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                        }


                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => true,
                                       'valores' => $valores);
                    break;
                case $yml['parameters']['tipo_destino']['en_curso']:
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    $programas_id = array();
                    foreach ($nps as $np)
                    {
                        $programas_id[] = $np->getEntidadId();
                    }
                    $hoy = date('Y-m-d');
                    $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                                JOIN np.paginaEmpresa pe
                                                JOIN pe.pagina p
                                                JOIN np.nivel n
                                                WHERE pe.empresa = :empresa_id
                                                AND p.pagina IS NULL
                                                AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                                ORDER BY pe.orden ASC")
                                ->setParameters(array('empresa_id'=> $notificacion_programada->getNotificacion()->getEmpresa()->getId(),
                                                    'hoy' => $hoy));
                    $pes = $query->getResult();
                    //return new response(count($pes));
                    $valores = array();
                    $id_pagina = '';
                    foreach ($pes as $pe)
                    {
                        if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                        {
                            $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                            'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                            'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                            $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                        }


                    }
                    $entidades = array('tipo' => 'select',
                                       'multiple' => true,
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
                    if ($tipo_destino_id == $yml['parameters']['tipo_destino']['programa'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['aprobados'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['en_curso'])
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
            if ($tipo_destino_id == $yml['parameters']['tipo_destino']['todos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['programa'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['no_ingresado'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['aprobados'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['en_curso'])
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

            if ($tipo_destino_id == $yml['parameters']['tipo_destino']['programa'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['grupo'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['aprobados'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['en_curso'])
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
                    else {
                        $np = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findOneBy(array('notificacion' => $notificacion_programada->getNotificacion()->getId(),
                                                                                                                 'entidadId' => $entidad,
                                                                                                                 'grupo' => $notificacion_programada->getId()));
                        $np->setFechaDifusion(new \DateTime($fecha_difusion));
                        $np->setEnviado(false);
                        $em->persist($np);
                        $em->flush();
                    }
                }
            }

            // if ($notificacion_programada->getFechaDifusion()->format('Y-m-d') == date('Y-m-d'))
            // {
                // $hoy = date('Y-m-d H:i:s');
                // //return new response($hoy);
                // // Se envía la notificación de una vez
                // $query = $em->getConnection()->prepare('SELECT fnnotificacion_programada(:pnotificacion_programada_id, :pfecha_hoy) AS resultado;');
                // $query->bindValue(':pnotificacion_programada_id', $notificacion_programada->getId(), \PDO::PARAM_INT);
                // $query->bindValue(':pfecha_hoy', $hoy, \PDO::PARAM_STR);
                // $query->execute();
                // $r = $query->fetchAll();
                // //return new response(count($r));
                // $notificaciones_id = array();

                // $background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
                // $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion.png';
                // $footer = $this->container->getParameter('folders')['uploads'].'recursos/footer.bg.form.png';
                // //$logo = ''; // Por ahora no se colocará el logo de formación en el header del correo
                // $link_plataforma = $this->container->getParameter('link_plataforma').$notificacion_programada->getNotificacion()->getEmpresa()->getId();
                // $j = 0; // Contador de correos exitosos
                // $np_id = 0; // notificacion_programada_id
                // $ids = array();
                // $crr = array();
                // $prueba_usuario= array();
                // for ($i = 0; $i < count($r); $i++)
                // {
                    // if ($j == $yml['parameters']['limite_correos_notificaciones']['controlador'])
                    // {
                        // // Cantidad tope de correos en una corrida
                        // break;
                    // }

                    // // Limpieza de resultados
                    // $reg = substr($r[$i]['resultado'], strrpos($r[$i]['resultado'], '{"')+2);
                    // $reg = str_replace('"}', '', $reg);

                    // // Se descomponen los elementos del resultado
                    // list($np_id, $usuario_id, $login, $clave, $nombre, $apellido, $correo, $asunto, $mensaje, $fecha, $nivel_id) = explode("__", $reg);
                    // array_push($ids,$i);
                    // array_push($prueba_usuario,array('np_id' => $np_id,
                                                     // 'usuario_id' => $usuario_id,
                                                     // 'login' => $login,
                                                     // 'nombre' => $nombre));

                    // if($fecha == 'vacio')
                    // {
                        // if ($correo != '')
                        // {

                            // // Validar que no se haya enviado el correo a este destinatario
                            // $correo_bd = $em->getRepository('LinkComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml['parameters']['tipo_correo']['notificacion_programada'],
                                                                                                            // 'entidadId' => $np_id,
                                                                                                            // 'usuario' => $usuario_id,
                                                                                                            // 'correo' => $correo));
                            // //return new response(var_dump($correo_bd->getEntidadId()));
                            // //return new response(var_dump($correo_bd));
                            // if (!$correo_bd)
                            // {
                                // //return new response($hoy);
                                // // Sustitución de variables en el texto
                                // $comodines = array('%%usuario%%', '%%clave%%', '%%nombre%%', '%%apellido%%');
                                // $reemplazos = array($login, $clave, $nombre, $apellido);
                                // $mensaje = str_replace($comodines, $reemplazos, $mensaje);

                                // $parametros_correo = array('twig' => 'LinkBackendBundle:Notificacion:emailCommand.html.twig',
                                                        // 'datos' => array('nombre' => $nombre,
                                                                            // 'apellido' => $apellido,
                                                                            // 'mensaje' => $mensaje,
                                                                            // 'background' => $background,
                                                                            // 'logo' => $logo,
                                                                            // 'footer' => $footer,
                                                                            // 'link_plataforma' => $link_plataforma),
                                                        // 'asunto' => $asunto,
                                                        // 'remitente' => $this->container->getParameter('mailer_user_tutor'),
                                                        // 'remitente_name' => $this->container->getParameter('mailer_user_tutor_name'),
                                                        // 'destinatario' => $correo,
                                                        // 'mailer' => 'tutor_mailer');
                                // $ok = $f->sendEmail($parametros_correo);
                                // //$ok = 1;
                                // if ($ok)
                                // {

                                    // $j++;

                                    // // Si es una notificación para un grupo de participantes, se marca como enviado
                                    // $r_np = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                                    // if ($r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo'] || $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa'] || $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['aprobados']|| $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['en_curso'])
                                    // {
                                        // $r_np->setEnviado(true);
                                        // $em->persist($r_np);
                                        // $em->flush();
                                        // array_push($notificaciones_id,$np_id);
                                    // }else{
                                        // if(count($notificaciones_id) == 0){
                                            // array_push($notificaciones_id,$np_id);
                                        // }
                                    // }

                                    // // Registro del correo recien enviado
                                    // $tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['notificacion_programada']);
                                    // $r_usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
                                    // $email = new AdminCorreo();
                                    // $email->setTipoCorreo($tipo_correo);
                                    // $email->setEntidadId($np_id);
                                    // $email->setUsuario($r_usuario);
                                    // $email->setCorreo($correo);
                                    // $email->setFecha(new \DateTime('now'));
                                    // $em->persist($email);
                                    // $em->flush();

                                // }

                                // /*return $this->render('LinkBackendBundle:Notificacion:emailCommand.html.twig', array('nombre' => $nombre,
                                                                            // 'apellido' => $apellido,
                                                                            // 'mensaje' => $mensaje,
                                                                            // 'background' => $background,
                                                                            // 'logo' => $logo,
                                                                            // 'link_plataforma' => $link_plataforma));*/

                            // }

                        // }
                    // }else{
                        // if($fecha == '1900-01-01'){
                            // $query = $em->createQuery("SELECT np, pe FROM LinkComunBundle:CertiNivelPagina np
                                                        // JOIN np.paginaEmpresa pe
                                                        // WHERE np.nivel = :nivel_id
                                                        // AND pe.fechaVencimiento >= :hoy
                                                        // AND pe.empresa = :empresa_id")
                                        // ->setParameters(array('nivel_id'=> $nivel_id,
                                                            // 'hoy' => $hoy,
                                                            // 'empresa_id' => $notificacion_programada->getNotificacion()->getEmpresa()->getId()));
                            // $niveles_f = $query->getResult();
                            // if($niveles_f)
                            // {
                                // if ($correo != '')
                                // {

                                    // // Validar que no se haya enviado el correo a este destinatario
                                    // $correo_bd = $em->getRepository('LinkComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml['parameters']['tipo_correo']['notificacion_programada'],
                                                                                                                    // 'entidadId' => $np_id,
                                                                                                                    // 'usuario' => $usuario_id,
                                                                                                                    // 'correo' => $correo));
                                    // //return new response(var_dump($correo_bd->getEntidadId()));
                                    // //return new response(var_dump($correo_bd));
                                    // if (!$correo_bd)
                                    // {
                                        // //return new response($hoy);
                                        // // Sustitución de variables en el texto
                                        // $comodines = array('%%usuario%%', '%%clave%%', '%%nombre%%', '%%apellido%%');
                                        // $reemplazos = array($login, $clave, $nombre, $apellido);
                                        // $mensaje = str_replace($comodines, $reemplazos, $mensaje);

                                        // $parametros_correo = array('twig' => 'LinkBackendBundle:Notificacion:emailCommand.html.twig',
                                                                // 'datos' => array('nombre' => $nombre,
                                                                                    // 'apellido' => $apellido,
                                                                                    // 'mensaje' => $mensaje,
                                                                                    // 'background' => $background,
                                                                                    // 'logo' => $logo,
                                                                                    // 'footer' => $footer,
                                                                                    // 'link_plataforma' => $link_plataforma),
                                                                // 'asunto' => $asunto,
                                                                // 'remitente' => $this->container->getParameter('mailer_user_tutor'),
                                                                // 'remitente_name' => $this->container->getParameter('mailer_user_tutor_name'),
                                                                // 'destinatario' => $correo,
                                                                // 'mailer' => 'tutor_mailer');
                                        // $ok = $f->sendEmail($parametros_correo);
                                        // //$ok = 1;
                                        // if ($ok)
                                        // {

                                            // $j++;

                                            // // Si es una notificación para un grupo de participantes, se marca como enviado
                                            // $r_np = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                                            // if ($r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo'] || $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa'] || $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['aprobados']|| $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['en_curso'])
                                            // {
                                                // $r_np->setEnviado(true);
                                                // $em->persist($r_np);
                                                // $em->flush();
                                                // array_push($notificaciones_id,$np_id);
                                            // }else{
                                                // if(count($notificaciones_id) == 0){
                                                    // array_push($notificaciones_id,$np_id);
                                                // }
                                            // }

                                            // // Registro del correo recien enviado
                                            // $tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['notificacion_programada']);
                                            // $r_usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
                                            // $email = new AdminCorreo();
                                            // $email->setTipoCorreo($tipo_correo);
                                            // $email->setEntidadId($np_id);
                                            // $email->setUsuario($r_usuario);
                                            // $email->setCorreo($correo);
                                            // $email->setFecha(new \DateTime('now'));
                                            // $em->persist($email);
                                            // $em->flush();

                                        // }

                                        // /*return $this->render('LinkBackendBundle:Notificacion:emailCommand.html.twig', array('nombre' => $nombre,
                                                                                    // 'apellido' => $apellido,
                                                                                    // 'mensaje' => $mensaje,
                                                                                    // 'background' => $background,
                                                                                    // 'logo' => $logo,
                                                                                    // 'link_plataforma' => $link_plataforma));*/

                                    // }

                                // }
                            // }
                        // }else{
                            // if($fecha > $hoy)
                            // {
                                // if ($correo != '')
                                // {

                                    // // Validar que no se haya enviado el correo a este destinatario
                                    // $correo_bd = $em->getRepository('LinkComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml['parameters']['tipo_correo']['notificacion_programada'],
                                                                                                                    // 'entidadId' => $np_id,
                                                                                                                    // 'usuario' => $usuario_id,
                                                                                                                    // 'correo' => $correo));
                                    // //return new response(var_dump($correo_bd->getEntidadId()));
                                    // //return new response(var_dump($correo_bd));
                                    // if (!$correo_bd)
                                    // {
                                        // //return new response($hoy);
                                        // // Sustitución de variables en el texto
                                        // $comodines = array('%%usuario%%', '%%clave%%', '%%nombre%%', '%%apellido%%');
                                        // $reemplazos = array($login, $clave, $nombre, $apellido);
                                        // $mensaje = str_replace($comodines, $reemplazos, $mensaje);

                                        // $parametros_correo = array('twig' => 'LinkBackendBundle:Notificacion:emailCommand.html.twig',
                                                                // 'datos' => array('nombre' => $nombre,
                                                                                    // 'apellido' => $apellido,
                                                                                    // 'mensaje' => $mensaje,
                                                                                    // 'background' => $background,
                                                                                    // 'logo' => $logo,
                                                                                    // 'footer' => $footer,
                                                                                    // 'link_plataforma' => $link_plataforma),
                                                                // 'asunto' => $asunto,
                                                                // 'remitente' => $this->container->getParameter('mailer_user_tutor'),
                                                                // 'remitente_name' => $this->container->getParameter('mailer_user_tutor_name'),
                                                                // 'destinatario' => $correo,
                                                                // 'mailer' => 'tutor_mailer');
                                        // $ok = $f->sendEmail($parametros_correo);
                                        // //$ok = 1;
                                        // if ($ok)
                                        // {

                                            // $j++;

                                            // // Si es una notificación para un grupo de participantes, se marca como enviado
                                            // $r_np = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                                            // if ($r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['grupo'] || $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['programa'] || $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['aprobados']|| $r_np->getTipoDestino()->getId() == $yml['parameters']['tipo_destino']['en_curso'])
                                            // {
                                                // $r_np->setEnviado(true);
                                                // $em->persist($r_np);
                                                // $em->flush();
                                                // array_push($notificaciones_id,$np_id);
                                            // }else{
                                                // if(count($notificaciones_id) == 0){
                                                    // array_push($notificaciones_id,$np_id);
                                                // }
                                            // }

                                            // // Registro del correo recien enviado
                                            // $tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['notificacion_programada']);
                                            // $r_usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
                                            // $email = new AdminCorreo();
                                            // $email->setTipoCorreo($tipo_correo);
                                            // $email->setEntidadId($np_id);
                                            // $email->setUsuario($r_usuario);
                                            // $email->setCorreo($correo);
                                            // $email->setFecha(new \DateTime('now'));
                                            // $em->persist($email);
                                            // $em->flush();

                                        // }

                                        // /*return $this->render('LinkBackendBundle:Notificacion:emailCommand.html.twig', array('nombre' => $nombre,
                                                                                    // 'apellido' => $apellido,
                                                                                    // 'mensaje' => $mensaje,
                                                                                    // 'background' => $background,
                                                                                    // 'logo' => $logo,
                                                                                    // 'link_plataforma' => $link_plataforma));*/

                                    // }

                                // }
                            // }
                        // }

                    // }

                    // //return new response('holaaa');



                // }
                // //return new response(var_dump($prueba_usuario));
                // // Si se enviaron todos los correos, se coloca la notificación como enviada
                // if ($np_id)
                // {
                    // if($tipo_destino_id == $yml['parameters']['tipo_destino']['todos'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['nivel'] ||
                       // $tipo_destino_id == $yml['parameters']['tipo_destino']['no_ingresado'] || $tipo_destino_id == $yml['parameters']['tipo_destino']['no_ingresado_programa']){
                           // $np_main = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                       // }else{
                           // $np_hija = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_id);
                           // $np_main = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($np_hija->getGrupo());

                       // }


                    // $query = $em->createQuery('SELECT COUNT(c.id) FROM LinkComunBundle:AdminCorreo c
                                                // WHERE c.tipoCorreo = :notificacion_programada
                                                // AND c.entidadId IN (:np_id)' )
                                // ->setParameters(array('notificacion_programada' => $yml['parameters']['tipo_correo']['notificacion_programada'],
                                                      // 'np_id' => $notificaciones_id));
                    // $emails = $query->getSingleScalarResult();

                    // if($emails == count($r)){
                        // $np_main->setEnviado(true);
                        // $em->persist($np_main);
                        // $em->flush();
                    // }


                // }

            // }

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
        $session = new Session();
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
                $hoy = date('Y-m-d');
                $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                            WHERE n.empresa = :empresa_id
                                            ORDER BY n.nombre ASC")
                            ->setParameter('empresa_id',$notificacion->getEmpresa()->getId());
                $niveles = $query->getResult();

                $valores = array();
                foreach ($niveles as $nivel)
                {
                    //return new response('aqui1');
                   if($nivel->getFechaFin())
                   {
                    //return new response('aqui2');
                        $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                                WHERE n.id = :nivel_id
                                                AND n.fechaFin >= :hoy
                                                ORDER BY n.nombre ASC")
                                ->setParameters(array('nivel_id'=> $nivel->getId(),
                                                    'hoy' => $hoy));
                        $niveles_f = $query->getResult();
                        if($niveles_f)
                        {
                            foreach($niveles_f as $nivel_f)
                            {
                                $valores[] = array('id' => $nivel->getId(),
                                                'nombre' => $nivel->getNombre(),
                                                'selected' => $notificacion_programada_id ? $nivel->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                            }
                        }
                   }else{
                    //return new response('aqui3');
                        $query = $em->createQuery("SELECT np, pe FROM LinkComunBundle:CertiNivelPagina np
                                                    JOIN np.paginaEmpresa pe
                                                    WHERE np.nivel = :nivel_id
                                                    AND pe.fechaVencimiento >= :hoy
                                                    AND pe.empresa = :empresa_id")
                                    ->setParameters(array('nivel_id'=> $nivel->getId(),
                                                          'hoy' => $hoy,
                                                          'empresa_id' => $notificacion->getEmpresa()->getId()));
                        $niveles_f = $query->getResult();
                        //return new response(var_dump($niveles_f));
                        if($niveles_f)
                        {
                            //return new response('aqui4');

                            foreach($niveles_f as $nivel_f)
                            {
                                $valores[] = array('id' => $nivel->getId(),
                                                'nombre' => $nivel->getNombre(),
                                                'selected' => $notificacion_programada_id ? $nivel->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                            }
                        }
                   }

                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => false,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['programa']:
                $programas_id = array();
                if ($notificacion_programada_id)
                {
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    foreach ($nps as $np)
                    {
                        $programas_id[] = $np->getEntidadId();
                    }
                }
                /*$query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                            JOIN pe.pagina p
                                            WHERE pe.empresa = :empresa_id
                                            AND p.pagina IS NULL
                                            ORDER BY pe.orden ASC")
                            ->setParameter('empresa_id', $notificacion->getEmpresa()->getId());
                $pes = $query->getResult();*/
                $hoy = date('Y-m-d');
                $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                            JOIN np.paginaEmpresa pe
                                            JOIN pe.pagina p
                                            JOIN np.nivel n
                                            WHERE pe.empresa = :empresa_id
                                            AND p.pagina IS NULL
                                            AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                            ORDER BY pe.orden ASC")
                            ->setParameters(array('empresa_id'=> $notificacion->getEmpresa()->getId(),
                                                  'hoy' => $hoy));
                $pes = $query->getResult();
                //return new response(count($pes));
                $valores = array();
                $id_pagina = '';
                foreach ($pes as $pe)
                {
                    if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                    {
                        $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                        'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                        'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                        $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                    }


                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => true,
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
                $hoy = date('Y-m-d');
                $qb = $em->createQueryBuilder();
                $qb->select('ru, u')
                   ->from('LinkComunBundle:AdminRolUsuario', 'ru')
                   ->leftJoin('ru.usuario', 'u')
                   ->leftJoin('u.nivel', 'n')
                   ->andWhere('u.empresa = :empresa_id')
                   ->andWhere('ru.rol = :participante')
                   ->andWhere('u.activo = :status')
                   ->orderBy('u.nombre', 'ASC')
                   ->setParameters(array('empresa_id' => $notificacion->getEmpresa()->getId(),
                                         'participante' => $yml['parameters']['rol']['participante'],
                                         'status'=>TRUE));
                $query = $qb->getQuery();
                $rus = $query->getResult();
                $valores = array();
                foreach ($rus as $ru)
                {
                    if($ru->getUsuario()->getCorreoPersonal() OR $ru->getUsuario()->getCorreoCorporativo())
                    {
                        if($ru->getUsuario()->getNivel()->getFechaFin())
                        {
                            if($ru->getUsuario()->getNivel()->getFechaFin() > $hoy)
                            {
                                $correo = !$ru->getUsuario()->getCorreoPersonal() ? !$ru->getUsuario()->getCorreoCorporativo() ? $this->get('translator')->trans('Sin correo') : $ru->getUsuario()->getCorreoCorporativo() : $ru->getUsuario()->getCorreoPersonal();
                                $valores[] = array('id' => $ru->getUsuario()->getId(),
                                                'nombre' => $ru->getUsuario()->getNombre().' '.$ru->getUsuario()->getApellido().' ('.$correo.')',
                                                'selected' => in_array($ru->getUsuario()->getId(), $usuarios_id) ? 'selected' : '');
                            }
                        }else{
                            $correo = !$ru->getUsuario()->getCorreoPersonal() ? !$ru->getUsuario()->getCorreoCorporativo() ? $this->get('translator')->trans('Sin correo') : $ru->getUsuario()->getCorreoCorporativo() : $ru->getUsuario()->getCorreoPersonal();
                            $valores[] = array('id' => $ru->getUsuario()->getId(),
                                            'nombre' => $ru->getUsuario()->getNombre().' '.$ru->getUsuario()->getApellido().' ('.$correo.')',
                                            'selected' => in_array($ru->getUsuario()->getId(), $usuarios_id) ? 'selected' : '');
                        }
                    }

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
                $hoy = date('Y-m-d');
                $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                            JOIN np.paginaEmpresa pe
                                            JOIN pe.pagina p
                                            JOIN np.nivel n
                                            WHERE pe.empresa = :empresa_id
                                            AND p.pagina IS NULL
                                            AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                            ORDER BY pe.orden ASC")
                            ->setParameters(array('empresa_id'=> $notificacion->getEmpresa()->getId(),
                                                  'hoy' => $hoy));
                $pes = $query->getResult();
                $valores = array();
                $id_pagina = '';
                foreach ($pes as $pe)
                {
                    if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                    {
                        $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                        'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                        'selected' => $notificacion_programada_id ? $pe->getPaginaEmpresa()->getPagina()->getId() == $notificacion_programada->getEntidadId() ? 'selected' : '' : '');
                        $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                    }
                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => false,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['aprobados']:
                $programas_id = array();
                if ($notificacion_programada_id)
                {
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    foreach ($nps as $np)
                    {
                        $programas_id[] = $np->getEntidadId();
                    }
                }
                $hoy = date('Y-m-d');
                $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                            JOIN np.paginaEmpresa pe
                                            JOIN pe.pagina p
                                            JOIN np.nivel n
                                            WHERE pe.empresa = :empresa_id
                                            AND p.pagina IS NULL
                                            AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                            ORDER BY pe.orden ASC")
                            ->setParameters(array('empresa_id'=> $notificacion->getEmpresa()->getId(),
                                                  'hoy' => $hoy));
                $pes = $query->getResult();
                //return new response(count($pes));
                $valores = array();
                $id_pagina = '';
                foreach ($pes as $pe)
                {
                    if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                    {
                        $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                        'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                        'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                        $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                    }


                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => true,
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['en_curso']:
                $programas_id = array();
                if ($notificacion_programada_id)
                {
                    $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                    foreach ($nps as $np)
                    {
                        $programas_id[] = $np->getEntidadId();
                    }
                }
                $hoy = date('Y-m-d');
                $query = $em->createQuery("SELECT np,pe FROM LinkComunBundle:CertiNivelPagina np
                                            JOIN np.paginaEmpresa pe
                                            JOIN pe.pagina p
                                            JOIN np.nivel n
                                            WHERE pe.empresa = :empresa_id
                                            AND p.pagina IS NULL
                                            AND (n.fechaFin IS NULL OR n.fechaFin >= :hoy)
                                            ORDER BY pe.orden ASC")
                            ->setParameters(array('empresa_id'=> $notificacion->getEmpresa()->getId(),
                                                  'hoy' => $hoy));
                $pes = $query->getResult();
                //return new response(count($pes));
                $valores = array();
                $id_pagina = '';
                foreach ($pes as $pe)
                {
                    if($pe->getPaginaEmpresa()->getPagina()->getId() != $id_pagina)
                    {
                        $valores[] = array('id' => $pe->getPaginaEmpresa()->getPagina()->getId(),
                                        'nombre' => $pe->getPaginaEmpresa()->getPagina()->getNombre(),
                                        'selected' => in_array($pe->getPaginaEmpresa()->getPagina()->getId(), $programas_id) ? 'selected' : '');
                        $id_pagina = $pe->getPaginaEmpresa()->getPagina()->getId();
                    }


                }
                $entidades = array('tipo' => 'select',
                                   'multiple' => true,
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


        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $session = new Session();
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
                $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $programa = $em->getRepository('LinkComunBundle:CertiPagina')->find($np->getEntidadId());
                    $valores[] = $programa->getNombre();
                }
                $entidades = array('tipo' => 'table',
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['grupo']:
                $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($np->getEntidadId());
                    $correo = !$usuario->getCorreoPersonal() ? !$usuario->getCorreoCorporativo() ? $this->get('translator')->trans('Sin correo') : $usuario->getCorreoCorporativo() : $usuario->getCorreoPersonal();
                    $valores[] = $usuario->getNombre().' '.$usuario->getApellido().' ('.$correo.')';
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
                $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $programa = $em->getRepository('LinkComunBundle:CertiPagina')->find($np->getEntidadId());
                    $valores[] = $programa->getNombre();
                }
                $entidades = array('tipo' => 'table',
                                   'valores' => $valores);
                break;
            case $yml['parameters']['tipo_destino']['en_curso']:
                $nps = $em->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByGrupo($notificacion_programada->getId());
                $valores = array();
                foreach ($nps as $np)
                {
                    $programa = $em->getRepository('LinkComunBundle:CertiPagina')->find($np->getEntidadId());
                    $valores[] = $programa->getNombre();
                }
                $entidades = array('tipo' => 'table',
                                   'valores' => $valores);
                break;
        }

        return $this->render('LinkBackendBundle:Notificacion:showNotificacionProgramada.html.twig', array('notificacion_programada' => $notificacion_programada,
                                                                                                          'entidades' => $entidades));

    }

    public function previewnAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($id);
        $mensaje = $notificacion->getMensaje();
        return new response ($notificacion->getMensaje());
    }

    public function ajaxExcelUsuariosCorreosAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
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
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $cantidad= '';

        $notificacion_programada_id = (int)$request->request->get('notificacion_programada_id');

        //return new response($notificacion_programada_id);
        $notificacion_programada = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->find($notificacion_programada_id);

        $return = array();
        $query = $em->getConnection()->prepare('SELECT
                                                fnlistado_participantes_correos(:re, :pnotificacion_programada_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pnotificacion_programada_id', $notificacion_programada->getId(), \PDO::PARAM_INT);
        $query->execute();
        $res = $query->fetchAll();

        // Solicita el servicio de excel
        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/CorreosParticipantes.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G');

        // Encabezado
        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Listado de participantes correos enviados'));
            $row = 5;
            $i = 0;
            $styleThinBlackBorderOutline = array(
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            );
            $font_size = 11;
            $font = 'Arial';
            $horizontal_aligment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
            $vertical_aligment = \PHPExcel_Style_Alignment::VERTICAL_CENTER;
            //return new response(var_dump($res));

            foreach ($res as $re)
            {

                $correo = trim($re['correo1']) ? trim($re['correo1']) : trim($re['correo2']);
                $objWorksheet->getStyle("A$row:E$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
                $objWorksheet->getStyle("A$row:E$row")->getFont()->setSize($font_size); // Tamaño de las letras
                $objWorksheet->getStyle("A$row:E$row")->getFont()->setName($font); // Tipo de letra
                $objWorksheet->getStyle("A$row:E$row")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                $objWorksheet->getStyle("A$row:E$row")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                $objWorksheet->getRowDimension($row)->setRowHeight(40); // Altura de la fila
                // Datos de las columnas comunes
                $objWorksheet->setCellValue('A'.$row, $re['nombre']);
                $objWorksheet->setCellValue('B'.$row, $re['apellido']);
                $objWorksheet->setCellValue('C'.$row, $re['login']);
                $objWorksheet->setCellValue('D'.$row, $re['correo1']);
                $objWorksheet->setCellValue('E'.$row, $re['correo2']);
                $row++;
                $i++;

            }
                settype($i,'string');
                $objWorksheet->setCellValue('F5', $i);


        // Crea el writer


        $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel2007');
        $hoy = date('y-m-d H:i:s');
        $path ='recursos/notificaciones/'.'CorreosParticipantes_'.$hoy.'.xlsx';
        $xls = $yml['parameters']['folders']['dir_uploads'].$path;
        $writer->save($xls);
        $archivo = $yml['parameters']['folders']['uploads'].$path;
        $document_name = 'CorreosParticipantes_'.$hoy.'.xls';
        $bytes = filesize($xls);
        $document_size = $f->fileSizeConvert($bytes);


       // return $response;

        //return new response(var_dump($res));



        $return = json_encode($archivo);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}
