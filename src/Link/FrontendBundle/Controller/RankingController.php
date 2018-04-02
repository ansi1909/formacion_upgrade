<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Link\ComunBundle\Entity\CertiPaginaLog;

class RankingController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
        }
        

        /*return $this->render('LinkFrontendBundle:Ranking:index.html.twig', array('pagina' => $pagina,
                                                                                  'modulos' =>$modulos,
                                                                                  'porcentaje_avance' =>$porcentaje_avance,
                                                                                  'lis_mods' =>$lis_mods));*/

        return $this->render('LinkFrontendBundle:Ranking:index.html.twig');


        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response; 

    }

}