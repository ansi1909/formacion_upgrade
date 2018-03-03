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
        $archivo = $yml['parameters']['folders']['dir_project'].'web/front/client_styles/'.$empresa_id.'/sass/_variables_color.scss';
        if (!file_exists($archivo))
        {
            $archivo = $yml['parameters']['folders']['dir_project'].'web/front/client_styles/formacion/sass/_variables_color.scss';
        }
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
            $preferencia->setTitle($this->get('translator')->trans('Sistema Formación').' 2.0');
        }

        if ($request->getMethod() == 'POST')
        {

            return new Response($f->getWebDirectory());
            $layout_id = $request->request->get('layout_id');
            $title = $request->request->get('title');
            $logo = trim($request->request->get('logo'));
            $favicon = trim($request->request->get('favicon'));

            $layout = $em->getRepository('LinkComunBundle:AdminLayout')->find($layout_id[0]);
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            
            // Preferencia
            $preferencia->setLayout($layout);
            $preferencia->setTitle($title);
            $preferencia->setLogo($logo != '' ? $logo : null);
            $preferencia->setFavicon($favicon != '' ? $favicon : null);
            $preferencia->setUsuario($usuario);
            $em->persist($preferencia);
            $em->flush();

            // Colores
            foreach ($atributos as $atributo)
            {
                $hex = $request->request->get('atributos_id'.$atributo['id']);
                if ($hex) 
                {

                    $atributo_bd = $em->getRepository('LinkComunBundle:AdminAtributo')->find($atributo['id']);
                    $color = $em->getRepository('LinkComunBundle:AdminColor')->findOneBy(array('preferencia' => $preferencia->getId(),
                                                                                               'atributo' => $atributo_bd->getId()));
                    if (!$color)
                    {
                        $color = new AdminColor();
                    }
                    $color->setPreferencia($preferencia);
                    $color->setAtributo($atributo_bd);
                    $color->setHex($hex);
                    $em->persist($color);
                    $em->flush();

                    // Se escribe en el arreglo de contenido
                    $i = 0;
                    foreach ($content as $c)
                    {
                        if (strpos($c, '$'.$atributo['variable']) === 0)
                        {
                            $content[$i] = "\$".$atributo['variable'].": ".$hex.";\n";
                        }
                        $i++;
                    }

                }
            }

            $new_file = $yml['parameters']['folders']['dir_project'].'web/front/client_styles/'.$empresa_id.'/sass/_variables_color.scss';
            $fp = fopen($new_file, "w+");
            foreach ($content as $c){
                fwrite($fp, $c);
            }
            fclose($fp);

            // Aquí se correría el comando que genera el nuevo main.css de la empresa
            $css = 'front/client_styles/'.$empresa_id.'/css/main.css';
            $preferencia->setCss($css);
            $em->persist($preferencia);
            $em->flush();

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));

        }

        //return new Response(var_dump($atributos));
        
        return $this->render('LinkBackendBundle:Preferencia:edit.html.twig', array('preferencia' => $preferencia,
                                                                                   'layouts' => $layouts,
                                                                                   'atributos' => $atributos));

    }

}
