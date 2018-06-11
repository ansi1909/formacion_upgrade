<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminTutorial; 
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\TranslatorInterface;
use Link\ComunBundle\Model\UploadHandler;


class TutorialController extends Controller
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

       return $this->render('LinkBackendBundle:Tutorial:index.html.twig' );

    }

    

    protected function nameArchivo($name)
    {

        $aux = explode("/",$name);
        $longitud = count($aux);
        $name_ = false;
        $route = false;

        if ($longitud==3)//carpeta : recursos/tutoriales
        {
            $name_ = $aux[2];
            $route = '';
        }
        else if($longitud==4)
        {
            $name_ = $aux[3];
            $route = $aux[2].'/';
        }
        else if($longitud==1)
        {
            $name_ = $name;
        }
        
        return ['name'=>$name_,'route'=>$route];
    }

    protected function setTutorial($em,$datosAjax,$tutorial,$session)
    {
        $pdf_ = $this->nameArchivo($datosAjax['pdf']);
        $video_ = $this->nameArchivo($datosAjax['video']);
        $imagen_ = $this->nameArchivo($datosAjax['imagen']);
        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $tutorial->setNombre(ucfirst(strtolower($datosAjax['nombre'])));
        $tutorial->setDescripcion(ucfirst(strtolower($datosAjax['descripcion'])));
        $tutorial->setPdf($pdf_['name']);
        $tutorial->setVideo($video_['name']);
        $tutorial->setImagen($imagen_['name']);
        $tutorial->setUsuario($usuario);
        $tutorial->setFecha(new \DateTime('now'));
        
        $em->persist($tutorial);
        $em->flush();

        return ['tutorial'=>$tutorial,'routePdf'=>$pdf_['route'],'routeVideo'=>$video_['route'],'routeImagen'=>$imagen_['route']];

    }

    protected function moverArchivo($pathInicio,$pathTutorial,$nombreArchivo)
    {
        if ($nombreArchivo!='') 
        {
            rename($pathInicio.$nombreArchivo,$pathTutorial.$nombreArchivo);
        }

        return 0;
    }

    protected function eliminarArchivos($tutorial,$rutaTutorial,$tipoArchivo)
    {
        $extensiones['pdf']='pdf';
        $extensiones['imagen']=explode('-',$tipoArchivo['imagen']);
        $extensiones['video']=explode('-',$tipoArchivo['video']);

        $archivosDirectorio=scandir($rutaTutorial);
        for ($i=0; $i <count($archivosDirectorio) ; $i++) 
        { 
            $archivo=explode('.',$archivosDirectorio[$i]);
            if (count($archivo)==2) 
            {
                for ($j=0; $j <count($extensiones['imagen']) ; $j++) 
                { 
                    if (($archivo[1]==$extensiones['imagen'][$j])&&($tutorial->getImagen()!=$archivosDirectorio[$i])) 
                    {
                        unlink($rutaTutorial.'/'.$archivosDirectorio[$i]);
                    }
                }
                for ($k=0; $k <count($extensiones['video']) ; $k++) 
                { 
                     if (($archivo[1]==$extensiones['video'][$k]) &&($tutorial->getVideo()!=$archivosDirectorio[$i]))
                    {
                        unlink($rutaTutorial.'/'.$archivosDirectorio[$i]);
                    }
                }

                if (($archivo[1]=='pdf')&&($tutorial->getPdf()!=$archivosDirectorio[$i]))
                {
                     unlink($rutaTutorial.'/'.$archivosDirectorio[$i]);
                }
            }
        }

        return true;
    }

    
    public function ajaxUpdateTutorialAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $tutorial_id = $request->request->get('tutorial_id');
        $nombre = $request->request->get('nombre');
        $pdf = $request->request->get('pdf');
        $video = $request->request->get('video');
        $imagen = $request->request->get('imagen');
        $descripcion = $request->request->get('descripcion');
       

        if ($tutorial_id)
        {
            $tutorial = $em->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);
        }
        else {
            $tutorial = new AdminTutorial();
            $tutorial->setFecha(new \DateTime('now'));
        }
        
        $tutorial->setNombre($nombre);
        $tutorial->setPdf($pdf);
        $tutorial->setVideo($video);
        $tutorial->setImagen($imagen);
        $tutorial->setDescripcion($descripcion);
        $tutorial->setUsuario($usuario);

        $em->persist($tutorial);
        $em->flush();

        if (!$tutorial_id)//si es nuevo
        {
            // Hacer el movimiento de archivos en caso de que sea nuevo tutorial
            $dir_uploads = $this->container->getParameter('folders')['dir_uploads'].'recursos/tutoriales/';
            mkdir($dir_uploads.$tutorial->getId(),0777);
            $this->moverArchivo($dir_uploads,$dir_uploads.$tutorial->getId().'/',$tutorial->getPdf());
            $this->moverArchivo($dir_uploads,$dir_uploads.$tutorial->getId().'/',$tutorial->getImagen());
            $this->moverArchivo($dir_uploads,$dir_uploads.$tutorial->getId().'/',$tutorial->getVideo());

            
        }
  
        $return = array('id' => $tutorial->getId(),
                        'nombre' => $tutorial->getNombre(),
                        'pdf' => substr(strrchr($tutorial->getPdf(), '/'), 1),
                        'video' => substr(strrchr($tutorial->getVideo(), '/'), 1),
                        'imagen'=> substr(strrchr($tutorial->getImagen(), '/'), 1),
                        'descripcion' => $tutorial->getDescripcion());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }


    public function ajaxEditTutorialAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $tutorial_id = $request->query->get('tutorial_id');
                
        $tutorial = $this->getDoctrine()->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);

        $return = array('nombre' => $tutorial->getNombre(),
                        'pdf' => $tutorial->getPdf(),
                        'video' => $tutorial->getVideo(),
                        'imagen'=> $tutorial->getImagen(),
                        'descripcion'=>$tutorial->getDescripcion());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }


    public function ajaxRefreshTableAction(Request $request)
    {
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $ruta = $yml['parameters']['folders']['uploads'].'recursos/tutoriales/';
        $em = $this->getDoctrine()->getManager();
        $data = ['data'=>[]];

        $query= $em->createQuery('SELECT t FROM LinkComunBundle:AdminTutorial t
                                                ORDER BY t.id ASC');
        $tutoriales = $query->getResult();
        $f = $this->get('funciones');
                
        foreach ($tutoriales as $tutorial)
        {
           $delete_disabled = $f->linkEliminar($tutorial->getId(),'AdminTutorial');
           $delete = $delete_disabled == '' ? 'delete' : '';
           $enlacePdf = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getPdf().'" target="_blank">'.              $tutorial->getPdf().
                       ' </a>';

                        $enlaceVideo = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getVideo().'" target="_blank">'.$tutorial->getVideo().' </a>';
                        $acciones = '
                                      <td class="center" >
                                         <a href="#" title="'.$this->get('translator')->trans('Editar').'"  class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$tutorial->getId().'" ><span class="fa fa-pencil"></span> 
                                         </a>
                                         <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm '.$delete.''.$delete_disabled.'" data="'.$tutorial->getId().'"><span class="fa fa-trash"></span>

                                         </a>
                                      </td>';
                        
            array_push($data['data'],[$tutorial->getId(),$tutorial->getNombre(),$enlacePdf,$enlaceVideo,$acciones]);
        }

        $return = json_encode($data);
        return new Response($return, 200, array('Content-Type' => 'application/json'));  
    }

    protected function deleteFilesTutorial($tutorial_id)
    {
       
       $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
       $directorio = $dir_uploads.'recursos/tutoriales/'.$tutorial_id;
       $archivos=scandir($directorio);

       for ($i=2; $i <count($archivos); $i++) 
       { 
          
          unlink($directorio.'/'.$archivos[$i]);
       }

       rmdir($directorio);
       return true;

    }

    public function ajaxDeleteTutorialAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');

        $ok = 1;
        $object = $em->getRepository('LinkComunBundle:AdminTutorial')->find($id);
        $em->remove($object);
        $em->flush();
        $this->deleteFilesTutorial($id);

        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));


    }

    public function ajaxUploadFileTutorialAction(Request $request)
    {
        $session = new Session();
        $auxTut=$request->request->get('tutorial_id');
        $tutorial_id = ($auxTut>0) ? $auxTut.'/' : ''; 

        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
        $uploads = $this->container->getParameter('folders')['uploads'];
        $upload_dir = $dir_uploads.'recursos/tutoriales/'.$tutorial_id;
        $upload_url = $uploads.'recursos/tutoriales/'.$tutorial_id;
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);

        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}
