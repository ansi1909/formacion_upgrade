<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNivel; 
use Link\ComunBundle\Entity\AdminEmpresa; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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



            $empresas[]= array('id'=>$empresadb->getId(),
                              'nombre'=>$empresadb->getNombre(),
                              'pais' =>$empresadb->getPais()->getNombre(),
                              'niveles'=>$nivel_empresa);
        } 

        #return new Response(var_dump($niveles));
        return $this->render('LinkBackendBundle:Nivel:index.html.twig', array ('empresas' => $empresas));

    }

   public function ajaxUpdateNivelAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $nombre = $request->request->get('nombre');
        $empresa_id= $request->request->get('empresa_id');

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
    
        $nivel = new AdminNivel();

        $nivel->setNombre($nombre);
        $nivel->setEmpresa($empresa);
                
        $em->persist($nivel);
        $em->flush();

        $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                   WHERE n.empresa = :empresa_id
                                   ORDER BY n.id ASC")
                    ->setParameter('empresa_id', $empresa_id);
        $niveles = $query->getResult();

        $html = '<div class="tree">
                    <ul data-jstree=\'{ "opened" : true }\'>';
        foreach ($niveles as $n)
        {
            $html .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\'>'.$n->getNombre().'</li>';
        }
        $html .= '</ul>
                </div>';
                    
        $return = array('empresa_id' => $empresa_id,
                        'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

}