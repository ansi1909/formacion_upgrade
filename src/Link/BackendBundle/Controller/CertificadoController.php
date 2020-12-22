<?php

namespace Link\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiCertificado;
use Symfony\Component\Yaml\Yaml;
use Spipu\Html2Pdf\Html2Pdf;
use ZipArchive;

class CertificadoController extends Controller
{
    public function indexAction($app_id, Request $request)
    {
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $certificadodb = array();

        foreach ($certificados as $certificado)
        {
            if ($usuario_empresa)
            {
                if ($certificado->getEmpresa()->getId() == $usuario_empresa)
                {
                    $certificadodb[] = array('id' => $certificado->getId(),
                                             'empresa' => $certificado->getEmpresa()->getNombre(),
                                             'tipoCertificado' => $certificado->getTipoCertificado()->getNombre(),
                                             'tipoImagenCertificado' => $certificado->getTipoImagenCertificado()->getNombre(),
                                             'delete_disabled' => $f->linkEliminar($certificado->getId(),'CertiCertificado'));
                }
            }
            else {
                $certificadodb[] = array('id' => $certificado->getId(),
                                         'empresa' => $certificado->getEmpresa()->getNombre(),
                                         'tipoCertificado' => $certificado->getTipoCertificado()->getNombre(),
                                         'tipoImagenCertificado' => $certificado->getTipoImagenCertificado()->getNombre(),
                                         'delete_disabled' => $f->linkEliminar($certificado->getId(),'CertiCertificado'));
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

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        $empresas = $em->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                               array('nombre' => 'ASC'));

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

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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

        if($certificado->getTipoCertificado()->getId() == 1)
        {
            $entidad=$certificado->getEmpresa()->getNombre();
        }else
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

        $certificado_id = $request->query->get('certificado_id');
        $tipo_certificado_id = $request->query->get('tipo_certificado_id');
        $empresa_id = $request->query->get('empresa_id');

        $error = 0;
        $html = '';

        $empresa = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        $tipo_certificado = $em->getRepository('LinkComunBundle:CertiTipoCertificado')->find($tipo_certificado_id);

        $certificado = false;

        if($tipo_certificado->getId()==1)//validamos que no exista un tipo de certificado para la empresa
            $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('tipoCertificado' => $tipo_certificado->getId(),
                                                                                                   'empresa' => $empresa->getId()));

        if($certificado)
        {
            $tipo_imagen='';
            if($certificado->getTipoImagenCertificado()->getId() == 1)
                $tipo_imagen='Certificado';
            else
                $tipo_imagen='Constancia';

            $error= 1;

            $html .= '<div class="col-14">
                        <label>La empresa ya tiene registrado un '.$tipo_imagen.'.</label>
                      </div>';
        }else
        {
            if($tipo_certificado->getId() == 1)
            {
                $html .= '<div class="col-14">
                            <input class="form-control form_sty1" type="hidden" name="entidad" id="entidad" value="0">
                            <span class="fa fa-font"></span>
                          </div>';
            }else
            {

                $certificados = $em->getRepository('LinkComunBundle:CertiCertificado')->findBy(array('tipoCertificado' => $tipo_certificado->getId(),
                                                                                                     'empresa' => $empresa->getId()));

               $entidad_ids = array();
                if($certificado_id!=0)
                {
                    $certificado_especifico = $em->getRepository('LinkComunBundle:CertiCertificado')->find($certificado_id);

                    foreach ($certificados as $certificado) {
                        if($certificado_especifico->getEntidadId() != $certificado->getEntidadId())
                            $entidad_ids[] = $certificado->getEntidadId();
                    }
                }else
                {
                    foreach ($certificados as $certificado) {
                            $entidad_ids[] = $certificado->getEntidadId();
                    }
                }

                if($tipo_certificado->getId() == 2)
                {

                    if(count($entidad_ids) == 0)
                    {
                        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                   JOIN pe.pagina p
                                                   WHERE pe.empresa= :empresa AND p.pagina IS NULL AND p.estatusContenido = :estatus_contenido_activo and pe.activo = :activo ORDER BY p.nombre ASC')
                                    ->setParameters(array('empresa' => $empresa->getId(),
                                                          'estatus_contenido_activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                          'activo' => true ));
                        $paginaEmpresa = $query->getResult();
                    }else
                    {
                        $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                   JOIN pe.pagina p
                                                   WHERE pe.empresa= :empresa AND p.pagina IS NULL AND pe.pagina NOT IN (:entidad) AND p.estatusContenido = :estatus_contenido_activo and pe.activo = :activo ORDER BY p.nombre ASC')
                                    ->setParameters(array('empresa' => $empresa->getId(),
                                                          'estatus_contenido_activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                          'entidad' => $entidad_ids,
                                                          'activo' => true ));
                        $paginaEmpresa = $query->getResult();
                    }

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
                        $certificado_especifico=false;

                        if($certificado_id!=0)
                            $certificado_especifico = $em->getRepository('LinkComunBundle:CertiCertificado')->find($certificado_id);

                        if($certificado_especifico)
                        {

                            $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                       JOIN pe.pagina p
                                                       WHERE pe.empresa= :empresa AND p.pagina IS NULL AND pe.pagina = :entidad AND p.estatusContenido = :estatus_contenido_activo and pe.activo = :activo ORDER BY p.nombre ASC')
                                        ->setParameters(array('empresa' => $empresa->getId(),
                                                              'estatus_contenido_activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                              'entidad' => $certificado_especifico->getEntidadId(),
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
                            }
                        }else
                        {
                            $error = 1;
                            $html .= '<div class="col-sm-14 col-md-14 col-lg-14">
                                        <label for="texto" class="col-20 col-form-label">'.$this->get('translator')->trans('La empresa no tiene páginas asignadas o ya fueron asignadas').'.</label>
                                        <input class="form-control form_sty1" type="hidden" name="entidad" id="entidad" value="">
                                       </div>';
                        }
                    }
                }else
                {
                    if($tipo_certificado->getId() == 3)
                    {

                        if(count($entidad_ids) == 0)
                        {
                            $grupoPaginas = $em->getRepository('LinkComunBundle:CertiGrupo')->findByEmpresa($empresa->getId());
                        }else
                        {
                            $query = $em->createQuery('SELECT cg FROM LinkComunBundle:CertiGrupo cg
                                                       WHERE cg.empresa= :empresa AND cg.id NOT IN (:entidad)')
                                        ->setParameters(array('empresa' => $empresa->getId(),
                                                              'entidad' => $entidad_ids ));
                            $grupoPaginas = $query->getResult();
                        }

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
                            $error = 1;
                            $html .= '<div class="col-sm-14 col-md-14 col-lg-14">
                                        <label for="texto" class="col-20 col-form-label">La Empresa no tiene Grupo de Páginas registradas o ya fueron asignadas.</label>
                                        <input class="form-control form_sty1" type="hidden" name="entidad" id="entidad" value="">
                                       </div>';
                        }
                    }
                }
            }
        }

        $return = array('error' => $error, 'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function generarPdfAction($id_certificado)
    {
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $contenidoMod = '';

        $certificado = $em->getRepository('LinkComunBundle:CertiCertificado')->find($id_certificado);

        if ($certificado->getTipoCertificado()->getId() != $yml['parameters']['tipo_certificado']['empresa'])
        {

            if ($certificado->getTipoCertificado()->getId() == $yml['parameters']['tipo_certificado']['pagina'])
            {

                $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($certificado->getEntidadId());
                $articulo = $pagina->getCategoria()->getId() == $yml['parameters']['categoria']['materia'] || $pagina->getCategoria()->getId() == $yml['parameters']['categoria']['leccion'] ? $this->get('translator')->trans('de la') : $this->get('translator')->trans('del');
                $contenidoMod .= '<div style="font-size:21px;text-align:center"> <h1>'.$this->get('translator')->trans('Contenido').' '.$articulo.' '.$pagina->getCategoria()->getNombre().': '.$pagina->getNombre().'</h1>';

                $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                            JOIN pe.pagina p
                                            WHERE pe.empresa = :empresa_id AND p.pagina = :pagina_id
                                            ORDER BY pe.orden ASC')
                            ->setParameters(array('empresa_id' => $certificado->getEmpresa()->getId(),
                                                  'pagina_id' => $pagina->getId()));
                $subpaginas = $query->getResult();

                foreach ($subpaginas as $subpagina)
                {
                    $contenidoMod .= '<h2> * '.$subpagina->getPagina()->getCategoria()->getNombre().' '.$subpagina->getOrden().': '.$subpagina->getPagina()->getNombre().'</h2>';
                }

                $contenidoMod .= '</div>';

            }
            else {

                $grupos = $em->getRepository('LinkComunBundle:CertiGrupoPagina')->findByGrupo($certificado->getEntidadId());

                foreach ($grupos as $grupo)
                {

                    $pagina = $grupo->getPagina();
                    $articulo = $pagina->getCategoria()->getId() == $yml['parameters']['categoria']['materia'] || $pagina->getCategoria()->getId() == $yml['parameters']['categoria']['leccion'] ? $this->get('translator')->trans('de la') : $this->get('translator')->trans('del');
                    $contenidoMod .= '<div style="font-size:21px;text-align:center"> <h1>'.$this->get('translator')->trans('Contenido').' '.$articulo.' '.$pagina->getCategoria()->getNombre().': '.$pagina->getNombre().'</h1>';

                    $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                JOIN pe.pagina p
                                                WHERE pe.empresa = :empresa_id AND p.pagina = :pagina_id
                                                ORDER BY pe.orden ASC')
                                ->setParameters(array('empresa_id' => $certificado->getEmpresa()->getId(),
                                                      'pagina_id' => $pagina->getId()));
                    $subpaginas = $query->getResult();

                    foreach ($subpaginas as $subpagina)
                    {
                        $contenidoMod .= '<h2> * '.$subpagina->getPagina()->getCategoria()->getNombre().' '.$subpagina->getOrden().': '.$subpagina->getPagina()->getNombre().'</h2>';
                    }

                    $contenidoMod .= '</div>';

                }

            }

        }

        $programa = '';

        if($certificado->getTipoCertificado()->getId() == $yml['parameters']['tipo_certificado']['empresa'])//por empresa
        {
            $programa = $certificado->getEmpresa()->getNombre();
        }
        else {
            if($certificado->getTipoCertificado()->getId() == $yml['parameters']['tipo_certificado']['pagina'])//por pagina
            {
                $programa = $pagina->getNombre();
            }
            else {
                $grupoPaginas = $em->getRepository('LinkComunBundle:CertiGrupo')->find($certificado->getEntidadId());
                $programa = $grupoPaginas->getNombre();
            }
        }

        $ruta ='<img src="'.$this->container->getParameter('folders')['dir_project'].'web/img/codigo_qr.png">';
        $file = $this->container->getParameter('folders')['dir_uploads'].$certificado->getImagen();

        if($certificado->getTipoImagenCertificado()->getId() == $yml['parameters']['tipo_imagen_certificado']['certificado'] )
        {

            $comodines = array('%%categoria%%');
            $reemplazos = array('Programa');
            $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());


            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(10, 35, 0, 0));
            $certificado_pdf->writeHTML('<page pageset="new" backimg="'.$file.'" backimgw="90%" backimgx="center">
                                        <div>
                                            <div style="font-size:22px;margin-top:95px;text-align:center">'.$certificado->getEncabezado().'</div>
                                            <div style="text-align:center; font-size:40px; margin-top:25px; text-transform:uppercase;">'.$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'].'</div>
                                            <div style="text-align:center; font-size:24px; margin-top:25px; margin-left:50px; margin-right:50px;">'.$descripcion.'</div>
                                            <div style="text-align:center; font-size:40px; margin-top:25px; text-transform:uppercase;">'.$programa.'</div>
                                            <div style="text-align:center;margin-top:40px;font-size:14px;">'.$this->get('translator')->trans('Fecha inicio').': dd/mm/aa  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->get('translator')->trans('Fecha fin').': dd/mm/aa </div>
                                            <div style="text-align:center;margin-top:15px;font-size:14px;">'.$this->get('translator')->trans('Equivalente a').': x hrs. '.$this->get('translator')->trans('académicas').'</div>
                                            <div style="text-align:center; font-size:14px; margin-top:20px;">'.$fecha.'</div>
                                            <div style="margin-top:60px; margin-left:910px; ">'.$ruta.'</div>
                                        </div>

                                        </page>');

            if ($contenidoMod != '')
            {
                $certificado_pdf->writeHtml('<page title="prueba" pageset="new"  backimgw="90%" backimgx="center">'
                                                .$contenidoMod.'
                                            </page>');
            }

            $certificado_pdf->output('certificado.pdf');

        }
        else {
            $certificado_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(5, 60, 10, 5));
            $certificado_pdf->writeHTML('<page orientation="portrait" format="A4" pageset="new" backimg="'.$file.'" backtop="20mm" backbottom="20mm" backleft="0mm" backright="0mm">
                                            <div style=" text-align:center; font-size:20px;">'.$certificado->getEncabezado().'</div>
                                            <div style="margin-top:30px; text-align:center; color: #00558D; font-size:30px;">'.$session->get('usuario')['nombre'].' '.$session->get('usuario')['apellido'].'</div>
                                            <div style="margin-top:40px; text-align:center; font-size:20px;">'.$certificado->getDescripcion().'</div>
                                            <div style="margin-top:30px; text-align:center; color: #00558D; font-size:40px;">'.$programa.'</div>
                                            <div style="margin-left:30px; margin-top:30px; text-align:left; font-size:16px; line-height:20px;">'.$certificado->getTitulo().'</div>
                                            <div style="margin-top:40px; text-align:center; font-size:14px;">'.$fecha.'</div>
                                            <div style="margin-top:50px; margin-left:500px; ">'.$ruta.'</div>
                                        </page>');
            $certificado_pdf->output('constancia.pdf');
        }

    }

    public function generarCertificadosAction($app_id)
    {
        $session = new Session();
        $f = $this->get('funciones');

        if (!$session->get('ini') || $f->sesionBloqueda($session->get('sesion_id')))
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
        $empresa_select = $session->get('usuario')['empresa'];
        $pagina_select = null;
        $pagina = 0;

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $empresas = array();

        $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));


        $resultado= null;
        return $this->render('LinkBackendBundle:Certificado:generarCertificado.html.twig', array('usuario' => $usuario,
                                                                                                 'empresas' => $empresas,
                                                                                                 'empresa_select' => $empresa_select,
                                                                                                 'pagina_select' => $pagina_select,
                                                                                                 'resultado' => $resultado,
                                                                                                 'pagina' => $pagina));

    }

    public function GenerarCertificadosZipAction($app_id, Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $empresa_id = $request->request->get('empresa_id');
        $pagina_id = $request->request->get('programa_id');
        $fechaD = $request->request->get('fechaD');
        $fechaH = $request->request->get('fechaH');
        $fi = explode("/", $fechaD);
        $inicio = $fi[2].'-'.$fi[1].'-'.$fi[0];
        $ff = explode("/", $fechaH);
        $fin = $ff[2].'-'.$ff[1].'-'.$ff[0];
        //return new response($inicio);
        $uploads = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $values = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
        $nombrePagina = $f->eliminarAcentos($pagina->getNombre());
        $query = $em->getConnection()->prepare('SELECT
                                                fnlistado_certificados(:re, :pempresa_id, :ppagina_id, :pinicio, :pfin) as
                                                resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pempresa_id', $empresa_id, \PDO::PARAM_INT);
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->bindValue(':pinicio', $inicio, \PDO::PARAM_STR);
        $query->bindValue(':pfin', $fin, \PDO::PARAM_STR);
        $query->execute();
        $rs = $query->fetchAll();

        //return new response(var_dump($rs));
        if($rs)
        {
            $zip = new ZipArchive();

            $dirpath = $uploads['parameters']['folders']['dir_uploads'].'recursos/tmp';
            $salida = $uploads['parameters']['folders']['dir_uploads'].'recursos';

            $zip->open($dirpath.'/certificados-'.$nombrePagina.'.zip', ZipArchive::CREATE);
            $contador = 0;
            foreach($rs as $usuario)
            {

                //return new response('aqui');
                $query = $em->createQuery("SELECT pe, p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                            JOIN pe.pagina p
                                            WHERE pe.empresa = :empresa_id AND p.pagina = :pagina_id
                                            ORDER BY p.orden ASC")
                            ->setParameters(array('empresa_id' => $empresa_id,
                                                'pagina_id' => $pagina_id));
                $subpages = $query->getResult();

                foreach($subpages as $subpage)
                {
                    $modulos[] = array('nombre' => $subpage->getPagina()->getNombre());
                    $fecha_vencimiento = $subpage->getFechaVencimiento()->format('Y/m/d');
                    $modulo_id = $subpage->getId();
                    $subpaginas_ids[] = $subpage->getId();

                    //return new response(var_dump($subpaginas_ids));
                }

                $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
                $categoria = $pagina->getCategoria()->getNombre();

                $contenidoMod = '<div style="font-size:21px;text-align:center"> <h1>'.$this->get('translator')->trans('Contenido del').' '.$categoria.': '.$pagina->getNombre().'</h1>';

                $item = 1;
                foreach ($subpages as $modulo)
                {
                    $contenidoMod .= '<h2> * '.$this->get('translator')->trans('Módulo').' '.$item.': '.$modulo->getPagina()->getNombre().'</h2>';
                    $item += 1;
                }
                $contenidoMod .= '</div>';


                if ($pagina)
                {

                    $certificado = $f->getCertificado($empresa_id, $values['parameters']['tipo_certificado'], $pagina->getId());

                    //return new response (var_dump($certificado->getId()));

                    if ($certificado)//si existe certificado imprimimos el documento
                    {

                        //cambiamos la fecha al formato aaaa-mm-dd
                        $fn_array = explode("/", $fecha_vencimiento);
                        $d = $fn_array[0];
                        $m = $fn_array[1];
                        $a = $fn_array[2];
                        $fecha_vencimiento = "$a-$m-$d";

                        $fecha = $f->fechaNatural($fecha_vencimiento);

                        $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $usuario['id'],
                                                                                                            'pagina' => $pagina->getId() ));

                        $size = 2;
                        $contenido = $uploads['parameters']['folders']['verificar_codigo_qr'].'/'.$pagina_log->getId();

                        $nombre = $pagina->getId().'_'.$usuario['id'].'.png';

                        $directorio = $uploads['parameters']['folders']['dir_uploads'].'recursos/qr/'.$nombre;

                        \PHPQRCode\QRcode::png($contenido, $directorio, 'H', $size, 4);

                        $ruta ='<img src="'.$directorio.'">';

                        $file = $uploads['parameters']['folders']['dir_uploads'].$certificado->getImagen();


                        if ($certificado->getTipoImagenCertificado()->getId() == $values['parameters']['tipo_imagen_certificado']['certificado'] )
                        {

                            /*certificado numero 2*/
                        if ($pagina_log->getPagina()->getCategoria()->getNombre() == 'Curso') {

                                $comodines = array('%%categoria%%');
                                $reemplazos = array('Curso');
                                $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());
                            }
                            else{
                                $comodines = array('%%categoria%%');
                                $reemplazos = array('Programa');
                                $descripcion = str_replace($comodines, $reemplazos, $certificado->getDescripcion());
                            }

                            $certificado_pdf = new Html2Pdf('L','A4','es','true','UTF-8',array(0, 15, 0, 0));
                            $certificado_pdf->writeHTML('<page title="Certificado" pageset="new" backimg="'.$file.'" backimgw="90%" backimgx="center">
                                                            <div style="margin-left:910px; ">'.$ruta.'</div>
                                                            <div style="font-size:22px; margin-top:90px; text-align:center">'.$certificado->getEncabezado().'</div>
                                                            <div style="text-align:center; font-size:40px; margin-top:25px; text-transform:uppercase;">'.$usuario['nombre'].' '.$usuario['apellido'].'</div>
                                                            <div style="text-align:center; font-size:24px; margin-top:25px; ">'.$descripcion.'</div>
                                                            <div style="text-align:center; font-size:40px; margin-top:25px; text-transform:uppercase;">'.$pagina->getNombre().'</div>
                                                            <div style="text-align:center; margin-top:40px; font-size:14px;">'.$this->get('translator')->trans('Fecha inicio').':'.$pagina_log->getFechaInicio()->format("d/m/Y").'   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->get('translator')->trans('Fecha fin').':'.$pagina_log->getFechaFin()->format("d/m/Y").' </div>
                                                            <div style="text-align:center; margin-top:15px; font-size:14px;">'.$this->get('translator')->trans('Equivalente a').': '.$pagina->getHorasAcademicas().' hrs. '.$this->get('translator')->trans('académicas').'</div>
                                                        </page>');

                            $certificado_pdf->writeHtml('<page title="prueba" pageset="new"  backimgw="90%" backimgx="center">'
                                                            .$contenidoMod.'
                                                        </page>');

                            //Generamos el PDF
                            $certificado_pdf->output($uploads['parameters']['folders']['dir_uploads'].'recursos/tmp/certificado-'.$usuario['nombre'].'-'.$usuario['apellido'].'-'.$pagina_log->getId().'.pdf', 'F');


                            $zip->addFile($dirpath.'/certificado-'.$usuario['nombre'].'-'.$usuario['apellido'].'-'.$pagina_log->getId().'.pdf', 'certificado/certificado-'.$usuario['nombre'].'-'.$usuario['apellido'].'-'.$pagina_log->getId().'.pdf');





                            $paginasEmpresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findBy(array('empresa' => $empresa_id,
                                                                                                                    'pagina' => $modulo_id));

                            $notas = false;
                            foreach($paginasEmpresa as $paginaEmpresa)
                            {
                                $notas = $paginaEmpresa->getPruebaActiva();
                                //return new response (var_dump($notas));
                            }

                            if($notas)
                            {
                                $nota = 0;

                                //se consulta la informacion de la pagina padre
                                $query = $em->createQuery('SELECT pl.nota as nota FROM LinkComunBundle:CertiPruebaLog pl
                                                            JOIN pl.prueba p
                                                            WHERE p.pagina = :pagina
                                                            and pl.estado = :estado
                                                            and pl.usuario = :usuario')
                                                ->setParameters(array('usuario' => $usuario['id'],
                                                                    'pagina' => $pagina_id,
                                                                    'estado' => $values['parameters']['estado_prueba']['aprobado']))
                                                ->setMaxResults(1);
                                $nota_programa = $query->getResult();

                                //return new response(var_dump($nota_programa));

                                foreach ($nota_programa as $n)
                                {
                                    $nota = (double)$n['nota'];
                                    //return new response($nota.'aqui');
                                }

                                $cantidad_intentos = '';
                                $query = $em->createQuery('SELECT count(pl.id) FROM LinkComunBundle:CertiPruebaLog pl
                                                        JOIN pl.prueba p
                                                        WHERE p.pagina = :pagina
                                                        and pl.usuario = :usuario')
                                            ->setParameters(array('usuario' => $usuario['id'],
                                                                'pagina' => $pagina_id));
                                $cantidad_intentos = $query->getSingleScalarResult();
                                $cantidad_intentos = $cantidad_intentos ? $cantidad_intentos : '';

                                $programa_aprobado = array('id' => $pagina_id,
                                                        'nombre' => $pagina->getNombre(),
                                                        'categoria' => $values['parameters']['categoria']['programa'],
                                                        'nota' => $nota,
                                                        'cantidad_intentos' => $cantidad_intentos ? $cantidad_intentos : '');

                                if (count($subpaginas_ids))
                                {
                                    //$subpaginas_ids = $f->hijas($subpages);


                                    //return new response(var_dump($pagina->getId()));


                                    $programa_aprobado = $f->notasPrograma($subpaginas_ids, $usuario['id'], $values['parameters']['estado_prueba']['aprobado']);

                                }

                                if ($programa_aprobado)
                                {

                                    if($session->get('empresa')['logo']!='')
                                    {
                                        $file = $uploads['parameters']['folders']['dir_uploads'].$session->get('empresa')['logo'];

                                    }
                                    else {
                                        $file =  $uploads['parameters']['folders']['dir_project'].'web/img/logo_formacion_smart.png';
                                    }

                                    //return new response($file);

                                    $constancia_pdf = new Html2Pdf('P','A4','es','true','UTF-8',array(15, 10, 15, 5));

                                    $html = "<!DOCTYPE html>
                                        <html lang='es'>
                                            <head>
                                                <meta charset='UTF-8'>
                                                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                                                <meta http-equiv='X-UA-Compatible' content='ie=edge'>
                                                <title>Constancia de notas</title>
                                                <style type='text/css'>
                                                    body.detalle-noticias {
                                                        background-color: #fff; }
                                                    .constancia {
                                                        padding: 2px 0px; }
                                                    .constancia .imgConst {
                                                        width: 250px;
                                                        height: 72px; }
                                                    .constancia .tituloConst {
                                                        color: #5CAEE6;
                                                        font-size: 2.25rem;
                                                        font-weight: 400;
                                                        line-height: 10px;}
                                                    .constancia .datosParticipante {
                                                        margin-top: 1rem;
                                                        border-radius: 1rem;
                                                        background: #FAFAFA; }
                                                    .constancia .textConst {
                                                        color: #212529;
                                                        font-size: 1.5rem;
                                                        font-weight: 400;
                                                        line-height: 25px;
                                                        text-aling:justify; }
                                                    .constancia .datosParticipante .tituloPart, .constancia .tituloPart {
                                                        padding: 4px;
                                                        color: #5C6266;
                                                        font-size: 1.125rem;
                                                        font-weight: 400;
                                                        line-height: 10px; }
                                                    .row {
                                                        display: flex; flex-wrap: wrap; padding: 4px; }
                                                    .center {
                                                        text-align: center;}
                                                    .table-notas {
                                                        font-size: 1.125rem;
                                                        line-height: 10px;
                                                        font-weight: 300;
                                                        text-align: left; }
                                                    .table-notas thead th {
                                                        line-height: 10px;
                                                        color: #212529;
                                                        font-weight: 300;
                                                        text-align: center;
                                                            padding: 6px; }
                                                    .table-notas tbody tr {
                                                        color: #5CAEE6; }
                                                    .table-notas tbody td {
                                                        border-bottom:1px solid #CFD1D2;
                                                        padding: 4px;
                                                        cellpadding: 0;
                                                        cellspacing: 0; }
                                                    hr {
                                                        color: #99c51b;
                                                        background-color: #99c51b;
                                                        height: 5px;
                                                    }
                                                </style>
                                            </head>
                                            <body class='detalle-noticias'>
                                                <div class='constancia'>
                                                    <div class='row center'>
                                                        <img class='imgConst' src='".$file."'/>
                                                        <h3 class='tituloConst'>".$this->get('translator')->trans('Constancia de Notas')."</h3>
                                                    </div>
                                                    <div class='row'>
                                                        <div class='datosParticipante'>
                                                            <div class='row'>
                                                                <span class='tituloPart'>".$this->get('translator')->trans('Participante').": <span>".$usuario['nombre'].' '.$session->get('usuario')['apellido']."</span></span>
                                                            </div>
                                                            <div class='row'>
                                                                <span class='tituloPart'>".$this->get('translator')->trans('Correo electrónico').": <span>".$usuario['correo']."</span></span>
                                                            </div>
                                                            <div class='row'>
                                                                <span class='tituloPart'>".$this->get('translator')->trans('Programa').": <span>".$pagina->getNombre()."</span></span>
                                                            </div>
                                                            <div class='row'>
                                                                <span class='tituloPart'>".$this->get('translator')->trans('Inicio del programa').": <span>".$paginaEmpresa->getFechaInicio()->format('d/m/Y')."</span></span>
                                                            </div>
                                                            <div class='row'>
                                                                <span class='tituloPart'>".$this->get('translator')->trans('Fin del programa').": <span>".$paginaEmpresa->getFechaVencimiento()->format('d/m/Y')."</span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='row'>
                                                        <p class='textConst'>".$this->get('translator')->trans('Por medio de la presente se certifica que el participante arriba indicado ha cursado y aprobado las pruebas correspondientes a').":</p>
                                                    </div>
                                                    <div class='row'>";
                                                        $puntaje = 0;
                                                        $indice = 0;
                                                        if (count($subpaginas_ids))
                                                        {
                                                            $valor = '';
                                                            $style = '';
                                                            $guion = '';
                                                            //return new response($nota);
                                                            $puntaje = $puntaje + $nota;
                                                            $nota = $cantidad_intentos != '' ? number_format((double)$nota, 2, ',', '.') : '';
                                                            $html .= "<table class='table-notas'>
                                                                <thead>
                                                                    <tr>
                                                                        <th style='width: 380;'>".$this->get('translator')->trans('Módulos')."</th>
                                                                        <th style='width: 100;'>".$this->get('translator')->trans('Puntaje')."</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr style='font-size: 14px; font-weight: 300;'>
                                                                    <td style='padding-left:10px;'>".$pagina->getnombre()."</td>
                                                                    <td class='center'>".$nota."</td>
                                                                    </tr>";
                                                            foreach ($programa_aprobado as $programa)
                                                            {

                                                                if ($programa['categoria'] == $values['parameters']['categoria']['modulo'])
                                                                {
                                                                    $valor = 20;
                                                                    $guion = '';
                                                                }
                                                                else {
                                                                    if ($programa['categoria'] == $values['parameters']['categoria']['materia'])
                                                                    {
                                                                        $valor = 30;
                                                                        $guion = '+ ';
                                                                    }
                                                                    else {
                                                                        if ($programa['categoria'] == $values['parameters']['categoria']['leccion'])
                                                                        {
                                                                            $valor = 40;
                                                                            $guion = '- ';
                                                                        }
                                                                    }
                                                                }
                                                                $puntaje = $puntaje+$programa['nota'];

                                                                if($programa['nota'] != 0)
                                                                {
                                                                    $indice = $indice+1;
                                                                    $nota = $programa['nota'];
                                                                }
                                                                $html .= "<tr ".$style.">
                                                                            <td style='padding-left:".$valor."px;'>".$guion.$programa['nombre']."</td>
                                                                            <td class='center'>".number_format($nota, 2, ',', '.')."</td>
                                                                        </tr>";
                                                            }
                                                            if ($indice > 0)
                                                            {
                                                                $html .= "<tr style='font-size:16px; font-weight:300; font-color:#000000;'>
                                                                            <td style='color:#000000;'>".$this->get('translator')->trans('Puntaje definitivo del programa').":</td>
                                                                            <td style='color:#000000;' class='center'>".number_format($puntaje/$indice, 2, ',', '.')."</td>
                                                                        </tr>";
                                                            }
                                                            $margin = '50';
                                                            $html .= "</tbody>
                                                                </table>";
                                                        }
                                                        else {
                                                            $puntaje = $programa_aprobado['nota'];
                                                            if ($programa_aprobado['nota'] != 0)
                                                            {
                                                                $nota = $programa_aprobado['nota'];
                                                            }
                                                            else {
                                                                $nota = "N/A";
                                                            }
                                                            $margin = '200';
                                                            $html .= "<table class='table-notas' cellpadding='0' cellspacing='0'>
                                                                        <thead>
                                                                            <tr>
                                                                                <th style='width: 380;'>".$this->get('translator')->trans('Módulos')."</th>
                                                                                <th style='width: 100;'>".$this->get('translator')->trans('Puntaje')."</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <tr style='font-size: 14px; font-weight: 300;'>
                                                                                <td style='padding-left:10px;'>".$session->get('paginas')[$programa_id]['nombre']."</td>
                                                                                <td class='center'>".number_format($nota, 2, ',', '.')."</td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>";
                                                        }
                                                    $html .= "</div>
                                                    <div class='row' style='margin-top:".$margin."px;'>
                                                        <table text-align='center' width='600' border=0' height='50'>
                                                            <tr>
                                                                <td width='150' class='center'>_____________________</td>
                                                                <td width='150'></td>
                                                                <td width='150' class='center'>_____________________</td>
                                                            </tr>
                                                            <tr>
                                                                <td width='150' class='center'>".$this->get('translator')->trans('Firma del Participante')."</td>
                                                                <td width='150'></td>
                                                                <td width='150' class='center'>".$this->get('translator')->trans('Firma del Supervisor')."</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class='row' style='margin-top:".$margin."px;'>
                                                        <hr>
                                                    </div>
                                                </div>
                                            </body>
                                        </html>";

                                    $constancia_pdf->WriteHTML($html);
                                    $constancia_pdf->output($uploads['parameters']['folders']['dir_uploads'].'recursos/tmp/notas-'.$usuario['id'].'-'.$pagina_log->getId().'.pdf', 'F');

                                    $zip->addFile($dirpath.'/notas-'.$usuario['id'].'-'.$pagina_log->getId().'.pdf', 'notas/notas-'.$usuario['id'].'-'.$pagina_log->getId().'.pdf');

                                }

                            }

                        }

                    }
                    else {
                        return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'certificado'));
                    }
                }



            }
            //return new response ($contador);
            $zip->close();
            /*header("Content-type: application/zip");
            header("Content-disposition: attachment; filename=certificados-$nombrePagina.zip");
            header('Content-Length: ' . filesize($dirpath.'/certificados-'.$nombrePagina.'.zip'));*/
            $ruta_zip= $dirpath.'/certificados-'.$nombrePagina.'.zip';
            //readfile($dirpath.'/certificados-'.$nombrePagina.'.zip');
            /*$files = glob($dirpath.'/*'); //obtenemos todos los nombres de los ficheros
            foreach($files as $file){
            if(is_file($file))
            unlink($file); //elimino el fichero
            }*/

            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));
            $resultado = true;

            return $this->render('LinkBackendBundle:Certificado:generarCertificado.html.twig', array('usuario' => $usuario,
                                                                                                    'empresas' => $empresas,
                                                                                                    'empresa_select' => $empresa_id,
                                                                                                    'pagina_select' => $pagina_id,
                                                                                                    'resultado' => $resultado,
                                                                                                    'pagina' => $nombrePagina,
                                                                                                    'ruta' => $ruta_zip));
        }else{

            $resultado = false;

            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            $empresas = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->findBy(array('activo' => true),
                                                                                                    array('nombre' => 'ASC'));


            return $this->render('LinkBackendBundle:Certificado:generarCertificado.html.twig', array('usuario' => $usuario,
                                                                                                    'empresas' => $empresas,
                                                                                                    'empresa_select' => $empresa_id,
                                                                                                    'pagina_select' => $pagina_id,
                                                                                                    'resultado' => $resultado,
                                                                                                    'pagina' => $nombrePagina));

        }


    }

    public function DescargarZipAction($ruta)
    {
        $uploads = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));
        $dirpath = $uploads['parameters']['folders']['dir_uploads'].'recursos/tmp';
        $f = $this->get('funciones');
        $nombrePagina = $f->eliminarAcentos($ruta);
            header("Content-type: application/zip");
            header("Content-disposition: attachment; filename=certificados-$nombrePagina.zip");
            header('Content-Length: ' . filesize($dirpath.'/certificados-'.$nombrePagina.'.zip'));
            //$ruta_zip= $dirpath.'/certificados-'.$nombrePagina.'.zip';
            readfile($dirpath.'/certificados-'.$nombrePagina.'.zip');
            $files = glob($dirpath.'/*'); //obtenemos todos los nombres de los ficheros
            foreach($files as $file){
            if(is_file($file))
            unlink($file); //elimino el fichero
            }
    }

}
