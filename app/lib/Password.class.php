<?php

class Password {
	

	/*
	Esta función hashea una password dada usando el algoritmo de hasheo
	*/
	public function hash_password($pwd, $salt = "") {
		return md5($pwd.$salt);	
	}
	
	public function generate_salt() {
		return uniqid(mt_rand());
	}
	
	
}


?>