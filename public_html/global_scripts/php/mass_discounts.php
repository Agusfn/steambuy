<?php
ini_set('max_execution_time', 600);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("mysql_connection.php");
require_once("admlogin_functions.php");
require_once("main_purchase_functions.php");
require_once("steam_product_fetch.php");


if(!isAdminLoggedIn()) {
	echo "Denied";
	exit;	
}

$error = -1; // Error inicial:  -1: no se está procesando (no se envio form), 0: ok para proceder, 1: las alicuotas no son numericas 

if(isset($_POST["more32_profit"]) && isset($_POST["less32_profit"])) {
	
	$more32_profit = $_POST["more32_profit"];
	$less32_profit = $_POST["less32_profit"];
	
	if(is_numeric($more32_profit) && is_numeric($less32_profit)) {	 
		if(isset($_POST["force_update"])) $force_update = true;
		else $force_update = false;
		if(isset($_POST["ignore_stock"])) $ignore_stock_games = true;
		else $ignore_stock_games = false;
		 
		$error = 0; 
	} else $error = 1;
	
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Descuentos masivos</title>
</head>
    
<body>
	<?php
    if($error >= 0) {
		if($error == 0) {
			
			reducir_precios($con, $less32_profit, $more32_profit);
			
		} else if($error == 1) echo "Las alicuotas no son numericas.";
    } else {
        ?>
        Si comienza o finaliza una oferta de Steam, dejar que se refleje en el sitio primero antes de usar esta herramienta<br/><br/>
        <form action="" method="post">
        	Alicuota juegos &gt; 32 usd:<br/>
            <input type="text" name="more32_profit" placeholder="1.35" /><br/>
            Alicuota juegos &lt; 32 usd:<br/>
            <input type="text" name="less32_profit" placeholder="1.30" /><br/>
            <input type="checkbox" name="force_update" />Forzar actualización de precio (ignora si el precio de ahora es más barato y pone el nuevo mas caro)<br/>
            <input type="checkbox" name="ignore_stock" />Ignorar juegos en oferta de Stock (se conservan)<br/><br/>
            <input type="submit" value="Comenzar" />
        </form>
        <?php
    } ?>

</body>
</html>

<?php


function reducir_precios($con, $profit1, $profit2) {
	
	global $ignore_stock_games;
	global $force_update;

	$cotiz = obtener_cotiz_mxbr($con);

	$sql = "SELECT * FROM `products` WHERE `product_enabled`=1 AND `product_sellingsite`=1 AND NOT (`product_has_customprice`=1 AND `product_customprice_currency`='ars')";
	if($ignore_stock_games) {
		$sql .= " AND NOT (`product_has_customprice`=1 AND `product_has_limited_units`=1)";	
	}
	$sql .= " ORDER BY `product_rating` DESC";
	$query = mysqli_query($con, $sql);
	
		
	while($pData = mysqli_fetch_assoc($query)) {
		
		echo $pData["product_name"].". ";
		
		$priceDataMx = ssf_getpriceinfo($pData["product_site_url"], "mx");
		$priceDataBr = ssf_getpriceinfo($pData["product_site_url"], "br");

		if($priceDataBr["error"] != 0 || $priceDataMx["error"] != 0) {
			echo "<strong><span style='color:#DB0000;'>Error obteniendo precios mxn/brl de Steam.</span></strong> Err mx: ".$priceDataMx["error"].". Error brl: ".$priceDataBr["error"].".<br/>";
			continue;
		}
				
		$brPriceToUsd = $priceDataBr["finalprice"] / $cotiz["br"];
		$mxPriceToUsd = $priceDataMx["finalprice"] / $cotiz["mx"];
		
		// Obtener precio más barato
		if($brPriceToUsd > $mxPriceToUsd) {
			$cheapest = $mxPriceToUsd;	
		} else $cheapest = $brPriceToUsd;
		
		// Sumar extra (ganancia)
		if($cheapest < 32) $new_price = round($cheapest * $profit1, 2);
		else $new_price = round($cheapest * $profit2, 2);
		
		
		
		if($new_price < $pData["product_finalprice"] || $force_update) {
			if($priceDataMx["firstprice"] == $priceDataMx["finalprice"]) { // si no tiene una oferta de Steam
				$sql = "UPDATE `products` SET `product_external_limited_offer`=0, `product_steam_discount_price`=0, `product_has_customprice`=1, `product_customprice_currency`='usd', 
				`product_has_limited_units`=0, `product_finalprice`=".mysqli_real_escape_string($con, $new_price)." WHERE `product_id`=".$pData["product_id"];
				echo "Cambiar precio: ".$pData["product_finalprice"]." a ".$new_price.". (Sin oferta de steam). ";
			} else { // Si tiene una oferta de Steam
				$priceDataUsd = ssf_getpriceinfo($pData["product_site_url"]);
				$sql = "UPDATE `products` SET `product_external_limited_offer`=1, `product_steam_discount_price`=".mysqli_real_escape_string($con, $priceDataUsd["finalprice"]).", 
				`product_external_offer_endtime`='0000-00-00 00:00:00', `product_has_customprice`=1, `product_customprice_currency`='usd', `product_has_limited_units`=0, 
				`product_finalprice`=".mysqli_real_escape_string($con, $new_price)." WHERE `product_id`=".$pData["product_id"];
				echo "Cambiar precio: ".$pData["product_finalprice"]." a ".$new_price.". (Oferta Steam). ";
			}
			if(mysqli_query($con, $sql)) echo "<strong>OK</strong>.";
			else echo "<strong>Error</strong>.";
		
		} else {
			echo "<strong>No necesario reducir</strong>. Precio actual: ".$pData["product_finalprice"].", precio región mxn/brl: ".$new_price.".";
		}
		echo " (".$priceDataBr["finalprice"]." brl,".$priceDataMx["finalprice"]." mxn).<br/>";

	}
}


function obtener_cotiz_mxbr($con) {
	$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'brl_quote'");
	$brl_quote = mysqli_fetch_row($query);						
	$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'mxn_quote'");
	$mxn_quote = mysqli_fetch_row($query);	
	return array("br"=>$brl_quote[0], "mx"=>$mxn_quote[0]);
}








?>

