<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminEmpresa;
use Link\ComunBundle\Entity\AdminUsuario;
use Link\ComunBundle\Entity\AdminEvento;


class CalendarioController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');
        if (!$session->get('ini'))
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
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1;
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findByEmpresa($usuario->getEmpresa());
            $eventos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->findByEmpresa($usuario->getEmpresa());
        }
        else {
            $query = $em->createQuery("SELECT e FROM LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = :activo
                                       ORDER BY e.id ASC")
                        ->setParameters(array('activo' => true));
            $empresas = $query->getResult();
            
            $query2 = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                       JOIN LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = :activo 
                                       AND e.id = n.empresa
                                       ORDER BY n.id ASC")
                         ->setParameters(array('activo' => true));
            $niveles = $query2->getResult();

            $query3 = $em->createQuery("SELECT ev FROM LinkComunBundle:AdminEvento ev
                                       JOIN LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = :activo 
                                       AND e.id = ev.empresa
                                       ORDER BY ev.id ASC")
                         ->setParameters(array('activo' => true));
            $eventos = $query3->getResult();
        }

        return $this->render('LinkBackendBundle:Calendario:index.html.twig', array('empresas' => $empresas,
                                                                                   'usuario_empresa' => $usuario_empresa,
                                                                                   'eventos' => $eventos,
                                                                                   'niveles' => $niveles,
                                                                                   'app_id' => $app_id,
                                                                                   'usuario' => $usuario));
    }

    public function eventosAction($userID)
    {
        
        $em = $this->getDoctrine()->getManager();
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($userID); 

        if ($usuario->getEmpresa()) {
            $eventos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->findByEmpresa($usuario->getEmpresa()->getId());
        }
        else {
            $query = $em->createQuery("SELECT ev FROM LinkComunBundle:AdminEvento ev
                                       JOIN LinkComunBundle:AdminEmpresa e
                                       WHERE e.activo = :activo 
                                       AND e.id = ev.empresa
                                       ORDER BY ev.id ASC")
                         ->setParameters(array('activo' => true));
            $eventos = $query->getResult();
        }

        $return = array();
        
        foreach ($eventos as $evento){

            if($evento->getNivel()){
                $nombre_nivel = $evento->getNivel()->getNombre();
                $id_nivel = $evento->getNivel()->getId();
            }else{
                $nombre_nivel = 'Todos los niveles';
                $id_nivel = 0;
            }
        
            $e = array('id' => $evento->getId(),
                       'title' => $evento->getNombre(),
                       'start' => $evento->getFechaInicio()->format('Y-m-d H:i:s'),
                       'end' => $evento->getFechaFin()->format('Y-m-d H:i:s'),
                       'descripcion' => $evento->getDescripcion(),
                       'empresa' => $evento->getEmpresa()->getNombre(),
                       'empresa_id' => $evento->getEmpresa()->getId(),
                       'nivel' => $nombre_nivel,
                       'nivel_id' => $id_nivel,
                       'lugar' => $evento->getLugar()
                       );
            
            array_push($return, $e);
        
        }

        $return = json_encode($return); //json codifica el arreglo
        return new Response($return, 200, array('Content-Type' => 'application/json')); // asegurando el correcto content type
    }

    public function updateAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $evento_id = $request->request->get("evento_id");
        $empresa_admin = $request->request->get("empresa");
        $empresa_usuario = $request->request->get("empresa_usuario");
        $fecha_inicio = $request->request->get('start');
        $fecha_fin = $request->request->get('end');

        if($empresa_usuario){
            $empresa_id = $empresa_usuario;
        }else{
            $empresa_id = $empresa_admin;
        }
        $nivel_id = $request->request->get("nivel");
        $usuario_id = $request->request->get("usuario");
        if($nivel_id != 0){
            $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        }
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

        if($evento_id){
            $evento = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->find($evento_id);
        }else{
            $evento = new AdminEvento();
        }

        $evento->setNombre($request->request->get("title"));
        $evento->setDescripcion($request->request->get("descripcion"));
        $evento->setLugar($request->request->get("lugar"));
        $evento->setFechaInicio(new \DateTime($fecha_inicio));
        $evento->setFechaFin(new \DateTime($fecha_fin));
        $evento->setEmpresa($empresa);
        if($nivel_id != 0){
            $evento->setNivel($nivel);
        }
        $evento->setUsuario($usuario);
        $em->persist($evento);
        $em->flush();

        if($evento->getNivel()){
            $nombre_nivel = $evento->getNivel()->getNombre();
        }else{
            $nombre_nivel = 'Todos los niveles';
        }

        $return = array('id' => $evento->getId(),
                   'titulo' =>$evento->getNombre(),
                   'descripcion' =>$evento->getDescripcion(),
                   'lugar' =>$evento->getLugar(),
                   'empresa' =>$evento->getEmpresa()->getNombre(),
                   'nivel' =>$nombre_nivel,
                   'fechainicio' =>$evento->getFechaInicio()->format('d-m-Y G:ia'),
                   'fechafin' =>$evento->getFechaFin()->format('d-m-Y G:ia'),
                   'status' => "success",
                   'delete_disabled' =>$f->linkEliminar($evento->getId(),'AdminEvento'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function editEventDateAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $event = $request->request->get("Event");
        $evento_id = $event[0];
        $fecha_inicio = $event[1];
        $fecha_fin = $event[2];
        $evento = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->find($evento_id);

        if(!$evento){
            $return = array('status' => "error");

            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        $evento->setFechaInicio(new \DateTime($fecha_inicio));
        $evento->setFechaFin(new \DateTime($fecha_fin));
        $em->persist($evento);
        $em->flush();

        if($evento->getNivel()){
            $nombre_nivel = $evento->getNivel()->getNombre();
        }else{
            $nombre_nivel = 'Todos los niveles';
        }

        $return = array('id' => $evento->getId(),
                        'titulo' =>$evento->getNombre(),
                        'descripcion' =>$evento->getDescripcion(),
                        'lugar' =>$evento->getLugar(),
                        'empresa' =>$evento->getEmpresa()->getNombre(),
                        'nivel' =>$nombre_nivel,
                        'fechainicio' =>$evento->getFechaInicio()->format('d-m-Y G:ia'),
                        'fechafin' =>$evento->getFechaFin()->format('d-m-Y G:ia'),
                        'status' => "success",
                        'delete_disabled' =>$f->linkEliminar($evento->getId(),'AdminEvento'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}