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

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 

        return $this->render('LinkBackendBundle:Notificacion:index.html.twig', array('empresas' => $empresas,
                                                                                        'usuario_empresa' => $usuario_empresa,
                                                                                        'usuario' => $usuario));
    }

    public function ajaxNotificacionAction(Request $request)
    {

        /*$em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $nivel_id = $request->query->get('nivel_id');
        $f = $this->get('funciones');

        $qb = $em->createQueryBuilder();
        $qb->select('u')
           ->from('LinkComunBundle:AdminUsuario', 'u');
        $qb->andWhere('u.empresa = :empresa_id');
        $parametros['empresa_id'] = $empresa_id;

        if ($nivel_id)
        {
            $qb->andWhere('u.nivel = :nivel_id');
            $parametros['nivel_id'] = $nivel_id;
        }

        if ($empresa_id || $nivel_id)
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $usuarios_db = $query->getResult();
        $usuarios = '';

        foreach ($usuarios_db as $usuario) {
            $delete_disabled = $f->linkEliminar($usuario->getId(), 'AdminUsuario');
            $class_delete = $delete_disabled == '' ? 'delete' : '';
            $usuarios .= '<tr><td>'.$usuario->getNombre().'</td><td>'.$usuario->getApellido().'</td><td>'.$usuario->getNivel()->getNombre().'</td>
            <td class="center">
                <a href="'.$this->generateUrl('_nuevoParticipante', array('usuario_id' => $usuario->getId())).'" class="btn btn-link btn-sm"><span class="fa fa-pencil"></span></a>
                <a href="#" class="btn btn-link btn-sm '.$class_delete.' '.$delete_disabled.'" data="'.$usuario->getId().'"><span class="fa fa-trash"></span></a>
            </td> </tr>';
        }
        
        $return = array('usuarios' => $usuarios);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));*/

    }

    public function ajaxUpdateNotificacionAction(Request $request)
    {
        
        /*$em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $tipo_notificacion_id = $request->request->get('tipo_notificacion_id');
        $nombre = $request->request->get('tipo_notificacion');

        if ($tipo_notificacion_id)
        {
            $tipo_notificacion = $em->getRepository('LinkComunBundle:AdminTipoNotificacion')->find($tipo_notificacion_id);
        }
        else {
            $tipo_notificacion = new AdminTipoNotificacion();
        }

        $tipo_notificacion->setNombre($nombre);
        
        $em->persist($tipo_notificacion);
        $em->flush();
                    
        $return = array('id' => $tipo_notificacion->getId(),
                        'nombre' =>$tipo_notificacion->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($tipo_notificacion->getId(),'AdminTipoNotificacion'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));*/
        
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

}
