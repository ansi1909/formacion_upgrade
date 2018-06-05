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
    	$pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
    	$subpagina= $pagina->getPagina();
    	if($subpagina)
    	{
    		return $this->render('LinkBackendBundle:VistaPrevia:vistaPreviaSP.html.twig', array('pagina'=>$pagina));
    	}
    	else
    	{
    		return $this->render('LinkBackendBundle:VistaPrevia:vistaPreviaP.html.twig', array('pagina'=>$pagina));
    	}
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