<?php

class UserPassword {
	

	/*
	Esta función hashea una password dada usando el algoritmo de hasheo de php
	*/
	public function hash_password($pwd) {
		return password_hash($pwd, PASSWORD_DEFAULT);	
	}

	
	public function verify_password($password, $hash) {
		return password_verify($password, $hash);	
	}
	
}


?>