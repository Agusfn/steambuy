<?php
require_once "MysqlHelp.class.php";
require_once "Product.class.php";

class Order {
	
	private $mysql;
	private $orderId;
	public $orderData;

	public function __construct($con, $orderid = false) {
		$this->mysql = new MysqlHelp($con);
		if($orderid && preg_match("/^J[0-9]{4,7}$/", $orderid)) {
			$this->orderId = $orderid;	
		}
	}
	
	
	public function exists() {
		
		if(empty($this->orderId)) return false;
		$sql = "SELECT * FROM `orders` WHERE `order_id`='".$this->mysql->escape_str($this->orderId)."'";
		$orderData = $this->mysql->fetch_row($sql);
		if($orderData) {
			$this->orderData = $orderData;
			return true;
		} else return false;
			
	}
	
	public function belongs_to_user($userid) {
		if(empty($this->orderData)) return false;
		if($this->orderData["associated_userid"] == $userid) return true;
		else return false;
	}
	
	
	/* Registrar informe de pago en un pedido.
	*/
	public function register_payment_inform($filename) {
		$sql = "UPDATE `orders` SET `order_informedpayment`=1, `order_informed_date`=NOW(), `order_informed_image`='".$this->mysql->escape_str($filename)."' WHERE `order_id`='".$this->mysql->escape_str($this->orderId)."'";
		$this->mysql->update_table($sql);
	}
	
	/* Cancelar un pedido.
	*/
	public function cancel($reason) {
		
		if($this->orderData["order_status"] != 1) return false;
		
		// Si el producto pedido es de stock, reasignar la unidad al catálogo para la venta.
		if($this->orderData["product_fromcatalog"] == 1 && $this->orderData["product_limited_unit"] > 0) {
			
			$product = new Product($this->mysql->con, $this->orderData["product_id_catalog"]);
			
			if($product->exists()) {
				$product->add_stock_from_canceled_order($this->orderData);
			}
			
		}

		// Cancelar pedido
		$sql = "UPDATE `orders` SET `order_status`=3, `order_status_change`=NOW(), `cancel_reason`='".$this->mysql->escape_str($reason)."' WHERE `order_id`='".$this->mysql->escape_str($this->orderId)."'";
		$this->mysql->update_table($sql);
		
		return true;
		
	}
	
}
?>