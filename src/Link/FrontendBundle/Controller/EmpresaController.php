<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Link\ComunBundle\Entity\AdminEmpresa;
use Doctrine\ORM\EntityRepository;

class EmpresaController extends Controller
{
    public function vistaPreviaAction($empresa_id)
    {
    	$em = $this->getDoctrine()->getManager();
    	$empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        return $this->render('LinkFrontendBundle:Empresa:vistaPrevia.html.twig', array('empresa'=>$empresa));
    }

}
