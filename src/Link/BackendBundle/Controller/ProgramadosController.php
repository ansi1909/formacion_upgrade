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


class ProgramadosController extends Controller
{
   public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        $session->set('app_id', $app_id);
        $notificacionesdb = array();

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
            $notificaciones_programadas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNotificacionProgramada')->findByNotificacion($notificacion->getId());
            foreach ($notificaciones_programadas as $notificacion_programada) {

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

                $notificacionesdb[]= array('id'=>$notificacion_programada->getId(),
                              'tipo_destino'=>$notificacion_programada->getTipoDestino()->getNombre(),
                              'entidad'=>$entidad,
                              'fecha_difusion'=>$notificacion_programada->getFechaDifusion(),
                              'delete_disabled'=>$f->linkEliminar($notificacion->getId(),'AdminNotificacionProgramada'));
            }
            

        }


        return $this->render('LinkBackendBundle:Programados:index.html.twig', array('empresas' => $empresas,
                                                                                        'usuario_empresa' => $usuario_empresa,
                                                                                        'notificaciones' => $notificacionesdb,
                                                                                        'usuario' => $usuario));
    }

   /*public function ajaxUpdateRolAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $rol_id = $request->request->get('rol_id');
        $nombre = $request->request->get('rol');
        $descripcion = $request->request->get('descripcion');

        if ($rol_id)
        {
            $rol = $em->getRepository('LinkComunBundle:AdminRol')->find($rol_id);
        }
        else {
            $rol = new AdminRol();
        }

        $rol->setNombre($nombre);
        $rol->setDescripcion($descripcion);
        
        $em->persist($rol);
        $em->flush();
                    
        $return = array('id' => $rol->getId(),
                        'nombre' =>$rol->getNombre(),
                        'descripcion' =>$rol->getDescripcion(),
                        'delete_disabled' =>$f->linkEliminar($rol->getId(),'AdminRol'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditRolAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $rol_id = $request->query->get('rol_id');
                
        $rol = $this->getDoctrine()->getRepository('LinkComunBundle:AdminRol')->find($rol_id);

        $query = $em->createQuery("SELECT r FROM LinkComunBundle:AdminRol r 
                                    WHERE r.nombre IS NULL 
                                    ORDER BY r.id ASC");
        $apps = $query->getResult();


        $return = array('nombre' => $rol->getNombre(),
                        'descripcion' => $rol->getDescripcion());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }*/

}
