<?php
/*
$data: order_id, product_name, change_type
si change_type=1: receiver_name, new_product_name, new_order_price
si change_type=2: old_buyer_email, new_buyer_email
si change_type=3: old_buyer_name, new_buyer_name
*/

if(!isset($data)) return false;

if($data["change_type"] == 1) { // Cambio producto

	$subject = "Tu pedido por el juego ".$data["product_name"]." ha sido cambiado por el ".$data["new_product_name"];
	echo "Estimado/a ".$data["receiver_name"].", el pedido ID <strong>".$data["order_id"]."</strong> que realizaste por el juego <strong>".$data["product_name"]."</strong> 
	ha sido modificado por el/los juego/s <strong>".$data["new_product_name"]."</strong>";			
	if($data["new_order_price"] != "" && is_numeric($data["new_order_price"])) {
		echo ", con un valor de &#36;".$data["new_order_price"]." pesos.";
	} else echo ".";
	echo "<br/>Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago, y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado, o si está acreditado, deberá llegar dentro del día de hoy.<br/>
	<br/>
	Un saludo,<br/>
	El equipo de SteamBuy";	
			
} else if($data["change_type"] == 2) { // Cambio e-mail destinatario juego

	$subject = "Se ha modificado el e-mail de envío del pedido por el ".$data["product_name"];
	echo "Estimado/a, hemos recibido la solicitud para modificar el e-mail del destinatario del pedido ID <strong>".$data["order_id"]."</strong> por el juego <strong>".$data["product_name"]."</strong>.<br/>
	El e-mail anterior era <strong>".$data["old_buyer_email"]."</strong>, y el nuevo e-mail es <strong>".$data["new_buyer_email"]."</strong>. A este nuevo e-mail se enviarán los productos comprados.<br/><br/>
	Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago, y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado.<br/>
	<br/>
	Un saludo,<br/>
	El equipo de SteamBuy";	
	
} else if($data["change_type"] == 3) { // Cambio nombre destinatario

	$subject = "Se ha modificado el nombre del titular del pedido por el juego ".$data["product_name"];
	echo "Estimado/a, hemos recibido la solicitud para modificar el nombre del receptor/comprador del pedido ID <strong>".$data["order_id"]."</strong> por el juego <strong>".$data["product_name"]."</strong>.<br/>
	El nombre anterior es <strong>".$data["old_buyer_name"]."</strong>, y el nuevo nombre es <strong>".$data["new_buyer_name"]."</strong>.<br/>
	<br/>
	Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago, y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego de acreditado.<br/>
	<br/>
	Un saludo,<br/>
	El equipo de SteamBuy";	
	
}
	

?>