<?php
/*
Carga previa de todas las páginas del sitio
*/
session_start();
header("Content-Type: text/html; charset=UTF-8");

require_once "mysql-connection.php";
require_once "UserLogin.class.php";

if (!$con) {
    require_once(ROOT."app/template/db_conn_error.php");
	exit;
}


// Si hay codigo de referido, guardar en sesión.
if(isset($_GET["r"])) {
	if(preg_match("/^[0-9a-f]{7}$/", $_GET["r"])) {
		$_SESSION["referrer_code"] = $_GET["r"];	
	}
}


$login = new UserLogin($con);
$loggedUser = $login->user_logged_in();

?>