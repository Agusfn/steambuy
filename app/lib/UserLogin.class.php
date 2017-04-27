<?php
/*  Clase de inicio de sesión

Para estar logueado se debe tener 2
*/


require_once "UserPassword.class.php";
require_once "MysqlHelp.class.php";
require_once "User.class.php";


class UserLogin {
	
	const AUTH_EXPIRES = 30; // Cantidad de días que duran las cookies y la auth. de "no cerrar sesion"
	
	private $mysql;
		
	public function __construct($con) {
		$this->mysql = new MysqlHelp($con);
	}

	/* Método para determinar si un usuario (client) está logueado o no, en base a sus variables de sesion y cookies
	 * Devuelve una instancia de la clase User si está logueado, y FALSE si no																	*/
	public function user_logged_in() {
				
		if(isset($_SESSION["login_userid"])) {
			$userid = $_SESSION["login_userid"];
		} else {		
			if($userid = $this->check_cookie_login()) {
				$_SESSION["login_userid"] = $userid;
			} else return false;
		}
		
		if(!isset($userid)) {
			return false;
		}
		
		$user = new User($this->mysql->con, $userid);
		
		if(!$user->exists()) {
			$this->logout();
			return false;
		}
		
		$user->get_data();
		
		if($user->userData["banned"] == 1) {
			$this->logout();
			return false;
		}
		
		$user->update_last_visit_log($_SERVER["REMOTE_ADDR"]);
		
		return $user;
	}
	
	
	
	
	/* Método para iniciar la sesión del cliente con un usuario. Se llama sólo desde ajax-login, donde se verifica previamente si el usuario existe
		$user_email: e-mail de la cuenta que se desea loguear
		$keep_login: no cerrar sesión
	*/
	public function create_login_session($user_id, $keep_login) {	
		$_SESSION["login_userid"] = $user_id;
		if($keep_login) {
			$this->save_auth_cookies($user_id);	
		}
	}
	
	
	public function logout() {
		unset($_SESSION["login_userid"]);
		session_destroy();
		$this->destroy_login_cookies();
	}
	
	
	/* Funcion para verificar si una combinacion de email y pass dada son válidas
	*/
	public function verify_credentials($email, $password) {
		
		$user = new User($this->mysql->con, "email", $email);
		
		if(!$user->exists()) return false;
		
		$user->get_data();

		$password_hash = $user->userData["password_hash"];
		
		$pwd = new UserPassword;
		if($pwd->verify_password($password, $password_hash)) {
			return true;	
		} else {
			return false;
		}	
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
	
	
	/* Método para chequear si el usuario posee una sesión vigente de recordar login.
	Devuelve el id del user que se esta logueado o FALSE.
	*/
	private function check_cookie_login() {
	
		if(!isset($_COOKIE["s"]) || !isset($_COOKIE["v"])) return false;
		
		$selector = $_COOKIE["s"]; $validator = $_COOKIE["v"];
		
		if(!$this->valid_auth_selector($selector) || !$this->valid_auth_validator($validator)) {
			$this->destroy_login_cookies();
			return false;
		}
		
		$sql = "SELECT * FROM `user_auth_tokens` WHERE `selector` = '".$this->mysql->escape_str($selector)."' AND `expires` > NOW()";
		
		if($authData = $this->mysql->fetch_row($sql)) {
			
			$token = $this->hash_auth_validator($validator);
			
			if(hash_equals($authData["token"], $token)) {
				return $authData["user_id"];
			} else {
				$this->destroy_login_cookies();
				return false;
			}
			
		} else {
			$this->destroy_login_cookies();
			return false;	
		}
		
	}
	
	
	public function save_auth_cookies($user_id) {
		
		$selector = $this->generate_auth_selector();
		$validator = $this->generate_auth_validator();
		$token = $this->hash_auth_validator($validator);
		
		$this->insert_auth_entry($selector, $token, $user_id);
		
		setcookie("s", $selector,  strtotime("+".self::AUTH_EXPIRES." days"), "/");
		setcookie("v", $validator,  strtotime("+".self::AUTH_EXPIRES." days"), "/");
			
	}
	
	/* Método para insertar (si no existe) o actualizar la fila de tokens de auth.
	 * Se utiliza en el método save_auth_cookies() únicamente		*/
	private function insert_auth_entry($selector, $token, $userid) {
		
		$expires = date('Y-m-d H:i:s', strtotime("+".self::AUTH_EXPIRES." days"));
		$selector = $this->mysql->escape_str($selector);
		$token = $this->mysql->escape_str($token);
		$expires = $this->mysql->escape_str($expires);
		
		$sql = "UPDATE `user_auth_tokens` SET `selector`='".$selector."', `token`='".$token."', `expires`='".$expires."' WHERE `user_id`=".$userid;
		if($this->mysql->update_table($sql)) {
			return true;	
		} else {
			$sql = "INSERT INTO `user_auth_tokens` (`id`,`selector`,`token`,`user_id`,`expires`) VALUES (NULL, '".$selector."', '".$token."', ".$userid.", '".$expires."')";
			if($this->mysql->insert_into_table($sql)) return true;
			else return false;
		}	
	}
	
	
	
	/* Metodo para generar un codigo selector único para las cookies.
		Es un hex de 7 caracteres
	*/
	public function generate_auth_selector() {
		$selector = substr(bin2hex(openssl_random_pseudo_bytes(16)), 10, 7);
		if(!$this->auth_selector_already_exists($selector)) {
			return $selector;
		} else return $this->generate_auth_selector();
	}
	
	/* Funcion que se usa en generate_auth_selector() unicamente
	*/
	private function auth_selector_already_exists($selector) {
		$sql = "SELECT COUNT(*) FROM `user_auth_tokens` WHERE `selector`=".$this->mysql->escape_str($selector);
		if($this->mysql->fetch_row($sql) == 0) return false;
		else return true;
	}
	
	private function generate_auth_validator() {
		return bin2hex(openssl_random_pseudo_bytes(20));	
	}
	
	/* Hashea una validator de auth (guardada en cookies), en un token que se guarda en DB
	*/
	private function hash_auth_validator($validator) {
		return hash('sha256', $validator);
	}

	
	private function destroy_login_cookies() {
		setcookie("s", "", 1, "/");
		setcookie("v", "", 1, "/");
		unset($_COOKIE["s"]);
		unset($_COOKIE["v"]);	
	}
	
	private function valid_auth_selector($str) {
		if(preg_match("/^[a-f0-9]{7}$/", $str)) return true;
		else return false;
	}
	private function valid_auth_validator($str) {
		if(preg_match("/^[a-f0-9]{40}$/", $str)) return true;
		else return false;
	}

	
}





?>