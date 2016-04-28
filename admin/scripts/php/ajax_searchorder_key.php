<?php

if(isset($_POST["key"])) {

	require_once("../../../global_scripts/php/mysql_connection.php");	
	
	$sql = "SELECT * FROM `orders` WHERE `order_status` = 2 AND `order_sentkeys` LIKE '%".mysqli_real_escape_string($con, $_POST["key"])."%' ORDER BY `order_date` ASC";
	$res = mysqli_query($con, $sql);
	
	if(mysqli_num_rows($res) > 0) {
		$i = 0;
		$result = "";
		while($oData = mysqli_fetch_assoc($res)) {
			$result .= "ID: ".$oData["order_id"]."\n".$oData["order_sentkeys"]."\n\n";
		}
	} else {
		$result = 0;
	}
	
	echo $result;
}

?>