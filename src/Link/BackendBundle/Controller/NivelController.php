<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNivel; 
use Link\ComunBundle\Entity\AdminEmpresa; 

class NivelController extends Controller
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


        $em = $this->getDoctrine()->getManager();

        $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        $niveles = array();
                
        foreach ($empresas as $empresa)
        {
            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                       WHERE n.empresa = :empresa_id
                                       ORDER BY n.id ASC")
                        ->setParameter('empresa_id', $empresa->getId());
            $nivel_empresa= $query->getResult();
            $niveles[]= array('empresa_id'=>$empresa->getId(),
                              'empresa_nombre'=>$empresa->getNombre(),
                              'empresa_pais' =>$empresa->getPais(),
                              'nivel'=>$nivel_empresa);
        }

        #return new Response(var_dump($niveles));
       return $this->render('LinkBackendBundle:Nivel:index.html.twig', array ('niveles' => $niveles));

    }

}