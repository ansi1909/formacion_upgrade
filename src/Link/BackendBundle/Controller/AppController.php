<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminAplicacion;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AppController extends Controller
{
    public function indexAction($app_id, Request $request)
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

        $aplicaciones = array();
        
        // Todas las aplicaciones principales
        $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                    WHERE a.aplicacion IS NULL 
                                    ORDER BY a.orden ASC");
        $apps = $query->getResult();
        $aplicaciones_str = '<option value=""></option>';

        foreach ($apps as $app)
        {

        	$aplicaciones_str .= '<option value="'.$app->getId().'">'.$app->getNombre().'</option>';

            $query = $em->createQuery('SELECT COUNT(a.id) FROM LinkComunBundle:AdminAplicacion a 
                                        WHERE a.aplicacion = :aplicacion_id')
                        ->setParameter('aplicacion_id', $app->getId());
            $tiene_subaplicaciones = $query->getSingleScalarResult();

            $aplicaciones[] = array('orden' => $app->getOrden(),
                                    'id' => $app->getId(),
                                   	'nombre' => $app->getNombre(),
                                   	'url' => $app->getUrl(),
                                   	'icono' => $app->getIcono(),
                                   	'activo' => $app->getActivo(),
                                   	'delete_disabled' => $f->linkEliminar($app->getId(), 'AdminAplicacion'),
                                      'tiene_subaplicaciones' => $tiene_subaplicaciones);

        }

        return $this->render('LinkBackendBundle:App:index.html.twig', array('aplicaciones' => $aplicaciones,
        																	'aplicaciones_str' =>$aplicaciones_str));

        // Solicita el servicio de excel
        /*   $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

           $phpExcelObject->getProperties()->setCreator("liuggio")
               ->setLastModifiedBy("Giulio De Donato")
               ->setTitle("Office 2005 XLSX Test Document")
               ->setSubject("Office 2005 XLSX Test Document")
               ->setDescription("Test document for Office 2005 XLSX, generado usando clases de PHP")
               ->setKeywords("office 2005 openxml php")
               ->setCategory("Archivo de ejemplo");
           $phpExcelObject->setActiveSheetIndex(0)
               ->setCellValue('A1', 'Hola')
               ->setCellValue('B2', 'Mundo!');
           $phpExcelObject->getActiveSheet()->setTitle('Simple');
           // Define el indice de página al número 1, para abrir esa página al abrir el archivo
           $phpExcelObject->setActiveSheetIndex(0);

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            // Envia la respuesta del controlador
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // Agrega los headers requeridos
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'PhpExcelFileSample.xlsx'
            );

            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;*/





        // estas lineas nos puede servir para comprobar que nuestro fichero
      // que queremos cargar existe
      // $fileWithPath - Es el nombre del fichero con el path completo
      // if(file_exists($fileWithPath)) {
      //      echo 'exist'."<br>";
      // } else {
      //      echo 'dont exist'."<br>";
      //      die;
      // }
      //cargamos el archivo a procesar.
    /*$fileWithPath = $this->get('kernel')->getRootDir()."/../web/balance_2017.xlsx";
      //$objPHPExcel = $this->get('xls.load_xls2007')->load($fileWithPath);
    $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
      //se obtienen las hojas, el nombre de las hojas y se pone activa la primera hoja
      $total_sheets=$objPHPExcel->getSheetCount();
      $allSheetName=$objPHPExcel->getSheetNames();
      $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
      //Se obtiene el número máximo de filas
      $highestRow = $objWorksheet->getHighestRow();
      //Se obtiene el número máximo de columnas
      $highestColumn = $objWorksheet->getHighestColumn();
      $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
      //$headingsArray contiene las cabeceras de la hoja excel. Llos titulos de columnas
      $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
      $headingsArray = $headingsArray[1];
 
      //Se recorre toda la hoja excel desde la fila 2 y se almacenan los datos
       $r = -1;
       $namedDataArray = array();
       for ($row = 2; $row <= $highestRow; ++$row) {
            $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
            if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                  ++$r;
                  foreach($headingsArray as $columnKey => $columnHeading) {
                          $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                  } //endforeach
            } //endif
        }
       return new Response(var_dump($namedDataArray));*/

    }

    public function ajaxEditAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $app_id = $request->query->get('app_id');
        $subaplicaciones = '<option value=""></option>';
        
        $aplicacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);

        // Todas las aplicaciones principales
        $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                    WHERE a.aplicacion IS NULL 
                                    ORDER BY a.orden ASC");
        $apps = $query->getResult();

        foreach ($apps as $app)
        {
            if (!(!$aplicacion->getAplicacion() && $aplicacion->getId() == $app->getId()))
            {
                $selected = $aplicacion->getAplicacion() ? $aplicacion->getAplicacion()->getId() == $app->getId() ? ' selected' : '' : '';
                $subaplicaciones .= '<option value="'.$app->getId().'"'.$selected.'>'.$app->getNombre().'</option>';
            }
        }

        $return = array('nombre' => $aplicacion->getNombre(),
                        'url' => $aplicacion->getUrl(),
                        'icono' => $aplicacion->getIcono(),
                        'activo' => $aplicacion->getActivo(),
                        'subaplicaciones' => $subaplicaciones);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUpdateAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        
        $app_id = $request->request->get('app_id');
        $nombre = $request->request->get('nombre');
        $url = $request->request->get('url');
        $icono = $request->request->get('icono');
        $activo = $request->request->get('activo');
        $subaplicacion_id = $request->request->get('subaplicacion_id');

        if ($app_id)
        {
        	$aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);
        }
        else {
        	$aplicacion = new AdminAplicacion();
        }

        $aplicacion->setNombre($nombre);
        $aplicacion->setUrl($url);
        $aplicacion->setIcono($icono);
        $aplicacion->setActivo($activo ? true : false);
        if ($subaplicacion_id)
        {
        	$subaplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($subaplicacion_id);
        	$aplicacion->setAplicacion($subaplicacion);
        }
        else {
        	$aplicacion->setAplicacion(null);
        }
        $em->persist($aplicacion);
        $em->flush();
                    
        $return = array('id' => $aplicacion->getId(),
        				'nombre' => $aplicacion->getNombre(),
        				'url' => $aplicacion->getUrl(),
        				'icono' => '<span class="fa '.$aplicacion->getIcono().'"></span> '.$aplicacion->getIcono(),
        				'activo' => $aplicacion->getActivo() ? $this->get('translator')->trans('Si') : 'No',
        				'subaplicacion_id' => $aplicacion->getAplicacion() ? 1 : 0,
        				'subaplicacion' => $aplicacion->getAplicacion() ? $aplicacion->getAplicacion()->getNombre() : '',
                        'delete_disabled' => $f->linkEliminar($aplicacion->getId(), 'AdminAplicacion'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxDeleteAplicacionAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $app_id = $request->request->get('id');
        $ok = 1;

        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);
        $em->remove($aplicacion);
        $em->flush();
            
        $return = array('ok' => $ok);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxSubAplicacionesAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $app_id = $request->query->get('app_id');
        $html = '';
        
        $aplicacion = $this->getDoctrine()->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);
        $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAplicacion a 
                                    WHERE a.aplicacion = :app_id 
                                    ORDER BY a.orden ASC")
                    ->setParameter('app_id', $app_id);
        $subaplicaciones = $query->getResult();

        foreach ($subaplicaciones as $subaplicacion)
        {
            $checked = $subaplicacion->getActivo() ? 'checked' : '';
            $delete_disabled = $f->linkEliminar($subaplicacion->getId(), 'AdminAplicacion');
            $delete = $delete_disabled=='' ? 'delete' : '';
            $html .= '<tr>
                        <td>'.$subaplicacion->getOrden().'</td>
                        <td>'.$subaplicacion->getId().'</td>
                        <td>'.$subaplicacion->getNombre().'</td>
                        <td class="center">
                            <div class="can-toggle demo-rebrand-2 small">
                                <input id="f'.$subaplicacion->getId().'" class="cb_activo" type="checkbox" '.$checked.'>
                                <label for="f'.$subaplicacion->getId().'">
                                    <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                                </label>
                            </div>
                        </td>
                        <td class="center">
                            <a href="#" class="btn btn-link btn-sm edit" data-toggle="modal" data-target="#formModal" data="'.$subaplicacion->getId().'"><span class="fa fa-pencil"></span></a>
                            <a href="#" class="btn btn-link btn-sm '.$delete.' '.$delete_disabled.'" data="'.$subaplicacion->getId().'"><span class="fa fa-trash"></span></a>
                        </td>
                    </tr>';
        }

        $return = array('html' => $html,
                        'empresa' => $aplicacion->getNombre());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
}
