<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\AdminEmpresa;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Yaml\Yaml;

class EmpresaController extends Controller
{
   public function indexAction($app_id)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
      
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
        
        $r = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa');
        $empresas_db = $r->findAll();

        $empresas = array();
        foreach ($empresas_db as $empresa)
        {
            $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminRolUsuario ru
                                        JOIN ru.usuario u
                                        join u.nivel n
                                        WHERE u.empresa = :empresa_id
                                        AND  u.activo = :activo
                                        AND ru.rol = :rol_id
                                        and LOWER(n.nombre) not like :revisor
                                        and LOWER(n.nombre) not like :tutor')
                        ->setParameters(array('empresa_id' => $empresa->getId(),
                                            'activo' => 'true',
                                            'rol_id' => $yml['parameters']['rol']['participante'],
                                            'revisor' => 'revisor%',
                                            'tutor' => 'tutor%'));
            $usuarios_activos = $query->getSingleScalarResult();

            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'pais' => $empresa->getPais(),
                                'fechaCreacion' => $empresa->getFechaCreacion(),
                                'activo' => $empresa->getActivo(),
                                'delete_disabled' => $f->linkEliminar($empresa->getId(), 'AdminEmpresa'),
                                'limite_usuarios' => $empresa->getLimiteUsuarios() ? $empresa->getLimiteUsuarios() : 'N/A',
                                'usuarios_acceso' => $usuarios_activos);
        }

        return $this->render('LinkBackendBundle:Empresa:index.html.twig', array('empresas'=>$empresas));

    }

    public function registroAction($empresa_id, Request $request){
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->findOneById2($session->get('code'));
        $horarios = $this->prepareHours($pais->getId());

        if ($empresa_id) 
        {
            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $horarios =  $this->prepareHours($empresa->getPais()->getId());
            if($empresa->getZonaHoraria()){
                for ($i=0; $i <count($horarios) ; $i++) { 
                    if($horarios[$i]->id == $empresa->getZonaHoraria()->getId()){
                        $horarios[$i]->selected='selected';
                    }
                }

            }

        }
        else {
            $empresa = new AdminEmpresa();
            $empresa->setPais($pais);
            $empresa->setFechaCreacion(new \DateTime('now'));
        }

        // Lista de paises
        $qb = $em->createQueryBuilder();
        $qb->select('p')
           ->from('LinkComunBundle:AdminPais', 'p')
           ->orderBy('p.nombre', 'ASC');
        $query = $qb->getQuery();
        $paises = $query->getResult();

        if ($request->getMethod() == 'POST')
        {
            
            $nombre = $request->request->get('nombre');
            $pais_id = $request->request->get('pais_id');
            $zona_id = $request->request->get('zona_id');
            $limite_usuarios = $request->request->get('limite_usuarios');
            $bienvenida = $request->request->get('bienvenida');
            $activo = $request->request->get('activo');
            $activo2 = $request->request->get('activo2');
            $activo3 = $request->request->get('activo3');
            $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id);
            $zona = ($zona_id)? $this->getDoctrine()->getRepository('LinkComunBundle:AdminZonaHoraria')->find($zona_id):NULL;
            $empresa->setNombre($nombre);
            $empresa->setActivo($activo ? true : false);
            $empresa->setChatActivo($activo2 ? true : false);
            $empresa->setWebinar($activo3 ? true : false);
            $empresa->setBienvenida($bienvenida);
            $empresa->setPais($pais);
            $empresa->setZonaHoraria($zona);
            $empresa->setLimiteUsuarios($limite_usuarios ? $limite_usuarios : null);
            $em->persist($empresa);
            $em->flush();
            $f->subDirEmpresa($empresa->getId(), $this->container->getParameter('folders'));

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));

        }
        return $this->render('LinkBackendBundle:Empresa:registro.html.twig', array('empresa' => $empresa,
                                                                                   'paises' => $paises,
                                                                                   'zonas'=> $horarios));

    }

    public function mostrarAction($empresa_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        return $this->render('LinkBackendBundle:Empresa:mostrar.html.twig', array('empresa' => $empresa));
    }

    public function ajaxZonaHorariaAction(Request $request){
        $ok = 0;
        $em = $this->getDoctrine()->getManager();
        $session = new Session();
        $a = $this->get('funciones');
        $pais_id = $request->request->get('pais_id');
       
        $zonaHoraria = $this->prepareHours($pais_id);
        if($zonaHoraria){
          $ok = 1;
          $zonaHoraria = json_encode($zonaHoraria);
        }

        $return = array('zonaHoraria'=>$zonaHoraria,'ok'=>$ok);
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));  
    }

    public function prepareHours($pais_id,$horarioDefault=false){
        $horarios = array();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT zh FROM LinkComunBundle:AdminZonaHoraria zh
                                      WHERE zh.pais = :pais_id 
                                      ORDER BY zh.nombre ASC")
                    ->setParameters(array('pais_id' => $pais_id));
        $horariosQuery = $query->getResult();

        foreach ($horariosQuery as $horario) {
            $names = explode("/", $horario->getNombre());
            $len = count($names);
            $nombre = str_replace("_"," ",$names[$len-1]);
            array_push($horarios,(object)array('id'=>$horario->getId(),'zona'=>$nombre,'selected'=>''));
       }
       if ($horarioDefault) {
            $horarios[0]->selected ='selected'; //seleccionar el primer huso horario por defecto
       }

       return $horarios;
    }

    public function registradosEmpresaAction( Request $request )
    {
        $session = new Session();
        $a = $this->get('funciones');
        
        $empresa_id = $request->request->get('empresa_id');
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare('SELECT
                                                    fnlistado_participantes(:re, :preporte, :pempresa_id, :pnivel_id, :ppagina_id) as
                                                    resultado; fetch all from re;');
        $ok = 0;
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':preporte', 1, \PDO::PARAM_INT);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':pnivel_id', 0, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', 0, \PDO::PARAM_INT);
        $query->execute();
        $registrados = $query->fetchAll();

        $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/participantesEmpresa.xlsx';
        $spreadsheet = $readerXlsx->load($fileWithPath);
        $objWorksheet = $spreadsheet->setActiveSheetIndex(0);

        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M','N','O');

        $objWorksheet->setCellValue('A1', $this->get('translator')->trans('Usuarios registrados'));
        $objWorksheet->setCellValue('A2', $this->get('translator')->trans('Empresa').': '.$empresa->getNombre());

        if (count($registrados)>0) {
            
            
            $row = 5;
            $last_row=($row+count($registrados))-1;
            $i = 0;
            $styleThinBlackBorderOutline = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                );

            $font_size = 11;
            $font = 'Arial';
            $horizontal_aligment = \PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
            $vertical_aligment = \PHPExcel_Style_Alignment::VERTICAL_CENTER;

            // Estilizar las celdas antes de insertar los datos
                for ($f=$row; $f<=$last_row; $f++)
                {
                        $objWorksheet->getStyle("A$f:O$f")->applyFromArray($styleThinBlackBorderOutline); //bordes
                        $objWorksheet->getStyle("A$f:O$f")->getFont()->setSize($font_size); // Tamaño de las letras
                        $objWorksheet->getStyle("A$f:O$f")->getFont()->setName($font); // Tipo de letra
                        $objWorksheet->getStyle("A$f:O$f")->getAlignment()->setHorizontal($horizontal_aligment); // Alineado horizontal
                        $objWorksheet->getStyle("A$f:O$f")->getAlignment()->setVertical($vertical_aligment); // Alineado vertical
                        $objWorksheet->getStyle("A$f:O$f")->getAlignment()->setWrapText(true);//ajustar texto a la columna
                        $objWorksheet->getRowDimension($f)->setRowHeight(35); // Altura de la fila
                }

                foreach ($registrados as $participante)
                {

                    $correo = ($participante['correo'])? $participante['correo']:$participante['correo2'];
                    $activo = ($participante['activo'])? 'Sí' : 'No';
                    $competencia = ($participante['competencia'])? 'Sí' : 'No';

                    // Datos de las columnas del reporte
                    $objWorksheet->setCellValue('A'.$row, $participante['codigo']);
                    $objWorksheet->setCellValue('B'.$row, $participante['login']);
                    $objWorksheet->setCellValue('C'.$row, $participante['nombre']);
                    $objWorksheet->setCellValue('D'.$row, $participante['apellido']);
                    $objWorksheet->setCellValue('E'.$row, $participante['fecha_registro']);
                    $objWorksheet->setCellValue('F'.$row, $participante['clave']);
                    $objWorksheet->setCellValue('G'.$row, $correo);
                    $objWorksheet->setCellValue('H'.$row, $competencia);
                    $objWorksheet->setCellValue('I'.$row, $participante['pais']);
                    $objWorksheet->setCellValue('J'.$row, $participante['campo1']);
                    $objWorksheet->setCellValue('K'.$row, $participante['campo2']);
                    $objWorksheet->setCellValue('L'.$row, $participante['campo3']);
                    $objWorksheet->setCellValue('M'.$row, $participante['campo4']);
                    $objWorksheet->setCellValue('N'.$row, $participante['nivel']);
                    $objWorksheet->setCellValue('O'.$row, $activo);
                  
                    $row++;
                }


                $ok = 1;
        }

        
        
         $empresaName = $a->eliminarAcentos($empresa->getNombre());
         $empresaName = strtoupper($empresaName);
         $hoy = date('y-m-d h i');
         $writer = $this->get('phpoffice.spreadsheet')->createWriter($spreadsheet, 'Xlsx');
         $path = 'recursos/reportes/PARTICIPANTES '.$empresaName.' '.$hoy.'.xls';
         $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
         $writer->save($xls);

         $archivo = $this->container->getParameter('folders')['uploads'].$path;
         $html = ($ok == 1)? '<a href ="'.$archivo.'" class="btn btn-link btn-sm enlaces" id="excelFile'.$empresa_id.'"  ><span class="fa fa-download"> </span></a>':'<strong>'.$empresa->getNombre().', </strong>'. '<span>'. $this->get('translator')->trans('no posee participantes registrados').'</span>';
         
         $return = array('html'=>$html,'ok'=>$ok);
         $return = json_encode($return);
         return new Response($return, 200, array('Content-Type' => 'application/json'));          
        
    }

}
