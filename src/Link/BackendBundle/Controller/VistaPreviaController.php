<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Link\ComunBundle\Entity\AdminEmpresa;
use Link\ComunBundle\Entity\CertiPagina;
use Doctrine\ORM\EntityRepository;

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
}