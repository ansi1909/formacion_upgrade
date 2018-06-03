<?php 
$ip = "201.211.188.95";
$ipdat = file_get_contents("http://www.geoplugin.net/json.gp");
echo "que trae el ipdat".$ipdat;
?>