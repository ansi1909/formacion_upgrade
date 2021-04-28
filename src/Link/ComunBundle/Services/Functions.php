<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Link\ComunBundle\Entity\AdminAlarma;
use Symfony\Component\Translation\TranslatorInterface;
use Link\ComunBundle\Entity\AdminSesion;
use Link\ComunBundle\Entity\CertiPagina;
use Link\ComunBundle\Entity\CertiPrueba;
use Link\ComunBundle\Entity\CertiPregunta;
use Link\ComunBundle\Entity\CertiOpcion;
use Link\ComunBundle\Entity\CertiEmpresa;
use Link\ComunBundle\Entity\CertiPreguntaOpcion;
use Link\ComunBundle\Entity\CertiPruebaLog;
use Link\ComunBundle\Entity\CertiPreguntaAsociacion;
use Link\ComunBundle\Entity\AdminCorreo;
use Link\ComunBundle\Entity\AdminUsuario;
use Link\ComunBundle\Entity\AdminEvento;
use Link\ComunBundle\Entity\AdminNoticia;



class Functions
{

  protected $em;
  protected $container;
  protected $mailer;
  private $templating;
  private $translator;

    var $meses=array("1"=>"Enero","2"=>"Febrero","3"=>"Marzo","4"=>"Abril","5"=>"Mayo","6"=>"Junio","7"=>"Julio","8"=>"Agosto","9"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre");

  public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container)
  {

    $this->em = $em;
    $this->container = $container;
    $this->mailer = $container->get('mailer');
        $this->templating = $container->get('templating');
        $this->translator = $container->get('translator');
  }

  // Función que valida si un registro de una tabla puede ser eliminado dependiendo de su relación con otras tablas
  // Parámteros: $id = Valor del id del registro a comparar
  //         $entidad = Nombre de las tabla a comparar (formato Entity)
  public function linkEliminar($id, $entidad)
  {

    $em = $this->em;
    $html = '';

    // $entidades array('entidad destino' => 'atributo destino')
      switch ($entidad)
      {
        case 'AdminAplicacion':
          $entidades = array('AdminAplicacion' => 'aplicacion',
                     'AdminPermiso' => 'aplicacion');
          break;
      case 'AdminRol':
          $entidades = array('AdminPermiso' => 'rol',
                     'AdminRolUsuario' => 'rol');
          break;
        case 'AdminEmpresa':
          $entidades = array('AdminNivel' => 'empresa',
                     'AdminUsuario' => 'empresa',
                     'CertiGrupo' => 'empresa',
                     'CertiPaginaEmpresa' => 'empresa',
                     'AdminNoticia' => 'empresa',
                     'AdminPreferencia' => 'empresa',
                     'CertiCertificado' => 'empresa',
                     'AdminEvento' => 'empresa',
                     'TmpParticipante' => 'empresa',
                     'CertiMuro' => 'empresa',
                     'CertiForo' => 'empresa',
                     'AdminNotificacion' => 'empresa');
          break;
        case 'AdminUsuario':
          $entidades = array('AdminRolUsuario' => 'usuario',
                     'AdminSesion' => 'usuario',
                     'CertiPagina' => 'usuario',
                     'CertiPrueba' => 'usuario',
                     'CertiPregunta' => 'usuario',
                     'CertiOpcion' => 'usuario',
                     'CertiPaginaLog' => 'usuario',
                     'CertiPruebaLog' => 'usuario',
                     'AdminNoticia' => 'usuario',
                     'CertiMuro' => 'usuario',
                     'CertiForo' => 'usuario',
                     'CertiForoArchivo' => 'usuario',
                     'AdminNotificacion' => 'usuario',
                     'AdminNotificacionProgramada' => 'usuario',
                     'AdminPreferencia' => 'usuario',
                     'AdminTutorial' => 'usuario',
                     'AdminEvento' => 'usuario',
                     'AdminLike' => 'usuario',
                     'AdminAlarma' => 'usuario');
          break;
            case 'AdminNivel':
                $entidades = array('AdminUsuario' => 'nivel',
                                   'CertiNivelPagina' => 'nivel',
                                   'AdminEvento' => 'nivel',
                                   'TmpParticipante' => 'nivel');
                break;
            case 'CertiPagina':
                $entidades = array('CertiPagina' => 'pagina',
                                   'CertiPaginaEmpresa' => 'pagina',
                                   'CertiPrueba' => 'pagina',
                                   'CertiGrupoPagina' => 'pagina',
                                   'CertiPaginaLog' => 'pagina',
                                   'CertiForo' => 'pagina',
                                   'CertiMuro' => 'pagina');
                break;
            case 'CertiPrueba':
                $entidades = array('CertiPregunta' => 'prueba',
                                   'CertiOpcion' => 'prueba',
                                   'CertiPruebaLog' => 'prueba');
                break;
            case 'CertiPregunta':
                $entidades = array('CertiPregunta' => 'pregunta',
                                   'CertiPreguntaOpcion' => 'pregunta',
                                   'CertiPreguntaAsociacion' => 'pregunta',
                                   'CertiRespuesta' => 'pregunta');
                break;
            case 'AdminTipoNotificacion':
                $entidades = array('AdminNotificacion' => 'tipoNotificacion');
                break;
            case 'AdminNotificacion':
                $entidades = array('AdminNotificacionProgramada' => 'notificacion');
                break;
            case 'AdminNotificacionProgramada':
                $entidades = array('AdminNotificacionProgramada' => 'grupo');
                break;
            case 'CertiMuro':
                $entidades = array('CertiMuro' => 'muro');
                break;
            case 'CertiForo':
                $entidades = array('CertiForo' => 'foro',
                           'CertiForoArchivo' => 'foro');
                break;
            case 'CertiGrupo':
          $entidades = array('CertiGrupoPagina' => 'grupo');
          break;
        case 'CertiCategoria':
          $entidades = array('CertiPagina' => 'categoria');
          break;
            default:
          $entidades = array();
          break;
      }

      foreach ($entidades as $entity => $attr)
        {
          $qb = $em->createQueryBuilder();
      $qb->select('COUNT(tr.id)')
           ->from('LinkComunBundle:'.$entity, 'tr')
           ->where('tr.'.$attr.' = :id')
           ->setParameter('id',$id);
        $query = $qb->getQuery();
        $cuenta = $query->getSingleScalarResult();
      if ($cuenta)
      {
        $html = 'disabled';
        break;
      }
        }

        return $html;

  }

  public function eliminarAcentos($text)
    {

        $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
        $text = strtolower($text);
        $patron = array (
            // Espacios, puntos y comas por guion
            //'/[\., ]+/' => ' ',

            // Vocales
            '/\+/' => '',
            '/&agrave;/' => 'a',
            '/&egrave;/' => 'e',
            '/&igrave;/' => 'i',
            '/&ograve;/' => 'o',
            '/&ugrave;/' => 'u',

            '/&aacute;/' => 'a',
            '/&eacute;/' => 'e',
            '/&iacute;/' => 'i',
            '/&oacute;/' => 'o',
            '/&uacute;/' => 'u',

            '/&acirc;/' => 'a',
            '/&ecirc;/' => 'e',
            '/&icirc;/' => 'i',
            '/&ocirc;/' => 'o',
            '/&ucirc;/' => 'u',

            '/&atilde;/' => 'a',
            '/&etilde;/' => 'e',
            '/&itilde;/' => 'i',
            '/&otilde;/' => 'o',
            '/&utilde;/' => 'u',

            '/&auml;/' => 'a',
            '/&euml;/' => 'e',
            '/&iuml;/' => 'i',
            '/&ouml;/' => 'o',
            '/&uuml;/' => 'u',

            '/&auml;/' => 'a',
            '/&euml;/' => 'e',
            '/&iuml;/' => 'i',
            '/&ouml;/' => 'o',
            '/&uuml;/' => 'u',

            // Otras letras y caracteres especiales
            '/&aring;/' => 'a',
            '/&ntilde;/' => 'n',

            // Agregar aqui mas caracteres si es necesario

        );

        $text = preg_replace(array_keys($patron),array_values($patron),$text);
        return $text;
    }

  //verifica si el usuario aprobo el curso y alguna evaluacion correspondiente
  public function notasDisponibles($pagina_id,$usuario_id,$yml){
    $em = $this->em;
    $buscar = array($pagina_id);
    $estructura = array($pagina_id);
    $cn_pruebas = 0;
    $cn_aprobadas = 0;
    //obtener estructura del programa
    while ($buscar!=NULL) {
      $pag_id = array_pop($buscar);
      $paginas = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina'=>$pag_id,'estatusContenido'=>$yml['parameters']['estatus_contenido']['activo'],'categoria'=>$yml['parameters']['categoria']['modulo']));
      foreach ($paginas as $pagina) {
        array_push($buscar,$pagina->getId());
        array_push($estructura,$pagina->getId());
      }

    }

    $programa = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina'=>$pag_id,'usuario'=>$usuario_id,'estatusPagina'=>$yml['parameters']['estatus_pagina']['completada']));
    //obtener pruebas
    foreach ($estructura as $pag_id) {
      $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneBy(array('pagina'=>$pag_id));
      if ($prueba != NULL) {
         $cn_pruebas++;
         $prueba_log = $em->getRepository('LinkComunBundle:CertiPruebaLog')->findOneBy(array('prueba'=>$prueba->getId(),'usuario'=>$usuario_id,'estado'=>$yml['parameters']['estado_prueba']['aprobado']));
         if($prueba_log != NULL){
          $cn_aprobadas++;
         }
      }
    }
    return ($cn_aprobadas>0 && $programa )? 1:0;
  }



  public function tipoDescripcion($tipoMensaje, $muro, $author)
    {

        $mensaje = $muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido().' '.$this->translator->trans('realizó una publicación en el muro de').' '.$muro->getPagina()->getNombre().'.';

        if($tipoMensaje == 'Respondió')
        {
           $mensaje = $muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido().' '.$this->translator->trans('respondió en el muro al comentario de').' '.$author.'.';
        }

        return $mensaje;

    }

  //Obtiene la informacion de los tutores asignados a una empresa
  public function getTutoresEmpresa($empresa_id, $yml)
  {
     $em = $this->em;

     $dql = "
                 SELECT au FROM LinkComunBundle:AdminUsuario au
                 INNER JOIN LinkComunBundle:AdminRolUsuario ru WITH au.id = ru.usuario
                 WHERE ru.rol = :rol
                 AND au.empresa = :empresa
                 AND au.activo = TRUE
                ";

        $query = $em->createQuery($dql);
        $query->setParameters(['rol' => $yml['parameters']['rol']['tutor'],
                               'empresa' => $empresa_id]);
        $tutores = $query->getResult();

        return $tutores;

  }

