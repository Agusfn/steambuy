<?php
/*
Función para reducir precios de juegos.

Params:
$con: variable de conexion mysql
$mxbrcotiz: array de cotizaciones de usd en mxn y en usd
$alicuotas_region: array con alicuotas de ganancia a las ofertas de region. "menor_32_usd","mayor_32_usd"
$ignored_games: array de ID's de productos (SB) ignorados
$ignore_stock_games: ignorar productos de stock (0-1)
$force_update: si hay algun juego con mejor precio, igual se actualiza al nuevo
$start_from: desde donde empieza el script
$limit: cuantas copias procesa (0: sin limite)

Output:
Listado con resultado del análisis, juego por juego.
*/

function reducir_precios($con, $mxbrcotiz, $alicuotas_region, $ignored_games, $ignore_stock_games, $force_update, $start_from, $limit) {
	
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
				
		$brPriceToUsd = $priceDataBr["finalprice"] / $mxbrcotiz["br"];
		$mxPriceToUsd = $priceDataMx["finalprice"] / $mxbrcotiz["mx"];
		
		// Obtener precio más barato
		if($brPriceToUsd > $mxPriceToUsd) {
			$cheapest = $mxPriceToUsd;	
		} else $cheapest = $brPriceToUsd;
		
		// Sumar extra (ganancia)
		if($cheapest < 32) $new_price = round($cheapest * $alicuotas_region["menor_32_usd"], 2);
		else $new_price = round($cheapest * $alicuotas_region["mayor_32_usd"], 2);
		
		
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
?>