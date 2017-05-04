<?php
require_once "../../../config.php";
require_once ROOT."app/lib/user-page-preload.php";

require_once ROOT."app/lib/Order.class.php";
require_once ROOT."app/lib/FileUpload.class.php";


$error = 0; // 1:no logueado, 2:id no enviada, 3:el pedido no existe o pertenece, 4:el pedido no necesita informe de pago, 5:error con el archivo, 6:error guardando archivo


// Procesar infome de pago

if($loggedUser) {
	
	// Verificamos que el pedido cumpla con las condiciones necesarias para ser informado.
	if(isset($_POST["orderid"])) {
		
		$order = new Order($con, $_POST["orderid"]);
			
		if($order->exists() && $order->belongs_to_user($loggedUser->userData["id"])) {
			
			if($order->orderData["order_status"] == 1 && ($order->orderData["order_paymentmethod"] == 2 || ($order->orderData["product_limited_discount"] == 1 && $order->orderData["order_confirmed_payment"] == 0))) {
				
				$fileupload = new FileUpload("inform-image");
				if($fileupload->valid_file(2, "img")) {
					
					if($fileupload->save_file($_POST["orderid"], ROOT_PUBLIC."data/img/payment_receipts/")) {
						
						// Registrar informe de pago
						$order->register_payment_inform($fileupload->file_name);
				
					} else $error = 6; 

				} else $error = 5;

			} else $error = 4;
				
		} else $error = 3;

	} else $error = 2;

} else $error = 1;



if($error == 0) {
	$result_title = "Archivo subido - SteamBuy";
	$result_message = "<div class='alert alert-success page-message'>El informe de pago se realizó correctamente. <a href='javascript:window.history.back();'>Volver</a></div>";
} else {
	$result_title = "Error subiendo archivo - SteamBuy";
	
	$result_message = "<div class='alert alert-danger page-message'>";
	if($error == 1) $result_message .= "No estás logueado.";
	else if($error == 2) $result_message .= "Se ha producido un error.";
	else if($error == 3) $result_message .= "El pedido no existe o no pertenece a tu cuenta.";	
	else if($error == 4) $result_message .= "El pedido no necesita informe de pago.";	
	else if($error == 5) $result_message .= $fileupload->uploadError;	
	else if($error == 6)  $result_message .= "Hubo un error guardando el archivo.";
	$result_message .= " <a href='javascript:window.history.back();'>Volver atrás</a></div>";	
}


$template = new DisplayTemplate;

$template->insert_content($result_message, $result_title);

$template->display_rendered_html();


?>