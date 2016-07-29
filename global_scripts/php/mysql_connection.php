<?php

if($_SERVER["SERVER_ADDR"] == "::1" || $_SERVER["SERVER_ADDR"] == "127.0.0.1") // Si es localhost
{
	$mysql_server = "localhost";
	$mysql_user = "root";
	$mysql_password = "20596";
	$mysql_database = "steambuy_av";
} else {
	$mysql_server = "localhost";
	$mysql_user = "steambuy_dbadmin";
	$mysql_password = "ld}u{@zl(x^4";
	$mysql_database = "steambuy_db";
}

$connection_error = 0;
$con = @mysqli_connect($mysql_server, $mysql_user, $mysql_password, $mysql_database);
if($con != false) {
	mysqli_query($con, "SET NAMES 'utf8', time_zone = '-03:00'");
}




?>