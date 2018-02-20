<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminEmpresa;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Yaml\Yaml;

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
        $f->setRequest($session->get('sesion_id'));
        
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
                                'delete_disabled' => $f->linkEliminar($empresa->getId(), 'AdminEmpresa'));
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
        $f->setRequest($session->get('sesion_id'));

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
            $activo2 = $request->request->get('activo2');
            $activo3 = $request->request->get('activo3');

            $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id);

            $empresa->setNombre($nombre);
            $empresa->setActivo($activo ? true : false);
            $empresa->setChatActivo($activo2 ? true : false);
            $empresa->setWebinar($activo3 ? true : false);
            $empresa->setBienvenida($bienvenida);
            $empresa->setPais($pais);
            $em->persist($empresa);
            $em->flush();

            // Se crea el directorio para los activos de la empresa
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $f->subDirEmpresa($empresa->getId(), $yml['parameters']['folders']['dir_uploads']);

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
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        return $this->render('LinkBackendBundle:Empresa:mostrar.html.twig', array('empresa' => $empresa));

    }

}
