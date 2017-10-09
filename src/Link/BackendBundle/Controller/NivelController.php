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

        $empresasdb = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        $empresas = array();
                
        foreach ($empresasdb as $empresadb)
        {
            $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                       WHERE n.empresa = :empresa_id
                                       ORDER BY n.id ASC")
                        ->setParameter('empresa_id', $empresadb->getId());
            $nivel_empresa= $query->getResult();



            $empresas[]= array('empresa_id'=>$empresadb->getId(),
                              'empresa_nombre'=>$empresadb->getNombre(),
                              'empresa_pais' =>$empresadb->getPais()->getNombre(),
                              'niveles'=>$nivel_empresa);
        } 

        #return new Response(var_dump($niveles));
        return $this->render('LinkBackendBundle:Nivel:index.html.twig', array ('empresas' => $empresas));

    }

}