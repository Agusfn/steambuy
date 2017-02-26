<?php

if(isset($_POST["invoice_number"])) {

	require_once("../../../global_scripts/php/mysql_connection.php");	
	
	$return = array("result"=>0, "text"=>""); // Result: 0=ningun pedido, 1= 1 pedido, 2=varios pedidos 

	$sql = "SELECT * FROM `orders` WHERE `order_purchaseticket` LIKE '%".mysqli_real_escape_string($con, $_POST["invoice_number"])."%' ORDER BY `order_date` ASC";
	$res = mysqli_query($con, $sql);
	
	if(mysqli_num_rows($res) == 0) {
		$return["result"] = 0;
	} else if(mysqli_num_rows($res) == 1) {
		$return["result"] = 1;
		$oData = mysqli_fetch_assoc($res);
		$return["text"] = $oData["order_id"];
	} else if(mysqli_num_rows($res) > 0) {
		while($oData = mysqli_fetch_assoc($res)) {
			$return["result"] = 2;
			$return["text"] .= $oData["order_id"]."\n";
		}
	} 
	
	echo json_encode($return);
}

?>