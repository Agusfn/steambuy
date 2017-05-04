<?php
require_once "../../config.php";
require_once ROOT."app/lib/user-page-preload.php";

require_once ROOT."app/lib/UserRegister.class.php";


$error = false;

if(isset($_GET["code"])) {
	
	$userRegister = new UserRegister($con);
	
	if(!$userRegister->validate_account_email($_GET["code"])) {
		$error = true;	
	} 
	
} else $error = true;


$title = "Verificar cuenta - SteamBuy";
if($error) {
	$content = "<div class='alert alert-danger page-message'>Ha ocurrido un error procesando la solicitud. El link es inválido o la cuenta ya está verificada.</div>";	
} else {
    $content = "<div class='alert alert-success page-message'>La cuenta ha sido verificada exitosamente.</div>";
}

$template = new DisplayTemplate;
$template->insert_content($content, $title);
$template->display_rendered_html();


?>
