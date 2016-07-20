<?php
if(isset($_POST["type"])) {

	require_once("../../global_scripts/php/mysql_connection.php");
	require_once("../../global_scripts/php/main_purchase_functions.php");
	require_once("../../global_scripts/email/mailer.php");
	
	$config = include("../../global_scripts/config.php");
	
	if(!$con) {
		echo "Error: Sin conexión";
		exit;	
	}
		

	if($_POST["type"] == 1 && isset($_POST["payment_method"]) && isset($_POST["product_name"]) && isset($_POST["product_sellingsite"]) && 
	isset($_POST["product_siteurl"]) && isset($_POST["product_discount"]) && isset($_POST["product_usdprice"]) && isset($_POST["client_name"]) && 
	isset($_POST["client_email"]) && isset($_POST["client_ip"]) && isset($_POST["remember_data"])) 
	{ 
		
		$payment_method = $_POST["payment_method"];
		$clientName = $_POST["client_name"];
		$clientEmail = $_POST["client_email"];
		$gameName = $_POST["product_name"];
		$gameSellingSite = $_POST["product_sellingsite"];
		$gameSiteUrl = $_POST["product_siteurl"];
		$gameUsdPrice = floatval($_POST["product_usdprice"]);
		$gameDiscount = $_POST["product_discount"];
		
		// ****** Validación de datos ******
		
		// nombre
		$clientName = preg_replace('/\s\s+/', ' ', $clientName);
		if(preg_match("/^[a-z\sñáéíóú]*$/i",$clientName) == false) {
			echo "Error: Tu nombre ingresado es inválido."; return;
		}
		// email
		if(!preg_match("/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",$clientEmail) || $clientEmail == ""){
			echo "Error: Tu email ingresado es inválido."; return;
		}
		// nombre del juego
		$gameName = preg_replace('/\s\s+/', ' ', $gameName);
		if(strlen($gameName) == 0){
			echo "Error: No se ha ingresado el nombre del juego."; return;			
		} else if(preg_match("/(2|two|3|three|4|four|6|six)(\s|\-)?pack/i",$gameName)) {
			echo "Error: No se permite la compra de packs múltiples.";	
		}
		// sitio de venta
		if($gameSellingSite != 0 && $gameSellingSite != 1) {
			echo "Error: Sitio de venta inválido."; return;	
		} else {
			$gameSellingSite += 1;	
		}
		// url del juego
		if($gameSellingSite == 1 && strpos($gameSiteUrl,"store.steampowered.com") === false) {
			echo "Error: La URL ingresada no es de la tienda de Steam."; return;		
		}else if($gameSellingSite == 2 && strpos($gameSiteUrl,"amazon.com") === false) {
			echo "Error: La URL ingresada no es de la tienda de Amazon."; return;		
		}
		// precio del juego
		if($payment_method != 1 && $payment_method != 2) {
			echo "Error: Medio de pago no válido."; return;	
		}
		if(is_numeric($gameUsdPrice) && $gameUsdPrice != "") {
			if($gameUsdPrice > 100) {
				echo "Error: El precio del juego no debe ser mayor a 100 usd."; return;	
			}
		} else {
			echo "Error: No se ha ingresado un monto válido."; return;	
		}
		
		// **** Guardar datos de comprador ****
		
		if($_POST["remember_data"] == "true") {
			setcookie("client_name", $clientName, time() + 5184000, "/");
			setcookie("client_email", $clientEmail, time() + 5184000, "/");
		} else if($_POST["remember_data"] == "false")  {
			if(isset($_COOKIE["client_name"])) {
			  unset($_COOKIE["client_name"]);
			  setcookie("client_name", "", time() - 3600, "/"); 
			}
			if(isset($_COOKIE["client_email"])) {
			  unset($_COOKIE["client_email"]);
			  setcookie("client_email", "", time() - 3600, "/"); 
			}
		}
		
		// ****** Calcular precio ******
		$gameArsPrice = quickCalcGame($payment_method, $gameUsdPrice);
		if(!is_numeric($gameArsPrice)) {
			echo "Error: Calculo de precio, ".$gameArsPrice; return;	
		}
		
		// ****** Crear orden *******
		$order = new order($con);
		
		if($order->createGameOrder($payment_method, mysqli_real_escape_string($con, $gameName), "", mysqli_real_escape_string($con, $gameSellingSite), 
		mysqli_real_escape_string($con, $gameSiteUrl), mysqli_real_escape_string($con, $gameDiscount), $gameUsdPrice, $gameArsPrice, mysqli_real_escape_string($con, $clientName), 
		mysqli_real_escape_string($con, $clientEmail), mysqli_real_escape_string($con, $_POST["client_ip"]))) 
		{
				
			$orderInfo = $order->orderInfo;
			
			if($payment_method == 2) {
				$orderInfo["bank_account"] = "Caja de ahorro $ 0849/01118545/07 ";
				$orderInfo["bank_account_cbu"] = "0150849701000118545070";
				$orderInfo["bank_account_owner"] = "Rodrigo Fernandez Nuñez";
				$orderInfo["bank_account_cuil"] = "23-35983336-9";
			}
			
			$mail = new Mail;
			$mail_data = array(
			"receiver_name"=>$clientName,
			"order_id"=>$orderInfo["order_id"],
			"order_password"=>$orderInfo["order_password"],
			"product_name"=>$gameName,
			"order_ars_price"=>$gameArsPrice,
			"payment_method"=>$payment_method,
			"product_external_discount"=>$gameDiscount,
			"product_sellingsite"=>$gameSellingSite,
			"product_site_url"=>$gameSiteUrl,
			"order_fromcatalog"=>0);
			if($payment_method == 1) {
				$mail_data["order_purchaseticket_url"] = $orderInfo["order_purchaseticket"];	
			}
			
			$mail->prepare_email("pedido_juego_generado", $mail_data);
			$mail->add_address($clientEmail, $clientName);
			
			if(!$mail->send()) $orderInfo["mailsent"] = 0;
			else $orderInfo["mailsent"] = 1;
			
			echo json_encode($orderInfo);

		} else echo "Error: ". $order->error; 	
			
	}

}
?>