<?php
/*
data: receiver_name, order_id, old_product_name, new_product_name, new_order_price
*/

if(!isset($data)) return false;

$subject = "Tu pedido por el juego ".$data["old_product_name"]." ha sido reactivado y cambiado por el ".$data["new_product_name"];

echo "Estimado/a ".$data["receiver_name"].", el pedido ID <strong>".$data["order_id"]."</strong> que realizaste por el juego <strong>".$data["old_product_name"]."</strong> 
ha sido reactivado y modificado por el/los juego/s <strong>".$data["new_product_name"]."</strong>";
if($data["new_order_price"] != "" && is_numeric($data["new_order_price"])) {
	echo ", con un valor de &#36;".$data["new_order_price"]." pesos.";
} else echo ".";
echo "<br/>Cuando registremos el pago el pedido será enviado, o si el pago ya está acreditado solamente espera a recibir el producto.<br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy";
?>
