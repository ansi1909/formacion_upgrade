<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminEmpresa;
use Doctrine\ORM\EntityRepository;

class EmpresaController extends Controller
{
   public function indexAction()
    {
      $r = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa');
      $empresas = $r->findAll();

      return $this->render('LinkBackendBundle:Empresa:index.html.twig', array('empresas'=>$empresas));
    }
}
