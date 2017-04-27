<?php
require_once "MysqlHelp.class.php";

class AuthCookies {
	
	
	
	
	/* Método para chequear si el usuario posee una sesión vigente de recordar login.
	Devuelve el id del user que se esta logueado o FALSE.
	*/
	public function check_cookie_login() {
	
		if(!isset($_COOKIE["s"]) || !isset($_COOKIE["v"])) return false;
		
		$selector = $_COOKIE["s"];
		$validator = $_COOKIE["v"];
		
		// chequear formato de variables
		
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
	
	public function destroy_login_cookies() {
		setcookie("s", "", time()-3600);
		setcookie("v", "", time()-3600);
		unset($_COOKIE["s"]);
		unset($_COOKIE["v"]);	
	}	
	
	public function save_auth_cookies($user_id) {
		
		$selector = $this->generate_auth_selector();
		$validator = $this->generate_auth_validator();
		$token = $this->hash_auth_validator($validator);
		
		//$this->insert_auth_entry($userid, $expira, $token)

			
	}
	
	
	
	/* Método para insertar (si no existe) o actualizar la fila de tokens de auth.
	 * Se utiliza en el método save_auth_cookies() únicamente		*/
	private function insert_auth_entry($selector, $token, $userid, $expires) {
		
		$selector = $this->mysql->escape_str($selector);
		$token = $this->mysql->escape_str($token);
		$expires = $this->mysql->escape_str($expires);
		
		$sql = "UPDATE TABLE `user_auth_tokens` SET `selector`='".$selector."' `token`='".$token."' `expires`='".$expires."' WHERE `user_id`=".$userid;
		if($this->mysql->update_table($sql)) {
			return true;	
		} else {
			$sql = "INSERT INTO `user_auth_tokens` (`id`,`selector`,`token`,`user_id`,`expires`) VALES (NULL, '".$selector."', '".$token."', ".$userid.", '".$expires."')";
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
	
	
	private function valid_auth_selector($str) {
		if(preg_match("/^[a-f0-9]{7}$/", $str)) return true;
		else return false;
	}
	
	
	
	
	
	
	
	
	
}



?>