  ////// Envia los correo y notificaciones a los tutores indicando actividad en el muro
    public function sendMailNotificationsMuro($tutores, $yml, $muro, $categoria, $tipoMensaje, $background, $logo, $link_plataforma)
    {

        $em = $this->em;

        foreach ($tutores as $tutor)
        {

            //$correo = ($tutor->getCorreoCorporativo()) ? $tutor->getCorreoCorporativo() : ($tutor->getCorreoPersonal()) ? $tutor->getCorreoPersonal() : null;

            $correo = ($tutor->getCorreoCorporativo())? $tutor->getCorreoCorporativo():(($tutor->getCorreoPersonal()? $tutor->getCorreoPersonal : null));


            // El usuario debe estar dentro del nivel asignado para ver el contenido de la página
            $query = $em->createQuery('SELECT count(np.id) FROM LinkComunBundle:CertiNivelPagina np
                                       JOIN np.paginaEmpresa pe
                                       WHERE pe.pagina = :pagina_id
                                       AND np.nivel = :nivel_id')
                        ->setParameters(array('pagina_id' => $categoria['programa_id'],
                                              'nivel_id' => $tutor->getNivel()->getId()));
            $nivel_asignado = $query->getSingleScalarResult();
            //query revisado

            //verificar si el usuario es tutor, en caso de ser falso envia el correo al tutor
            if($muro->getUsuario()->getId()!= $tutor->getId() && $nivel_asignado && $correo)
            {

                $encabezadoUsuario = 'El usuario: '.$muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido().', '.mb_strtolower($tipoMensaje, 'UTF-8').' lo siguiente: ';

                $parametros_correo = ['twig' => 'LinkFrontendBundle:Leccion:emailMuroTutor.html.twig',
                                      'datos' => ['leccion' => $muro->getPagina()->getNombre(),
                                                  'categoria' => $categoria['categoria'],
                                                  'nombre' => $tutor->getNombre().' '.$tutor->getApellido(),
                                                  'nombrePrograma' => $categoria['nombre'],
                                                  'encabezadoUsuario' => $encabezadoUsuario,
                                                  'mensaje' => $muro->getMensaje(),
                                                  'usuarioPadre' => ($tipoMensaje == 'Respondió') ? $muro->getMuro()->getUsuario()->getNombre().' '.$muro->getMuro()->getUsuario()->getApellido() : null,
                                                  'mensajePadre' => ($tipoMensaje == 'Respondió') ? $muro->getMuro()->getMensaje() : null,
                                                  'empresa' => $tutor->getEmpresa()->getNombre(),
                                                  'background' => $background,
                                                  'logo' => $logo,
                                                  'link_plataforma' => $link_plataforma
                                                 ],
                                        'asunto' => $this->translator->trans('Actividad en el muro').': '.$categoria['nombre'],
                    'remitente' => $this->container->getParameter('mailer_user'),
                    'remitente_name' => $this->container->getParameter('mailer_user_name'),
                    'mailer' => 'soporte_mailer',
                                        'destinatario' => $correo
                                     ];

                $ok = $this->sendEmail($parametros_correo);

                if ($ok)
                {

                    // Nuevo registro en la tabla de admin_correo
                    $tipo_correo = $em->getRepository('LinkComunBundle:AdminTipoCorreo')->find($yml['parameters']['tipo_correo']['muro']);
                    $email = new AdminCorreo();
                    $email->setTipoCorreo($tipo_correo);
                    $email->setEntidadId($muro->getId());
                    $email->setUsuario($tutor);
                    $email->setCorreo($correo);
                    $email->setFecha(new \DateTime('now'));
                    $em->persist($email);
                    $em->flush();

                    //crea la notificacion para el usuario cuando el usuario que publica
                    $descripcion = $this->tipoDescripcion($tipoMensaje, $muro, $parametros_correo['datos']['usuarioPadre']);
                    $tipoAlarma = ($tipoMensaje=='Respondió') ? 'respuesta_muro' : 'aporte_muro';
                    $this->newAlarm($yml['parameters']['tipo_alarma'][$tipoAlarma], $descripcion, $tutor, $muro->getId());

                }

            }

        }

        return 1;

    }

  // Retorna el URL hasta el directorio web de la aplicación. NO incluye el slash.
  public function getWebDirectory()
  {
    $request = Request::createFromGlobals();
    $url = $request->getBasePath();
    return $url;
  }

  function mb_wordwrap($str, $len = 75, $break = " ", $cut = true)
  {
    $len = (int) $len;

    if (empty($str))
      return "";

    $pattern = "";

    if ($cut)
      $pattern = '/([^'.preg_quote($break).']{'.$len.'})/u';
    else
      return wordwrap($str, $len, $break);

    return preg_replace($pattern, "\${1}".$break, $str);
  }

  public function sendEmail($parametros)
  {

    $ok = 0;

    if ($this->container->getParameter('sendMail'))
    {
      $mailer = $this->container->get('swiftmailer.mailer.'.$parametros["mailer"]);
      // ->setBody($this->render($parametros['twig'], $parametros['datos']), 'text/html');
      $body = $this->templating->render($parametros['twig'],$parametros['datos']);
      $message = \Swift_Message::newInstance()
              ->setSubject($parametros['asunto'])
              ->setFrom([$parametros['remitente'] => $parametros['remitente_name']])
              ->setTo($parametros['destinatario'])
              ->setBody($body, 'text/html');
          $ok = $mailer->send($message);
    }

        return $ok;

  }

  /**
  * Función que retorna un arreglo de los id de los roles que tiene el usuario loggeado
  *
  * @param $roles array
  * @return $routes array
  */
  public function getRolesId($roles)
  {

    $roles_id = array();
      foreach ($roles as $rol) {
          $roles_id[] = $rol['id'];
      }

      return $roles_id;

  }

  public function obtenerIcono($extension)
  {

        if(($extension == 'doc')||($extension == 'docx')){
          $icono = 'fa-file-word-o';
        }
        if(($extension == 'png')||($extension == 'jpg')){
          $icono = 'fa-file-image-o';
        }
        if(($extension == 'xls')||($extension == 'xlsx')){
          $icono = 'fa-file-excel-o';
        }
        if($extension == 'pdf'){
          $icono = 'fa-file-pdf-o';
        }
        if($extension == 'txt'){
          $icono = 'fa-file-archive-o';
        }
        return $icono;
  }

  public function generarClave()
  {
        $caracteres = "ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789";
        $numerodeletras=8;
        $contrasena = "";
        for($i=0;$i<$numerodeletras;$i++){
            $contrasena .= substr($caracteres,rand(0,strlen($caracteres)),1);
        }
        return $contrasena;
  }

  function sanear_string($string)
  {

      $string = trim($string);

      $string = str_replace(
          array('Á', 'À', 'Â', 'Ä'),
          array('á', 'á', 'á', 'á'),
          $string
      );

      $string = str_replace(
          array('É', 'È', 'Ê', 'Ë'),
          array('é', 'é', 'é', 'é'),
          $string
      );

      $string = str_replace(
          array('Í', 'Ì', 'Ï', 'Î'),
          array('í', 'í', 'í', 'í'),
          $string
      );

      $string = str_replace(
          array('Ó', 'Ò', 'Ö', 'Ô'),
          array('ó', 'ó', 'ó', 'ó'),
          $string
      );

      $string = str_replace(
          array('Ú', 'Ù', 'Û', 'Ü'),
          array('ú', 'ú', 'ú', 'ú'),
          $string
      );

      $string = str_replace(
          array('Ñ'),
          array('ñ'),
          $string
      );

      return $string;
  }

  // Recibe la fecha de nacimiento en formato AAAA-MM-DD. Retorna la edad.
  public function calcularEdad($fecha)
  {

    if (!$fecha)
    {
      $edad = 'Fecha de nacimiento no especificada';
    }
    else {
      $datetime1 = new \DateTime($fecha);
      $datetime2 = new \DateTime("now");
      $interval = $datetime1->diff($datetime2);

      if ($interval->format('%y') < 1){
        // Si es menos que un año, se contabiliza los meses
        if ($interval->format('%m') < 1)
        {
          // Si es menos que un mes, se contabilizan los días
          $edad = $interval->format('%d').' días';
        }
        else {
          $edad = $interval->format('%m').' meses';
        }
      }
      else {
        $year = $interval->format('%y')==1 ? 'año' : 'años';
        if ($interval->format('%m') == 0)
        {
          $edad = $interval->format('%y').' '.$year;
        }
        else {
          $edad = $interval->format('%y').' '.$year.' y ';
          if ($interval->format('%m') < 2){
            $edad .= $interval->format('%m').' mes';
          }
          else {
            if ($interval->format('%m') == 0)
              $edad = $interval->format('%m') ;
            else
              $edad .= $interval->format('%m').' meses';
          }
        }
      }
    }

        return $edad;

  }

  // Recibe la cantidad de días, meses o años de duración y el formato.
  // Retorna la fecha de vencimiento a partir de hoy
  public function vencimiento($cantidad, $tipo, $formato)
  {

    switch ($tipo)
    {
      case 'Días':
        $vencimiento = date($formato,mktime(0,0,0,date('m'),date('d')+$cantidad,date('Y')));
        break;
      case 'Meses':
        $vencimiento = date($formato,mktime(0,0,0,date('m')+$cantidad,date('d'),date('Y')));
        break;
      case 'Años':
        $vencimiento = date($formato,mktime(0,0,0,date('m'),date('d'),date('Y')+$cantidad));
        break;
      default:
        $vencimiento = date($formato);
    }

    return $vencimiento;

  }

  // Calcula la diferencia de tiempo entre fecha y hoy
  // Retorna la cantidad de días
  public function timeAgo($fecha)
  {

    $days_ago = 0;

    if ($fecha)
    {
      $datetime1 = new \DateTime($fecha);
      $datetime2 = new \DateTime("now");
      $interval = $datetime1->diff($datetime2);
      $days_ago = $interval->format('%a');
    }

        return(integer) $days_ago;

  }

public function porcentaje_finalizacion($fechaInicio,$fechaFin,$diasVencimiento){
    $datetime1 = new \DateTime($fechaInicio);
    $datetime2 = new \DateTime($fechaFin);
    $interval = $datetime2->diff($datetime1);
    $diasTotales = $interval->format('%a');
    $porcentaje = ($diasVencimiento * 100)/$diasTotales;
    return round($porcentaje,0);
  }


  // Calcula la diferencia de tiempo entre fecha y hoy
  // Si es menor de una hora retorna la cantidad de minutos
  // Si es más de una hora y fecha es hoy retorna la hora de fecha
  // Si fecha es ayer retorna "Ayer Hora"
  // Si fecha es menor que ayer se muestra fecha formateado con la hora
  public function sinceTime($fecha)
  {

    $hoy = date('Y-m-d');
    $ayer = date('Y-m-d', strtotime('yesterday'));
    $time_ago = '';

    if ($fecha)
    {

      $datetime1 = new \DateTime($fecha);
      $datetime2 = new \DateTime(date('Y-m-d H:i:s'));
      $interval = $datetime1->diff($datetime2);

      if ($fecha < $ayer)
      {
        $time_ago = $datetime1->format('d/m/Y H:i');
      }
      elseif ($fecha >= $ayer.' 00:00:00' && $fecha < $ayer.' 23:59:59')
      {
        $time_ago = $this->translator->trans('Ayer').' '.$datetime1->format('H:i');
      }
      elseif ($datetime1->format('Y-m-d') == $hoy) {
        if ($interval->format('%h') > 1)
        {
          $time_ago = $this->translator->trans('Hoy a las').' '.$datetime1->format('H:i');
        }
        else {
          $time_ago = 'Hace '.$interval->format('%i').' '.$this->translator->trans('minutos');
        }
      }

    }

        return $time_ago;

  }

  // Retorna la fecha intermedia entre inicio y final en formato AAAA-MM-DD
  // Formato de inicio y final: DD/MM/AAAA
  public function mitadPeriodo($inicio, $final)
  {

    $inicio_arr = explode("/", $inicio);
    $inicio = $inicio_arr[2].'-'.$inicio_arr[1].'-'.$inicio_arr[0];
    $final_arr = explode("/", $final);
    $final = $final_arr[2].'-'.$final_arr[1].'-'.$final_arr[0];

    $datetime1 = new \DateTime($inicio);
    $datetime2 = new \DateTime($final);
    $interval = $datetime1->diff($datetime2);
    $dias_mitad = $interval->format('%a')/2;

    // Fecha intermedia
    $datetime1->modify('+'.$dias_mitad.' days');
    $fecha_intermedia = $datetime1->format('Y-m-d');

    return $fecha_intermedia;

  }

  // Verifica si los roles tienen acceso a una aplicacion
  public function accesoRoles($roles, $app_id)
  {

    $ok = 0;
    $em = $this->em;
    if($roles){
          foreach ($roles as $rol_id)
              {
                $qb = $em->createQueryBuilder();
            $qb->select('COUNT(p.id)')
                 ->from('LinkComunBundle:AdminPermiso', 'p')
                 ->where('p.aplicacion = :app_id AND p.rol = :rol_id')
                 ->setParameters(array('app_id' => $app_id,
                             'rol_id' => $rol_id));
              $query = $qb->getQuery();
              $cuenta = $query->getSingleScalarResult();
            if ($cuenta)
            {
              $ok = 1;
              break;
            }
              }
  }

  return $ok;

  }

  // Verifica si el usuario tiene el rol Empresa y devuelve empresa_id
  public function rolEmpresa($usuario_id, $roles, $yml)
  {

    $empresa_id = 0;
    $em = $this->em;

    foreach ($roles as $rol_id)
        {
          $rol = $em->getRepository('LinkComunBundle:AdminRol')->find($rol_id);
          if ($rol->getEmpresa())
          {
            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
            $empresa_id = $usuario->getEmpresa()->getId();
          }
        }

    return $empresa_id;

  }

  // Retorna el código de dos caracteres del país (Ej. VE)
  public function getLocaleCode()
  {

    $code = 'VE';

    if ($this->is_connected('www.geoplugin.net'))
    {

      $ip = $this->get_client_ip();
      $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
      //get ISO2 country code
      if(property_exists($ipdat, 'geoplugin_countryCode')) {
        if (trim($ipdat->geoplugin_countryCode))
        {
          $code = trim($ipdat->geoplugin_countryCode);
        }
      }

    }

        return $code;

  }

  // Optiene la IP del cliente
  public function get_client_ip() {

      $ipaddress = '';
      if (getenv('HTTP_CLIENT_IP'))
          $ipaddress = getenv('HTTP_CLIENT_IP');
      else if(getenv('HTTP_X_FORWARDED_FOR'))
          $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
      else if(getenv('HTTP_X_FORWARDED'))
          $ipaddress = getenv('HTTP_X_FORWARDED');
      else if(getenv('HTTP_FORWARDED_FOR'))
          $ipaddress = getenv('HTTP_FORWARDED_FOR');
      else if(getenv('HTTP_FORWARDED'))
         $ipaddress = getenv('HTTP_FORWARDED');
      else if(getenv('REMOTE_ADDR'))
          $ipaddress = getenv('REMOTE_ADDR');
      else
          $ipaddress = 'UNKNOWN';
      return $ipaddress;

  }

  // Indica si es alcanzable una web
  public function is_connected($web)
  {
      $connected = @fsockopen($web, 80);
                                          //website, port  (try 80 or 443)
      if ($connected){
          $is_conn = true; //action when connected
          fclose($connected);
      }else{
          $is_conn = false; //action in connection failure
      }
      return $is_conn;

  }

  // Actualiza la fecha y hora del request de la sesión actual
  public function setRequest($sesion_id)
  {

    $em = $this->em;
    $session = new Session();

    $admin_sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($sesion_id);
    if($admin_sesion){
      $admin_sesion->setFechaRequest(new \DateTime('now'));
          $em->persist($admin_sesion);
          $em->flush();
    }
    else{
      $session->invalidate();
          $session->clear();
    }


  }

  // Retorna un arreglo con la estructura completa de las páginas con sus sub-páginas
  public function paginas($pages)
  {

    $paginas = array();

        foreach ($pages as $page)
        {


          $subpaginas = $this->subPaginas($page->getId());


            $paginas[] = array('id' => $page->getId(),
                       'orden' => $page->getOrden(),
                               'nombre' => $page->getNombre(),
                               'categoria' => $page->getCategoria()->getNombre(),
                               'modificacion' => $page->getFechaModificacion()->format('d/m/Y H:i a'),
                               'usuario' => $page->getUsuario()->getNombre().' '.$page->getUsuario()->getApellido(),
                               'status' => $page->getEstatusContenido()->getNombre(),
                               'subpaginas' => $subpaginas,
                               'delete_disabled' => $this->linkEliminar($page->getId(), 'CertiPagina'),
                               'mover' => $this->paginaMovible($page->getId()));

        }

        return $paginas;

  }

  // Retorna un arreglo multidimensional de las subpaginas dada pagina_id
  public function subPaginas($pagina_id, $paginas_asociadas = array(), $json = 0, $movimiento = array())
  {

    $em = $this->em;
    $subpaginas = array();
    $tiene = 0;
    $return = $json ? array() : '';

    $subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina' => $pagina_id),
                                        array('orden' => 'ASC'));

    foreach ($subpages as $subpage)
    {
      $tiene++;
      if (!$json)
      {
        $incluir = 1;
        if($movimiento)
        {
          if($movimiento['pagina_id'] == $subpage->getId())
          {
            $incluir = 0;
          }
        }
        if($incluir)
        {
          $check = in_array($subpage->getId(), $paginas_asociadas) ? ' <span class="fa fa-check"></span>' : '';
          $return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getId().'" p_str="'.$subpage->getCategoria()->getNombre().': '.$subpage->getNombre().'">'.$subpage->getCategoria()->getNombre().': '.$subpage->getNombre().$check;
          $subPaginas = $this->subPaginas($subpage->getId(), $paginas_asociadas);
          if ($subPaginas['tiene'] > 0)
          {
            $return .= '<ul>';
            $return .= $subPaginas['return'];
            $return .= '</ul>';
          }
          $return .= '</li>';
        }
      }
      else {
        // Forma json para tree
        $subPaginas = $this->subPaginas($subpage->getId(), $paginas_asociadas, 1);
        if ($subPaginas['tiene'] > 0)
        {
          $return[] = array('text' => $subpage->getCategoria()->getNombre().': '.$subpage->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right',
                                  'children' => $subPaginas['return']);
        }
        else {
          $return[] = array('text' => $subpage->getCategoria()->getNombre().': '.$subpage->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
        }
      }
    }

    $subpaginas = array('tiene' => $tiene,
              'return' => $return);

    return $subpaginas;
}

public function obtenerCursos(){
  $em = $this->em;
  $query = $em->getConnection()->prepare('SELECT * FROM view_cursos');
  $query->execute();
  $cursos = $query->fetchAll();
  return $cursos;
}

public function obtenerPruebas(){
  $em = $this->em;
  $query = $em->getConnection()->prepare('SELECT * FROM view_pruebas');
  $query->execute();
  $pruebas = $query->fetchAll();
  return $pruebas;
}

public function obtenerEstructuraJson($pagina_id){
    $em = $this->em;
    $query = $em->getConnection()->prepare('SELECT
                                                    fnobtener_estructura
                                                    (:ppagina_id) as
                                                    resultado;');
        $query->bindValue(':ppagina_id', $pagina_id, \PDO::PARAM_INT);
        $query->execute();
        $gc = $query->fetchAll();
        return $gc[0]['resultado'];
 }
 public function estructuraSubpaginasHtml($padre_id,$estructura,$html,$yml,$padding){
        $padding++;
        $subpaginas = $estructura[$padre_id];
        foreach ($subpaginas as $subpagina) {
           $html .='<div class="col-sm-16 col-md-16 col-md-lg-16 modal-text padding-'.$padding.'"><span class="fa fa-angle-right"></span>&nbsp;&nbsp;'.$subpagina['categoria'].' :  '.$subpagina['nombre'].'
           </div>';
          if ($subpagina['categoria_id'] != $yml['parameters']['categoria']['leccion'] && isset($estructura[$subpagina['id']])) {
              $html = $this->estructuraSubpaginasHtml($subpagina['id'],$estructura,$html,$yml,$padding);
          }
        }
    return $html;
}

 public function obtenerEstructuraHtml($estructura,$yml){
   $html = '';
   $padding = 1;
   if(isset($estructura['padre'])){
      $padre = $estructura['padre'];
      $html .='<div class="col-sm-16 col-md-16 col-md-lg-16 modal-text padding-'.$padding.'"><span class="fa fa-angle-right"></span>&nbsp;&nbsp;'.$padre['categoria'].' :  '.$padre['nombre'].'
      </div>';
      if (isset($estructura[$padre['id']]) && $padre['categoria_id'] != $yml['parameters']['categoria']['leccion']) {
        $html = $this->estructuraSubpaginasHtml($padre['id'],$estructura,$html,$yml,$padding);
      }
    }
   return $html;
 }


  // Retorna un arreglo multidimensional de las subpaginas asignadas a una empresa dada pagina_id, empresa_id
  public function subPaginasEmpresa($pagina_id, $empresa_id, $json = 0)
  {

    $em = $this->em;
    $subpaginas = array();
    $tiene = 0;
    $return = $json ? array() : '';
    $query = $em->createQuery("SELECT pe, p FROM LinkComunBundle:CertiPaginaEmpresa pe
                                    JOIN pe.pagina p
                                    WHERE pe.empresa = :empresa_id AND p.pagina = :pagina_id
                                    ORDER BY p.orden ASC")
                    ->setParameters(array('empresa_id' => $empresa_id,
                                'pagina_id' => $pagina_id));
        $subpages = $query->getResult();
    //Query probado

    foreach ($subpages as $subpage)
    {
      $tiene++;
      if (!$json)
      {
        $return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getPagina()->getId().'" p_str="'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().'" tipo_recurso_id="'. $subpage->getPagina()->getCategoria()->getId() .' " >'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre();
        $subPaginas = $this->subPaginasEmpresa($subpage->getPagina()->getId(), $subpage->getEmpresa()->getId());
        if ($subPaginas['tiene'] > 0)
        {
          $return .= '<ul>';
          $return .= $subPaginas['return'];
          $return .= '</ul>';
        }
        $return .= '</li>';
      }
      else {
        // Forma json para tree
        $subPaginas = $this->subPaginasEmpresa($subpage->getPagina()->getId(), $subpage->getEmpresa()->getId(), 1);
        if ($subPaginas['tiene'] > 0)
        {
          $return[] = array('text' => $subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right',
                                  'children' => $subPaginas['return']);
        }
        else {
          $return[] = array('text' => $subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre(),
                                  'state' => array('opened' => true),
                                  'icon' => 'fa fa-angle-double-right');
        }
      }
    }

    $subpaginas = array('tiene' => $tiene,
              'return' => $return);

    return $subpaginas;

  }


  // Crea o actualiza asignaciones de sub-páginas con los mismos valores de la página padre
  public function asignacionSubPaginas($pagina_empresa, $yml, $onlyDates = 0, $onlyMuro = 0, $onlyColaborativo = 0)
  {
        $em = $this->em;
        //$only_muro = ($onlyMuro!=0)? 'TRUE':'FALSE';
        //$only_dates = ($onlyDates!=0)? 'TRUE':'FALSE';
        //$only_colaborativo = ($onlyColaborativo!=0)? 'TRUE':'FALSE';
        $query = $em->getConnection()->prepare('SELECT
                                                    fnasignar_subpaginas
                                                    (:pempresa_id,
                                                     :pestatus_contenido,
                                                     :only_dates,
                                                     :only_muro,
                                                     :only_colaborativo) as
                                                    resultado;');
        $query->bindValue(':pempresa_id', $pagina_empresa->getId(), \PDO::PARAM_INT);
        $query->bindValue(':pestatus_contenido', $yml['parameters']['estatus_contenido']['activo'], \PDO::PARAM_INT);
        $query->bindValue(':only_dates', $onlyDates, \PDO::PARAM_INT);
        $query->bindValue(':only_muro', $onlyMuro, \PDO::PARAM_INT);
        $query->bindValue(':only_colaborativo', $onlyColaborativo, \PDO::PARAM_INT);
        $query->execute();
        $gc = $query->fetchAll();
        $indices = $gc[0]['resultado'];
  }

  // Permite crear la carpeta empresa_id en cada sub-directorio de uploads/
  public function subDirEmpresa($empresa_id, $folder_yml)
  {

    $dir_uploads = $folder_yml['dir_uploads'];
    $dir_project = $folder_yml['dir_project'];

    $subdirectorios[] = 'recursos/usuarios/';
    $subdirectorios[] = 'recursos/niveles/';
    $subdirectorios[] = 'recursos/noticias/';
    $subdirectorios[] = 'recursos/notificaciones/';
    $subdirectorios[] = 'recursos/participantes/';
    $subdirectorios[] = 'recursos/empresas/';
    $subdirectorios[] = 'recursos/qr/';
    $subdirectorios[] = 'recursos/certificados/';
    $subdirectorios[] = 'recursos/espacio/';

    if ($empresa_id)
    {

      foreach ($subdirectorios as $subdirectorio)
      {
        $dir = $dir_uploads.$subdirectorio.$empresa_id.'/';
            if (!file_exists($dir) && !is_dir($dir))
            {
                mkdir($dir, 750, true);
            }
      }

      // Se crea el directorio para los archivos de hojas de estilos
      $dir_web = $dir_project.'web/front/client_styles/'.$empresa_id.'/';
      if (!file_exists($dir_web) && !is_dir($dir_web))
          {
              mkdir($dir_web, 750, true);
              $this->recurse_copy($dir_project.'web/front/client_styles/formacion/', $dir_web);
          }

    }

  }

  function recurse_copy($src,$dst) {

      $dir = opendir($src);
      @mkdir($dst);

      while(false !== ( $file = readdir($dir)) ) {
          if (( $file != '.' ) && ( $file != '..' )) {
              if ( is_dir($src . '/' . $file) ) {
                  $this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
              }
              else {
                  copy($src . '/' . $file,$dst . '/' . $file);
              }
          }
      }
      closedir($dir);
      chmod($dst,0750);

  }

  // Retorna 0 si la fecha dada está en formato DD/MM/YYYY y es correcta
    function checkFecha($fecha){

        $ok = 1;

        $fecha_arr = explode("/", $fecha);

        if (count($fecha_arr) != 3){
            $ok = 0;
        }
        else {
            if (!checkdate($fecha_arr[1], $fecha_arr[0], $fecha_arr[2])){
                $ok = 0;
            }
        }

        return $ok;

    }

    // Formatea la fecha dada en formato DD/MM/YYYY a YYYY-MM-DD
    function formatDate($fecha){

        $fecha_arr = explode("/", $fecha);
        $new_fecha = $fecha_arr[2].'-'.$fecha_arr[1].'-'.$fecha_arr[0];

        return $new_fecha;

    }

  // Retorna un arreglo multidimensional de las subpaginas asignadas a una empresa dada pagina_id, empresa_id
  public function subPaginasNivel($pagina_id, $estatus_contenido, $empresa_id)
  {
    $em = $this->em;
    $subpaginas = array();
    $orden = 0;

    $query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa
                                    AND p.pagina = :pagina_id
                                    AND p.estatusContenido = :estatus_activo
                                    AND pe.activo = :activo
                                    AND pe.fechaInicio <= :hoy
                                   ORDER BY p.orden')
                    ->setParameters(array('empresa' => $empresa_id,
                                'pagina_id' => $pagina_id,
                                          'estatus_activo' => $estatus_contenido,
                                          'activo' => true,
                                          'hoy' => date('Y-m-d')));
        $subpages = $query->getResult();

    foreach ($subpages as $subpage)
    {

            $orden++;

      $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:CertiPrueba p
                                       WHERE p.estatusContenido = :activo AND p.pagina = :pagina_id')
                        ->setParameters(array('activo' => $estatus_contenido,
                                    'pagina_id' => $subpage->getPagina()->getId()));
            $tiene_evaluacion = $query->getSingleScalarResult();

            $subpaginas[$subpage->getPagina()->getId()] = array('id' => $subpage->getPagina()->getId(),
                                      'orden' => $orden,
                                                  'nombre' => $subpage->getPagina()->getNombre(),
                                                  'categoria' => $subpage->getPagina()->getCategoria()->getNombre(),
                                                  'foto' => $subpage->getPagina()->getFoto(),
                                                  'tiene_evaluacion' => $subpage->getPruebaActiva() ? $tiene_evaluacion ? true : false : false,
                                                  'acceso' => $subpage->getAcceso(),
                                                  'muro_activo' => $subpage->getMuroActivo(),
                                                  'espacio_colaborativo' => $subpage->getColaborativo(),
                                                  'prelacion' => $subpage->getPrelacion() ? $subpage->getPrelacion()->getId() : 0,
                                                  'inicio' => $subpage->getFechaInicio()->format('d/m/Y'),
                                                  'vencimiento' => $subpage->getFechaVencimiento()->format('d/m/Y'),
                                                  'subpaginas' => $this->subPaginasNivel($subpage->getPagina()->getId(), $estatus_contenido, $empresa_id));

    }

    return $subpaginas;

  }

  // Retorna un arreglo multidimensional con la estructura del menú lateral para la vista de las lecciones
  public function menuLecciones($indexedPages, $programa, $subpagina_id, $href, $usuario_id, $estatus_completada, $dimension = 1, $to_activate = 1)
  {

    $em = $this->em;
    $menu_str = '';
    $i = 0;

    if (count($programa['subpaginas']))
    {
      foreach ($programa['subpaginas'] as $subpagina)
      {
        if ($subpagina['acceso'])
        {
          $i++;
          $active = '';
          $isCurrentPage = '';
          if ($subpagina_id && $to_activate)
          {
            if ($subpagina['id'] == $subpagina_id)
            {
              $active = ' active';
              $isCurrentPage = 'j-current';
              $to_activate = 0;
            }
            else {
              if ($dimension == 2 && count($subpagina['subpaginas']))
              {
                if (array_key_exists($subpagina_id, $subpagina['subpaginas']))
                {
                  $active = ' active';
                  $to_activate = 0;
                }
                else {
                  // Recorrer las sub-páginas de la sub-página a ver si se encuentra subpagina_id dentro del conjunto
                  foreach ($subpagina['subpaginas'] as $nieto)
                  {
                    if (count($nieto['subpaginas']))
                    {
                      if (array_key_exists($subpagina_id, $nieto['subpaginas']))
                      {
                        $active = ' active';
                        $to_activate = 0;
                        break;
                      }
                    }
                  }
                }
              }
            }
          }
          else {
            if ($i==1 && $to_activate)
            {
              $active = ' active';
              $to_activate = 0;
            }
          }
          $bloqueada = '';
          $prelacion_id = 0;
          $prelada_por = '';
          $aprobada = '';
          if ($subpagina['prelacion'])
          {
            // Se determina si el contenido estará bloqueado
            $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                        WHERE pl.pagina = :pagina_id
                                        AND pl.usuario = :usuario_id
                                        AND pl.estatusPagina = :completada')
                        ->setParameters(array('pagina_id' => $subpagina['prelacion'],
                                    'usuario_id' => $usuario_id,
                                          'completada' => $estatus_completada));
            $leccion_completada = $query->getSingleScalarResult();
            $bloqueada = $leccion_completada ? '' : 'less-disabled';
            $prelacion_id = $leccion_completada ? 0 : $subpagina['prelacion'];
          }
          else {
            // Puede que el padre tenga prelación
            if ($indexedPages[$subpagina['id']]['padre'])
            {
              $padre = $indexedPages[$indexedPages[$subpagina['id']]['padre']];
              if ($padre['prelacion'])
              {
                $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                            WHERE pl.pagina = :pagina_id
                                            AND pl.usuario = :usuario_id
                                            AND pl.estatusPagina = :completada')
                            ->setParameters(array('pagina_id' => $padre['prelacion'],
                                        'usuario_id' => $usuario_id,
                                              'completada' => $estatus_completada));
                $leccion_completada = $query->getSingleScalarResult();
                $bloqueada = $leccion_completada ? '' : 'less-disabled';
                $prelacion_id = $leccion_completada ? 0 : $padre['prelacion'];
              }
            }
          }
          if ($prelacion_id)
          {
            if (isset($indexedPages[$prelacion_id]))
            {
              $prelada_por = $this->translator->trans('Prelada por').' '.$indexedPages[$prelacion_id]['categoria'].': '.$indexedPages[$prelacion_id]['nombre'];
            }
            else {
              // La prelación no está dentro del conjunto de indexPages
              $prelada = $em->getRepository('LinkComunBundle:CertiPagina')->find($prelacion_id);
              $prelada_por = $this->translator->trans('Prelada por').' '.$prelada->getCategoria()->getNombre().': '.$prelada->getNombre();
            }
          }
          // Se determina si el contenido ya está completado
          $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                      WHERE pl.pagina = :pagina_id
                                      AND pl.usuario = :usuario_id
                                      AND pl.estatusPagina = :completada')
                      ->setParameters(array('pagina_id' => $subpagina['id'],
                                  'usuario_id' => $usuario_id,
                                        'completada' => $estatus_completada));
          $aprobada = $query->getSingleScalarResult();
          if ($aprobada)
          {
            $class_aprobada = 'liItemsAprob';
            $icon_aprobada = '<i class="material-icons">check_circle</i>';
          }
          else {
            $class_aprobada = '';
            $icon_aprobada = '';
          }

          $menu_str .= '<li title="'.$prelada_por.'" class="'.$class_aprobada.'" data-toggle="tooltip" data-placement="bottom">
                  <a href="'.$href.'/'.$subpagina['id'].'" class="'.$active.' '.$bloqueada.' '.$isCurrentPage.'" id="m-'.$subpagina['id'].'">'.$icon_aprobada.$subpagina['nombre'].'</a>';
          if (count($subpagina['subpaginas']) && $dimension == 1)
          {
            // Recorremos las sub-páginas de la sub-página a ver si existe al menos una que tenga acceso
            $acceso = 0;
            foreach ($subpagina['subpaginas'] as $sub)
            {
              if ($sub['acceso'])
              {
                $acceso = 1;
                break;
              }
            }
            if ($acceso)
            {
              $menu_str .= '<ul class="ul-items">';
              $menu_str .= $this->menuLecciones($indexedPages, $subpagina, $subpagina_id, $href, $usuario_id, $estatus_completada, 2, $to_activate);
              $menu_str .= '</ul>';
            }
          }
          $menu_str .= '</li>';
        }
      }
    }
    else {
      // Se verifica si es una página padre
      $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($programa['id']);
      if ($programa['acceso'] && !$pagina->getPagina())
      {

        $active = ' active';
        $bloqueada = '';
        $prelacion_id = 0;
        $prelada_por = '';
        $aprobada = '';

        if ($programa['prelacion'])
        {
          // Se determina si el contenido estará bloqueado
          $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                      WHERE pl.pagina = :pagina_id
                                      AND pl.usuario = :usuario_id
                                      AND pl.estatusPagina = :completada')
                      ->setParameters(array('pagina_id' => $programa['prelacion'],
                                  'usuario_id' => $usuario_id,
                                        'completada' => $estatus_completada));
          $leccion_completada = $query->getSingleScalarResult();
          $bloqueada = $leccion_completada ? '' : 'less-disabled';
          $prelacion_id = $leccion_completada ? 0 : $programa['prelacion'];
        }
        if ($prelacion_id)
        {
          if (isset($indexedPages[$prelacion_id]))
          {
            $prelada_por = $this->translator->trans('Prelada por').' '.$indexedPages[$prelacion_id]['categoria'].': '.$indexedPages[$prelacion_id]['nombre'];
          }
          else {
            // La prelación no está dentro del conjunto de indexPages
            $prelada = $em->getRepository('LinkComunBundle:CertiPagina')->find($prelacion_id);
            $prelada_por = $this->translator->trans('Prelada por').' '.$prelada->getCategoria()->getNombre().': '.$prelada->getNombre();
          }
        }

        // Se determina si el contenido ya está completado
        $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                    WHERE pl.pagina = :pagina_id
                                    AND pl.usuario = :usuario_id
                                    AND pl.estatusPagina = :completada')
                    ->setParameters(array('pagina_id' => $programa['id'],
                                'usuario_id' => $usuario_id,
                                      'completada' => $estatus_completada));
        $aprobada = $query->getSingleScalarResult();
        if ($aprobada)
        {
          $class_aprobada = 'liItemsAprob';
          $icon_aprobada = '<i class="material-icons">check_circle</i>';
        }
        else {
          $class_aprobada = '';
          $icon_aprobada = '';
        }

        $menu_str .= '<li title="'.$prelada_por.'" class="'.$class_aprobada.'" data-toggle="tooltip" data-placement="bottom">
                <a href="'.$href.'" class="'.$active.' '.$bloqueada.' '.$isCurrentPage.'" id="m-'.$programa['id'].'">'.$icon_aprobada.$programa['nombre'].'</a>';
        $menu_str .= '</li>';

      }
    }

    return $menu_str;

  }

  public function indexPages($pagina)
  {

    $indexedPages = array();
    $sobrinos = 0;

    // Recorrido inicial de las sub-páginas para determinar si a este nivel tienen sobrinos (sub-páginas de los hermanos)
    foreach ($pagina['subpaginas'] as $subpagina)
    {
      if (count($subpagina['subpaginas']))
      {
        $sobrinos++;
      }
    }

    // Indexar las sub-páginas
    foreach ($pagina['subpaginas'] as $subpagina)
    {
      $subpagina['padre'] = $pagina['id'];
      $subpagina['sobrinos'] = $sobrinos;
      $subpagina['hijos'] = count($subpagina['subpaginas']);
      $indexedPages[$subpagina['id']] = $subpagina;
      if (count($subpagina['subpaginas']))
      {
        $indexedPages += $this->indexPages($subpagina);
      }
    }

    return $indexedPages;

  }

  // Retorna un arreglo con toda la información de la lecciones de una página, con su muro.
  public function contenidoLecciones($pagina_arr, $wizard, $usuario_id, $yml, $empresa_id)
  {

    $em = $this->em;
    $lecciones = array();

    $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_arr['id']);

    $lecciones = $pagina_arr;
    $lecciones['descripcion'] = $pagina->getDescripcion();
    $lecciones['contenido'] = $pagina->getContenido();
    $lecciones['foto'] = $pagina->getFoto();
    $lecciones['pdf'] = $pagina->getPdf();
    $lecciones['next_subpage'] = 0;
    $bloqueada = 0;
    if ($pagina_arr['prelacion'])
    {
      // Se determina si el contenido estará bloqueado
      $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                  WHERE pl.pagina = :pagina_id
                                  AND pl.usuario = :usuario_id
                                  AND pl.estatusPagina = :completada')
                  ->setParameters(array('pagina_id' => $pagina_arr['prelacion'],
                              'usuario_id' => $usuario_id,
                                    'completada' => $yml['parameters']['estatus_pagina']['completada']));
      $leccion_completada = $query->getSingleScalarResult();
      $bloqueada = $leccion_completada ? 0 : 1;
    }
    $lecciones['bloqueada'] = $bloqueada;

    // Muros recientes
    $muros_recientes = $this->muroPagina($pagina_arr['id'], 'id', 'DESC', 0, 5, $usuario_id, $empresa_id, $yml['parameters']['social']);
    $muros_valorados = $this->muroPaginaValorados($pagina_arr['id'], 0, 5, $usuario_id, $empresa_id, $yml['parameters']['social']);
    $lecciones['muros_recientes'] = $muros_recientes;
    $lecciones['muros_valorados'] = $muros_valorados;

    $sublecciones = array();
    $i = 0;
        foreach ($pagina_arr['subpaginas'] as $subpagina_arr)
    {

      if (!$wizard && $i==0 && $subpagina_arr['acceso'])
      {
        // Al terminar la lectura del contenido, el botón "Siguiente" se debe redireccionar al primer hijo con acceso
        $lecciones['next_subpage'] = $subpagina_arr['id'];
        $i++;
      }

      $subpagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($subpagina_arr['id']);
      $subleccion = $subpagina_arr;
      $subleccion['descripcion'] = $subpagina->getDescripcion();
      $subleccion['contenido'] = $subpagina->getContenido();
      $subleccion['foto'] = $subpagina->getFoto();
      $subleccion['pdf'] = $subpagina->getPdf();
      $bloqueada = 0;
      if ($subpagina_arr['prelacion'])
      {
        // Se determina si el contenido estará bloqueado
        $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                    WHERE pl.pagina = :pagina_id
                                    AND pl.usuario = :usuario_id
                                    AND pl.estatusPagina = :completada')
                    ->setParameters(array('pagina_id' => $subpagina_arr['prelacion'],
                                'usuario_id' => $usuario_id,
                                      'completada' => $yml['parameters']['estatus_pagina']['completada']));
        $leccion_completada = $query->getSingleScalarResult();
        $bloqueada = $leccion_completada ? 0 : 1;
      }
      $subleccion['bloqueada'] = $bloqueada;

      // Se verifica si esta sublección ya fue vista
      $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                  WHERE pl.pagina = :pagina_id
                                  AND pl.usuario = :usuario_id
                                  AND pl.estatusPagina IN (:vista)')
                  ->setParameters(array('pagina_id' => $subpagina_arr['id'],
                              'usuario_id' => $usuario_id,
                                    'vista' => array($yml['parameters']['estatus_pagina']['completada'], $yml['parameters']['estatus_pagina']['en_evaluacion'])));
      $vista = $query->getSingleScalarResult();
      $subleccion['vista'] = $vista;

      $muros_recientes = $this->muroPagina($subpagina_arr['id'], 'id', 'DESC', 0, 5, $usuario_id, $empresa_id, $yml['parameters']['social']);
      $muros_valorados = $this->muroPaginaValorados($subpagina_arr['id'], 0, 5, $usuario_id, $empresa_id, $yml['parameters']['social']);
      $subleccion['muros_recientes'] = $muros_recientes;
      $subleccion['muros_valorados'] = $muros_valorados;

      $sublecciones[] = $subleccion;

    }
    $lecciones['subpaginas'] = $sublecciones;

    return $lecciones;

  }

  // Arreglo de comentarios en el muro de una página y sus respuestas
  public function muroPagina($pagina_id, $orderCriteria, $asc, $offset, $limit, $usuario_id, $empresa_id, $social)
  {

    $em = $this->em;
    $qb = $em->createQueryBuilder();
        $qb->select('m')
           ->from('LinkComunBundle:CertiMuro', 'm')
           ->andWhere('m.pagina = :pagina_id')
           ->andWhere('m.empresa = :empresa_id')
           ->andWhere('m.muro IS NULL')
           ->orderBy('m.'.$orderCriteria, $asc)
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->setParameters(array('pagina_id' => $pagina_id,
                       'empresa_id' => $empresa_id));
        $query = $qb->getQuery();
        $muros_bd = $query->getResult();
        $muros = array();

        // Total de comentarios en este muro
        $query = $em->createQuery('SELECT COUNT(m.id) FROM LinkComunBundle:CertiMuro m
                                WHERE m.pagina = :pagina_id
                                AND m.muro IS NULL
                                AND m.empresa = :empresa_id')
                ->setParameters(array('pagina_id' => $pagina_id,
                            'empresa_id' => $empresa_id));
    $total_comentarios = $query->getSingleScalarResult();

        foreach ($muros_bd as $muro)
        {

          // Total de respuestas de este comentario
          $query = $em->createQuery('SELECT COUNT(m.id) FROM LinkComunBundle:CertiMuro m
                                  WHERE m.muro = :muro_id')
                  ->setParameter('muro_id', $muro->getId());
      $total_respuestas = $query->getSingleScalarResult();

          $muros[] = array('id' => $muro->getId(),
                 'mensaje' => $muro->getMensaje(),
                 'usuario' => $muro->getUsuario()->getId() == $usuario_id ? $this->translator->trans('Yo') : $muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido(),
                 'foto' => $muro->getUsuario()->getFoto(),
                 'cuando' => $this->sinceTime($muro->getFechaRegistro()->format('Y-m-d H:i:s')),
                 'total_respuestas' => $total_respuestas,
                 'likes' => $this->likes($social['muro'], $muro->getId(), $usuario_id),
                 'submuros' => $this->subMuros($muro->getId(), 0, 5, $usuario_id, $social));
        }

        $return = array('muros' => $muros,
                'total_comentarios' => $total_comentarios);
        return $return;

  }

  // Arreglo de comentarios en el muro de una página y sus respuestas
  public function muroPaginaValorados($pagina_id, $offset, $limit, $usuario_id, $empresa_id, $social)
  {

    $em = $this->em;

    // Búsqueda inicial de todos los
    $qb = $em->createQueryBuilder();
        $qb->select('m')
           ->from('LinkComunBundle:CertiMuro', 'm')
           ->andWhere('m.pagina = :pagina_id')
           ->andWhere('m.empresa = :empresa_id')
           ->andWhere('m.muro IS NULL')
           ->setParameters(array('pagina_id' => $pagina_id,
                       'empresa_id' => $empresa_id));
        $query = $qb->getQuery();
        $muros_bd = $query->getResult();
        $muros = array();
        $muros_likes = array();

        foreach ($muros_bd as $muro)
        {
          $likes = $this->likes($social['muro'], $muro->getId(), $usuario_id);
          $muros_likes[$muro->getId()] = $likes['cantidad'];
        }
        arsort($muros_likes); // Se ordena de mayor a menor por la cantidad de likes

        // Se toman los ids desde $offset hasta $limit elemento
        $i = 0; // iterador del foreach
        $j = 0; // Incrementador para el limit
        foreach ($muros_likes as $muro_id => $likes)
        {
          if ($i >= $offset)
          {

            $muro = $em->getRepository('LinkComunBundle:CertiMuro')->find($muro_id);

            // Total de respuestas de este comentario
            $query = $em->createQuery('SELECT COUNT(m.id) FROM LinkComunBundle:CertiMuro m
                                    WHERE m.muro = :muro_id')
                    ->setParameter('muro_id', $muro->getId());
        $total_respuestas = $query->getSingleScalarResult();

            $muros[] = array('id' => $muro->getId(),
                   'mensaje' => $muro->getMensaje(),
                   'usuario' => $muro->getUsuario()->getId() == $usuario_id ? $this->translator->trans('Yo') : $muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido(),
                   'foto' => $muro->getUsuario()->getFoto(),
                   'cuando' => $this->sinceTime($muro->getFechaRegistro()->format('Y-m-d H:i:s')),
                   'total_respuestas' => $total_respuestas,
                   'likes' => $this->likes($social['muro'], $muro->getId(), $usuario_id),
                   'submuros' => $this->subMuros($muro->getId(), 0, 5, $usuario_id, $social));
            $j++;
          }
          if ($j == $limit)
          {
            break;
          }
          $i++;
        }

        // Total de comentarios en este muro
        $query = $em->createQuery('SELECT COUNT(m.id) FROM LinkComunBundle:CertiMuro m
                                WHERE m.pagina = :pagina_id
                                AND m.muro IS NULL
                                AND m.empresa = :empresa_id')
                ->setParameters(array('pagina_id' => $pagina_id,
                            'empresa_id' => $empresa_id));
    $total_comentarios = $query->getSingleScalarResult();

    $return = array('muros' => $muros,
                'total_comentarios' => $total_comentarios);
        return $return;

  }

  public function subMuros($muro_id, $offset, $limit, $usuario_id, $social)
  {

    $em = $this->em;
    $qb = $em->createQueryBuilder();
        $qb->select('m')
           ->from('LinkComunBundle:CertiMuro', 'm')
           ->andWhere('m.muro = :muro_id')
           ->orderBy('m.id', 'DESC')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->setParameter('muro_id', $muro_id);
        $query = $qb->getQuery();
        $submuros_bd = $query->getResult();

      $submuros = array();
      foreach ($submuros_bd as $submuro)
      {
        $submuros[] = array('id' => $submuro->getId(),
                  'mensaje' => $submuro->getMensaje(),
                  'usuario' => $submuro->getUsuario()->getId() == $usuario_id ? $this->translator->trans('Yo') : $submuro->getUsuario()->getNombre().' '.$submuro->getUsuario()->getApellido(),
                  'foto' => $submuro->getUsuario()->getFoto(),
                  'cuando' => $this->sinceTime($submuro->getFechaRegistro()->format('Y-m-d H:i:s')),
                  'likes' => $this->likes($social['muro'], $submuro->getId(), $usuario_id));
      }

      return $submuros;

  }

  // Retorna 1 si la prueba está habilitada
  public function pruebaActiva($pagina, $usuario_id, $estatus_completada)
  {

    $em = $this->em;
    $activar = 1;

    foreach ($pagina['subpaginas'] as $subpagina)
    {

      $query = $em->createQuery('SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPaginaLog pl
                                  WHERE pl.pagina = :pagina_id
                                  AND pl.usuario = :usuario_id
                                  AND pl.estatusPagina = :completada')
                  ->setParameters(array('pagina_id' => $subpagina['id'],
                              'usuario_id' => $usuario_id,
                                    'completada' => $estatus_completada));
      $leccion_completada = $query->getSingleScalarResult();

      if (!$leccion_completada)
      {
        $activar = 0;
        break;
      }

    }

    return $activar;

  }

  public function startLesson($indexedPages, $pagina_id, $usuario_id, $pagina_iniciada)
  {

    $em = $this->em;
    $logs = array();

    $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);
    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
    $estatus_pagina = $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($pagina_iniciada);

    $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                            'usuario' => $usuario_id));

        if (!$pagina_log)
        {

          //Revisar antes si el padre ya tiene log
          if ($indexedPages[$pagina_id]['padre'] > 0)
          {
            $logs_padre = $this->startLesson($indexedPages, $indexedPages[$pagina_id]['padre'], $usuario_id, $pagina_iniciada);
            if (count($logs_padre))
            {
              $logs += $logs_padre;
            }
          }

            $pagina_log = new CertiPaginaLog();
            $pagina_log->setPagina($pagina);
            $pagina_log->setUsuario($usuario);
            $pagina_log->setFechaInicio(new \DateTime('now'));
            $pagina_log->setEstatusPagina($estatus_pagina);
            $pagina_log->setPorcentajeAvance(0);
            $em->persist($pagina_log);
          $em->flush();

          $logs[] = $pagina_log->getId();

        }

    return $logs;

  }

  public function finishLesson($indexedPages, $pagina_id, $usuario_id, $yml)
  {

    $em = $this->em;
    $log_id = '0_0'; // logid_puntos

    $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $pagina_id,
                                                                                            'usuario' => $usuario_id,
                                                                                            'estatusPagina' => $yml['parameters']['estatus_pagina']['iniciada']));

        if ($pagina_log)
        {

          $puntos_agregados = 0;
          // Revisar antes si tiene sub-páginas iniciadas
          $subpaginas_iniciadas = $this->subpaginasIniciadas($indexedPages, $pagina_id, $usuario_id, $yml['parameters']['estatus_pagina']['completada']);
          if (!$subpaginas_iniciadas)
          {
            // Se completa o se coloca en evaluación la lección
            if ($indexedPages[$pagina_id]['tiene_evaluacion'])
            {
              $estatus = $yml['parameters']['estatus_pagina']['en_evaluacion'];
              $avance = (1 - $yml['parameters']['ponderacion']['evaluacion'])*100;
            }
            else {
              $estatus = $yml['parameters']['estatus_pagina']['completada'];
              $avance = 100;
              // Si la completó en menos de la mitad del período se gana unos puntos
              $mitad_periodo = $this->mitadPeriodo($indexedPages[$pagina_id]['inicio'], $indexedPages[$pagina_id]['vencimiento']);
              $inicio_arr = explode("/", $indexedPages[$pagina_id]['inicio']);
          $inicio = $inicio_arr[2].'-'.$inicio_arr[1].'-'.$inicio_arr[0];
          if ($inicio <= $mitad_periodo)
          {
            $puntos_agregados = $yml['parameters']['puntos']['mitad_periodo'];
            $puntos = $pagina_log->getPuntos() + $puntos_agregados;
            $pagina_log->setPuntos($puntos);
          }
            }
            $status_pagina = $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($estatus);
            $pagina_log->setFechaFin(new \DateTime('now'));
              $pagina_log->setEstatusPagina($status_pagina);
              $pagina_log->setPorcentajeAvance($avance);
              $em->persist($pagina_log);
            $em->flush();

            // Cálculo del porcentaje de avance de toda la línea de ascendente
            $this->calculoAvance($indexedPages, $pagina_id, $usuario_id, $yml, $puntos_agregados);

          }

          $log_id = $pagina_log->getId().'_'.$puntos_agregados;

        }

    return $log_id;

  }

  public function subpaginasIniciadas($indexedPages, $pagina_id, $usuario_id, $estatus_completada)
  {

    $em = $this->em;
    $iniciada = 0;
    $completadas = 0;

    if (count($indexedPages[$pagina_id]['subpaginas']))
    {
      foreach ($indexedPages[$pagina_id]['subpaginas'] as $subpagina)
      {
        $qb = $em->createQueryBuilder();
            $qb->select('pl')
               ->from('LinkComunBundle:CertiPaginaLog', 'pl')
               ->andWhere('pl.pagina = :pagina_id')
               ->andWhere('pl.usuario = :usuario_id')
               ->orderBy('pl.id', 'DESC')
               ->setParameters(array('pagina_id' => $subpagina['id'],
                             'usuario_id' => $usuario_id));
            $query = $qb->getQuery();
            $subpagina_iniciada = $query->getResult();
        if ($subpagina_iniciada)
        {
          if ($subpagina_iniciada[0]->getEstatusPagina()->getId() != $estatus_completada)
          {
            $iniciada = 1;
            break;
          }
          else {
            $completadas++;
          }
        }
      }
      if (!$iniciada && count($indexedPages[$pagina_id]['subpaginas']) != $completadas)
      {
        $iniciada = 1;
      }
    }

        return $iniciada;

  }

  public function calculoAvance($indexedPages, $pagina_id, $usuario_id, $yml, $puntos = 0)
  {

    $em = $this->em;

    if ($indexedPages[$pagina_id]['padre'])
    {

      $pagina_padre_id = $indexedPages[$pagina_id]['padre'];
      $pagina_padre_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $pagina_padre_id,
                                                                                                'usuario' => $usuario_id));

      if ($pagina_padre_log)
      {

        $n = count($indexedPages[$pagina_padre_id]['subpaginas']);
        $max_porcentaje = $indexedPages[$pagina_padre_id]['tiene_evaluacion'] ? (1 - $yml['parameters']['ponderacion']['evaluacion']) : 1;
        $avance_total = 0;
        $avance_parcial = 0;
        $subpaginas_completadas = 1;

        foreach ($indexedPages[$pagina_padre_id]['subpaginas'] as $subpagina)
        {
          $subpagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $subpagina['id'],
                                                                                                   'usuario' => $usuario_id));
          if ($subpagina_log)
          {
            $avance_parcial += $subpagina_log->getPorcentajeAvance();
            if ($subpagina_log->getEstatusPagina()->getId() != $yml['parameters']['estatus_pagina']['completada'])
            {
              $subpaginas_completadas = 0;
            }
          }
          else {
            $subpaginas_completadas = 0;
          }
        }

        $avance_total = ($avance_parcial/$n)*$max_porcentaje;

        if ($indexedPages[$pagina_padre_id]['tiene_evaluacion'])
        {
          $avance_prueba = 0;
          $query = $em->createQuery("SELECT pl FROM LinkComunBundle:CertiPruebaLog pl
                                          JOIN pl.prueba p
                                          WHERE pl.usuario = :usuario_id
                                          AND p.pagina = :pagina_id
                                          AND pl.estado != :estado
                                          ORDER BY pl.id DESC")
                          ->setParameters(array('usuario_id' => $usuario_id,
                                      'pagina_id' => $pagina_padre_id,
                                      'estado' => $yml['parameters']['estado_prueba']['reprobado']));
              $pruebas_log = $query->getResult();
              if ($pruebas_log)
              {
                $avance_prueba = $pruebas_log[0]->getPorcentajeAvance();
              }
              $avance_total += $avance_prueba*$yml['parameters']['ponderacion']['evaluacion'];
        }

        // Finalmente se almacena el avance calculado en la página padre
        $avance_total = round($avance_total, 2);
        $pagina_padre_log->setPorcentajeAvance($avance_total > 100 ? 100 : $avance_total);
        if ($avance_total >= 100)
        {
          $estatus_pagina = $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($yml['parameters']['estatus_pagina']['completada']);
          $pagina_padre_log->setEstatusPagina($estatus_pagina);
          $pagina_padre_log->setFechaFin(new \DateTime('now'));
        }
        else {
          if ($subpaginas_completadas && $indexedPages[$pagina_padre_id]['tiene_evaluacion'])
          {
            $estatus_pagina = $em->getRepository('LinkComunBundle:CertiEstatusPagina')->find($yml['parameters']['estatus_pagina']['en_evaluacion']);
            $pagina_padre_log->setEstatusPagina($estatus_pagina);
          }
        }

        // Puntos agregados
        $puntos_agregados = $pagina_padre_log->getPuntos() + $puntos;
        $pagina_padre_log->setPuntos($puntos_agregados);
              $em->persist($pagina_padre_log);
            $em->flush();

      }

      // Calcular el avance del abuelo
      $this->calculoAvance($indexedPages, $pagina_padre_id, $usuario_id, $yml, $puntos);

    }

  }

