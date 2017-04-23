<?php

require_once("../config.php");
require_once(ROOT."app/lib/mysql-connection.php");
require_once(ROOT."app/lib/UserLogin.class.php");

$userlogin = new UserLogin($con);


if($userlogin->verify_credentials("agustin-fn@hotmail.com", "mateofn_20")) {
	echo "OK!";	
} else {
	echo "Error";
}

?>