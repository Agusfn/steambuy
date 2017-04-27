<?php
/*
Solicitud AJAX previo al login, para 1) verif que no este logueado, 2) verif. que el usuario pueda hacer el login, 3) verificar que el user y pass son validos
Si se verifica, se procede al login vía envío de formulario a cuenta/login.php

*/
require_once "../../../config.php";
session_start();
require_once ROOT."app/lib/mysql-connection.php";
require_once ROOT."app/lib/UserLogin.class.php";
require_once ROOT."app/lib/User.class.php";
require_once ROOT."app/lib/gReCaptcha.class.php";



$result = array("success" => false, "error_text"=>"", "needs_captcha"=>0);


sleep(1);



if(!isset($_POST["email"]) || !isset($_POST["password"]) || !isset($_POST["keep_loggedin"])) {
	send_error("No se enviaron los datos necesarios.");
}

	
$email = $_POST["email"];
$pass = $_POST["password"];

if($_POST["keep_loggedin"] == 1) $keep_login = true;
else $keep_login = false;


if(strlen($email) > 60 || strlen($pass) > 40) send_error("Error con formato de datos.");



$login = new UserLogin($con);

// Ver si ya está logueado
if($login->user_logged_in()) send_error("Ya tienes una sesión iniciada, recarga la página.");

		
// Verificar si el ip/cuenta está apta intentar loguear
$allowed = $login->verify_login_allowed($_SERVER["REMOTE_ADDR"], $email);


if($allowed == 0)  { // Si está bloqueado
	send_error("Alcanzaste la cantidad máxima de intentos, espera un momento para volver a hacerlo.");
	
} else if($allowed == 2) { // Si necesita captcha

	if(isset($_POST["captcha_key"])) {
		
		$captcha = new gReCaptcha;
		if($captcha->verify_captcha($_POST["captcha_key"]) == false) {
			send_error("No se validó el captcha.");
		} // Si es válido el captcha, continua normalmente
		
	} else {
		$result["needs_captcha"] = true;
		echo json_encode($result);
		exit;
	}
}


// Verificar combinacion de user y pass
$auth = $login->verify_credentials($email, $pass);
if($auth) {

	// Instanciar usuario para verificar si está baneado o mail no verificado
	$userId = $login->get_user_id($email);
	$user = new User($con, $userId);
	$user->get_data();
	
	if($user->is_banned()) {
		send_error("La cuenta está bloqueada. Motivo: ".$user->ban_reason);
	}
	if(!$user->email_verified()) {
		send_error("El e-mail de la cuenta no está verificado, veríficalo desde el mensaje enviado a tu casilla de correo al momento de registrar la cuenta.");
	}
	
	// Logueamos
	$login->create_login_session($userId, $keep_login);
		
	$result["success"] = true;
	echo json_encode($result);
		
	
} else {
	
	$login->add_login_failed_attempt($_SERVER["REMOTE_ADDR"], $email);
	send_error("La combinación de usuario y contraseña es inválida.");
	
}





//echo json_encode($result);


function send_error($text) {
	
	global $result;
	
	$result["success"] = false;
	$result["error_text"] = $text;
	
	echo json_encode($result);
	exit;
}


?>