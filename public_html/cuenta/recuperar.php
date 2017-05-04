<?php
require_once "../../config.php";
require_once ROOT."app/lib/user-page-preload.php";

require_once ROOT."app/lib/UserRegister.class.php";
require_once ROOT."app/lib/PasswordRecover.class.php";


$error = false;

if(isset($_GET["email"]) && isset($_GET["token"]) && strlen($_GET["email"]) <= 60) {
	
	if(isset($_POST["new_password"])) {
		$changingPassword = true;
		$pass = $_POST["new_password"];
	} else $changingPassword = false;

	
	$email = $_GET["email"]; $token = $_GET["token"];
	
	
	$user = new User($con, "email", $email);
	if($user->exists()) {
	
		$recover = new PasswordRecover($con);
		
		if($recover->verify_token($user->userData["id"], $token)) {

			if($changingPassword) {		
				
				if($user->valid_password($pass)) {
					
					$user->change_password($pass);
					$recover->mark_request_success($token);
					
				} else $error = true;	
			}
			
		} else $error = true;

	} else $error = true;

} else $error = true;





$title = "Reestablecer contraseña - SteamBuy";
if($error) {
	$content = "<div class='alert alert-danger page-message'>Ha ocurrido un error procesando la solicitud. El usuario no existe, o el token ha expirado.</div>";	
} else {
	if($changingPassword) {
		$content = "<div class='alert alert-success page-message'>La contraseña se ha modificado exitosamente.</div>";
	} else {
		ob_start();
		?>
		<h3 class="title">Reestablecer contraseña</h3>
		<form action="" method="post" id="recover-form">
			<input type="hidden" name="email" value="<?php echo $email; ?>" />
			<input type="hidden" name="token" value="<?php echo $token; ?>" />
			Ingresa la nueva contraseña:
			<input type="password" class="form-control" id="password1" name="new_password" maxlength="40" />
			Reingresa la contraseña:
			<input type="password" class="form-control" id="password2" maxlength="40" />
			<input type="button" class="btn btn-primary" id="submit-btn" value="Cambiar contraseña" />
		</form>
		<?php
		$content = ob_get_clean();
	}
}

$template = new DisplayTemplate;
$template->insert_content($content, $title);
$template->display_rendered_html();


?>