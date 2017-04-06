<?php

if(isset($_POST["product_type"]) && isset($_POST["client_email"])) 
{
	
	require_once("../../global_scripts/php/mysql_connection.php");
	require_once("../../global_scripts/php/purchase-functions.php");

	
	if($_POST["product_type"] == 1 && isset($_POST["product_url"]) && isset($_POST["product_price"]))
	{
		$status = 0; // Estado: 0 = sin problemas, 1 = máxima cantidad de pedidos alcanzada (error), 2 = se repite el pedido (advertencia)
		
		$sql1 = "SELECT count(*) FROM `orders` WHERE order_status = 1 AND buyer_email = '".mysqli_real_escape_string($con, $_POST["client_email"])."'";
		$res1 = mysqli_query($con, $sql1);
		$count1 = mysqli_fetch_row($res1);
		if(intval($count1[0]) >= 20) {
			$status = 1;
			$result = array(0, 0, $status);
		} else {
			$sql2 = "SELECT count(*) FROM `orders` WHERE buyer_email = '".mysqli_real_escape_string($con, $_POST["client_email"])."' AND product_site_url = 
			'".mysqli_real_escape_string($con, $_POST["product_url"])."' AND order_date > DATE_SUB(NOW(), INTERVAL 5 DAY) AND NOT `order_status` = 3 AND `product_sellingsite` = 1";
			$res2 = mysqli_query($con, $sql2);
			$count2 = mysqli_fetch_row($res2);
			if(intval($count2[0]) > 0) {
				$status = 2;	
			}
			$result = array(quickCalcGame(1, $_POST["product_price"]), quickCalcGame(2, $_POST["product_price"]), $status);
		}
		echo json_encode($result);
	}
	else if($_POST["product_type"] == 2) // paypal
	{
		$status = 0; // Estado: 0 = sin problemas, 1 = máxima cantidad de pedidos alcanzada (error)
		
		$sql = "SELECT count(*) FROM `orders` WHERE `order_status` = 1 AND `buyer_email` = '".mysqli_real_escape_string($con, $_POST["client_email"])."'";
		$res = mysqli_query($con, $sql);
		$count = mysqli_fetch_row($res);
		if(intval($count[0]) >= 10) $status = 1;	
		
		echo $status;
	}
	
}







?>