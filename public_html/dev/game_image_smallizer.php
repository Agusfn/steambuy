<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../global_scripts/php/mysql_connection.php");

define("MAIN_PATH", "../data/img/game_imgs/"); // Dir donde se guardan las imgs de máx tamaño

$NEW_PATH = "../data/img/game_imgs/320x149/"; // Directorio donde se quieren guardar las imágenes de nuevo tamaño
$DIM_WIDTH = 320; // Nuevo tamaño pixels(ancho)
$DIM_HEIGHT = 149; // Nuevo tamaño pixels(largo)


$sql = "SELECT `product_mainpicture` FROM `products`";

$query = mysqli_query($con, $sql);

$i = 0;
while($res = mysqli_fetch_row($query)) {
	
	/*if($i>=5) break;
	$i++;*/
	
	$filename = $res[0];	

	if(!file_exists($NEW_PATH.$filename) && file_exists(MAIN_PATH.$filename)) { 
		$new_img = imagecreatetruecolor($DIM_WIDTH, $DIM_HEIGHT);
		imagecopyresampled($new_img, imagecreatefromjpeg(MAIN_PATH.$filename), 0,0,0,0, $DIM_WIDTH, $DIM_HEIGHT, 460, 215);
		if(imagejpeg($new_img, $NEW_PATH.$filename, 98)) echo $filename." ok<br/>";
		
	}
	
	
}







?>