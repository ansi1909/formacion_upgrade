<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiCertificado;
use Symfony\Component\Yaml\Yaml;
use Spipu\Html2Pdf\Html2Pdf;

class CertificadoController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }else 
        {
            $session->set('app_id', $app_id);
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) )
            {
                return $this->redirectToRoute('_authException');
            }
        }

        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();
        $app_id = $session->get('app_id');

        $usuario_empresa = 0;
        if($session->get('administrador')==false)//si no es administrador
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            if ($usuario->getEmpresa()) 
                $usuario_empresa = $usuario->getEmpresa()->getId(); 
        }

        //contultamos el nombre de la aplicacion para reutilizarla en la vista
        $aplicacion = $em->getRepository('LinkComunBundle:AdminAplicacion')->find($app_id);

        $certificados = $em->getRepository('LinkComunBundle:CertiCertificado')->findAll();

        $certificadodb= array();
        if($certificados)
        {
            foreach ($certificados as $certificado)
            {
                $certificadodb[]= array('id'=>$certificado->getId(),
                                    'empresa'=>$certificado->getEmpresa()->getNombre(),
                                    'tipoCertificado'=>$certificado->getTipoCertificado()->getNombre(),
                                    'tipoImagenCertificado'=>$certificado->getTipoImagenCertificado()->getNombre(),
                                    'delete_disabled'=>$f->linkEliminar($certificado->getId(),'CertiCertificado'));
            }
        }

        return $this->render('LinkBackendBundle:Certificado:index.html.twig', array('aplicacion' => $aplicacion,
                                                                                    'certificados' => $certificadodb,
                                                                                    'usuario_empresa' => $usuario_empresa  ));

    }

    public function registroAction($certificado_id, Request $request)
    {

        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) )
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();
     
        $usuario_empresa = 0;
        if($session->get('administrador')==false)//si no es administrador
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            if ($usuario->getEmpresa()) 
                $usuario_empresa = $usuario->getEmpresa()->getId(); 
        }
     
        $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findByActivo(true);
     
        $tipo_certificados = $em->getRepository('LinkComunBundle:CertiTipoCertificado')->findAll(array('nombre' => 'ASC')); 
        $tipo_imagen_certificados = $em->getRepository('LinkComunBundle:CertiTipoImagenCertificado')->findAll(array('nombre' => 'ASC'));

        if ($certificado_id)
            $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($certificado_id);
        else 
            $certificado = new CertiCertificado();

        if ($request->getMethod() == 'POST')
        {
            $empresa_id = $request->request->get('empresa_id');
            $tipo_imagen_certificado_id = $request->request->get('tipo_imagen_certificado_id');
            $tipo_certificado_id = $request->request->get('tipo_certificado_id');
            $entidad = $request->request->get('entidad');
            $imagen = trim($request->request->get('imagen'));
            $encabezado = trim($request->request->get('encabezado'));
            $nombre = 'Nombre del Participante';
            $descripcion = trim($request->request->get('descripcion'));
            $titulo = trim($request->request->get('titulo'));
            $fecha = 'Caracas, 12 de Febero de 2018';
            $qr = 'código qr';

            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $tipoCertificado = $em->getRepository('LinkComunBundle:CertiTipoCertificado')->find($tipo_certificado_id);
            $tipoImagenCertificado = $em->getRepository('LinkComunBundle:CertiTipoImagenCertificado')->find($tipo_imagen_certificado_id);
            
            $certificado->setEntidadId($entidad);
            $certificado->setEmpresa($empresa);
            $certificado->setTipoCertificado($tipoCertificado);
            $certificado->setTipoImagenCertificado($tipoImagenCertificado);
            $certificado->setImagen($imagen);
            $certificado->setEncabezado($encabezado);
            $certificado->setNombre($nombre);
            $certificado->setDescripcion($descripcion);
            $certificado->setTitulo($titulo);
            $certificado->setFecha($fecha);
            $certificado->setQr($qr);

            $em->persist($certificado);
            $em->flush();

            return $this->redirectToRoute('_showCertificado', array('certificado_id' => $certificado->getId()));

        }

        return $this->render('LinkBackendBundle:Certificado:registro.html.twig', array('empresas' => $empresas,
                                                                                       'certificado' => $certificado,
                                                                                       'tipoCertificados' => $tipo_certificados,
                                                                                       'tipoImagenCertificados' => $tipo_imagen_certificados,
                                                                                       'usuario_empresa' => $usuario_empresa  ));

    }

    public function mostrarAction($certificado_id)
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) )
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $usuario_empresa = 0;
        if($session->get('administrador')==false)//si no es administrador
        {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            if ($usuario->getEmpresa()) 
                $usuario_empresa = $usuario->getEmpresa()->getId(); 
        }

        $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($certificado_id);

        $entidad='';
        if($certificado->getEntidadId() != 0)
        {
            if($certificado->getTipoCertificado()->getId() == 2)
            {
                $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($certificado->getEntidadId());

                $entidad = $pagina->getNombre();
            }else
            {
                $grupoPaginas= $em->getRepository('LinkComunBundle:CertiGrupo')->find($certificado->getEntidadId());

                $entidad = $grupoPaginas->getNombre();
            }
        }

        return $this->render('LinkBackendBundle:Certificado:mostrar.html.twig', array('certificado' => $certificado,
                                                                                      'entidad' => $entidad,
                                                                                      'usuario_empresa' => $usuario_empresa ));

    }

    public function ajaxTipoCertificadoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $tipo = $request->query->get('tipo_certificado_id');
        $empresa_id = $request->query->get('empresa_id');
        $error = array('existente' => '');
        $ok = 1;
        $msg = '';
        $html = '';
       
        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if($tipo == 1)
        {
            $html .= '<div class="col-14">
                        <input class="form-control form_sty1" type="hidden" name="entidad" id="entidad" value="0">
                        <span class="fa fa-font"></span>
                      </div>';
        }else
        {

            if($tipo == 2)
            {
                
                $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe 
                                           JOIN pe.pagina p
                                           WHERE pe.empresa= :empresa AND p.pagina IS NULL AND p.estatusContenido = :estatus_contenido_activo and pe.activo = :activo ORDER BY p.nombre ASC')
                            ->setParameters(array('empresa' => $empresa->getId(),
                                                  'estatus_contenido_activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                  'activo' => true ));
                $paginaEmpresa = $query->getResult();
                if($paginaEmpresa)
                {
                    $html .= '<label for="texto" class="col-2 col-form-label">Entidad</label>';
                    $html .= '<div class="col-14">
                                <select class="form-control form_sty_select" name="entidad" id="entidad">'; 
                    foreach ($paginaEmpresa as $pagina)
                    {
                        $nombre = ucwords(mb_strtolower( $pagina->getPagina()->getNombre(),"UTF-8" ));
                        $html .= '<option value="'.$pagina->getPagina()->getId().'">'.$nombre.'</option>';
                    }
                    $html .= '  </select>
                                <span class="fa fa-industry"></span>
                              </div>';                    

                }else
                {
                     $html .= '<div class="col-sm-8 col-md-8 col-lg-8">
                                <label for="texto" class="col-20 col-form-label">La Empresa no tiene Páginas registradas</label>
                                <input class="form-control form_sty1" type="hidden" name="entidad" id="entidad" value="">
                               </div>';
                }
            }else
            {
                if($tipo == 3)
                {
                    $grupoPaginas = $em->getRepository('LinkComunBundle:CertiGrupo')->findByEmpresa($empresa->getId());
                    if($grupoPaginas)
                    {
                        $html .= '<label for="texto" class="col-2 col-form-label">Entidad</label>';
                        $html .= '<div class="col-14">
                                    <select class="form-control form_sty_select" name="entidad" id="entidad">';                        
                        foreach ($grupoPaginas as $pagina)
                        {
                            $nombre = ucwords(mb_strtolower( $pagina->getNombre(),"UTF-8" ));
                            $html .= '<option value="'.$pagina->getId().'">'.$nombre.'</option>';
                        }
                        $html .= '  </select>
                                <span class="fa fa-industry"></span>
                              </div>';
                    }else
                    {
                         $html .= '<div class="col-sm-8 col-md-8 col-lg-8">
                                    <label for="texto" class="col-20 col-form-label">La Empresa no tiene Grupo de Páginas registradas</label>
                                    <input class="form-control form_sty1" type="hidden" name="entidad" id="entidad" value="">
                                   </div>';
                    }
                }
            }  
        }

        $return = array('ok' => $ok,'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function generarPdfAction()
    {
        $session = new Session();
        $f = $this->get('funciones');
      
        if (!$session->get('ini'))
        {
            return $this->redirectToRoute('_loginAdmin');
        }
        else {
            if (!$f->accesoRoles($session->get('usuario')['roles'], $session->get('app_id')) )
            {
                return $this->redirectToRoute('_authException');
            }
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        //Recogemos el contenido de la vista
        ob_start(); 
        // $html= $this->render('LinkBackendBundle:Certificado:certificado.html.twig'); 
        //les content pour le pdf

        //require_once 'certificado.html.twig';
        $html=ob_get_clean(); 

        //Le indicamos el tipo de hoja y la codificación de caracteres
        $certificado = new Html2Pdf('P','A4','es','true','UTF-8');
        $certificado->writeHTML('<h1>Hola mundo</h1>');
        //$certificado->writeHTML($html);
        //Generamos el PDF
        $certificado->output('certificiado.pdf');

       

//Incluimos la librería
 /*   require_once 'html2pdf_v4.03/html2pdf.class.php';
     
    //Recogemos el contenido de la vista
    ob_start(); 
    require_once 'vistaImprimir.php';
    $html=ob_get_clean(); 
 
    //Pasamos esa vista a PDF
     
    //Le indicamos el tipo de hoja y la codificación de caracteres
    $mipdf=new HTML2PDF('P','A4','es','true','UTF-8');
 
    //Escribimos el contenido en el PDF
    $mipdf->writeHTML($html);
 
    //Generamos el PDF
    $mipdf->Output('PdfGeneradoPHP.pdf');
*/

    }

}
