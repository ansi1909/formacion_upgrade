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
    public function indexAction($app_id,$r,$pagina_id,$empresa_id,Request $request)
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
                $logueado = $re['logueado'] > 0 ? 'Sí' : 'No';
                $phpExcelObject->setActiveSheetIndex(0)
                               ->setCellValue('A1', 'Nombre')
                               ->setCellValue('B1', 'Apellido')
                               ->setCellValue('C1', 'Login')
                               ->setCellValue('D1', 'Correo Personal')
                               ->setCellValue('E1', 'Correp Corporativo')
                               ->setCellValue('F1', 'Activo')
                               ->setCellValue('G1', 'Logueado')
                               ->setCellValue('H1', 'Fecha de registro')
                               ->setCellValue('I1', 'Fecha de nacimiento')
                               ->setCellValue('J1', 'País')
                               ->setCellValue('K1', 'Nivel')
                               ->setCellValue('L1', 'campo1')
                               ->setCellValue('M1', 'campo2')
                               ->setCellValue('N1', 'campo3')
                               ->setCellValue('O1', 'campo4')
                               ->setCellValue('A'.$i, $re['nombre'])
                               ->setCellValue('B'.$i, $re['apellido'])
                               ->setCellValue('C'.$i, $re['login'])
                               ->setCellValue('D'.$i, $re['correo'])
                               ->setCellValue('E'.$i, $re['correo2'])
                               ->setCellValue('F'.$i, $activo)
                               ->setCellValue('G'.$i, $logueado)
                               ->setCellValue('H'.$i, $re['fecha_registro'])
                               ->setCellValue('I'.$i, $re['fecha_nacimiento'])
                               ->setCellValue('J'.$i, $re['pais'])
                               ->setCellValue('K'.$i, $re['nivel'])
                               ->setCellValue('L'.$i, $re['campo1'])
                               ->setCellValue('M'.$i, $re['campo2'])
                               ->setCellValue('N'.$i, $re['campo3'])
                               ->setCellValue('O'.$i, $re['campo4']);
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
                                                                                 'reporte'=>$r,
                                                                                 'pagina_id'=>$pagina_id,
                                                                                 'empresa_dashboard'=>$empresa_id));    
    }

    public function ajaxProgramasEAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_previa');

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina IS NULL')
                    ->setParameter('empresa_id', $empresa_id);
        $paginas = $query->getResult();

        $options = '<option value=""></option>';
        foreach ($paginas as $pagina)
        {
            if ($pagina->getPagina()->getId() == $pagina_id) 
            {
                $options .= '<option value="'.$pagina->getPagina()->getId().'" selected >'.$pagina->getPagina()->getNombre().'  </option>';
            }
            else
            {
                 $options .= '<option value="'.$pagina->getPagina()->getId().'">'.$pagina->getPagina()->getNombre().' </option>';
            }
           
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxListadoParticipantesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

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
                    <tbody style="font-size: .7rem;">';
        
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

    public function interaccionColaborativoAction($app_id, Request $request)
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

        // Lógica inicial de la pantalla de este reporte
        $datos = 'Foo';

        return $this->render('LinkBackendBundle:Reportes:interaccionColaborativo.html.twig', array('datos' => $datos));

    }

    public function interaccionMuroAction($app_id, Request $request)
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
        $reporte = 6;
        $pagina_id = 0;

        // Lógica inicial de la pantalla de este reporte
        $usuario_empresa = 0;
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 

        return $this->render('LinkBackendBundle:Reportes:interaccionMuro.html.twig', array('empresas' => $empresas,
                                                                                           'usuario_empresa' => $usuario_empresa,
                                                                                           'usuario' => $usuario,
                                                                                           'reporte' => $reporte,
                                                                                           'pagina_id'=>$pagina_id));

    }

    public function ajaxLeccionesEAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $pagina_id = $request->query->get('pagina_id');
        $options = '<option value=""></option>';

        $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa_id
                                   AND p.pagina = :pagina_id
                                   AND p.categoria = 2')
                    ->setParameters(array('empresa_id' => $empresa_id , 'pagina_id' => $pagina_id));
        $modulos = $query->getResult();

        //return new Response (var_dump($modulos));

        foreach ( $modulos as $modulo){

            $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                       JOIN pe.pagina p
                                       WHERE pe.empresa = :empresa_id
                                       AND p.pagina = :materia_id
                                       AND p.categoria = 3')
                        ->setParameters(array('empresa_id' => $empresa_id , 'materia_id' => $modulo->getPagina()->getId()));
            $materias = $query->getResult();

            foreach ( $materias as $materia){

                $query = $em->createQuery('SELECT pe,p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                           JOIN pe.pagina p
                                           WHERE pe.empresa = :empresa_id
                                           AND p.pagina = :materia_id
                                           AND p.categoria = 4')
                            ->setParameters(array('empresa_id' => $empresa_id , 'materia_id' => $materia->getPagina()->getId()));
                $lecciones = $query->getResult();



                foreach ($lecciones as $leccion)
                {
                    $options .= '<option value="'.$leccion->getPagina()->getId().'">'.$leccion->getPagina()->getNombre().' </option>'; 
                }

             }

         }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

   /* public function ajaxListadoMuroAction(Request $request)
    {

        

    }*/

    public function reporteGeneralAction($app_id, $empresa_id, Request $request)
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
        $usuarios_activos=0;
        $usuarios_inactivos=0;
        $usuarios_registrados=0;
        $i= 5;

        $query = $em->getConnection()->prepare('SELECT
                                                fnreporte_general(:re, :pempresa_id) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re) {
            if ($re['logueado'] > 0) {
                $usuarios_activos++;
            }else{
                $usuarios_inactivos++;
            }
           
        }

        $query2 = $em->getConnection()->prepare('SELECT
                                                fnreporte_general2(:re, :pempresa_id) as
                                                resultado; fetch all from re;');
        $re1 = 're';
        $query2->bindValue(':re', $re1, \PDO::PARAM_STR);
        $query2->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query2->execute();
        $r1 = $query2->fetchAll();


         // Solicita el servicio de excel
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("formacion")
               ->setLastModifiedBy($session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'])
               ->setTitle("Listado de participantes")
               ->setSubject("Listado de participantes")
               ->setDescription("Listado de participantes")
               ->setKeywords("office 2005 openxml php")
               ->setCategory("Reportes");
            
            //return new Response (var_dump($r1));
            $usuarios_registrados = $usuarios_activos + $usuarios_inactivos;
             foreach ($r1 as $r2) {
                 $i++;
                 $query3 = $em->getConnection()->prepare('SELECT
                                                         fnreporte_general3(:re, :pempresa_id, :ppagina_id) as
                                                         resultado; fetch all from re;');
                 $re2 = 're';
                 $query3->bindValue(':re', $re2, \PDO::PARAM_STR);
                 $query3->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                 $query3->bindValue(':ppagina_id', $r2['id'], \PDO::PARAM_INT);
                 $query3->execute();
                 $r3 = $query3->fetchAll();

                 foreach ($r3 as $r4) {
                    $usuarios_c = $r4['usuarios'];
                 }

                 $query4 = $em->getConnection()->prepare('SELECT
                                                         fnreporte_general4(:re, :pempresa_id, :ppagina_id) as
                                                         resultado; fetch all from re;');
                 $re3 = 're';
                 $query4->bindValue(':re', $re3, \PDO::PARAM_STR);
                 $query4->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
                 $query4->bindValue(':ppagina_id', $r2['id'], \PDO::PARAM_INT);
                 $query4->execute();
                 $r4 = $query4->fetchAll();

                 foreach ($r4 as $r5) {
                    $usuarios_f = $r5['usuarios'];
                 }
                 $usuarios_r = $r2['usuarios'];
                 $usuarios_n = $usuarios_r - ($usuarios_f + $usuarios_c);
                 

                 $phpExcelObject->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'Usuarios registrados')
                                   ->setCellValue('B1', 'Usuarios activos')
                                   ->setCellValue('C1', 'Usuarios inactivos')
                                   ->setCellValue('A2', $usuarios_registrados)
                                   ->setCellValue('B2', $usuarios_activos)
                                   ->setCellValue('C2', $usuarios_inactivos)
                                   ->setCellValue('A5', 'Programas')
                                   ->setCellValue('B5', 'Fecha inicio')
                                   ->setCellValue('C5', 'Fecha fin')
                                   ->setCellValue('D5', 'Usuarios registrados')
                                   ->setCellValue('E5', 'Usuarios cursando')
                                   ->setCellValue('F5', 'Usuarios finalizado')
                                   ->setCellValue('G5', 'Usuarios no iniciados')
                                   ->setCellValue('A'.$i, $r2['programa'])
                                   ->setCellValue('B'.$i, $r2['fecha_inicio'])
                                   ->setCellValue('C'.$i, $r2['fecha_fin'])
                                   ->setCellValue('D'.$i, $r2['usuarios'])
                                   ->setCellValue('E'.$i, $usuarios_c)
                                   ->setCellValue('F'.$i, $usuarios_f)
                                   ->setCellValue('G'.$i, $usuarios_n);
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


    }

    public function ajaxFiltroProgramasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        
        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                    JOIN pe.pagina p 
                                    WHERE pe.empresa = :empresa_id
                                    AND p.pagina IS NULL 
                                    ORDER BY pe.orden')
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

    
}