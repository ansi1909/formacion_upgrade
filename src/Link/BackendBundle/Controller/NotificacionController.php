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
        $notificacionesdb = array();
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 
        $tipo_notificaciones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoNotificacion')->findAll();

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
            $notificaciones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->findByEmpresa($usuario->getEmpresa());
        }
        else {
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = :activo
                                       ORDER BY e.id ASC")
                        ->setParameters(array('activo' => true));
            $empresas = $query->getResult();
            
            $query2 = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNotificacion n
                                       JOIN LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = :activo 
                                       AND e.id = n.empresa
                                       ORDER BY n.id ASC")
                         ->setParameters(array('activo' => true));
            $notificaciones = $query2->getResult();
        }

         foreach ($notificaciones as $notificacion)
        {
            $notificacionesdb[]= array('id'=>$notificacion->getId(),
                                       'asunto'=>$notificacion->getAsunto(),
                                       'empresa'=>$notificacion->getEmpresa()->getNombre(),
                                       'tipo_notificacion'=>$notificacion->getTipoNotificacion()->getNombre(),
                                       'delete_disabled'=>$f->linkEliminar($notificacion->getId(),'AdminNotificacion'));

        }


        return $this->render('LinkBackendBundle:Notificacion:index.html.twig', array('empresas' => $empresas,
                                                                                     'usuario_empresa' => $usuario_empresa,
                                                                                     'notificaciones' => $notificacionesdb,
                                                                                     'tipo_notificaciones' => $tipo_notificaciones,
                                                                                     'usuario' => $usuario));
    }

    public function ajaxNotificacionAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $usuario_empresa = $request->query->get('usuario_empresa');
        $f = $this->get('funciones');

        $qb = $em->createQueryBuilder();

        if ($empresa_id)
        {
            $qb->select('u')
               ->from('LinkComunBundle:AdminNotificacion', 'u');
            $qb->andWhere('u.empresa = :empresa_id');
            $parametros['empresa_id'] = $empresa_id;
            $qb->setParameters($parametros);
        }else{
            $qb->select('u')
               ->from('LinkComunBundle:AdminNotificacion', 'u');
        }

        $query = $qb->getQuery();
        $notificaciones_db = $query->getResult();
        $notificaciones = '';
        $notificaciones .= '<table class="table" id="dt">
                            <thead class="sty__title">
                                <tr>
                                    <th>'.$this->get('translator')->trans('Asunto').'</th>';
                                    if ($usuario_empresa == 0){
                                        $notificaciones .= '<th>'.$this->get('translator')->trans('Empresa').'</th>';
                                    }
                                    $notificaciones .= '<th>'.$this->get('translator')->trans('Tipo notificación').'</th>
                                    <th>'.$this->get('translator')->trans('Acciones').'</th>
                                </tr>
                            </thead>
                            <tbody>';

        foreach ($notificaciones_db as $notificacion) {
            $delete_disabled = $f->linkEliminar($notificacion->getId(), 'AdminNotificacion');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $notificaciones .= '<tr><td>'.$notificacion->getAsunto().'</td><td>'.$notificacion->getEmpresa()->getNombre().'</td><td>'.$notificacion->getTipoNotificacion()->getNombre().'</td>
            <td class="center">
                <a href="'.$this->generateUrl('_editNotificacion', array('notificacion_id' => $notificacion->getId())).'" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit"><span class="fa fa-pencil"></span></a>
                <a href="'.$this->generateUrl('_showNotificacion', array('notificacion_id' => $notificacion->getId(), 'status' => 'show')).'" title="'.$this->get('translator')->trans('Ver').'" class="btn btn-link btn-sm"><span class="fa fa-eye"></span></a>
                <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$notificacion->getId().'"><span class="fa fa-trash"></span></a>
            </td> </tr>';
        }
        $notificaciones .= '</tbody>
                        </table>';
        
        $return = array('notificaciones' => $notificaciones);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function createNotificacionAction(Request $request)
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
        $notificacion = new AdminNotificacion();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $usuario_empresa = 0;

        $notificacion->setUsuario($usuario);

        if ($usuario->getEmpresa()) {

            $usuario_empresa = 1;
            $notificacion->setEmpresa($usuario->getEmpresa());

            $form = $this->createFormBuilder($notificacion)
                ->setAction($this->generateUrl('_createNotificacion'))
                ->setMethod('POST')
                ->add('asunto', TextType::class, array('label' => $this->get('translator')->trans('Asunto')))
                ->add('mensaje', TextareaType::class, array('label' => $this->get('translator')->trans('Mensaje')))
                ->add('tipoNotificacion', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminTipoNotificacion',
                                                            'choice_label' => 'nombre',
                                                            'expanded' => false,
                                                            'label' => $this->get('translator')->trans('Tipo notificación'),
                                                            'placeholder' => ''))
                ->getForm();
                
        }else{

            $form = $this->createFormBuilder($notificacion)
                ->setAction($this->generateUrl('_createNotificacion'))
                ->setMethod('POST')
                ->add('asunto', TextType::class, array('label' => $this->get('translator')->trans('Asunto')))
                ->add('mensaje', TextareaType::class, array('label' => $this->get('translator')->trans('Mensaje')))
                ->add('tipoNotificacion', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminTipoNotificacion',
                                                                   'choice_label' => 'nombre',
                                                                   'expanded' => false,
                                                                   'label' => $this->get('translator')->trans('Tipo notificación'),
                                                                   'placeholder' => ''))
                ->add('empresa', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminEmpresa',
                                                          'choice_label' => 'nombre',
                                                          'expanded' => false,
                                                          'label' => $this->get('translator')->trans('Empresa'),
                                                          'placeholder' => '',
                                                          'query_builder' => function(EntityRepository $er){
                                                             return $er->createQueryBuilder('e')
                                                                      ->where('e.activo = ?1')
                                                                      ->setParameter(1, true)
                                                                      ->orderBy('e.nombre', 'ASC');
                                                         }))
                ->getForm();
                
        }

        $form->handleRequest($request);

        
        if ($request->getMethod() == 'POST')
        {

            $em->persist($notificacion);
            $em->flush();

            return $this->redirectToRoute('_showNotificacion', array('notificacion_id' => $notificacion->getId(), 'status'=> 'exito'));
            
        }
        
        return $this->render('LinkBackendBundle:Notificacion:newNotificacion.html.twig', array('form' => $form->createView(),
                                                                                               'usuario_empresa' => $usuario_empresa,
                                                                                               'usuario' => $usuario));
        
    }

    public function showNotificacionAction(Request $request, $notificacion_id, $status)
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

            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) or $usuario->getEmpresa() and $usuario->getEmpresa()->getId() != $notificacion->getEmpresa()->getId())
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));
        
        $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        $mensaje = $status;
        if($mensaje == "exito"){
            $mensaje = 1;
        }else{
            $mensaje = 0;
        }
        $usuario_empresa = 0;

        if ($usuario->getEmpresa()) {

            $usuario_empresa = 1;
        }

        return $this->render('LinkBackendBundle:Notificacion:showNotificacion.html.twig', array('notificacion' => $notificacion,
                                                                                                'mensaje' => $mensaje,
                                                                                                'usuario_empresa' => $usuario_empresa,
                                                                                                'usuario' => $usuario));

    }

    public function editNotificacionAction(Request $request, $notificacion_id)
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

            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) or $usuario->getEmpresa() and $usuario->getEmpresa()->getId() != $notificacion->getEmpresa()->getId())
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        $usuario_empresa = 0;

        $notificacion->setUsuario($usuario);

        if ($usuario->getEmpresa()) {

            $usuario_empresa = 1;
            $notificacion->setEmpresa($usuario->getEmpresa());

            $form = $this->createFormBuilder($notificacion)
                ->setAction($this->generateUrl('_editNotificacion', array('notificacion_id' => $notificacion->getId())))
                ->setMethod('POST')
                ->add('asunto', TextType::class, array('label' => $this->get('translator')->trans('Asunto')))
                ->add('mensaje', TextareaType::class, array('label' => $this->get('translator')->trans('Mensaje')))
                ->add('tipoNotificacion', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminTipoNotificacion',
                                                                   'choice_label' => 'nombre',
                                                                   'expanded' => false,
                                                                   'label' => $this->get('translator')->trans('Tipo notificación'),
                                                                   'placeholder' => ''))
                ->getForm();
                
        }else{

            $form = $this->createFormBuilder($notificacion)
                ->setAction($this->generateUrl('_editNotificacion', array('notificacion_id' => $notificacion->getId())))
                ->setMethod('POST')
                ->add('asunto', TextType::class, array('label' => $this->get('translator')->trans('Asunto')))
                ->add('mensaje', TextareaType::class, array('label' => $this->get('translator')->trans('Mensaje')))
                ->add('tipoNotificacion', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminTipoNotificacion',
                                                                   'choice_label' => 'nombre',
                                                                   'expanded' => false,
                                                                   'label' => $this->get('translator')->trans('Tipo notificación'),
                                                                   'placeholder' => ''))
                ->add('empresa', EntityType::class, array('class' => 'Link\\ComunBundle\\Entity\\AdminEmpresa',
                                                          'choice_label' => 'nombre',
                                                          'expanded' => false,
                                                          'label' => $this->get('translator')->trans('Empresa'),
                                                          'placeholder' => '',
                                                          'query_builder' => function(EntityRepository $er){
                                                             return $er->createQueryBuilder('e')
                                                                      ->where('e.activo = ?1')
                                                                      ->setParameter(1, true)
                                                                      ->orderBy('e.nombre', 'ASC');
                                                         }))
                ->getForm();
                
        }

        $form->handleRequest($request);

        
        if ($request->getMethod() == 'POST')
        {

            $em->persist($notificacion);
            $em->flush();

            return $this->redirectToRoute('_showNotificacion', array('notificacion_id' => $notificacion->getId(), 'status'=> 'exito'));
            
        }
        
        return $this->render('LinkBackendBundle:Notificacion:editNotificacion.html.twig', array('form' => $form->createView(),
                                                                                                'usuario_empresa' => $usuario_empresa,
                                                                                                'usuario' => $usuario));
        
    }

}
