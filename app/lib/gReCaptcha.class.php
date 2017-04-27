<?php


class gReCaptcha {

	const API_URL = "https://www.google.com/recaptcha/api/siteverify";
	const SECRET = "6LcaKx4UAAAAADRLT7Vfvk1HNvmgzx_OZD-dyFbN";
	
	public function verify_captcha($user_response) {
		
		$get_vars = "?secret=".self::SECRET."&response=".$user_response;
		
		$response_json = file_get_contents(self::API_URL.$get_vars);
		if(!$response_json) return false;
		
		$response_data = json_decode($response_json, true);
		if($response_data == NULL) return false;
		
		if($response_data["success"] == true) return true;
		else return false;

	}

	
}

?>