<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;

class RolController extends Controller
{
   public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $query= $em->createQuery('SELECT r FROM LinkComunBundle:AdminRol r
                                        ORDER BY r.nombre ASC');
        $roles=$query->getResult();
       
       return new Response(var_dump($roles));
            
      //return $this->render('LinkBackendBundle:Rol:index.html.twig',
        //                       array('roles'=>$roles));
    }
}
