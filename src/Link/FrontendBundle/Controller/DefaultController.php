<?php

namespace Link\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Link\ComunBundle\Entity\AdminSesion;
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
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Lo sentimos. Sesión expirada.')));
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
                
                $pagina_sesion = $session->get('paginas')[$arp->getPagina()->getId()];
                $subpaginas_ids = $f->hijas($pagina_sesion['subpaginas']);
                $datos_certi_pagina = $this->getDoctrine()->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $session->get('empresa')['id'],
                                                                                                                                 'pagina' => $arp->getPagina()->getId()));
                $query_actividad_hija = $em->createQuery('SELECT ar FROM LinkComunBundle:CertiPaginaLog ar
                                                          JOIN LinkComunBundle:CertiPagina p 
                                                          WHERE ar.usuario = :usuario_id
                                                          AND ar.estatusPagina != :completada
                                                          AND p.id = ar.pagina
                                                          AND p.id IN (:hijas)
                                                          ORDER BY ar.id DESC')
                                            ->setParameters(array('usuario_id' => $session->get('usuario')['id'],
                                                                  'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                                                  'hijas' => $subpaginas_ids))
                                    ->setMaxResults(1);
                $ar = $query_actividad_hija->getResult();

                if($ar[0]){

                    $id =  $ar[0]->getPagina()->getId();
                    $padre_id = $arp->getPagina()->getId();
                    $titulo_padre = $arp->getPagina()->getNombre();
                    $titulo_hijo = $ar[0]->getPagina()->getNombre();
                    $imagen = $arp->getPagina()->getFoto();
                    $categoria = $ar[0]->getPagina()->getCategoria()->getNombre();
                    $porcentaje = round($arp->getPorcentajeAvance());
                    $fecha_vencimiento = $f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d"));

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

                $actividad_reciente[]= array('id'=>$id,
                                             'padre_id'=>$padre_id,
                                             'titulo_padre'=>$titulo_padre,
                                             'titulo_hijo'=>$titulo_hijo,
                                             'imagen'=>$imagen,
                                             'categoria'=>$categoria,
                                             'fecha_vencimiento'=>$fecha_vencimiento,
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
           
            $programas_disponibles[]= array('id'=>$pg->getPagina()->getId(),
                                            'nombre'=>$pg->getPagina()->getNombre(),
                                            'nombregrupo'=>$pg->getGrupo()->getNombre(),
                                            'imagen'=>$pg->getPagina()->getFoto(),
                                            'descripcion'=>$pg->getPagina()->getDescripcion(),
                                            'fecha_vencimiento'=>$f->timeAgo($datos_certi_pagina->getFechaVencimiento()->format("Y/m/d")),
                                            'tiene_subpaginas'=>$tiene_subpaginas,
                                            'continuar'=>$continuar);
            
        }
        return $this->render('LinkFrontendBundle:Default:index.html.twig', array('bienvenida' => $bienvenida,
                                                                                 'reciente' => $reciente,
                                                                                 'grupos' => $grupos,
                                                                                 'actividad_reciente' => $actividad_reciente,
                                                                                 'programas_disponibles' => $programas_disponibles));

        $response->headers->setCookie(new Cookie('Peter', 'Griffina', time() + 36, '/'));

        return $response;        
    }

    
    public function ajaxBuscarClaveAction(Request $request)
    {
              
        $em = $this->getDoctrine()->getManager();              

        $usuario = 'danny';//trim($request->query->get('usuario'));
        //return new response(var_dump($_COOKIE));

        $ok = 1;
        $error = '';
        $clave = '';
        
        if($_COOKIE && $usuario!="" && $usuario == $_COOKIE["nombre_usuario"])
        {
            $ok = 0;   
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findBy(array('login' => $_COOKIE["nombre_usuario"],
                                                                                        'cookies' => $_COOKIE["marca_aleatoria_usuario"]));
            
            $clave = $usuario-getClave();
        }else
        {
            $error = $this->get('translator')->trans('Debe ingresar el usuario.');
        }
        
        $return = array('ok' => $ok, 'clave' => $clave, 'error' => $error);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }
    
    public function authExceptionEmpresaAction($mensaje)
    {
        return $this->render('LinkFrontendBundle:Default:authException.html.twig', array('mensaje' => $mensaje));
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

                            $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                            $f = $this->get('funciones');

                            // Envío de correo con los datos de acceso, usuario y clave
                            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                                                'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
                                                'destinatario' => $correo,
                                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
                                                'datos' => array('usuario' => $usuario->getLogin(),
                                                                 'clave' => $usuario->getClave()) );
                            $correoRecuperacion = $f->sendEmail($parametros, $this);
                            return $this->redirectToRoute('_login');
                        }
                    }
                }
            }
        }else
        {
            $error = $this->get('translator')->trans('Debe ingresar el correo electronico.');
        }

      /*  if ($ok == 1)
        {

            // Envío de correo con el usuario y clave provisional
            $f = $this->get('funciones');
            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                                'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
                                'destinatario' => $correo,
                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
                                'datos' => array('usuario' => $usuario->getLogin(),
                                                 'clave' => $usuario->getClave()) );
            $correoRecuperacion = $f->sendEmail($parametros, $this);
        }**/
        
        $return = array('ok' => $ok, 'error' => $error);

        $return = json_encode($return);
        return new Response($return, 200, array('Content-Type' => 'application/json'));
        
    }

    public function loginAction($empresa_id, Request $request)
    {

        $f = $this->get('funciones');
        $error = '';
        $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));

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
                $logo = $preferencia->getLogo();
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
                $favicon = '';
                $layout = 'base_';
                $title = '';
                $css = '';
                $webinar = false;
                $chat = false;
                $plantilla = 'base.html.twig';
            }

            $empresa = array('id' => $empresa_id,
                             'nombre' => $empresa_bd->getNombre(),
                             'chat' => $chat,
                             'webinar' => $webinar,
                             'plantilla' => $plantilla,
                             'logo' => $logo,
                             'favicon' => $favicon,
                             'titulo' => $title,
                             'css' => $css);

            if ($request->getMethod() == 'POST')
            {               
                $login = $request->request->get('usuario');
                $clave = $request->request->get('password');

                $usuario = $this->getDoctrine()->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $login,
                                                                                                                'clave' => $clave));
                if (!$usuario)//validamos que el usuario exista
                {
                    $error = $this->get('translator')->trans('Usuario o clave incorrecta.');
                }
                else {
                    
                    if (!$usuario->getActivo()) //validamos que el usuario este activo
                    {
                        $error = $this->get('translator')->trans('Usuario inactivo. Contacte al administrador del sistema.');
                    }
                    else {

                        if (!$empresa_bd->getActivo()) //validamos que la empresa este activa
                        {
                            $error = $this->get('translator')->trans('La empresa está inactiva. Contacte al administrador del sistema.');
                        }
                        else {
                            
                            if (!$usuario->getEmpresa())
                            {
                                $error = $this->get('translator')->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
                            }
                            else {
                                if ($usuario->getEmpresa()->getId() != $empresa_bd->getId()) //validamos que el usuario pertenezca a la empresa
                                {
                                    $error = $this->get('translator')->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
                                }                       
                                else {

                                    $roles_front = array();
                                    $roles_front[] = $yml['parameters']['rol']['participante'];
                                    $roles_front[] = $yml['parameters']['rol']['tutor'];
                                    $roles_ok = 0;
                                    $participante = false;
                                    $tutor = false;

                                    $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
                                                ->setParameter('usuario_id', $usuario->getId());
                                    $roles_usuario_db = $query->getResult();
                                    
                                    foreach ($roles_usuario_db as $rol_usuario)
                                    {
                                        // Verifico si el rol está dentro de los roles de backend
                                        if (in_array($rol_usuario->getRol()->getId(), $roles_front))
                                        {
                                            $roles_ok = 1;
                                        }
                                        if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['participante'])
                                        {
                                            $participante = true;
                                        }
                                        if ($rol_usuario->getRol()->getId() == $yml['parameters']['rol']['tutor'])
                                        {
                                            $tutor = true;
                                        }
                                    }

                                    if (!$roles_ok)
                                    {
                                        $error = $this->get('translator')->trans('Los roles que tiene el usuario no son permitidos para ingresar al sistema.');
                                    }
                                    else {

                                        // se consulta si la empresa tiene paginas activas
                                        $query = $em->createQuery('SELECT np FROM LinkComunBundle:CertiNivelPagina np
                                                                   JOIN np.paginaEmpresa pe
                                                                   JOIN pe.pagina p
                                                                   WHERE pe.empresa = :empresa 
                                                                    AND p.pagina IS NULL 
                                                                    AND np.nivel = :nivel_usuario 
                                                                    AND pe.activo = :activo 
                                                                    AND pe.fechaInicio <= :hoy 
                                                                    AND pe.fechaVencimiento >= :hoy
                                                                   ORDER BY p.orden')
                                                    ->setParameters(array('empresa' => $empresa_bd->getId(),
                                                                          'nivel_usuario' => $usuario->getNivel()->getId(),
                                                                          'activo' => true,
                                                                          'hoy' => date('Y-m-d')));
                                        $paginas_bd = $query->getResult();
                                        
                                        if (!$paginas_bd)  //validamos que la empresa tenga paginas activas
                                        {
                                            $error = $this->get('translator')->trans('No hay Programas disponibles para la empresa. Contacte al administrador del sistema.');
                                        }
                                        else {
                                        
                                            // Se setea los datos del usuario
                                            $datosUsuario = array('id' => $usuario->getId(),
                                                                  'nombre' => $usuario->getNombre(),
                                                                  'apellido' => $usuario->getApellido(),
                                                                  'correo' => $usuario->getCorreoPersonal(),
                                                                  'foto' => $usuario->getFoto(),
                                                                  'participante' => $participante,
                                                                  'tutor' => $tutor);
                                            
                                            // Estructura de páginas
                                            $paginas = array();
                                            foreach ($paginas_bd as $pagina)
                                            {
                                                $query = $em->createQuery('SELECT COUNT(cp.id) FROM LinkComunBundle:CertiPrueba cp
                                                                           WHERE cp.estatusContenido = :activo and cp.pagina = :pagina_id')
                                                            ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
                                                                                  'pagina_id' => $pagina->getPaginaEmpresa()->getPagina()->getId()));
                                                $tiene_evaluacion = $query->getSingleScalarResult();

                                                $subPaginas = $f->subPaginasNivel($pagina->getPaginaEmpresa()->getPagina()->getId(), $yml['parameters']['estatus_contenido']['activo'], $empresa_bd->getId());

                                                $paginas[$pagina->getPaginaEmpresa()->getPagina()->getId()] = array('id' => $pagina->getPaginaEmpresa()->getPagina()->getId(),
                                                                                                                    'nombre' => $pagina->getPaginaEmpresa()->getPagina()->getNombre(),
                                                                                                                    'categoria' => $pagina->getPaginaEmpresa()->getPagina()->getCategoria()->getNombre(),
                                                                                                                    'foto' => $pagina->getPaginaEmpresa()->getPagina()->getFoto(),
                                                                                                                    'tiene_evaluacion' => $tiene_evaluacion ? true : false,
                                                                                                                    'acceso' => $pagina->getPaginaEmpresa()->getAcceso(),
                                                                                                                    'muro_activo' => $pagina->getPaginaEmpresa()->getMuroActivo(),
                                                                                                                    'prelacion' => $pagina->getPaginaEmpresa()->getPrelacion() ? $pagina->getPaginaEmpresa()->getPrelacion()->getId() : 0,
                                                                                                                    'inicio' => $pagina->getPaginaEmpresa()->getFechaInicio()->format('d/m/Y'),
                                                                                                                    'vencimiento' => $pagina->getPaginaEmpresa()->getFechaVencimiento()->format('d/m/Y'),
                                                                                                                    'subpaginas' => $subPaginas);
                                            }

                                            // Cierre de sesiones activas
                                            $sesiones = $this->getDoctrine()->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                                                                         'disponible' => true));
                                            foreach ($sesiones as $s)
                                            {
                                                $s->setDisponible(false);
                                            }

                                            // Se crea la sesión en BD
                                            $admin_sesion = new AdminSesion();
                                            $admin_sesion->setFechaIngreso(new \DateTime('now'));
                                            $admin_sesion->setUsuario($usuario);
                                            $admin_sesion->setDisponible(true);
                                            $em->persist($admin_sesion);
                                            $em->flush();

                                            //$session = new session();
                                            $session->set('iniFront', true);
                                            $session->set('sesion_id', $admin_sesion->getId());
                                            $session->set('code', $f->getLocaleCode());
                                            $session->set('usuario', $datosUsuario);
                                            $session->set('empresa', $empresa);
                                            $session->set('paginas', $paginas);

                                            //1) creo una marca aleatoria en el registro de este usuario
                                            //alimentamos el generador de aleatorios
                                            mt_srand (time());
                                            //generamos un número aleatorio
                                            $numero_aleatorio = mt_rand(1000000,999999999);
                                            //2) meto la marca aleatoria en la tabla de usuario
                                            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneById($session->get('usuario')['id']);
                                            //hay que validar si el usuario hace la marca de recordar
                                            /* $usuario->setCookies($numero_aleatorio);
                                            $em->persist($usuario);
                                            $em->flush();*/

                                            //$ssql = "update usuario set cookie='$numero_aleatorio' where id_usuario=" . $usuario_encontrado->id_usuario;
                                            //mysql_query($ssql);
                                            //3) ahora meto una cookie en el ordenador del usuario con el identificador del usuario y la cookie aleatoria
                                        /*    setcookie("id_usuario", $usuario->getId(), time()+(60*60*24*365));
                                            setcookie("nombre_usuario", $usuario->getLogin());
                                            setcookie("clave_usuario", $usuario->getClave());
                                            setcookie("marca_aleatoria_usuario", $numero_aleatorio, time()+(60*60*24*365));*/

                                            return $this->redirectToRoute('_inicio');                                           
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //if (isset($_COOKIE['remember']))
            //{

            $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa' => $empresa, 
                                                                                                     'error' => $error));
            return $response;

        }
        else {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Url de la empresa no existe')));
        }
    }
        

}