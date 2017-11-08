<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNivel;
use Symfony\Component\Yaml\Yaml;

class NivelController extends Controller
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

        $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();

        #return new Response(var_dump($niveles));
        return $this->render('LinkBackendBundle:Nivel:index.html.twig', array ('empresas' => $empresas));

    }

   public function ajaxUpdateNivelAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $nombre = $request->request->get('nombre');
        $empresa_id = $request->request->get('empresa_id');

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
    
        $nivel = new AdminNivel();

        $nivel->setNombre($nombre);
        $nivel->setEmpresa($empresa);
                
        $em->persist($nivel);
        $em->flush();

        $return = array('empresa_id' => $empresa_id);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxTreeNivelesAction($empresa_id, Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                   WHERE n.empresa = :empresa_id
                                   ORDER BY n.id ASC")
                    ->setParameter('empresa_id', $empresa_id);
        $niveles = $query->getResult();

        $return = array();

        foreach ($niveles as $nivel)
        {
            $return[] = array('text' => $nivel->getNombre(),
                              'state' => array('opened' => true),
                              'icon' => 'fa fa-angle-double-right');
        }

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function nivelesAction($empresa_id, Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();

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

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $nivelesdb = array();
        $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                   WHERE n.empresa = :empresa_id
                                   ORDER BY n.id ASC")
                    ->setParameter('empresa_id', $empresa_id);
        $niveles = $query->getResult();

        foreach ( $niveles as $nivel )
        {
            $nivelesdb[] = array('id'=>$nivel->getId(),
                                 'nombre'=>$nivel->getNombre(),
                                 'delete_disabled' => $f->linkEliminar($nivel->getId(),'AdminNivel'));
        }

        return $this->render('LinkBackendBundle:Nivel:niveles.html.twig', array ('niveles' => $nivelesdb,
                                                                                 'empresa' => $empresa));

    }

    public function ajaxUploadNivelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $nivel_id = $request->request->get('nivel_id');
        $nombre = $request->request->get('nombre');
        $empresa_id = $request->request->get('empresa_id');

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        
        if ($nivel_id){
            $nivel = $em -> getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        }
        else {
            $nivel = new AdminNivel();
        }

        $nivel->setNombre($nombre);
        $nivel->setEmpresa($empresa);
                
        $em->persist($nivel);
        $em->flush();

        $return = array('id' => $nivel->getId(),
                        'nombre' => $nivel->getNombre(),
                        'empresa' => $empresa->getNombre(),
                        'delete_disabled' => $f->linkEliminar($nivel->getId(),'AdminNivel'));

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
    
    public function ajaxEditNivelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $nivel_id = $request->query->get('nivel_id');
        
        $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        
        $return = array('nombre' => $nivel->getNombre());
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxNivelesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        
        $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_id),
                                                                                             array('nombre' => 'ASC'));

        $options = '<option value="0"></option>';
        foreach ($niveles as $nivel)
        {
            $options .= '<option value="'.$nivel->getId().'">'.$nivel->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function uploadNivelesAction($empresa_id, Request $request)
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
        $errores = array();
        
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if ($request->getMethod() == 'POST')
        {

            $file = $request->request->get('file');
            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            $fileWithPath = $yml['parameters']['folders']['dir_uploads'].$file;
            
            if(!file_exists($fileWithPath)) 
            {
                $errores[] = $this->get('translator')->trans('El archivo').' '.$fileWithPath.' '.$this->get('translator')->trans('no existe');
            } 
            else {
                
                $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
              
                // Se obtienen las hojas, el nombre de las hojas y se pone activa la primera hoja
                $total_sheets = $objPHPExcel->getSheetCount();
                $allSheetName = $objPHPExcel->getSheetNames();
                $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
              
                // Se obtiene el número máximo de filas y columnas
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
              
                // $headingsArray contiene las cabeceras de la hoja excel. Los titulos de columnas
                $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
                $headingsArray = $headingsArray[1];

                $col = 0;
                $row = 9;
                $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                return new Response($cell->getValue());
         
                //Se recorre toda la hoja excel desde la fila 2
                $r = -1;
                $niveles = array();
                $col = 0;
                for ($row=2; $row<=$highestRow; ++$row) 
                {

                    $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                    $nombre = trim($cell->getValue());

                    if (!$nombre)
                    {
                        $errores[] = ''
                    }
                }
                return new Response(var_dump($namedDataArray));

            }
        }
        
        return $this->render('LinkBackendBundle:Nivel:uploadNiveles.html.twig', array('empresa' => $empresa,
                                                                                      'errores' => $errores));

    }

}