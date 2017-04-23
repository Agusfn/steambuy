<?php
/*
data: receiver_name, order_id, product_name, listed_keys
*/

if(!isset($data)) return false;

$subject = "Has recibido el ".$data["product_name"];
 ?>

Estimado/a <?php echo $data["receiver_name"]; ?>, hemos registrado el pago de tu pedido ID <strong><?php echo $data["order_id"]; ?></strong> por el juego <strong><?php echo $data["product_name"]; ?></strong>,
 a continuación te listaremos cómo activar tu/s producto/s.<br/>
<br/>
<?php 

if(strpos($data["listed_keys"], "store.steampowered.com/account/ackgift/") !== false) { // Posee un link de Steam (VAC)
    echo "El juego se activa con URL de activación, deberás hacer click en el enlace que te mostraremos a continuación, pero antes, asegurate de tener la cuenta de Steam en la que deseás 
	activar el juego<strong> logueada en el navegador y en el escritorio</strong> al momento de hacerle click, ya que sólo es posible aceptarlo para la biblioteca de juegos.<br/> 
    Una vez que se usa un link no puede volver a usarse, por eso, procurá no estar logueado con una cuenta que ya tiene el juego, porque <strong>podrás perderlo</strong>.<br/><br/>";
} 
echo $data["listed_keys"];
?>
<br/>
Si recibiste un juego en formato de CD-KEY, en esta <a href='http://steambuy.com.ar/soporte/preguntas-frecuentes/#c24' target='_blank'>guía</a> podés ver cómo activarlo.<br/>

<br/>
<strong>Estaríamos agradecidos si dieras 'me gusta' y comentaras acerca de tu experiencia en nuestra <a href='http://facebook.com/steambuy' target='_blank'>página de Facebook</a>.</strong><br/><br/>
Un saludo y gracias por comprar,
<br/>
El equipo de SteamBuy