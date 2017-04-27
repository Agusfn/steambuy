<?php
/* Clase para recuperación de contraseña de usuario
	Criterio para permitir las solicitudes: 1 por usuario, 1 por IP por hora.
*/

require_once "MysqlHelp.class.php";

class PasswordRecover {
	
	/* Método para enviar solicitud de recuperación de contraseña. Genera una entry en la tabla de
	*/
	public function password_recovery_request($user_email, $request_ip) {
		
		// verificar si existe el usuario
		
		// verificar si ya hay alguna solicitud pendiente, o si la IP hizo alguna en la última hora
		
		// generar solicitud y enviar email
		
		
	}
	
	
	public function generate_recovery_token() {
		return bin2hex(openssl_random_pseudo_bytes(16));	
	}
	
	
}



?>