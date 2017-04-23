<?php

$con = @mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);
if($con != false) {
	mysqli_query($con, "SET NAMES 'utf8', time_zone = '-03:00'");
}

?>