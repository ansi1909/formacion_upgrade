<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Link\ComunBundle\Entity\AdminNivel;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\CertiNivelPagina;
use Symfony\Component\Yaml\Yaml;

class NivelController extends Controller
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
        $fecha_inicio = $request->request->get('fechaInicio');
        $fecha_fin = $request->request->get('fechaFin');
        $fi = explode("/", $fecha_inicio);
        $inicio = $fi[2].'-'.$fi[1].'-'.$fi[0];
        $ff = explode("/", $fecha_fin);
        $fin = $ff[2].'-'.$ff[1].'-'.$ff[0];

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
    
        $nivel = new AdminNivel();

        $nivel->setNombre($nombre);
        $nivel->setEmpresa($empresa);
        $nivel->setFechaInicio(new \DateTime($inicio));
        $nivel->setFechaFin(new \DateTime($fin));
                
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

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
        $nivelesdb = array();
        $query = $em->createQuery("SELECT n FROM LinkComunBundle:AdminNivel n
                                   WHERE n.empresa = :empresa_id
                                   ORDER BY n.nombre ASC")
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
        $fecha_inicio = $request->request->get('fechaInicio');
        $fecha_fin = $request->request->get('fechaFin');
        if ($fecha_inicio && $fecha_fin) {
            $inicio = explode("/",$fecha_inicio);
            $fin = explode("/", $fecha_fin);
            $fecha_inicio = new \DateTime($inicio[2].'-'.$inicio[1].'-'.$inicio[0]);
            $fecha_fin = new \DateTime($fin[2].'-'.$fin[1].'-'.$fin[0]);
        }else{
            $fecha_inicio = NULL;
            $fecha_fin = NULL;
        }

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if ($nivel_id){
            $nivel = $em -> getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
        }
        else {
            $nivel = new AdminNivel();
        }


        $nivel->setNombre($nombre);
        $nivel->setEmpresa($empresa);
        $nivel->setFechaInicio($fecha_inicio);
        $nivel->setFechaFin($fecha_fin);

                
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

        //return new response(var_dump($nivel->getFechaInicio()));
        
        $return = array('nombre' => $nivel->getNombre(),
                        'fechaInicio'=> $nivel->getFechaInicio()? $nivel->getFechaInicio()->format('d/m/Y'):NULL,
                        'fechaFin'=> $nivel->getFechaFin()? $nivel->getFechaFin()->format('d/m/Y'):NULL);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxNivelesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        
        $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_id),
                                                                                             array('nombre' => 'ASC'));

        $options = '<option value="0">Todos los niveles</option>';
        foreach ($niveles as $nivel)
        {
            $options .= '<option value="'.$nivel->getId().'">'.$nivel->getNombre().'</option>';
        }
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxNivelesUsuarioAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $calendario = $request->query->get('calendario');

        if($calendario)
        {
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_id),
                                                                                             array('nombre' => 'ASC'));

            $options = '<option value=""></option>';
            foreach ($niveles as $nivel)
            {
                $nivel_usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneByNivel($nivel->getId());
                if ($nivel_usuario)
                {
                    $options .= '<option value="'.$nivel->getId().'">'.$nivel->getNombre().'</option>';
                }
            }

            $options .= '<option value="">'.$this->get('translator')->trans('Todos los niveles').'</option>';
        }
        else{
            $niveles = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->findBy(array('empresa' => $empresa_id),
                                                                                             array('nombre' => 'ASC'));

            $options = '<option value=""></option>';
            foreach ($niveles as $nivel)
            {
                $nivel_usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneByNivel($nivel->getId());
                if ($nivel_usuario)
                {
                    $options .= '<option value="'.$nivel->getId().'">'.$nivel->getNombre().'</option>';
                }
            }
        }
        
        
        $return = array('options' => $options);
        
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function uploadNivelesAction($empresa_id, Request $request)
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
        $errores = array();
        $nuevos_registros = 0;
        
        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if ($request->getMethod() == 'POST')
        {

            $file = $request->request->get('file');
            $fileWithPath = $this->container->getParameter('folders')['dir_uploads'].$file;
            
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

                //Se recorre toda la hoja excel desde la fila 2
                $r = -1;
                $col = 0;
                $niveles = array();
                for ($row=2; $row<=$highestRow; ++$row) 
                {

                    $cell = $objWorksheet->getCellByColumnAndRow($col, $row);
                    $nombre = trim($cell->getValue());

                    if (!$nombre)
                    {
                        $errores[] = $this->get('translator')->trans('Fila').' '.$row.': '.$this->get('translator')->trans('Dato en nulo.');
                    }
                    else {

                        // Los nombres de niveles repetidos se omiten
                        $query = $em->createQuery('SELECT COUNT(n.id) FROM LinkComunBundle:AdminNivel n 
                                                    WHERE n.empresa = :empresa_id AND LOWER(n.nombre) = :nombre')
                                    ->setParameters(array('empresa_id' => $empresa_id,
                                                          'nombre' => strtolower($nombre)));
                        
                        if (!$query->getSingleScalarResult())
                        {
                            // Lo agregamos en el array de niveles
                            $niveles[] = $nombre;
                        }

                    }

                }

                if (!count($errores) && count($niveles))
                {
                    foreach ($niveles as $n)
                    {
                        $nuevos_registros++;
                        $nivel = new AdminNivel();
                        $nivel->setNombre($n);
                        $nivel->setEmpresa($empresa);
                        $em->persist($nivel);
                        $em->flush();
                    }
                }

            }
            
        }
        
        return $this->render('LinkBackendBundle:Nivel:uploadNiveles.html.twig', array('empresa' => $empresa,
                                                                                      'errores' => $errores,
                                                                                      'nuevos_registros' => $nuevos_registros));

    }

    public function paginasNivelesAction($app_id)
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

        $usuario_empresa = 0;
        $nivelesdb= array();
        $empresas = array();
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']); 

        if ($usuario->getEmpresa()) {
            $usuario_empresa = 1; 

            $query= $em->createQuery('SELECT n FROM LinkComunBundle:AdminNivel n
                                        WHERE n.empresa = :empresa_id
                                        ORDER BY n.nombre ASC')
                                    ->setParameter('empresa_id', $usuario->getEmpresa()->getId());
            $niveles=$query->getResult();

            foreach ($niveles as $nivel)
            {
                $nivelesdb[]= array('id'=>$nivel->getId(),
                              'nombre'=>$nivel->getNombre());

            }
        }
        else {
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findAll();
        } 


        return $this->render('LinkBackendBundle:Nivel:paginasNiveles.html.twig', array('empresas' => $empresas,
                                                                                      'usuario_empresa' => $usuario_empresa,
                                                                                      'usuario' => $usuario,
                                                                                      'niveles' => $nivelesdb)); 

    }

    public function ajaxPaginasNivelesAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $empresa_id = $request->query->get('empresa_id');
        $f = $this->get('funciones');

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $qb = $em->createQueryBuilder();
        $qb->select('n')
           ->from('LinkComunBundle:AdminNivel', 'n')
           ->orderBy('n.nombre', 'ASC');
        $qb->andWhere('n.empresa = :empresa_id');
        $parametros['empresa_id'] = $empresa_id;


        if ($empresa_id)
        {
            $qb->setParameters($parametros);
        }

        $query = $qb->getQuery();
        $niveles_db = $query->getResult();
        $niveles = '<table class="table" id="">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Acciones').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach ($niveles_db as $nivel) {
            $niveles .= '<td>'.$nivel->getNombre().'</td>
            <td class="center">
                
                <a href="#subPanel" class="see" id="see" data="'. $nivel->getId() .'" title="'.$this->get('translator')->trans('Ver').'" class="btn btn-link btn-sm "><span class="fa fa-eye"></span></a>
                
            </td> </tr>';
        }

        $niveles .='</tbody>
                </table>';
        
        $return = array('niveles' => $niveles,
                        'empresa' =>$empresa->getNombre());
 
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxNivelPaginasAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $paginas = array();
        $nivel_id = $request->query->get('nivel_id');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $pagina = '<table class="table">
                    <thead class="sty__title">
                        <tr>
                            <th class="hd__title">'.$this->get('translator')->trans('Nombre').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Categoria').'</th>
                            <th class="hd__title">'.$this->get('translator')->trans('Asignar').'</th>
                        </tr>
                    </thead>
                    <tbody>';

        $nivel = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);

        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                  JOIN pe.pagina p
                                  WHERE pe.empresa = :empresa_id
                                  AND p.pagina IS NULL
                                  AND pe.activo = :activo
                                  AND p.estatusContenido = :contenido_activo
                                  ORDER BY p.orden ASC')
                    ->setParameters(array('empresa_id'=> $nivel->getEmpresa()->getId(),
                                          'activo'=> true,
                                          'contenido_activo'=> $yml['parameters']['estatus_contenido']['activo'] ));

        $pes = $query->getResult();

        foreach($pes as $pe)
        {

            $query = $em->createQuery('SELECT COUNT(pn.id) FROM LinkComunBundle:CertiNivelPagina pn
                                   WHERE pn.paginaEmpresa = :PaginaEmpresa_id
                                   AND pn.nivel = :nivel_id ')
                    ->setParameters(array('PaginaEmpresa_id'=> $pe->getId(),
                                          'nivel_id'=> $nivel->getId()));
            $c = $query->getSingleScalarResult();

            $checked = $c ? 'checked' : '';

            $pagina .= '<tr><td>'.$pe->getPagina()->getNombre().'</td>
                            <td>'.$pe->getPagina()->getCategoria()->getNombre() .'</td>
            <td><div class="can-toggle demo-rebrand-2 small">
                            <input id="f'.$pe->getId().'" class="cb_activo" type="checkbox" '.$checked.' >
                            <input type="hidden" id="id_nivel" name="id_nivel" value="'.$nivel->getId().'">
                            <label for="f'.$pe->getId().'">
                                <div class="can-toggle__switch" data-checked="'.$this->get('translator')->trans('Si').'" data-unchecked="No"></div>
                            </label>
                        </div></td></tr>';
        }

        $pagina .='</tbody>
                </table>';
        $paginas = array('paginas' => $pagina,
                         'nombre' => $nivel->getNombre());
                   
        $return = json_encode($paginas);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxAsignarPAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $id_pagina= $request->request->get('id_pagina');
        $nivel_id = $request->request->get('id_nivel');
        $entity = $request->request->get('entity');
        $checked = $request->request->get('checked');
        

        if ($checked=='0') 
        {
            $nivel_p = $em->getRepository('LinkComunBundle:'.$entity)->findOneBy(array('nivel' => $nivel_id,
                                                                                       'paginaEmpresa' => $id_pagina));
            $em->remove($nivel_p);
            $em->flush();
        }
        else
        {
            $nivel_p = new CertiNivelPagina();
            $pagina = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->find($id_pagina);
            $nivel = $em->getRepository('LinkComunBundle:AdminNivel')->find($nivel_id);
            $nivel_p->setNivel($nivel);
            $nivel_p->setpaginaEmpresa($pagina);

            $em->persist($nivel_p);
            $em->flush();

        }

        $return = array('id' => $nivel_p->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

}