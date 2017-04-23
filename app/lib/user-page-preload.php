<?php
/*
Carga previa de todas las páginas del sitio
*/
session_start();
header("Content-Type: text/html; charset=UTF-8");

require_once "mysql-connection.php";
require_once "UserLogin.class.php";
require_once "User.class.php";

if (!$con) {
    require_once(ROOT."app/template/db_conn_error.php");
	exit;
}

$login = new UserLogin($con);
$loggedIn = $login->is_user_logged_in();

if($loggedIn) {
	if($user = new User($con, $login->logged_userid)) {
		$userData = $user->userData;
	} else $loggedIn = false;
}








?>