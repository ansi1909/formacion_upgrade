<?php
$host="127.0.0.1";
$port="5432";

$user="formac_prodtion";
$pass="cwzq]=5APucJ";
$dbname="formac_aulavirtual";


$connect = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$pass");

if(!$connect)
{	
	echo "<p><i>No me conecte</i></p>";
}else
{

	$url= $_SERVER["REQUEST_URI"];
    $ruta = explode("/", $url);

	$sql = "select admin_usuario.nombre,admin_usuario.apellido, certi_pagina.nombre as programa
			from certi_pagina_log
			inner join admin_usuario on (admin_usuario.id=certi_pagina_log.usuario_id)
			inner join certi_pagina on (certi_pagina.id=certi_pagina_log.pagina_id)
			where certi_pagina_log.id=".$ruta[4]." limit 1";

	$resultado = pg_query($connect, $sql);

	while($row = pg_fetch_array($resultado)) 
	{
		$usuario = $row["nombre"]." ".$row["apellido"]; 
		$programa = $row["programa"]; 		
	}
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
	        <link rel="stylesheet" href="/formacion2.0/web/front/base_styles/css/bootstrap/bootstrap.css">
	        <link rel="stylesheet" href="/formacion2.0/web/front/client_styles/formacion/css/main.css">
	        <title>Verificacion del codigoQR</title>
	    </head>
	    <body class="codigoQR">
	        <section class="cQR">
	            <div class="container">
	                <div class="row">
	                    <div class="col-xs-auto col-sm-auto col-md-auto col-auto col-lg-auto col-xl-auto">
	                        <img class="imgForm" src="/formacion2.0/web/front/assets/img/formacion2.0.svg" alt="">
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
	                    <div class="col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12">
	                        <span class="text-cQR">Si quiere saber mas de nuestros productos y servicios, visitanos en:</span>
	                    </div> 
	                </div>
	                <div class="row align-items-center justify-content-center mt-3">
	                    <div class="col-sm-12 col-md-12 col-12 col-lg-12 col-xl-12 text-center">
	                        <a class="link" href="https://www.formacion2puntocero.com/" target="_blank">https://www.formacion2puntocero.com/</a>
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