  public function likes($social_id, $entidad_id, $usuario_id)
  {

    $em = $this->em;
    $cantidad = 0;
    $ilike = 0;

    $likes = $em->getRepository('LinkComunBundle:AdminLike')->findBy(array('social' => $social_id,
                                                                               'entidadId' => $entidad_id));

    foreach ($likes as $like)
    {
      $cantidad++;
      if ($like->getUsuario()->getId() == $usuario_id)
      {
        $ilike = 1;
      }
    }

    return array('cantidad' => $cantidad,
           'ilike' => $ilike);

  }

  //requiere formato 2001-12-11 hora, retorna 'dia de mes de año'
    public function fechaNatural($fecha)
    {
        if($fecha!="")
        {
            $arreglo=explode(" ",$fecha);
            $arreglo=$arreglo[0];
            $arreglo=explode("-",$arreglo);
            return $arreglo[2]." de ".$this->meses[(int)$arreglo[1]]." de ".$arreglo[0];
        }else
        {
            return "";
        }
    }

    // función para retornar todos los ids de las sugpaginas de una programa
    public function hijas($subpagina, $hijas=array())
  {
    foreach ($subpagina as $sub)
    {
      $hijas[] = $sub['id'];
      if ($sub['subpaginas'])
      {
        $hijas = $this->hijas($sub['subpaginas'], $hijas);
      }
    }
    return $hijas;
  }

