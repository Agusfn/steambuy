<?php

require_once("../../../global_scripts/php/mysql_connection.php");

if(isset($_POST["get_banlist"])) {
	$res = mysqli_query($con, "SELECT * FROM `banlist` ORDER BY `id` DESC");
	while($bans = mysqli_fetch_assoc($res)) {
		$ban_json[] = $bans;	
	}
	if(empty($ban_json)) echo "empty";
	else echo json_encode($ban_json);	
} else if(isset($_POST["delete_ban"]) && isset($_POST["ban_id"])) {
	if(mysqli_query($con, "DELETE FROM `banlist` WHERE `id` = ".mysqli_real_escape_string($con, $_POST["ban_id"]))) {
		echo "ok";	
	}
} else if(isset($_POST["add_ban"]) && isset($_POST["ban_ip"]) && isset($_POST["ban_reason"])) {
	if(mysqli_query($con, "INSERT INTO `banlist` (`id`, `ip`, `reason`) VALUES (NULL, '".mysqli_real_escape_string($con, $_POST["ban_ip"])."', '".mysqli_real_escape_string($con, $_POST["ban_reason"])."');")) {
		echo mysqli_insert_id($con);	
	}
}

?>