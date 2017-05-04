<?php
require_once "MysqlHelp.class.php";
require_once "UserPassword.class.php";
require_once "Mail.class.php";
require_once "User.class.php";


class UserRegister {
	
	const VERIF_KEY_LENGTH = 25;
	
	private $mysql;
	
	public $register_error;
	
	public function __construct($con) {
		$this->mysql = new MysqlHelp($con);
	}


	/*
	Función para crear un usuario nuevo.
	*/
	public function register_user($name, $lastname, $email, $password, $register_ip) {
		
		$user = new User(0,0,0);
		
		if(!$user->valid_name(1, $name) || !$user->valid_name(2, $lastname)) {
			$this->register_error = "El nombre o apellido no tiene formato válido.";
			return false;	
		}
		
		if(!$user->valid_email($email)) {
			$this->register_error = "El e-mail no tiene formato válido.";
			return false;
		}
		
		if(!$user->valid_password($password)) {
			$this->register_error = "La contraseña no tiene un formato válido.";
			return false;
		}
		
		if(!$this->user_email_available($email)) {
			$this->register_error = "La cuenta registrada con ese e-mail ya existe.";
			return false;
		}
		
		$pwd = new UserPassword;
		$hashed_pw = $pwd->hash_password($password);
		
		$validation_key = $this->generate_validation_key(self::VERIF_KEY_LENGTH);
		
		$ref_code = $this->generate_ref_code();
		
		
		if(!$this->send_validation_email($name." ".$lastname, $email, $validation_key)) {
			$this->register_error = "Hubo un problema enviando el e-mail de verificación de cuenta, la cuenta no se registró, intenta nuevamente más tarde.";
			return false;
		}
		
		$sql = "INSERT INTO `users` 
		(`id`, `register_date`, `email`, `name`, `lastname`, `birthdate`, `gender`, `identity_number`, `admin_level`, `verified_email`, `verified_email_key`, `verified_email_date`, 
		`verified_identity`, `password_hash`, `banned`, `fullname_history`, `last_visit_ips`, `last_visit_date`, `register_ip`, `ref_code`) 
		VALUES (NULL, NOW(), '".$this->mysql->escape_str($email)."', '".$this->mysql->escape_str($name)."', '".$this->mysql->escape_str($lastname)."', '0000-00-00', '', '', 0, 0, 
		'".$validation_key."', '0000-00-00 00:00:00', 0, '".$hashed_pw."', '0', '".$this->mysql->escape_str($name)." ".$this->mysql->escape_str($lastname)."', '', '0000-00-00 00:00:00', 
		'".$this->mysql->escape_str($register_ip)."', '".$ref_code."');";
		
		
		if($this->mysql->insert_into_table($sql)) {
			return true;
		} else {
			$this->register_error = "Hubo un problema con la base de datos, por favor intenta más tarde.";	
			return false;
		}
		
	}
	
	
	/* Verificar si una IP realizó un registro de cuenta en los últimos 30 minutos, para evitar flooding de cuentas.
	*/
	public function registration_allowed_ip($ip) {
		
		$sql = "SELECT COUNT(*) FROM `users` WHERE `register_ip` = '".$this->mysql->escape_str($ip)."' AND `register_date` >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
		
		if($this->mysql->fetch_value($sql) == 0) {
			return true;	
		} else return false;
	}
	
	
	/* Func. para validar el e-mail de una cuenta a partir de la key.
	*/
	public function validate_account_email($validation_key) {
		
		if(!preg_match("/^[A-Z0-9]{25,35}$/", $validation_key)) return false;
		$sql = "UPDATE `users` SET `verified_email` = 1, `verified_email_date` = NOW() WHERE `verified_email_key`='".$this->mysql->escape_str($validation_key)."' AND `verified_email` = 0";
		
		if($this->mysql->update_table($sql)) return true;
		else return false;
			
	}	
	
	
	/* Método para ver si existe un usuario con un e-mail dado
	*/
	private function user_email_available($email) {
		
		$sql = "SELECT COUNT(*) FROM `users` WHERE `email` = '".$this->mysql->escape_str($email)."'";
		if($this->mysql->fetch_value($sql) == 0) return true;
		else return false;
	
	}
	
	
	
	/* Método para generar una clave de validación de cuenta
	*/
	private function generate_validation_key($length) {
		$alphabet = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$pass = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $length; $i++) {
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
	
	private function generate_ref_code() {
		$code = substr(md5(time()), 5, 6);
		if($this->ref_code_available($code)) return $code;
		else return $this->generate_ref_code();
	}
	
	private function ref_code_available($code) {
		$sql = "SELECT COUNT(*) FROM `users` WHERE `ref_code`='".$this->mysql->escape_str($code)."'";
		if($this->mysql->fetch_value($sql) == 0) return true;
		else return false;
	}

	
}



?>