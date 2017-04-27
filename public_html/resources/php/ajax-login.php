<?php
/*
Solicitud AJAX previo al login, para 1) verif que no este logueado, 2) verif. que el usuario pueda hacer el login, 3) verificar que el user y pass son validos
Si se verifica, se procede al login vía envío de formulario a cuenta/login.php

*/
require_once "../../../config.php";
session_start();
require_once ROOT."app/lib/mysql-connection.php";
require_once ROOT."app/lib/UserLogin.class.php";


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

if(isset($_POST["captcha_key"])) {
	$login_result = $login->attempt_user_login($email, $pass, $keep_login, $_SERVER["REMOTE_ADDR"], $_POST["captcha_key"]);
} else {
	$login_result = $login->attempt_user_login($email, $pass, $keep_login, $_SERVER["REMOTE_ADDR"]);
}


if($login_result == true) {
	$result["success"] = true;
} else {
	if($login->needsCaptcha) {
		$result["needs_captcha"] = true;
	} else {
		send_error($login->loginError);
	}
}

echo json_encode($result);







function send_error($text) {
	
	global $result;
	
	$result["success"] = false;
	$result["error_text"] = $text;
	
	echo json_encode($result);
	exit;
}


?>