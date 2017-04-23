<?php
/*
data: receiver_name, order_id, product_name, reject_reason
*/

if(!isset($data)) return false;

$subject = "El informe de pago enviado para la compra de ".$data["product_name"]." ha sido rechazado";
?>
Estimado/a <?php echo $data["receiver_name"]; ?>, el informe de pago realizado para el pedido <strong><?php echo $data["order_id"]; ?></strong> por el juego 
<strong><?php echo $data["product_name"]; ?></strong> ha sido rechazado debido a: <?php echo $data["reject_reason"]; if(substr($data["reject_reason"],-1)!='.') echo "."; ?><br/>
Puedes encontrar adjunta la imágen que enviaste como informe de pago. <strong>Reenvia el informe de pago como corresponde siguiendo la siguiente 
<a href='http://steambuy.com.ar/faq/#18' target='_blank'>guía</a> para no perderte de la oferta limitada.</strong><br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy