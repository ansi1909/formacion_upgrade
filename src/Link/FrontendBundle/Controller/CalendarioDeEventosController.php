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

    public function indexAction()
    {
       $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $usuario_id = $session->get('usuario')['id'];

        return $this->render('LinkFrontendBundle:CalendarioDeEventos:index.html.twig', array('usuario_id' => $usuario_id));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;

    }

    public function eventosAction($userID)
    {
        
        $em = $this->getDoctrine()->getManager();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($userID); 

        $consult_eventos = $em->createQuery('SELECT ae FROM LinkComunBundle:AdminEvento ae
                                             JOIN LinkComunBundle:AdminEmpresa e 
                                             WHERE e.id = :empresa_id
                                             AND e.id = ae.empresa
                                             AND ae.nivel IS NULL
                                             OR ae.nivel = :nivel_usuario
                                             ORDER BY ae.id DESC')
                              ->setParameters(array('empresa_id' => $usuario->getEmpresa()->getId(),
                                                    'nivel_usuario' => $usuario->getNivel()->getId()));
        $eventos = $consult_eventos->getResult();

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