<?php

// Para testear usar fetch_product_info.php en /dev/

define("ROOT_LEVEL", "../../../../");
include_once("../../../../global_scripts/php/steam_product_fetch.php");

/*
Parámetros
-steam_url: url de steam
-data_requested: CSV con
n: name, p: price info, d: description, h: header image, s: screenshots, t: tags


Result
-error: 1= error cargando steamProduct

*/
$result = array("error"=>0);

if(isset($_POST["steam_url"]) && isset($_POST["cheap_prices"])) {
	
	$result["mxn_price"] = ssf_getpriceinfo($_POST["steam_url"], "mx");
	$result["brl_price"] = ssf_getpriceinfo($_POST["steam_url"], "br");
	
	echo json_encode($result);
	
} else if(isset($_POST["steam_url"]) && isset($_POST["data_requested"])) 
{
	$data = explode(",", $_POST["data_requested"]);
	$product = new steamProduct($_POST["steam_url"]);
	if($product->loadError == 0) {


		if(in_array("n", $data)) {
			if($product_name = $product->getName()) {
				$result["product_name"]["error"] = 0;
				$result["product_name"]["value"] = $product_name;
			} else $result["product_name"]["error"] = 1;
		}
		
		if(in_array("p", $data)) {
			$result["product_price"] = $product->getPriceInfo(true);	
		}
		
		if(in_array("d", $data)) {
			if($product_description = $product->getDescription()) {
				$result["product_description"]["error"] = 0;
				$result["product_description"]["value"] = $product_description;
			} else $result["product_description"]["error"] = 1;
		}
		
		if(in_array("h",$data)) {
			if(in_array("n", $data)) {
				if($product_image = $product->saveHeaderImg($product_name)) {
					$result["product_image"]["filename"] = $product_image;
					$result["product_image"]["error"] = 0;
				} else $result["product_image"]["error"] = 2;
			} else $result["product_image"]["error"] = 1;
		}
		
		if(in_array("s",$data)) {
			if($product_screenshosts = $product->getScreenshots(5)) {
				$result["product_screenshots"]["value"] = $product_screenshosts;
				$result["product_screenshots"]["error"] = 0;
			} else $result["product_screenshots"]["error"] = 1;
		}

		if(in_array("t",$data)) {
			if($product_tags = $product->getTags(8)) {
				$result["product_tags"]["value"] = $product_tags;
				$result["product_tags"]["error"] = 0;
			} else $result["product_tags"]["error"] = 1;
		}
		
	} else $result["error"] = 1;
	
	echo json_encode($result);
	
} else exit;

?>