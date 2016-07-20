<?php
/*
data: receiver_name, order_id, product_name
*/

if(!isset($data)) return false;

$subject = "Tu juego ".$data["product_name"]." pedido ha sido reservado";
?>
Estimado/a <?php echo $data["receiver_name"]; ?>, el juego <strong><?php echo $data["product_name"]; ?></strong> de tu pedido pedido ID <strong><?php echo $data["order_id"]; ?></strong> ha sido reservado,
de esta forma no te perderás la oferta externa limitada.<br/>
Los pedidos por boleta de pago son enviados durante el día en que se acredita el pago (entre 12 y 48hs luego de pagar), y los pedidos por transf/depósito durante las siguientes 12 hs hábiles luego 
de acreditado. Si tu pago ya está acreditado el pedido debería ser enviado durante el día de hoy.<br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy
