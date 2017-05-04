<?php
require_once("../../../global_scripts/php/steam_product_fetch.php");

if(!isset($_POST["urls"])) exit;

$urls = json_decode($_POST["urls"]);


for($i=0; $i<sizeof($urls); $i++) {
		
	$priceData = ssf_getpriceinfo($urls[$i]);
	if($priceData["error"] == 0) {
		$prices[$i] = roundfunc($priceData["finalprice"]);
	} else $prices[$i] = "";
		
}

echo json_encode($prices);


?>