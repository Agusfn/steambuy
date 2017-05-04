<?php
/* Este script envía una solicitud de recuperación de contraseña y devuelve por AJAX el resultado.
*/
require_once("../../../config.php");
require_once(ROOT."app/lib/mysql-connection.php");
require_once(ROOT."app/lib/PasswordRecover.class.php");

$result = array("success" => false, "error_text" => "");


if(isset($_POST["user_email"]) && strlen($_POST["user_email"]) <= 60) {
		
	$recovery = new PasswordRecover($con);
	$request = $recovery->password_recovery_request($_POST["user_email"], $_SERVER["REMOTE_ADDR"]);
	
	if($request) {
		$result["success"] = true;
	} else {
		$result["error_text"] = $recovery->request_error;
	}
	
} else $result["error_text"] = "No se enviaron los datos necesarios.";


echo json_encode($result);
?>