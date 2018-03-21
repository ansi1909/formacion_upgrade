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
            $descripcion = trim($request->request->get('descripcion'));
            $titulo = trim($request->request->get('titulo'));

            $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);
            $tipoCertificado = $em->getRepository('LinkComunBundle:CertiTipoCertificado')->find($tipo_certificado_id);
            $tipoImagenCertificado = $em->getRepository('LinkComunBundle:CertiTipoImagenCertificado')->find($tipo_imagen_certificado_id);
            
            $certificado->setEntidadId($entidad);
            $certificado->setEmpresa($empresa);
            $certificado->setTipoCertificado($tipoCertificado);
            $certificado->setTipoImagenCertificado($tipoImagenCertificado);
            $certificado->setImagen($imagen);
            $certificado->setEncabezado($encabezado);
            $certificado->setDescripcion($descripcion);
            $certificado->setTitulo($titulo);


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
        $fecha = $f->fechaNatural(date('Y-m-d'));

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
                                                                                      'fecha' => $fecha,
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

    public function generarPdfAction($id_certificado)
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
     
        $fecha = $f->fechaNatural(date('Y-m-d'));

        $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($id_certificado);

        $ruta ='<img src="'.'http://'.$_SERVER['HTTP_HOST'].'/formacion2.0/web/img/codigo_qr.png">';

        $file = 'http://'.$_SERVER['HTTP_HOST'].'/uploads/'.$certificado->getImagen();
        if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['certificado'] )
        {
            //fehca del dia de hoy '.date('d/m/Y').' 
            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(10, 35, 0, 0));
            $certificado_pdf->writeHTML('<page pageset="new" backimg="'.$file.'" backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm"> 
                                            <div style="font-size:24px;">'.$certificado->getEncabezado().'</div>
                                            <div style="text-align:center; font-size:40px; margin-top:60px; text-transform:uppercase;">'.$session->get('usuario')['apellido'].' '.$session->get('usuario')['nombre'].'</div>
                                            <div style="text-align:center; font-size:24px; margin-top:70px; ">'.$certificado->getDescripcion().'</div>
                                            <div style="text-align:center; font-size:50px; margin-top:60px; text-transform:uppercase;">Formación2.0</div>
                                            <div style="text-align:center; font-size:14px; margin-top:40px;">'.$fecha.'</div>
                                            <div style="margin-top:100px; margin-left:950px; ">'.$ruta.'</div>
                                        </page>');
            /*certificado numero 3
            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(48, 60, 0, 0));
            $certificado_pdf->writeHTML('<page pageset="new" backimg="'.$file.'" backtop="0mm" backbottom="0mm" backleft="0mm" backright="0mm"> 
                                            <div style="text-align:center; font-size:24px;">'.$certificado->getEncabezado().'</div>
                                            <div style="text-align:center; font-size:40px; margin-top:60px; text-transform:uppercase;">'.$session->get('usuario')['apellido'].' '.$session->get('usuario')['nombre'].'</div>
                                            <div style="text-align:center; font-size:24px; margin-top:50px; ">'.$certificado->getDescripcion().'</div>
                                            <div style="text-align:center; font-size:30px; margin-top:50px; text-transform:uppercase;">Formación2.0</div>
                                            <div style="text-align:center; font-size:18px; margin-top:10px;">'.$fecha.'</div>
                                            <div style="margin-top:100px; margin-left:950px; ">'.$ruta.'</div>
                                            
                                        </page>');*/
//<qrcode value="http://www.eldesvandejose.com" ec="H" style="margin-top:70px; margin-left:700px; width: 25mm; background-color: white; color: black; border:none"></qrcode>                                        
            //Generamos el PDF
            $certificado_pdf->output('certificiado.pdf');
        }else
        {
            if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['constancia'] )
            {                 
                $certificado_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(15, 60, 15, 5));
                $certificado_pdf->writeHTML('<page orientation="portrait" format="A4" pageset="new" backimg="'.$file.'" backtop="20mm" backbottom="20mm" backleft="0mm" backright="0mm">
                                                <div style=" text-align:center; font-size:20px;">'.$certificado->getEncabezado().'</div>
                                                <div style="margin-top:30px; text-align:center; color: #00558D; font-size:30px;">'.$session->get('usuario')['apellido'].' '.$session->get('usuario')['nombre'].'</div>
                                                <div style="margin-top:40px; text-align:center; font-size:20px;">'.$certificado->getDescripcion().'</div>
                                                <div style="margin-top:30px; text-align:center; color: #00558D; font-size:40px;">Formación2.0</div>
                                                <div style="margin-left:30px; margin-top:30px; text-align:left; font-size:16px; line-height:20px;">'.$certificado->getTitulo().'</div>
                                                <div style="margin-top:40px; text-align:center; font-size:14px;">'.$fecha.'</div>
                                                <div style="margin-top:50px; margin-left:500px; ">'.$ruta.'</div>
                                            </page>');
                $certificado_pdf->output('constancia.pdf');
            }
        }
    }

    public function generarVistaPdfAction($id_certificado)
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
       
        //contultamos el nombre de la aplicacion para reutilizarla en la vista
        $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($id_certificado);
 
        $file = 'http://'.$_SERVER['HTTP_HOST'].'/uploads/'.$certificado->getImagen();

        if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['certificado'] ) 
        {
            return $this->render('LinkBackendBundle:Certificado:certificado.html.twig', array('certificado' => $certificado,
                                                                                              'file' => $file));
        }else
        {
            if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['constancia'] ) 
                return $this->render('LinkBackendBundle:Certificado:constancia.html.twig', array('certificado' => $certificado,
                                                                                                 'file' => $file));
        }
    }

}
