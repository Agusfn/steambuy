<?php
/*
data: receiver_name, product_name, order_id, receiver_email
*/

if(!isset($data)) return false;

$subject = "Hemos registrado tu pago y se ha enviado tu ".$data["product_name"];

?>
Estimado/a <?php echo $data["receiver_name"]; ?> hemos registrado el pago de tu pedido ID <strong><?php echo $data["order_id"]; ?></strong> y tu juego <strong><?php echo $data["product_name"]; ?></strong> 
 ha sido enviado a la cuenta especificada por medio de Steam o por e-mail, según corresponda el envío.
<br/>
<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong>
<br/>
<br/>
Un saludo y gracias por comprar,<br/>
El equipo de SteamBuy