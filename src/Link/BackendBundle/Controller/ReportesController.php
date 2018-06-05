<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Link\ComunBundle\Entity\AdminSesion;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Yaml\Yaml;

class ReportesController extends Controller
{
    public function indexAction($app_id,$r,Request $request)
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

        if ($request->getMethod() == 'POST')
        {
            $empresa_id = $request->request->get('empresa_id');
            $reporte = $request->request->get('reporte');
            $pagina_id = $request->request->get('programa_id');
            $nivel_id = $request->request->get('nivel_id');
        	$nivel_id = $nivel_id ? $nivel_id : 0;
        	$pagina_id = $pagina_id ? $pagina_id : 0;
        	$i = 1;
        	$query = $em->getConnection()->prepare('SELECT
	                                                fnlistado_participantes(:re, :preporte, :pempresa_id, :pnivel_id, :ppagina_id) as
	                                                resultado; fetch all from re;');
	        $re = 're';
	        $query->bindValue(':re', $re, \PDO::PARAM_STR);
	        $query->bindValue(':preporte', $reporte, \PDO::PARAM_INT);
	        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
	        $query->bindValue(':pnivel_id', $nivel_id, \PDO::PARAM_INT);
	        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
	        $query->execute();
	        $r = $query->fetchAll();

	        // Solicita el servicio de excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("formacion")
               ->setLastModifiedBy($session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'])
               ->setTitle("Listado de participantes")
               ->setSubject("Listado de participantes")
               ->setDescription("Listado de participantes")
               ->setKeywords("office 2005 openxml php")
               ->setCategory("Reportes");
            foreach ($r as $re) {
      		    $i++;
      		    $activo = $re['activo'] ? 'Sí' : 'No';
       		    $phpExcelObject->setActiveSheetIndex(0)
       		   				   ->setCellValue('A1', 'Nombre')
                               ->setCellValue('B1', 'Apellido')
                               ->setCellValue('C1', 'Login')
                               ->setCellValue('D1', 'Correo')
                               ->setCellValue('E1', 'Activo')
                               ->setCellValue('F1', 'Fecha de registro')
                               ->setCellValue('G1', 'Fecha de nacimiento')
                               ->setCellValue('H1', 'País')
                               ->setCellValue('I1', 'Nivel')
                               ->setCellValue('A'.$i, $re['nombre'])
                               ->setCellValue('B'.$i, $re['apellido'])
                               ->setCellValue('C'.$i, $re['login'])
                               ->setCellValue('D'.$i, $re['correo'])
                               ->setCellValue('E'.$i, $activo)
                               ->setCellValue('F'.$i, $re['fecha_registro'])
                               ->setCellValue('G'.$i, $re['fecha_nacimiento'])
                               ->setCellValue('H'.$i, $re['pais'])
                               ->setCellValue('I'.$i, $re['nivel']);
        	}
            $phpExcelObject->getActiveSheet()->setTitle('Participantes');

            // Crea el writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
            //$writer->setUseBOM(true);
            //$yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
            //$writer->save($this->container->getParameter('folders')['dir_uploads'].'recursos/participantes/data.csv');
            
            // Envia la respuesta del controlador
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // Agrega los headers requeridos
            $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'ListadoDeParticipantes.xlsx'
            );

            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            $response->headers->set('Content-Disposition', $dispositionHeader);

            return $response;


	            //return new Response('Archivo creado...');

        	//return new Response (var_dump($r));

        }

        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 

        return $this->render('LinkBackendBundle:Reportes:index.html.twig', array('empresas' => $empresas,
			                                                                                        'usuario_empresa' => $usuario_empresa,
			                                                                                        'usuario' => $usuario,
			                                                                                        'reporte'=>$r));	
    }

    public function ajaxProgramasEAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina IS NULL')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxListadoParticipantesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $empresa_id = $request->query->get('empresa_id');
        $nivel_id = $request->query->get('nivel_id');
        $pagina_id = $request->query->get('pagina_id');
        $reporte = $request->query->get('reporte');

        // Llamada a la función de BD que trae el listado de participantes
        $query = $em->getConnection()->prepare('SELECT
                                                fnlistado_participantes(:re, :preporte, :pempresa_id, :pnivel_id, :ppagina_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':preporte', $reporte, \PDO::PARAM_INT);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':pnivel_id', $nivel_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();
        
        $html = '<table class="table" id="dt">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Apellido').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Login').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Correo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Activo').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de registro').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Fecha de nacimiento').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('País').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Nivel').'</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($r as $ru)
        {
            $activo = $ru['activo'] ? $this->get('translator')->trans('Sí') : 'No';
            $html .= '<tr>
                        <td>'.$ru['nombre'].'</td>
                        <td>'.$ru['apellido'].'</td>
                        <td>'.$ru['login'].'</td>
                        <td>'.$ru['correo'].'</td>
                        <td>'.$activo.'</td>
                        <td>'.$ru['fecha_registro'].'</td>
                        <td>'.$ru['fecha_nacimiento'].'</td>
                        <td>'.$ru['pais'].'</td>
                        <td>'.$ru['nivel'].'</td>
                    </tr>';
        }

        $html .= '</tbody>
                </table>';
        
        $return = array('html' => $html);
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}