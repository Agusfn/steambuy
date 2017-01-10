<?php
/*
data: receiver_name, product_name, order_id, receiver_email
*/

if(!isset($data)) return false;

$subject = "Hemos registrado tu pago y se ha enviado tu ".$data["product_name"];

?>
Estimado/a <?php echo $data["receiver_name"]; ?> hemos registrado el pago de tu pedido ID <strong><?php echo $data["order_id"]; ?></strong> y tu juego <strong><?php echo $data["product_name"]; ?></strong> 
ha sido enviado a esta dirección e-mail por medio de Steam, en formato 'Steam Gift'. Si no sabes como activar un juego en tu cuenta de Steam revisa la siguiente 
<a href='http://steambuy.com.ar/faq/#10' target='_blank'>guía</a>.
<br/>
<?php if(strpos($data["receiver_email"],"gmail.com") !== false) { 
	echo "Si usas Gmail, revisa en la sección de &quot;promociones&quot; por el mensaje con las instrucciones de activación.<br/><br/>";
} ?>
<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong>
<br/>
<br/>
Un saludo y gracias por comprar,<br/>
El equipo de SteamBuy