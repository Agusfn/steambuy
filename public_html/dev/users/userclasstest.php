<?php

require_once("../../global_scripts/php/mysql_connection.php");

require_once("../../../lib/UserRegister.class.php");



$user = new UserRegister($con);

if(!$user->register_user("Agustin", "Fernandez Nuñez", "agusfn20@gmail.com", "agusfn20password")) {
	echo $user->register_error;
}




?>