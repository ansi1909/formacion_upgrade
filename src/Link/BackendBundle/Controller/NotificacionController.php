<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNotificacion;
use Link\ComunBundle\Entity\AdminNotificacionProgramada;
use Link\ComunBundle\Entity\AdminTipoNotificacion;
use Link\ComunBundle\Entity\AdminNivel;
use Link\ComunBundle\Entity\CertiPagina;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class NotificacionController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $session->set('app_id', $app_id);
        if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
        {
            return $this->redirectToRoute('_authException');
        }
        $f->setRequest($session->get('sesion_id'));

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 
        $tipo_notificaciones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoNotificacion')->findAll();

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
            $notificaciones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->findByEmpresa($usuario->getEmpresa());
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
            $notificaciones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->findAll();
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
        $f = $this->get('funciones');

        $qb = $em->createQueryBuilder();
        $qb->select('u')
           ->from('LinkComunBundle:AdminNotificacion', 'u');
        $qb->andWhere('u.empresa = :empresa_id');
        $parametros['empresa_id'] = $empresa_id;

        if ($empresa_id)
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $notificaciones_db = $query->getResult();
        $notificaciones = '';

        foreach ($notificaciones_db as $notificacion) {
            $delete_disabled = $f->linkEliminar($notificacion->getId(), 'AdminNotificacion');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $notificaciones .= '<tr><td>'.$notificacion->getAsunto().'</td><td>'.$notificacion->getEmpresa()->getNombre().'</td><td>'.$notificacion->getTipoNotificacion()->getNombre().'</td>
            <td class="center">
                <a href="#" title="'.$this->get('translator')->trans('Editar').'" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$notificacion->getId().'"><span class="fa fa-pencil"></span></a>
                <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$notificacion->getId().'"><span class="fa fa-trash"></span></a>
                <a href="#" title="'.$this->get('translator')->trans('Ver Historial').'" class="btn btn-link btn-sm see" data="'.$notificacion->getId().'"><span class="fa fa-eye"></span></a>
            </td> </tr>';
        }
        
        $return = array('notificaciones' => $notificaciones);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxUpdateNotificacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $notificacion_id = $request->request->get('notificacion_id');
        $asunto = $request->request->get('asunto');
        $mensaje = $request->request->get('mensaje');
        $tipo_notificacion_id = $request->request->get('tipo_notificacion_id');
        $tipo_notificacion = $em->getRepository('LinkComunBundle:AdminTipoNotificacion')->find($tipo_notificacion_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        if ($usuario->getEmpresa()) {
            $empresa_id = $usuario->getEmpresa()->getId();
        }else{
            $empresa_id = $request->query->get('form_empresa_id');
        }
        

        if ($notificacion_id)
        {
            $notificacion = $em->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        }
        else {
            $notificacion = new AdminNotificacion();
        }

        $notificacion->setAsunto($asunto);
        $notificacion->setMensaje($mensaje);
        $notificacion->setTipoNotificacion($tipo_notificacion->getId());
        $notificacion->setUsuario($usuario->getId());
        
        $em->persist($notificacion);
        $em->flush();
                    
        $return = array('id' => $notificacion->getId(),
                        'asunto'=>$notificacion->getAsunto(),
                        'empresa'=>$notificacion->getEmpresa()->getNombre(),
                        'tipo_notificacion'=>$notificacion->getTipoNotificacion()->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($notificacion->getId(),'AdminNotificacion'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditNotificacionAction(Request $request)
    {
        
        /*$em = $this->getDoctrine()->getManager();
        $tipo_notificacion_id = $request->query->get('tipo_notificacion_id');
                
        $tipo_notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTipoNotificacion')->find($tipo_notificacion_id);

        $query = $em->createQuery("SELECT r FROM LinkComunBundle:AdminTipoNotificacion r 
                                    WHERE r.nombre IS NULL 
                                    ORDER BY r.id ASC");
        $apps = $query->getResult();


        $return = array('nombre' => $tipo_notificacion->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));*/
        
    }

    public function ajaxHistoryProgramationsAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $notificacion_id = $request->query->get('notificacion_id');
        $html = '';
        
        $notificacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacion')->find($notificacion_id);
        $notificaciones_programadas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByNotificacion($notificacion_id);

        foreach ($notificaciones_programadas as $notificacion_programada)
        {
            $checked = $notificacion_programada->getActivo() ? 'checked' : '';
            $delete_disabled = $f->linkEliminar($notificacion_programada->getId(), 'AdminNotificacionProgramada');
            $delete = $delete_disabled=='' ? 'delete' : '';
            if($notificacion_programada->getTipoDestino()->getNombre() == 'Programa'){

                $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($notificacion_programada->getEntidadId());
                $entidad = $programa->getNombre();

            }elseif($notificacion_programada->getTipoDestino()->getNombre() == 'Nivel'){
                $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($notificacion_programada->getEntidadId());
                $entidad = $nivel->getNombre();
            }else{
                $entidad = 'N/A';
            }
            $html .= '<tr>
                        <td>'.$notificacion_programada->getTipoDestino()->getNombre().'</td>
                        <td>'.$entidad.'</td>
                        <td>'.$notificacion_programada->getFechaDifusion().'</td>
                        <td class="center">
                            <a href="#" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$notificacion_programada->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$notificacion_programada->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
        }

        $return = array('html' => $html,
                        'notificacion' => $notificacion->getAsunto());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}
