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

        $paginas = array();
        
        foreach ($pages as $page)
        {

        	$subpaginas = $f->subPaginas($page->getId());

            $paginas[] = array('id' => $page->getId(),
                               'nombre' => $page->getNombre(),
                               'categoria' => $page->getCategoria()->getNombre(),
                               'creacion' => $page->getFechaCreacion()->format('d/m/Y'),
                               'status' => $page->getEstatusContenido()->getNombre(),
                               'subpaginas' => $subpaginas);

        }

        return new Response(var_dump($paginas));

        //return $this->render('LinkBackendBundle:Permiso:index.html.twig', array('permisos' => $permisos));

    }

}
