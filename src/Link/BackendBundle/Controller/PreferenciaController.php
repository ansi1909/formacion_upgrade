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
            $layout_id = $layout_base->getId();
            $preferencia = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa->getId());
            if ($preferencia)
            {
                $plantilla = $preferencia->getLayout()->getTwig();
                $layout_id = $preferencia->getLayout()->getId();
            }

            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'pais' => $empresa->getPais()->getNombre(),
                                'plantilla' => $plantilla,
                                'preferencia_id' => $preferencia ? $preferencia->getId() : 0,
                                'layout_id' => $layout_id);

        }

        $layouts_bd = $em->getRepository('LinkComunBundle:AdminLayout')->findAll();
        $layouts = array();
        foreach ($layouts_bd as $layout)
        {
            $thumbnails = $em->getRepository('LinkComunBundle:AdminThumbnail')->findByLayout($layout->getId());
            $layouts[] = array('id' => $layout->getId(),
                               'twig' => $layout->getTwig(),
                               'thumbnails' => $thumbnails);
        }

        return $this->render('LinkBackendBundle:Preferencia:index.html.twig', array('empresas'=>$empresas,
                                                                                    'layouts' => $layouts));

    }

    public function editAction($empresa_id, $preferencia_id, Request $request){

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
        $atributos = array(); // Atrinutos editables en el template
        $variables = array(); // Solo tendrán los nombres de las variables editables
        $content = array(); // Contenido del archivo _variables_color.scss

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $layouts_bd = $em->getRepository('LinkComunBundle:AdminLayout')->findAll();
        $layouts = array();
        foreach ($layouts_bd as $layout)
        {
            $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:AdminPreferencia p 
                                        WHERE p.empresa = :empresa_id AND p.layout = :layout_id')
                        ->setParameters(array('empresa_id' => $empresa_id,
                                              'layout_id' => $layout->getId()));
            $tiene_layout = $query->getSingleScalarResult();
            $thumbnails = $em->getRepository('LinkComunBundle:AdminThumbnail')->findByLayout($layout->getId());
            $layouts[] = array('id' => $layout->getId(),
                               'twig' => $layout->getTwig(),
                               'thumbnails' => $thumbnails,
                               'checked' => $tiene_layout ? true : false);
        }

        $admin_atributos = $em->getRepository('LinkComunBundle:AdminAtributo')->findAll();
        foreach ($admin_atributos as $attr)
        {
            $variables[] = $attr->getVariable();
            $atributos[$attr->getVariable()] = array('id' => $attr->getId(),
                                                     'variable' => $attr->getVariable(),
                                                     'descripcion' => $attr->getDescripcion(),
                                                     'valor' => '');
        }

        // Atributos por defecto
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $archivo = $yml['parameters']['folders']['dir_project'].'web/front/client_styles/formacion/sass/_variables_color.scss';
        $fp = fopen($archivo, 'r');
        while (!feof($fp))
        {
            $linea = fgets($fp);
            $content[] = $linea;
            if (strpos($linea, '$') === 0)
            {
                // Es una variable. Se descompone
                $str_arr = explode(":", $linea);
                $str_var = substr($str_arr[0], 1);
                if (in_array($str_var, $variables))
                {
                    // Es una variable modificable
                    $valor = trim($str_arr[1]);
                    $valor = substr($valor, 0, 7); // Valor en HEX
                    $atributos[$str_var]['valor'] = $valor;
                }
            }
        }
        fclose($fp);

        if ($preferencia_id) 
        {

            $preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->find($preferencia_id);

            // Atributos almacenados
            $colores = $em->getRepository('LinkComunBundle:AdminColor')->findByPreferencia($preferencia->getId());
            foreach ($colores as $color)
            {
                if (in_array($color->getAtributo()->getVariable(), $variables))
                {
                    $atributos[$color->getAtributo()->getVariable()]['valor'] = $color->getHex();
                }
            }

        }
        else {
            $preferencia = new AdminPreferencia();
            $preferencia->setEmpresa($empresa);
        }

        if ($request->getMethod() == 'POST')
        {

            /*
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

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));*/

        }
        
        return $this->render('LinkBackendBundle:Preferencia:edit.html.twig', array('preferencia' => $preferencia,
                                                                                   'layouts' => $layouts,
                                                                                   'atributos' => $atributos));

    }

}