  public function drawComment($muro, $prefix)
  {

    $uploads = $this->container->getParameter('folders')['uploads'];
    $img_user = $muro['foto'] ? $uploads.$muro['foto'] : $this->getWebDirectory().'/front/assets/img/user-default.png';
        $like_class = $muro['likes']['ilike'] ? 'ic-lke-act' : '';
        $html = '<div class="comment">
                    <div class="comm-header d-flex align-items-center mb-2">
                        <img class="img-fluid avatar-img" src="'.$img_user.'" alt="">
                        <div class="wrap-info-user flex-column ml-2">
                            <div class="name text-xs color-dark-grey">'.$muro['usuario'].'</div>
                            <div class="date text-xs color-grey">'.$muro['cuando'].'</div>
                        </div>
                    </div>
                    <div class="comm-body">
                        <p>'.$muro['mensaje'].'</p>
                    </div>
                    <div class="comm-footer d-flex justify-content-between align-items-center">
                        <a href="#" class="mr-0 text-sm color-light-grey like" data="'.$muro['id'].'">

                        </a>
                        <a href="#" class="links text-right  reply_comment" id="href_reply_'.$muro['id'].'" data="'.$muro['id'].'">'.$this->translator->trans('Responder').'</a>
                    </div>
                    <div id="'.$prefix.'_div-response-'.$muro['id'].'">
                    </div>
                    <div id="'.$prefix.'_respuestas-'.$muro['id'].'">';
        foreach ($muro['submuros'] as $submuro)
        {
          $html .= $this->drawResponses($submuro, $prefix);
        }

        if ($muro['total_respuestas'] > count($muro['submuros']))
        {
            $html .= '<input type="hidden" id="'.$prefix.'_more_answers-'.$muro['id'].'" name="'.$prefix.'_more_answers-'.$muro['id'].'" value="0">
                      <a href="#" class="btn btn-primary btn-sm  text-center mx-auto more_answers" data="'.$muro['id'].'">'.$this->translator->trans('Ver más respuestas').'</a>';
        }

        $html .= '</div>
                </div>';

        return $html;

  }

