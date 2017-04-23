<?php
require_once "MysqlHelp.class.php";

class User {
	
	private $mysql;
	public $userData;
	
	public $ban_reason;
	
	
	public function __construct($con, $userid) {
		
		$this->mysql = new MysqlHelp($con);
		if(is_numeric($userid)) {
			$sql = "SELECT * FROM `users` WHERE `id` = ".$userid;
			if($this->userData = $this->mysql->fetch_row($sql)) return true;
			else return false;
		} else return false;
	}


	public function user_is_banned() {
		$sql = "SELECT * FROM `users_bans` WHERE `user_id`=".$this->userData["id"]." AND `end_date` > NOW() ORDER BY `end_date` DESC LIMIT 1";
		if($banInfo = $this->mysql->fetch_row($sql)) {
			$this->ban_reason = $banInfo["reason"];
			return true;
		} else {
			if($this->userData["banned"] == 1) {
				$this->mysql->update_table("UPDATE `users` SET `banned`=0 WHERE `id`=".$this->userData["id"]);	
			}
			return false;
		}
	}
	
	
	public function get_ban_reason() {
		$sql = "SELECT `reason` FROM `users_bans` WHERE `user_id` = ".$this->userData["id"];	
	}
		
	
	
	
}



?>