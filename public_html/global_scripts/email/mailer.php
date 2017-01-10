<?php

require_once("PHPMailer/PHPMailerAutoload.php");


class Mail
{
	
	const MAIL_HOST = "localhost";
	//const MAIL_PORT = 587;
	const MAIL_USERNAME = "info@steambuy.com.ar";
	const MAIL_PASSWORD = "xIHHOTb_q9h6";

	private $email; // objeto PHPMailer
	private $dir_location;
	
	public $debug = 0;
	public $reportFailure = false;
	public $errorInfo;
	
	function __construct() {
		$this->email = new PHPMailer;
		$this->dir_location = dirname(__FILE__).DIRECTORY_SEPARATOR;
	}
	
	public function prepare_email($email_type, $data) { 
		
		if(!preg_match("/^[\/a-z0-9_]*$/i", $email_type)) return false;
		else if(!file_exists($this->dir_location."templates/".$email_type.".php")) return false;
		
		$body_frame = file_get_contents($this->dir_location."templates/main.html");
		
		ob_start();
		require($this->dir_location."templates/".$email_type.".php");
		$body_content = ob_get_clean();

		$this->email->Subject = $subject;
		$this->email->Body    = preg_replace("/<email_content>/", $body_content, $body_frame);
		$this->email->AltBody = strip_tags($this->email->Body);
	}
	
	
	public function add_address($receiver_email, $receiver_name) {
		$this->email->addAddress($receiver_email, $receiver_name);
	}
	
	public function display_email() {
		echo $this->email->Subject."<br/><br/>".$this->email->Body;	
	}
	
	public function add_attachment($file_location) {
		$this->email->addAttachment($file_location);
	}
	
	public function send() {

		$this->email->CharSet = 'UTF-8';
		$this->email->isSMTP();
		$this->email->Host = self::MAIL_HOST; 
		//$this->email->Port = self::MAIL_PORT;  
		$this->email->SMTPAuth = true; 
		$this->email->Username = self::MAIL_USERNAME;
		$this->email->Password = self::MAIL_PASSWORD;
		//$this->email->SMTPSecure = 'SSL';
		$this->email->From = self::MAIL_USERNAME;
		$this->email->FromName = 'SteamBuy';
		$this->email->addReplyTo('contacto@steambuy.com.ar', 'Contacto SteamBuy');
		$this->email->isHTML(true);
		if($this->debug) $this->email->SMTPDebug = 1;

		if($this->email->send()) return true;
		else {
			$this->errorInfo = $this->email->ErrorInfo;
			if($this->reportFailure) echo "Mailer unsuccesful: ".$this->errorInfo;
			return false;	
		}
		
	}
	
}



?>