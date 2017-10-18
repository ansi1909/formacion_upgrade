<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\CertiCategoria; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class CategoriaController extends Controller
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

        $categoriasdb= array();

        $query= $em->createQuery('SELECT c FROM LinkComunBundle:CertiCategoria c
                                        ORDER BY c.nombre ASC');
        $categorias=$query->getResult();
        
       //return new Response(var_dump($roles));
        
        foreach ($categorias as $categoria)
        {
            $categoriasdb[]= array('id'=>$categoria->getId(),
                              'nombre'=>$categoria->getNombre(),
                              'delete_disabled'=>$f->linkEliminar($categoria->getId(),'Certicategoria0'));

        }

       return $this->render('LinkBackendBundle:Categoria:index.html.twig', array('categorias'=>$categoriasdb));

    }

   public function ajaxUpdateCategoriaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $categoria_id = $request->request->get('categoria_id');
        $nombre = $request->request->get('categoria');

        if ($categoria_id)
        {
            $categoria = $em->getRepository('LinkComunBundle:CertiCategoria')->find($categoria_id);
        }
        else {
            $categoria = new CertiCategoria();
        }

        $categoria->setNombre($nombre);
                
        $em->persist($categoria);
        $em->flush();
                    
        $return = array('id' => $categoria->getId(),
                        'nombre' =>$categoria->getNombre(),
                        'delete_disabled' =>$f->linkEliminar($categoria->getId(),'CertiCategoria'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

   public function ajaxEditCategoriaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $categoria_id = $request->query->get('categoria_id');
                
        $categoria = $this->getDoctrine()->getRepository('LinkComunBundle:CertiCategoria')->find($categoria_id);

        $return = array('nombre' => $categoria->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDeleteCategoriaAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $categoria_id = $request->request->get('id');

        $ok = 1;

        $categoria = $em->getRepository('LinkComunBundle:CertiCategoria')->find($categoria_id);
        $em->remove($categoria);
        $em->flush();

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

}
