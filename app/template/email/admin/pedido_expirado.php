<?php
/*
data: receiver_name, order_id, product_name, expiration_type
si expiration_type = 2: inform_status, offer_endtime
	si inform_status = 2: reject_reason
	si inform_status = 3: order_informed_date
*/

if(!isset($data)) return false;

if($data["expiration_type"] == 1) { // expirado 5 días

	$subject = "Tu pedido por el juego ".$data["product_name"]." ha expirado";
	echo "Estimado/a ".$data["receiver_name"].", tu pedido ID <strong>".$data["order_id"]."</strong> que realizaste por el juego <strong>".$data["product_name"]."</strong> 
	ha expirado automáticamente debido a que no se registró el pago pasados 5 días de ser realizado, por lo cual el pedido y la boleta vencieron.<br/>
	<br/>
	Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para gestionar, si es posible, la compra del mismo producto, de lo contrario un cambio de producto o un 
	reembolso.<br/>
	<br/>
	Un saludo.<br/>
	<br/>
	El equipo de SteamBuy";
	
} else if($data["expiration_type"] == 2) { // expirado fin oferta externa lim.
	
	if($data["inform_status"] == 1) { // No informó
		$subject = "La oferta externa de tu pedido por el juego ".$data["product_name"]." ha finalizado y el pedido ha expirado";
		echo "Estimado/a ".$data["receiver_name"].", tu pedido ID <strong>".$data["order_id"]."</strong> que realizaste por el juego <strong>".$data["product_name"]."</strong> 
		ha expirado debido a que su oferta externa limitada ha finalizado y no has hecho el informe de pago, como se indica que es necesario antes de realizar la compra."; 
		if($data["offer_endtime"] != "") echo " La oferta ha finalizado el ".$data["offer_endtime"].".";
		echo "<br/>
		<br/>
		Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para para solicitar un cambio de productos y/o abono de la diferencia, o solicitar un reembolso.<br/>
		<br/>
		Un saludo.<br/>
		<br/>
		El equipo de SteamBuy";
		
	} else if($data["inform_status"] == 2) { // Cbte inválido 
	
		$subject = "La oferta externa por el juego ".$data["product_name"]." ha finalizado y tu informe de pago fue inválido";
		echo "Estimado/a ".$data["receiver_name"].", tu pedido ID <strong>".$data["order_id"]."</strong> que realizaste por el juego <strong>".$data["product_name"]."</strong> 
		ha sido cancelado debido a que la oferta externa limitada del juego ha finalizado y el comprobante de pago enviado no fue válido, razón: ".$data["reject_reason"]."<br/>"; 
		if($data["offer_endtime"] != "") echo " La oferta ha finalizado el ".$data["offer_endtime"].".";
		echo " Es necesario que se informe el pago enviando el comprobante de pago correcto antes del fin de la oferta, tal como se indica en nuestro sitio web antes de hacer la compra.<br/>
		<br/>
		Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para para solicitar un cambio de productos y/o abono de la diferencia, o solicitar un reembolso.<br/>
		<br/>
		Un saludo.<br/>
		<br/>
		El equipo de SteamBuy";
		
	} else if($data["inform_status"] == 3) { // Informó tarde
	
		$subject = "La oferta externa por el juego ".$data["product_name"]." ha finalizado y tu informe de pago fue realizado tarde";
		echo "Estimado/a ".$data["receiver_name"].", el informe de pago que realizaste para tu pedido ID <strong>".$data["order_id"]."</strong> por el juego <strong>".$data["product_name"]."</strong> 
		fue realizado el ".$data["order_informed_date"].", y la oferta externa del juego finalizó el ".$data["offer_endtime"].".<br/>
		<br/>
		Es necesario realizar el informe de pago a tiempo, tal como se indica antes de realizar el pedido, por lo tanto esta oferta <strong>ya no es válida para tu pedido</strong>, respondenos a 
		<a href='mailto:contacto@steambuy.com.ar'>nuestro correo</a> para para solicitar un cambio de productos y/o abono de la diferencia, o solicitar un reembolso.<br/>
		<br/>
		Un saludo.<br/>
		<br/>
		El equipo de SteamBuy";
	}
	
} else if($data["expiration_type"] == 3) { // expiro por cualquiera de los 2 motivos, sin mucha informacion
	
	$subject = "Tu pedido por el juego ".$data["product_name"]." ha expirado";
	echo "Estimado/a ".$data["receiver_name"].", el pedido ID <strong>".$data["order_id"]."</strong> que realizaste por el juego <strong>".$data["product_name"]."</strong> 
	ha expirado debido a que no se registró el pago luego de 5 días, o porque su oferta limitada finalizó.<br/>
	<br/>
	Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para pedir el producto nuevamente, pedir un reembolso o pedir un cambio de producto. Si no abonaste 
	el pedido ignora este mensaje.<br/>
	<br/>
	Un saludo,<br/>
	El equipo de SteamBuy";	
	
}
?>