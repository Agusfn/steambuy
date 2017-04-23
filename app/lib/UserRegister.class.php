<?php
require_once("Password.class.php");
require_once("Mail.class.php");

class User {
	
	private $con;
	
	public $register_error;
	
	public function __construct($con) {
		$this->con = $con;
	}


	/*
	Función para crear un usuario nuevo.
	*/
	public function register_user($name, $lastname, $email, $password, $register_ip) {
		
		if(!$this->valid_name(1, $name) || !$this->valid_name(2, $lastname)) {
			$this->register_error = "El nombre o apellido no tiene formato válido.";
			return false;	
		}
		
		if(!$this->valid_email($email)) {
			$this->register_error = "El e-mail no tiene formato válido.";
			return false;
		}
		
		if(!$this->valid_password($password)) {
			$this->register_error = "La contraseña no tiene un formato válido.";
			return false;
		}
		
		if($acc_exists = $this->check_user_existence($email)) {
			if($acc_exists[0] == 1) {
				$this->register_error = "La cuenta registrada con ese e-mail ya existe.";
				return false;
			}
		} else {
			$this->register_error = "Hubo un problema registrando la cuenta, intenta más tarde.";
			return false;
		}
		
		
		$pass = new Password;
		$salt = $pass->generate_salt();
		$hashed_pw = $pass->hash_password($password, $salt);
		
		$validation_key = $this->generate_validation_key();
		
		if(!$this->send_validation_email($name." ".$lastname, $email, $validation_key)) {
			$this->register_error = "Hubo un problema enviando el e-mail de verificación de cuenta, la cuenta no se registró, intenta nuevamente más tarde.";
			return false;
		}
		
		$sql = "INSERT INTO `users` 
		(`id`, `register_date`, `email`, `name`, `lastname`, `birthdate`, `admin_level`, `verified_email`, `verified_email_key`, `verified_email_date`, `password_hash`, `password_salt`, `banned`, 
		`fullname_history`, `last_visit_ips`, `last_visit_date`, `register_ip`) 
		VALUES (NULL, NOW(), '".$this->escape_str($email)."', '".$this->escape_str($name)."', '".$this->escape_str($lastname)."', '0000-00-00', '0', '0', '".$validation_key."', '0000-00-00 00:00:00', 
		'".$hashed_pw."', '".$salt."', '0', '".$this->escape_str($name)." ".$this->escape_str($lastname)."', '', '0000-00-00 00:00:00', '".$this->escape_str($register_ip)."');";
		
		if(mysqli_query($this->con, $sql)) {
			return true;	
		} else {
			$this->register_error = "Hubo un problema con la base de datos, por favor intenta más tarde.";	
			return false;
		}
		
	}
	
	
	/* Verificar si una IP realizó un registro de cuenta en los últimos 30 minutos, para evitar flooding de cuentas.
	*/
	public function registration_allowed_ip($ip) {
		
		$sql = "SELECT COUNT(*) FROM `users` WHERE `register_ip` = '".$this->escape_str($ip)."' AND `register_date` >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
		if(!$query = mysqli_query($this->con, $sql)) {
			return false;
		}
		$count = mysqli_fetch_row($query);
		if($count[0] == 0) {
			return true;	
		} else return false;
		
	}
	
	
	
	/* Método para ver si existe un usuario con un e-mail dado
	   Devuelve False si hay un error, o un array con 1 o 0 en la posicion [0]
	*/
	private function check_user_existence($email) {
		
		$sql = "SELECT COUNT(*) FROM `users` WHERE `email` = '".$this->escape_str($email)."'";
		if(!$query = mysqli_query($this->con, $sql)) {
			return false;
		}
		$count = mysqli_fetch_row($query);
		
		if($count[0] == 0 || $count[0] == 1) return array($count[0]);
		else return false;
	}
	
	
	
	/* Método para generar una clave de validación de cuenta
	*/
	private function generate_validation_key() {
		$alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < 25; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		$key = implode($pass).(time() - 1490000000);
		return $key;
	}
		
	private function send_validation_email($name, $email, $validation_key) {
		$mail = new Mail;
		$data = array("fullname" => $name, "validation_key" => $validation_key);
		$mail->prepare_email("user/cuenta_registrada", $data);
		$mail->add_address($email, $name);
		//$mail->display_email();
		//if($mail->send()) {
			return true;	
		//} else return false;
	}
	
	
	/*
	Validación de nombre o apellido p/ registración (nombre 3-17 carac, apellido 3-20)
	$t: tipo, 1:nombre, 2:apellido
	$name: texto a validar
	*/
	private function valid_name($t, $name) {
		if(preg_match("/^[A-záéíóúüñÁÉÍÓÚÜÑ ]{3,".($t == 1 ? "17" : "20")."}$/", $name)) {
			return true;
		} else return false;
	}
	
	// Validación e-mail. Máx 60 caracteres.
	private function valid_email($email) {
		if(strlen($email) > 60) return false;
		if(preg_match("/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",$email)){
			return true;
		} else return false;
	}
	
	/* Validar contraseña (entre 6 y 40 caracteres, y debe tener al menos una letra y al menos una no letra)
	*/
	private function valid_password($pass) {
		if(strlen($pass) < 6 || strlen($pass) > 40) {
			return false;	
		}
		if(!preg_match("/[a-zA-Z]/", $pass) || !preg_match("/[^a-zA-Z]/", $pass)) {
			return false;
		}
		return true;
	}
	
	private function escape_str($str) {
		return mysqli_real_escape_string($this->con, $str);	
	}
	
}



?>