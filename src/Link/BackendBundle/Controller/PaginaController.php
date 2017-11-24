<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiPagina;

class PaginaController extends Controller
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
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPagina p 
                                    WHERE p.pagina IS NULL
                                    ORDER BY p.id ASC");
        $pages = $query->getResult();

        $paginas = $f->paginas($pages);

        //return new Response(var_dump($paginas));

        return $this->render('LinkBackendBundle:Pagina:index.html.twig', array('paginas' => $paginas));

    }

    public function paginaAction($pagina_id)
    {
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
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findByPagina($pagina_id);
        $subpaginas = $f->paginas($subpages);

        return $this->render('LinkBackendBundle:Pagina:pagina.html.twig', array('pagina' => $pagina,
                                                                                'subpaginas' => $subpaginas));

    }

}
