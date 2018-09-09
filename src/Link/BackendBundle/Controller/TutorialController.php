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

    protected function cleanRootDirectory($directorio,$removeDir=false)
    {
        /*
            Elimina los archivos dentro del directorio indicado, sin consultar si estan registrados en la 
            base de datos, cuando el argumento: removeDir es true se procede a borrar la carpeta una vez
            se encuentre vacía. Se utiliza cuando se elimina un tutorial del sistema y para limpiar 
            el directorio : recursos/tutoriales/ luego de realizar una insercion. elimina la carpeta thumbnail
        */

        $existe = file_exists($directorio);

        if($existe)
        {

            $archivos = scandir($directorio);
            
            for ($i=0; $i <count($archivos) ; $i++) 
            { 
                if(!is_dir($directorio.'/'.$archivos[$i]) )
                {
                  unlink($directorio.'/'.$archivos[$i]);
                }
                else
                {
                    if ($archivos[$i] == 'thumbnail') {
                        $thumbnails = scandir($directorio.'/'.$archivos[$i].'/');
                        for ($j=2; $j<count($thumbnails) ; $j++) { 
                            unlink($directorio.'/'.$archivos[$i].'/'.$thumbnails[$j]);
                        }
                        rmdir($directorio.'/'.$archivos[$i]);
                    }
                }
            }

            if ($removeDir) {
                rmdir($directorio);
            }

        }

        return 0;

    }

    protected function deleteOrphanFilesTutorial($directorio,$objeto)
    {
        /*
            Elimina los archivos dentro de una carpeta de un tutorial, que no se encuentren 
            registrados en la base de datos. Se utiliza al momento de modificar un tutorial, para evitar 
            que queden archivos huerfanos luego de modificar el tutorial.
        */
        
        $archivos = scandir($directorio);
        for ($i=0; $i <count($archivos); $i++) 
        { 
            if (!is_dir($directorio.'/'.$archivos[$i])) {
                if($objeto->getPdf() != $archivos[$i] && $objeto->getImagen() != $archivos[$i] && $objeto->getVideo() != $archivos[$i])
                {
                    unlink($directorio.'/'.$archivos[$i]);
                }
            }
            else {
                if ($archivos[$i]=='thumbnail') 
                {
                    $thumbnails=scandir($directorio.'/'.$archivos[$i].'/');
                    for ($j=2; $j <count($thumbnails) ; $j++) 
                    { 
                        unlink($directorio.'/'.$archivos[$i].'/'.$thumbnails[$j]);
                    }
                    rmdir($directorio.'/'.$archivos[$i]);
                }
            }
        }

        return 0;

    }
    
    protected function moverArchivo($pathInicio,$pathTutorial,$nombreArchivo)
    {
        if ($nombreArchivo != '') 
        {
            rename($pathInicio.$nombreArchivo,$pathTutorial.$nombreArchivo);
        }

        return 0;
    }
    
    public function ajaxUpdateTutorialAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'].'recursos/tutoriales';
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
            mkdir($dir_uploads.'/'.$tutorial->getId(),0777);
            $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getPdf());
            $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getImagen());
            $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$tutorial->getId().'/',$tutorial->getVideo());
            $this->cleanRootDirectory($dir_uploads);//elimina los archivos huerfanos       
        }
        else{
            $this->deleteOrphanFilesTutorial($dir_uploads.'/'.$tutorial_id,$tutorial);//elimina los archivos huerfanos de la carpeta del tutorial
        }
  
        $return = array('id' => $tutorial->getId(),
                        'nombre' => $tutorial->getNombre(),
                        'pdf' => $tutorial->getPdf(),
                        'video' => $tutorial->getVideo(), 
                        'imagen'=> $tutorial->getImagen(),
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
        
        $em = $this->getDoctrine()->getManager();
        $data = ['data'=>[]];
        $ruta = $this->container->getParameter('folders')['uploads'].'recursos/tutoriales/';
        $f = $this->get('funciones');

        $query = $em->createQuery('SELECT t FROM LinkComunBundle:AdminTutorial t
                                    ORDER BY t.id ASC');
        $tutoriales = $query->getResult();
                
        foreach ($tutoriales as $tutorial)
        {
            $enlacePdf = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getPdf().'" target="_blank">'.$tutorial->getPdf().' </a>';
            $enlaceVideo = '<a href="'.$ruta.$tutorial->getId().'/'.$tutorial->getVideo().'" target="_blank">'.$tutorial->getVideo().' </a>';
            $acciones = '<td class="center" >
                            <a href="#" title="'.$this->get('translator')->trans('Editar').'"  class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$tutorial->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" title="'.$this->get('translator')->trans('Eliminar').'" class="btn btn-link btn-sm delete" data="'.$tutorial->getId().'" data-ubicacion="1"><span class="fa fa-trash"></span></a>
                        </td>';
            array_push($data['data'],[$tutorial->getId(),$tutorial->getNombre(),$enlacePdf,$enlaceVideo,$acciones]);
        }

        $return = json_encode($data);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDeleteTutorialAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $directorio = $this->container->getParameter('folders')['dir_uploads'].'/recursos/tutoriales/'.$id;

        $ok = 1;
        $object = $em->getRepository('LinkComunBundle:AdminTutorial')->find($id);
        $em->remove($object);
        $em->flush();
        $this->cleanRootDirectory($directorio,true);

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
