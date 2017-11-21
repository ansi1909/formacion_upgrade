<?php

namespace Link\ComunBundle\Services;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    							   'AdminNotificacion' => 'usuarioTutor',
    							   'AdminPreferencia' => 'usuario');
    			break;
            case 'AdminNivel':
                $entidades = array('AdminUsuario' => 'nivel',
                                   'CertiNivelPagina' => 'nivel');
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
	// Si es menos de una hora retorna la cantidad de minutos
	// Si es más de una hora y fecha es hoy retorna la hora de fecha
	// Si fecha es ayer retorna "Ayer Hora"
	// Si fecha es menor que ayer se muestra fecha formateado con la hora
	public function timeAgo($fecha)
	{

		$hoy = date('Y-m-d');
		$ayer = date('Y-m-d', strtotime('yesterday'));
		$time_ago = '';
		
		if ($fecha)
		{
			
			$datetime1 = new \DateTime($fecha);
			$datetime2 = new \DateTime("now");
			$interval = $datetime1->diff($datetime2);

			if ($fecha < $ayer)
			{
				$time_ago = $datetime1->format('Y-m-d H:i');
			}
			elseif ($fecha >= $ayer.' 00:00:00' && $fecha < $ayer.' 23:59:59') {
				$time_ago = 'Ayer '.$datetime1->format('H:i');
			}
			elseif ($datetime1->format('Y-m-d') == $hoy) {
				if ($interval->format('%h') > 1)
				{
					$time_ago = $datetime1->format('H:i');
				}
				else {
					$time_ago = 'Hace '.$datetime1->format('i').' minutos';
				}
			}
			
		}

        return $time_ago;

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
        	if ($rol_id == $yml['parameters']['rol']['empresa'])
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
		
		$admin_sesion = $em->getRepository('LinkComunBundle:AdminSesion')->find($sesion_id);
		$admin_sesion->setFechaRequest(new \DateTime('now'));
        $em->persist($admin_sesion);
        $em->flush();

	}

	// Retorna un arreglo multidimensional de las subpaginas dada pagina_id
	public function subPaginas($pagina_id)
	{

		$em = $this->em;
		$subpaginas = array();
		
		$subpages = $em->getRepository('LinkComunBundle:CertiPagina')->findByPagina($pagina_id);
		
		foreach ($subpages as $subpage)
		{
			$subpaginas[] = array('id' => $subpage->getId(),
								  'nombre' => $subpage->getCategoria()->getNombre().': '.$subpage->getNombre(),
								  'subpaginas' => $this->subPaginas($subpage->getId()));
		}

		return $subpaginas;

	}

}
