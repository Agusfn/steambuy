<?php
if(isset($_POST["type"])) {
	
	$paypal_quote_profit = 1.9;
	$imp = 1.35;
	
	require_once("../../global_scripts/php/mysql_connection.php");
	require_once("../../global_scripts/php/main_purchase_functions.php");
	require_once("../../global_scripts/PHPMailer/PHPMailerAutoload.php");
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
			
			// *** Enviar email ****
			$subject = "Se ha generado tu pedido por el juego ".$gameName;
			$body = "Estimado/a ".$clientName.", se ha generado tu pedido por el juego <strong>".$gameName."</strong> de <strong>".$gameUsdPrice." usd</strong> por 
			<strong>$".$gameArsPrice." pesos argentinos</strong>. El ID del pedido es <strong>".$order->orderInfo["order_id"]."</strong> y la clave es <strong>".$order->orderInfo["order_password"]."</strong>.<br/>
			El siguiente paso para recibir el juego es";
			if($payment_method == 1) {
				$body .= " imprimir y abonar en cualquier sucursal de pago la boleta de pago que puedes encontrar en el siguiente link: <br/>
				<a href='".$order->orderInfo["order_purchaseticket"]."' target='_blank'>".$order->orderInfo["order_purchaseticket"]."</a>.<br/><br/>
				Una vez abonado, el pago tomará entre 12 y 48 horas en acreditarse, y el juego será enviado el día en que se acredita el pago (por lo general al día siguiente 
				de abonar).<br/><br/>";
			} else if($payment_method == 2) {	
				$body .= " realizar una transferencia o depósito bancario a la siguiente cuenta:<br/><br/>
				<strong>Banco:</strong> ICBC<br/>
				<strong>Cuenta:</strong> Caja de ahorro $ 0849/01118545/07<br/>
				<strong>CBU:</strong> 0150849701000118545070<br/>
				<strong>Titular:</strong> Rodrigo Fernandez Nuñez<br/>
				<strong>CUIL:</strong> 23-35983336-9<br/>
				<strong>Monto:</strong> &#36;".$gameArsPrice." (Pesos argentinos)<br/><br/>
				Una vez realizado el pago, informa el mismo en la sección de <a href='http://steambuy.com.ar/informar/' target='_blank'>informar pago</a> enviando una
				foto o imágen del comprobante de transferencia/depósito para que podamos identificarlo. Este se acredita de forma instantánea en horario hábil, y el juego será enviado 
				durante las siguiente 12 horas hábiles luego de haberse acreditado el pago.<br/><br/>";
			}
			if($gameDiscount) {
				$body .= "<strong>El juego posee una oferta de tiempo limitado, por lo tanto deberás informar el pago en la sección de <a href='http://steambuy.com.ar/informar/' target='_blank'>informar pago</a> 
				antes de que finalice esta oferta.</strong> Revisa en el <a href='".$gameSiteUrl."' target='_blank'>sitio de venta</a> del juego la fecha de fin de oferta para saber si debés informar el pago o no.<br/><br/>";
			}
			$body .= "<strong>Recuerda que los pedidos se cancelan automáticamente a los 5 días de generarse o cuando su oferta externa limitada expira sin que se haya recibido el pago.</strong><br/>
			Ante cualquier duda revisa la página de <a href='http://steambuy.com.ar/soporte/' target='_blank'>soporte</a> o <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a>.<br/><br/>
			Un saludo.";
			
			$mail = new PHPMailer;
			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();
			$mail->Host = 'box756.bluehost.com'; 
			$mail->SMTPAuth = true; 
			$mail->Username = 'info@steambuy.com.ar';
			$mail->Password = '03488639268';
			$mail->SMTPSecure = 'tls';
			$mail->From = 'info@steambuy.com.ar';
			$mail->FromName = 'SteamBuy';
			$mail->addAddress($clientEmail,$clientName);
			$mail->addReplyTo('contacto@steambuy.com.ar', 'Contacto SteamBuy');
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = strip_tags($body);
			
			if(!$mail->send()) $orderInfo["mailsent"] = 0;
			else $orderInfo["mailsent"] = 1;
			
			echo json_encode($orderInfo);

		} else echo "Error: ". $order->error; 	
			
	} else if($_POST["type"] == 2 && isset($_POST["payment_method"]) && isset($_POST["paypal_tosend"]) && isset($_POST["client_name"]) && 
	isset($_POST["client_email"]) && isset($_POST["client_ip"]) && isset($_POST["remember_data"])) 
	{ 
		
		$payment_method = $_POST["payment_method"];
		$clientName = $_POST["client_name"];
		$clientEmail = $_POST["client_email"];
		$paypalToSend = $_POST["paypal_tosend"];
		$paypalToGet = round($paypalToSend - ($paypalToSend * 0.054 + 0.3), 2);
		
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
		
		// monto a enviar / medio de pago
		if($payment_method != 1 && $payment_method != 2) {
			echo "Error: Medio de pago no válido."; return;	
		}
		if(is_numeric($paypalToSend) && $paypalToSend != "") {
			if($paypalToSend < 1 || $paypalToSend > 200) {
				echo "Error: El monto a enviar no debe ser mayor a 200 usd ni menor a 1 usd."; return;	
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
		
		if($payment_method == 1) { // boleta
			$paypaylToPay = round(-1.05086 * (-1 * ($paypalToSend * (getDollarQuote() + $paypal_quote_profit) * $imp + 3) - 1.5125), 1);	
		} else if($payment_method == 2) { // transf
			$paypaylToPay = round(1.015 * ($paypalToSend * (getDollarQuote() + $paypal_quote_profit) * $imp + 3), 1);	
		}
		
		if(!is_numeric($paypaylToPay)) {
			echo "Error: Error en cálculo de precio"; return;	
		}
		
		// ****** Crear orden *******
		$order = new order($con);
		if($order->createPayPalOrder($payment_method, $paypalToSend, $paypaylToPay, mysqli_real_escape_string($con, $clientName), 
		mysqli_real_escape_string($con, $clientEmail), mysqli_real_escape_string($con, $_POST["client_ip"]))) 
		{
			
			$orderInfo = $order->orderInfo;
			$orderInfo["paypal_topayars"] = $paypaylToPay;
			$orderInfo["paypal_tosend"] = $paypalToSend;
			
			if($payment_method == 2) {
				$orderInfo["bank_account"] = "Caja de ahorro $ 0849/01118545/07 ";
				$orderInfo["bank_account_cbu"] = "0150849701000118545070";
				$orderInfo["bank_account_owner"] = "Rodrigo Fernandez Nuñez";
				$orderInfo["bank_account_cuil"] = "23-35983336-9";
			}
			
			// *** Enviar email ****
			$subject = "Se ha generado tu pedido de recarga de saldo PayPal";
			$body = "Estimado/a ".$clientName.", se ha generado tu pedido por el envío de saldo PayPal de <strong>".$paypalToSend." usd</strong> (recibiendo a tu cuenta <strong>".$paypalToGet." usd</strong>) por <strong>$".$paypaylToPay." pesos argentinos</strong>.<br/>
			El ID del pedido es <strong>".$order->orderInfo["order_id"]."</strong> y la clave es <strong>".$order->orderInfo["order_password"]."</strong>.<br/>
			El siguiente paso para recibir el saldo es";
			if($payment_method == 1) {
				$body .= " imprimir y abonar en cualquier sucursal de pago la boleta de pago que puedes encontrar en el siguiente link: <br/>
				<a href='".$order->orderInfo["order_purchaseticket"]."' target='_blank'>".$order->orderInfo["order_purchaseticket"]."</a>.<br/>
				Una vez abonado, el pago tomará entre 12 y 48 horas en acreditarse, y el saldo será enviado el día en que se acredita el pago (por lo general al día siguiente 
				de abonar).<br/><br/>";
			} else if($payment_method == 2) {
				$body .= " realizar una transferencia o depósito bancario a la siguiente cuenta:<br/><br/>
				<strong>Banco:</strong> ICBC<br/>
				<strong>Cuenta:</strong> Caja de ahorro $ 0849/01118545/07<br/>
				<strong>CBU:</strong> 0150849701000118545070<br/>
				<strong>Titular:</strong> Rodrigo Fernandez Nuñez<br/>
				<strong>CUIL:</strong> 23-35983336-9<br/>
				<strong>Monto:</strong> &#36;".$paypaylToPay." (Pesos argentinos)<br/><br/>
				Una vez realizado el pago, informa el mismo en la sección de <a href='http://steambuy.com.ar/informar/' target='_blank'>informar pago</a> enviando una
				foto o imágen del comprobante de transferencia/depósito para que podamos identificarlo. Este se acredita de forma instantánea en horario hábil, y el saldo será enviado 
				durante las siguiente 12 horas hábiles luego de haberse acreditado el pago.<br/><br/>";
			}
			$body .= "<strong>Recuerda que los pedidos se cancelan automáticamente a los 5 días de generarse sin que se haya recibido el pago.</strong><br/>
			Ante cualquier duda revisa la página de <a href='http://steambuy.com.ar/soporte/' target='_blank'>soporte</a> o <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a>.<br/><br/>
			Un saludo.";
			
			$mail = new PHPMailer;
			$mail->CharSet = 'UTF-8';
			$mail->isSMTP();
			$mail->Host = 'box756.bluehost.com'; 
			$mail->SMTPAuth = true; 
			$mail->Username = 'info@steambuy.com.ar';
			$mail->Password = '03488639268';
			$mail->SMTPSecure = 'tls';
			$mail->From = 'info@steambuy.com.ar';
			$mail->FromName = 'SteamBuy';
			$mail->addAddress($clientEmail,$clientName);
			$mail->addReplyTo('contacto@steambuy.com.ar', 'Contacto SteamBuy');
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = strip_tags($body);
			
			if(!$mail->send()) $orderInfo["mailsent"] = 0;
			else $orderInfo["mailsent"] = 1;
			
			
			echo json_encode($orderInfo);
			
		} else echo "Error: ". $order->error; 	
			
	}

}
?>