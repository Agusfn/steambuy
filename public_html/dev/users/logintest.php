<?php
require_once "../../../config.php";
require_once ROOT."app/lib/mysql-connection.php";

session_start();

require_once ROOT."app/lib/UserLogin.class.php";


//Queremos loguearnos con los datos:
$ip = "1.1.1";
$email = "agusfn20@gmail.com"; 
$pass = "mateofn_20";


/*
Pasos (SIN REMEMBER-ME)
1º verificamos si ya esta logueado
2º verificamos intentos de login
3º verificamos que las credenciales sean válidas
4º validamos sesion

*/


$login = new UserLogin($con);


$logged_in = $login->is_user_logged_in();
echo "Logueado: ".($logged_in ? "Si, userid:".$_SESSION["login_userid"] : "No")."<br/><br/>";

if(!$logged_in) {

	$allowed = $login->verify_login_allowed($ip, $email);
	echo "Login permitido: ";
	if($allowed == 1) echo "Si";
	else if($allowed == 2) echo "Si, c/ captcha";
	else if($allowed == 0) echo "No";
	echo "<br/><br/>";
	
	if($allowed == 0) echo "No autorizado para continuar el login", exit;
	
	$valid_credentials = $login->verify_credentials($email, $pass);
	echo "Credenciales válidas: ".($valid_credentials ? "Si" : "No")."<br/><br/>";
	
	if(!$valid_credentials) {
		$login->add_login_failed_attempt($ip, $email);
		echo "No autorizado, intento fallido de login registrado.<br/><br/>";
		exit;
	}
	
	if($login->login_user($email)) {
		echo "Logueado correctamente! Recargar página para ver mas info.";	
	}


} else {
	echo "Logueado! <a href='logouttest.php'>Cerrar sesion</a>";
}




?>