  public function drawResponses($submuro, $prefix)
  {

    $uploads = $this->container->getParameter('folders')['uploads'];
    $img_user = $submuro['foto'] ? $uploads.$submuro['foto'] : $this->getWebDirectory().'/front/assets/img/user-default.png';
        $like_class = $submuro['likes']['ilike'] ? 'ic-lke-act' : '';
        $html = '<div class="comment replied">
                    <div class="comm-header d-flex align-items-center mb-2">
                        <img class="img-fluid avatar-img" src="'.$img_user.'" alt="">
                        <div class="wrap-info-user flex-column ml-2">
                            <div class="name text-xs color-dark-grey">'.$submuro['usuario'].'</div>
                            <div class="date text-xs color-grey">'.$submuro['cuando'].'</div>
                        </div>
                    </div>
                    <div class="comm-body">
                        <p>'.$submuro['mensaje'].'</p>
                    </div>
                    <div class="comm-footer d-flex justify-content-between align-items-center">
                        <a href="#" class="mr-0 text-sm color-light-grey like" data="'.$submuro['id'].'">

                        </a>
                    </div>
                </div>';

        return $html;

  }

  public function newAlarm($tipo_alarma_id, $descripcion, $usuario, $entidad_id, $fecha = 0)
  {

    $em = $this->em;

    $fecha = !$fecha ? new \DateTime('now') : $fecha;
    $tipo_alarma = $em->getRepository('LinkComunBundle:AdminTipoAlarma')->find($tipo_alarma_id);

    $alarma = new AdminAlarma();
    $alarma->setTipoAlarma($tipo_alarma);
    $alarma->setDescripcion($descripcion);
    $alarma->setUsuario($usuario);
    $alarma->setEntidadId($entidad_id);
    $alarma->setLeido(false);
    $alarma->setFechaCreacion($fecha);
    $em->persist($alarma);
        $em->flush();

  }

  public function iniciarSesion($datos)
    {

        $exito = false;
        $error = '';
        $usuario = 0;

    $em = $this->em;

    $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                               WHERE LOWER(u.login) = :login
                              AND u.clave = :clave
                              AND u.empresa =:empresa
                              ORDER BY u.id ASC'
        )
            ->setParameters(array(
                'login' => strtolower($datos['login']),
                'clave' => $datos['clave'],
                'empresa' => $datos['empresa']['id'],
            ));
        $usuarios = $query->getResult();

        if ($usuarios)
        {
          $usuario = $usuarios[0];
        }

