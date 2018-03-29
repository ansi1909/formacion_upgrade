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
        
//echo $request->request->get('correo').'----'.$request->request->get('empresa_id');
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
                            $ok = 0;
                            $error = $this->get('translator')->trans('El correo se esta enviando.');

                           /* $yml = Yaml::parse(file_get_contents($this->get('kernel')->getRootDir().'/config/parametros.yml'));
                            $f = $this->get('funciones');

                            // Envío de correo con los datos de acceso, usuario y clave
                            $parametros = array('asunto' => $yml['parameters']['correo_recuperacion']['asunto'],
                                                'remitente'=>array($yml['parameters']['correo_recuperacion']['remitente'] => 'Formación 2.0'),
                                                'destinatario' => $correo,
                                                'twig' => 'LinkComunBundle:Default:emailRecuperacion.html.twig',
                                                'datos' => array('usuario' => $usuario->getLogin(),
                                                                 'clave' => $usuario->getClave()) );
                           // return new response(var_dump($parametros));
                            $correoRecuperacion = $f->sendEmail($parametros, $this);*/
                           // return $this->redirectToRoute('_login', array('empresa_id'=> $empresa_id));
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
            if ($empresa_bd->getActivo()==true)
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

                //validamos que exista una cookie
                if($_COOKIE && isset($_COOKIE["id_usuario"]) )// && $_COOKIE["marca_aleatoria_usuario"])
                {
                
                    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('id' => $_COOKIE["id_usuario"],
                                                                                                   'empresa' => $empresa_bd->getId(),
                                                                                                   'cookies' => $_COOKIE["marca_aleatoria_usuario"] ) );
                    if($usuario)
                    {
                        $login = $usuario->getLogin();
                        $clave = $usuario->getClave(); 
                        $verificacion=1;
                    }else
                    {
                        $error = $this->get('translator')->trans('La información almacenada en el navegador no es correcta, borre el historial.');
                    }
                }else
                {
                    if ($request->getMethod() == 'POST')
                    {
                        $login = $request->request->get('usuario');
                        $clave = $request->request->get('password');
                        $verificacion=1;
                    }
                }

                if($verificacion)
                {
                    $iniciarSesion = $f->iniciarSesion(array('recordar_datos' => 1,'login' => $login,'clave' => $clave,'empresa' => $empresa,'yml' => $yml['parameters'] ));

                    if($iniciarSesion['exito']==true)
                    {
                        return $this->redirectToRoute('_inicio');
                    }else
                    {
                        if($iniciarSesion['error']==true)
                        {
                            $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa' => $empresa, 
                                                                                                                     'error' => $error));
                            return $response;
                        }
                    }                    
                }else
                {
                    $response = $this->render('LinkFrontendBundle:Default:'.$layout.'login.html.twig', array('empresa' => $empresa, 
                                                                                                             'error' => $error));
                    return $response;
                }
                
            }else
            {
                return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('La empresa está inactiva. Contacte al administrador del sistema.')));
            }
        }else 
        {
            return $this->redirectToRoute('_authExceptionEmpresa', array('mensaje' => $this->get('translator')->trans('Url de la empresa no existe')));
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
        

}