<?php
ini_set('max_execution_time', 600);
ini_set('display_errors', 1);

header( 'Content-type: text/html; charset=utf-8' );
date_default_timezone_set("America/Argentina/Buenos_Aires");

define("ROOT_LEVEL", "../../");
define("DEBUG", true); // Muestra resultados en pantalla


/* En una megaoferta de steam, esta opción sirve para, durante un período de la oferta, configurar una fecha de fin de oferta arbitraria antes de la real, y una vez pasado
 esta fecha arbitraria, ignorar las ofertas de steam hasta que terminen
*/
define("override_steam_sales_end", false);
define("override_steam_sales_start", strtotime("2016-12-28 10:00:00")); // A partir de este momento se muestra la fecha de fin de oferta de todos los juegos en oferta, en la fecha customizada
define("override_steam_sales_custom_end", strtotime("2017-01-02 11:00:00"));  // Fecha manual de fin de oferta que indican los juegos. A partir de este momento se ignoran todas las ofertas de Steam
define("override_steam_sales_real_end", strtotime("2017-01-02 16:00:00")); // Acá se dejan de ignorar las ofertas de Steam



require_once("mysql_connection.php");
require_once("steam_product_fetch.php");


$sql = "SELECT * FROM `products` WHERE `product_enabled` = 1 AND `product_sellingsite` = 1 ORDER BY `product_rating` DESC";
//$sql = "SELECT * FROM `products` WHERE `product_enabled` = 1 AND `product_sellingsite` = 1 ORDER BY `product_rating` DESC LIMIT 30";


$res = mysqli_query($con, $sql);

if(DEBUG) echo "Actualizando juegos de Steam...<br/>";


while($pData = mysqli_fetch_array($res)) 
{
	$product = new steamProduct($pData["product_site_url"]);
	$error = 0;
	$sql2 = "";
	
	if($product->loadError == 0) {
		$prices = $product->getPriceInfo(true);
		if($prices["error"] == 0) {
			
			
			
			if(override_steam_sales_end) {
				if(during_override_sales()) {
					if($prices["product_discount"] == 1) {
						$prices["product_discount_endtime"] = date("Y-m-d H:i:s", override_steam_sales_custom_end);
					}				
				} else if(during_override_ignore_sales()) {
					if($prices["product_discount"] == 1) {
						$prices["product_discount"] = 0;
						$prices["product_finalprice"] = $prices["product_firstprice"];
					}
				}
			}


			if($prices["product_discount"] == 0) {	

				// Si no tiene oferta externa limitada, se actualiza el precio		
				//if($pData["product_has_customprice"] == 0 || ($pData["product_has_customprice"] == 1 && $pData["product_external_limited_offer"] == 1)) { 
					$sql2 = "UPDATE `products` SET `product_has_customprice` = 0, `product_external_limited_offer` = 0, `product_external_offer_endtime` = '0000-00-00 00:00:00',
					`product_listprice` = ".$prices["product_finalprice"].", `product_steam_discount_price`=0, `product_finalprice` = ".$prices["product_finalprice"].", `product_update_error` = 0 
					WHERE `product_id` = ".$pData["product_id"];
				//}
			} else if($prices["product_discount"] == 1) {

				if($prices["product_discount_endtime"] != "n/a") {
					mysqli_query($con, "UPDATE `products` SET `product_external_offer_endtime` = '".$prices["product_discount_endtime"]."' WHERE `product_id` = ".$pData["product_id"]);
				}
				
				// Si no tiene oferta propia se actualiza, si tiene, se actualiza sólo si la oferta de steam es mejor
				/*if($pData["product_has_customprice"] == 0) {
					
					$sql2 = "UPDATE `products` SET `product_external_limited_offer` = 1, `product_listprice` = ".$prices["product_firstprice"].", `product_steam_discount_price`=".$prices["product_finalprice"].",
					`product_finalprice` = ".$prices["product_finalprice"].", `product_update_error` = 0 WHERE `product_id` = ".$pData["product_id"];
				
				} else if($pData["product_has_customprice"] == 1 && $pData["product_customprice_currency"] == "usd") {
					
					if(($prices["product_finalprice"] < $pData["product_finalprice"]) || ($prices["product_finalprice"] > $pData["product_steam_discount_price"])) {*/
						
						$sql2 = "UPDATE `products` SET `product_has_customprice` = 0, `product_has_limited_units` = 0, `product_external_limited_offer` = 1,
						 `product_listprice` = ".$prices["product_firstprice"].", `product_steam_discount_price`=".$prices["product_finalprice"].", `product_finalprice` = ".$prices["product_finalprice"].", `product_update_error` = 0 
						 WHERE `product_id` = ".$pData["product_id"];
					/*}
				} */
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
	
	// Output de info de cada juego (DEBUG)
	if(DEBUG) {
		echo $pData["product_name"]."-- Error ".$error."."; 
		if($error == 0) {
			echo " Oferta: ".$prices["product_discount"];
		} else if($error == 2) {
			echo " Error getPriceInfo (".$prices["error"].")";	
		}
		echo "<br/>";
		flush();
    	ob_flush();	
	}
	

}

if(DEBUG) echo "Finalizado actualizaciones precios juegos de Steam";


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


function during_override_sales() {
	$now = time();
	if($now > override_steam_sales_start && $now < override_steam_sales_custom_end) return true;
	else return false;
}
function during_override_ignore_sales() {
	$now = time();
	if($now >= override_steam_sales_custom_end && $now < override_steam_sales_real_end) return true;
	else return false;
}



?>

