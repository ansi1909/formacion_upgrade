<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Link\ComunBundle\Entity\CertiPaginaEmpresa;

class Functions
{	
	
	protected $em;
	protected $container;
	protected $mailer;
	private $templating;

	public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container)
	{

		$this->em = $em;
		$this->container = $container;
		$this->mailer = $container->get('mailer');
        $this->templating = $container->get('templating');
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

	// Retorna el URL hasta el directorio web/ de la aplicación
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
	public function subPaginasNivel($pagina_id, $nivel_id, $estatus_contenido, $empresa_id)
	{

		$em = $this->em;
		$subpaginas = array();

		$query = $em->createQuery('SELECT pe FROM LinkComunBundle:CertiPaginaEmpresa pe
                                   JOIN pe.pagina p
                                   WHERE pe.empresa = :empresa 
                                   	AND p.pagina = :pagina_id 
                                   	AND p.estatusContenido = :estatus_activo 
                                   	AND pe.activo = :activo 
                                   ORDER BY p.orden')
                    ->setParameters(array('empresa' => $empresa_id,
                    					  'pagina_id' => $pagina_id,
                                          'estatus_activo' => $estatus_contenido,
                                          'activo' => true));
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
                                    							'subpaginas' => $this->subPaginasNivel($subpage->getPagina()->getId(), $nivel_id, $estatus_contenido, $empresa_id));
		
		}

		return $subpaginas;
		
	}

	// Retorna un arreglo multidimensional con la estructura del menú lateral para la vista de las lecciones
	public function menuLecciones($programa, $subpagina_id, $href, $dimension = 1)
	{

		$menu_str = '';
		
		foreach ($programa['subpaginas'] as $subpagina)
		{
			if ($subpagina['acceso'])
			{
				$active = $subpagina['id'] == $subpagina_id ? ' active' : '';
				$menu_str .= '<li>
								<a href="'.$href.'/'.$subpagina['id'].'" class="menuLeccion'.$active.'" id="m-'.$subpagina['id'].'">'.$subpagina['nombre'].'</a>';
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
						$menu_str .= $this->menuLecciones($subpagina, $subpagina_id, $href, 2);
						$menu_str .= '</ul>';
					}
				}
				$menu_str .= '</li>';
			}
		}

		return $menu_str;

	}

	public function contenidoLecciones($programa, $depth = 1)
	{

		$em = $this->em;
		$lecciones = array();

		foreach ($programa['subpaginas'] as $subpagina)
		{
			$pagina = $em->getRepository('LinkComunBundle:CertiPagina')->find($subpagina['id']);
			//$lecciones 
		}

	}

}
