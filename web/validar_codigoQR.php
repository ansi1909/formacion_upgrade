<?php
//$h = $_SERVER["HTTP_HOST"];
$h = $_SERVER["SERVER_NAME"];
$b = $_SERVER["SCRIPT_FILENAME"];
$dr = $_SERVER["CONTEXT_DOCUMENT_ROOT"];
$p = strrpos($b, "web");
$b2 = substr($b, 0, $p);
$file = $b2.'app/config/parameters.yml';
$fp = fopen($file, 'r');

$host="127.0.0.1";
$port="5432";
$user="develo";
$pass="F0rm4c10n2.0";
$dbname="develo_formacion";

while (!feof($fp))
{

    $linea = fgets($fp);
    if (strpos($linea, "database_host") !== false){
    	$dh_array = explode(":", $linea);
    	$host = trim($dh_array[1]);
    }

    if (strpos($linea, "database_port") !== false){
    	$dp_array = explode(":", $linea);
    	$port = trim($dp_array[1]);
    }

    if (strpos($linea, "database_name") !== false){
    	$dn_array = explode(":", $linea);
    	$dbname = trim($dn_array[1]);
    }

    if (strpos($linea, "database_password") !== false){
    	$dps_array = explode(":", $linea);
    	$pass = trim($dps_array[1]);
    }

    if (strpos($linea, "database_user") !== false){
    	$du_array = explode(":", $linea);
    	$user = trim($du_array[1]);
    }

}
fclose($fp);

$connect = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$pass");

if(!$connect)
{	
	echo "<p><i>No me conecte</i></p>";
}
else {

	$url = $_SERVER["REQUEST_URI"];
	$url_pos = strrpos($url, "validar_codigoQR");
	$url_web = substr($url, 0, $url_pos);
	$ruta = explode("/", $url);

	$sql = "select u.nombre, u.apellido, p.nombre as programa, p.id as id pl.fecha_inicio as fecha_inicio, pl.fecha_fin as fecha_fin, p.horas_academicas as horas_academicas 
			from certi_pagina_log pl 
			inner join admin_usuario u on (u.id=pl.usuario_id)
			inner join certi_pagina p on (p.id=pl.pagina_id)
			where pl.id=".$ruta[4]." limit 1";

	$resultado = pg_query($connect, $sql);

	while($row = pg_fetch_array($resultado)) 
	{
		$usuario = $row["nombre"]." ".$row["apellido"];
		$programa = $row["programa"];
		$fecha_inicio = $row["fecha_inicio"];
		$fecha_fin = $row["fecha_fin"];
		$horas_academicas = $row["horas_academicas"];
		$pagina_id = $row["id"];
	}

	/*$sql1 = "select prl.nota 
			 from certi_prueba_log prl 
			 inner join ( certi_prueba pr
						 inner join certi_pagina p on pr.pagina_id = p.id)
			 on prl.prueba_id = pr.id
			 where p.pagina_id =".$pagina_id."
			 and prl.estado = 'APROBADO'";
	
	$resultado1 = pg_query($connect, $sql1);

	$promedio = 0;
	$notas = 0;
	$contador = 0;
	while($row1 = pg_fetch_array($resultado1))
	{
		$notas = $notas + $row1["nota"];
		$contador++;
	}

	$promedio = $notas / $contador;*/
	
	if(isset($resultado))
	{
?>
	<!DOCTYPE html>
	<html lang="es">
	    <head>
	        <meta charset="UTF-8">
	        <meta name="viewport" content="width=device-width, initial-scale=1.0">
	        <meta http-equiv="X-UA-Compatible" content="ie=edge">
	        <link href="https://fonts.googleapis.com/css?family=Merriweather:400,700|Open+Sans:300,400,400i,600,600i,700|Roboto:300,400,500,700" rel="stylesheet">
	        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	        <link rel="stylesheet" href="<?php echo $url_web ?>front/base_styles/css/bootstrap/bootstrap.css">
	        <link rel="stylesheet" href="<?php echo $url_web ?>front/client_styles/formacion/css/main.css">
	        <title>Verificación del codigo QR</title>
	    </head>
	    <body class="codigoQR">
	        <section class="cQR">
	            <div class="container">
	                <div class="row">
	                    <div class="col-xs-auto col-sm-auto col-md-auto col-auto col-lg-auto col-xl-auto">
	                        <img class="imgForm" src="<?php echo $url_web ?>front/assets/img/formacion2.0_smart.svg" alt="">
	                    </div> 
	                </div>
	                <div class="row">
	                    <div class="col-xs-auto col-sm-auto col-md-auto col-auto col-lg-auto col-xl-auto">
	                        <h3 class="text-cQR">Se hace constar que:</h3>
	                    </div> 
	                </div>
	                <div class="row">
	                    <div class="col-xs-12 col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12">
	                        <h1 class="nameP-cQR"><?php echo $usuario;?></h1>
	                    </div>
	                </div>
	                <div class="row">
	                    <div class="col-xs-auto col-sm-auto col-md-auto col-auto col-lg-auto col-xl-auto">
	                        <h3 class="text-cQR">Completó exitosamente el programa:</h3>
	                    </div> 
	                </div>
	                <div class="row">
	                    <div class="col-xs-10 col-sm-10 col-md-10 col-10 col-lg-10 col-xl-10">
	                        <h1 class="progA-cQR"><?php echo $programa; ?></h1>
	                    </div>
	                </div>
	                <div class="row align-items-center justify-content-between mt-12v">
	                    <div class="col-sm-6 col-md-6 col-6 col-lg-6 col-xl-6">
	                        <span class="text-cQR">Fecha inicio: <?php echo $fecha_inicio ?></span>
	                    </div>
	                    <div class="col-sm-6 col-md-6 col-6 col-lg-6 col-xl-6">
	                        <span class="text-cQR">Fecha fin: <?php echo $fecha_fin ?></span>
	                    </div>
	                </div>
	                <div class="row align-items-center justify-content-between mt-12v">
	                    <div class="col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12">
	                        <span class="text-cQR">Equivalente a: <?php echo $horas_academicas ?> hrs. académicas</span>
	                    </div> 
					</div>
					<div class="row align-items-center justify-content-between mt-12v">
	                    <div class="col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12">
	                        <span class="text-cQR">Promedio de nota: <?php echo $promedio ?></span>
	                    </div> 
	                </div>
	                <div class="row align-items-center justify-content-between mt-12v">
	                    <div class="col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12">
	                        <span class="text-cQR">Si quiere saber mas de nuestros productos y servicios, visitanos en:</span>
	                    </div> 
	                </div>
	                <div class="row align-items-center justify-content-center mt-3">
	                    <div class="col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12 text-center">
	                        <a class="link" href="https://www.formacion2puntocero.com/" target="_blank">https://wwwformacionsmart.com/</a>
	                    </div> 
	                </div>
	            </div>
	        </section>
	    </body>
	</html>
<?php
	}
}
pg_close($connect);
?>