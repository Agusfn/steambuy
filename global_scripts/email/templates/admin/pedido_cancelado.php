<?php
/*
data necesaria: receiver_name, order_id, product_name, cancel_reason
*/

if(!isset($data)) return false;

$subject = "Tu pedido por el juego ".$data["product_name"]." ha sido cancelado";

?>
Estimado/a <?php echo $data["receiver_name"]; ?>, el pedido ID <strong><?php echo $data["order_id"]; ?></strong> que realizaste por el juego <strong><?php echo $data["product_name"]; ?></strong> 
ha sido cancelado debido a: <?php echo $data["cancel_reason"]; if(substr($data["cancel_reason"],-1)!='.') echo "."; ?><br/>
<br/>
Si ya abonaste el pedido, <a href='mailto:contacto@steambuy.com.ar'>contáctanos</a> para solicitar un cambio de producto o un reembolso.<br/>
<br/>
Un saludo,<br/>
El equipo de SteamBuy