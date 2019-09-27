<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Link\ComunBundle\Entity\CertiForo;
use Link\ComunBundle\Entity\CertiForoArchivo;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Model\UploadHandler;


class ColaborativoController extends Controller
{
    public function indexAction($programa_id, $subpagina_id, Request $request)
    {

    	$session = new Session();
        $f = $this->get('funciones');
        
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $programa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($programa_id);

        // Foros vacíos, eliminarlos
        $query = $em->createQuery("SELECT f FROM LinkComunBundle:CertiForo f 
                                    WHERE f.pagina = :programa_id 
                                    AND f.empresa = :empresa_id 
                                    AND f.foro IS NULL 
                                    AND f.tema IS NULL 
                                    ORDER BY f.fechaPublicacion DESC")
                    ->setParameters(array('programa_id' => $programa_id,
                                          'empresa_id' => $session->get('empresa')['id']));
        $foros_bd = $query->getResult();
        foreach ($foros_bd as $foro)
        {
            $foro_archivo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForoArchivo')->findByForo($foro->getId());
            foreach ($foro_archivo as $fa)
            {
                $file = $this->container->getParameter('folders')['dir_uploads'].$fa->getArchivo();
                if (file_exists($file))
                {
                    unlink($file);
                }
                $em->remove($fa);
                $em->flush();
            }
            $dirname = $this->container->getParameter('folders')['dir_uploads'].'recursos/espacio/'.$session->get('empresa')['id'].'/'.$foro->getId().'/';
            $f->delete_folder($dirname);
            $em->remove($foro);
            $em->flush();
        }

        $query = $em->createQuery("SELECT f FROM LinkComunBundle:CertiForo f 
                                    WHERE f.pagina = :programa_id 
                                    AND f.empresa = :empresa_id 
                                    AND f.foro IS NULL 
                                    ORDER BY f.fechaPublicacion DESC")
                    ->setParameters(array('programa_id' => $programa_id,
                                          'empresa_id' => $session->get('empresa')['id']));
        $foros_bd = $query->getResult();

        $foros = array();

        foreach ($foros_bd as $foro)
        {
            $listar = 0;
            if ($foro->getFechaPublicacion()->format('Y-m-d') > date('Y-m-d') || $foro->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d'))
            {
                if ($session->get('usuario')['tutor'])
                {
                    $listar = 1;
                    if ($foro->getFechaPublicacion()->format('Y-m-d') > date('Y-m-d'))
                    {
                        $name_ft = $this->get('translator')->trans('Aún sin publicar');
                    }
                    else {
                        $name_ft = $this->get('translator')->trans('Vencido');
                    }
                    $coment_f = 0;
                    $coment_f_span = '';
                    $resp_ft = 0;
                }
            }
            else {
                $listar = 1;
                // Último comentario realizado sobre este tema
                $foro_hijo = $em->getRepository('LinkComunBundle:CertiForo')->findOneBy(array('foro' => $foro->getId()),
                                                                                        array('fechaRegistro' => 'DESC'));
                if (!$foro_hijo)
                {
                    $name_ft = $foro->getUsuario()->getNombre().' '.$foro->getUsuario()->getApellido();
                    $coment_f = 0;
                    $coment_f_span = '';
                    $resp_ft = 0;
                }
                else {
                    $query = $em->createQuery('SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f 
                                                WHERE f.foro = :foro_id')
                                ->setParameter('foro_id', $foro->getId());
                    $total_comentarios = $query->getSingleScalarResult();
                    $name_ft = $foro_hijo->getUsuario()->getNombre().' '.$foro_hijo->getUsuario()->getApellido();
                    $coment_f = 1;
                    $coment_f_span = $f->sinceTime($foro_hijo->getFechaRegistro()->format('Y-m-d H:i:s'));
                    $resp_ft = $total_comentarios;
                }
            }
            if ($listar)
            {
                $publicacion = $foro->getFechaPublicacion()->format('d/m/Y');
                $vencimiento = $foro->getFechaVencimiento()->format('d/m/Y');
                $foros[] = array('id' => $foro->getId(),
                                 'tema' => $foro->getTema(),
                                 'name_ft' => $name_ft,
                                 'coment_f' => $coment_f,
                                 'coment_f_span' => $coment_f_span,
                                 'resp_ft' => $resp_ft,
                                 'publicacion' => $publicacion,
                                 'vencimiento' => $vencimiento,
                                 'usuario_id' => $foro->getUsuario()->getId());
            }
        }

        return $this->render('LinkFrontendBundle:Colaborativo:index.html.twig', array('programa' => $programa,
                                                                                      'foros' => $foros,
                                                                                      'subpagina_id' => $subpagina_id));

    }

    public function ajaxSaveForoAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();

        $foro_id = $request->request->get('foro_id');
        $pagina_id = $request->request->get('pagina_id');
        $tema = $request->request->get('tema');
        $fechaPublicacion = $request->request->get('fechaPublicacion');
        $fechaVencimiento = $request->request->get('fechaVencimiento');
        $mensaje = $request->request->get('mensaje_content');

        if (!$foro_id)
        {

            $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            $foro = new CertiForo();
            $foro->setFechaRegistro(new \DateTime('now'));
            $foro->setPagina($pagina);
            $foro->setEmpresa($empresa);
            $foro->setUsuario($usuario);

        }
        else {
            $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        }

        $foro->setTema($tema);
        $foro->setMensaje($mensaje);

        list($d, $m, $a) = explode("/", $fechaPublicacion);
        $fechaPublicacion = "$a-$m-$d";
        $foro->setFechaPublicacion(new \DateTime($fechaPublicacion));

        list($d, $m, $a) = explode("/", $fechaVencimiento);
        $fechaVencimiento = "$a-$m-$d";
        $foro->setFechaVencimiento(new \DateTime($fechaVencimiento));

        $em->persist($foro);
        $em->flush();

        if (!$foro_id)
        {

            // Se crea el subdirectorio para los archivos del espacio colaborativo
            $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];
            $dir = $dir_uploads.'recursos/espacio/'.$empresa->getId().'/'.$foro->getId().'/';
            if (!file_exists($dir) && !is_dir($dir))
            {
                mkdir($dir, 750, true);
            }

        }

        $return = array('foro_id' => $foro->getId());

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxEditForoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        
        $foro_id = $request->query->get('foro_id');

        $html = '';

        $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);

        // Archivos
        $archivos_bd = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForoArchivo')->findBy(array('foro' => $foro_id),
                                                                                                       array('fechaRegistro' => 'ASC'));
        foreach ($archivos_bd as $archivo)
        {
            $archivo_arr = $f->archivoForo($archivo, $session->get('usuario')['id']);
            $href = $this->container->getParameter('folders')['uploads'].$archivo_arr['archivo'];
            $html .= '<li class="element-downloads" id="archivo-'.$archivo_arr['id'].'">
                        <div class="row px-0 d-flex justify-content-between">
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0 d-flex">
                                <img src="'.$archivo_arr['img'].'" class="imgdl" alt="">
                                <p class="nameArch">'.$archivo_arr['descripcion'].'</p>
                            </div>
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0">
                                <div class="cont-opc">
                                    <a href="'.$href.'" target="_blank"><span class="material-icons icDl" data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Descargar archivo').'">file_download</span></a>
                                    <span class="color-light-grey barra">|</span>
                                    <a href="#attachments"><span class="material-icons color-light-grey icDl delete" data="'.$archivo_arr['id'].'" id="iconCloseDownloads" title="'.$this->get('translator')->trans('Eliminar').'" data-toggle="tooltip" data-placement="bottom">clear</span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row px-0 justify-content-start">
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0 d-flex">
                                <p class="nameUpload">'.$archivo_arr['usuario'].'</p>
                            </div>
                        </div>
                    </li>';
        }
        
        $return = array('tema' => $foro->getTema(),
                        'fechaPublicacion' => $foro->getFechaPublicacion()->format('d/m/Y'),
                        'fechaVencimiento' => $foro->getFechaVencimiento()->format('d/m/Y'),
                        'fechaPublicacionF' => $foro->getFechaPublicacion()->format('Y-m-d'),
                        'fechaVencimientoF' => $foro->getFechaVencimiento()->format('Y-m-d'),
                        'mensaje' => $foro->getMensaje(),
                        'html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxSearchForoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        $term = $request->query->get('term');
        $foros = array();

        $qb = $em->createQueryBuilder();
        $qb->select('f')
           ->from('LinkComunBundle:CertiForo', 'f')
           ->where('f.foro IS NULL')
           ->andWhere('LOWER(f.tema) LIKE :term')
           ->setParameter('term', '%'.$term.'%');

        $query = $qb->getQuery();
        $foros_bd = $query->getResult();

        foreach ($foros_bd as $foro)
        {
            $foros[] = array('id' => $foro->getId(),
                             'label' => $foro->getTema(),
                             'value' => $foro->getTema());
        }

        $return = json_encode($foros);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function detalleAction($foro_id, $subpagina_id)
    {

        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $em = $this->getDoctrine()->getManager();

        $foro = $em->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        $likes = $f->likes($yml['parameters']['social']['espacio_colaborativo'], $foro->getId(), $session->get('usuario')['id']);
        $timeAgo = $f->sinceTime($foro->getFechaRegistro()->format('Y-m-d H:i:s'));

        $foros_hijos = $f->forosHijos($foro_id, 0, 5, $session->get('usuario'), $yml['parameters']['social']['espacio_colaborativo']);

        // Total aportes de este espacio
        $query = $em->createQuery('SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f 
                                    WHERE f.foro = :foro_id')
                    ->setParameter('foro_id', $foro_id);
        $total_aportes = $query->getSingleScalarResult();

        // Archivos del foro
        $archivos = array();
        $archivos_bd = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForoArchivo')->findBy(array('foro' => $foro_id),
                                                                                                       array('fechaRegistro' => 'ASC'));
        foreach ($archivos_bd as $archivo)
        {
            $archivos[] = $f->archivoForo($archivo, $session->get('usuario')['id']);
        }

        $programa = $foro->getPagina();
        
        return $this->render('LinkFrontendBundle:Colaborativo:detalle.html.twig', array('programa' => $programa,    
                                                                                        'foro' => $foro,
                                                                                        'likes' => $likes,
                                                                                        'timeAgo' => $timeAgo,
                                                                                        'foros_hijos' => $foros_hijos,
                                                                                        'subpagina_id' => $subpagina_id,
                                                                                        'total_aportes' => $total_aportes,
                                                                                        'archivos' => $archivos));

    }

    public function ajaxDeleteForoAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $foro_id = $request->request->get('foro_id');

        // Se eliminan primero las alarmas
        $query = $em->createQuery("SELECT a FROM LinkComunBundle:AdminAlarma a 
                                    WHERE a.tipoAlarma IN (:tipos) 
                                    AND a.entidadId = :foro_id")
                    ->setParameters(array('tipos' => array($yml['parameters']['tipo_alarma']['espacio_colaborativo'],
                                                           $yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo']),
                                          'foro_id' => $foro_id));
        $alarmas = $query->getResult();

        foreach ($alarmas as $alarma)
        {
            $em->remove($alarma);
            $em->flush();
        }

        // Ahora los sub-foros
        $foros_hijos = $em->getRepository('LinkComunBundle:CertiForo')->findByForo($foro_id);

        foreach ($foros_hijos as $foro_hijo)
        {
            $em->remove($foro_hijo);
            $em->flush();
        }

        // Eliminación de los archivos asociados
        $archivos = $em->getRepository('LinkComunBundle:CertiForoArchivo')->findByForo($foro_id);

        foreach ($archivos as $archivo)
        {
            $file = $this->container->getParameter('folders')['dir_uploads'].$archivo->getArchivo();
            if (file_exists($file))
            {
                unlink($file);
            }
            $em->remove($archivo);
            $em->flush();
        }

        $dirname = $this->container->getParameter('folders')['dir_uploads'].'recursos/espacio/'.$session->get('empresa')['id'].'/'.$foro_id.'/';
        $f->delete_folder($dirname);

        // Finalmente se elimina el foro padre
        $foro = $em->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        $em->remove($foro);
        $em->flush();

        $return = array('ok' => 1);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

    public function ajaxSaveForoResponseAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();
        $html = '';

        // Recepción de parámetros del request
        $foro_id = $request->request->get('foro_id');
        $mensaje = $request->request->get('mensaje');
        $foro_main_id = $request->request->get('foro_main_id');

        // Preparando entidades de almacenamiento
        $foro_padre = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $foro_main = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_main_id);

        $foro = new CertiForo();
        $foro->setTema($foro_padre->getTema());
        $foro->setMensaje($mensaje);
        $foro->setPagina($foro_padre->getPagina());
        $foro->setEmpresa($foro_padre->getEmpresa());
        $foro->setForo($foro_padre);
        $foro->setUsuario($usuario);
        $foro->setFechaRegistro(new \DateTime('now'));
        $foro->setFechaPublicacion(new \DateTime('now'));
        $foro->setFechaVencimiento($foro_padre->getFechaVencimiento());
        $em->persist($foro);
        $em->flush();

        // Generación de alarmas
        $background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
        $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion_smart.png';
        $footer = $this->container->getParameter('folders')['uploads'].'recursos/footer.bg.form.png';
        $link_plataforma = $this->container->getParameter('link_plataforma').$foro_main->getUsuario()->getEmpresa()->getId();
        if ($foro_main->getUsuario()->getId() != $usuario->getId() && $foro_main->getId() == $foro->getForo()->getId())
        {

            $descripcion = $usuario->getNombre().' '.$usuario->getApellido().' '.$this->get('translator')->trans('ha comentado en el espacio colaborativo de').' '.$foro_main->getPagina()->getCategoria()->getNombre().' '.$foro_main->getPagina()->getNombre().'.';
            $f->newAlarm($yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo'], $descripcion, $foro_main->getUsuario(), $foro_main->getId());

            $correo_tutor = (!$foro_main->getUsuario()->getCorreoPersonal() || $foro_main->getUsuario()->getCorreoPersonal() == '') ? (!$foro_main->getUsuario()->getCorreoCorporativo() || $foro_main->getUsuario()->getCorreoCorporativo() == '') ? 0 : $foro_main->getUsuario()->getCorreoCorporativo() : $foro_main->getUsuario()->getCorreoPersonal();
            if ($correo_tutor)
            {
                $parametros_correo = array('twig' => 'LinkFrontendBundle:Colaborativo:emailColaborativo.html.twig',
                                           'datos' => array('mensaje' => $mensaje,
                                                            'background' => $background,
                                                            'logo' => $logo,
                                                            'footer' => $footer,
                                                            'link_plataforma' => $link_plataforma),
                                           'asunto' => 'Formación Smart: '.$descripcion,
                                           'remitente' => $this->container->getParameter('mailer_user'),
                                           'remitente_name' => $this->container->getParameter('mailer_user_name'),
                                           'mailer' => 'soporte_mailer',
                                           'destinatario' => $correo_tutor);
                $correo = $f->sendEmail($parametros_correo);
            }

        }

        // Puntaje obtenido
        if ($session->get('usuario')['participante'])
        {
            $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $foro_padre->getPagina()->getid(),
                                                                                                'usuario' => $session->get('usuario')['id']));
            $puntos = $pagina_log->getPuntos() + $yml['parameters']['puntos']['espacio_colaborativo'];
            $pagina_log->setPuntos($puntos);
            $em->persist($pagina_log);
            $em->flush();
        }

        if ($foro_id == $foro_main_id)
        {
            // Respuesta
            $html .= '<li class="f-card-det" id="toDel-'.$foro->getId().'">
                        <div class="cont-det">';
        }
        else {
            // Re-Respuesta
            $html .= '<div class="row resp-rply justify-content-center" id="toDel-'.$foro->getId().'">
                        <div class="col-12 text-justify">';
        }

        $img_user = $usuario->getFoto() ? $this->container->getParameter('folders')['uploads'].$usuario->getFoto() : $f->getWebDirectory().'/front/assets/img/user-default.png';

        $html .= '<div class="row justify-content-between">
                    <div class="col-auto">
                        <img class="img-ec-det" src="'.$img_user.'" alt="">
                        <span class="name_ft">'.$this->get('translator')->trans('Yo').' <span class="coment_ft">'.$this->get('translator')->trans('Ahora').'</span></span>
                    </div>
                    <div class="col-auto">
                        <div class="text-right">
                            <a href="#"><span class="material-icons ic-del" data="'.$foro->getId().'">delete</span></a>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-12 text-justify">'.$foro->getMensaje().'</div>
                </div>
                <div class="row align-items-end foo-esp_col-det justify-content-between">
                    <div class="col-auto">
                        <span class="like_ft like" data="'.$foro->getId().'"><i id="like'.$foro->getId().'" class="material-icons ic-lke">thumb_up</i> <span id="cantidad_like-'.$foro->getId().'">0</span></span>
                    </div>';

        if ($foro_id == $foro_main_id)
        {
            // Botón de responder
            $html .= '<div class="col-auto">
                            <a href="#" data-toggle="modal" data-target="#modalresp" class="reResponse" data="'.$foro->getId().'">
                                <span class="resp_ft"><i class="material-icons ic-rpy">reply</i>'.$this->get('translator')->trans('Responder').'</span>
                            </a>
                        </div>';
        }

        $html .= '</div>';

        if ($foro_id == $foro_main_id)
        {
            $html .= '<div id="div_addReResponse'.$foro->getId().'">
                        </div>
                    </div>
                </li>';
        }
        else {
            $html .= '</div>
                    </div>';
        }

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxMasForoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $f = $this->get('funciones');
        
        $foro_id = $request->query->get('foro_id');
        $offset = $request->query->get('offset');
        $offset += 5;
        $next_offset = $offset+5;
        $more = 0;
        $html = '';

        $foros_hijos = $f->forosHijos($foro_id, $offset, 5, $session->get('usuario'), $yml['parameters']['social']['espacio_colaborativo']);

        foreach ($foros_hijos as $foro_hijo)
        {

            $img_user = $foro_hijo['foto'] ? $this->container->getParameter('folders')['uploads'].$foro_hijo['foto'] : $f->getWebDirectory().'/front/assets/img/user-default.png';
            $like_class = $foro_hijo['likes']['ilike'] ? 'ic-lke-act' : '';
            $html .= '<li class="f-card-det" id="toDel-'.$foro_hijo['id'].'">
                        <div class="cont-det">
                            <div class="row justify-content-between">
                                <div class="col-auto">
                                    <img class="img-ec-det" src="'.$img_user.'" alt="">
                                    <span class="name_ft">'.$foro_hijo['usuario'].' <span class="coment_ft">'.$foro_hijo['timeAgo'].'</span></span>
                                </div>';
            if ($foro_hijo['delete_link'] == 1)
            {
                $html .= '<div class="col-auto">
                                        <div class="text-right">
                                            <a href="#" data-toggle="modal" data-target="#modalDelete"><span class="material-icons ic-del" data="{{ foro_hijo.id }}">delete</span></a>
                                        </div>
                                    </div>';
            }
            $html .= '</div>
                            <div class="row justify-content-center">
                                <div class="col-12 text-justify">
                                    '.$foro_hijo['mensaje'].'
                                </div>
                            </div>
                            <div class="row align-items-end foo-esp_col-det justify-content-between">
                                <div class="col-auto">
                                    <span class="like_ft like" data="'.$foro_hijo['id'].'"><i id="like'.$foro_hijo['id'].'" class="material-icons ic-lke '.$like_class.'">thumb_up</i> <span id="cantidad_like-'.$foro_hijo['id'].'">'.$foro_hijo['likes']['cantidad'].'</span></span>
                                </div>
                                <div class="col-auto">
                                    <a href="#" data-toggle="modal" data-target="#modalresp" class="reResponse" data="'.$foro_hijo['id'].'">
                                        <span class="resp_ft"><i class="material-icons ic-rpy">reply</i>'.$this->get('translator')->trans('Responder').'</span>
                                    </a>
                                </div>
                            </div>';
            foreach ($foro_hijo['respuestas'] as $foro_nieto)
            {
                $img_user = $foro_nieto['foto'] ? $this->container->getParameter('folders')['uploads'].$foro_nieto['foto'] : $f->getWebDirectory().'/front/assets/img/user-default.png';
                $like_class = $foro_nieto['likes']['ilike'] ? 'ic-lke-act' : '';
                $html .= '<div class="row resp-rply justify-content-center" id="toDel-'.$foro_nieto['id'].'">
                                    <div class="col-12 text-justify">
                                        <div class="row justify-content-between">
                                            <div class="col-auto">
                                                <img class="img-ec-det" src="'.$img_user.'" alt="">
                                                <span class="name_ft">'.$foro_nieto['usuario'].' <span class="coment_ft">'.$foro_nieto['timeAgo'].'</span></span>
                                            </div>';
                if ($foro_nieto['delete_link'] == 1)
                {
                    $html .= '<div class="col-auto">
                                                    <div class="text-right">
                                                        <a href="#" data-toggle="modal" data-target="#modalDelete"><span class="material-icons ic-del" data="'.$foro_nieto['id'].'">delete</span></a>
                                                    </div>
                                                </div>';
                }
                $html .= '</div>
                                        <div class="row justify-content-center">
                                            <div class="col-12 text-justify">
                                                '.$foro_nieto['mensaje'].'
                                            </div>
                                        </div>
                                        <div class="row align-items-end foo-esp_col-det justify-content-between">
                                            <div class="col-auto">
                                                <span class="like_ft like" data="'.$foro_nieto['id'].'"><i id="like'.$foro_nieto['id'].'" class="material-icons ic-lke '.$like_class.'">thumb_up</i> <span id="cantidad_like-'.$foro_nieto['id'].'">'.$foro_nieto['likes']['cantidad'].'</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
            }
            $html .= '<div id="div_addReResponse'.$foro_hijo['id'].'">
                            </div>
                        </div>
                    </li>';

        }

        // Total aportes de este espacio
        $query = $em->createQuery('SELECT COUNT(f.id) FROM LinkComunBundle:CertiForo f 
                                    WHERE f.foro = :foro_id')
                    ->setParameter('foro_id', $foro_id);
        $total_aportes = $query->getSingleScalarResult();

        if ($total_aportes > $next_offset)
        {
            $more = 1;
        }

        $return = array('html' => $html,
                        'more' => $more,
                        'offset' => $offset);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function ajaxUploadForoArchivoAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        $foro_id = $request->request->get('upload_foro_id');
        $pagina_id = $request->request->get('upload_pagina_id');

        $dir_uploads = $this->container->getParameter('folders')['dir_uploads'];

        if (!$foro_id)
        {

            $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
            $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

            $foro = new CertiForo();
            $foro->setFechaRegistro(new \DateTime('now'));
            $foro->setPagina($pagina);
            $foro->setEmpresa($empresa);
            $foro->setUsuario($usuario);
            $em->persist($foro);
            $em->flush();

            $foro_id = $foro->getId();
            $session->set('upload_foro_id', $foro_id);

            // Se crea el subdirectorio para los archivos del espacio colaborativo
            $dir = $dir_uploads.'recursos/espacio/'.$empresa->getId().'/'.$foro->getId().'/';
            if (!file_exists($dir) && !is_dir($dir))
            {
                mkdir($dir, 750, true);
            }

        }

        $uploads = $this->container->getParameter('folders')['uploads'];
        $upload_dir = $dir_uploads.'recursos/espacio/'.$session->get('empresa')['id'].'/'.$foro_id.'/';
        $upload_url = $uploads.'recursos/espacio/'.$session->get('empresa')['id'].'/'.$foro_id.'/';
        $options = array('upload_dir' => $upload_dir,
                         'upload_url' => $upload_url);
        $upload_handler = new UploadHandler($options);

        $return = json_encode($upload_handler);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxSaveForoArchivoAction(Request $request)
    {
        
        $session = new Session();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $em = $this->getDoctrine()->getManager();
        $html = '';
        $generar_alarma = 1;

        // Recepción de parámetros del request
        $foro_id = $request->request->get('foro_id');
        $descripcion = $request->request->get('descripcion');
        $archivo = $request->request->get('archivo');
        $edit = $request->request->get('edit');

        
        if (!$foro_id)
        {
            $foro_id = $session->get('upload_foro_id');
            $archivo = 'recursos/espacio/'.$session->get('empresa')['id'].'/'.$foro_id.'/'.$archivo;
            $generar_alarma = 0;
        }

        // Preparando entidades de almacenamiento
        $foro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($foro_id);
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);

        $foro_archivo = new CertiForoArchivo();
        $foro_archivo->setDescripcion($descripcion);
        $foro_archivo->setForo($foro);
        $foro_archivo->setUsuario($usuario);
        $foro_archivo->setFechaRegistro(new \DateTime('now'));
        $foro_archivo->setArchivo($archivo);
        $em->persist($foro_archivo);
        $em->flush();

        $archivo_arr = $f->archivoForo($foro_archivo, $session->get('usuario')['id']);
        $href = $this->container->getParameter('folders')['uploads'].$archivo_arr['archivo'];
        $background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
        $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion_smart.png';
        $footer = $this->container->getParameter('folders')['uploads'].'recursos/footer.bg.form.png';
        $link_plataforma = $this->container->getParameter('link_plataforma').$foro->getUsuario()->getEmpresa()->getId();

        // Generación de alarmas
        if ($foro->getUsuario()->getId() != $usuario->getId() && $generar_alarma)
        {

            $descripcion_alarma = $usuario->getNombre().' '.$usuario->getApellido().' '.$this->get('translator')->trans('ha subido un archivo en el espacio colaborativo de').' '.$foro->getPagina()->getCategoria()->getNombre().' '.$foro->getPagina()->getNombre().'.';
            $f->newAlarm($yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo'], $descripcion_alarma, $foro->getUsuario(), $foro->getId());

            $correo_tutor = (!$foro->getUsuario()->getCorreoPersonal() || $foro->getUsuario()->getCorreoPersonal() == '') ? (!$foro->getUsuario()->getCorreoCorporativo() || $foro->getUsuario()->getCorreoCorporativo() == '') ? 0 : $foro->getUsuario()->getCorreoCorporativo() : $foro->getUsuario()->getCorreoPersonal();
            if ($correo_tutor)
            {
                $parametros_correo = array('twig' => 'LinkFrontendBundle:Colaborativo:emailArchivo.html.twig',
                                           'datos' => array('descripcion' => $descripcion,
                                                            'descarga' => $href,
                                                            'background' => $background,
                                                            'logo' => $logo,
                                                            'footer' => $footer,
                                                            'link_plataforma' => $link_plataforma),
                                           'asunto' => 'Formación Smart: '.$descripcion_alarma,
                                           'remitente' => $this->container->getParameter('mailer_user'),
                                           'remitente_name' => $this->container->getParameter('mailer_user_name'),
                                           'mailer' => 'soporte_mailer',
                                           'destinatario' => $correo_tutor);
                $correo = $f->sendEmail($parametros_correo);
            }

        }

        // Puntaje obtenido
        if ($session->get('usuario')['participante'])
        {
            $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $foro->getPagina()->getId(),
                                                                                                'usuario' => $session->get('usuario')['id']));
            $puntos = $pagina_log->getPuntos() + $yml['parameters']['puntos']['espacio_colaborativo'];
            $pagina_log->setPuntos($puntos);
            $em->persist($pagina_log);
            $em->flush();
        }

        if ($edit)
        {
            $html .= '<li class="element-downloads" id="archivo-'.$archivo_arr['id'].'">
                        <div class="row px-0 d-flex justify-content-between">
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0 d-flex">
                                <img src="'.$archivo_arr['img'].'" class="imgdl" alt="">
                                <p class="nameArch">'.$archivo_arr['descripcion'].'</p>
                            </div>
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0">
                                <div class="cont-opc">
                                    <a href="'.$href.'" target="_blank"><span class="material-icons icDl" data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Descargar archivo').'">file_download</span></a>
                                    <span class="color-light-grey barra">|</span>
                                    <a href="#attachments"><span class="material-icons color-light-grey icDl delete" data="'.$archivo_arr['id'].'" id="iconCloseDownloads" title="'.$this->get('translator')->trans('Eliminar').'" data-toggle="tooltip" data-placement="bottom">clear</span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row px-0 justify-content-start">
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0 d-flex">
                                <p class="nameUpload">'.$archivo_arr['usuario'].'</p>
                            </div>
                        </div>
                    </li>';
        }
        else {
            $html .= '<li class="element-downloads">
                        <div class="row px-0 d-flex justify-content-between">
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0 d-flex">
                                <img src="'.$archivo_arr['img'].'" class="imgdl" alt="">
                                <p class="nameArch">'.$archivo_arr['descripcion'].'</p>
                            </div>
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0">
                                <div class="cont-opc">
                                    <a href="'.$href.'" target="_blank"><span class="material-icons icDl" data-toggle="tooltip" data-placement="bottom" title="'.$this->get('translator')->trans('Descargar archivo').'">file_download</span></a>
                                </div>
                            </div>
                        </div>
                        <div class="row px-0 justify-content-start">
                            <div class="col-auto col-sm-auto col-md-auto col-lg-auto col-xl-auto px-0 d-flex">
                                <p class="nameUpload">'.$archivo_arr['usuario'].'</p>
                            </div>
                        </div>
                    </li>';
        }

        $return = array('html' => $html,
                        'foro_id' => $foro_id);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function ajaxDeleteFileAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $archivo_id = $request->request->get('archivo_id');

        $archivo = $em->getRepository('LinkComunBundle:CertiForoArchivo')->find($archivo_id);
        $foro_id = $archivo->getForo()->getId();
        $file = $this->container->getParameter('folders')['dir_uploads'].$archivo->getArchivo();
        if (file_exists($file))
        {
            unlink($file);
        }
        $em->remove($archivo);
        $em->flush();

        $query = $em->createQuery('SELECT COUNT(fa.id) FROM LinkComunBundle:CertiForoArchivo fa 
                                    WHERE fa.foro = :foro_id')
                    ->setParameter('foro_id', $foro_id);
        $archivos = $query->getSingleScalarResult();

        $return = array('archivos' => $archivos);

        $return = json_encode($return);
        return new Response($return,200,array('Content-Type' => 'application/json'));

    }

}
