<?php
require_once "../../../../config.php";

session_start();

require_once ROOT."app/lib/mysql-connection.php";
require_once ROOT."app/lib/UserLogin.class.php";
require_once ROOT."app/lib/Order.class.php";


$login = new UserLogin($con);
$loggedUser = $login->user_logged_in();

$result = array("success" => false);

if($loggedUser) {

	if(isset($_POST["order_id"])) {
		
		$order = new Order($con, $_POST["order_id"]);
			
		if($order->exists() && $order->belongs_to_user($loggedUser->userData["id"])) {

			if($order->orderData["order_status"] == 1 && $order->orderData["order_confirmed_payment"] != 1) {
				
				$order->cancel("Cancelado por el usuario.");
				$result["success"] = true;
			}
		}		
	}
}


echo json_encode($result);
?>

