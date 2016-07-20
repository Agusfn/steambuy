<?php
/*
data: receiver_name, order_id, product_name, listed_keys
*/

if(!isset($data)) return false;

$subject = "Hemos registrado tu pago y has recibido el ".$data["product_name"]." de SteamBuy";

if(strpos($data["listed_keys"], "humblebundle.com") !== false) {
	$hasHumble = true;
} else $hasHumble = false; ?>

Estimado/a <?php echo $data["receiver_name"]; ?>, hemos registrado el pago de tu pedido ID <strong><?php echo $data["order_id"]; ?></strong> por el juego <strong><?php echo $data["product_name"]; ?></strong>, 
las claves de activación o links para activar tu producto las puedes encontrar a continuación:<br/>
<br/>
<?php echo $data["listed_keys"]; ?>
<br/>
Si no sabes como activar una clave de activación en Steam u Origin puedes ver la siguiente <a href='http://steambuy.com.ar/faq/#24' target='_blank'>guía</a>.
<?php if($hasHumble) $body .= "Para saber cómo activar productos de Humble Bundle puedes ver esta otra <a href='http://steambuy.com.ar/faq/#25' target='_blank'>guía</a>."; ?>
<br/>
<br/>
<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong><br/><br/>
Un saludo y gracias por comprar,
<br/>
El equipo de SteamBuy