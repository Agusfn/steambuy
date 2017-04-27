<?php
require "../../config.php";
require ROOT."app/lib/mysql-connection.php";
require ROOT."app/lib/UserRegister.class.php";



if(isset($_GET["code"])) {
	
	$userRegister = new UserRegister($con);
	
	if($userRegister->validate_account_email($_GET["code"])) {
		echo "OK!";	
	} else echo "NO!";
}



?>