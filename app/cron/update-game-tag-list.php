<?php
/*
Este script carga categorías de juegos en base a los tags de los mismos, y las ordena por cantidad de juegos con la misma categoría.
Estas categorías se almacenan en la base de datos y luego se usan para mostrar una lista en el sitio en la sección de "explorar".
*/
require_once "../../config.php";
require_once(ROOT."app/lib/mysql_connection.php");

define("TAG_LIMIT", 100); // Límite de tags o categorías que se almacenan en la base de datos

$restricted_categories = array("buena trama","gran banda sonora","difícil","divertido","oscuro");


$query = mysqli_query($con, "SELECT `product_tags` FROM `products` WHERE `product_enabled` = 1");

$wholetags = "";
while($tag = mysqli_fetch_row($query)) {
	if($tag[0] != "") $wholetags .= $tag[0].",";	
}
$wholetags = mb_strtolower($wholetags, "UTF-8");

$tag_array = explode(",", $wholetags);
$frecuent_tags = array_count_values($tag_array);
arsort($frecuent_tags);


mysqli_query($con, "TRUNCATE TABLE `game_categories`");

$i = 0;
while($tag = current($frecuent_tags)) {
	
	if($i >= TAG_LIMIT) break;
	
	// Si es una categoria restringida, omitir
	if(in_array(key($frecuent_tags), $restricted_categories)) {
		next($frecuent_tags);
		continue;	
	}
	$sql = "INSERT INTO `game_categories` (`id`, `tag_name`, `product_count`) VALUES (NULL, '".key($frecuent_tags)."',  '".$tag."')";
	mysqli_query($con, $sql);
	next($frecuent_tags);
	$i++;
}


echo "Terminado! Cargados ".TAG_LIMIT." tags.<br/><br/><br/>Tags:<br/><br/>";
print_r($frecuent_tags);



?>