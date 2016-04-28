<?php
session_start();

ini_set('max_execution_time', 150);

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/mysql_connection.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");
require_once("../global_scripts/PHPMailer/PHPMailerAutoload.php");


if(!isAdminLoggedIn()) exit;


/* 
Parámetros: orderid, action, data, notify, redir.

Orderid:	ID Del pedido a operar.
Action:
			1=Cancelar pedido. data: (cancel_reason)
			2=Expirar pedido. data: (exp_type, inform_status, offer_endtime, reject_reason)
			3=Cambiar pedido. data: (change_type, new_product_name, new_order_price, new_buyer_email, new_buyer_namel, notify)
			4=Marcar como reservado.
			5=Concretar.
			6=Concretar. data: (product_keys)
			7=Rechazar informe de pago. data: (reject_reason)
			8=Reactivar pedido.
			9=Reactivar y cambiar producto. data: (new_product_name, new_order_price)
Data: 		Datos de la operación a realizar con el pedido.
Notify: 	Enviar un e-mail al comprador por la operación.
Redir: 		URL de redirección luego de realizar la operación en el pedido. 
*/


if(isset($_POST["orderid"]) && isset($_POST["action"]) && isset($_POST["data"]) && isset($_POST["notify"]) && isset($_POST["redir"]))
{
	
	$orderid = $_POST["orderid"];
	$safeorderid = mysqli_real_escape_string($con, $orderid);

	$res = mysqli_query($con, "SELECT * FROM `orders` WHERE `order_id` = '".$safeorderid."'");
	if(mysqli_num_rows($res) != 1) {
		echo "Se encontraron ".mysqli_num_rows($res)." pedidos con el ID ".$safeorderid;
		return;
	}
	$orderData = mysqli_fetch_assoc($res);
	$data = json_decode($_POST["data"], true);

	$sql = "";

	if($_POST["action"] == 1) {
		if(notifEmail(1, $data, $orderData)) {
			cancelOrder($orderid);
		} else return;
	} else if($_POST["action"] == 2) {
		if(notifEmail(2, $data, $orderData)) {
			cancelOrder($orderid);
		} else return;
	} else if($_POST["action"] == 3) {
		if($data["change_type"] == 1) { // Cambio productos
			if(notifEmail(3, $data, $orderData)) {
				if($data["new_order_price"] != "" && is_numeric($data["new_order_price"])) {
					$sql = "UPDATE orders SET `product_name`='".mysqli_real_escape_string($con, $data["new_product_name"])."', `product_arsprice`=".$data["new_order_price"]." WHERE `order_id`='".$safeorderid."'";
				} else $sql = "UPDATE orders SET `product_name`='".mysqli_real_escape_string($con, $data["new_product_name"])."' WHERE `order_id`='".$safeorderid."'";
			} else return;		
		} else if($data["change_type"] == 2) { // Cambio e-mail
			if(notifEmail(3, $data, $orderData)) {
				$sql = "UPDATE orders SET `buyer_email`='".mysqli_real_escape_string($con, $data["new_buyer_email"])."' WHERE `order_id`='".$safeorderid."'";
			} else return;
		} else if($data["change_type"] == 3) { // Cambio nombre
			if(notifEmail(3, $data, $orderData)) {
				$sql = "UPDATE orders SET `buyer_name`='".mysqli_real_escape_string($con, $data["new_buyer_name"])."' WHERE `order_id`='".$safeorderid."'";
			} else return;
		}
	} else if($_POST["action"] == 4) {
		if(notifEmail(4, $data, $orderData)) {
			$sql = "UPDATE orders SET `order_reserved_game`=1 WHERE `order_id`='".$safeorderid."'";
		} else return;
	} else if($_POST["action"] == 5) {
		if(notifEmail(5, $data, $orderData)) {
			$sql = "UPDATE orders SET `order_status`=2, `order_status_change`=NOW() WHERE `order_id`='".$safeorderid."'";
			if($orderData["order_paymentmethod"] == 1) deleteReceipt($orderData["order_informed_image"]);
		} else return;
	} else if($_POST["action"] == 6) {
		
		$products = preg_split('/\n|\r\n?/', $data["product_keys"]);
		$listed_keys = "";
		$msg = "";

		for($i=0;$i<sizeof($products);$i++) { // revisar cada key

			$split = explode("==", $products[$i]);
			
			if(!isset($split[1]) || sizeof($split) != 2) {
				echo "Hay uno o más juegos con formato incorrecto. Formato: Juego==Key. Asegurarse que no hayan líneas extra.<br/><br/>
				keys enviadas:<br/><textarea>".$data["product_keys"]."</textarea>";
				exit;	
			}

			$sql2 = "SELECT * FROM `orders` WHERE `order_status` = 2 AND `order_sentkeys` LIKE '%".mysqli_real_escape_string($con, $split[1])."%'";
			$res2 = mysqli_query($con, $sql2);
			
			if(mysqli_num_rows($res2) > 0) {
				$msg .= "<br/><br/><strong>Se ha encontrado otro/s pedido/s con la clave o link ".$split[1].":</strong><br/><br/>";
				while($oData = mysqli_fetch_assoc($res2)) {
					$msg .= "<strong>ID:</strong> ".$oData["order_id"]."<br/><strong>Keys:</strong><br/> ".nl2br($oData["order_sentkeys"])."<br/><br/>";
				}
			} else $listed_keys .= $split[0].": <strong>".$split[1]."</strong><br/>";	
		}
		if($msg == "") {
			if(notifEmail(6, $data, $orderData)) {
				$sql = "UPDATE orders SET `order_status`=2, `order_status_change`=NOW(), 
				`order_sentkeys`='".mysqli_real_escape_string($con, $data["product_keys"])."' WHERE `order_id`='".$safeorderid."'";
				if($orderData["order_paymentmethod"] == 1) deleteReceipt($orderData["order_informed_image"]);
			} else return;
		} else {
			echo $msg;
			exit;
		}
	} else if($_POST["action"] == 7) {
		if(notifEmail(7, $data, $orderData)) {
			$sql = "UPDATE orders SET `order_informedpayment`=0, `order_informed_date`='0000-00-00 00:00:00', `order_informed_image`='' 
			WHERE `order_id`='".$safeorderid."'";
			deleteReceipt($orderData["order_informed_image"]);
		} else return;
	} else if($_POST["action"] == 8) {
		if(notifEmail(8, $data, $orderData)) {
			$sql = "UPDATE orders SET `order_status`=1, `order_status_change`='0000-00-00 00:00:00' WHERE `order_id`='".$safeorderid."'";
		} else return;
	} else if($_POST["action"] == 9) {
		if(notifEmail(9, $data, $orderData)) {		
			if($data["new_order_price"] != "" && is_numeric($data["new_order_price"])) {
				$sql = "UPDATE `orders` SET `product_name`='".mysqli_real_escape_string($con, $data["new_product_name"])."', `product_arsprice`=".$data["new_order_price"].", `order_status`=1, `order_status_change`='0000-00-00 00:00:00' WHERE `order_id`='".$safeorderid."'";
			} else 	$sql = "UPDATE `orders` SET `product_name`='".mysqli_real_escape_string($con, $data["new_product_name"])."', `order_status`=1, `order_status_change`='0000-00-00 00:00:00' WHERE `order_id`='".$safeorderid."'";
		} else return;
	}
	
	if($sql != "" || $_POST["action"] == 1 || $_POST["action"] == 2) {
		if($sql != "") mysqli_query($con, $sql);
		if($_POST["redir"] != "") header("Location: ".$_POST["redir"]);
		else header("Location: index.php");
	}
}

