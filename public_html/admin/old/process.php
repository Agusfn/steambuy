<?php
session_start();

ini_set('max_execution_time', 150);

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/mysql_connection.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once(ROOT."app/lib/purchase-functions.php");
require_once("../global_scripts/email/mailer.php");


if(!isAdminLoggedIn()) exit;


/* 
Parámetros: orderid, action, data, notify, redir.

Orderid:	ID Del pedido a operar.
Action:
			1=Cancelar pedido. data: (cancel_reason)
			2=Expirar pedido. data: (exp_type, inform_status, offer_endtime, reject_reason)
			3=Cambiar pedido. data: (change_type, new_product_name, new_order_price, new_buyer_email, new_buyer_name)
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


if(!isset($_POST["orderid"]) || !isset($_POST["action"]) || !isset($_POST["data"]) || !isset($_POST["notify"]) || !isset($_POST["redir"])) exit;

$action = $_POST["action"];	
$orderid = $_POST["orderid"];
$notify = $_POST["notify"];
$safeorderid = mysqli_real_escape_string($con, $orderid);

$res = mysqli_query($con, "SELECT * FROM `orders` WHERE `order_id` = '".$safeorderid."'");
	
if(mysqli_num_rows($res) != 1) {
	echo "Se encontraron ".mysqli_num_rows($res)." pedidos con el ID ".$safeorderid;
	return;
}
	
$orderData = mysqli_fetch_assoc($res);
$op_data = json_decode($_POST["data"], true);

if($notify) {
	$mail = new Mail;
	$mail->reportFailure = true;
	$mail->add_address($orderData["buyer_email"], $orderData["buyer_name"]);
	$email_data = array(
		"order_id"=>$orderid, 
		"receiver_name"=>$orderData["buyer_name"], 
		"product_name"=>$orderData["product_name"]);
}

$sql = "";
switch($action) {
	
	case 1: // cancelar
		
		if($orderData["order_status"] != 1) {
			echo "El pedido no está activo, no puede cancelarse";
			return;
		}
			
		$email_data["cancel_reason"] = $op_data["cancel_reason"];
		
		if($notify) {
			$mail->prepare_email("admin/pedido_cancelado", $email_data);
			if($mail->send()) {
				cancelOrder($orderid);	
			} else return;	
		} else cancelOrder($orderid);	
			
		break;
			
	case 2: // expirar

		if($orderData["order_status"] != 1) {
			echo "El pedido no está activo, no puede cancelarse";
			return;
		}
		if($notify) {
			$email_data["expiration_type"] = $op_data["exp_type"]; // exp type:  1= 5 días, 2= oferta finalizada.
			if($op_data["exp_type"] == 2) {
				$email_data["inform_status"] = $op_data["inform_status"];
				$email_data["offer_endtime"] = $op_data["offer_endtime"];
				if($op_data["inform_status"] == 2) {
					$email_data["reject_reason"] = $op_data["reject_reason"];
				} else if($op_data["inform_status"] == 3) {
					$email_data["order_informed_date"] = $orderData["order_informed_date"];
				}
			}
		
			$mail->prepare_email("admin/pedido_expirado", $email_data);
			if($mail->send()) {
				cancelOrder($orderid);	
			} else return;
		} else cancelOrder($orderid);

		break;	
			
	case 3: // cambiar pedido
		
		$email_data["change_type"] = $op_data["change_type"];
			
		if($op_data["change_type"] == 1) { // Cambio producto
			
			if($notify) {
				$email_data["new_product_name"] = $op_data["new_product_name"];
				$email_data["new_order_price"] = $op_data["new_order_price"];
			}	
			$new_price = "";
			if($op_data["new_order_price"] != "" && is_numeric($op_data["new_order_price"])) $new_price = ", `product_arsprice`=".$op_data["new_order_price"];
			$sql = "UPDATE `orders` SET `product_name`='".mysqli_real_escape_string($con, $op_data["new_product_name"])."'".$new_price." WHERE `order_id`='".$safeorderid."'";
				
		} else if($op_data["change_type"] == 2) { // cambio email comp.
			
			if($notify) {
				$email_data["old_buyer_email"] = $orderData["buyer_email"];
				$email_data["new_buyer_email"] = $op_data["new_buyer_email"];
				$mail->add_address($op_data["new_buyer_email"], "Comprador");
			}		
			$sql = "UPDATE `orders` SET `buyer_email`='".mysqli_real_escape_string($con, $op_data["new_buyer_email"])."' WHERE `order_id`='".$safeorderid."'";
				
		} else if($op_data["change_type"] == 3) { // cambio nombre comp.
			
			if($notify) {
				$email_data["old_buyer_name"] = $orderData["buyer_name"];
				$email_data["new_buyer_name"] = $op_data["new_buyer_name"];
			}
			$sql = "UPDATE `orders` SET `buyer_name`='".mysqli_real_escape_string($con, $op_data["new_buyer_name"])."' WHERE `order_id`='".$safeorderid."'";
		}
		if($notify) {
			$mail->prepare_email("admin/pedido_cambiado", $email_data);
			if(!$mail->send()) return;
		}
		
		break;
			
	case 4: // reservar pedido
	
		if($orderData["order_reserved_game"] == 1) {
			echo "El pedido ya está reservado";
			return;
		}
		
		$sql = "UPDATE orders SET `order_reserved_game`=1 WHERE `order_id`='".$safeorderid."'";
		if($notify) {
			$mail->prepare_email("admin/pedido_reservado", $email_data);
			if(!$mail->send()) return;
		}
		
		break;
			
	case 5: // concretar pedido
	
		if($orderData["order_status"] != 1) {
			echo "El pedido no está activo, no puede marcarse como concretado";
			return;
		}
		
		$sql = "UPDATE orders SET `order_status`=2, `order_status_change`=NOW() WHERE `order_id`='".$safeorderid."'";
		
		if($notify) {
			$email_data["receiver_email"] = $orderData["buyer_email"];
			$mail->prepare_email("admin/pedido_concretado", $email_data);
			if($mail->send()) {
				if($orderData["order_paymentmethod"] == 1) deleteReceipt($orderData["order_informed_image"]);
			} else return;			
		} else {
			if($orderData["order_paymentmethod"] == 1) deleteReceipt($orderData["order_informed_image"]);
		}

		break;
			
	case 6: // concretar con keys
	
		if($orderData["order_status"] != 1) {
			echo "El pedido no está activo, no puede marcarse como concretado";
			return;
		}
		
		$products = preg_split('/\n|\r\n?/', $op_data["product_keys"]);
		$listed_keys = "";
		$key_used_msg = "";
	
		for($i=0;$i<sizeof($products);$i++) { // revisar cada key

			$split = explode("==", $products[$i]);
			if(sizeof($split) != 2) {
				echo "Hay uno o más juegos con formato incorrecto. Formato: Juego==Key. Asegurarse que no hayan líneas extra.<br/><br/>
				keys enviadas:<br/><textarea>".$op_data["product_keys"]."</textarea>";
				exit;	
			}
	
			$sql2 = "SELECT * FROM `orders` WHERE `order_status` = 2 AND `order_sentkeys` LIKE '%".mysqli_real_escape_string($con, $split[1])."%'";
			$res2 = mysqli_query($con, $sql2);
				
			if(mysqli_num_rows($res2) > 0) {
				$key_used_msg .= "<br/><br/><strong>Se ha/n encontrado otro/s pedido/s con la clave o link ".$split[1].":</strong><br/><br/>";
				while($oData = mysqli_fetch_assoc($res2)) {
					$key_used_msg .= "<strong>ID:</strong> ".$oData["order_id"]."<br/><strong>Keys:</strong><br/> ".nl2br($oData["order_sentkeys"])."<br/><br/>";
				}
			} else {
				if(substr($split[1],0,4) === "http" || substr($split[1],0,22) === "store.steampowered.com") {
					$listed_keys .= $split[0].": <strong><a href='".$split[1]."' target='_blank'>".$split[1]."</a></strong><br/>";
				} else {
					$listed_keys .= $split[0].": <strong>".$split[1]."</strong><br/>";
				}
			}
		}
			
		if($key_used_msg == "") {
			
			$sql = "UPDATE orders SET `order_status`=2, `order_status_change`=NOW(), `order_sentkeys`='".mysqli_real_escape_string($con, $op_data["product_keys"])."' 
			WHERE `order_id`='".$safeorderid."'";
			
			if($notify) {
				$email_data["listed_keys"] = $listed_keys;
				$mail->prepare_email("admin/pedido_concretado_keys", $email_data);
				if($mail->send()) {
					if($orderData["order_paymentmethod"] == 1) deleteReceipt($orderData["order_informed_image"]);
				} else return;				
			} else {
				if($orderData["order_paymentmethod"] == 1) deleteReceipt($orderData["order_informed_image"]);
			}
			
		} else {
			echo $key_used_msg;
			return;
		}

		break;
	
	case 7: // rechazar informe pago
	
		if($orderData["order_informedpayment"] == 0) {
			echo "El informe de pago no puede rechazarse porque no está informado";
			return;
		}
		
		$sql = "UPDATE orders SET `order_informedpayment`=0, `order_informed_date`='0000-00-00 00:00:00', `order_informed_image`='' WHERE `order_id`='".$safeorderid."'";
		
		if($notify) {
			$email_data["reject_reason"] = $op_data["reject_reason"];
			$mail->prepare_email("admin/informe_pago_rechazado", $email_data);
			if(file_exists("../data/img/payment_receipts/".$orderData["order_informed_image"])) {
				$mail->add_attachment("../data/img/payment_receipts/".$orderData["order_informed_image"]);
			}
			if($mail->send()) {
				deleteReceipt($orderData["order_informed_image"]);
			} else return;			
		} else deleteReceipt($orderData["order_informed_image"]);

		break;
			
	case 8: // reactivar pedido
	
		if($orderData["order_status"] != 3) {
			echo "El pedido no está cancelado, no puede reactivarse";
			return;
		}
		
		$sql = "UPDATE orders SET `order_status`=1, `order_status_change`='0000-00-00 00:00:00' WHERE `order_id`='".$safeorderid."'";
		if($notify) {
			$mail->prepare_email("admin/pedido_reactivado", $email_data);
			if(!$mail->send()) return false;
		}
		
		break;
			
	case 9: // reactivar y cambiar pedido
		
		if($orderData["order_status"] != 3) {
			echo "El pedido no está cancelado, no puede reactivarse";
			return;
		}		
		
		$new_price = "";
		if($op_data["new_order_price"] != "" && is_numeric($op_data["new_order_price"])) $new_price = ", `product_arsprice`=".$op_data["new_order_price"];
		$sql = "UPDATE `orders` SET `product_name`='".mysqli_real_escape_string($con, $op_data["new_product_name"])."'".$new_price.", `order_status`=1, `order_status_change`='0000-00-00 00:00:00' WHERE `order_id`='".$safeorderid."'";
			
		if($notify) {
			$email_data["old_product_name"] = $orderData["product_name"];
			$email_data["new_product_name"] = $op_data["new_product_name"];
			$email_data["new_order_price"] = $op_data["new_order_price"];
				
			$mail->prepare_email("admin/pedido_reactivado_cambiado", $email_data);
			if(!$mail->send()) return false;			
		}

		break;
}
	
	
if($sql != "") mysqli_query($con, $sql);	


if($action == 3 || $action == 8 || $action == 9) { // Si es un cambio de productos o una reactivacion, volver al pedido
	header("Location: pedido.php?orderid=".$safeorderid);
} else {
	if($_POST["redir"] != "") header("Location: ".$_POST["redir"]);
	else header("Location: pedidos.php");	
}



?>