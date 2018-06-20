<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Link\ComunBundle\Entity\AdminAlarma;
use Symfony\Component\Translation\TranslatorInterface;
use Link\ComunBundle\Entity\AdminSesion;


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
	//			   $entidad = Nombre de las tabla a comparar (formato Entity)
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
    							   'AdminPreferencia' => 'empresa');
    			break;
    		case 'AdminUsuario':
    			$entidades = array('AdminRolUsuario' => 'usuario',
    							   'AdminSesion' => 'usuario',
    							   'CertiPagina' => 'usuario',
    							   'CertiPrueba' => 'usuario',
    							   'CertiPregunta' => 'usuario',
    							   'CertiOpcion' => 'usuario',
    							   'CertiRespuesta' => 'usuario',
    							   'CertiPaginaLog' => 'usuario',
    							   'CertiPruebaLog' => 'usuario',
    							   'AdminNoticia' => 'usuario',
    							   'CertiMuro' => 'usuario',
    							   'CertiForo' => 'usuario',
    							   'AdminNotificacion' => 'usuario',
    							   'AdminNotificacionProgramada' => 'usuario',
    							   'AdminPreferencia' => 'usuario');
    			break;
            case 'AdminNivel':
                $entidades = array('AdminUsuario' => 'nivel',
                                   'CertiNivelPagina' => 'nivel');
                break;
            case 'CertiPagina':
                $entidades = array('CertiPagina' => 'pagina',
                                   'CertiPaginaEmpresa' => 'pagina',
                                   'CertiPrueba' => 'pagina',
                                   'CertiGrupoPagina' => 'pagina',
                                   'CertiPaginaLog' => 'pagina',
                                   'CertiForo' => 'pagina');
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
                $entidades = array('CertiForo' => 'foro');
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

		if ($this->container->getParameter('sendMail'))
		{
			// ->setBody($this->render($parametros['twig'], $parametros['datos']), 'text/html');
			$ok=0;
			$body = $this->templating->render($parametros['twig'],$parametros['datos']);
			$message = \Swift_Message::newInstance()
	            ->setSubject($parametros['asunto'])
	            ->setFrom($parametros['remitente'])
	            ->setTo($parametros['destinatario'])
	            ->setBody($body, 'text/html');
	        $ok=$this->mailer->send($message);
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

        return $days_ago;

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
		
		return $ok;

	}

	// Verifica si el usuario tiene el rol Empresa y devuelve empresa_id
	public function rolEmpresa($usuario_id, $roles, $yml)
	{

		$empresa_id = 0;
		$em = $this->em;
		
		foreach ($roles as $rol_id)
        {
        	if ($rol_id != $yml['parameters']['rol']['administrador'])
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
		
		foreach ($subpages as $subpage)
		{
			$tiene++;
			if (!$json)
			{
				$return .= '<li data-jstree=\'{ "icon": "fa fa-angle-double-right" }\' p_id="'.$subpage->getPagina()->getId().'" p_str="'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre().'">'.$subpage->getPagina()->getCategoria()->getNombre().': '.$subpage->getPagina()->getNombre();
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

	public function emailUsuarios($usuarios, $notificacion, $template)
	{
		$controller = 'RecordatoriosCommand';
      	$parametros = array();
		foreach ($usuarios as $usuario) {
          $parametros= array('twig'=>$template,
                             'asunto'=>$notificacion->getAsunto(),
                             'remitente'=>array('webmail@formacion2puntocero.com' => 'Formación2.0'),
                             'destinatario'=>$usuario->getCorreoCorporativo(),
                             'datos'=>array('mensaje' => $notificacion->getMensaje(), 'usuario' => $usuario ));

          $this->sendEmail($parametros, $controller);

        }
		
		return true;

	}

	// Crea o actualiza asignaciones de sub-páginas con los mismos valores de la página padre
	public function asignacionSubPaginas($pagina_empresa, $yml, $onlyDates = 0, $onlyMuro = 0)
	{

		$em = $this->em;
		$orden = 0;
		
		$subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina' => $pagina_empresa->getPagina()->getId(),
																					'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']),
																			  array('orden' => 'ASC'));
		
		foreach ($subpages as $subpage)
		{

			$orden++;
			$subpagina_empresa = $em->getRepository('LinkComunBundle:CertiPaginaEmpresa')->findOneBy(array('pagina' => $subpage->getId(),
                                                                                                    	   'empresa' => $pagina_empresa->getEmpresa()->getId()));

			$query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:CertiPrueba p 
			                                        WHERE p.pagina = :pagina_id AND p.estatusContenido = :activo')
			            ->setParameters(array('pagina_id' => $subpage->getId(),
			                        		  'activo' => $yml['parameters']['estatus_contenido']['activo']));
			$tiene_prueba = $query->getSingleScalarResult();

			if (!$subpagina_empresa)
            {
                // Nueva asignación
                $subpagina_empresa = new CertiPaginaEmpresa();
                $subpagina_empresa->setEmpresa($pagina_empresa->getEmpresa());
	            $subpagina_empresa->setPagina($subpage);
	            $subpagina_empresa->setFechaInicio($pagina_empresa->getFechaInicio());
	            $subpagina_empresa->setFechaVencimiento($pagina_empresa->getFechaVencimiento());
	            $subpagina_empresa->setActivo($pagina_empresa->getActivo());
	            $subpagina_empresa->setAcceso($pagina_empresa->getAcceso());
	            $subpagina_empresa->setPruebaActiva($tiene_prueba ? $pagina_empresa->getPruebaActiva() : false);
	            $subpagina_empresa->setMaxIntentos($pagina_empresa->getMaxIntentos());
	            $subpagina_empresa->setPuntajeAprueba($pagina_empresa->getPuntajeAprueba());
	            $subpagina_empresa->setMuroActivo($pagina_empresa->getMuroActivo());
	            $subpagina_empresa->setColaborativo($pagina_empresa->getColaborativo());
            }
            else {
            	if ($onlyDates)
	            {
	            	$subpagina_empresa->setFechaInicio($pagina_empresa->getFechaInicio());
	            	$subpagina_empresa->setFechaVencimiento($pagina_empresa->getFechaVencimiento());
	            }
	            if ($onlyMuro)
	            {
	            	$subpagina_empresa->setMuroActivo($pagina_empresa->getMuroActivo());
	            }
            }
            
            $subpagina_empresa->setOrden($orden);
            $em->persist($subpagina_empresa);
            $em->flush();
			
			$this->asignacionSubPaginas($subpagina_empresa, $yml, $onlyDates, $onlyMuro);

		}

	}

	public function sendEmailCommand($parametros)
	{

		if ($this->container->getParameter('sendMail'))
		{
			// ->setBody($this->render($parametros['twig'], $parametros['datos']), 'text/html');
			$body = $this->templating->render($parametros['twig'],$parametros['datos']);
			$message = \Swift_Message::newInstance()
	            ->setSubject($parametros['asunto'])
	            ->setFrom($parametros['remitente'])
	            ->setTo($parametros['destinatario'])
	            ->setBody($body, 'text/html');
	        $this->mailer->send($message);
		}
		
        return true;
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
						            AND pe.fechaVencimiento >= :hoy
                                   ORDER BY pe.orden')
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
					if ($subpagina_id && $to_activate)
					{
						if ($subpagina['id'] == $subpagina_id)
						{
							$active = ' active';
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
						$prelada_por = $this->translator->trans('Prelada por').' '.$indexedPages[$prelacion_id]['categoria'].': '.$indexedPages[$prelacion_id]['nombre'];
					}
					$menu_str .= '<li title="'.$prelada_por.'">
									<a href="'.$href.'/'.$subpagina['id'].'" class="'.$active.' '.$bloqueada.'" id="m-'.$subpagina['id'].'">'.$subpagina['nombre'].'</a>';
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
					$prelada_por = $this->translator->trans('Prelada por').' '.$indexedPages[$prelacion_id]['categoria'].': '.$indexedPages[$prelacion_id]['nombre'];
				}
				$menu_str .= '<li title="'.$prelada_por.'">
								<a href="'.$href.'" class="'.$active.' '.$bloqueada.'" id="m-'.$programa['id'].'">'.$programa['nombre'].'</a>';
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
		foreach ($subpagina as $sub) {
			$hijas[] = $sub['id'];
			if($sub['subpaginas']){
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
                            <i id="'.$prefix.'_i-'.$muro['id'].'" class="material-icons mr-1 text-sm color-light-grey '.$like_class.'">thumb_up</i> <span id="'.$prefix.'_like-'.$muro['id'].'">'.$muro['likes']['cantidad'].'</span>
                        </a>
                        <a href="#" class="links text-right text-xs reply_comment" data="'.$muro['id'].'">'.$this->translator->trans('Responder').'</a>
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
                      <a href="#" class="links text-center d-block more_answers" data="'.$muro['id'].'">'.$this->translator->trans('Ver más respuestas').'</a>';
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
                            <i id="'.$prefix.'_i-'.$submuro['id'].'" class="material-icons mr-1 text-sm color-light-grey '.$like_class.'">thumb_up</i> <span id="'.$prefix.'_like-'.$submuro['id'].'">'.$submuro['likes']['cantidad'].'</span>
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

        $exito=false;
        $error='';

		$em = $this->em;

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $datos['login'],
                                                                                       'clave' => $datos['clave']));
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
                                        ->setParameters(array('empresa' => $datos['empresa']['id'],
                                                              'nivel_usuario' => $usuario->getNivel()->getId(),
                                                              'activo' => true,
                                                              'hoy' => date('Y-m-d')));
                            $paginas_bd = $query->getResult();
                            
                            if (!$paginas_bd)  //validamos que la empresa tenga paginas activas
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
                                
                                // Estructura de páginas
                                $paginas = array();
                                $orden = 0;
                                foreach ($paginas_bd as $pagina)
                                {

                                	$orden++;

                                    $query = $em->createQuery('SELECT COUNT(cp.id) FROM LinkComunBundle:CertiPrueba cp
                                                               WHERE cp.estatusContenido = :activo and cp.pagina = :pagina_id')
                                                ->setParameters(array('activo' => $datos['yml']['estatus_contenido']['activo'],
                                                                      'pagina_id' => $pagina->getPaginaEmpresa()->getPagina()->getId()));
                                    $tiene_evaluacion = $query->getSingleScalarResult();

                                    $subPaginas = $this->subPaginasNivel($pagina->getPaginaEmpresa()->getPagina()->getId(), $datos['yml']['estatus_contenido']['activo'], $datos['empresa']['id']);

                                    $paginas[$pagina->getPaginaEmpresa()->getPagina()->getId()] = array('id' => $pagina->getPaginaEmpresa()->getPagina()->getId(),
                                    																	'orden' => $orden,
                                                                                                        'nombre' => $pagina->getPaginaEmpresa()->getPagina()->getNombre(),
                                                                                                        'categoria' => $pagina->getPaginaEmpresa()->getPagina()->getCategoria()->getNombre(),
                                                                                                        'foto' => $pagina->getPaginaEmpresa()->getPagina()->getFoto(),
                                                                                                        'tiene_evaluacion' => $pagina->getPaginaEmpresa()->getPruebaActiva() ? $tiene_evaluacion ? true : false : false,
                                                                                                        'acceso' => $pagina->getPaginaEmpresa()->getAcceso(),
                                                                                                        'muro_activo' => $pagina->getPaginaEmpresa()->getMuroActivo(),
                                                                                                        'espacio_colaborativo' => $pagina->getPaginaEmpresa()->getColaborativo(),
                                                                                                        'prelacion' => $pagina->getPaginaEmpresa()->getPrelacion() ? $pagina->getPaginaEmpresa()->getPrelacion()->getId() : 0,
                                                                                                        'inicio' => $pagina->getPaginaEmpresa()->getFechaInicio()->format('d/m/Y'),
                                                                                                        'vencimiento' => $pagina->getPaginaEmpresa()->getFechaVencimiento()->format('d/m/Y'),
                                                                                                        'subpaginas' => $subPaginas);

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
                                $admin_sesion->setUsuario($usuario);
                                $admin_sesion->setDisponible(true);
                                $em->persist($admin_sesion);
                                $em->flush();

                                $session = new session();
                                $session->set('iniFront', true);
                                $session->set('sesion_id', $admin_sesion->getId());
                                $session->set('code', $datos['yml']['search_locale'] ? $this->getLocaleCode() : 'VE');
                                $session->set('usuario', $datosUsuario);
                                $session->set('empresa', $datos['empresa']);
                                $session->set('paginas', $paginas);

                                if($datos['recordar_datos']==1)
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

								$exito=true;
                            }
                        }
                    }
                }
                
            }
        }       	
       	return array("error"=>$error,"exito"=>$exito);
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

	        if($nota_programa)
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
	        $pagina = $em->getRepository('LinkComunBundle:CertiPagina')->findOneById($subpage);
			
			if($nota>0)
			{
		        $subpaginas[$subpage] = array('id' => $subpage,
		        							  'nombre' => $pagina->getNombre(),
		        							  'categoria' => $pagina->getCategoria()->getId(),
								  		      'nota' => $nota,
				               			      'cantidad_intentos' => $cantidad_intentos);
		    }
		}

		return $subpaginas;
	}

	public function iniciarSesionAdmin($datos)
    {

        $exito=false;
        $error='';

		$em = $this->em;

        $usuario = $em->getRepository('LinkComunBundle:AdminUsuario')->findOneBy(array('login' => $datos['login'],
                                                                                       'clave' => $datos['clave']));

		if (!$usuario)
        {
        	$error = $this->translator->trans('Usuario o clave incorrecta.');
        }
        else {
            
            if (!$usuario->getActivo())
            {
                $error = $this->translator->trans('Usuario inactivo. Contacte al administrador del sistema.');
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
                        if (in_array($rol_usuario->getRol()->getId(), $roles_bk))
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
                            $datosUsuario = array('id' => $usuario->getId(),
                                                  'nombre' => $usuario->getNombre(),
                                                  'apellido' => $usuario->getApellido(),
                                                  'correo' => $usuario->getCorreoPersonal(),
                                                  'foto' => $usuario->getFoto(),
                                                  'roles' => $roles_usuario);

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

                            if($datos['recordar_datos']==1)
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
							$exito=true;
                        }
                    }
                }
            }
        }
       	return array("error"=>$error,"exito"=>$exito);
    }

    // Calcula la diferencia de tiempo entre fecha y hoy
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

	public function nextLesson($indexedPages, $pagina_id, $usuario_id, $empresa_id, $yml, $programa_id)
	{

		$em = $this->em;

		$next_lesson = 0;
		$evaluacion = 0;
		$nombre_pagina = $indexedPages[$pagina_id]['categoria'].': '.$indexedPages[$pagina_id]['nombre'];
		$continue_button = array('next_lesson' => $next_lesson,
								 'evaluacion' => $evaluacion,
								 'nombre_pagina' => $nombre_pagina);
        $pagina_padre_id = 0;

        $pl = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('usuario' => $usuario_id,
                                                                                    'pagina' => $pagina_id));

        if ($indexedPages[$pagina_id]['tiene_evaluacion'] && $pl->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
        {
        	$evaluacion = $this->evaluacionPagina($pagina_id, $usuario_id, $empresa_id, $yml);
        	$continue_button = array('next_lesson' => $next_lesson,
								 	 'evaluacion' => $evaluacion,
								 	 'nombre_pagina' => $nombre_pagina);
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
                        break;
                    }
                    else {
                        if ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['iniciada'])
                        {
                            $next_lesson = $subpagina['id'];
                            $nombre_pagina = $indexedPages[$next_lesson]['categoria'].': '.$indexedPages[$next_lesson]['nombre'];
                            break;
                        }
                        elseif ($pagina_log->getEstatusPagina()->getId() == $yml['parameters']['estatus_pagina']['en_evaluacion'])
                        {
                            $evaluacion = $subpagina['id'];
                            $nombre_pagina = $indexedPages[$evaluacion]['categoria'].': '.$indexedPages[$evaluacion]['nombre'];
                            break;
                        }
                    }
                }

            }

            if ($next_lesson || $evaluacion)
            {
            	$continue_button = array('next_lesson' => $next_lesson,
							 	 		 'evaluacion' => $evaluacion,
							 	 		 'nombre_pagina' => $nombre_pagina);
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
}
