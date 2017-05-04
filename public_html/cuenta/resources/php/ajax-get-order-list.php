<?php
require_once "../../../../config.php";

session_start();

require_once ROOT."app/lib/mysql-connection.php";
require_once ROOT."app/lib/UserLogin.class.php";


$results_per_page = 20; // Cantidad de resultados por página.

sleep(1);

$login = new UserLogin($con);
$loggedUser = $login->user_logged_in();

if($loggedUser) {
	
	if(isset($_POST["page"])) {
		if(is_numeric($_POST["page"])) $page = $_POST["page"];
		else $page = 1;	
	} else {
		$page = 1;	
	}
	
	$sql = "SELECT * FROM `orders` WHERE `associated_userid` = ".$loggedUser->userData["id"]." ORDER BY `order_date` DESC LIMIT ".(($page-1)*20).",".$results_per_page;
	$query = mysqli_query($con, $sql);
	if(mysqli_num_rows($query) == 0) {
		
		echo "<div style='text-align: center; margin-top: 55px;font-size: 16px;'>No realizaste pedidos, empezá a comprar desde <a href='".PUBLIC_URL."'>nuestro catálogo</a>.</div>";
		
	} else {
		
		$order_list_html = "<table class='table table-striped orders-table'><thead><tr><th>ID pedido</th><th>Fecha</th><th>Estado</th><th>Producto</th><th>Precio ARS</th><th>Medio<br/>pago</th><th></th></tr></thead><tbody>";
		while($order = mysqli_fetch_assoc($query)) {
			$order_list_html .= order_list_row($order);
		}
		$order_list_html .= "</tbody></table>";
		echo $order_list_html;
	}
	
	exit;
	
} else {
	echo "Se ha producido un error cargando los datos, actualiza la página.";
	exit;	
}


function order_list_row($orderData) {

	// Estado del pedido
	if($orderData["order_status"] == 1) {
		
		if($orderData["order_paymentmethod"] == 1) {
			if($orderData["order_confirmed_payment"] == 0) {
				$order_status = "<span style='font-size:13px;color:#555'>Esperando pago <span class='glyphicon glyphicon-time'></span></span>";
			} else if($orderData["order_confirmed_payment"] == 1) {
				$order_status = "<span style='font-size:13px;color:#555;text-decoration: underline;text-decoration-style: dashed;' data-toggle='tooltip' data-placement='top' title='Deberías estar recibiendo el pedido en el día en que se acredita el pago, o como máximo a las 48hs siguientes de haberse acreditado.'>Envío en proceso <span class='glyphicon glyphicon-time'></span></span>";
			}
		} else if($orderData["order_paymentmethod"] == 2) {
			if($orderData["order_informedpayment"] == 0) {
				$order_status = "<span style='font-size:13px;color:#555'>Esperando informe pago</span>";
			} else if($orderData["order_informedpayment"] == 1) {
				$order_status = "<span style='font-size:13px;color:#555;text-decoration: underline;text-decoration-style: dashed;' data-toggle='tooltip' data-placement='top' title='Debemos revisar el pago y enviar el pedido. Deberías estar recibiendo el pedido en las sgtes. 12hs hábiles de acreditado el pago.'>Envío en proceso <span class='glyphicon glyphicon-time'></span></span>";
			}	
		}
		
	} else if($orderData["order_status"] == 2) {
		$send_date = date("d/m/y H:i", strtotime($orderData["order_status_change"]));
		$order_status = "<span style='color: #139200;text-decoration: underline;text-decoration-style: dashed;' data-toggle='tooltip' data-placement='top' title='Enviado el ".$send_date." a ".$orderData["buyer_email"]."'>Enviado</span>";
	} else if($orderData["order_status"] == 3) {
		$order_status = "<span style='color:#B10000;'>Cancelado</span>";
	}
	
	// Forma de pago (y estado del pago/informe de pago)
	if($orderData["order_paymentmethod"] == 1) {
		$payment_method = "<a href='".$orderData["order_purchaseticket"]."' target='_blank'><span class='glyphicon glyphicon-barcode'></span></a>";
		if($orderData["order_confirmed_payment"] == 1) {
			$payment_method .= "<span style='color: #139200;margin-left:15px;' class='glyphicon glyphicon-ok' data-toggle='tooltip' data-placement='top' title='El pago está acreditado'></span>";
		} else if($orderData["order_informedpayment"] == 1) {
			$payment_method .= "<span style='margin-left:15px;' class='glyphicon glyphicon-ok' data-toggle='tooltip' data-placement='top' title='El pago está informado (pendiente de revisión)'></span>";	
		}
	} else if($orderData["order_paymentmethod"] == 2) {
		$payment_method = "<i class='fa fa-university' aria-hidden='true'></i>";
		if($orderData["order_informedpayment"] == 1) {
			$payment_method .= "<span style='margin-left:15px;' class='glyphicon glyphicon-ok' data-toggle='tooltip' data-placement='top' title='El pago está informado".($orderData["order_status"] == 1 ? " (pendiente de revisión)" : "")."'></span>";	
		}
	}
	
	// Botón de acciones
	$actions_btn = "";
	if($orderData["order_status"] == 1) {

		if($orderData["order_paymentmethod"] == 2 || ($orderData["product_limited_discount"] == 1 && $orderData["order_confirmed_payment"] == 0)) {
			if($orderData["order_informedpayment"] == 0) {
				$actions_btn .= "<li><a href='javascript:void(0);' class='inform-payment'>Informar el pago</a></li>";	
			} else if($orderData["order_informedpayment"] == 1) {
				$actions_btn .= "<li><a href='javascript:void(0);' class='inform-payment'>Reenviar informe de pago</a></li>";	
			}
		}
		
		if(!($orderData["order_paymentmethod"] == 1 && $orderData["order_confirmed_payment"] == 1)) {
			$actions_btn .= "<li><a href='javascript:void(0);' class='cancel-order'>Cancelar pedido</a></li>";	
		}
		
		if($actions_btn != "") {
			$actions_btn = "
			<div class='btn-group'>
			  <button type='button' class='btn btn-default dropdown-toggle btn-sm' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'><span class='caret'></span></button>
			  <ul class='dropdown-menu'>".$actions_btn."</ul>
			</div>";			
		}

	}

	$html = "
	<tr>
    	<td class='order-id'>".$orderData["order_id"]."</td>
        <td>".date("d/m/y", strtotime($orderData["order_date"]))."</td>
        <td class='order-status'>".$order_status."</td>
        <td class='product-name'>".$orderData["product_name"]."</td>
        <td>&#36;".$orderData["product_arsprice"]."</td>
		<td style='color:#555;'>".$payment_method."</td>
		<td>".$actions_btn."</td>
   </tr>";
   return $html;
	
}


?>