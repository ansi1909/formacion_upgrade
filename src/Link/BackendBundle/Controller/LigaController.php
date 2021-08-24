<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminLigas;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Model\UploadHandler;


class LigaController extends Controller
{
    public function indexAction($app_id)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $ligasdb = array();

        $query = $em->createQuery('SELECT l FROM LinkComunBundle:AdminLigas l
                                        ORDER BY l.nombre ASC');
        $ligas = $query->getResult();
        
        foreach ($ligas as $liga)
        {
            $ligasdb[] = array('id' => $liga->getId(),
                               'nombre' => $liga->getNombre(),
                               'descripcion' => $liga->getDescripcion(),
                               'puntos' => $liga->getPuntuacion(),
                               'delete_disabled' => $f->linkEliminar($liga->getId(),'AdminLigas'));

        }

       return $this->render('LinkBackendBundle:Ligas:index.html.twig', array('ligas'=>$ligasdb));

    }


    protected function moverArchivo($pathInicio,$pathLiga,$nombreArchivo)
    {
        if ($nombreArchivo != '') 
        {
            rename($pathInicio.$nombreArchivo,$pathLiga.$nombreArchivo);
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
                if( $objeto->getImagen() != $archivos[$i] )
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

    public function ajaxUpdateLigaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'].'recursos/ligas';

        $liga_id = $request->request->get('liga_id');
        $nombre = $request->request->get('nombre');
        $descripcion = $request->request->get('descripcion');
        $minimo = $request->request->get('minimo');
        $maximo = $request->request->get('maximo');
        $imagen = $request->request->get('imagen');

        
       

        if ($liga_id)
        {
            $liga = $em->getRepository('LinkComunBundle:AdminLigas')->find($liga_id);
        }
        else {
            $liga = new AdminLigas();
        }

        $liga->setNombre($nombre);
        $liga->setdescripcion($descripcion);
        $liga->setPorcentajemin($minimo);
        $liga->setPorcentajemax($maximo);
        $liga->setImagen($imagen);
                        
        $em->persist($liga);
        $em->flush();

        if (!$liga_id)//si es nuevo
        {
            // Hacer el movimiento de archivos en caso de que sea nuevo tutorial
            mkdir($dir_uploads.'/'.$liga->getId(),0777);
            $this->moverArchivo($dir_uploads.'/',$dir_uploads.'/'.$liga->getId().'/',$liga->getImagen());
            $this->cleanRootDirectory($dir_uploads);//elimina los archivos huerfanos       
        }
        else{
            $this->deleteOrphanFilesTutorial($dir_uploads.'/'.$liga_id,$liga);//elimina los archivos huerfanos de la carpeta del tutorial
        }
                  
        
        $return = array('id'                => $liga->getId(),
                        'nombre'            => $liga->getNombre(),
                        'descripcion'       => $liga->getDescripcion(),
                        'minimo'            => $liga->getPorcentajemin(),
                        'maximo'            => $liga->getPorcentajemax(),
                        'imagen'            => $liga->getImagen(),
                        'delete_disabled'   => $f->linkEliminar($liga->getId(),'AdminLigas'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxEditLigaAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $liga_id = $request->query->get('liga_id');
                
        $liga = $this->getDoctrine()->getRepository('LinkComunBundle:AdminLigas')->find($liga_id);

        $return = array(
                    'nombre'       => $liga->getNombre(), 
                    'descripcion'  => $liga->getDescripcion(),
                    'minimo'       => $liga->getPorcentajemin(),
                    'maximo'       => $liga->getPorcentajemax(),
                    'imagen'       => $liga->getImagen()
                );

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUploadFileLigaAction(Request $request)
    {
        $session = new Session();
        $auxTut=$request->request->get('liga_id');
        //return new response($auxTut);
        $liga_id = ($auxTut>0) ? $auxTut.'/' : ''; 
        //return new response($liga_id);
        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
        //return new response($dir_uploads);
        $uploads = $this->container->getParameter('folders')['uploads'];
        $upload_dir = $dir_uploads.'recursos/ligas/'.$liga_id;
        $upload_url = $uploads.'recursos/ligas/'.$liga_id;
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);
        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

}