<?php
ini_set('max_execution_time', 600);
ini_set('display_errors', 1);

header( 'Content-type: text/html; charset=utf-8' );
date_default_timezone_set("America/Argentina/Buenos_Aires");

define("ROOT_LEVEL", "../../");
define("DEBUG", true); // Muestra resultados en pantalla


/*// Esto hace que se ignore cualquier tipo de oferta de Steam, y los juegos van a pasar a tener precio de lista o seguir con oferta interna si tenian una
define("ignore_steam_sales_temp", false)
; 
// Momento en que se comienza a ignorar
define("ignore_sales_start", strtotime("2016-12-22 11:00:00"));

// Momento en que se termina de ignorar
define("ignore_sales_end", strtotime("2016-12-22 15:00:00"));  */



require_once("mysql_connection.php");
require_once("steam_product_fetch.php");




$sql = "SELECT * FROM `products` WHERE `product_enabled` = 1 AND `product_sellingsite` = 1 ORDER BY `product_rating` DESC";
$res = mysqli_query($con, $sql);

if(DEBUG) echo "Iniciando...<br/>";


while($pData = mysqli_fetch_array($res)) 
{
	$product = new steamProduct($pData["product_site_url"]);
	$error = 0;
	$sql2 = "";
	
	if($product->loadError == 0) {
		$prices = $product->getPriceInfo(true);
		if($prices["error"] == 0) {


			if($prices["product_discount"] == 0) {	

				// Si no tiene oferta externa limitada, se actualiza el precio		
				if($pData["product_has_customprice"] == 0 || ($pData["product_has_customprice"] == 1 && $pData["product_external_limited_offer"] == 1)) { 
					$sql2 = "UPDATE `products` SET `product_has_customprice` = 0, `product_external_limited_offer` = 0, `product_external_offer_endtime` = '0000-00-00 00:00:00',
					`product_listprice` = ".$prices["product_finalprice"].", `product_steam_discount_price`=0, `product_finalprice` = ".$prices["product_finalprice"].", `product_update_error` = 0 
					WHERE `product_id` = ".$pData["product_id"];
				}
			} else if($prices["product_discount"] == 1) {

				if($prices["product_discount_endtime"] != "n/a") {
					mysqli_query($con, "UPDATE `products` SET `product_external_offer_endtime` = '".$prices["product_discount_endtime"]."' WHERE `product_id` = ".$pData["product_id"]);
				}
				
				// Si no tiene oferta propia se actualiza, si tiene, se actualiza sólo si la oferta de steam es mejor
				if($pData["product_has_customprice"] == 0) {
					$sql2 = "UPDATE `products` SET `product_external_limited_offer` = 1, `product_listprice` = ".$prices["product_firstprice"].", `product_steam_discount_price`=".$prices["product_finalprice"].",
					`product_finalprice` = ".$prices["product_finalprice"].", `product_update_error` = 0 WHERE `product_id` = ".$pData["product_id"];
				} else if($pData["product_has_customprice"] == 1 && $pData["product_customprice_currency"] == "usd") {
					if(($prices["product_finalprice"] < $pData["product_finalprice"]) || ($prices["product_finalprice"] > $pData["product_steam_discount_price"])) {
						$sql2 = "UPDATE `products` SET `product_has_customprice` = 0, `product_has_limited_units` = 0, `product_external_limited_offer` = 1,
						 `product_listprice` = ".$prices["product_firstprice"].", `product_steam_discount_price`=".$prices["product_finalprice"].", `product_finalprice` = ".$prices["product_finalprice"].", `product_update_error` = 0 
						 WHERE `product_id` = ".$pData["product_id"];
					}
				} 
			}
		} else $error = 2;
	} else $error = 1;
	
	
	if($error > 0) {
		if($pData["product_has_customprice"] == 0 && $pData["product_external_limited_offer"] == 1) {
			$sql2 = "UPDATE `products` SET `product_external_limited_offer` = 0, `product_external_offer_endtime` = '0000-00-00 00:00:00',
			`product_finalprice` = `product_listprice`, `product_steam_discount_price`=0, `product_update_error` = 1 WHERE `product_id` = ".$pData["product_id"];
		}		
	}
	
	if($sql2 != "") mysqli_query($con, $sql2);
	
	if(DEBUG) {
		echo $pData["product_name"]."-- Error ".$error; 
		if($error == 2) {
			echo ": error getPriceInfo. Suberror: ".$prices["error"];	
		}
		echo "<br/>";
		flush();
    	ob_flush();	
	}
	

}

if(DEBUG) echo "Finalizado";


// Actualizar precios de juegos de tiendas ajenas a steam acorde a la fecha de finalizacion de la oferta
$sql = "SELECT * FROM `products` WHERE `product_enabled` = 1 AND NOT `product_sellingsite` = 1 AND `product_external_limited_offer` = 1 
AND NOT `product_external_offer_endtime` = '0000-00-00 00:00:00'";
$res = mysqli_query($con, $sql);
while($pData = mysqli_fetch_assoc($res)) {
	//echo time().",".strtotime($pData["product_external_offer_endtime"]);
	if(time() > strtotime($pData["product_external_offer_endtime"])) {
		
		if($pData["product_sellingsite"] == 3 || $pData["product_sellingsite"] == 4) {
			mysqli_query($con, "UPDATE `products` SET `product_enabled` = 0 WHERE `product_id` = ".$pData["product_id"]);	
		} else {
			mysqli_query($con, "UPDATE `products` SET `product_external_limited_offer` = 0, `product_external_offer_endtime` = '0000-00-00 00:00:00',
			`product_finalprice` = `product_listprice` WHERE `product_id` = ".$pData["product_id"]);	
		}
		
	}
}



/*function on_sale_ignore_interval() {
	$now = time();
	if($now > ignore_sales_start && $now < ignore_sales_end) return true;
	else return false;
}*/


?>

