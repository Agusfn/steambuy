<?php
/*
Archivo ajax de registro de usuarios.
Indice errores devueltos:
0: operacion exitosa, 1: datos no enviados, 2: ya registro una cuenta en los últimos 30 min, 3: otro error (se especifica en error_text)

*/

require_once("../../../config.php");
require_once(ROOT."app/lib/mysql-connection.php");
require_once(ROOT."app/lib/UserRegister.class.php");


$result = array("error"=>0, "error_text"=>"", "data"=>"");

if(isset($_POST["email"]) && isset($_POST["name"]) && isset($_POST["lastname"]) && isset($_POST["password"])) {

	$user = new UserRegister($con);
	$ip = $_SERVER["REMOTE_ADDR"];	
	
	if($user->registration_allowed_ip($ip)) {
		
		if(!$user->register_user($_POST["name"], $_POST["lastname"], $_POST["email"], $_POST["password"], $ip)) {
			$result["error"] = 3;
			$result["error_text"] = $user->register_error;	
		}	
			
	} else {
		$result["error"] = 2;
		$result["error_text"] = "Ya registraste una cuenta hace unos minutos. Espera un momento para registrar otra.";
	}

} else {
	$result["error"] = 1;
	$result["error_text"] = "No se enviaron los datos necesarios.";
}


echo json_encode($result);



?>