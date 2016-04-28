<?php

require_once("../PHPMailer/PHPMailerAutoload.php");


class email
{
	const mail_host = "box756.bluehost.com";
	const mail_account = "info@steambuy.com.ar"; // E-mail de la cta de mail de bluehost
	const mail_password = "03488639268";
	
	private $subject;
	private $body;
	
	
	public function gameOrderGenerated($buyername, $orderid, $paymethod, $invoice_url, $gamename, $arsprice, $usdprice, $ext_lim_offer) {
		
	}
	
	public function orderCanceled($orderid, $reason = "") {
		// Función ejemplo para futuras mejoras
	}
	
}

?>