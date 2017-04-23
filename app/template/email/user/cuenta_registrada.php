<?php
/*
data: fullname, validation_key
*/

if(!isset($data)) return false;

$subject = "Te has registrado en Steambuy! Activa tu cuenta ahora";
?>
Estimado/a <?php echo $data["fullname"]; ?>, gracias por registrarte en SteamBuy! El siguiente paso para poder usar tu cuenta es verificar la misma, haciendo click en el siguiente enlace:<br/>
<a target="_blank" href="http://localhost/steambuy/public_html/cuenta/verificar.php?cod=<?php echo $data["validation_key"] ?>">http://steambuy.com.ar/cuenta/verificar.php?cod=<?php echo $data["validation_key"] ?></a>
<br/>
Una vez que haya sido verificada, podrás iniciar sesión y disfrutar de todo lo que tiene nuestra tienda!
<br/><br/>
Un saludo,<br/>
El equipo de SteamBuy