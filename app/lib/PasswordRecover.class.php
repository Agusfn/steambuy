<?php
/* Clase para recuperación de contraseña de usuario
	Criterio para permitir las solicitudes: 1 por usuario, 1 por IP por hora.
*/

require_once "MysqlHelp.class.php";
require_once "User.class.php";
require_once "Mail.class.php";

class PasswordRecover {
	
	const REQUEST_EXPIRES = 30; // Minutos de validez del link/solicitud de cambio de contraseña.
	
	public $request_error; // Texto error solicitando e-mail de de recup.
	
	private $mysql;
	
	
	
	public function __construct($con) {
		$this->mysql = new MysqlHelp($con);
	}
	
	
	
	/* Método para enviar solicitud de recuperación de contraseña. Genera una entry en la tabla de
	*/
	public function password_recovery_request($user_email, $request_ip) {
		
		$user = new User($this->mysql->con, "email", $user_email);
		if(!$user->exists()) {
			$this->request_error = "El usuario no existe.";
			return false;
		}
		
		$sql = "SELECT COUNT(*) FROM `password_recovery_requests` WHERE `user_id`=".$user->userData["id"]." AND `succeeded`=0 AND `expires` > NOW()";
		$count = $this->mysql->fetch_value($sql);
		if($count != 0) {
			$this->request_error = "Ya hay una solicitud de cambio de contraseña pendiente para este usuario, revisa la bandeja de correo.";
			return false;
		}
		
		$sql = "SELECT COUNT(*) FROM `password_recovery_requests` WHERE `request_ip`='".$this->mysql->escape_str($request_ip)."' AND `request_date` > DATE_SUB(NOW(),INTERVAL 1 HOUR)";
		$count = $this->mysql->fetch_value($sql);
		if($count != 0) {
			$this->request_error = "Ya realizaste una solicitud hace poco. Espera un momento para realizar otra.";
			return false;
		}
		
		$token = $this->generate_recovery_token();

		if(!$this->send_recovery_email($user->userData, $token)) {
			$this->request_error = "Hubo un problema enviando el e-mail de recuperación de cuenta, intenta nuevamente más tarde.";
			return false;
		}
				
		$this->insert_new_request($user->userData["id"], $token, $request_ip);
		
		return true;
		
	}
	
	
	public function verify_token($user_id, $token) {
	
		$sql = "SELECT * FROM `password_recovery_requests` WHERE `user_id`=".$user_id." AND `succeeded`=0 AND `expires` > NOW() LIMIT 1";
		$request = $this->mysql->fetch_row($sql);
		if(!$request) return false;
		
		if(hash_equals($request["token"], $token)) {
			return true;
		} else return false;
		
	}
	
	
	public function mark_request_success($token) {
		$sql = "UPDATE `password_recovery_requests` SET `succeeded`=1 WHERE `token`='".$this->mysql->escape_str($token)."'";
		$this->mysql->update_table($sql);
	}
	
	
	
	private function insert_new_request($user_id, $token, $ip) {
		if(!is_numeric($user_id)) return false;
		$expires = date("Y-m-d H:i:s", strtotime("+".self::REQUEST_EXPIRES." minute"));
		$sql = "INSERT INTO `password_recovery_requests` (`id`, `request_date`, `user_id`, `token`, `request_ip`, `expires`, `succeeded`) 
		VALUES (NULL, NOW(), ".$user_id.", '".$this->mysql->escape_str($token)."', '".$this->mysql->escape_str($ip)."', '".$expires."',0) ";
		return $this->mysql->insert_into_table($sql);
	}
	
	
	
	public function generate_recovery_token() {
		return bin2hex(openssl_random_pseudo_bytes(24));	
	}
	
	
	public function send_recovery_email($user_data, $token) {
		
		$recover_url = PUBLIC_URL."cuenta/recuperar.php?email=".urlencode($user_data["email"])."&token=".$token;
		$full_name = $user_data["name"]." ".$user_data["lastname"];
		
		$mail = new Mail;
		
		$data = array("fullname" => $full_name, "recover_url" => $recover_url, "expiration_time" => self::REQUEST_EXPIRES);
		
		$mail->prepare_email("user/recuperar_cuenta", $data);
		
		$mail->add_address($user_data["email"], $full_name);
				
		return true;
		/*if($mail->send()) return true;
		else return false;*/
	}
	
	
}



?>