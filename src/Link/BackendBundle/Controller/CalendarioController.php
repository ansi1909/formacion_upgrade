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
use Symfony\Component\Yaml\Yaml;


class CalendarioController extends Controller
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
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) 
        {
            $eventos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->findByEmpresa($usuario->getEmpresa()->getId());
            $empresas = array();
        }
        else {
            $query = $em->createQuery("SELECT ev FROM LinkComunBundle:AdminEvento ev
                                       JOIN ev.empresa e 
                                       WHERE e.activo = :activo 
                                       ORDER BY ev.id ASC")
                         ->setParameter('activo', true);
            $eventos = $query->getResult();
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findByActivo(true);
        }

        return $this->render('LinkBackendBundle:Calendario:index.html.twig', array('empresas' => $empresas,
                                                                                   'eventos' => $eventos,
                                                                                   'usuario' => $usuario));
    }

    public function eventosAction()
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $eventos = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->findByEmpresa($usuario->getEmpresa()->getId());
        }
        else {
            $query = $em->createQuery("SELECT ev FROM LinkComunBundle:AdminEvento ev
                                       JOIN ev.empresa e 
                                       WHERE e.activo = :activo 
                                       ORDER BY ev.id ASC")
                         ->setParameter('activo', true);
            $eventos = $query->getResult();
        }

        $return = array();
        
        foreach ($eventos as $evento)
        {

            if ($evento->getNivel())
            {
                $nombre_nivel = $evento->getNivel()->getNombre();
                $id_nivel = $evento->getNivel()->getId();
            }
            else{
                $nombre_nivel = $this->get('translator')->trans('Todos los niveles');
                $id_nivel = 0;
            }

            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $evento->getEmpresa()->getId()),
                                                                                                 array('nombre' => 'ASC'));
            $options = '<option value=""></option>';
            foreach ($niveles as $nivel)
            {
                $selected = $nivel->getId() == $id_nivel ? 'selected' : '';
                $options .= '<option value="'.$nivel->getId().'" '.$selected.'>'.$nivel->getNombre().'</option>';
            }
            if($selected)
            {
                $options .= '<option value="">'.$this->get('translator')->trans('Todos los niveles').'</option>';
            }else{
                $options .= '<option value="" selected>'.$this->get('translator')->trans('Todos los niveles').'</option>';
            }
        
            $e = array('id' => $evento->getId(),
                       'title' => $evento->getNombre(),
                       'start' => $evento->getFechaInicio()->format('Y-m-d H:i:s'),
                       'end' => $evento->getFechaFin()->format('Y-m-d H:i:s'),
                       'descripcion' => $evento->getDescripcion(),
                       'empresa' => $evento->getEmpresa()->getNombre(),
                       'empresa_id' => $evento->getEmpresa()->getId(),
                       'nivel' => $nombre_nivel,
                       'nivel_id' => $options,
                       'lugar' => $evento->getLugar()
                       );
            
            array_push($return, $e);
        
        }

        $return = json_encode($return); //json codifica el arreglo
        return new Response($return, 200, array('Content-Type' => 'application/json')); // asegurando el correcto content type
    }

    public function updateAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $evento_id = $request->request->get("evento_id");
        $fecha_inicio = $request->request->get('start');
        $fecha_fin = $request->request->get('end');
        $empresa_id = $request->request->get("empresa_id");
        $nivel_id = $request->request->get("nivel_id");
        $hoy('Y-m-d h:i:s');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if ($nivel_id != 0)
        {
            $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        }
        else {
            $nivel = null;
        }
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($evento_id)
        {
            $evento = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEvento')->find($evento_id);
        }
        else {
            $evento = new AdminEvento();
        }

        $evento->setNombre($request->request->get("title"));
        $evento->setDescripcion($request->request->get("descripcion"));
        $evento->setLugar($request->request->get("lugar"));
        $evento->setFechaInicio(new \DateTime($fecha_inicio));
        $evento->setFechaFin(new \DateTime($fecha_fin));
        $evento->setEmpresa($empresa);
        $evento->setNivel($nivel);
        $evento->setUsuario($usuario);
        $evento->setFechaCreacion(new \DateTime($hoy));
        $em->persist($evento);
        $em->flush();

        if (!$evento_id)
        {

            // Generación de alarma
            if ($nivel_id != 0)
            {
                $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                                            WHERE u.activo = :activo 
                                            AND u.nivel = :nivel_id')
                            ->setParameters(array('activo' => true,
                                                  'nivel_id' => $nivel_id));
            }
            else {
                $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                                            WHERE u.activo = :activo 
                                            AND u.empresa = :empresa_id')
                            ->setParameters(array('activo' => true,
                                                  'empresa_id' => $empresa_id));
            }
             
            $alarma_usuarios = $query->getResult();

            $descripcion = $this->get('translator')->trans('Ha sido publicado el evento').' '. $evento->getNombre() .'.';
            foreach($alarma_usuarios as $usuario){
                $f->newAlarm($yml['parameters']['tipo_alarma']['evento'], $descripcion, $usuario, $evento->getId());
            }

        }

        if ($evento->getNivel())
        {
            $nombre_nivel = $evento->getNivel()->getNombre();
        }
        else{
            $nombre_nivel = $this->get('translator')->trans('Todos los niveles');
        }

        $return = array('id' => $evento->getId(),
                        'titulo' => $evento->getNombre(),
                        'descripcion' => $evento->getDescripcion(),
                        'lugar' => $evento->getLugar(),
                        'empresa' => $evento->getEmpresa()->getNombre(),
                        'nivel' => $nombre_nivel,
                        'fechainicio' => $evento->getFechaInicio()->format('d-m-Y G:ia'),
                        'fechafin' => $evento->getFechaFin()->format('d-m-Y G:ia'),
                        'status' => "success",
                        'delete_disabled' => $f->linkEliminar($evento->getId(),'AdminEvento'));

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

        if (!$evento)
        {
            $return = array('status' => "error");
            $return = json_encode($return);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        $evento->setFechaInicio(new \DateTime($fecha_inicio));
        $evento->setFechaFin(new \DateTime($fecha_fin));
        $em->persist($evento);
        $em->flush();

        if ($evento->getNivel())
        {
            $nombre_nivel = $evento->getNivel()->getNombre();
        }
        else {
            $nombre_nivel = $this->get('translator')->trans('Todos los niveles');
        }

        $return = array('id' => $evento->getId(),
                        'titulo' => $evento->getNombre(),
                        'descripcion' => $evento->getDescripcion(),
                        'lugar' => $evento->getLugar(),
                        'empresa' => $evento->getEmpresa()->getNombre(),
                        'nivel' => $nombre_nivel,
                        'fechainicio' => $evento->getFechaInicio()->format('d-m-Y G:ia'),
                        'fechafin' => $evento->getFechaFin()->format('d-m-Y G:ia'),
                        'status' => "success",
                        'delete_disabled' => $f->linkEliminar($evento->getId(),'AdminEvento'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}