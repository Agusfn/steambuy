<?php
/*
E-mail para recuperar la cuenta (reestablecer contraseña)
data: fullname, recover_url, expiration_time
*/

if(!isset($data)) return false;

$subject = "Recuperación de contraseña";
?>
Estimado/a <?php echo $data["fullname"]; ?>, hemos recibido una solicitud para reestablecer la contraseña de tu cuenta. Si no solicitaste esto, por favor ignora este mensaje.<br/>
Haz click en el siguiente enlace y sigue las instrucciones en el sitio para reestablecer la contraseña de tu cuenta:<br/>
<a href="<?php echo $data["recover_url"]; ?>"><?php echo $data["recover_url"]; ?></a><br/><br/>
Esta solicitud será válida durante <?php echo $data["expiration_time"]; ?> minutos.<br/><br/>

Un saludo,<br/>
El equipo de SteamBuy