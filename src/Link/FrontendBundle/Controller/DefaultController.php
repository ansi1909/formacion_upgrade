<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\AdminLike;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{

    public function indexAction()
    {

        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();

        if (!$session->get('iniFront'))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $datos = $session;
        $empresa_obj = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);
        $bienvenida = $empresa_obj->getBienvenida();

        // buscando las últimas 3 interacciones del usuario donde la página no este completada
        $query_actividad_padre = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar
                                                   JOIN LinkComunBundle:CertiPagina p 
                                                   WHERE ar.usuario = :usuario_id
                                                   AND ar.estatusPagina != :completada
                                                   AND p.id = ar.pagina
                                                   AND p.pagina IS NULL
                                                   ORDER BY ar.id DESC')
                                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                          'completada' => $yml['parameters']['estatus_pagina']['completada']))
                                    ->setMaxResults(3);
        $actividadreciente_padre = $query_actividad_padre->getResult();

        $actividad_reciente = array();
        // Si tiene actividades
        if(count($actividadreciente_padre) >=  1){
            $reciente = 1;
            foreach ($actividadreciente_padre as $arp) {
                $ar = array();
                $pagina_sesion = $session->get('paginas')[$arp->getPagina()->getId()];
                $subpaginas_ids = $f->hijas($pagina_sesion['subpaginas']);
                //return new Response(var_dump($subpaginas_ids));
                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $arp->getPagina()->getId()));

                if(count($subpaginas_ids)){

                    $query_actividad_hija = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar 
                                                              WHERE ar.usuario = :usuario_id
                                                              AND ar.estatusPagina != :completada
                                                              AND ar.pagina IN (:hijas)
                                                              ORDER BY ar.id DESC')
                                                ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                                      'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                                                      'hijas' => $subpaginas_ids))
                                        ->setMaxResults(1);
                    $ar = $query_actividad_hija->getResult();
                }
                if($ar){
                    if($ar[0]){

                        $id =  $ar[0]->getPagina()->getId();
                        $padre_id = $arp->getPagina()->getId();
                        $titulo_padre = $arp->getPagina()->getNombre();
                        $titulo_hijo = $ar[0]->getPagina()->getNombre();
                        $imagen = $arp->getPagina()->getFoto();
                        $categoria = $ar[0]->getPagina()->getCategoria()->getNombre();
                        $porcentaje = round($arp->getPorcentajeAvance());
                        $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                    }
                }else{

                    $id = 0;
                    $padre_id = $arp->getPagina()->getId();
                    $titulo_padre = $arp->getPagina()->getNombre();
                    $titulo_hijo = '';
                    $imagen = $arp->getPagina()->getFoto();
                    $categoria = $arp->getPagina()->getCategoria()->getNombre();
                    $porcentaje = round($arp->getPorcentajeAvance());
                    $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

                }

                $porcentaje_finalizacion = $f->timeAgoPorcentaje($datos_certi_pagina->getFechaInicio()->format("Y/m/d"), $datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
                if($porcentaje_finalizacion >= 70){
                   $class_finaliza = 'alertTimeGood';
                }elseif($porcentaje_finalizacion >= 31 and $porcentaje_finalizacion <= 69){
                    $class_finaliza = 'alertTimeWarning';
                }elseif ($porcentaje_finalizacion <= 30) {
                    $class_finaliza = 'alertTimeDanger';
                }

                $actividad_reciente[]= array('id'=>$id,
                                             'padre_id'=>$padre_id,
                                             'titulo_padre'=>$titulo_padre,
                                             'titulo_hijo'=>$titulo_hijo,
                                             'imagen'=>$imagen,
                                             'categoria'=>$categoria,
                                             'fecha_vencimiento'=>$fecha_vencimiento,
                                             'class_finaliza'=>$class_finaliza,
                                             'porcentaje'=>$porcentaje);
            }
        // No tiene actividades
        }else{
            $reciente = 0;
        }

        $programas_disponibles = array();
        
        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach ($session->get('paginas') as $pg) {
            $paginas_ids[] = $pg['id'];
        }

        // Buscamos los grupos disponibles para el usuario por los programas disponibles en la sesión
        $group_query = $em->createQuery('SELECT cg FROM LinkComunBundle:CertiGrupo cg 
                                        JOIN LinkComunBundle:CertiGrupoPagina cgp
                                        WHERE cg.empresa = :empresa
                                        AND  cgp.grupo = cg.id
                                        AND cgp.pagina IN (:pagina)
                                        ORDER BY cg.id ASC')
                        ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                              'pagina' => $paginas_ids));
        $grupos = $group_query->getResult();

        // Buscamos datos especificos de los programas de la sesion obteniendo el grupo al que pertenece cada programa
        $query_pages_by_group = $em->createQuery('SELECT cgp 
                                                  FROM LinkComunBundle:CertiGrupo cg,
                                                       LinkComunBundle:CertiGrupoPagina cgp,
                                                       LinkComunBundle:CertiPagina cp
                                                  WHERE cg.empresa = :empresa
                                                  AND  cgp.grupo = cg.id
                                                  AND cp.id IN (:pagina)
                                                  AND cgp.pagina = cp.id
                                                  ORDER BY cg.id ASC')
                                    ->setParameters(array('empresa' => $session->get('empresa')['id'],
                                                          'pagina' => $paginas_ids));
        $pages_by_group = $query_pages_by_group->getResult();
        
        foreach ($pages_by_group as $pg) {

            // contruimos un array con los datos necesarios para el template y el grupo de cada programa
            $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                             'pagina' => $pg->getPagina()->getId()));

            $pagina_sesion = $session->get('paginas')[$pg->getPagina()->getId()];

            if(count($pagina_sesion['subpaginas']) >= 1){
                $tiene_subpaginas = 1;
            }else{
                $tiene_subpaginas = 0;
            }

            $datos_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                'pagina' => $pg->getPagina()->getId()));
            if($datos_log){
                if($datos_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada']){
                    if($datos_certi_pagina->getAcceso()){
                        // aprobado y con acceso de seguir viendo
                        $continuar = 2;
                    }else{
                        // aprobado y sin poder ver solo descargar notas y certificados
                        $continuar = 3;
                    }
                }else{
                   // cursando actualemnete el progarama - continuar
                   $continuar = 1; 
                }
                
            }else{
                // si registros - iniciar
                $continuar = 0;
            }

            $porcentaje_finalizacion = $f->timeAgoPorcentaje($datos_certi_pagina->getFechaInicio()->format("Y/m/d"), $datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));
            if($porcentaje_finalizacion >= 70){
               $class_finaliza = 'alertTimeGood';
            }elseif($porcentaje_finalizacion >= 31 and $porcentaje_finalizacion <= 69){
                $class_finaliza = 'alertTimeWarning';
            }elseif ($porcentaje_finalizacion <= 30) {
                $class_finaliza = 'alertTimeDanger';
            }
           
            $programas_disponibles[]= array('id'=>$pg->getPagina()->getId(),
                                            'nombre'=>$pg->getPagina()->getNombre(),
                                            'nombregrupo'=>$pg->getGrupo()->getNombre(),
                                            'imagen'=>$pg->getPagina()->getFoto(),
                                            'descripcion'=>$pg->getPagina()->getDescripcion(),
                                            'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                            'class_finaliza'=>$class_finaliza,
                                            'tiene_subpaginas'=>$tiene_subpaginas,
                                            'continuar'=>$continuar);
            
        }
        return $this->render('LinkFrontendBundle:Default:index.html.twig', array('bienvenida' => $bienvenida,
                                                                                 'reciente' => $reciente,
                                                                                 'grupos' => $grupos,
                                                                                 'actividad_reciente' => $actividad_reciente,
                                                                                 'programas_disponibles' => $programas_disponibles));

        return $response;        
    }

    public function authExceptionEmpresaAction($tipo)
    {

        $preferencia = array('logo' => ($_COOKIE && isset($_COOKIE["logo"])) ? $_COOKIE["logo"] : '',
                             'favicon' => ($_COOKIE && isset($_COOKIE["favicon"])) ? $_COOKIE["favicon"] : '',
                             'plantilla' => ($_COOKIE && isset($_COOKIE["plantilla"])) ? $_COOKIE["plantilla"] : 'base.html.twig',
                             'css' => ($_COOKIE && isset($_COOKIE["css"])) ? $_COOKIE["css"] : '');
        
        switch ($tipo) {
            case 'sesion':
                $mensaje = array('principal' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.'),
                                 'indicaciones' => array($this->get('translator')->trans('El tiempo de inactividad dentro de la aplicación ha superado el límite máximo de conexión'),
                                                         $this->get('translator')->trans('Puede ingresar de nuevo desde la pantalla de login'),
                                                         $this->get('translator')->trans('Al loggearse se restablecerán los datos para una nueva sesión')));
                $empresa_id = ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0;
                $continuar = '<a href="'.$this->generateUrl('_login', array('empresa_id' => $empresa_id)).'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                break;
            
            case 'certificado':
                $mensaje = array('principal' => $this->get('translator')->trans('Certificado inexistente para este contenido'),
                                 'indicaciones' => array($this->get('translator')->trans('La empresa debe cargar el modelo de certificado y asociarlo a esta página'),
                                                         $this->get('translator')->trans('En el módulo administrativo de Certificados y Constancias se puede agregar certificados'),
                                                         $this->get('translator')->trans('También puede solicitar la carga del certificado para esta página a través del Administrador de Contenido del equipo de Formación 2.0')));
                $continuar = '<a href="'.$this->generateUrl('_inicio').'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                break;

            case 'empresa':
                $mensaje = array('principal' => $this->get('translator')->trans('La empresa está inactiva'),
                                 'indicaciones' => array($this->get('translator')->trans('Es probable que se haya vencido el acceso para ingresar al sistema'),
                                                         $this->get('translator')->trans('Contacte al Administrador del Sistema para mayor información')));
                $empresa_id = ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0;
                $continuar = '<a href="'.$this->generateUrl('_login', array('empresa_id' => $empresa_id)).'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                break;

            case 'url':
                $mensaje = array('principal' => $this->get('translator')->trans('Url de la empresa no existe'),
                                 'indicaciones' => array($this->get('translator')->trans('El Url proporcionado no es correcto'),
                                                         $this->get('translator')->trans('Ingrese el Url de acceso al sistema recibido por el usuario autorizado de su empresa'),
                                                         $this->get('translator')->trans('Contacte al Administrador del Sistema para mayor información')));
                $empresa_id = ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0;
                $continuar = '<a href="'.$this->generateUrl('_login', array('empresa_id' => $empresa_id)).'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                break;

            case 'prueba':
                $mensaje = array('principal' => $this->get('translator')->trans('No existe evaluación para esta página'),
                                 'indicaciones' => array($this->get('translator')->trans('Puede solicitar crear una evaluación para esta página a través del Administrador de Contenido del equipo de Formación 2.0')));
                $continuar = '<a href="'.$this->generateUrl('_inicio').'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                break;

            case 'pregunta':
                $mensaje = array('principal' => $this->get('translator')->trans('Esta evaluación no tiene preguntas configuradas'),
                                 'indicaciones' => array($this->get('translator')->trans('Contacte al Administrador del Sistema para mayor información')));
                $continuar = '<a href="'.$this->generateUrl('_inicio').'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                break;

        }

        return $this->render('LinkFrontendBundle:Default:authException.html.twig', array('mensaje' => $mensaje,
                                                                                         'preferencia' => $preferencia,
                                                                                         'continuar' => $continuar));

    }

    public function ajaxCorreoAction(Request $request)
    {
        
        $em = $this->getDoctrine()->getManager();
        
        $correo = trim($request->request->get('correo'));
        $empresa_id = $request->request->get('empresa_id');
        $ok = 1;
        $error = '';

        if($correo!="")
        {
            $usuario = $em ->getRepository('LinkComunBundle:AdminUsuario')->findOneByCorreoCorporativo($correo);

            if(!$usuario)
            {
                $usuario = $em ->getRepository('LinkComunBundle:AdminUsuario')->findOneByCorreoPersonal($correo);
            }

            if (!$usuario)//validamos que el correo exista
            {
                $error = $this->get('translator')->trans('El correo no existe en la base de datos.');
            }
            else{
                if (!$usuario->getActivo()) //validamos que el usuario este activo
                {
                    $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
                }
                else {
                    if(!$usuario->getEmpresa())
                    {
                        $error = $this->get('translator')->trans('El usuario no tiene empresa asignada. Contacte al administrador del sistema.');
                    }else
                    {
                        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

                        if ($empresa && $usuario->getEmpresa()->getId() != $empresa->getId()) //validamos que el usuario pertenezca a la empresa
                        {
                            $error = $this->get('translator')->trans('El correo no pertenece a un Usuario de la empresa. Contacte al administrador del sistema.');
                        }
                        else{
                            $ok = 0;
                            $error = $this->get('translator')->trans('El correo se esta enviando.');

                            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                            $f = $this->get('funciones');

                            // Envío de correo con los datos de acceso, usuario y clave
                            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                                                'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
                                                'destinatario' => $correo,
                                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
                                                'datos' => array('usuario' => $usuario->getLogin(),
                                                                 'clave' => $usuario->getClave()) );
                           // return new response(var_dump($parametros));
                            $correoRecuperacion = $f->sendEmail($parametros);
                            return $this->redirectToRoute('_login', array('empresa_id'=> $empresa_id));
                        }
                    }
                }
            }
        }else
        {
                       
            $error = $this->get('translator')->trans('Debe ingresar el correo electronico.');
        }

        $return = array('ok' => $ok, 'error' => $error);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function loginAction($empresa_id, Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $verificacion='';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $em = $this->getDoctrine()->getManager();

        $session = new Session();
        //return new response(var_dump($_COOKIE));
        $empresa_bd = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if ($empresa_bd)
        {
            if ($empresa_bd->getActivo())
            {
                //se consulta la preferencia de la empresa
                $preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa_id);

                if ($preferencia)
                {
                    $logo = $preferencia->getLogo() ? $preferencia->getLogo() : '';
                    $logo_login = $preferencia->getLogoLogin() ? $preferencia->getLogo() : '';
                    $favicon = $preferencia->getFavicon();
                    $layout = explode(".", $preferencia->getLayout()->getTwig());
                    $layout = $layout[0]."_";
                    $title = $preferencia->getTitle();
                    $css = $preferencia->getCss();
                    $webinar = $empresa_bd->getWebinar();
                    $chat = $empresa_bd->getChatActivo();
                    $plantilla = $preferencia->getLayout()->getTwig();
                }
                else {
                    $logo = '';
                    $logo_login = '';
                    $favicon = '';
                    $layout = 'base_';
                    $title = '';
                    $css = '';
                    $webinar = false;
                    $chat = false;
                    $plantilla = 'base.html.twig';
                }

                // Usar las cookies para las preferencias de la empresa y personalizar la pantalla de excepción
                setcookie("empresa_id", $empresa_id, time()+(60*60*24*365),'/');
                setcookie("logo", $logo, time()+(60*60*24*365),'/');
                setcookie("favicon", $favicon, time()+(60*60*24*365),'/');
                setcookie("plantilla", $plantilla, time()+(60*60*24*365),'/');
                setcookie("css", $css, time()+(60*60*24*365),'/');

                $empresa = array('id' => $empresa_id,
                                 'nombre' => $empresa_bd->getNombre(),
                                 'chat' => $chat,
                                 'webinar' => $webinar,
                                 'plantilla' => $plantilla,
                                 'logo' => $logo,
                                 'favicon' => $favicon,
                                 'titulo' => $title,
                                 'css' => $css);

                //validamos que exista una cookie
                if($_COOKIE && isset($_COOKIE["id_usuario"]))
                {
                    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario"],
                                                                                                   'empresa' => $empresa_bd->getId(),
                                                                                                   'cookies' => $_COOKIE["marca_aleatoria_usuario"] ) );
                    
                    if($usuario)
                    {
                        $recordar_datos=1;
                        $login = $usuario->getLogin();
                        $clave = $usuario->getClave(); 
                        $verificacion=1;
                    }
                    else {
                        $error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
                    }
                }
                else {
                    if ($request->getMethod() == 'POST')
                    {
                        $recordar_datos = $request->request->get('recordar_datos');
                        $login = $request->request->get('usuario');
                        $clave = $request->request->get('password');
                        $verificacion=1;
                    }
                }

                if($verificacion)
                {
                    $iniciarSesion = $f->iniciarSesion(array('recordar_datos' => $recordar_datos,'login' => $login,'clave' => $clave,'empresa' => $empresa,'yml' => $yml['parameters'] ));

                    if($iniciarSesion['exito']==true)
                    {
                        return $this->redirectToRoute('_inicio');
                    }
                    else {
                        if($iniciarSesion['error']==true)
                        {

                            $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa' => $empresa, 
                                                                                                                     'logo_login' => $logo_login,
                                                                                                                     'error' => $iniciarSesion['error']));
                            return $response;
                        }
                    }                    
                }
                else {
                    $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa' => $empresa, 
                                                                                                             'logo_login' => $logo_login,
                                                                                                             'error' => $error));
                    return $response;
                }

            }
            else {
                return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'empresa'));
            }
        }
        else {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'url'));
        }
    }

    public function ajaxLikeAction(Request $request)
    {
        
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $social_id = $request->request->get('social_id');
        $entidad_id = $request->request->get('entidad_id');
        $usuario_id = $request->request->get('usuario_id');

        if ($social_id == $yml['parameters']['social']['muro']) 
        {
            $entity = 'CertiMuro';
        }
        else {
            $entity = 'CertiForo';
        }

        $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:'.$entity)->find($entidad_id);
        
        $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $entidad->getPagina()->getid(),
                                                                                            'usuario' => $entidad->getUsuario()->getId()));

        $like = $em->getRepository('LinkComunBundle:AdminLike')->findOneBy(array('social' => $social_id,
                                                                                 'entidadId' => $entidad_id,
                                                                                 'usuario' => $usuario_id));

        if (!$like)
        {
            // Se agrega y se suman los puntos
            $social = $this->getDoctrine()->getRepository('LinkComunBundle:AdminSocial')->find($social_id);
            $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
            $like = new AdminLike();
            $like->setSocial($social);
            $like->setEntidadId($entidad_id);
            $like->setUsuario($usuario);
            $em->persist($like);
            $em->flush();
            $puntos_like = $yml['parameters']['puntos']['like_recibido'];
        }
        else {
            // Se elimina y se restan los puntos
            $em->remove($like);
            $em->flush();
            $puntos_like = -$yml['parameters']['puntos']['like_recibido'];
        }

        $puntos = $pagina_log->getPuntos() + $puntos_like;
        $pagina_log->setPuntos($puntos);
        $em->persist($pagina_log);
        $em->flush();

        // Cantidad de likes de la entidad
        $likes_arr = $f->likes($social_id, $entidad_id, $usuario_id);

        $return = array('ilike' => $likes_arr['ilike'],
                        'cantidad' => $likes_arr['cantidad'],
                        'puntos_like' => $puntos_like);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }
        
    public function ajaxNotiAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $query = $em->createQuery('SELECT a FROM LinkComunBundle:AdminAlarma a
                                   WHERE a.usuario = :usuario_id
                                   ORDER BY a.id DESC')
                    ->setMaxResults(10)
                    ->setParameter('usuario_id', $usuario->getId());
        $notificaciones = $query->getResult();

        $hoy = new \DateTime();
        $now = strtotime($hoy->format('d-m-Y'));
        $noti ='';
        $sonar=0;
        $noti = '';
        foreach ($notificaciones as $notificacion)
        {
            $fecha = strtotime($notificacion->getFechaCreacion()->format('d-m-Y'));
            if ($fecha <= $now) {

               if ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['respuesta_muro']) {

                    $noti.='<a href="#" data-toggle="modal" data-target="#modalMn" class="click" data='. $notificacion->getId() .'>
                            <input type="hidden" id="muro_id'.$notificacion->getId().'" value="'. $notificacion->getEntidadId() .'">';

                }elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['espacio_colaborativo']) {

                    $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($notificacion->getEntidadId());
                    $noti.='<a href="'.$this->generateUrl('_detalleColaborativo', array('foro_id' =>$entidad->getId())).'">';

                }elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento']) {

                    $noti.='<a href="'.$this->generateUrl('_calendarioDeEventos').'">';

                }elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo']) {

                    $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:CertiForo')->find($notificacion->getEntidadId());
                    $noti.='<a href="'.$this->generateUrl('_detalleColaborativo', array('foro_id' =>$entidad->getId())).'">';

                }elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['noticia']) {

                    $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($notificacion->getEntidadId());
                    $noti.='<a href="'.$this->generateUrl('_noticiaDetalle', array('noticia_id' =>$entidad->getId())).'">';

                }elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['novedad']) {

                    $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($notificacion->getEntidadId());
                    $noti.='<a href="'.$this->generateUrl('_noticiaDetalle', array('noticia_id' =>$entidad->getId())).'">';

                }elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['biblioteca']) {

                    $entidad = $this->getDoctrine()->getRepository('LinkComunBundle:AdminNoticia')->find($notificacion->getEntidadId());
                    $noti.='<a href="'.$this->generateUrl('_bibliotecaDetalle', array('biblioteca_id' =>$entidad->getId())).'">';

                }
                    if ($notificacion->getLeido() == true) {
                            $noti .= '<li class="AnunListNotify " data="'.$notificacion->getId() .'">
                                      <input type="hidden" id="tipo_noti'.$notificacion->getId().'" value= "'. $notificacion->getTipoAlarma()->getId() .'" >';
                        }
                        elseif ($notificacion->getLeido() == false) {
                            $sonar= 1;
                            $noti .= '<li class="AnunListNotify notiSinLeer leido " data="'.$notificacion->getId() .'">
                                      <input type="hidden" id="tipo_noti'.$notificacion->getId().'" value= "'. $notificacion->getTipoAlarma()->getId() .'" >';
                        }       
                               $noti .= '<div class="anunNotify">
                                   <span class="stickerNotify '. $notificacion->getTipoAlarma()->getCss() .'"><i class="material-icons icNotify">'. $notificacion->getTipoAlarma()->getIcono() .'</i></span>
                                   <p class="textNotify text-justify">'. $notificacion->getDescripcion() .'</p>
                               </div>
                            </li>
                        </a>';
            }
        }

        $noti .= '<li class="listMoreNotify text-center">
                    <a href="'.$this->generateUrl('_notificaciones').'"><span class="moreNotify"><i class="material-icons icMore">add</i>Ver más</span></a>
                  </li>';

        $return = json_encode(array('noti' => $noti,
                                    'sonar' => $sonar));
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxLeidoAction(Request $request)
    {

        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $noti_id = $request->request->get('noti_id');

        $notificacion = $em->getRepository('LinkComunBundle:AdminAlarma')->find($noti_id);

        $notificacion->setLeido(TRUE);
        
        $em->persist($notificacion);
        $em->flush();

        $return = 'ok';

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function notificacionesAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $todas=array();
        $no_leidas=array();
        $leidas=array();

        $query = $em->createQuery('SELECT a FROM LinkComunBundle:AdminAlarma a
                                   WHERE a.usuario = :usuario_id
                                   ORDER BY a.id DESC')
                    ->setParameter('usuario_id', $usuario->getId());
        $alarmas = $query->getResult();

            foreach($alarmas as $alarma)
            {
                $todas[] =array('id'=>$alarma->getId(),
                                   'descripcion'=>$alarma->getDescripcion(),
                                   'css'=>$alarma->getTipoAlarma()->getCss(),
                                   'icono'=>$alarma->getTipoAlarma()->getIcono(),
                                   'leido'=>$alarma->getLeido(),
                                   'tipo'=>$alarma->getTipoAlarma()->getid(),
                                   'entidad'=>$alarma->getEntidadId());

                if($alarma->getLeido() == TRUE)
                {
                    $leidas[] =array('id'=>$alarma->getId(),
                                   'descripcion'=>$alarma->getDescripcion(),
                                   'css'=>$alarma->getTipoAlarma()->getCss(),
                                   'icono'=>$alarma->getTipoAlarma()->getIcono(),
                                   'tipo'=>$alarma->getTipoAlarma()->getid(),
                                   'entidad'=>$alarma->getEntidadId());

                }elseif ($alarma->getLeido() == FALSE) 
                {
                    $no_leidas[] =array('id'=>$alarma->getId(),
                                   'descripcion'=>$alarma->getDescripcion(),
                                   'css'=>$alarma->getTipoAlarma()->getCss(),
                                   'icono'=>$alarma->getTipoAlarma()->getIcono(),
                                   'tipo'=>$alarma->getTipoAlarma()->getid(),
                                   'entidad'=>$alarma->getEntidadId());
                }
            }

            return $this->render('LinkFrontendBundle:Notificaciones:index.html.twig', array('todas' => $todas,
                                                                                            'leidas' => $leidas,
                                                                                            'no_leidas' => $no_leidas));
    }

    public function ajaxNotiMuroAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $muro_id = $request->query->get('muro_id');
        $muro ="";
        $upload= 'http://localhost/uploads/';

        $padre = $em->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

        $query = $em->createQuery('SELECT m FROM LinkComunBundle:CertiMuro m
                                   WHERE m.muro = :muro_id
                                   ORDER BY m.id ASC')
                    ->setParameter('muro_id', $padre->getId());
        $hijos = $query->getResult();

        $img = $upload.$padre->getUsuario()->getFoto();

        $query = $em->createQuery('SELECT COUNT(l.id) FROM LinkComunBundle:AdminLike l
                                   WHERE l.entidadId = :muro_id')
                    ->setParameter('muro_id', $padre->getId());
        $likes = $query->getSingleScalarResult();

        $muro = '<div class="msjMuro" >
                    <div class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex">
                                <img class="avatar-img" src="'.$img.'" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">'.$padre->getUsuario()->getLogin().'</div>
                                    <div class="date text-xs color-grey">hace 2 días</div>
                                </div>
                            </div>
                            <a href="" class="mr-0 text-sm color-light-grey">
                                <i class="material-icons mr-1 text-sm color-light-grey">thumb_up</i> '.$likes.'
                            </a>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">'. $padre->getMensaje() .'</p>
                        </div>
                    </div>
                </div>
                <ul class="msjMuroResp">';

        foreach( $hijos as $hijo )
        {
            $img = $upload.$hijo->getUsuario()->getFoto();

            $query = $em->createQuery('SELECT COUNT(l.id) FROM LinkComunBundle:AdminLike l
                                       WHERE l.entidadId = :muro_id')
                        ->setParameter('muro_id', $hijo->getId());
            $likes = $query->getSingleScalarResult();

            $muro .='<li class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex text-left">
                                <img class="avatar-img" src="'.$img.'" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">'.$hijo->getUsuario()->getLogin().'</div>
                                    <div class="date text-xs color-grey">hace 2 días</div>
                                </div>
                            </div>
                            <a href="" class="mr-0 text-sm color-light-grey">
                                <i class="material-icons mr-1 text-sm color-light-grey">thumb_up</i> '.$likes.'
                            </a>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">'. $hijo->getMensaje() .'</p>
                        </div>
                    </li>';
        }

        $muro .='</ul>';

        $input='<input type="hidden" id="id_muro" data= "'.$padre->getId().'" >';

        $return = array('html' => $muro,
                        'muro' => $input);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxRespuestaComentarioAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $mensaje = $request->request->get('mensaje');
        $muro_id = $request->request->get('muro_id');

        $return = array('mensaje' => $mensaje,
                        'muro' => $muro_id);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
}