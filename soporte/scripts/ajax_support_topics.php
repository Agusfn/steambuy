<?php

require_once("../../global_scripts/php/mysql_connection.php");

if(isset($_POST["search_query"])) {
	
	if($res = mysqli_query($con, "SELECT `order`,`question` FROM `faq` WHERE `question` LIKE '%".mysqli_real_escape_string($con, $_POST["search_query"])."%'")) {
		while($q = mysqli_fetch_assoc($res)) {
			$result[] = $q;	
		}
		if(!isset($result)) {
			echo "0";
		} else if(sizeof($result) > 0) echo json_encode($result);
	}
	
}

?>