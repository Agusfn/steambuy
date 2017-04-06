<?php

// Este script consulta si un e-mail tiene 2 o más pedidos generados de un mismo juego en los últimos 5 días

if(isset($_POST["email"]) && isset($_POST["product_id"])) {

	require_once("../../../global_scripts/php/mysql_connection.php");
	
	$result = -1; // result: 0: tiene 2 o más, 1: tiene >1, -1: no se pudo obtener

	$sql = "SELECT `product_site_url` FROM `products` WHERE `product_id` = ".mysqli_real_escape_string($con, $_POST["product_id"]);
	$res = mysqli_query($con, $sql);
	if(mysqli_num_rows($res) == 1) {
		$url = mysqli_fetch_row($res);
		$sql2 = "SELECT count(*) FROM `orders` WHERE `buyer_email` = '".mysqli_real_escape_string($con, $_POST["email"])."' AND product_site_url = 
		'".mysqli_real_escape_string($con, $url[0])."' AND `order_date` > DATE_SUB(NOW(), INTERVAL 5 DAY) AND NOT `order_status` = 3 AND `product_sellingsite` = 1";	
		$res2 = mysqli_query($con, $sql2);
		$count = mysqli_fetch_row($res2);
		if($count[0] >= 2) $result = 0;
		else $result = 1;
	}
	
	echo $result;
}

?>