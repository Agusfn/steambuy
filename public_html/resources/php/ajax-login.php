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


$error_text = array(1 => "No se enviaron los datos necesarios.", 2 => "Ya tienes una sesión iniciada, recarga la página.", 
					3 => "Alcanzaste la cantidad máxima de intentos, espera un momento para volver a hacerlo.", 4=> "Ocurrió un error verificando.", 
					5=> "La combinación de usuario y contraseña es inválida.", 7 => "Hubo un error iniciando sesión.");

$result = array("error"=>0, "error_text"=>"");





if(!isset($_POST["email"]) || !isset($_POST["password"])) {
	send_error(1);
}

	
$email = $_POST["email"];
$pass = $_POST["password"];

if(strlen($email) > 60 || strlen($pass) > 40) send_error(1);



$login = new UserLogin($con);

// Ver si ya está logueado
if($login->is_user_logged_in()) send_error(2);

		
// Verificar si el ip/cuenta está apta intentar loguear
$allowed = $login->verify_login_allowed($_SERVER["REMOTE_ADDR"], $email);
if($allowed == 0) send_error(3);


if($allowed == 2) {
	if(isset($_POST["key"])) {
		
	} else {
		
	}
}


// Verificar combinacion de user y pass
if(!$login->verify_credentials($email, $pass)) {
	$login->add_login_failed_attempt($_SERVER["REMOTE_ADDR"], $email);
	send_error(5);
}


// Obtenemos id de usuario e instanciamos la class User para ver si no está baneado
$userId = $login->get_user_id($email);

if(!$user = new User($con, $userId)) send_error(7);


if($user->user_is_banned()) {
	send_error(6, "La cuenta está bloqueada. Motivo: ".$user->ban_reason);
}


// Logueamos
if($login->login_user($email)) {
	echo json_encode($result);
} else send_error(7);




//echo json_encode($result);


function send_error($number, $text = false) {
	
	global $result, $error_text;
	
	$result["error"] = $number;
	if($text) $result["error_text"] = $text;
	else $result["error_text"] = $error_text[$number];
	
	echo json_encode($result);
	exit;
}


?>