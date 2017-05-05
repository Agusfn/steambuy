<?php
/*
$data: receiver_name, order_id, order_password, product_name, order_ars_price, payment_method

si payment_method=1: order_purchaseticket_url
*/

if(!isset($data)) return false;


$subject = "Se ha generado tu pedido por gift card ".$data["product_name"];


echo "Estimado/a ".$data["receiver_name"].", se ha generado tu pedido por <strong>".$data["product_name"]."</strong> por <strong>&#36;".$data["order_ars_price"]." 
pesos argentinos</strong>. El ID del pedido es <strong>".$data["order_id"]."</strong> y la clave es <strong>".$data["order_password"]."</strong>.<br/>
El siguiente paso para recibir el código es";
if($data["payment_method"] == 1) {
	echo " imprimir y abonar en cualquier sucursal de pago la boleta de pago que puedes encontrar en el siguiente link: <br/>
	<a href='".$data["order_purchaseticket_url"]."' target='_blank'>".$data["order_purchaseticket_url"]."</a>.<br/><br/>
	Una vez abonado, el pago tomará entre 12 y 48 horas en acreditarse, y el código será enviado el día en que se acredita el pago (por lo general al día siguiente 
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
	foto o imágen del comprobante de transferencia/depósito para que podamos identificarlo. Este se acredita de forma instantánea en horario hábil, y el código será enviado 
	durante las siguientes 12 horas hábiles luego de haberse acreditado el pago.<br/><br/>";
}
echo "<strong>Recuerda que este pedido se cancelará automáticamente en 5 días si no se recibe el pago.</strong><br/>
Ante cualquier duda revisa la página de <a href='http://steambuy.com.ar/soporte/' target='_blank'>soporte</a> o <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a>.<br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy";





?>