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
                                                                                   'usuario' => $usuario));
    }

    public function eventosAction($userID)
    {
        
        $em = $this->getDoctrine()->getManager();
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($userID); 

        if ($usuario->getEmpresa()) {
            $eventos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->findByEmpresa($usuario->getEmpresa());
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
        
            $e = array('id' => $evento->getId(),
                       'title' => $evento->getNombre(),
                       'start' => $evento->getFechaInicio()->format('Y-m-d H:i:s'),
                       'end' => $evento->getFechaFin()->format('Y-m-d H:i:s'),
                       'descripcion' => $evento->getDescripcion(),
                       'empresa' => $evento->getEmpresa()->getNombre(),
                       'empresa_id' => $evento->getEmpresa()->getId(),
                       'nivel' => $evento->getNivel()->getNombre(),
                       'nivel_id' => $evento->getNivel()->getId(),
                       'lugar' => $evento->getLugar()
                       );
            
            array_push($return, $e);
        
        }

        $return = json_encode($return); //json codifica el arreglo
        return new Response($return, 200, array('Content-Type' => 'application/json')); // asegurando el correcto content type
    }

    public function createAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $format = "d-m-Y H:i:s";
        $empresa_admin = $request->request->get("empresa");
        $empresa_usuario = $request->request->get("empresa_usuario");
        $fecha_inicio = $request->request->get("start_date");
        $fecha_fin = $request->request->get("end_date");
        if($empresa_usuario){
            $empresa_id = $empresa_usuario;
        }else{
            $empresa_id = $empresa_admin;
        }
        $nivel_id = $request->request->get("nivel");
        $usuario_id = $request->request->get("usuario");
        $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

        $evento = new AdminEvento();
        $evento->setNombre($request->request->get("title"));
        $evento->setDescripcion($request->request->get("descripcion"));
        $evento->setLugar($request->request->get("lugar"));
        $evento->setFechaInicio(new \DateTime($fecha_inicio));
        $evento->setFechaFin(new \DateTime($fecha_fin));
        $evento->setEmpresa($empresa);
        $evento->setNivel($nivel);
        $evento->setUsuario($usuario);
        $em->persist($evento);
        $em->flush();

        return new JsonResponse(array(
            "status" => "success"
        ));
    }
    
    public function updateAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $evento_id = $request->request->get("evento_id");
        $empresa_admin = $request->request->get("empresa");
        $empresa_usuario = $request->request->get("empresa_usuario");
        $fecha_inicio = $request->request->get("start_date");
        $fecha_fin = $request->request->get("end_date");
        if($empresa_usuario){
            $empresa_id = $empresa_usuario;
        }else{
            $empresa_id = $empresa_admin;
        }
        $nivel_id = $request->request->get("nivel");
        $usuario_id = $request->request->get("usuario");
        $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);


        if(!$evento){
            return new JsonResponse(array(
                "status" => "error",
                "message" => "The evento to update $evento_id doesn't exist."
            ));
        }

        $evento->setNombre($request->request->get("title"));
        $evento->setDescripcion($request->request->get("descripcion"));
        $evento->setLugar($request->request->get("lugar"));
        $evento->setFechaInicio(new \DateTime($fecha_inicio));
        $evento->setFechaFin(new \DateTime($fecha_fin));
        $evento->setEmpresa($empresa);
        $evento->setNivel($nivel);
        $evento->setUsuario($usuario);
        $em->persist($evento);
        $em->flush();

        return new JsonResponse(array(
            "status" => "success"
        ));
    }

    public function deleteAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $evento_id = $request->request->get("evento_id");
        $evento = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->find($evento_id);

        if(!$evento){
            return new JsonResponse(array(
                "status" => "error",
                "message" => "The given evento $evento_id doesn't exist."
            ));
        }

        $em->remove($evento);
        $em->flush();       

        return new JsonResponse(array(
            "status" => "success"
        ));
    }
}