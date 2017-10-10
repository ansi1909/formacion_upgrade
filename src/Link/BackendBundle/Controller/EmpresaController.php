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
        $empresas_db = $r->findAll();

        $empresas = array();
        foreach ($empresas_db as $empresa)
        {
            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'pais' => $empresa->getPais(),
                                'fechaCreacion' => $empresa->getFechaCreacion(),
                                'activo' => $empresa->getActivo(),
                                'delete_disabled' => $f->linkEliminar($empresa->getId(), 'AdminNivel,'));
        }

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

        $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->findOneById2($session->get('code'));

        if ($empresa_id) 
        {
            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        }
        else {
            $empresa = new AdminEmpresa();
            $empresa->setPais($pais);
            $empresa->setFechaCreacion(new \DateTime('now'));
        }

        // Lista de paises
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('LinkComunBundle:AdminPais', 'p')
           ->orderBy('p.nombre', 'ASC');
        $query = $qb->getQuery();
        $paises = $query->getResult();

        if ($request->getMethod() == 'POST')
        {

            $nombre = $request->request->get('nombre');
            $pais_id = $request->request->get('pais_id');
            $bienvenida = $request->request->get('bienvenida');
            $activo = $request->request->get('activo');

            $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id);

            $empresa->setNombre($nombre);
            $empresa->setActivo($activo ? true : false);
            $empresa->setBienvenida($bienvenida);
            $empresa->setPais($pais);
            $em->persist($empresa);
            $em->flush();

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));

        }
        
        return $this->render('LinkBackendBundle:Empresa:registro.html.twig', array('empresa' => $empresa,
                                                                                   'paises' => $paises));

    }

    public function mostrarAction($empresa_id)
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

        $em = $this->getDoctrine()->getManager();
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        return $this->render('LinkBackendBundle:Empresa:mostrar.html.twig', array('empresa' => $empresa));

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
