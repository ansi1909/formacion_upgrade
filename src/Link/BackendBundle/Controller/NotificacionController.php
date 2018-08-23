<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNotificacion;
use Link\ComunBundle\Entity\AdminTipoNotificacion;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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

}
