<?php

require_once "MysqlHelp.class.php";

class Product {
	
	public $productId;
	public $productData;

	private $mysql;
	
	
	public function __construct($con, $product_id) {
		$this->mysql = new MysqlHelp($con);
		$this->productId = $product_id;
	}
	
	
	// Verificar si existe el producto. Si existe, guardar data en $this->productData
	public function exists() {
		
		if(!is_numeric($this->productId)) return false;
		
		$sql = "SELECT * FROM `products` WHERE `product_id`=".$this->productId;
		
		if($this->productData = $this->mysql->fetch_row($sql)) {
			return true;
		} else return false;
	}
	
	
	/* Función para agregar stock a una publicación si se cancela un pedido de un producto de stock.
		(la copia reservada vuelve a la venta)
	*/
	public function add_stock_from_canceled_order($orderData) {
		
		// si el producto del pedido (reservado de stock) se encuentra en stock, suma +1 unidad
		if($this->productData["product_has_limited_units"] == 1) { 
			
			$this->mysql->update_table("UPDATE products SET product_limited_units = product_limited_units + 1 WHERE product_id = ".$orderData["product_id_catalog"]);
		
		// si no se encuentra en stock, revisa el precio del pedido y si es más bajo lo cambia
		} else if($this->productData["product_has_limited_units"] == 0) { 
			
			if($orderData["product_usdprice"] != 0) {
				if(!($this->productData["product_has_customprice"] == 1 && $this->productData["product_customprice_currency"] == "ars") && $orderData["product_usdprice"] < $this->productData["product_finalprice"])
				{
					$this->mysql->update_table("UPDATE products SET product_has_limited_units = 1, product_limited_units = product_limited_units + 1, product_has_customprice = 1, 
					product_customprice_currency = 'usd', product_external_limited_offer = 0, product_external_offer_endtime='0000-00-00 00:00:00', product_finalprice = ".$orderData["product_usdprice"]." WHERE product_id = ".$orderData["product_id_catalog"]);
				} else {
					$this->mysql->update_table("UPDATE products SET product_limited_units = product_limited_units + 1 WHERE product_id = ".$orderData["product_id_catalog"]);
				}
			
			} else {
				
				if($orderData["order_paymentmethod"] == 2) {
					$newProductPrice = round(-1.05086 * (floatval($orderData["product_arsprice"]) / -1.015 - 1.5125),1); // el precio en transf del pedido se vuelve a establecer para boleta
				} else $newProductPrice = $orderData["product_arsprice"];
				
				$this->mysql->update_table("UPDATE products SET product_has_limited_units = 1, product_limited_units = product_limited_units + 1, product_has_customprice = 1, 
				product_customprice_currency = 'ars', product_external_limited_offer = 0, product_external_offer_endtime='0000-00-00 00:00:00', product_finalprice = ".$newProductPrice." WHERE product_id = ".$orderData["product_id_catalog"]);
			}
		}	
			
	}
	
	
	
	
	
	
}


?>