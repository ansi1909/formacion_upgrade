<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Link\ComunBundle\Entity\AdminEmpresa;
use Doctrine\ORM\EntityRepository;

class PaginaController extends Controller
{
    public function vistaPreviaAction($pagina_id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
    	$subpagina= $pagina->getPagina();
    	if($subpagina)
    	{
    		return $this->render('LinkFrontendBundle:Pagina:vistaPreviaSP.html.twig', array('pagina'=>$pagina));
    	}
    	else
    	{
    		return $this->render('LinkFrontendBundle:Pagina:vistaPreviaP.html.twig', array('pagina'=>$pagina));
    	}

        
    }

}
