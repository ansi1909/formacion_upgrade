<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Symfony\Component\Yaml\Yaml;

class LeccionController extends Controller
{
    public function indexAction($programa_id, $subpagina_id, Request $request)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        /*if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa');
        }
        $f->setRequest($session->get('sesion_id'));*/

        $em = $this->getDoctrine()->getManager();

        // Menú lateral dinámico
        $menu_str = $f->menuLecciones($session->get('paginas')[$programa_id], $subpagina_id);

        return new Response('Hay que desarrollar este controlador');

        return $this->render('LinkFrontendBundle:App:index.html.twig');

    }

}