        if (!$usuario)//validamos que el usuario exista
        {
            $error = $this->translator->trans('Usuario o clave incorrecta.');
        }
        else {
            if (!$usuario->getActivo()) //validamos que el usuario este activo
            {
                $error = $this->translator->trans('Usuario inactivo. Contacte al administrador del sistema.');
            }
            else {
                if (!$usuario->getEmpresa())
                {
                    $error = $this->translator->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
                }
                else {
                    if ($usuario->getEmpresa()->getId() != $datos['empresa']['id']) //validamos que el usuario pertenezca a la empresa
                    {
                        $error = $this->translator->trans('El Usuario no pertenece a la empresa. Contacte al administrador del sistema.');
                    }
                    else {

                        if (!$usuario->getEmpresa()->getActivo())
                        {
                            $error = $this->translator->trans('La empresa está inactiva').'.';
                        }
                        else {

                            $roles_front = array();
                            $roles_front[] = $datos['yml']['rol']['participante'];
                            $roles_front[] = $datos['yml']['rol']['tutor'];
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
                                if ($rol_usuario->getRol()->getId() == $datos['yml']['rol']['participante'])
                                {
                                    $participante = true;
                                }
                                if ($rol_usuario->getRol()->getId() == $datos['yml']['rol']['tutor'])
                                {
                                    $tutor = true;
                                }
                            }

                            if (!$roles_ok)
                            {
                                $error = $this->translator->trans('Los roles que tiene el usuario no son permitidos para ingresar al sistema.');
                            }
                            else {

                                // Si tiene sessiones activas por más de 60 min, se cierra automáticamente.
                                $sesiones_activas = $em->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                                                    'disponible' => true));
                                $is_active = false;
                                foreach ($sesiones_activas as $sesion_activa)
                                {
                                    $timeFirst  = strtotime($sesion_activa->getFechaRequest()->format('Y-m-d H:i:s'));
                                    $timeSecond = strtotime(date('Y-m-d H:i:s'));
                                    $differenceInSeconds = $timeSecond - $timeFirst;
                                    $differenceInMinutes = number_format($differenceInSeconds/60, 0);
                                    if ($differenceInMinutes < 5)
                                    {
                                        $is_active = true;
                                    }
                                    else {
                                        $sesion_activa->setDisponible(false);
                                        $em->persist($sesion_activa);
                                        $em->flush();
                                    }
                                }

                                if ($is_active)
                                {
                                    $error = $this->translator->trans('Este usuario tiene una sesión activa. Espera 5 minutos e intenta ingresar de nuevo.');
                                }
                                else {

                                  // se consulta si la empresa tiene paginas activas
                                  $query = $em->getConnection()->prepare('SELECT fnpaginas_login
                                                            (:ppempresa_id,
                                                             :ppnivel_id,
                                                             :ppfecha) as resultado;');
                                  $query->bindValue(':ppempresa_id',$datos['empresa']['id'], \PDO::PARAM_INT);
                                  $query->bindValue(':ppnivel_id',$usuario->getNivel()->getId(), \PDO::PARAM_INT);
                                  $query->bindValue(':ppfecha',date('Y-m-d'), \PDO::PARAM_STR);
                                  $query->execute();
                                  $gc = $query->fetchAll();
                                  $paginas = json_decode($gc[0]['resultado'],true);

                                    if (!$paginas)  //validamos que la empresa tenga paginas activas
                                    {
                                        $error = $this->translator->trans('No hay Programas disponibles para la empresa. Contacte al administrador del sistema.');
                                    }
                                    else {

                                        // Se setea los datos del usuario
                                        $datosUsuario = array('id' => $usuario->getId(),
                                                              'login' => $usuario->getLogin(),
                                                              'nombre' => $usuario->getNombre(),
                                                              'apellido' => $usuario->getApellido(),
                                                              'correo' => trim($usuario->getCorreoPersonal()) != '' ? trim($usuario->getCorreoPersonal()) : trim($usuario->getCorreoCorporativo()),
                                                              'correo_corporativo' => trim($usuario->getCorreoCorporativo()),
                                                              'fecha_nacimiento' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('Y-m-d') : '',
                                                              'fecha_nacimiento_formateada' => $usuario->getFechaNacimiento() ? $usuario->getFechaNacimiento()->format('d/m/Y') : '',
                                                              'foto' => $usuario->getFoto(),
                                                              'participante' => $participante,
                                                              'tutor' => $tutor);
                                        // Cierre de sesiones activas
                                        $sesiones = $em->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                                                    'disponible' => true));
                                        foreach ($sesiones as $s)
                                        {
                                            $s->setDisponible(false);
                                            $em->persist($s);
                                            $em->flush();
                                        }

                                        // Se crea la sesión en BD
                                        $admin_sesion = new AdminSesion();
                                        $admin_sesion->setFechaIngreso(new \DateTime('now'));
                                        $admin_sesion->setFechaRequest(new \DateTime('now'));
                                        $admin_sesion->setUsuario($usuario);
                                        $admin_sesion->setDisponible(true);
                                        $admin_sesion->setDispositivo($datos['dispositivo']);
                                        $admin_sesion->setUbicacion($datos['ubicacion']);
                                        $em->persist($admin_sesion);
                                        $em->flush();

                                        $session = new session();
                                        $session->set('iniFront', true);
                                        $session->set('sesion_id', $admin_sesion->getId());
                                        $session->set('code', $datos['yml']['search_locale'] ? $this->getLocaleCode() : 'VE');
                                        $session->set('usuario', $datosUsuario);
                                        $session->set('empresa', $datos['empresa']);
                                        $session->set('paginas', $paginas);

                                        if ($datos['recordar_datos'] == 1)
                                        {
                                            //alimentamos el generador de aleatorios
                                            mt_srand (time());
                                            //generamos un número aleatorio para la cookie
                                            $numero_aleatorio = mt_rand(1000000,999999999);
                                            //se guarda la cookie en la tabla admin_usuario
                                            $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($session->get('usuario')['id']);
                                            //hay que validar si el usuario hace la marca de recordar
                                            $usuario->setCookies($numero_aleatorio);
                                            $em->persist($usuario);
                                            $em->flush();
                                            //se creo la variable de las cookie con el id del usuario de manera que cuando destruya la cookie sea la del usuario activo
                                            setcookie("id_usuario", $usuario->getId(), time()+(60*60*24*365),'/');
                                            setcookie("marca_aleatoria_usuario", $numero_aleatorio, time()+(60*60*24*365),'/');
                                        }

                                        $exito = true;

                                    }
                                }
                            }

                        }

                    }
                }

            }
        }

        return array("error" => $error,
               "exito" => $exito);

    }

  public function notasPrograma($subpaginas_ids, $usuario_id, $estatus_aprobado)
  {

    $em = $this->em;
    $subpaginas = array();

    foreach ($subpaginas_ids as $subpage)
    {

      $cantidad_intentos = 0;
      $nota = 0;

      $query = $em->createQuery('SELECT pl.nota as nota FROM LinkComunBundle:CertiPruebaLog pl
                                     JOIN pl.prueba p
                                     WHERE p.pagina = :pagina
                                     and pl.estado = :estado
                                     and pl.usuario = :usuario')
                      ->setParameters(array('usuario' => $usuario_id,
                                'pagina' => $subpage,
                                            'estado' => $estatus_aprobado))
                      ->setMaxResults(1);
          $nota_programa = $query->getResult();

          if ($nota_programa)
          {

        foreach ($nota_programa as $n)
        {
          $nota = $n['nota'];
        }

        $query = $em->createQuery('SELECT count(pl.id) FROM LinkComunBundle:CertiPruebaLog pl
                                       JOIN pl.prueba p
                                       WHERE p.pagina = :pagina
                                       and pl.usuario = :usuario')
                        ->setParameters(array('usuario' => $usuario_id,
                                  'pagina' => $subpage));
            $cantidad_intentos = $query->getSingleScalarResult();
      }
          $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($subpage);

      if($nota > 0)
      {
            $subpaginas[$subpage] = array('id' => $subpage,
                            'nombre' => $pagina->getNombre(),
                            'categoria' => $pagina->getCategoria()->getId(),
                            'nota' => $nota,
                                  'cantidad_intentos' => $cantidad_intentos ? $cantidad_intentos : '');
        }

    }

    return $subpaginas;

  }

  public function iniciarSesionAdmin($datos)
    {

        $exito = false;
        $error = '';
        $usuario = 0;

    $em = $this->em;

    $query = $em->createQuery('SELECT u FROM LinkComunBundle:AdminUsuario u
                  WHERE LOWER(u.login) = :login AND u.clave = :clave')
                    ->setParameters(array('login' => strtolower($datos['login']),
                                'clave' => $datos['clave']));
        $usuarios = $query->getResult();

        if ($usuarios)
        {
          $usuario = $usuarios[0];
        }

    if (!$usuario)
        {
          $error = $this->translator->trans('Usuario o clave incorrecta.');
        }
        else {

          // Si tiene sessiones activas por más de 60 min, se cierra automáticamente.
          $sesiones_activas = $em->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                    'disponible' => true));
          $is_active = false;
          foreach ($sesiones_activas as $sesion_activa)
          {
            $timeFirst  = strtotime($sesion_activa->getFechaRequest()->format('Y-m-d H:i:s'));
        $timeSecond = strtotime(date('Y-m-d H:i:s'));
        $differenceInSeconds = $timeSecond - $timeFirst;
        $differenceInMinutes = number_format($differenceInSeconds/60, 0);
        if ($differenceInMinutes < 5)
        {
          $is_active = true;
        }
        else {
          $sesion_activa->setDisponible(false);
          $em->persist($sesion_activa);
                    $em->flush();
        }
          }

            if (!$usuario->getActivo())
            {
                $error = $this->translator->trans('Usuario inactivo. Contacte al administrador del sistema.');
            }
            else if ($is_active)
            {
                $error = $this->translator->trans('Este usuario tiene una sesión activa. Espera 5 minutos e intenta ingresar de nuevo.');
            }
            else {

                // Se verifica si el usuario pertenece a una empresa activa
                $empresa_activa = 1;
                if ($usuario->getEmpresa())
                {
                    if (!$usuario->getEmpresa()->getActivo())
                    {
                        $empresa_activa = 0;
                    }
                }

                if (!$empresa_activa)
                {
                    $error = $this->translator->trans('La empresa a la que pertenece este usuario está inactiva.');
                }
                else {

                    $roles_bk = array();
                    $roles_usuario = array();
                    $roles_bk[] = $datos['yml']['rol']['administrador'];
                    $roles_bk[] = $datos['yml']['rol']['empresa'];
                    $roles_bk[] = $datos['yml']['rol']['tutor'];
                    $roles_ok = 0;
                    $administrador = false;

                    $query = $em->createQuery('SELECT ru FROM LinkComunBundle:AdminRolUsuario ru WHERE ru.usuario = :usuario_id')
                                ->setParameter('usuario_id', $usuario->getId());
                    $roles_usuario_db = $query->getResult();

                    foreach ($roles_usuario_db as $rol_usuario)
                    {

                        // Verifico si el rol está dentro de los roles de backend
                        if ($rol_usuario->getRol()->getBackend())
                        {
                            $roles_ok = 1;
                        }

                        if ($rol_usuario->getRol()->getId() == $datos['yml']['rol']['administrador'])
                        {
                            $administrador = true;
                        }

                        $roles_usuario[] = $rol_usuario->getRol()->getId();

                    }

                    if (!$roles_ok)
                    {
                        $error = $this->translator->trans('Los roles que tiene el usuario no son permitidos para ingresar a la aplicación.');
                    }
                    else {

                        $query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a
                                             WHERE p.rol IN (:roles) AND a.activo = :activo AND a.aplicacion IS NULL')
                                ->setParameters(array('roles' => $roles_usuario,
                                                  'activo' => true));

                        if (!$query->getSingleScalarResult())
                        {
                            $error = $this->translator->trans('Usted no tiene aplicaciones asignadas para su rol.');
                        }
                        else {

                            // Se setea la sesion y se prepara el menu
                            $datosUsuario = array('id'       => $usuario->getId(),
                                                  'nombre'   => $usuario->getNombre(),
                                                  'apellido' => $usuario->getApellido(),
                                                  'correo'   => $usuario->getCorreoPersonal(),
                                                  'foto'     => $usuario->getFoto(),
                                                  'roles'    => $roles_usuario,
                                                  'empresa'  => ($usuario->getEmpresa())? $usuario->getEmpresa()->getId(): false
                                                );

                            // Opciones del menu
                            $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a
                                                        WHERE p.rol IN (:rol_id)
                                                        AND a.activo = :activo
                                                        AND a.aplicacion IS NULL
                                                        ORDER BY a.orden ASC")
                                        ->setParameters(array('rol_id' => $roles_usuario,
                                                              'activo' => true));
                            $permisos = $query->getResult();

                            $permisos_id = array();
                            $menu = array();

                            foreach ($permisos as $permiso)
                            {

                                if (!in_array($permiso->getId(), $permisos_id))
                                {

                                    $permisos_id[] = $permiso->getId();

                                    $submenu = array();

                                    $query = $em->createQuery("SELECT p FROM LinkComunBundle:AdminPermiso p JOIN p.aplicacion a
                                                                WHERE p.rol IN (:rol_id)
                                                                AND a.activo = :activo
                                                                AND a.aplicacion = :app_id
                                                                ORDER BY a.orden ASC")
                                                ->setParameters(array('rol_id' => $roles_usuario,
                                                                      'activo' => true,
                                                                      'app_id' => $permiso->getAplicacion()->getId()));
                                    $subpermisos = $query->getResult();

                                    foreach ($subpermisos as $subpermiso)
                                    {

                                        if (!in_array($subpermiso->getId(), $permisos_id))
                                        {

                                            $permisos_id[] = $subpermiso->getId();

                                            $submenu[] = array('id' => $subpermiso->getAplicacion()->getId(),
                                                               'url' => $subpermiso->getAplicacion()->getUrl(),
                                                               'nombre' => $subpermiso->getAplicacion()->getNombre(),
                                                               'icono' => $subpermiso->getAplicacion()->getIcono(),
                                                               'url_existente' => $subpermiso->getAplicacion()->getUrl() ? 1 : 0);
                                        }
                                    }

                                    $menu[] = array('id' => $permiso->getAplicacion()->getId(),
                                                    'url' => $permiso->getAplicacion()->getUrl(),
                                                    'nombre' => $permiso->getAplicacion()->getNombre(),
                                                    'icono' => $permiso->getAplicacion()->getIcono(),
                                                    'url_existente' => $permiso->getAplicacion()->getUrl() ? 1 : 0,
                                                    'submenu' => $submenu);
                                }
                            }

                            // Cierre de sesiones activas
                            $sesiones = $em->getRepository('LinkComunBundle:AdminSesion')->findBy(array('usuario' => $usuario->getId(),
                                                                                                                         'disponible' => true));
                            foreach ($sesiones as $s)
                            {
                                $s->setDisponible(false);
                            }

                            // Se crea la sesión en BD
                            $admin_sesion = new AdminSesion();
                            $admin_sesion->setFechaIngreso(new \DateTime('now'));
                            $admin_sesion->setFechaRequest(new \DateTime('now'));
                            $admin_sesion->setUsuario($usuario);
                            $admin_sesion->setDisponible(true);
                            $em->persist($admin_sesion);
                            $em->flush();

              $session = new session();
                            $session->set('ini', true);
                            $session->set('sesion_id', $admin_sesion->getId());
                            $session->set('code', $datos['yml']['search_locale'] ? $this->getLocaleCode() : 'VE');
                            $session->set('administrador', $administrador);
                            $session->set('usuario', $datosUsuario);
                            $session->set('menu', $menu);

                            if($datos['recordar_datos'] == 1)
                            {
                                //alimentamos el generador de aleatorios
                                mt_srand (time());
                                //generamos un número aleatorio para la cookie
                                $numero_aleatorio = mt_rand(1000000,999999999);
                                //se guarda la cookie en la tabla admin_usuario
                                $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneById($session->get('usuario')['id']);
                                //hay que validar si el usuario hace la marca de recordar
                                $usuario->setCookies($numero_aleatorio);
                                $em->persist($usuario);
                                $em->flush();
                                //se creo la variable de las cookie con el id del usuario de manera que cuando destruya la cookie sea la del usuario activo
                                setcookie("id_usuario", $usuario->getId(), time()+(60*60*24*365),'/');
                                setcookie("marca_aleatoria_usuario", $numero_aleatorio, time()+(60*60*24*365),'/');
                            }
              $exito = true;
                        }
                    }
                }
            }
        }

        return array("error" => $error,
               "exito" => $exito);

    }

    // Calcula la diferencia de tiempo entre fecha_ini y fecha_venc
  // Retorna la cantidad de días
  public function timeAgoPorcentaje($fecha_ini, $fecha_venc)
  {

    $fin = new \DateTime($fecha_venc);
    $inicio = new \DateTime($fecha_ini);
    $hoy = new \DateTime("now");
    $interval_complete = $fin->diff($inicio);
    $interval_available = $fin->diff($hoy);
    $complete_days = $interval_complete->format('%a');
    $available_days = $interval_available->format('%a');

    $complete_days = (int) $complete_days;
    $available_days = (int) $available_days;

    $porcentaje = ($available_days * 100) / $complete_days;

    return $porcentaje;

  }

  // Arreglo de comentarios en el espacio colaborativo y sus respuestas
  public function forosHijos($foro_id, $offset, $limit, $usuario, $social_colaborativo)
  {

    $em = $this->em;
    $qb = $em->createQueryBuilder();
        $qb->select('f')
           ->from('LinkComunBundle:CertiForo', 'f')
           ->andWhere('f.foro = :foro_id')
           ->orderBy('f.fechaRegistro', 'ASC')
           ->setFirstResult($offset)
           ->setMaxResults($limit)
           ->setParameter('foro_id', $foro_id);
        $query = $qb->getQuery();
        $foros = $query->getResult();

        $foros_hijos = array();

        foreach ($foros as $foro_hijo)
        {

            $foros_nietos = array();
            $foros_nietos_bd = $em->getRepository('LinkComunBundle:CertiForo')->findBy(array('foro' => $foro_hijo->getId()),
                                                                                       array('fechaRegistro' => 'ASC'));
            foreach ($foros_nietos_bd as $foro_nieto)
            {
                $autor_nieto = $foro_nieto->getUsuario()->getId() == $usuario['id'] ? $this->translator->trans('Yo') : $foro_nieto->getUsuario()->getNombre().' '.$foro_nieto->getUsuario()->getApellido();
                $delete_link = $foro_nieto->getUsuario()->getId() != $usuario['id'] ? $usuario['tutor'] ? 1 : 0 : 1;
                $foros_nietos[] = array('id' => $foro_nieto->getId(),
                                        'usuario' => $autor_nieto,
                                        'foto' => $foro_nieto->getUsuario()->getFoto(),
                                        'timeAgo' => $this->sinceTime($foro_nieto->getFechaRegistro()->format('Y-m-d H:i:s')),
                                        'mensaje' => $foro_nieto->getMensaje(),
                                        'likes' => $this->likes($social_colaborativo, $foro_nieto->getId(), $usuario['id']),
                                        'delete_link' => $delete_link);
            }
            $autor = $foro_hijo->getUsuario()->getId() == $usuario['id'] ? $this->translator->trans('Yo') : $foro_hijo->getUsuario()->getNombre().' '.$foro_hijo->getUsuario()->getApellido();
            $delete_link = $foro_hijo->getUsuario()->getId() != $usuario['id'] ? $usuario['tutor'] ? 1 : 0 : 1;
            $foros_hijos[] = array('id' => $foro_hijo->getId(),
                                   'usuario' => $autor,
                                   'foto' => $foro_hijo->getUsuario()->getFoto(),
                                   'timeAgo' => $this->sinceTime($foro_hijo->getFechaRegistro()->format('Y-m-d H:i:s')),
                                   'mensaje' => $foro_hijo->getMensaje(),
                                   'likes' => $this->likes($social_colaborativo, $foro_hijo->getId(), $usuario['id']),
                                   'delete_link' => $delete_link,
                                   'respuestas' => $foros_nietos);

        }

        return $foros_hijos;

  }

  // Arreglo del archivo en el espacio colaborativo
  public function archivoForo($archivo, $usuario_id)
  {

    $extension = strtolower(substr($archivo->getArchivo(), strrpos($archivo->getArchivo(), ".")+1));

    $doc_extensions = array('doc', 'docx');
    $img_extensions = array('png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'svg');
    $excel_extensions = array('xls', 'xlsx');

    if (in_array($extension, $doc_extensions))
    {
      $img = $this->getWebDirectory().'/front/assets/img/doc.svg';
    }
    elseif (in_array($extension, $img_extensions))
    {
      $img = $this->getWebDirectory().'/front/assets/img/jpg.svg';
    }
    elseif (in_array($extension, $excel_extensions))
    {
      $img = $this->getWebDirectory().'/front/assets/img/xls.svg';
    }
    elseif ($extension == 'pdf')
    {
      $img = $this->getWebDirectory().'/front/assets/img/pdf.svg';
    }
    else {
      $img = $this->getWebDirectory().'/front/assets/img/jpg.svg';
    }

    $archivo_arr = array('id' => $archivo->getId(),
               'descripcion' => $archivo->getDescripcion(),
               'usuario' => $archivo->getUsuario()->getId() == $usuario_id ? $this->translator->trans('Yo') : $archivo->getUsuario()->getNombre().' '.$archivo->getUsuario()->getApellido(),
               'archivo' => $archivo->getArchivo(),
               'img' => $img);

        return $archivo_arr;

  }

  /*function delete_folder($dir) {
    if (is_dir($dir))
    {
        $objects = opendir($dir);
        foreach ($objects as $object)
        {
            if ($object != "." && $object != "..")
            {
              if (filetype($dir."/".$object) == "dir")
              {
                $this->delete_folder($dir."/".$object);
              }
              else {
                unlink($dir."/".$object);
              }
            }
        }
        reset($objects);
        rmdir($dir);
      }
  }*/

  function delete_folder($directory, $delete_parent = null)
    {
      $files = glob($directory . '/{,.}[!.,!..]*',GLOB_MARK|GLOB_BRACE);
      foreach ($files as $file)
      {
          if (is_dir($file))
          {
            $this->delete_folder($file, 1);
          }
          else {
            unlink($file);
          }
      }
      if ($delete_parent)
      {
          rmdir($directory);
      }
    }

  public function nextLesson($indexedPages, $pagina_id, $usuario_id, $empresa_id, $yml, $programa_id)
  {

    $em = $this->em;

    $next_lesson = 0;
    $evaluacion = 0;
    $duracion = 0;
    $nombre_pagina = $indexedPages[$pagina_id]['categoria'].': '.$indexedPages[$pagina_id]['nombre'];
    $categoria = $indexedPages[$pagina_id]['categoria'];
    $pagina_padre_id = 0;
    $continue_button = array('next_lesson' => $next_lesson,
                 'evaluacion' => $evaluacion,
                 'duracion' => $duracion,
                 'nombre_pagina' => $nombre_pagina,
                 'categoria' => $categoria,
                 'pagina_padre_id' => $pagina_padre_id);

        $pl = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                    'pagina' => $pagina_id));

        if ($indexedPages[$pagina_id]['tiene_evaluacion'] && $pl->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
        {
          $evaluacion = $this->evaluacionPagina($pagina_id, $usuario_id, $empresa_id, $yml);
          if ($evaluacion)
          {
            $duracion = $this->duracionPrueba($evaluacion);
            $nombre_pagina = $indexedPages[$evaluacion]['categoria'].': '.$indexedPages[$evaluacion]['nombre'];
        $categoria = $indexedPages[$evaluacion]['categoria'];
            if ($indexedPages[$evaluacion]['padre'])
            {
              $pagina_padre_id = $indexedPages[$evaluacion]['padre'];
            }
          }
          $continue_button = array('next_lesson' => $next_lesson,
                   'evaluacion' => $evaluacion,
                   'duracion' => $duracion,
                   'nombre_pagina' => $nombre_pagina,
                   'categoria' => $categoria,
                   'pagina_padre_id' => $pagina_padre_id);
        }

        if ($indexedPages[$pagina_id]['padre'] && !$evaluacion)
        {

            $pagina_padre_id = $indexedPages[$pagina_id]['padre'];
            $keys = array_keys($indexedPages[$pagina_padre_id]['subpaginas']);

            if (isset($keys[array_search($pagina_id,$keys)+1]))
            {
              // Próxima lección es hermana
                $next_lesson = $keys[array_search($pagina_id,$keys)+1];
                $nombre_pagina = $indexedPages[$next_lesson]['categoria'].': '.$indexedPages[$next_lesson]['nombre'];
                $categoria = $indexedPages[$next_lesson]['categoria'];
            }
            else {

                // Buscar la próxima página hermana que no haya sido completada
                foreach ($indexedPages[$pagina_padre_id]['subpaginas'] as $subpagina)
                {
                    $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                                        'pagina' => $subpagina['id']));
                    if (!$pagina_log)
                    {
                        $next_lesson = $subpagina['id'];
                        $nombre_pagina = $indexedPages[$next_lesson]['categoria'].': '.$indexedPages[$next_lesson]['nombre'];
                        $categoria = $indexedPages[$next_lesson]['categoria'];
                        break;
                    }
                    else {
                        if ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['iniciada'])
                        {
                            $next_lesson = $subpagina['id'];
                            $nombre_pagina = $indexedPages[$next_lesson]['categoria'].': '.$indexedPages[$next_lesson]['nombre'];
                            $categoria = $indexedPages[$next_lesson]['categoria'];
                            break;
                        }
                        elseif ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                        {
                            $evaluacion = $subpagina['id'];
                            $duracion = $this->duracionPrueba($evaluacion);
                            $nombre_pagina = $indexedPages[$evaluacion]['categoria'].': '.$indexedPages[$evaluacion]['nombre'];
                            $categoria = $indexedPages[$evaluacion]['categoria'];
                            break;
                        }
                    }
                }

            }

            if ($next_lesson || $evaluacion)
            {
              if ($evaluacion)
            {
              $duracion = $this->duracionPrueba($evaluacion);
              if ($indexedPages[$evaluacion]['padre'])
              {
                $pagina_padre_id = $indexedPages[$evaluacion]['padre'];
              }
            }
              $continue_button = array('next_lesson' => $next_lesson,
                     'evaluacion' => $evaluacion,
                     'duracion' => $duracion,
                     'nombre_pagina' => $nombre_pagina,
                     'categoria' => $categoria,
                     'pagina_padre_id' => $pagina_padre_id);
            }
            else {
              if ($pagina_padre_id != $programa_id)
              {
                // nextLesson desde el punto de vista del padre
                $continue_button = $this->nextLesson($indexedPages, $pagina_padre_id, $usuario_id, $empresa_id, $yml, $programa_id);
              }
            }

        }

        return $continue_button;

  }

  // Retorna 0 si la página no tiene evaluación que presentar, sino retorna la pagina_id de la evaluación
  public function evaluacionPagina($pagina_id, $usuario_id, $empresa_id, $yml)
  {

    $em = $this->em;
    $pagina_evaluacion_id = 0;

    $query = $em->createQuery("SELECT pl FROM LinkComunBundle:CertiPruebaLog pl
                                    JOIN pl.prueba p
                                    WHERE pl.usuario = :usuario_id
                                    AND p.pagina = :pagina_id
                                    ORDER BY pl.id DESC")
                    ->setParameters(array('usuario_id' => $usuario_id,
                                          'pagina_id' => $pagina_id));
        $pruebas_log = $query->getResult();
        if ($pruebas_log)
        {
            switch ($pruebas_log[0]->getEstado())
            {
                case $yml['parameters']['estado_prueba']['curso']:
                    $pagina_evaluacion_id = $pagina_id;
                    break;
                case $yml['parameters']['estado_prueba']['aprobado']:
                    $pagina_evaluacion_id = 0;
                    break;
                case $yml['parameters']['estado_prueba']['reprobado']:
                    // Cantidad de intentos
                    $query = $em->createQuery("SELECT COUNT(pl.id) FROM LinkComunBundle:CertiPruebaLog pl
                                                JOIN pl.prueba p
                                                WHERE pl.usuario = :usuario_id
                                                AND p.pagina = :pagina_id")
                                ->setParameters(array('usuario_id' => $usuario_id,
                                                      'pagina_id' => $pagina_id));
                    $intentos = $query->getSingleScalarResult();
                    $query = $em->createQuery("SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                                WHERE pe.empresa = :empresa_id
                                                AND pe.pagina = :pagina_id")
                                ->setParameters(array('empresa_id' => $empresa_id,
                                                      'pagina_id' => $pagina_id));
                    $pe = $query->getResult();
                    $max_intentos = $pe[0]->getMaxIntentos();
                    if ($intentos < $max_intentos)
                    {
                        $pagina_evaluacion_id = $pagina_id;
                    }
                    break;
            }
        }
        else {
            // Se verifica si la página tiene creada una evaluación
            $query = $em->createQuery("SELECT COUNT(p.id) FROM LinkComunBundle:CertiPrueba p
                                        WHERE p.pagina = :pagina_id
                                        AND p.estatusContenido = :activo")
                        ->setParameters(array('activo' => $yml['parameters']['estatus_contenido']['activo'],
                                              'pagina_id' => $pagina_id));
            $evaluaciones = $query->getSingleScalarResult();
            if ($evaluaciones)
            {
                $pagina_evaluacion_id = $pagina_id;
            }
        }

        return $pagina_evaluacion_id;

  }

  // Crea o actualiza asignaciones de sub-páginas con los mismos valores de la página padre
  public function reordenarSubAsignaciones($pagina_empresa)
  {

    $em = $this->em;
    $orden = 0;
    $paginas_ordenadas = array();

    $subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina' => $pagina_empresa->getPagina()->getId()),
                                        array('orden' => 'ASC'));

    foreach ($subpages as $subpage)
    {

      $subpagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $subpage->getId(),
                                                                                                         'empresa' => $pagina_empresa->getEmpresa()->getId()));

      if ($subpagina_empresa)
            {

              $orden++;
              $subpagina_empresa->setOrden($orden);
              $em->persist($subpagina_empresa);
              $em->flush();

              $paginas_ordenadas[] = array('orden' => $orden,
                                           'pagina_id' => $subpage->getId(),
                                           'pagina_empresa_id' => $subpagina_empresa->getId(),
                                           'nombre' => $subpage->getCategoria()->getNombre().' '.$subpage->getNombre(),
                                           'subpaginas' => $this->reordenarSubAsignaciones($subpagina_empresa));

            }

    }

    return $paginas_ordenadas;

  }

  // Retorna 0 si la pagina_id ha sido asignada a alguna empresa y no se puede mover
  public function paginaMovible($pagina_id)
  {

    $em = $this->em;

    $query = $em->createQuery('SELECT COUNT(pe.id) FROM LinkComunBundle:CertiPaginaEmpresa pe
                                WHERE pe.pagina = :pagina_id')
                ->setParameter('pagina_id', $pagina_id);

    return $query->getSingleScalarResult();

  }

  // Retorna el id de la página padre de todas
  public function paginaRaiz($pagina)
  {

    if ($pagina->getPagina())
    {
      return $this->paginaRaiz($pagina->getPagina());
    }
    else {
      return $pagina->getId();
    }

  }

  public function sesionBloqueda($sesion_id)
  {

    $em = $this->em;
    $sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($sesion_id);

    return !$sesion->getDisponible();

  }

  // Retorna el tamaño de un archivo entendible
  function fileSizeConvert($bytes)
  {

      $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            )
        );

      foreach($arBytes as $arItem)
      {
          if($bytes >= $arItem["VALUE"])
          {
              $result = $bytes / $arItem["VALUE"];
              $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
              break;
          }
      }

      return $result;

  }

  // Duplicación de una página
  public function duplicarPagina($pagina_id, $nombre, $usuario_id , $evaluacion)
  {

    $em = $this->em;
    $c = 0;

    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
    $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_id);

    // Orden para el nuevo registro
    if ($pagina->getPagina())
    {
      $query = $em->createQuery('SELECT MAX(p.orden) FROM LinkComunBundle:CertiPagina p
                                      WHERE p.pagina = :pagina_id')
            ->setParameter('pagina_id', $pagina->getPagina()->getId());
    }
    else {
      $query = $em->createQuery('SELECT MAX(p.orden) FROM LinkComunBundle:CertiPagina p
                                      WHERE p.pagina IS NULL');
    }
        $orden = $query->getSingleScalarResult();
        $orden++;

        $new_pagina = new CertiPagina();
        $new_pagina->setNombre($nombre);
        $new_pagina->setPagina($pagina->getPagina());
        $new_pagina->setCategoria($pagina->getCategoria());
        $new_pagina->setDescripcion($pagina->getDescripcion());
        $new_pagina->setContenido($pagina->getContenido());
        $new_pagina->setFoto($pagina->getFoto());
        $new_pagina->setPdf($pagina->getPdf());
        $new_pagina->setFechaCreacion(new \DateTime('now'));
        $new_pagina->setFechaModificacion(new \DateTime('now'));
        $new_pagina->setEstatusContenido($pagina->getEstatusContenido());
        $new_pagina->setUsuario($usuario);
        $new_pagina->setOrden($orden);
        $new_pagina->setEncuesta($pagina->getEncuesta());
        $new_pagina->setHorasAcademicas($pagina->getHorasAcademicas());
        $em->persist($new_pagina);
        $em->flush();
        $c++;

        // Duplicación de la prueba
        if($evaluacion == 1){
           $c += $this->duplicarPrueba($pagina_id, $new_pagina->getId(), $usuario_id);
        }
        // Duplicar sub-páginas
        $c += $this->duplicarSubPaginas($pagina_id, $new_pagina->getId(), $usuario_id, $evaluacion);

        return array('inserts' => $c,
               'id' => $new_pagina->getId());

  }

  // Duplicación de sub-páginas dada la página
  public function duplicarSubPaginas($pagina_id, $pagina_padre_id, $usuario_id, $evaluacion )
  {

    $em = $this->em;
    $c = 0;

    $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

    $paginas = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina' => $pagina_id),
                                                                             array('orden' => 'ASC'));

    foreach ($paginas as $pagina)
    {

      $pagina_padre = $em->getRepository('LinkComunBundle:CertiPagina')->find($pagina_padre_id);

      $query = $em->createQuery('SELECT MAX(p.orden) FROM LinkComunBundle:CertiPagina p
                                      WHERE p.pagina = :pagina_id')
            ->setParameter('pagina_id', $pagina_padre_id);
      $orden = $query->getSingleScalarResult();
          $orden++;

          $new_pagina = new CertiPagina();
          $new_pagina->setNombre($pagina->getNombre());
          $new_pagina->setPagina($pagina_padre);
          $new_pagina->setCategoria($pagina->getCategoria());
          $new_pagina->setDescripcion($pagina->getDescripcion());
          $new_pagina->setContenido($pagina->getContenido());
          $new_pagina->setFoto($pagina->getFoto());
          $new_pagina->setPdf($pagina->getPdf());
          $new_pagina->setFechaCreacion(new \DateTime('now'));
          $new_pagina->setFechaModificacion(new \DateTime('now'));
          $new_pagina->setEstatusContenido($pagina->getEstatusContenido());
          $new_pagina->setUsuario($usuario);
          $new_pagina->setOrden($orden);
          $new_pagina->setEncuesta($pagina->getEncuesta());
          $new_pagina->setHorasAcademicas($pagina->getHorasAcademicas());
          $em->persist($new_pagina);
          $em->flush();
          $c++;

          // Duplicación de la prueba
          if($evaluacion == 1){
              $c += $this->duplicarPrueba($pagina->getId(), $new_pagina->getId(), $usuario_id);
          }


          // Duplicar sub-páginas
          $c += $this->duplicarSubPaginas($pagina->getId(), $new_pagina->getId(), $usuario_id, $evaluacion);

    }

        return $c;

  }

  // Duplicación de la evaluación de una página
  public function duplicarPrueba($pagina_id, $new_pagina_id, $usuario_id)
  {

    $em = $this->em;
    $c = 0; // Cantidad de registros insertados

    $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina_id);

    if ($prueba)
    {

      $new_pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($new_pagina_id);
      $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);

      $new_prueba = new CertiPrueba();
      $new_prueba->setNombre($prueba->getNombre().'(Copia)');
      $new_prueba->setPagina($new_pagina);
      $new_prueba->setCantidadPreguntas($prueba->getCantidadPreguntas());
      $new_prueba->setCantidadMostrar($prueba->getCantidadMostrar());
      $new_prueba->setDuracion($prueba->getDuracion());
      $new_prueba->setUsuario($usuario);
      $new_prueba->setEstatusContenido($prueba->getEstatusContenido());
      $new_prueba->setFechaCreacion(new \DateTime('now'));
      $new_prueba->setFechaModificacion(new \DateTime('now'));
          $em->persist($new_prueba);
          $em->flush();
          $c++;

          // Preguntas
          $par_preguntas = array(); // $par_preguntas[$pregunta_id] = $new_pregunta_id
          $orden = 0;
          $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPregunta p
                                      WHERE p.prueba = :prueba_id
                                      AND p.pregunta IS NULL
                                      ORDER BY p.orden ASC")
                      ->setParameter('prueba_id', $prueba->getId());
          $preguntas = $query->getResult();

          foreach ($preguntas as $pregunta)
          {

            $orden++;
            $new_pregunta = new CertiPregunta();
            $new_pregunta->setEnunciado($pregunta->getEnunciado());
            $new_pregunta->setImagen($pregunta->getImagen());
            $new_pregunta->setPrueba($new_prueba);
            $new_pregunta->setTipoPregunta($pregunta->getTipoPregunta());
            $new_pregunta->setTipoElemento($pregunta->getTipoElemento());
            $new_pregunta->setUsuario($usuario);
            $new_pregunta->setEstatusContenido($pregunta->getEstatusContenido());
            $new_pregunta->setValor($pregunta->getValor());
            $new_pregunta->setOrden($orden);
            $new_pregunta->setFechaCreacion(new \DateTime('now'));
        $new_pregunta->setFechaModificacion(new \DateTime('now'));
            $em->persist($new_pregunta);
            $em->flush();
            $c++;

            $par_preguntas[$pregunta->getId()] = $new_pregunta->getId();

            // Sub-preguntas
            $sub_preguntas = $em->getRepository('LinkComunBundle:CertiPregunta')->findByPregunta($pregunta->getId());
            foreach ($sub_preguntas as $sub_pregunta)
            {

              $new_sub_pregunta = new CertiPregunta();
              $new_sub_pregunta->setEnunciado($sub_pregunta->getEnunciado());
              $new_sub_pregunta->setImagen($sub_pregunta->getImagen());
              $new_sub_pregunta->setPrueba($new_prueba);
              $new_sub_pregunta->setTipoPregunta($sub_pregunta->getTipoPregunta());
              $new_sub_pregunta->setTipoElemento($sub_pregunta->getTipoElemento());
              $new_sub_pregunta->setUsuario($usuario);
              $new_sub_pregunta->setEstatusContenido($sub_pregunta->getEstatusContenido());
              $new_sub_pregunta->setValor($sub_pregunta->getValor());
              $new_sub_pregunta->setPregunta($new_pregunta);
              $new_sub_pregunta->setFechaCreacion(new \DateTime('now'));
          $new_sub_pregunta->setFechaModificacion(new \DateTime('now'));
              $em->persist($new_sub_pregunta);
              $em->flush();
              $c++;

              $par_preguntas[$sub_pregunta->getId()] = $new_sub_pregunta->getId();

            }

          }

          // Opciones
          $par_opciones = array();  // $par_opciones[$opcion_id] = $new_opcion_id
          $opciones = $em->getRepository('LinkComunBundle:CertiOpcion')->findByPrueba($prueba->getId());

          foreach ($opciones as $opcion)
          {

            $new_opcion = new CertiOpcion();
            $new_opcion->setDescripcion($opcion->getDescripcion());
            $new_opcion->setImagen($opcion->getImagen());
            $new_opcion->setPrueba($new_prueba);
            $new_opcion->setUsuario($usuario);
            $new_opcion->setFechaCreacion(new \DateTime('now'));
        $new_opcion->setFechaModificacion(new \DateTime('now'));
        $em->persist($new_opcion);
            $em->flush();
            $c++;

            $par_opciones[$opcion->getId()] = $new_opcion->getId();

          }

          // Preguntas/Opciones y de Asociación
          $query = $em->createQuery("SELECT p FROM LinkComunBundle:CertiPregunta p
                                      WHERE p.prueba = :prueba_id
                                      ORDER BY p.orden ASC")
                      ->setParameter('prueba_id', $prueba->getId());
          $preguntas = $query->getResult();

          foreach ($preguntas as $pregunta)
          {

            $pos = $em->getRepository('LinkComunBundle:CertiPreguntaOpcion')->findByPregunta($pregunta->getId());
            foreach ($pos as $po)
            {

              $new_pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($par_preguntas[$po->getPregunta()->getId()]);
              $new_opcion = $em->getRepository('LinkComunBundle:CertiOpcion')->find($par_opciones[$po->getOpcion()->getId()]);
              $pregunta_opcion = new CertiPreguntaOpcion();
              $pregunta_opcion->setPregunta($new_pregunta);
              $pregunta_opcion->setOpcion($new_opcion);
              $pregunta_opcion->setCorrecta($po->getCorrecta());
              $em->persist($pregunta_opcion);
              $em->flush();
              $c++;
            }

            $pas = $em->getRepository('LinkComunBundle:CertiPreguntaAsociacion')->findByPregunta($pregunta->getId());
            foreach ($pas as $pa)
            {

              $new_pregunta = $em->getRepository('LinkComunBundle:CertiPregunta')->find($par_preguntas[$pa->getPregunta()->getId()]);

              $preguntas_asociadas = explode(",", $pa->getPreguntas());
              $new_preguntas_asociadas = array();
              foreach ($preguntas_asociadas as $pregunta_asociada)
              {
                $new_preguntas_asociadas[] = $par_preguntas[$pregunta_asociada];
              }
              $new_preguntas_asociadas_str = implode(",", $new_preguntas_asociadas);

              $opciones_asociadas = explode(",", $pa->getOpciones());
              $new_opciones_asociadas = array();
              foreach ($opciones_asociadas as $opcion_asociada)
              {
                $new_opciones_asociadas[] = $par_opciones[$opcion_asociada];
              }
              $new_opciones_asociadas_str = implode(",", $new_opciones_asociadas);

              $pregunta_asociacion = new CertiPreguntaAsociacion();
              $pregunta_asociacion->setPregunta($new_pregunta);
              $pregunta_asociacion->setPreguntas($new_preguntas_asociadas_str);
              $pregunta_asociacion->setOpciones($new_opciones_asociadas_str);
              $em->persist($pregunta_asociacion);
              $em->flush();
              $c++;

            }

          }

    }

    return $c;

  }

  // Retorna la duración en minutos de una prueba
  public function duracionPrueba($pagina_id)
  {

    $em = $this->em;
    $duracion = 0;

    $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina_id);

    if ($prueba)
    {
      // Duración en minutos
        $duracion = intval($prueba->getDuracion()->format('G'))*60;
        $duracion += intval($prueba->getDuracion()->format('i'));
    }

      return $duracion;

  }

  // Retorna true si la página o algunos de sus padres está vencido
  public function programaVencido($pagina_id, $empresa_id ,$usuario)
  {

    $em = $this->em;
    $vencido = false;

    $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $pagina_id,
                                                  'empresa' => $empresa_id));
    $fecha_nivel = ($usuario->getNivel()->getFechaInicio() && $usuario->getNivel()->getFechaFin())? true:false;
    if ($fecha_nivel) {
      $vencido = date('Y-m-d') < $usuario->getNivel()->getFechaFin()->format('Y-m-d')? false:true;
    }else{
          if ($pagina_empresa)
          {
            if ($pagina_empresa->getFechaVencimiento()->format('Y-m-d') < date('Y-m-d'))
            {
              $vencido = true;

            }
            elseif ($pagina_empresa->getPagina()->getPagina()) {
              // Verificación de programa vencido del padre
              $vencido = $this->programaVencido($pagina_empresa->getPagina()->getPagina()->getId(), $empresa_id,$usuario);
            }
          }
    }

      return $vencido;

  }

  public function eventoVencido($evento){
     $fecha_vencimiento = $evento->getFechaFin() ? true:false;
     $vencido = false;
     if($fecha_vencimiento){
      $vencido = date('Y-m-d') < $evento->getFechaFin()->format('Y-m-d')? false:true;
     }

     return $vencido;
  }

  public function noticiaVencida($noticia){
    $fecha_vencimiento = $noticia->getFechaVencimiento()? true:false;
    $vencido = false;
    if ($fecha_vencimiento) {
      $vencido = date('Y-m-d') < $noticia->getFechaVencimiento()->format('Y-m-d')? false:true;
    }
    return $vencido;
  }

    // Retorna el certificado de la página, sino de un grupo de página, sino de la empresa
    public function getCertificado($empresa_id, $tipo_certificado, $pagina_id)
    {

        $em = $this->em;
        $certificado = false;

        //consultamos el certificado por pagina
        $certificado_pagina = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $empresa_id,
                                                                                                      'tipoCertificado' => $tipo_certificado['pagina'],
                                                                                                      'entidadId' => $pagina_id));

        if ($certificado_pagina)
        {
            $certificado = $certificado_pagina;
        }
        else {

            //consultamos el certificado por grupo de paginas
            $certificado_grupos = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $empresa_id,
                                                                                                          'tipoCertificado' => $tipo_certificado['grupo_paginas'],
                                                                                                          'entidadId' => $pagina_id));
            if ($certificado_grupos)
            {
                $certificado = $certificado_grupos;

            }
            else {

                //consultamos el certificado por empresa     'entidadId' => 0
                $certificado_empresas = $em->getRepository('LinkComunBundle:CertiCertificado')->findOneBy(array('empresa' => $empresa_id,
                                                                                                                'tipoCertificado' => $tipo_certificado['empresa']));
                if ($certificado_empresas)
                {
                    $certificado = $certificado_empresas;

                }

            }

        }

        return $certificado;

    }

    // Retorna 1 si dentro de la estructura de la página existe alguna evaluación, sino retorna 0
    public function hasTest($pagina_sesion)
    {

        $r = 0;

        if ($pagina_sesion['tiene_evaluacion'])
        {
            $r = 1;
        }
        else {
            if (count($pagina_sesion['subpaginas']))
            {
                foreach ($pagina_sesion['subpaginas'] as $subpagina_sesion)
                {
                    $r = $this->hasTest($subpagina_sesion);
                    if ($r == 1)
                    {
                        break;
                    }
                }
            }
        }

        return $r;

    }

    // Retorna la estructura de las actividades recientes en los programas para el Dashboard y Mis Programas
    public function getActividadesRecientes($usuario_id, $paginas_sesion, $empresa_id, $yml)
    {

        $em = $this->em;
        $timeZone = 0;
        $session = new session();

        // buscando las últimas 3 interacciones del usuario donde la página no esté completada
        $query = $em->createQuery('SELECT pl FROM LinkComunBundle:CertiPaginaLog pl
                                    JOIN LinkComunBundle:CertiPaginaEmpresa pe WITH pe.pagina = pl.pagina
                                    JOIN pl.pagina p
                                    WHERE pl.usuario = :usuario_id
                                        AND pl.estatusPagina != :completada
                                        AND p.pagina IS NULL
                                        AND pe.activo = :activo
                                        AND pe.empresa = :empresa
                                    ORDER BY pl.id DESC')
                    ->setParameters(array('usuario_id' => $usuario_id,
                                          'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                          'activo' => true,
                                         'empresa' => $session->get('empresa')['id']) );
        $actividadreciente_padre = $query->getResult();

        $actividad_reciente = array();
        $reciente = 0;

        foreach ($actividadreciente_padre as $arp)
        {

            if ($reciente == 3)
            {
                break;
            }

            if (array_key_exists($arp->getPagina()->getId(), $paginas_sesion) && !array_key_exists($arp->getPagina()->getId(), $actividad_reciente))
            {

                $reciente++;
                $ar = array();
                $pagina_sesion = $paginas_sesion[$arp->getPagina()->getId()];
                $subpaginas_ids = $this->hijas($pagina_sesion['subpaginas']);
                $pagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('empresa' => $empresa_id,
                                                                                                            'pagina' => $arp->getPagina()->getId()));
                $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->find($usuario_id);
                $fechaFin = $pagina_empresa->getFechaVencimiento();
                $fechaInicio = $pagina_empresa->getFechaInicio();
                $padre_id = $arp->getPagina()->getId();
                $imagen = $arp->getPagina()->getFoto();
                $porcentajeAvance = round($arp->getPorcentajeAvance());

                if($usuario->getNivel()){
                  if ($usuario->getNivel()->getFechaInicio() && $usuario->getNivel()->getFechaFin()) {
                      $fechaInicio = $usuario->getNivel()->getFechaInicio();
                      $fechaFin = $usuario->getNivel()->getFechaFin();
                  }
                }

                if ($pagina_empresa->getEmpresa()->getZonaHoraria()) {
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

                 $dias = $this->timeAgo($fechaFin);
                 $porcentaje = $this->porcentaje_finalizacion($fechaInicio,$fechaFin,$dias);
                 $porcentaje_finalizacion = $dias;
                 $class_finaliza = $this->classFinaliza($porcentaje);
                  if ($link_enabled)
                    {
                      $nivel_vigente = true;
                      if($dias == 0){
                         $dias_vencimiento = $this->translator->trans('Vence hoy');
                      }else{
                         $dias_vencimiento = $this->translator->trans('Finaliza en').' '.$dias.' '.$this->translator->trans('días');
                      }
                    }
                    else {
                        $nivel_vigente = false;
                        $dias_vencimiento = $this->translator->trans('Programa Vencido');
                    }


                $titulo_padre = $arp->getPagina()->getNombre();


                if (count($subpaginas_ids))
                {

                    $query = $em->createQuery('SELECT pl FROM LinkComunBundle:CertiPaginaLog pl
                                                WHERE pl.usuario = :usuario_id
                                                    AND pl.estatusPagina != :completada
                                                    AND pl.pagina IN (:hijas)
                                                ORDER BY pl.id DESC')
                                ->setParameters(array('usuario_id' => $usuario_id,
                                                      'completada' => $yml['parameters']['estatus_pagina']['completada'],
                                                      'hijas' => $subpaginas_ids))
                                ->setMaxResults(1);
                    $ar = $query->getResult();
                }


                if ($ar)
                {

                    $id =  $ar[0]->getPagina()->getId();
                    $titulo_hijo = $ar[0]->getPagina()->getNombre();
                    $categoria = $ar[0]->getPagina()->getCategoria()->getNombre();

                    if ($ar[0]->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                    {
                        $avanzar = 2;
                        $evaluacion_pagina = $id;
                        $evaluacion_programa = $padre_id;
                    }
                    else {
                        $avanzar = 0;
                        $evaluacion_pagina = 0;
                        $evaluacion_programa = 0;
                    }

                }
                else {

                    $id = 0;
                    $titulo_hijo = '';
                    $categoria = $arp->getPagina()->getCategoria()->getNombre();

                    // buscando registros de la pagina para validar si esta en evaluación
                    $pagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                                        'pagina' => $padre_id));
                    if ($arp->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                    {
                        $avanzar = 2;
                        $evaluacion_pagina = $padre_id;
                        $evaluacion_programa = $padre_id;
                    }
                    else {
                        $avanzar = 0;
                        $evaluacion_pagina = 0;
                        $evaluacion_programa = 0;
                    }

                }



                $actividad_reciente[$arp->getPagina()->getId()] = array('id' => $id,
                                                                        'padre_id' => $padre_id,
                                                                        'titulo_padre' => $titulo_padre,
                                                                        'titulo_hijo' => $titulo_hijo,
                                                                        'imagen' => $imagen,
                                                                        'categoria' => $categoria,
                                                                        'dias_vencimiento' => $dias_vencimiento,
                                                                        'class_finaliza' => $class_finaliza,
                                                                        'porcentaje' => $porcentajeAvance,
                                                                        'avanzar' => $avanzar,
                                                                        'evaluacion_pagina' => $evaluacion_pagina,
                                                                        'evaluacion_programa' => $evaluacion_programa,
                                                                        'link_enabled' => $link_enabled);

            }

        }

        return array('actividad_reciente' => $actividad_reciente,
                     'reciente' => $reciente);

    }

    public function searchMail($mail,$empresa_id,$usuario_id=0){
        //usuario_id = 0 cuando no se que quiere comprobar los datos de correo para un usuario en especifico
        $em = $this->em;
        $query = $em->createQuery('SELECT COUNT(u.id) FROM LinkComunBundle:AdminUsuario u
                                    WHERE (u.correoCorporativo =:mail OR u.correoPersonal=:mail)
                                    AND u.empresa =:empresa_id
                                    AND u.id !=:usuario_id')
                ->setParameters(['mail'=> $mail,'empresa_id'=>$empresa_id,'usuario_id'=>$usuario_id]);
        $result = $query->getSingleScalarResult();

        return $result;
    }

        public function ExcelMails($mails,$encabezado,$pex,$yml,$sufijo){
        $em = $this->em;
        $readerXlsx  = $this->get('phpoffice.spreadsheet')->createReader('Xlsx');
        $fileWithPath = $this->container->getParameter('folders')['dir_project'].'docs/formatos/correosFallidos.xlsx';
        $spreadsheet = $readerXlsx->load($fileWithPath);
        $objWorksheet = $spreadsheet->setActiveSheetIndex(0);
        $columnNames = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $styleThinBlackBorderOutline = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => \PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
        );

        // Encabezado
         $objWorksheet->setCellValue('B1', $encabezado['titulo']);
         $objWorksheet->setCellValue('B2', $encabezado['empresa']);
         $objWorksheet->setCellValue('B3', $encabezado['fecha']);

         $row = 5;
         foreach ($mails as $mail){
            $objWorksheet->getStyle("A$row:F$row")->applyFromArray($styleThinBlackBorderOutline); //bordes
            // Datos de las columnas comunes
            $objWorksheet->setCellValue('A'.$row, $mail->getUsuario()->getLogin());
            $objWorksheet->setCellValue('B'.$row, $mail->getUsuario()->getNombre());
            $objWorksheet->setCellValue('C'.$row, $mail->getUsuario()->getApellido());
            $objWorksheet->setCellValue('D'.$row, $mail->getCorreo());
            $objWorksheet->setCellValue('E'.$row, $mail->getUsuario()->getNivel()->getNombre());
            $objWorksheet->setCellValue('F'.$row, $mail->getMensaje());
            $row++;

        }
        $hoy = date('d-m-Y');
        $writer = $this->get('phpoffice.spreadsheet')->createWriter($spreadsheet, 'Xlsx');
        $path = 'recursos/notificaciones/'.'correosNoentregados_'.$encabezado['empresa'].'_'.$hoy.'_'.$sufijo.'.xls';
        $xls = $yml['parameters']['folders']['dir_uploads'].$path;
        $writer->save($xls);
        $archivo = $yml['parameters']['folders']['uploads'].$path;
        $document_name = 'correosNoEntregados_'.$encabezado['empresa'].'_'.$hoy.'_'.$sufijo.'.xls';
        $bytes = filesize($xls);
        $document_size = $this->fileSizeConvert($bytes);

        $return = array('archivo' => $archivo,
                        'document_name' => $document_name,
                        'document_size' => $document_size);
        return $return;

    }

    public function converDate($date,$initialTimeZone,$finalTimeZone,$reportDate=true){
        $format = ($reportDate)? 'd/m/Y g:i a':'Y-m-d H:i:s';
        $date = new \DateTime(date('Y-m-d H:i:s',strtotime($date)),new \DateTimeZone($initialTimeZone));
        $date->setTimeZone(new \DateTimeZone($finalTimeZone));
        $date = $date->format($format);
        $date = explode(" ",$date);
        $return = ($reportDate)? (object)array('fecha'=>$date[0],'hora'=>$date[1].' '.$date[2]):(object)array('fecha'=>$date[0],'hora'=>$date[1]);
        return $return;
    }

    public function totalTime($date_inicio,$date_fin){
        //$fecha_inicio_total = $date_inicio.' '.$time_inicio[0].':00'
        //(new \DateTime('now'))
        $fecha_inicio_total = new \DateTime(date('Y/m/d H:i:s', strtotime($date_inicio)));

        //$time_fin = explode(" ",$time_fin);
        //$fecha_fin_total = $date_fin.' '.$time_fin[0];
        $fecha_fin_total = new \DateTime(date('Y/m/d H:i:s', strtotime($date_fin)));
        $interval = date_diff($fecha_inicio_total, $fecha_fin_total);
        return $interval->format('%h:%i:%s');
    }

    public function AvancetotalTime($date_inicio,$date_fin,$usuario_id){
      $fecha_inicio = new \DateTime(date('Y-m-d 00:00:00', strtotime($date_inicio)));
      $fecha_fin = new \DateTime(date('Y-m-d 23:59:59', strtotime($date_fin)));
      $fecha_inicio = $fecha_inicio->format('Y-m-d H:i:s');
      $fecha_fin = $fecha_fin->format('Y-m-d H:i:s');
      
      $em = $this->em;

        $query = $em->getConnection()->prepare('SELECT
                                                fnavance_total_time(:pfecha_inicio, :pfecha_fin, :pusuario_id) as
                                                resultado;');
        
        $query->bindValue(':pfecha_inicio', $fecha_inicio, \PDO::PARAM_STR);
        $query->bindValue(':pfecha_fin', $fecha_fin, \PDO::PARAM_STR);
        $query->bindValue(':pusuario_id', $usuario_id, \PDO::PARAM_INT);
        $query->execute();
        $rs = $query->fetchAll();

        return $rs[0]['resultado'];

      
    }

    public function clearNameTimeZone($timeZone,$pais,$yml){
        if ($timeZone!=$yml['parameters']['time_zone']['utc']) {
            $nameArray = explode($yml['parameters']['time_zone']['name_separator'],$timeZone);
            $lengthArray = count($nameArray);
            $zone = str_replace("_"," ",$nameArray[$lengthArray-1]);
            return ($zone == $pais)? $zone:$zone.', '.$pais.'.';
        }else{
            return $timeZone.'.';
        }

    }

    public function classFinaliza($porcentaje){
      if ($porcentaje >= 70){
        $class_finaliza = 'alertTimeGood';
      }
      elseif ($porcentaje >= 31 && $porcentaje <= 69){
        $class_finaliza = 'alertTimeWarning';
      }
      elseif ($porcentaje >= 0 && $porcentaje <= 30) {
        $class_finaliza = 'alertTimeDanger';
      }else {
        $class_finaliza = 'alertTimeDanger';
      }
      return $class_finaliza;
    }

    public function obtenerProgramaCurso($pagina)
    {
      while ($pagina->getPagina()){
        $pagina = $pagina->getPagina();
      }

      $categoria = $pagina->getCategoria();
      return array(
        'categoria' => $categoria->getNombre(),
        'nombre' => $pagina->getNombre(),
        'programa_id' => $pagina->getId()
      );

    }

    public function alarmasGrupo($tipoAlarma,$descripcion,$entidadId,$fecha,$grupoId,$empresaId,$usuarioId,$rolIn = 2,$rolExc = 3){
       $em = $this->em;
       $query = $em->getConnection()->prepare('SELECT fnalarmas_participantes
                                                            (:ptipo_alarma,
                                                             :pdescripcion,
                                                             :pentidad_id,
                                                             :pfecha,
                                                             :pgrupo_id,
                                                             :pempresa_id,
                                                             :pusuario_id,
                                                             :prolin_id,
                                                             :prolex_id) as resultado;');
                    $query->bindValue(':ptipo_alarma', $tipoAlarma, \PDO::PARAM_INT);
                    $query->bindValue(':pdescripcion',$descripcion, \PDO::PARAM_STR);
                    $query->bindValue(':pentidad_id', $entidadId, \PDO::PARAM_INT);
                    $query->bindValue(':pfecha',$fecha, \PDO::PARAM_STR);
                    $query->bindValue(':pgrupo_id',$grupoId, \PDO::PARAM_INT);
                    $query->bindValue(':pempresa_id',$empresaId, \PDO::PARAM_INT);
                    $query->bindValue(':pusuario_id',$usuarioId , \PDO::PARAM_INT);
                    $query->bindValue(':prolin_id',$rolIn, \PDO::PARAM_INT);
                    $query->bindValue(':prolex_id',$rolExc , \PDO::PARAM_INT);
                    $query->execute();
                    $gc = $query->fetchAll();
                    $resultado = $gc[0]['resultado'];


      return $resultado;
    }



  public function obtenerEstructura($pagina_id,$yml){
    $padres = array($pagina_id);
    $retorno = array();
    $em = $this->em;


    while (count($padres)>0) {
        $padresTemp = array();
        foreach ($padres as $padre) {
          $hijos = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina'=>$padre,'estatusContenido'=>$yml['parameters']['estatus_contenido']['activo']));
          if(count($hijos)>0){
            foreach ($hijos as $hijo) {
             array_push($retorno,$hijo->getId());
             $nietos =  $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina'=>$hijo->getId(),'estatusContenido'=>$yml['parameters']['estatus_contenido']['activo']));
             if(count($nietos)>0){
              array_push($padresTemp,$hijo->getId());
             }
            }
          }
        }

        $padres = $padresTemp;

      }

      return $retorno;
    }

    public function statusChecksHerencia($paginaEmpresa,$estructuraPagina){
        $em = $this->em;
        $muro_c = 0;
        $espacio_c = 0;
        $periodo_c = 0;
        $total = count($estructuraPagina);

        foreach ($estructuraPagina as $subpagina) {
          $subpaginaEmpresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina'=>$subpagina,'empresa'=>$paginaEmpresa->getEmpresa()->getId()));

          if ($subpaginaEmpresa->getMuroActivo() == $paginaEmpresa->getMuroActivo()) {
            $muro_c++;
          }
          if ($subpaginaEmpresa->getColaborativo() == $paginaEmpresa->getColaborativo()){
            $espacio_c++;
          }
          if($subpaginaEmpresa->getFechaInicio() == $paginaEmpresa->getFechaInicio() && $subpaginaEmpresa->getFechaVencimiento() == $paginaEmpresa->getFechaVencimiento()){
            $periodo_c++;

          }
        }

        $retorno = array(
          'muro'=> $muro_c == $total ? 1:0,
          'espacio'=> $espacio_c == $total ? 1:0,
          'periodo'=> $periodo_c == $total ? 1:0
         );


        return $retorno;
    }

    public function obtenerPaginas($pagina_id,$categoria_id,$yml){
      $paginas = null;
      $em = $this->em;
      $ids = array();
      
      $categoria = $yml['parameters']['categoria']['modulo'];

      $query = $em->createQuery('SELECT p FROM LinkComunBundle:CertiPagina p
                                 WHERE p.pagina =:pagina_id
                                 ORDER BY p.nombre ASC')
                ->setParameters(['pagina_id'=> $pagina_id]);
      $modulos = $query->getResult();

      if ($categoria_id == $yml['parameters']['categoria']['modulo'] || $yml['parameters']['categoria']['competencia']) {
        $paginas = $modulos;
      }elseif ($categoria_id == $yml['parameters']['categoria']['materia'] || $categoria_id == $yml['parameters']['categoria']['leccion'] ) {
        foreach ($modulos as $modulo) {
          array_push($ids,$modulo->getId());
        }
          //AND pl.estatusPagina IN (:vista)')
        $query = $em->createQuery('SELECT p FROM LinkComunBundle:CertiPagina p
                                   WHERE p.pagina IN (:ids)
                                   ORDER BY p.orden ASC')
                  ->setParameters(['ids'=> $ids]);
        $materias = $query->getResult();

        if ($categoria_id == $yml['parameters']['categoria']['materia']) {
          $paginas = $materias;
        }else{
          $ids = array();
          foreach ($materias as $materia) {
            array_push($ids,$materia->getId());
          }
          $query = $em->createQuery('SELECT p FROM LinkComunBundle:CertiPagina p
                                     WHERE p.pagina IN (:ids)
                                     ORDER BY p.orden ASC')
                          ->setParameters(['ids'=> $ids]);
          $paginas = $query->getResult();
        }
      }
      return $paginas;
    }

    public function obtnerPadres($pagina,$yml){
      $return = array('html'=>null,'categoria'=>$pagina->getCategoria()->getId(),'padre'=>null,'pagina_id'=>$pagina->getId());
      $em = $this->em;
     // $activo = $yml['parameters']['estatus_contenido']['activo'];
      $query = $em->createQuery('SELECT p FROM LinkComunBundle:CertiPagina p
                                 WHERE p.pagina =:pagina_id
                                 ORDER BY p.orden ASC')
                ->setParameters(['pagina_id'=> $pagina->getPagina()->getId()]);
      $paginas = $query->getResult();

      if ($pagina->getCategoria()->getId() == $yml['parameters']['categoria']['leccion']) {
          $materia = $em->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('id'=>$pagina->getPagina()->getId()));
          $modulo = $em->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('id'=>$materia->getPagina()->getId()));
          $programa = $em->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('id'=>$modulo->getPagina()->getId()));
      }else if($pagina->getCategoria()->getId() == $yml['parameters']['categoria']['materia']){
          $modulo = $em->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('id'=>$pagina->getPagina()->getId()));
          $programa = $em->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('id'=>$modulo->getPagina()->getId()));
      }else{
       /* Si la pagina con evaluacion es un modulo*/
        $programa = $em->getRepository('LinkComunBundle:CertiPagina')->findOneBy(array('id'=>$pagina->getPagina()->getId()));
      }
      $html ='';
      $html .=  '<option value="" id="" data-orden=""  > </option>';
      foreach ($paginas  as $pagina) {
            $prueba = $em->getRepository('LinkComunBundle:CertiPrueba')->findOneByPagina($pagina->getId());
            if($prueba){
                $disabled = ( $prueba->getPagina()->getId() != $return['pagina_id'])?"disabled = true ":" ";
                $selected = ( $prueba->getPagina()->getId() == $return['pagina_id'])? "selected = true ":" ";
                $style = ( $prueba->getPagina()->getId() == $return['pagina_id'])? ' style="color:#0BA92D" ':' style="color:#D696A9" ';
                $html .=  '<option value='.$pagina->getId().' id="s-'.$pagina->getId().'" data-orden="'.$pagina->getOrden().'"'.$disabled.$selected.$style.' >'.$pagina->getNombre().'</option>';
            }else{
                $html .=  '<option value='.$pagina->getId().' id= s-'.$pagina->getId().' data-orden='.$pagina->getOrden().'>'.$pagina->getNombre().'</option>';
            }
      }
      $return['html'] = $html;
      $return['categoria'] = $pagina->getCategoria()->getId();
      $return['padre'] = $programa->getId();
      //$return['pagina_id'] = $pagina->getId();
      return $return;
    }

    public function obtenerIp(){
      if (!empty($_SERVER['HTTP_CLIENT_IP']))
          return $_SERVER['HTTP_CLIENT_IP'];

      if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
          return $_SERVER['HTTP_X_FORWARDED_FOR'];

      return $_SERVER['REMOTE_ADDR'];
    }


}
