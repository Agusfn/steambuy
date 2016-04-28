<?php

require_once("../../global_scripts/php/mysql_connection.php");

if(isset($_POST["faq_array"])) {
	
	$array = $_POST["faq_array"];
	
	for($i=0;$i<sizeof($array);$i++) {
		mysqli_query($con, "UPDATE `faq` SET `order` = ".($i+1)." WHERE `number` = ".$array[$i]);
	}
	echo "OK";

}

?>