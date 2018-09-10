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
            $empresas[] = array('id' => $empresa->getId(),
                                'nombre' => $empresa->getNombre(),
                                'pais' => $empresa->getPais(),
                                'fechaCreacion' => $empresa->getFechaCreacion(),
                                'activo' => $empresa->getActivo(),
                                'delete_disabled' => $f->linkEliminar($empresa->getId(), 'AdminEmpresa'));
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

        if ($empresa_id) 
        {
            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
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
            $bienvenida = $request->request->get('bienvenida');
            $activo = $request->request->get('activo');
            $activo2 = $request->request->get('activo2');
            $activo3 = $request->request->get('activo3');

            $pais = $this->getDoctrine()->getRepository('LinkComunBundle:AdminPais')->find($pais_id);

            $empresa->setNombre($nombre);
            $empresa->setActivo($activo ? true : false);
            $empresa->setChatActivo($activo2 ? true : false);
            $empresa->setWebinar($activo3 ? true : false);
            $empresa->setBienvenida($bienvenida);
            $empresa->setPais($pais);
            $em->persist($empresa);
            $em->flush();

            // Se crea el directorio para los activos de la empresa
            $f->subDirEmpresa($empresa->getId(), $this->container->getParameter('folders'));

            return $this->redirectToRoute('_showEmpresa', array('empresa_id' => $empresa->getId()));

        }
        
        return $this->render('LinkBackendBundle:Empresa:registro.html.twig', array('empresa' => $empresa,
                                                                                   'paises' => $paises));

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

    public function registradosEmpresaAction( Request $request )
    {
        $session = new Session();
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

        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/participantesEmpresa.xlsx';
        $objPHPExcel = \PHPExcel_IOFactory::load($fileWithPath);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

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
                    $activo = ($participante['activo'])? 1:0;
                    $competencia = ($participante['competencia'])? 1:0;

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

         $writer = $this->get('phpexcel')->createWriter($objPHPExcel, 'Excel5');
         $path = 'recursos/reportes/participantesEmpresa'.$session->get('sesion_id').'.xls';
         $xls = $this->container->getParameter('folders')['dir_uploads'].$path;
         $writer->save($xls);

         $archivo = $this->container->getParameter('folders')['uploads'].$path;
         $html = ($ok == 1)? '<a href ="'.$archivo.'" class="btn btn-link btn-sm enlaces" id="excelFile'.$empresa_id.'"  ><span class="fa fa-download"> </span></a>':'<strong>'.$empresa->getNombre().', </strong>'. '<span>'. $this->get('translator')->trans('no posee participantes registrados').'</span>';
         
         $return = array('html'=>$html,'ok'=>$ok);
         $return = json_encode($return);
         return new Response($return, 200, array('Content-Type' => 'application/json'));          
        
    }

}
