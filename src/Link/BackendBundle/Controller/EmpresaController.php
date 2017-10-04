<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminEmpresa;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


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

      $r = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais');
      $paises = $r->findAll();
      return $this->render('LinkBackendBundle:Empresa:registro.html.twig', array('paises' => $paises));

    }

    public function guardarEmpresaAction(Request $request)
    {
      
    }

    public function ajaxActiveEmpresaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $empresa_id = $request->request->get('app_id');
        $checked = $request->request->get('checked');

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $empresa->setActivo($checked ? true : false);
        $em->persist($empresa);
        $em->flush();
                    
        $return = array('id' => $empresa->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
}
