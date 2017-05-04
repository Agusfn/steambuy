<?php
require_once "../../../../config.php";

session_start();

require_once ROOT."app/lib/mysql-connection.php";
require_once ROOT."app/lib/UserLogin.class.php";


$login = new UserLogin($con);
$loggedUser = $login->user_logged_in();

$result = array("success" => false, "error_text" => "");


if($loggedUser) {
	
	if(isset($_POST["data"])) {
		
		if($_POST["data"] == "name" && isset($_POST["name"]) && isset($_POST["lastname"])) {
			
			if($loggedUser->update_fullname($_POST["name"], $_POST["lastname"])) {
				$result["success"] = true;			
			} else $result["error_text"] = "El nombre y/o apellido no tienen formato válido, o hubo un error cambiando nombre.";
			
		} else if($_POST["data"] == "password" && isset($_POST["old_password"]) && isset($_POST["new_password"])) {
		
			if($loggedUser->verify_password($_POST["old_password"])) {
				
				$loggedUser->change_password($_POST["new_password"]);
				$result["success"] = true;
				
			} else {
				$result["error_text"] = "La contraseña actual ingresada es inválida.";	
			}
			
		}
		
	} 
	
} else $result["error_text"] = "No estás con la sesión iniciada.";



echo json_encode($result);
?>

