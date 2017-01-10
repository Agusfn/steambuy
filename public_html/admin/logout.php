<?php session_start();

require_once("../global_scripts/php/admlogin_functions.php");

if(isAdminLoggedIn())
{
	session_destroy();
	setcookie("apw", "", time() - 3600, "/");
	
}

if(isset($_GET["redir"])) {
	header("Location: ".$_GET["redir"]);
} else {
	header("Location: ../");
}



?>