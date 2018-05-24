<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminTutorial; 
use Symfony\Component\Yaml\Yaml;


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
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $tutorialdb= array();

        $query= $em->createQuery('SELECT t FROM LinkComunBundle:AdminTutorial t
                                        ORDER BY t.nombre ASC');
        $tutoriales=$query->getResult();
        
        foreach ($tutoriales as $tutorial)
        {
            $tutorialdb[]= array('id'=>$tutorial->getId(),
                              'nombre'=>$tutorial->getNombre(),
                              'pdf'=>$tutorial->getPdf(),
                              'video'=>$tutorial->getVideo(),
                              'delete_disabled'=>$f->linkEliminar($tutorial->getId(),'AdminTutorial'));
        }

       return $this->render('LinkBackendBundle:Tutorial:index.html.twig', array('tutoriales'=>$tutorialdb) );

    }

    protected function actualizarArchivo($archivo,$newArchivo,$rutaRaiz,$rutaTutoriales,$tutorial,$metodo)// modfica los archivos de un tutorial que existe
    {
         /////se usa para obtener el nombre del archivo que se esta obteniendo del tutorial
         switch ($metodo) 
         {
             case 'pdf':
                        $metodo=$tutorial->getPdf();
                        break;
             case 'video':
                        $metodo=$tutorial->getVideo();
                        break;
             case 'imagen':
                        $metodo=$tutorial->getImagen();
                        break;
         }

         /// ahora realizamos la modificacion correspondiente en el archivo

         if (count($newArchivo)>=3)//si es true es porque se cargo un nuevo archivo
            {
                $archivo=$newArchivo[2];
                unlink($rutaRaiz.$rutaTutoriales.$tutorial->getId().'/'.$metodo);//se elimina el archivo viejo
                rename($rutaRaiz.$rutaTutoriales.$archivo,$rutaRaiz.$rutaTutoriales.$tutorial->getId().'/'.$archivo);//se agrega el nuevo archivo al directorio del tutorial correspondiente
            }
            else
            {
                rename($rutaRaiz.$rutaTutoriales.$tutorial->getId().'/'.$metodo,$rutaRaiz.$rutaTutoriales.$tutorial->getId().'/'.$archivo); 
            }

            return $archivo;
    }

    
    // cuando el archivo se crea de nuevo
    protected function nuevoArchivo($rutaRaiz,$rutaTutoriales,$pdf,$imagen,$video,$default)
    {
            mkdir($rutaRaiz.$rutaTutoriales.$default,0777);//crea la carpeta nueva
            rename($rutaRaiz.$rutaTutoriales.$pdf,$rutaRaiz.$rutaTutoriales.$default.'/'.$pdf);//mueve el pdf a lacarpeta default
            rename($rutaRaiz.$rutaTutoriales.$imagen,$rutaRaiz.$rutaTutoriales.$default.'/'.$imagen);//mueve la imagen a la carpeta default
            rename($rutaRaiz.$rutaTutoriales.$video,$rutaRaiz.$rutaTutoriales.$default.'/'.$video);//mueve el video a la carpeta default

            return true;

    }



    public function ajaxUpdateTutorialAction(Request $request)
    {
        
        $ymlParameters = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $ymlParametros = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $raiz=$ymlParameters['parameters']['folders']['dir_uploads'];
        $rutaTutoriales=$ymlParametros['parameters']['tutoriales']['ruta'];
        $default=$ymlParametros['parameters']['tutoriales']['default'];

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $nuevo=0;
        $tutorial_id = $request->request->get('tutorial_id');
        $nombre = $request->request->get('nombre');
        $pdf = $request->request->get('pdf');
        $video = $request->request->get('video');
        $imagen = $request->request->get('imagen');
        $descripcion=$request->request->get('descripcion');


        $newPdf=explode("/",$pdf);
        $newImagen=explode("/",$imagen);
        $newVideo=explode("/",$video);

        if ($tutorial_id)
        {
            $tutorial = $em->getRepository('LinkComunBundle:AdminTutorial')->find($tutorial_id);

            
            $pdf=$this->actualizarArchivo($pdf,$newPdf,$raiz,$rutaTutoriales,$tutorial,'pdf');
            $imagen=$this->actualizarArchivo($imagen,$newImagen,$raiz,$rutaTutoriales,$tutorial,'imagen');
            $video=$this->actualizarArchivo($video,$newVideo,$raiz,$rutaTutoriales,$tutorial,'video');
        }
        else 
        {
            $tutorial = new AdminTutorial();
            $nuevo=1;

            $pdf=$newPdf[2];
            $imagen=$newImagen[2];
            $video=$newVideo[2];

            $this->nuevoArchivo($raiz,$rutaTutoriales,$pdf,$imagen,$video,$default);

           
        }

        $tutorial->setNombre($nombre);
        $tutorial->setPdf($pdf);
        $tutorial->setVideo($video);
        $tutorial->setImagen($imagen);
        $tutorial->setDescripcion($descripcion);
        $tutorial->setFecha(new \DateTime('now'));
        
        $em->persist($tutorial);
        $em->flush();

        if($nuevo==1)
        {
            rename($raiz.$rutaTutoriales.$default, $raiz.$rutaTutoriales.$tutorial->getId());
        }
                    
        $return = array('id' => $tutorial->getId(),
                        'nombre' =>$tutorial->getNombre(),
                        'pdf' =>$tutorial->getPdf(),
                        'video' =>$tutorial->getVideo(),
                        'imagen'=>$tutorial->getImagen(),
                        'descripcion'=>$tutorial->getDescripcion(),
                        'delete_disabled' =>$f->linkEliminar($tutorial->getId(),'AdminTutorial'));

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

}
