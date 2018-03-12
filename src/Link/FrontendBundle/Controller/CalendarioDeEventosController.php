<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;

class CalendarioDeEventosController extends Controller
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
    

}