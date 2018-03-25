<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;
use Link\ComunBundle\Entity\CertiPaginaLog;
use Symfony\Component\Translation\TranslatorInterface;

class Functions
{	
	
	protected $em;
	protected $container;
	protected $mailer;
	private $templating;
	private $translator;

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
	
	public function sendEmail($parametros, $controller)
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
	// Si es menos de una hora retorna la cantidad de minutos
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
					$time_ago = $datetime1->format('H:i');
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
                               'delete_disabled' => $this->linkEliminar($page->getId(), 'CertiPagina'));

        }

        return $paginas;

	}

	// Retorna un arreglo multidimensional de las subpaginas dada pagina_id
	public function subPaginas($pagina_id, $paginas_asociadas = array(), $json = 0)
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

	// Verifica si el usuario tiene el rol Empresa y devuelve empresa_id
	public function emailUsuarios($usuarios, $notificacion, $template)
	{
		$controller = 'RecordatoriosCommand';
      	$parametros = array();
		foreach ($usuarios as $usuario) {
          $parametros= array('twig'=>$template,
                             'asunto'=>$notificacion->getAsunto(),
                             'remitente'=>array('tutorvirtual@formacion2puntocero.com' => 'Formación 2.0'),
                             'destinatario'=>$usuario->getCorreoCorporativo(),
                             'datos'=>array('mensaje' => $notificacion->getMensaje(), 'usuario' => $usuario ));

          $this->sendEmail($parametros, $controller);

        }
		
		return true;

	}

	// Crea o actualiza asignaciones de sub-páginas con los mismos valores de la página padre
	public function asignacionSubPaginas($pagina_empresa, $yml, $onlyDates = 0)
	{

		$em = $this->em;
		
		$subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findBy(array('pagina' => $pagina_empresa->getPagina()->getId(),
																					'estatusContenido' => $yml['parameters']['estatus_contenido']['activo']),
																			  array('orden' => 'ASC'));
		
		foreach ($subpages as $subpage)
		{

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
            }
            
            $em->persist($subpagina_empresa);
            $em->flush();
			
			$this->asignacionSubPaginas($subpagina_empresa, $yml, $onlyDates);

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

		$query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa 
                                   	AND p.pagina = :pagina_id 
                                   	AND p.estatusContenido = :estatus_activo 
                                   	AND pe.activo = :activo 
                                   	AND pe.fechaInicio <= :hoy 
						            AND pe.fechaVencimiento >= :hoy
                                   ORDER BY p.orden')
                    ->setParameters(array('empresa' => $empresa_id,
                    					  'pagina_id' => $pagina_id,
                                          'estatus_activo' => $estatus_contenido,
                                          'activo' => true,
                                          'hoy' => date('Y-m-d')));
        $subpages = $query->getResult();

		foreach ($subpages as $subpage)
		{
            
			$query = $em->createQuery('SELECT COUNT(p.id) FROM LinkComunBundle:CertiPrueba p
                                       WHERE p.estatusContenido = :activo AND p.pagina = :pagina_id')
                        ->setParameters(array('activo' => $estatus_contenido,
                        					  'pagina_id' => $subpage->getPagina()->getId()));
            $tiene_evaluacion = $query->getSingleScalarResult();

            $subpaginas[$subpage->getPagina()->getId()] = array('id' => $subpage->getPagina()->getId(),
                                    							'nombre' => $subpage->getPagina()->getNombre(),
                                    							'categoria' => $subpage->getPagina()->getCategoria()->getNombre(),
                                    							'foto' => $subpage->getPagina()->getFoto(),
                                    							'tiene_evaluacion' => $tiene_evaluacion ? true : false,
                                    							'acceso' => $subpage->getAcceso(),
                                    							'muro_activo' => $subpage->getMuroActivo(),
                                    							'prelacion' => $subpage->getPrelacion() ? $subpage->getPrelacion()->getId() : 0,
                                    							'inicio' => $subpage->getFechaInicio()->format('d/m/Y'),
                                    							'vencimiento' => $subpage->getFechaVencimiento()->format('d/m/Y'),
                                    							'subpaginas' => $this->subPaginasNivel($subpage->getPagina()->getId(), $estatus_contenido, $empresa_id));
		
		}

		return $subpaginas;
		
	}

	// Retorna un arreglo multidimensional con la estructura del menú lateral para la vista de las lecciones
	public function menuLecciones($programa, $subpagina_id, $href, $usuario_id, $estatus_completada, $dimension = 1, $to_activate = 1)
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
					}
					$menu_str .= '<li>
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
							$menu_str .= $this->menuLecciones($subpagina, $subpagina_id, $href, $usuario_id, $estatus_completada, 2, $to_activate);
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
				}
				$menu_str .= '<li>
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
		$lecciones['muros_recientes'] = $muros_recientes;

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
			                        		  'vista' => array($yml['parameters']['estatus_pagina']['completada'], $yml['parameters']['estatus_pagina']['en_evaluación'])));
			$vista = $query->getSingleScalarResult();
			$subleccion['vista'] = $vista;

			$muros_recientes = $this->muroPagina($subpagina_arr['id'], 'id', 'DESC', 0, 5, $usuario_id, $empresa_id, $yml['parameters']['social']);
			$subleccion['muros_recientes'] = $muros_recientes;

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
		                            AND m.empresa = :empresa_id')
		            ->setParameters(array('pagina_id' => $pagina_id,
		            					  'empresa_id' => $empresa_id));
		$total_comentarios = $query->getSingleScalarResult();

        foreach ($muros_bd as $muro)
        {

        	$qb = $em->createQueryBuilder();
	        $qb->select('m')
	           ->from('LinkComunBundle:CertiMuro', 'm')
	           ->andWhere('m.muro = :muro_id')
	           ->orderBy('m.id', 'DESC')
	           ->setFirstResult(0)
	           ->setMaxResults(5)
	           ->setParameter('muro_id', $muro->getId());
	        $query = $qb->getQuery();
	        $submuros_bd = $query->getResult();

	        // Total de respuestas de este comentario
	        $query = $em->createQuery('SELECT COUNT(m.id) FROM LinkComunBundle:CertiMuro m 
			                            WHERE m.muro = :muro_id')
			            ->setParameter('muro_id', $muro->getId());
			$total_respuestas = $query->getSingleScalarResult();
        	
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
        	$muros[] = array('id' => $muro->getId(),
    						 'mensaje' => $muro->getMensaje(),
    						 'usuario' => $muro->getUsuario()->getId() == $usuario_id ? $this->translator->trans('Yo') : $muro->getUsuario()->getNombre().' '.$muro->getUsuario()->getApellido(),
    						 'foto' => $muro->getUsuario()->getFoto(),
    						 'cuando' => $this->sinceTime($muro->getFechaRegistro()->format('Y-m-d H:i:s')),
    						 'total_respuestas' => $total_respuestas,
    						 'likes' => $this->likes($social['muro'], $muro->getId(), $usuario_id),
    						 'submuros' => $submuros);
        }

        $return = array('muros' => $muros,
        				'total_comentarios' => $total_comentarios);
        return $return;

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
        			$estatus = $yml['parameters']['estatus_pagina']['en_evaluación'];
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
	        	$this->calculoAvance($indexedPages, $pagina_id, $usuario_id, $yml);

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

	public function calculoAvance($indexedPages, $pagina_id, $usuario_id, $yml)
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

				foreach ($indexedPages[$pagina_padre_id]['subpaginas'] as $subpagina)
				{
					$subpagina_log = $em->getRepository('LinkComunBundle:CertiPaginaLog')->findOneBy(array('pagina' => $subpagina['id'],
			                                                                                        	   'usuario' => $usuario_id));
					if ($subpagina_log)
					{
						$avance_parcial += $subpagina_log->getPorcentajeAvance();
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
	            $em->persist($pagina_padre_log);
	        	$em->flush();

			}

			// Calcular el avance del abuelo
			$this->calculoAvance($indexedPages, $pagina_padre_id, $usuario_id, $yml);

		}

	}

	public function likes($social_muro, $entidad_id, $usuario_id)
	{

		$em = $this->em;
		$cantidad = 0;
		$ilike = 0;

		$likes = $em->getRepository('LinkComunBundle:AdminLike')->findBy(array('social' => $social_muro,
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

}
