<?php

define("ROOT_LEVEL", "../../../");

if(!isset($_POST["action"]) || !isset($_POST["orders"]) || !isset($_POST["key"])) exit;


if($_POST["key"] != "v4d87s3nb12k8f2c7f21b4u1rff8s1yh3") return;


require_once("../../../global_scripts/php/mysql_connection.php");
require_once("../../../global_scripts/php/main_purchase_functions.php");
require_once("../../../global_scripts/email/mailer.php");

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
		
		$mail_data = array(
			"receiver_name"=>$orderInfo["buyer_name"], 
			"order_id"=>$orderInfo["order_id"], 
			"product_name"=>$orderInfo["product_name"],
			"cancel_reason"=>$_POST["reason"]);
		
		$mail = new Mail;
		$mail->prepare_email("admin/pedido_cancelado", $mail_data);
		$mail->add_address($orderInfo["buyer_email"], $orderInfo["buyer_name"]);
			
		if(!$mail->send()) {
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

		$mail_data = array(
			"receiver_name"=>$orderInfo["buyer_name"], 
			"order_id"=>$orderInfo["order_id"], 
			"product_name"=>$orderInfo["product_name"],
			"expiration_type"=>3);
		
		$mail = new Mail;
		$mail->prepare_email("admin/pedido_expirado", $mail_data);
		$mail->add_address($orderInfo["buyer_email"], $orderInfo["buyer_name"]);
		
		if($mail->send()) {
			if(cancelOrder($orderid)) {
				$orders_successful += 1;
			} else {
				$result["error"] = 1;
				$result["error_text"] = "No se pudo cancelar pedido ".$orderid.". (Mail enviado)";
				break;		
			}			
		} else {
			$result["error"] = 1;
			$result["error_text"] = "No se pudo enviar e-mail del pedido ".$orderid.". Error: ".$mail["error_text"];
			break;				
		}
		

	} else if($_POST["action"] == "concrete") {

		$mail_data = array(
			"receiver_name"=>$orderInfo["buyer_name"],
			"receiver_email"=>$orderInfo["buyer_email"],
			"order_id"=>$orderInfo["order_id"], 
			"product_name"=>$orderInfo["product_name"]
			);
		
		$mail = new Mail;
		$mail->prepare_email("admin/pedido_concretado", $mail_data);
		$mail->add_address($orderInfo["buyer_email"], $orderInfo["buyer_name"]);
		
		if($mail->send()) {
			mysqli_query($con, "UPDATE orders SET `order_status`=2, `order_status_change`=NOW() WHERE `order_id`='".$orderid."'");
			$orders_successful += 1;
			if($orderInfo["order_paymentmethod"] == 1) deleteReceipt($orderInfo["order_informed_image"]);			
		} else {
			$result["error"] = 1;
			$result["error_text"] = "No se pudo enviar e-mail del pedido ".$orderid.". Error: ".$mail["error_text"];
			break;					
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



?>