function notifEmail($action, $data, $orderInfo) {
	
	if($action != 4 && $action != 5 && $action != 6) { // Enviar e-mail para los envios de productos y reservas, si o si.
		if($_POST["notify"] == 0) return true;
	}

	global $listed_keys;
		
	$mail = new PHPMailer;
	$mail->CharSet = 'UTF-8';
	$mail->isSMTP();
	$mail->Host = 'box756.bluehost.com'; 
	$mail->Port = 587;  
	$mail->SMTPAuth = true; 
	$mail->Username = 'info@steambuy.com.ar';
	$mail->Password = '03488639268';
	$mail->SMTPSecure = 'SSL';
	//$mail->SMTPDebug = 1;
	$mail->From = 'info@steambuy.com.ar';
	$mail->FromName = 'SteamBuy';
	$mail->addAddress($orderInfo["buyer_email"], $orderInfo["buyer_name"]);
	$mail->addReplyTo('contacto@steambuy.com.ar', 'Contacto SteamBuy');
	
	if($action == 1) {
		$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha sido cancelado";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", el pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
		ha sido cancelado debido a: ".$data["cancel_reason"]."<br/><br/>
		Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para solicitar un cambio de producto o un reembolso.<br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
	} else if($action == 2) {
		
		if($data["exp_type"] == 1) { // expirado 5 días
			$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha expirado";
			$body = "Estimado/a ".$orderInfo["buyer_name"].", tu pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
			ha expirado automáticamente debido a que no se registró el pago pasados 5 días de ser realizado, por lo cual el pedido y la boleta vencieron.<br/><br/>
			Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para gestionar, si es posible, la compra del mismo producto, de lo contrario un cambio de producto o un reembolso.<br/><br/>
			Un saludo.<br/><br/>
			El equipo de SteamBuy";
		} else if($data["exp_type"] == 2) { // expirado fin oferta externa lim.
			if($data["inform_status"] == 1) { // No informó
				$subject = "La oferta externa de tu pedido por el juego ".$orderInfo["product_name"]." ha finalizado y el pedido ha expirado";
				$body = "Estimado/a ".$orderInfo["buyer_name"].", tu pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
				ha expirado debido a que su oferta externa limitada ha finalizado y no has hecho el informe de pago, como se indica que es necesario antes de realizar la compra."; 
				if($data["offer_endtime"] != "") $body .= " La oferta ha finalizado el ".$data["offer_endtime"].".";
				$body .= "<br/><br/>
				Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para para solicitar un cambio de productos y/o abono de la diferencia, o solicitar un reembolso.<br/><br/>
				Un saludo.<br/><br/>
				El equipo de SteamBuy";
			} else if($data["inform_status"] == 2) { // Cbte inválido 
				$subject = "La oferta externa por el juego ".$orderInfo["product_name"]." ha finalizado y tu informe de pago fue inválido";
				$body = "Estimado/a ".$orderInfo["buyer_name"].", tu pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
				ha sido cancelado debido a que la oferta externa limitada del juego ha finalizado y el comprobante de pago enviado no fue válido, razón: ".$data["reject_reason"]."<br/>"; 
				if($data["offer_endtime"] != "") $body .= " La oferta ha finalizado el ".$data["offer_endtime"].".";
				$body .= " Es necesario que se informe el pago enviando el comprobante de pago correcto antes del fin de la oferta, tal como se indica en nuestro sitio web antes de hacer la compra.<br/><br/>
				Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para para solicitar un cambio de productos y/o abono de la diferencia, o solicitar un reembolso.<br/><br/>
				Un saludo.<br/><br/>
				El equipo de SteamBuy";
				if(file_exists("../data/img/payment_receipts/".$orderInfo["order_informed_image"])) {
					$mail->addAttachment("../data/img/payment_receipts/".$orderInfo["order_informed_image"]);
				}
			} else if($data["inform_status"] == 3) { // Informó tarde
				$subject = "La oferta externa por el juego ".$orderInfo["product_name"]." ha finalizado y tu informe de pago fue realizado tarde";
				$body = "Estimado/a ".$orderInfo["buyer_name"].", el informe de pago que realizaste para tu pedido ID <strong>".$orderInfo["order_id"]."</strong> por el juego <strong>".$orderInfo["product_name"]."</strong> 
				fue realizado el ".date("d/m/y H:i:s",strtotime($orderInfo["order_informed_date"])).", y la oferta externa del juego finalizó el ".$data["offer_endtime"].".<br/><br/>
				Es necesario realizar el informe de pago a tiempo, tal como se indica antes de realizar el pedido, por lo tanto esta oferta <strong>ya no es válida para tu pedido</strong>, respondenos a <a href='mailto:contacto@steambuy.com.ar'>nuestro correo</a> para para solicitar un cambio de productos y/o abono de la diferencia, o solicitar un reembolso.<br/><br/>
				Un saludo.<br/><br/>
				El equipo de SteamBuy";
			}
		}
	} else if($action == 3) {
		if($data["change_type"] == 1) {
			$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha sido cambiado por el ".$data["new_product_name"];
			$body = "Estimado/a ".$orderInfo["buyer_name"].", el pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
			ha sido modificado por el/los juego/s <strong>".$data["new_product_name"]."</strong>";			
			if($data["new_order_price"] != "" && is_numeric($data["new_order_price"])) {
				$body .= ", con un valor de $".$data["new_order_price"].".";
			} else $body .= ".";
			$body .= "<br/>Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago, y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado, o si está acreditado, deberá llegar dentro del día de hoy.<br/><br/>
			Un saludo,<br/>
			El equipo de SteamBuy";			
		} else if($data["change_type"] == 2) {
			$mail->addAddress($data["new_buyer_email"], "Comprador");
			$subject = "Se ha modificado el e-mail de envío del pedido por el ".$orderInfo["product_name"];
			$body = "Estimado/a, hemos recibido la solicitud para modificar el e-mail del destinatario del pedido ID <strong>".$orderInfo["order_id"]."</strong> por el juego <strong>".$orderInfo["product_name"]."</strong>.<br/>
			El e-mail anterior es <strong>".$orderInfo["buyer_email"]."</strong>, y el nuevo e-mail es <strong>".$data["new_buyer_email"]."</strong>, a este nuevo e-mail se enviarán los productos comprados.<br/><br/>
			Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago, y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado.<br/><br/>
			Un saludo,<br/>
			El equipo de SteamBuy";	
		} else if($data["change_type"] == 3) {
			$subject = "Se ha modificado el nombre del titular del pedido por el juego ".$orderInfo["product_name"];
			$body = "Estimado/a, hemos recibido la solicitud para modificar el nombre del receptor/comprador del pedido ID <strong>".$orderInfo["order_id"]."</strong> por el juego <strong>".$orderInfo["product_name"]."</strong>.<br/>
			El nombre anterior es <strong>".$orderInfo["buyer_name"]."</strong>, y el nuevo nombre es <strong>".$data["new_buyer_name"]."</strong>.<br/><br/>
			Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago, y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado.<br/><br/>
			Un saludo,<br/>
			El equipo de SteamBuy";	
		}
	
	} else if($action == 4) {
		$subject = "Tu juego ".$orderInfo["product_name"]." pedido ha sido reservado";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", el juego <strong>".$orderInfo["product_name"]."</strong> de tu pedido pedido ID <strong>".$orderInfo["order_id"]."</strong> ha sido reservado,
		de esta forma no te perderás la oferta externa limitada.<br/>
		Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago (entre 12 y 48hs luego de pagar), y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado. Si tu pago ya está acreditado
		el pedido debería ser enviado durante el día de hoy.<br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
	} else if($action == 5) {
		$subject = "Hemos registrado tu pago y se ha enviado tu ".$orderInfo["product_name"];
		$body = "Estimado/a ".$orderInfo["buyer_name"].", hemos registrado el pago de tu pedido ID <strong>".$orderInfo["order_id"]."</strong> y tu juego <strong>".$orderInfo["product_name"]."</strong>
		ha sido enviado a esta dirección e-mail por medio de Steam, en formato 'Steam Gift'. Si no sabes como activar un juego en tu cuenta de Steam revisa la siguiente <a href='http://steambuy.com.ar/faq/#10' target='_blank'>guía</a>.<br/>";
		if(strpos($orderInfo["buyer_email"],"gmail.com") !== false) {
			$body.="Si usas Gmail, revisa en la sección de &quot;promociones&quot; por el mensaje con las instrucciones de activación.<br/><br/>";
		}
		$body .= "<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong><br/><br/>
		Un saludo y gracias por comprar,<br/>
		El equipo de SteamBuy";
	} else if($action == 6) {
		if(strpos($data["product_keys"], "humblebundle.com") !== false) {
			$hasHumble = true;
		} else $hasHumble = false;
		
		$subject = "Hemos registrado tu pago y has recibido el ".$orderInfo["product_name"]." de SteamBuy";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", hemos registrado el pago de tu pedido ID <strong>".$orderInfo["order_id"]."</strong> por el juego <strong>".$orderInfo["product_name"]."</strong>,
		las claves de activación o links para activar tu producto las puedes encontrar a continuación:<br/><br/>
		".$listed_keys."<br/>
		Si no sabes como activar una clave de activación en Steam u Origin puedes ver la siguiente <a href='http://steambuy.com.ar/faq/#24' target='_blank'>guía</a>. ";
		if($hasHumble) $body .= "Para saber cómo activar productos de Humble Bundle puedes ver esta otra <a href='http://steambuy.com.ar/faq/#25' target='_blank'>guía</a>.";
		$body .= "<br/><br/>
		<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong><br/><br/>
		Un saludo y gracias por comprar,<br/>
		El equipo de SteamBuy";
	} else if($action == 7) {
		$subject = "El informe de pago enviado para la compra de ".$orderInfo["product_name"]." ha sido rechazado";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", el informe de pago realizado para la compra del juego <strong>".$orderInfo["product_name"]."</strong> (pedido ID <strong>".$orderInfo["order_id"]."</strong>)
		ha sido rechazado debido a: ".$data["reject_reason"]."<br/>
		Puedes encontrar adjunta la imágen que enviaste como informe de pago. <strong>Reenvia el informe de pago como corresponde siguiendo la siguiente <a href='http://steambuy.com.ar/faq/#18' target='_blank'>guía</a> para no perderte de la oferta limitada.</strong><br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
		if(file_exists("../data/img/payment_receipts/".$orderInfo["order_informed_image"])) {
			$mail->addAttachment("../data/img/payment_receipts/".$orderInfo["order_informed_image"]);
		}
	} else if($action == 8) {
		$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha sido reactivado";
		$body = "Estimado/a ".$orderInfo["buyer_name"].", tu pedido ID <strong>".$orderInfo["order_id"]."</strong> por el juego <strong>".$orderInfo["product_name"]."</strong> 
		ha sido reactivado.<br/>
		Cuando registremos el pago el pedido será enviado, o si el pago ya está acreditado solamente espera a recibir el producto.<br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
	} else if($action == 9) {
		$subject = "Tu pedido por el juego ".$orderInfo["product_name"]." ha sido reactivado y cambiado por el ".$data["new_product_name"];
		$body = "Estimado/a ".$orderInfo["buyer_name"].", el pedido ID <strong>".$orderInfo["order_id"]."</strong> que realizaste por el juego <strong>".$orderInfo["product_name"]."</strong> 
		ha sido reactivado y modificado por el/los juego/s <strong>".$data["new_product_name"]."</strong>";
		if($data["new_order_price"] != "" && is_numeric($data["new_order_price"])) {
			$body .= ", con un valor de $".$data["new_order_price"].".";
		} else $body .= ".";
		$body .= "<br/>Cuando registremos el pago el pedido será enviado, o si el pago ya está acreditado solamente espera a recibir el producto.<br/><br/>
		Un saludo,<br/>
		El equipo de SteamBuy";
	}

	$mail->isHTML(true);
	$mail->Subject = $subject;
	$mail->Body    = $body;
	$mail->AltBody = strip_tags($body);
	
	if(!$mail->send()) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
		return false;
	} else return true;
}

?>