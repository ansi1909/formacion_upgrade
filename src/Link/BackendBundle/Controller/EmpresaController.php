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
   public function indexAction($app_id)
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
        
      $r = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa');
      $empresas = $r->findAll();

      return $this->render('LinkBackendBundle:Empresa:index.html.twig', array('empresas'=>$empresas));
    }

    public function registroAction($empresa_id, Request $request){

      $session = new Session();
      $f = $this->get('funciones');
      
      if (!$session->get('ini'))
      {
          return $this->redirectToRoute('_loginAdmin');
        }
      else {
        if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')))
        {
          return $this->redirectToRoute('_authException');
        }
      }

      $em = $this->getDoctrine()->getManager();

      if ($empresa_id) 
      {
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
      }
      else {
        $empresa = new AdminEmpresa();
      } 

    }
}
