<?php
require_once "../../config.php";
require_once ROOT."app/lib/UserLogin.class.php";

session_start();

$login = new UserLogin(0);
$login->logout();

if(isset($_GET["redir"])) {
	header("Location: ".$_GET["redir"]);
} else header("Location: ".PUBLIC_URL);
?>