<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminPreferencia;
use Link\ComunBundle\Entity\AdminColor;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Yaml\Yaml;

class PreferenciaController extends Controller
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
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        $layout_base = $em->getRepository('LinkComunBundle:AdminLayout')->find($yml['parameters']['layout']['base']);
        $thumbnail_base = $this->getDoctrine()->getRepository('LinkComunBundle:AdminThumbnail')->findOneByLayout($layout_base->getId());
        
        $r = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa');
        $empresas_db = $r->findAll();

        $empresas = array();
        foreach ($empresas_db as $empresa)
        {

            $plantilla = $layout_base->getTwig();
            $preferencia = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa->getId());
            if ($preferencia)
            {
                $plantilla = $preferencia->getLayout()->getTwig();
            }

            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'pais' => $empresa->getPais(),
                                'plantilla' => $plantilla);
            
        }

        return $this->render('LinkBackendBundle:Preferencia:index.html.twig', array('empresas'=>$empresas));

    }

}
