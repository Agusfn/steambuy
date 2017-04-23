<?php
/*  Clase de inicio de sesión

Para estar logueado se debe tener 2
*/


require_once "Password.class.php";
require_once "MysqlHelp.class.php";
/*requirdde_once "User.class.php";
*/

class UserLogin {
	
	private $mysql;
	
	public $logged_userid;
	
	public function __construct($con) {
		$this->mysql = new MysqlHelp($con);
	}


	/* Funcion para verificar si una combinacion de email y pass dada son válidas
	*/
	public function verify_credentials($email, $password) {
		
		if(!$this->user_exists("email", $email)) return false;
		
		if(!$salt = $this->get_user_salt($email)) return false;
		
		$pwd = new Password;
		$passwordhash = $pwd->hash_password($password, $salt);
		
		$sql = "SELECT COUNT(*) FROM `users` WHERE `email` = '".$this->mysql->escape_str($email)."' AND `password_hash` = '".$this->mysql->escape_str($passwordhash)."'";
		$count = $this->mysql->fetch_value($sql);
		
		if($count == 1) {
			return true;	
		} else {
			return false; // La cuenta no tiene ese password hash
		}
		
		
	}
	
	
	/* Método para determinar si un usuario (client) está logueado o no, en base a sus variables de sesion y cookies
	 * Devuelve TRUE si está logueado, y FALSE si no																	*/
	public function is_user_logged_in() {
				
		if(!isset($_SESSION["login_userid"])) return false;
		
		if($this->user_exists("userid", $_SESSION["login_userid"])) {
			$this->logged_userid = $_SESSION["login_userid"];
			return true;
		} else {
			$this->logout();
			return false;
		}// agregar algo para chequear si esta baneado
		
	}
	
	
	/* Método para verificar si un intento de inicio de sesión: 1) puede hacerse sin más, 2) se puede hacer pero necesita captcha, o 3) no puede hacerse.
	
	El criterio es según la cantida de intentos en la última hora:
	Para una IP: 3 intentos sin captch > 4 con captcha > bloqueo.
	Para un user: 3 intentos sin captcha > resto con captcha
	Devuelve según sea la autorizacion. 0:no, 1:si, 2:captcha, sino FALSE.
	*/
	public function verify_login_allowed($ip, $email_attempt) {
		
		$sql = "SELECT COUNT(*) FROM `login_attempts_failed` WHERE `ip` = '".$this->mysql->escape_str($ip)."' AND `date` >= DATE_SUB(NOW(),INTERVAL 1 HOUR);";
		$attempts_ip = $this->mysql->fetch_value($sql);
		if($attempts_ip === false) return false;
		
		if($attempts_ip >= 3 && $attempts_ip <= 6) return 2;
		else if($attempts_ip >= 7) return 0;
		
		$sql = "SELECT COUNT(*) FROM `login_attempts_failed` WHERE `email` = '".$this->mysql->escape_str($email_attempt)."' AND `date` >= DATE_SUB(NOW(),INTERVAL 1 HOUR);";
		$attempts_account = $this->mysql->fetch_value($sql);
		if($attempts_account === false) return false;
		
		if($attempts_account >= 3) return 2;
		else return 1;		
	
	}
	
	/* Método para iniciar la sesión del cliente con un usuario
	*/
	public function login_user($user_email) {
		
		if($this->user_exists("email", $user_email)) {
			
			$sql = "SELECT `id` FROM `users` WHERE `email`='".$this->mysql->escape_str($user_email)."'";
			$userid = $this->mysql->fetch_value($sql);
			if($userid === false) return false;
			
			$_SESSION["login_userid"] = $userid;
			return true;
			
		} else return false;
	}
	
	public function logout() {
		session_destroy();	
	}
	
	public function add_login_failed_attempt($ip, $acc_email) {
		$sql = "INSERT INTO `login_attempts_failed` (`id`,`date`,`ip`,`email`) VALUES (NULL, NOW(), '".$this->mysql->escape_str($ip)."', '".$this->mysql->escape_str($acc_email)."');";
		if($this->mysql->insert_into_table($sql)) {
			return true;
		} else return false;
	}
	
	
	public function get_user_id($email) {
		$sql = "SELECT `id` FROM `users` WHERE `email`='".$this->mysql->escape_str($email)."'";
		return $this->mysql->fetch_value($sql);
	}
	


	/* Método para saber si existe un usuario con un user id dado.
	*  Usado para determinar si existe el usuario del que se está logueado.
		$data_type: tipo de dato que se busca (email/userid)
		$value: valor
	*/
	private function user_exists($data_type, $value) {
		
		if($data_type == "email") {
			$sql = "SELECT COUNT(*) FROM `users` WHERE `email` = '".$this->mysql->escape_str($value)."'";
		} else if($data_type == "userid") {
			if(!is_numeric($value)) return false;
			$sql = "SELECT COUNT(*) FROM `users` WHERE `id` = ".$value;
		} else return false;
		
		$ammount = $this->mysql->fetch_value($sql);
		if($ammount == 1) return true;
		else return false;	
	}
	
	
	/* Funcion para obtener la SAL
	*/
	private function get_user_salt($email) {
		$sql = "SELECT `password_salt` FROM `users` WHERE `email` = '".$this->mysql->escape_str($email)."'";
		return $this->mysql->fetch_value($sql);
	}
	

	
}





?>