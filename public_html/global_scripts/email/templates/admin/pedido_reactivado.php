<?php
/*
data: receiver_name, order_id, product_name
*/

if(!isset($data)) return false;

$subject = "Tu pedido por el juego ".$data["product_name"]." ha sido reactivado";
?>
Estimado/a <?php echo $data["receiver_name"]; ?>, tu pedido ID <strong><?php echo $data["order_id"]; ?></strong> por el juego <strong><?php echo $data["product_name"]; ?></strong> ha sido 
reactivado.<br/>
Cuando registremos el pago el pedido será enviado, o si el pago ya está acreditado solamente espera a recibir el producto.<br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy