<?php
require_once("../../global_scripts/php/mysql_connection.php");




if(isset($_POST["search_term"])) {
	
	
	if(strlen($_POST["search_term"]) > 20) {
		$search_term = substr($_POST["search_term"], 0, 20);
	} else $search_term = $_POST["search_term"];
	
	$basic_filter = "AND `product_enabled` = 1 AND NOT (`product_has_limited_units` = 1 AND `product_limited_units` = 0)";
	$sql = "SELECT `product_id`,`product_name` FROM `products` WHERE `product_name` LIKE '%".mysqli_real_escape_string($con, $search_term)."%' ".$basic_filter." ORDER BY `product_rating` DESC LIMIT 6";
	
	//sleep(2);
	$query = mysqli_query($con, $sql);
	
	$results = array();
	
	while($result = mysqli_fetch_assoc($query)) {
		$results[] = array("url" => "juegos/".$result["product_id"]."/", "nombre" => $result["product_name"]);
	}
		
	echo json_encode($results);
}



?>