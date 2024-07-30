<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Session\Session;

class VistaPreviaController extends Controller
{
    public function vistaPreviaBAction($empresa_id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa_id);
        $layout = 'base.html.twig';
        if ($preferencia)
        {
            $layout = $preferencia->getLayout()->getTwig();
        }

        return $this->render('LinkBackendBundle:VistaPrevia:vistaPreviaB.html.twig', array('empresa'=>$empresa,
                                                                                           'layout' => $layout));
    }

    public function vistaPreviaPAction($pagina_id)
    {

        $em = $this->getDoctrine()->getManager();
        
        $logo = '';
        $tipo_logo = 'imgLogoHor';
        $favicon = '';
        $title = '';
        $css = '';
        $plantilla = 'base.html.twig';
        $titulo = '';
        $subtitulo = '';

        $empresa_arr = array('id' => 0,
                             'nombre' => 'Formación Smart',
                             'plantilla' => $plantilla,
                             'logo' => $logo,
                             'tipo_logo' => $tipo_logo,
                             'favicon' => $favicon,
                             'titulo' => $title,
                             'css' => $css);

        $session = new Session();
        $session->set('empresa', $empresa_arr);

        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        if ($pagina->getPagina())
        {
            $titulo =  $pagina->getPagina()->getCategoria()->getNombre().': '.$pagina->getPagina()->getNombre();
            $subtitulo = $pagina->getCategoria()->getNombre().': '.$pagina->getNombre();
        }
        else {
            $titulo = $pagina->getCategoria()->getNombre().': '.$pagina->getNombre();
        }

        return $this->render('LinkBackendBundle:VistaPrevia:vistaPreviaP.html.twig', array('pagina' => $pagina,
                                                                                           'titulo' => $titulo,
                                                                                           'subtitulo' => $subtitulo));
    	
    }

    public function vistaPreviaDashboardAction($empresa_id)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa_id);

        if ($preferencia)
        {
            $logo = $preferencia->getLogo() ? $preferencia->getLogo() : '';
            $tipo_logo = $preferencia->getTipoLogo() ? $preferencia->getTipoLogo()->getCss() : 'imgLogoHor';
            $favicon = $preferencia->getFavicon();
            $title = $preferencia->getTitle();
            $css = $preferencia->getCss();
            $plantilla = $preferencia->getLayout()->getTwig();
        }
        else {
            $logo = '';
            $tipo_logo = 'imgLogoHor';
            $favicon = '';
            $title = '';
            $css = '';
            $plantilla = 'base.html.twig';
        }

        $empresa_arr = array('id' => $empresa_id,
                             'nombre' => $empresa->getNombre(),
                             'plantilla' => $plantilla,
                             'logo' => $logo,
                             'tipo_logo' => $tipo_logo,
                             'favicon' => $favicon,
                             'titulo' => $title,
                             'css' => $css);

        $session = new Session();
        $session->set('empresa', $empresa_arr);

        return $this->render('LinkBackendBundle:VistaPrevia:vistaPreviaDashboard.html.twig', array('empresa' => $empresa));
    }

}