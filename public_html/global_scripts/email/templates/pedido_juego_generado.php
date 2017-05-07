<?php
/*
$data: receiver_name, order_id, order_password, product_name, order_ars_price, payment_method, product_external_discount, product_sellingsite, product_site_url, order_fromcatalog

si payment_method=1: order_purchaseticket_url
si order_fromcatalog = 1 y product_external_discount = 1: product_external_offer_endtime
*/

if(!isset($data)) return false;


$subject = "Se ha generado tu pedido por el juego ".$data["product_name"];


echo "Estimado/a ".$data["receiver_name"].", se ha generado tu pedido por el juego <strong>".$data["product_name"]."</strong> por <strong>&#36;".$data["order_ars_price"]." 
pesos argentinos</strong>. El ID del pedido es <strong>".$data["order_id"]."</strong> y la clave es <strong>".$data["order_password"]."</strong>.<br/>
El siguiente paso para recibir el juego es";
if($data["payment_method"] == 1) {
	echo " imprimir y abonar en cualquier sucursal de pago la boleta de pago que puedes encontrar en el siguiente link: <br/>
	<a href='".$data["order_purchaseticket_url"]."' target='_blank'>".$data["order_purchaseticket_url"]."</a>.<br/><br/>
	Una vez abonado, el pago tomará entre 12 y 48 horas en acreditarse, y el juego será enviado el día en que se acredita el pago (por lo general al día siguiente 
	de abonar).<br/><br/>";
} else if($data["payment_method"] == 2) {	
	echo " realizar una transferencia o depósito bancario a la siguiente cuenta:<br/><br/>
	<strong>Banco:</strong> ICBC<br/>
	<strong>Cuenta:</strong> Caja de ahorro $ 0849/01118545/07<br/>
	<strong>CBU:</strong> 0150849701000118545070<br/>
	<strong>Titular:</strong> Rodrigo Fernandez Nuñez<br/>
	<strong>CUIL:</strong> 23-35983336-9<br/>
	<strong>Monto:</strong> &#36;".$data["order_ars_price"]." (Pesos argentinos)<br/><br/>
	Una vez realizado el pago, informa el mismo en la sección de <a href='http://steambuy.com.ar/informar/' target='_blank'>informar pago</a> enviando una
	foto o imágen del comprobante de transferencia/depósito para que podamos identificarlo. Este se acredita de forma instantánea en horario hábil, y el juego será enviado 
	durante las siguientes 12 horas hábiles luego de haberse acreditado el pago.<br/>";
}
if($data["product_sellingsite"] == 1 && $data["stock"] == 0) {
	echo "<strong>Para recibir el juego deberás agregarnos a nuestra <a href='http://steamcommunity.com/id/steambuyarg/'>cuenta de Steam</a> a la lista de amigos o aceptar la solicitud que enviaremos a la cuenta proporcionada.</strong><br/><br/>";	
}

if($data["product_external_discount"] == 1) {

	if($data["order_fromcatalog"] == 0) { // formulario compra


		if($productData["product_sellingsite"] == 1) {
			echo "Este juego posee una oferta limitada de reventa de Steam. Es necesario que el pago se encuentre <strong>acreditado con tiempo antes de que la oferta finalice</strong>. De lo contrario deberás elegir un cambio de pedido o un reembolso. ";
		} else {
			echo "<strong>El juego posee una oferta externa de tiempo limitado, por lo tanto deberás informar el pago en la sección de <a href='http://steambuy.com.ar/informar/' target='_blank'>informar pago</a> 
			antes de que finalice esta oferta.</strong> Revisa en el <a href='".$data["product_site_url"]."' target='_blank'>sitio de venta</a> del juego la fecha de fin de oferta para saber si debés informar el pago o no.<br/><br/>";	
		}



	} else if($data["order_fromcatalog"] == 1) { // catálogo
		
		
	
		if($data["product_sellingsite"] == 1) {
			echo "Este juego posee una oferta limitada de reventa de Steam. Es necesario que el pago se encuentre <strong>acreditado con tiempo antes de que la oferta finalice</strong>. De lo contrario deberás elegir un cambio de pedido o un reembolso. ";
		} else {
		?>
			Este juego posee una oferta externa de tiempo limitado, deberás <strong><a href="../informar/" target="_blank">informar el pago</a></strong> 
			antes de que termine la oferta para que te lo reservemos.&nbsp;
		<?php
		}
									
		$end_hour = date("H:i:s", strtotime($data["product_external_offer_endtime"]));
	
		if($data["product_external_offer_endtime"] != "0000-00-00 00:00:00" && $end_hour != "00:00:00") {
			echo "La oferta de este juego finaliza el <strong>".date("d/m/y H:i:s", strtotime($data["product_external_offer_endtime"]))."</strong>.";
		} else if($data["product_external_offer_endtime"] != "0000-00-00 00:00:00") {
			echo "La oferta de este juego finaliza el <strong>".date("d/m/y", strtotime($data["product_external_offer_endtime"]))." (medianoche)</strong>.";
		} else if($data["product_sellingsite"] == 2) {
			echo "Te recomendamos informar el pago lo antes posible ya que Amazon no especifica la fecha de fin de oferta de este juego.";
		} else {
			echo "Revisa la <a href='".$data["product_site_url"]."' target='_blank'>página de venta</a> externa del producto para saber cuándo finaliza la oferta.";	
		}
		echo "<br/><br/>
		<strong>Recuerda que este pedido se cancelará automáticamente en 5 días si no se recibe el pago o si finaliza la oferta limitada.</strong><br/>";
		
	}
	
} else {
	
	echo "<strong>Recuerda que este pedido se cancelará automáticamente en 5 días si no se recibe el pago.</strong><br/>";
	
}
echo "Ante cualquier duda revisa la página de <a href='http://steambuy.com.ar/soporte/' target='_blank'>soporte</a> o <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a>.<br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy";





?>