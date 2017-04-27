<?php
require_once "MysqlHelp.class.php";
require_once "UserPassword.class.php";

class User {
	
	public $userData;
	
	private $mysql;
	
	public $userId;
	public $userEmail;
	
	public $ban_reason;
	
	
	public function __construct($con, $data_type, $data) {
		$this->mysql = new MysqlHelp($con);
		
		if($data_type == "userid") {
			$this->userId = $data;
		} else if($data_type == "email") {
			$this->userEmail = $data;
		}	
	}
	
	
	/*  Función para determinar si un usuario existe. Si existe llama a get_data() y guarda los datos del user en userData
	 * 	Devuelve TRUE si existe, FALSE si no.
	*/
	public function exists() {
		
		if(!empty($this->userId)) {
			
			if(!is_numeric($this->userId)) return false;
			$sql = "SELECT COUNT(*) FROM `users` WHERE `id` = ".$this->userId;
			$count = $this->mysql->fetch_value($sql);
			if($count == 1) {
				$this->get_data();
				return true;
			} else return false;
		
		} else {
			
			$sql = "SELECT `id` FROM `users` WHERE `email` = ".$this->mysql->escape_str($this->userEmail);
			if($userid = $this->mysql->fetch_value($sql)) {
				$this->userId = $userid;
				$this->get_data();
				return true;
			} else return false;
		}
		

			
	}
	
	/* Funcion para verificar si la contraseña del usuario es válida.
	*/
	public function verify_password($password) {

		$password_hash = $this->userData["password_hash"];
		
		$pwd = new UserPassword;
		if($pwd->verify_password($password, $password_hash)) {
			return true;	
		} else {
			return false;
		}	
	}
		
	
	private function get_data() {
		$sql = "SELECT * FROM `users` WHERE `id` = ".$this->userId;
		$data = $this->mysql->fetch_row($sql);
		if($data != false) {
			$this->userData = $data;
			return true;
		} else return false;
	}





	public function email_verified() {
		if($this->userData["verified_email"] == 1) return true;
		else return false;	
	}

	public function is_banned() {
		$sql = "SELECT * FROM `users_bans` WHERE `user_id`=".$this->userData["id"]." AND `end_date` > NOW() ORDER BY `end_date` DESC LIMIT 1";
		if($banInfo = $this->mysql->fetch_row($sql)) {
			$this->ban_reason = $banInfo["reason"];
			return true;
		} else { // Asume que se termina el tiempo de baneo.
			if($this->userData["banned"] == 1) {
				$this->mysql->update_table("UPDATE `users` SET `banned`=0 WHERE `id`=".$this->userData["id"]);	
			}
			return false;
		}
	}
	
	public function get_ban_reason() {
		$sql = "SELECT `reason` FROM `users_bans` WHERE `user_id` = ".$this->userData["id"];	
	}
		
	
	
	public function update_last_visit_log($ip) {
		
		$last_visit_ips = $this->mysql->fetch_value("SELECT `last_visit_ips` FROM `users` WHERE `id`=".$this->userId);
		
		if($ips = $this->push_into_csv($last_visit_ips, $ip, 7)) {
			$this->mysql->update_table("UPDATE `users` SET `last_visit_date`=NOW(), `last_visit_ips`='".$this->mysql->escape_str($ips)."' WHERE `id`=".$this->userId);
		} else {
			$this->mysql->update_table("UPDATE `users` SET `last_visit_date`=NOW() WHERE `id`=".$this->userId);
		}
			
	}
	
	
	private function push_into_csv($csv_string, $value, $max_value_len) {
		
		$values = explode(",", $csv_string);
		
		if(!in_array($value, $values)) {
			if(sizeof($values) >= $max_value_len) {
				array_pop($values);
			}
			array_unshift($values, $value);
			return rtrim(implode(",", $values),",");
		} else {
			return false;	
		}
		
	}
	
	
}



?>