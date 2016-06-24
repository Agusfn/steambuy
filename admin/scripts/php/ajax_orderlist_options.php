<?php

define("ROOT_LEVEL", "../../../");

if(!isset($_POST["action"]) || !isset($_POST["orders"]) || !isset($_POST["key"])) exit;


if($_POST["key"] != "v4d87s3nb12k8f2c7f21b4u1rff8s1yh3") return;


require_once("../../../global_scripts/php/mysql_connection.php");
require_once("../../../global_scripts/php/main_purchase_functions.php");
require_once("../../../global_scripts/PHPMailer/PHPMailerAutoload.php");


$orders = json_decode($_POST["orders"]);



$result = array("error"=>"0", "error_text"=>""); // Error: 0=sin errores, 1=con errores

$orders_successful = 0;

foreach($orders as $orderid) {
	
	$query = mysqli_query($con, "SELECT * FROM orders WHERE order_id = '".mysqli_real_escape_string($con, $orderid)."' AND order_status = 1");
	if(mysqli_num_rows($query) == 1) {
		$orderInfo = mysqli_fetch_assoc($query);
	} else {
		$result["error"] = 1;
		$result["error_text"] = "No se encontró pedido activo ".$orderid;
		break;
	}
	
	if($_POST["action"] == "cancel" && isset($_POST["reason"])) {
		
		$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha sido cancelado";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", el pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
		ha sido cancelado debido a: ".$_POST["reason"]."<br/><br/>
		Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para solicitar un cambio de producto o un reembolso.<br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
		
		$mail = sendEmail($orderInfo["buyer_email"], $orderInfo["buyer_name"], $subject, $body);
			
		if($mail["error"]) {
			$result["error"] = 1;
			$result["error_text"] = "No se pudo enviar e-mail del pedido ".$orderid.". Error: ".$mail["error_text"];
			break;		
		} else {
			if(cancelOrder($orderid)) {
				$orders_successful += 1;
			} else {
				$result["error"] = 1;
				$result["error_text"] = "No se pudo cancelar pedido ".$orderid.". (Mail enviado)";
				break;		
			}
		}

	} else if($_POST["action"] == "expire") {
		
		$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha expirado";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", el pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
		ha expirado debido a que no se registró el pago luego de 5 días, o porque su oferta limitada finalizó.<br/><br/>
		Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para pedir el producto nuevamente, pedir un reembolso o pedir un cambio de producto. Si no abonaste el pedido ignora este mensaje.<br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
		
		$mail = sendEmail($orderInfo["buyer_email"], $orderInfo["buyer_name"], $subject, $body);
			
		if($mail["error"]) {
			$result["error"] = 1;
			$result["error_text"] = "No se pudo enviar e-mail del pedido ".$orderid.". Error: ".$mail["error_text"];
			break;		
		} else {
			if(cancelOrder($orderid)) {
				$orders_successful += 1;
			} else {
				$result["error"] = 1;
				$result["error_text"] = "No se pudo cancelar pedido ".$orderid.". (Mail enviado)";
				break;		
			}
		}

	} else if($_POST["action"] == "concrete") {
		
		$subject = "Hemos registrado tu pago y se ha enviado tu ".$orderInfo["product_name"];
		$body = "Estimado/a ".$orderInfo["buyer_name"].", hemos registrado el pago de tu pedido ID <strong>".$orderInfo["order_id"]."</strong> y tu juego <strong>".$orderInfo["product_name"]."</strong>
		ha sido enviado a esta dirección e-mail por medio de Steam, en formato 'Steam Gift'. Si no sabes como activar un juego en tu cuenta de Steam revisa la siguiente <a href='http://steambuy.com.ar/faq/#10' target='_blank'>guía</a>.<br/>";
		if(strpos($orderInfo["buyer_email"],"gmail.com") !== false) {
			$body.="Si usas Gmail, revisa en la sección de &quot;promociones&quot; por el mensaje con las instrucciones de activación.<br/><br/>";
		}
		$body .= "<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong><br/><br/>
		Un saludo y gracias por comprar,<br/>
		El equipo de SteamBuy";

		$mail = sendEmail($orderInfo["buyer_email"], $orderInfo["buyer_name"], $subject, $body);

		if($mail["error"]) {
			$result["error"] = 1;
			$result["error_text"] = "No se pudo enviar e-mail del pedido ".$orderid.". Error: ".$mail["error_text"];
			break;		
		} else {
			mysqli_query($con, "UPDATE orders SET `order_status`=2, `order_status_change`=NOW() WHERE `order_id`='".$orderid."'");
			$orders_successful += 1;
			if($orderInfo["order_paymentmethod"] == 1) deleteReceipt($orderInfo["order_informed_image"]);
		}

	} else {
		$result["error"] = 1;
		$result["error_text"] = "No se aporto una accion correcta";
	}
	


}

if($result["error"] == 1) {
	$result["error_text"] .= "\n".$orders_successful." pedidos procesados.";
}

echo json_encode($result);



function sendEmail($toemail, $toname, $subject, $body) {
	
	$result = array("error"=>false, "error_text"=>"");
	
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
	$mail->addAddress($toemail, $toname);
	$mail->addReplyTo('contacto@steambuy.com.ar', 'Contacto SteamBuy');
	$mail->isHTML(true);
	$mail->Subject = $subject;
	$mail->Body    = $body;
	$mail->AltBody = strip_tags($body);
	
	if(!$mail->send()) { 
		$result["error"] = true; 
		$result["error_text"] = $mail->ErrorInfo; 
	}
	return $result;
	
}


?>