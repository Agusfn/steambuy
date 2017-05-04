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
			
			$sql = "SELECT `id` FROM `users` WHERE `email` = '".$this->mysql->escape_str($this->userEmail)."'";
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
		
	/* Función para cambiar la contraseña de un usuario. Se utiliza en cuenta/recuperar.php
	*/
	public function change_password($new_password) {
		
		// Deshabilitar, si existen, los token de autorizacion de cookies login.
		$sql = "UPDATE `user_auth_tokens` SET `expires`='0000-00-00 00:00:00' WHERE `id`=".$this->userData["id"];
		$this->mysql->update_table($sql);
		
		// Cambiar contraseña
		$pwd = new UserPassword;
		$hash = $pwd->hash_password($new_password);
		
		$sql = "UPDATE `users` SET `password_hash`='".$this->mysql->escape_str($hash)."' WHERE `id`=".$this->userData["id"];
		if($this->mysql->update_table($sql)) return true;
		else return false;
		
	}
	
	
	/* Verificar si una orden pertenece al usuario.
	*/
	public function order_belongs($orderid) {
		if(!preg_match("/^J[0-9]{4,7}$/", $orderid)) return false;
		$sql = "SELECT COUNT(*) FROM `orders` WHERE `order_id`='".$this->mysql->escape_str($orderid)."' AND `associated_userid` = ".$this->userData["id"];
		if($this->mysql->fetch_value($sql) == 1) {
			return true;	
		} else return false;
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
	
	/* Método para modificar el nombre y apellido del usuario.
	*/
	public function update_fullname($name, $lastname) {
		
		if($this->valid_name(1, $name) && $this->valid_name(2, $lastname)) {
			
			$sql = "UPDATE `users` SET `name`='".$this->mysql->escape_str($name)."', `lastname`='".$this->mysql->escape_str($lastname)."' WHERE `id`=".$this->userId;
			$this->mysql->update_table($sql);
			
			$this->update_fullname_log($name." ".$lastname);
			return true;
			
		} else return false;
		
	}
	
	/* Método para agregar un nuevo input al CSV de historial de cambio de nombres en la db.
	*/
	public function update_fullname_log($fullname) {
		
		$name_history = $this->mysql->fetch_value("SELECT `fullname_history` FROM `users` WHERE `id`=".$this->userId);
		
		if($new_name_history = $this->push_into_csv($name_history, $fullname, 7)) {
			$this->mysql->update_table("UPDATE `users` SET `fullname_history`='".$this->mysql->escape_str($new_name_history)."' WHERE `id`=".$this->userId);
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
	
	
	/*
	Validación de nombre o apellido p/ registración (nombre 3-17 carac, apellido 3-20)
	$t: tipo, 1:nombre, 2:apellido
	$name: texto a validar
	*/
	public function valid_name($t, $name) {
		if(preg_match("/^[A-záéíóúüñÁÉÍÓÚÜÑ ]{3,".($t == 1 ? "17" : "20")."}$/", $name)) {
			return true;
		} else return false;
	}
	
	// Validación e-mail. Máx 60 caracteres.
	public function valid_email($email) {
		if(strlen($email) > 60) return false;
		if(preg_match("/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",$email)){
			return true;
		} else return false;
	}
	
	/* Validar contraseña (entre 6 y 40 caracteres, y debe tener al menos una letra y al menos una no letra)
	*/
	public function valid_password($pass) {
		if(strlen($pass) < 6 || strlen($pass) > 40) {
			return false;	
		}
		if(!preg_match("/[a-zA-Z]/", $pass) || !preg_match("/[^a-zA-Z]/", $pass)) {
			return false;
		}
		return true;
	}
	
	
	public function fullname() {
		return $this->userData["name"]." ".$this->userData["lastname"];	
	}
}



?>