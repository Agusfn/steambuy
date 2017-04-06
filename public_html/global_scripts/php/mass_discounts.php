<?php
ini_set('max_execution_time', 600);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("mysql_connection.php");
require_once("admlogin_functions.php");
require_once("purchase-functions.php");
require_once("steam_product_fetch.php");


if(!isAdminLoggedIn()) {
	echo "Denied";
	exit;	
}

$error = -1; // Error inicial:  -1: no se está procesando (no se envio form), 0: ok para proceder, 1: las alicuotas no son numericas 

if(isset($_POST["more32_profit"]) && isset($_POST["less32_profit"]) && isset($_POST["ignore_games"])) {
	
	$ignored_games = explode(",", $_POST["ignore_games"]);
	
	$start_from = $_POST["start_from"];
	$limit = $_POST["limit"];
	
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
			
			reducir_precios($con, $less32_profit, $more32_profit, $ignored_games);
			
		} else if($error == 1) echo "Las alicuotas no son numericas.";
    } else {
		$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_menor32'");
		$alicuotas_menor32 = mysqli_fetch_row($query);
		$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_mayor32'");
		$alicuotas_mayor32 = mysqli_fetch_row($query);
        ?>
        Si comienza o finaliza una oferta de Steam, dejar que se refleje en el sitio primero antes de usar esta herramienta<br/><br/>
        <form action="" method="post">
        	Alicuota juegos &gt; 32 usd:<br/>
            <input type="text" name="more32_profit" value="<?php echo $alicuotas_mayor32[0]; ?>" /><br/>
            Alicuota juegos &lt; 32 usd:<br/>
            <input type="text" name="less32_profit" value="<?php echo $alicuotas_menor32[0]; ?>" /><br/>
            <input type="checkbox" name="force_update" />Forzar actualización de precio (si un juego tiene una oferta más baja que la sugerida, le pone el precio sugerido igual)<br/>
            <input type="checkbox" name="ignore_stock" />Ignorar juegos en oferta de Stock (se conservan)<br/><br/>
            Ignorar juegos (separar IDs con coma)<br/>
            <input type="text" name="ignore_games" value="38"/><br/>
            Arrancar desde el juego (a partir del mas rateado)<br/>
            <input type="text" name="start_from" value="0"/><br/>
            Límite cantidad de juegos (0: sin limite):<br/>
            <input type="text" name="limit" value="0"/><br/><br/>
            <input type="submit" value="Comenzar" />
        </form>
        <?php
    } ?>

</body>
</html>

<?php


function reducir_precios($con, $profit1, $profit2, $ignored_games) {
	
	global $ignore_stock_games;
	global $force_update;
	global $start_from;
	global $limit;

	$cotiz = obtener_cotiz_mxbr($con);

	$sql = "SELECT * FROM `products` ORDER BY `product_rating` DESC LIMIT ".mysqli_real_escape_string($con, $start_from).",".($limit == 0 ? 999999 : mysqli_real_escape_string($con, $limit));
	$query = mysqli_query($con, $sql);

	$error_continuo = 0; // Si hay un error contactando a la api, sube +1, si no hay, se pone en cero. Si hay +7 errores sucesivos, se termina el script.
	$juegos_analizados = 0; // Cada 20, se hace un descanzo de 10 segundos.
		
	while($pData = mysqli_fetch_assoc($query)) {
		
		//Ignorar juegos que no son de Steam, ni están habilitados, o tienen precios custom en pesos
		if($pData["product_enabled"] == 0 || $pData["product_sellingsite"] != 1 || ($pData["product_has_customprice"] == 1 && $pData["product_customprice_currency"] == "ars")) {
			echo $pData["product_name"]." <strong> no es de steam/no está habilitado/tiene oferta especial</strong><br/>";
			continue;	
		}
		
		// Ignorar los de stock, si se pide
		if($ignore_stock_games) {
			if($pData["product_has_customprice"] == 1 && $pData["product_has_limited_units"] == 1) {
				echo $pData["product_name"]." <strong>de stock, ignorado.</strong><br/>";
				continue;
			}
		}
		
		// Lista ignorados
		if(in_array($pData["product_id"], $ignored_games)) {
			echo $pData["product_name"]." <strong>IGNORADO</strong><br/>";
			continue;
		}
		
		$juegos_analizados += 1;
		if(is_integer($juegos_analizados/20)) {
			echo "<br/><br/>Pausa 10 segundos<br/><br/>";
			sleep(10);
		}
		
		echo $pData["product_name"].". ";
		
		$priceDataMx = ssf_getpriceinfo($pData["product_site_url"], "mx");
		$priceDataBr = ssf_getpriceinfo($pData["product_site_url"], "br");

		if($priceDataBr["error"] != 0 || $priceDataMx["error"] != 0) {
			echo "<strong><span style='color:#DB0000;'>Error obteniendo precios mxn/brl de Steam.</span></strong> Err mx: ".$priceDataMx["error"].". Error brl: ".$priceDataBr["error"].".<br/>";
			$error_continuo +=1;
			if($error_continuo >= 7) {
				echo "Tarea detenida por errores repetidos";
				break;	
			}
			else continue;
		}
		$error_continuo = 0;
				
		$brPriceToUsd = $priceDataBr["finalprice"] / $cotiz["br"];
		$mxPriceToUsd = $priceDataMx["finalprice"] / $cotiz["mx"];
		
		// Obtener precio más barato
		if($brPriceToUsd > $mxPriceToUsd) {
			$cheapest = $mxPriceToUsd;	
		} else $cheapest = $brPriceToUsd;
		
		// Sumar extra (ganancia)
		if($cheapest < 32) $new_price = round($cheapest * $profit1, 2);
		else $new_price = round($cheapest * $profit2, 2);
		
		
		// Si el precio nuevo es menor que el precio de lista, y además: si el precio nuevo es menor que el precio actual o si se fuerza la actualización (en caso que el precio de oferta este muy bajo, sube)
		if(($new_price < $pData["product_listprice"]) && ($new_price < $pData["product_finalprice"] || $force_update)) {
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
		
		sleep(1); // Esperar 1 segundo, para no saturar al servidor de la API.

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

