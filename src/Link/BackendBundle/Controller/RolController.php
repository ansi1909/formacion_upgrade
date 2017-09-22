<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminRol; 
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class RolController extends Controller
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
        $query= $em->createQuery('SELECT r FROM LinkComunBundle:AdminRol r
                                        ORDER BY r.nombre ASC');
        $roles=$query->getResult();
       
       //return new Response(var_dump($roles));
            
       return $this->render('LinkBackendBundle:Rol:index.html.twig', array('roles'=>$roles));

    }


   public function registroAction(Request $request)
    {
        $rol= new AdminRol();

        $form= $this->createFormBuilder($rol)
            ->setAction($this->generateUrl('_RegistroRol'))
            ->add('nombre', TextType::class,array('label' => 'Nombre'))
            ->add('descripcion', TextareaType::class,array('label' => 'Descripcion'))
            ->add('save', SubmitType::class,array('label' => 'Registrar',
                                                  'attr' =>array('class' => 'btn btn-default')))
            ->getform();
        $form->handleRequest($request);
        if ($form->isValid()) 
                {
                    $em->persist($AdminRol);
                    $em->flush();
                    return $this->redirectToRoute('_FinRegistro');
                }
                return $this->render('LinkBackendBundle:Rol:registro.html.twig',
                                                                array('form'=>$form->createView()));
    }
    public function finRegistroAction()
    {
        return $this->render('LinkBackendBundle:Rol:finRegistro.html.twig');
    }
    

}
