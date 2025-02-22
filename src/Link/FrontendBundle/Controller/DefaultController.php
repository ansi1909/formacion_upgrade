<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\AdminLike;
use Link\ComunBundle\Entity\CertiMuro;
use Link\ComunBundle\Entity\AdminIntroduccion;
use Symfony\Component\HttpFoundation\Cookie;

class DefaultController extends Controller
{

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $session = new Session();
        $timeZone = 0;
        if (!$session->get('iniFront') || $f->sesionBloqueda($session->get('sesion_id')))
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'sesion'));
        }
        $f->setRequest($session->get('sesion_id'));

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parameters.yml'));

        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($session->get('empresa')['id']);

        /********************** LÓGICA PARA LA ESTRUCTURA DE ACTIVIDADES RECIENTES *******************/

        $actividades_recientes = $f->getActividadesRecientes($session->get('usuario')['id'], $session->get('paginas'), $session->get('empresa')['id'], $yml);
        $actividad_reciente = $actividades_recientes['actividad_reciente'];
        $reciente = $actividades_recientes['reciente'];

        //return new Response(var_dump($actividad_reciente));

        /***************** LÓGICA DE PREPARACIÓN DE TABS DE GRUPOS ***************************/

        // Convertimos los id de las paginas de la sesion en un nuevo array
        $paginas_ids = array();
        foreach($session->get('paginas') as $pg)
        {
            $paginas_ids[] = $pg['id'];
        }
        // // Buscamos los grupos disponibles para la empresa
         $query = $em->createQuery('SELECT g FROM LinkComunBundle:CertiGrupo g
                                     WHERE g.empresa = :empresa
                                     ORDER BY g.orden ASC')
                     ->setParameters(array('empresa' => $session->get('empresa')['id']));
         $gps = $query->getResult();

        $grupos_ids = array();
        $grupo_consulta = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->findByEmpresa($session->get('empresa')['id']);
        foreach ($gps as $grupo) {
            array_push($grupos_ids,$grupo->getId());
        }
        //REcorrmos los grupos disponobles para a empresa
        $grupos = array();
        foreach ($grupos_ids as $grupo_id) {
            $paginas = array();
            $agregar = false;
            foreach ($paginas_ids as $pagina_id) {
                $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupoPagina')->findOneBy(['grupo' => $grupo_id,'pagina' =>$pagina_id]);
                if($grupo){
                    $agregar = true;
                    $pagina_empresa = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                  'pagina' => $pagina_id));

                    $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
                    $fechaFin = $pagina_empresa->getFechaVencimiento();
                    $fechaInicio = $pagina_empresa->getFechaInicio();
                    if($usuario->getNivel()){
                        if ($usuario->getNivel()->getFechaInicio() && $usuario->getNivel()->getFechaFin()) {
                            $fechaInicio = $usuario->getNivel()->getFechaInicio();
                            $fechaFin = $usuario->getNivel()->getFechaFin();
                        }
                    }

                    if ($pagina_empresa->getEmpresa()->getZonaHoraria()){
                        $timeZone = 1;
                        $zonaHoraria = $pagina_empresa->getEmpresa()->getZonaHoraria()->getNombre();
                    }

                    if($timeZone){
                        date_default_timezone_set($zonaHoraria);
                    }

                    $fechaActual = date('d-m-Y H:i:s');
                    $fechaInicio = $fechaInicio->format('d-m-Y 00:00:00');
                    $fechaFin = new \DateTime($fechaFin->format('d-m-Y 23:59:00'));
                    $fechaFin = $fechaFin->format('d-m-Y H:i:s');
                    $link_enabled = (strtotime($fechaActual)<strtotime($fechaFin))? 1:0;
                    $pagina_sesion = $session->get('paginas')[$grupo->getPagina()->getId()];

                    if (count($pagina_sesion['subpaginas']) >= 1)
                    {
                        $tiene_subpaginas = 1;
                    }
                    else {
                        $tiene_subpaginas = 0;
                    }

                    $pagina_log = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $session->get('usuario')['id'],
                                                                                                                         'pagina' => $grupo->getPagina()->getId()));

                    $modulo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('pagina' => $grupo->getPagina()->getId()));
                    $prueba = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina' => $modulo->getId()));
                    $notas = $f->notasDisponibles($grupo->getPagina()->getId(),$session->get('usuario')['id'],$yml);

                    if ($pagina_log)
                    {
                        if ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['completada'])
                        {
                            if ($pagina_empresa->getAcceso() && $link_enabled)
                            {
                                // aprobado y con acceso de seguir viendo
                                $continuar = 2;
                            }
                            else {
                                // aprobado y sin poder ver solo descargar notas y certificados
                                $continuar = 3;
                            }
                        }
                        else {
                           // cursando actualmente el programa - 1 = continuar, 4 = vencido y sin haber finalizado
                           $continuar = $link_enabled ? 1 : 4;
                        }
                    }
                    else {
                        // sin registros - 0 = iniciar, 4 = vencido y sin haber iniciado
                        $continuar = $link_enabled ? 0 : 4;
                    }
                    $dias = $f->timeAgo($fechaFin);
                    $porcentaje = $f->porcentaje_finalizacion($fechaInicio,$fechaFin,$dias);
                    $porcentaje_finalizacion = $dias;
                    $class_finaliza = $f->classFinaliza($porcentaje);

                    if ($link_enabled)
                    {
                      $nivel_vigente = true;
                      if($dias == 0){
                         $dias_vencimiento = $this->get('translator')->trans('Vence hoy');
                      }else{
                         $dias_vencimiento = $this->get('translator')->trans('Finaliza en').' '.$dias.' '.$this->get('translator')->trans('días');
                      }
                    }
                    else {
                        $nivel_vigente = false;
                        $dias_vencimiento = $this->get('translator')->trans('Programa Vencido');
                    }
                    //agregar pagina al array de paginas para el grupo
                    $paginas[] = array('id' => $grupo->getPagina()->getId(),
                                       'nombre' => $grupo->getPagina()->getNombre(),
                                       'imagen' => $grupo->getPagina()->getFoto(),
                                       'descripcion' => $grupo->getPagina()->getDescripcion(),
                                       'pdf' => ($grupo->getPagina()->getPdf())?$yml2['parameters']['folders']['uploads'].$grupo->getPagina()->getPdf():null,
                                       'dias_vencimiento' => $dias_vencimiento,
                                       'class_finaliza' => $class_finaliza,
                                       'tiene_subpaginas' => $tiene_subpaginas,
                                       'continuar' => $continuar,
                                       'link_enabled' => $link_enabled,
                                       'nivel_vigente' => $nivel_vigente,
                                       'prueba' => $prueba,
                                       'notas' => $notas);

                }/*if de si existe un certigrupo*/
            } /*foreach de paginas*/
            if($agregar){
                $grupo = $this->getDoctrine()->getRepository('LinkComunBundle:CertiGrupo')->find($grupo_id);
                $grupos[] = array('id' => $grupo_id,
                                 'nombre' => $grupo->getNombre(),
                                 'paginas' => $paginas);
            }


        }/* foreach de grupos */
        /*************************************************************************************/
         $user_id = $session->get('usuario')['id'];
         $introduccion = $em->getRepository('LinkComunBundle:AdminIntroduccion')->findByUsuario(
             array('id' => $user_id)
         );

         if (count($introduccion) == 0) {
            $usuario_a_guardar = $em ->getRepository('LinkComunBundle:AdminUsuario')->findOneById($user_id);
            $intro_nuevo =  new AdminIntroduccion();
            $intro_nuevo->setUsuario($usuario_a_guardar);
            $intro_nuevo->setPasoActual(1);
            $intro_nuevo->setCancelado(false);

            $em->persist($intro_nuevo);
            $em->flush();

            $introduccion = $em->getRepository('LinkComunBundle:AdminIntroduccion')->findByUsuario(
             array('id' => $user_id)
            );
         }

         $paso_actual_intro = $introduccion[0]->getPasoActual();
         $cancelar_intro = $introduccion[0]->getCancelado();
         return $this->render('LinkFrontendBundle:Default:index.html.twig', array('bienvenida' => $empresa->getBienvenida(),
                                                                                 'reciente' => $reciente,
                                                                                 'grupos' => $grupos,
                                                                                 'actividad_reciente' => $actividad_reciente,
                                                                                 'paso_actual_intro' => $paso_actual_intro,
                                                                                 'cancelar_intro' => $cancelar_intro));

    }

    public function ajaxDescripcionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');

        $pagina_id = $request->request->get('pagina_id');
        $pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

        $return = array('nombre'=>$pagina->getNombre(),'descripcion'=>$pagina->getDescripcion());
        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }


    public function authExceptionEmpresaAction($tipo)
    {

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

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
                $imagen = 'front/assets/img/lock.svg';
                $texto = $this->get('translator')->trans('Sesión expirada');
                break;

            case 'certificado':
                $mensaje = array('principal' => $this->get('translator')->trans('Certificado inexistente para este contenido'),
                                 'indicaciones' => array($this->get('translator')->trans('La empresa debe cargar el modelo de certificado y asociarlo a esta página'),
                                                         $this->get('translator')->trans('En el módulo administrativo de Certificados y Constancias se puede agregar certificados'),
                                                         $this->get('translator')->trans('También puede solicitar la carga del certificado para esta página a través del Administrador de Contenido del equipo de Formación Smart')));
                $continuar = '<a href="'.$this->generateUrl('_inicio').'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                $imagen = 'front/assets/img/warning (1).svg';
                $texto = $this->get('translator')->trans('Certificado no encontrado');
                break;

            case 'empresa':
                $mensaje = array('principal' => $this->get('translator')->trans('La empresa está inactiva'),
                                 'indicaciones' => array($this->get('translator')->trans('Es probable que se haya vencido el acceso para ingresar al sistema'),
                                                         $this->get('translator')->trans('Contacte al Administrador del Sistema para mayor información')));
                $empresa_id = ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0;
                $continuar = '<a href="'.$this->generateUrl('_login', array('empresa_id' => $empresa_id)).'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                $imagen = 'front/assets/img/lock.svg';
                $texto = $this->get('translator')->trans('Empresa inactiva');
                break;

            case 'url':
                $mensaje = array('principal' => $this->get('translator')->trans('Url de la empresa no existe'),
                                 'indicaciones' => array($this->get('translator')->trans('El Url proporcionado no es correcto'),
                                                         $this->get('translator')->trans('Ingrese el Url de acceso al sistema recibido por el usuario autorizado de su empresa'),
                                                         $this->get('translator')->trans('Contacte al Administrador del Sistema para mayor información')));
                $empresa_id = ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0;
                $continuar = '<a href="'.$this->generateUrl('_login', array('empresa_id' => $empresa_id)).'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                $imagen = 'front/assets/img/warning (1).svg';
                $texto = $this->get('translator')->trans('Url no encontrada');
                break;

            case 'prueba':
                $mensaje = array('principal' => $this->get('translator')->trans('No existe evaluación para esta página'),
                                 'indicaciones' => array($this->get('translator')->trans('Puede solicitar crear una evaluación para esta página a través del Administrador de Contenido del equipo de Formación Smart')));
                $continuar = '<a href="'.$this->generateUrl('_inicio').'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                $imagen = 'front/assets/img/warning (1).svg';
                $texto = $this->get('translator')->trans('Evaluación no encontrada');
                break;

            case 'pregunta':
                $mensaje = array('principal' => $this->get('translator')->trans('Esta evaluación no tiene preguntas configuradas'),
                                 'indicaciones' => array($this->get('translator')->trans('Contacte al Administrador del Sistema para mayor información')));
                $continuar = '<a href="'.$this->generateUrl('_inicio').'"><button class="btn btn-warning btn-continuar continuar">'.$this->get('translator')->trans('Continuar').'</button></a>';
                $imagen = 'front/assets/img/warning (1).svg';
                $texto = $this->get('translator')->trans('Preguntas no encontradas');
                break;

            case 'mantenimiento':
                $mensaje = array('principal' => $this->get('translator')->trans('Página en mantenimiento'),
                                 'indicaciones' => array($this->get('translator')->trans('En estos momentos se están realizando optimizaciones en nuestros servidores'),
                                                         $this->get('translator')->trans('Ofrecemos disculpas por las molestias ocasionadas')));
                $empresa_id = ($_COOKIE && isset($_COOKIE["empresa_id"])) ? $_COOKIE["empresa_id"] : 0;
                $continuar = '';
                $imagen = 'front/assets/img/browser (1).svg';
                $texto = $this->get('translator')->trans('Página en mantenimiento');
                break;

        }

        return $this->render('LinkFrontendBundle:Default:authException.html.twig', array('mensaje' => $mensaje,
                                                                                         'preferencia' => $preferencia,
                                                                                         'imagen' => $imagen,
                                                                                         'texto' => $texto,
                                                                                         'continuar' => $continuar,
                                                                                         'servidor_mantenimiento' => $yml['parameters']['servidor_mantenimiento']));

    }

    public function ajaxCorreoAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $session = new Session();

        $correo = trim($request->request->get('correo'));
        $empresa_id = $request->request->get('empresa_id');
        //new response(var_dump(['empresa'=>$empresa_id]));
        $ok = 1;
        $error = '';



        if($correo!="")
        {
            $usuario = $em ->getRepository('LinkComunBundle:AdminUsuario')->findOneByCorreoCorporativo($correo);

            if(!$usuario)
            {
               // new response(var_dump(['correo'=>$correo,'entro'=>'Busca el correo personal']));
                $usuario = $em ->getRepository('LinkComunBundle:AdminUsuario')->findOneByCorreoPersonal($correo);
            }

            if (!$usuario)//validamos que el correo exista
            {
                $error = $this->get('translator')->trans('El correo no existe en la base de datos, por favor inserte uno diferente o comuníquese a soporte@formacionsmart.com');
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
                    }
                    else {

                        $empresa = $this->getDoctrine()->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

                        if ($empresa && $usuario->getEmpresa()->getId() != $empresa->getId()) //validamos que el usuario pertenezca a la empresa
                        {
                            $error = $this->get('translator')->trans('El correo no pertenece a un Usuario de la empresa. Contacte al administrador del sistema.');
                        }
                        else{
                            $ok = 0;
                            $error = $this->get('translator')->trans('La recuperación de tus credenciales se realizó correctamente, revisa tu correo electrónico');

                            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                            $f = $this->get('funciones');
                            $background = $this->container->getParameter('folders')['uploads'].'recursos/decorate_certificado.png';
                            $logo = $this->container->getParameter('folders')['uploads'].'recursos/logo_formacion_smart.png';
                            $footer = $this->container->getParameter('folders')['uploads'].'recursos/footer.bg.form.png';
                            $link_plataforma = $this->container->getParameter('link_plataforma').$empresa->getId();
                            // Envío de correo con los datos de acceso, usuario y clave
                            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                                                'remitente' => $this->container->getParameter('mailer_user'),
                                                'remitente_name' => $this->container->getParameter('mailer_user_name'),
                                                'destinatario' => $correo,
                                                'mailer' => 'soporte_mailer',
                                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
                                                'datos' => array('usuario' => $usuario->getLogin(),
                                                                 'clave' => $usuario->getClave(),
                                                                 'nombre' => $usuario->getNombre().' '.$usuario->getApellido(),
                                                                 'correo_soporte' => $yml['parameters']['correo_soporte']['remitente'],
                                                                 'background' => $background,
                                                                 'logo' => $logo,
                                                                 'footer' => $footer,
                                                                 'link_plataforma' => $link_plataforma));
                            $correoRecuperacion = $f->sendEmail($parametros);
                            //return $this->redirectToRoute('_login', array('empresa_id'=> $empresa_id));
                        }
                    }
                }
            }
        }else
        {

            $error = $this->get('translator')->trans('Debe ingresar el correo electrónico.');
        }

        $return = array('ok' => $ok, 'error' => $error);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));

    }

    public function loginAction($empresa_id, Request $request)
    {

        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        if ($yml['parameters']['servidor_mantenimiento'])
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('tipo' => 'mantenimiento'));
        }

        $f = $this->get('funciones');
        $error = '';
        $verificacion = '';

        $em = $this->getDoctrine()->getManager();

        $session = new Session();
        //return new response(var_dump($_COOKIE));
        $empresa_bd = $em->getRepository('LinkComunBundle:AdminEmpresa')->find($empresa_id);

        if ($empresa_bd)
        {

            //se consulta la preferencia de la empresa
            $preferencia = $em->getRepository('LinkComunBundle:AdminPreferencia')->findOneByEmpresa($empresa_id);

            if ($preferencia)
            {
                $logo = $preferencia->getLogo() ? $preferencia->getLogo() : '';
                $tipo_logo = $preferencia->getTipoLogo() ? $preferencia->getTipoLogo()->getCss() : 'imgLogoHor';
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
                $tipo_logo = 'imgLogoHor';
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
                             'tipo_logo' => $tipo_logo,
                             'favicon' => $favicon,
                             'titulo' => $title,
                             'css' => $css);

            //validamos que exista una cookie
            if ($_COOKIE && isset($_COOKIE["id_usuario"]))
            {
                $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario"],
                                                                                               'empresa' => $empresa_bd->getId(),
                                                                                               'cookies' => $_COOKIE["marca_aleatoria_usuario"] ) );

                if ($usuario)
                {

                    // Si tiene una sesión abierta se cierra, ya que lo respalda la Cookie
                    if ($session->get('iniFront'))
                    {
                        if (!$f->sesionBloqueda($session->get('sesion_id')))
                        {
                            $sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($session->get('sesion_id'));
                            if ($sesion)
                            {
                                $sesion->setDisponible(false);
                                $em->persist($sesion);
                                $em->flush();
                            }
                            $session->invalidate();
                            $session->clear();
                        }
                    }

                    $recordar_datos = 1;
                    $login = $usuario->getLogin();
                    $clave = $usuario->getClave();
                    $verificacion = 1;

                }
                else {
                    // Eliminamos las cookies almacenada
                    setcookie('id_usuario', '', time() - 42000, '/');
                    setcookie('marca_aleatoria_usuario', '', time() - 42000, '/');
                    //$error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
                }
            }
            else {
                if ($request->getMethod() == 'POST')
                {
                    $recordar_datos = $request->request->get('recordar_datos');
                    $login = $request->request->get('usuario');
                    $clave = $request->request->get('password');
                    $dispositivo = $request->request->get('dispositivo');
                    $verificacion = 1;
                }
                else {
                    if ($session->get('iniFront'))
                    {
                        if ($empresa_bd->getActivo() && !$f->sesionBloqueda($session->get('sesion_id')))
                        {
                            return $this->redirectToRoute('_inicio');
                        }
                    }
                }
            }

            if ($verificacion)
            {
                //$ip = $f->obtenerIp();
                //$ip = '190.77.147.121';
                //$geoPlugin_array  =  unserialize (  file_get_contents ( 'http://www.geoplugin.net/php.gp?ip='.$ip));
                //$ubicacion = $geoPlugin_array['geoplugin_city'].' , '.$geoPlugin_array['geoplugin_countryName'];
                $ubicacion =''.','.'';
                $iniciarSesion = $f->iniciarSesion(array('recordar_datos' => $recordar_datos,
                                                         'login' => $login,
                                                         'clave' => $clave,
                                                         'dispositivo' => $dispositivo,
                                                         'ubicacion' => $ubicacion,
                                                         'empresa' => $empresa,
                                                         'yml' => $yml['parameters']));

                if ($iniciarSesion['exito'])
                {
                    return $this->redirectToRoute('_inicio');
                }
                else {
                    if ($iniciarSesion['error'] != '')
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
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $query = $em->createQuery('SELECT a FROM LinkComunBundle:AdminAlarma a
                                   WHERE a.usuario = :usuario_id
                                    AND a.fechaCreacion <= :hoy
                                   ORDER BY a.id DESC')
                    ->setMaxResults(10)
                    ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                          'hoy' => date('Y-m-d H:i:s')));
        $notificaciones = $query->getResult();

        $sonar = 0;
        $html = '';

        foreach ($notificaciones as $notificacion)
        {

            if ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['respuesta_muro'] || $notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_muro'] ) {
                // $html .= '<a href="#" data-toggle="modal" data-target="#modalMn" class="click" data='. $notificacion->getId().'>
                //             <input type="hidden" id="muro_id'.$notificacion->getId().'" value="'. $notificacion->getEntidadId().'">';

            }
            elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['espacio_colaborativo'])
            {
                $html .= '<a href="'.$this->generateUrl('_detalleColaborativo', array('foro_id' => $notificacion->getEntidadId())).'">';
            }
            elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['evento'])
            {
                $html .= '<a href="'.$this->generateUrl('_calendarioDeEventos', array('view' => 'basicDay', 'date' => $notificacion->getFechaCreacion()->format('Y-m-d'))).'">';
            }
            elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['aporte_espacio_colaborativo'])
            {
                $html .= '<a href="'.$this->generateUrl('_detalleColaborativo', array('foro_id' => $notificacion->getEntidadId())).'">';
            }
            elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['noticia'] || $notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['novedad'])
            {
                $html .= '<a href="'.$this->generateUrl('_noticiaDetalle', array('noticia_id' => $notificacion->getEntidadId())).'">';
            }
            elseif ($notificacion->getTipoAlarma()->getId() == $yml['parameters']['tipo_alarma']['biblioteca'])
            {
                $html .= '<a href="'.$this->generateUrl('_bibliotecaDetalle', array('biblioteca_id' => $notificacion->getEntidadId())).'">';
            }

            if ($notificacion->getLeido())
            {
                    $html .= '<li class="AnunListNotify " data="'.$notificacion->getId().'">
                              <input type="hidden" id="tipo_noti'.$notificacion->getId().'" value="'.$notificacion->getTipoAlarma()->getId() .'">';
            }
            else {
                $sonar = 1;
                $html .= '<li class="AnunListNotify notiSinLeer leido " data="'.$notificacion->getId().'">
                          <input type="hidden" id="tipo_noti'.$notificacion->getId().'" value="'.$notificacion->getTipoAlarma()->getId().'">';
            }

            $html .= '<div class="anunNotify">
                        <span class="stickerNotify '. $notificacion->getTipoAlarma()->getCss().'"><i class="material-icons icNotify">'.$notificacion->getTipoAlarma()->getIcono().'</i></span>
                            <p class="textNotify text-justify">'. $notificacion->getDescripcion().'</p>
                    </div>
                </li>
            </a>';

        }

        $html .= '<li class="listMoreNotify text-center">
                    <a href="'.$this->generateUrl('_notificaciones').'"><span class="moreNotify"><i class="material-icons icMore">add</i>'.$this->get('translator')->trans('Ver más').'</span></a>
                  </li>';

        $return = json_encode(array('html' => $html,
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

                if ($alarma->getLeido() == TRUE)
                {
                    $leidas[] = array('id'=>$alarma->getId(),
                                      'descripcion'=>$alarma->getDescripcion(),
                                      'css'=>$alarma->getTipoAlarma()->getCss(),
                                      'icono'=>$alarma->getTipoAlarma()->getIcono(),
                                      'tipo'=>$alarma->getTipoAlarma()->getid(),
                                      'entidad'=>$alarma->getEntidadId());

                }
                elseif ($alarma->getLeido() == FALSE)
                {
                    $no_leidas[] = array('id'=>$alarma->getId(),
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
        $html = "";
        $upload = $this->container->getParameter('folders')['uploads'];
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

        $padre = $em->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

        $query = $em->createQuery('SELECT m FROM LinkComunBundle:CertiMuro m
                                   WHERE m.muro = :muro_id
                                   ORDER BY m.id ASC')
                    ->setParameter('muro_id', $padre->getId());
        $hijos = $query->getResult();

        $img = $padre->getUsuario()->getFoto() ? $upload.$padre->getUsuario()->getFoto() : $f->getWebDirectory().'/front/assets/img/user-default.png';
        $autor = $padre->getUsuario()->getId() == $session->get('usuario')['id'] ? $this->get('translator')->trans('Yo') : $padre->getUsuario()->getNombre().' '.$padre->getUsuario()->getApellido();

        $likes = $f->likes($yml['parameters']['social']['muro'], $padre->getId(), $session->get('usuario')['id']);
        $like = $likes["ilike"] ? "ic-lke-act" : "";

        $fechap = $f->sinceTime($padre->getFechaRegistro()->format('Y-m-d H:i:s'));

        $html .= '<div class="msjMuro" >
                    <div class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex">
                                <img class="avatar-img" src="'.$img.'" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">'.$autor.'</div>
                                    <div class="date text-xs color-grey">'.$fechap.'</div>
                                </div>
                            </div>
                            <a href="#" class="mr-0 text-sm color-light-grey">
                                <span class="like_ft like" data="'.$padre->getId().'"><i id="like'.$padre->getId().'" class="material-icons ic-lke '.$like .'">thumb_up</i> <span id="cantidad_like-'.$padre->getId().'">'. $likes['cantidad'] .'</span></span>
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
            $likes = $f->likes($yml['parameters']['social']['muro'], $hijo->getId(), $session->get('usuario')['id']);
            $like = $likes["ilike"] ? "ic-lke-act" : "";
            $cantidad = $likes['cantidad'];

            $img = $hijo->getUsuario()->getFoto() ? $upload.$hijo->getUsuario()->getFoto() : $f->getWebDirectory().'/front/assets/img/user-default.png';
            $autor = $hijo->getUsuario()->getId() == $session->get('usuario')['id'] ? $this->get('translator')->trans('Yo') : $hijo->getUsuario()->getNombre().' '.$hijo->getUsuario()->getApellido();

            $query = $em->createQuery('SELECT COUNT(l.id) FROM LinkComunBundle:AdminLike l
                                       WHERE l.entidadId = :muro_id')
                        ->setParameter('muro_id', $hijo->getId());
            $likes = $query->getSingleScalarResult();

            $fechah = $f->sinceTime($hijo->getFechaRegistro()->format('Y-m-d H:i:s'));

            $html .='<li class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex text-left">
                                <img class="avatar-img" src="'.$img.'" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">'.$autor.'</div>
                                    <div class="date text-xs color-grey">'.$fechah.'</div>
                                </div>
                            </div>
                            <a href="#" class="mr-0 text-sm color-light-grey">
                                <span class="like_ft like" data="'.$hijo->getId().'"><i id="like'.$hijo->getId().'" class="material-icons ic-lke '.$like .'">thumb_up</i> <span id="cantidad_like-'.$hijo->getId().'">'. $cantidad.'</span></span>
                            </a>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">'. $hijo->getMensaje() .'</p>
                        </div>
                    </li>';
        }

        $html .='</ul>';


        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    public function ajaxRespuestaComentarioAction(Request $request)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $f = $this->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
        $upload = $this->container->getParameter('folders')['uploads'];

        $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
        $mensaje = $request->request->get('mensaje');
        $muro_id = $request->request->get('muro_id');

        $muro = $this->getDoctrine()->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

        $comentario = new CertiMuro();
        $comentario->setMensaje($mensaje);
        $comentario->setPagina($muro->getPagina());
        $comentario->setUsuario($usuario);
        $comentario->setMuro($muro);
        $comentario->setEmpresa($muro->getEmpresa());
        $comentario->setFechaRegistro(new \DateTime('now'));

        $em->persist($comentario);
        $em->flush();

        $img = $usuario->getFoto() ? $upload.$usuario->getFoto() : $f->getWebDirectory().'/front/assets/img/user-default.png';
        $autor = $this->get('translator')->trans('Yo');
        $fechah = $this->get('translator')->trans('Ahora');
        $likes = 0;

        $html ='<li class="comment">
                        <div class="comm-header d-flex justify-content-between align-items-center mb-2">
                            <div class="profile d-flex text-left">
                                <img class="avatar-img" src="'.$img.'" alt="">
                                <div class="wrap-info-user flex-column ml-2">
                                    <div class="name text-xs color-dark-grey">'.$autor.'</div>
                                    <div class="date text-xs color-grey">'.$fechah.'</div>
                                </div>
                            </div>
                            <a href="" class="mr-0 text-sm color-light-grey">
                                <i class="material-icons mr-1 text-sm color-light-grey">thumb_up</i> '.$likes.'
                            </a>
                        </div>
                        <div class="comm-body text-justify">
                            <p class="textMuroNoti">'. $mensaje .'Este es el controlador</p>
                        </div>
                    </li>';

        $return = array('html' => $html);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }


}