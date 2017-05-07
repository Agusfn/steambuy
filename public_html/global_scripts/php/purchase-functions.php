<?php 
/*
índice de errores:

1: tipo de orden, medio de pago, o precio inválido
2: error generando boleta de pago

3: si se indica que el juego es del catálogo y no existe en el catálogo y no esta activo
4: el juego del catálgo no está en stock (se reestablece esto y se pone con precio de lista)


*/





/*
Clase de compras

Métodos:

checkProductPurchasable:
revisa si el producto se puede comprar (si existe, si esta habilitado, y si no está fuera de stock)

calcProductFinalArsPrices:
<se debe revisar si el producto se puede comprar primero>
calcula y devuelve el precio final en pesos por boleta (ppal) y por transferencia del producto revisado anteriormente

createGameOrder:
crea un nuevo pedido de compra (maneja stock, genera boleta, ingresa en db, y envía e-mail)

checkCouponValidity:
Revisa la validez de un cupón de descuento aplicado al producto analizado inicialmente con checkProductPurchasable
*/
class Purchase
{
	public $con;
	
	public $orderInfo = array('order_id' => '','order_password' => '','order_purchaseticket' => '');
	public $productData; // Si el producto puede venderse, acá se almacena información necesaria para la compra del producto. 
	public $product_type; // Tipo de producto: 1=juego, 2=gift card
	public $couponData;
	
	public $error;
	public $couponCheckError;
		
	
	private $CD_ID = array ("1"=>"545364","2"=>"584204","3"=>"545437"); // IDs de cuentas CuentaDigital
		
		
	public function __construct($mysql) {
		$this->con = $mysql;
	}

	public function checkProductPurchasable($product_type, $product_id) {
		
		if(!is_numeric($product_id)) return false;
		
		if($product_type == 1) { // Tipo de producto. 1=juego, 2=gift card
			
			$sql = "SELECT * FROM `products` WHERE `product_id` = ".mysqli_real_escape_string($this->con, $product_id)." AND product_enabled = 1 
			AND NOT (product_has_limited_units = 1 AND product_limited_units = 0)";
			$query = mysqli_query($this->con, $sql);
			if(mysqli_num_rows($query) == 1) {
				$this->productData = mysqli_fetch_assoc($query);
				$this->product_type = $product_type;
				return true;
			} else return false;	
					
		} else if($product_type == 2) {
			
			$sql = "SELECT * FROM `products_giftcards` WHERE `id` = ".mysqli_real_escape_string($this->con, $product_id)." AND `stock` > 0";
			$query = mysqli_query($this->con, $sql);
			if(mysqli_num_rows($query) == 1) {
				$this->productData = mysqli_fetch_assoc($query);
				$this->product_type = $product_type;
				return true;
			} else return false;
			
		} else return false;
		
		
		

	}
	
	public function calcProductFinalArsPrices() {
		
		if($this->product_type == 1) { // juego
			
			if($this->productData["product_has_customprice"] == 1 && $this->productData["product_customprice_currency"] == "ars") {
				$ticketPrice = $this->productData["product_finalprice"];
				$transferPrice =  $this->productData["product_finalprice"] - ($this->productData["product_finalprice"] * 0.0484 + 1.5125);
				$transferPrice = round(1.015 * $transferPrice, 1);
			} else {
				$ticketPrice = quickCalcGame(1, $this->productData["product_finalprice"]);	
				$transferPrice = quickCalcGame(2, $this->productData["product_finalprice"]);
			}
			if(is_numeric($ticketPrice) && is_numeric($transferPrice)) {
				return array("ticket_price"=>$ticketPrice, "transfer_price"=>$transferPrice);
			} else return false;
			
		} else if($this->product_type == 2) { // giftcard
			
			$ticketPrice = quickCalcGame(1, $this->productData["selling_price_usd"]);	
			$transferPrice = quickCalcGame(2, $this->productData["selling_price_usd"]);
			return array("ticket_price"=>$ticketPrice, "transfer_price"=>$transferPrice);
			
		}
			
	}
	
	public function checkCouponValidity($coupon_code) {
		
		// por ahora solo funciona para juegos.
		if($this->product_type != 1) {
			$this->couponCheckError = 2;
			return false;
		}
		
		if(!preg_match("/^[a-zA-Z0-9%$#@]*$/", $coupon_code)) {
			$this->couponCheckError = 1;
			return false;
		}
		
		$sql = "SELECT * FROM `discount_coupons` WHERE `coupon_code` = '".mysqli_real_escape_string($this->con, $coupon_code)."' AND `coupon_start_date` < NOW() AND `coupon_end_date` > NOW()";
		$query = mysqli_query($this->con, $sql);
		if(mysqli_num_rows($query) == 1) {
			
			$this->couponData = mysqli_fetch_assoc($query);
			
			$excludedProducts = explode(",", $this->couponData["coupon_excluded_products"]);
			if(in_array($this->productData["product_id"], $excludedProducts)) {
				$this->couponCheckError = 2;
				return false;	
			}
			
			$includedProducts = explode(",", $this->couponData["coupon_included_products"]);
			if(in_array($this->productData["product_id"], $includedProducts)) {
				return true;	
			}
			
			
			$productCriteria = explode(",", $this->couponData["coupon_discount_criteria"]);
			
			foreach($productCriteria as $criterion) {
				if($this->productInCouponCriterion($criterion)) return true;	
			}
			$this->couponCheckError = 2;
			return false;

		} else {
			$this->couponCheckError = 1;
			return false;	
		}
	}
	
	/*
	Función para verificar si un producto pertenece a un criterio, para determinar a qué productos afecta un cupón.
	
	params - $criterion: palabra clave de criterio individual, que engloba a cierto tipo de producto. Los criterios están determinados en esta función.
	return - true si pertenece, false si no pertenece
	*/
	private function productInCouponCriterion($criterion) {
				
		if($criterion == "stock") {
			
			if($this->productData["product_has_customprice"] == 1 && $this->productData["product_customprice_currency"] == "usd" && $this->productData["product_has_limited_units"] == 1
			&& $this->productData["product_external_limited_offer"] == 0) {
				return true;
			} return false;
			
			
		} else return false;
		
		
	}
	
	public function createGiftCardOrder($order_paymethod, $product_name, $product_id, $product_arsprice, $client_name, $client_email, $client_ip, $discount_coupon, $coupon_discounted_ammount) {
		
		global $config;
		
		if(($order_paymethod != 1 && $order_paymethod != 2) || !is_numeric($product_arsprice) || ($coupon_discounted_ammount != "" && !is_numeric($coupon_discounted_ammount))) { 
			$this->error = 1; 
			return false; 
		}
		
		// Crear una contraseña
		$orderPassword = randomPassword();
		$this->orderInfo["order_password"] = $orderPassword;
		
				
		// Revisar datos del juego y hacer la reducción de stock correspondiente si estuvo en stock
		if(is_numeric($product_id)) {

			$query = mysqli_query($this->con, "SELECT * FROM `products_giftcards` WHERE `id` = '".$product_id."' AND `stock` >= 1");
				
			if(mysqli_num_rows($query) == 1) {
				
				$productData = mysqli_fetch_assoc($query);

				// Reducir stock del producto (si es de stock)
				$product_limited_unit = $productData["stock"];
				mysqli_query($this->con, "UPDATE `products_giftcards` SET `stock` = `stock`-1 WHERE `id`=".$product_id);

			} else { 
				$this->error = 3; 
				return false; 
			}
			
		} else return false;


		// Obtener ID pedido
		$this->orderInfo["order_id"] = $this->obtener_id_nuevo_pedido();

		
		// Realizar consulta
		$sql = "INSERT INTO `orders` (`order_number`, `order_send_method`, `order_id`, `order_password`, `order_date`, `order_status`, `order_status_change`, `order_confirmed_payment`, `order_payment_time`,
		`order_purchaseticket`, `product_fromcatalog`, `product_type`, `product_id_catalog`, `product_limited_unit`, `order_paymentmethod`, `order_discount_coupon`, `coupon_discount_amt`, `product_usdprice`, 
		`product_arsprice`, `product_cur_steam_price`, `product_name`, `product_sellingsite`, `product_site_url`, `product_limited_discount`, `order_informedpayment`, `order_informed_date`, 
		`order_informed_image`, `order_reserved_game`, `order_sentkeys`, `buyer_name`, `buyer_email`, `buyer_ip`, `buyer_steam_url`) 
		VALUES (NULL, 0, '".$this->orderInfo["order_id"]."', '".$orderPassword."', NOW(), 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '', 1, 2, '".$product_id."', ".$product_limited_unit.", 
		".$order_paymethod.", '".$this->scp_str($discount_coupon)."', ".$coupon_discounted_ammount.", 0, '".$product_arsprice."', 0, '".$this->scp_str($product_name)."', 0, '', 0, 0, 
		'0000-00-00 00:00:00', '', 0, '', '".$this->scp_str($client_name)."', '".$this->scp_str($client_email)."', '".$this->scp_str($client_ip)."', '');";	
		
		
		$query = mysqli_query($this->con, $sql);
		if($query) {
			
			// Generar boleta de pago e insertarla en el pedido ya creado
			if($order_paymethod == 1) {
				
				$link_boleta = $this->generar_boleta($product_arsprice, "ID-".$this->orderInfo["order_id"]."-0USD-".$product_arsprice."ARS", $client_email);
				if(!$link_boleta) return false;
				
			} else $link_boleta = "";
			
			$this->orderInfo["order_purchaseticket"] = $link_boleta;
			
			if($this->guardar_link_boleta_en_pedido($this->orderInfo["order_id"], $link_boleta)) {
				return true;
			} else return false;
			
		} else {
			$this->error = mysqli_error($this->con);
			return false;	
		}
	}
	
	public function createGameOrder($order_paymethod, $product_name, $product_id_catalog, $product_sellingsite, $product_siteurl, $product_limitedoffer, $product_usdprice, 
	$product_arsprice, $client_name, $client_email, $client_steamurl, $client_ip, $discount_coupon, $coupon_discounted_ammount) 
	{
		global $config;
		
		if(($order_paymethod != 1 && $order_paymethod != 2) || !is_numeric($product_usdprice) || !is_numeric($product_arsprice) || 
		($coupon_discounted_ammount != "" && !is_numeric($coupon_discounted_ammount))) { 
			$this->error = 1; 
			return false; 
		}
		
		// Crear una contraseña
		$orderPassword = randomPassword();
		$this->orderInfo["order_password"] = $orderPassword;
		
		$current_steam_price = 0;
		
		// Revisar datos del juego y hacer la reducción de stock correspondiente si estuvo en stock
		if(is_numeric($product_id_catalog)) {
			
			$product_fromcatalog = 1;
			$query = mysqli_query($this->con, "SELECT * FROM `products` WHERE product_id = '".$product_id_catalog."' AND product_enabled = 1");

			if(mysqli_num_rows($query) == 1) {
				
				$productData = mysqli_fetch_assoc($query);

				if($productData["product_sellingsite"] == 1) {
					if($productData["product_external_limited_offer"] == 1) $current_steam_price = $productData["product_steam_discount_price"];
					else $current_steam_price = $productData["product_listprice"];
				}
				
				if($productData["product_has_limited_units"] == 1) {
					
					$productLimitedUnits = intval($productData["product_limited_units"]);
					if($productLimitedUnits == 0) {
						$this->error = 4;
						return false;
					}
					if(($productData["product_sellingsite"] == 3 || $productData["product_sellingsite"] == 4) && $productLimitedUnits == 1) {
						$sql2 = "UPDATE `products` SET `product_limited_units` = 0, `product_enabled` = 0 WHERE `product_id` = '".$product_id_catalog."'";	
						mysqli_query($this->con, $sql2);
					} else if($productLimitedUnits > 1 || ($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars" && $productData["product_listprice"] == 0)) {
						mysqli_query($this->con, "UPDATE products SET product_limited_units = product_limited_units - 1 WHERE product_id = '".$product_id_catalog."'"); // Reducir 1 stock
					} else if($productLimitedUnits == 1) {
						$sql2 = "UPDATE products SET product_has_limited_units = 0, product_limited_units = 0, product_has_customprice = 0, product_external_limited_offer = 0,
						product_external_offer_endtime = '0000-00-00 00:00:00', product_finalprice = product_listprice WHERE product_id = '".$product_id_catalog."'";		
						mysqli_query($this->con, $sql2);
					} 
					
					$product_limited_unit = $productLimitedUnits;
					
				} else $product_limited_unit = 0;
				
				
				// Establecer método de envío
				if($productData["product_sellingsite"] == 1 && $productData["product_has_limited_units"] == 0) {
					$send_method = 1; // Envío como steam gift a amigo de steam
				} else $send_method = 0; // Envio por mail convencional

			} else { 
				$this->error = 3; 
				return false; 
			}
			
		} else {
			$product_fromcatalog = 0;
			$product_limited_unit = 0;
			$current_steam_price = $product_usdprice;
		}
		
		// Obtener ID pedido
		$this->orderInfo["order_id"] = $this->obtener_id_nuevo_pedido();
		
		// Realizar consulta
		$sql = "INSERT INTO `orders` (`order_number`, `order_send_method`, `order_id`, `order_password`, `order_date`, `order_status`, `order_status_change`, `order_confirmed_payment`, `order_payment_time`,
		`order_purchaseticket`, `product_fromcatalog`, `product_type`, `product_id_catalog`, `product_limited_unit`, `order_paymentmethod`, `order_discount_coupon`, `coupon_discount_amt`, `product_usdprice`, 
		`product_arsprice`, `product_cur_steam_price`, `product_name`, `product_sellingsite`, `product_site_url`, `product_limited_discount`, `order_informedpayment`, `order_informed_date`, 
		`order_informed_image`, `order_reserved_game`, `order_sentkeys`, `buyer_name`, `buyer_email`, `buyer_ip`, `buyer_steam_url`) 
		VALUES (NULL, ".$send_method.", '".$this->orderInfo["order_id"]."', '".$orderPassword."', NOW(), 1, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '', ".$product_fromcatalog.", 1, '".$product_id_catalog."', ".$product_limited_unit.", 
		".$order_paymethod.", '".$this->scp_str($discount_coupon)."', ".$coupon_discounted_ammount.", '".$product_usdprice."', '".$product_arsprice."', '".$current_steam_price."', 
		'".$this->scp_str($product_name)."', ".$this->scp_str($product_sellingsite).", '".$this->scp_str($product_siteurl)."', ".$this->scp_str($product_limitedoffer).", 0, 
		'0000-00-00 00:00:00', '', 0, '', '".$this->scp_str($client_name)."', '".$this->scp_str($client_email)."', '".$this->scp_str($client_ip)."', '".$this->scp_str($client_steamurl)."');";	
		
		
		$query = mysqli_query($this->con, $sql);
		if($query) {
			
			// Generar boleta de pago e insertarla en el pedido ya creado
			if($order_paymethod == 1) {

				$link_boleta = $this->generar_boleta($product_arsprice, "ID-".$this->orderInfo["order_id"]."-".$product_usdprice."USD-".$product_arsprice."ARS", $client_email);
				if(!$link_boleta) return false;
				
			} else $link_boleta = "";
			
			$this->orderInfo["order_purchaseticket"] = $link_boleta;
			
			if($this->guardar_link_boleta_en_pedido($this->orderInfo["order_id"], $link_boleta)) {
				return true;
			} else return false;
			
		} else {
			$this->error = mysqli_error($this->con);
			return false;	
		}
		
	}
	
	private function scp_str($string) {
		return mysqli_real_escape_string($this->con, $string);	
	}
	
	
	private function obtener_id_nuevo_pedido() {
		
		$query = mysqli_query($this->con, "SHOW TABLE STATUS LIKE 'orders'");
		$res = mysqli_fetch_assoc($query);
		$autoIncrement = $res['Auto_increment'];
		return "J".$autoIncrement;
		
	}
	
	private function generar_boleta($monto, $codigo, $mail) {
		
		global $config;
		
		$rand = array($this->CD_ID[3]); 
		if(floatval($config["cd1_balance"]) < (42000 - 1000)) $rand[] = $this->CD_ID[1];	
		if(floatval($config["cd2_balance"]) < (10500 - 1000)) $rand[] = $this->CD_ID[2];	
		$cd_to_send = $rand[array_rand($rand)];

		$paymentTicketLink = get_url("https://www.cuentadigital.com/api.php?id=".$cd_to_send."&precio=".$monto."&venc=5&codigo=".$codigo."&hacia=".$mail."&concepto=Venta+de+productos+digitales");		
		
		if(strpos($paymentTicketLink, "https://www.cuentadigital.com/verfactura.php?id=") !== false) { 
			return $paymentTicketLink;
		} else return false;
		
	}
	
	private function guardar_link_boleta_en_pedido($pedido_id, $link_boleta) {
	
		if(mysqli_query($this->con, "UPDATE `orders` SET `order_purchaseticket`='".$link_boleta."' WHERE `order_id`='".$pedido_id."'")) {
			return true;
		} else return false;	
	}
	
	
}
	
	
function cancelOrder($orderid) 
{	
	global $con;
	
	$res1 = mysqli_query($con, "SELECT * FROM orders WHERE order_id = '".mysqli_real_escape_string($con, $orderid)."'");
	if(mysqli_num_rows($res1) == 1) {
		$oData = mysqli_fetch_assoc($res1);
		if($oData["product_fromcatalog"] == 1 && $oData["product_limited_unit"] > 0) {
			
			
			if($oData["product_type"] == 1) {
				
				$res2 = mysqli_query($con, "SELECT * FROM products WHERE product_id = ".mysqli_real_escape_string($con, $oData["product_id_catalog"]));
				if(mysqli_num_rows($res2) == 1) {
					$pData = mysqli_fetch_array($res2);
					
					if($pData["product_has_limited_units"] == 1) { // si el producto del pedido (reservado de stock) se encuentra en stock, suma +1 unidad
						mysqli_query($con, "UPDATE products SET product_limited_units = product_limited_units + 1 WHERE product_id = ".$oData["product_id_catalog"]);
					} else if($pData["product_has_limited_units"] == 0) { // si no se encuentra en stock, evalúa el precio y si es más bajo lo cambia
						if($oData["product_usdprice"] != 0) {
							if(!($pData["product_has_customprice"] == 1 && $pData["product_customprice_currency"] == "ars") && $oData["product_usdprice"] < $pData["product_finalprice"])
							{
								mysqli_query($con, "UPDATE products SET product_has_limited_units = 1, product_limited_units = product_limited_units + 1, product_has_customprice = 1, 
								product_customprice_currency = 'usd', product_external_limited_offer = 0, product_external_offer_endtime='0000-00-00 00:00:00', product_finalprice = ".$oData["product_usdprice"]." WHERE product_id = ".$oData["product_id_catalog"]);
							} else {
								mysqli_query($con, "UPDATE products SET product_limited_units = product_limited_units + 1 WHERE product_id = ".$oData["product_id_catalog"]);
							}
						} else {
							if($oData["order_paymentmethod"] == 2) {
								$newProductPrice = round(-1.05086 * (floatval($oData["product_arsprice"]) / -1.015 - 1.5125),1); // el precio en transf del pedido se vuelve a establecer para boleta
							} else $newProductPrice = $oData["product_arsprice"];
							mysqli_query($con, "UPDATE products SET product_has_limited_units = 1, product_limited_units = product_limited_units + 1, product_has_customprice = 1, 
							product_customprice_currency = 'ars', product_external_limited_offer = 0, product_external_offer_endtime='0000-00-00 00:00:00', product_finalprice = ".$newProductPrice." WHERE product_id = ".$oData["product_id_catalog"]);
						}
					}	
					
				}	
							
			} else if($oData["product_type"] == 2) {
				echo "AA";
				mysqli_query($con, "UPDATE `products_giftcards` SET `stock` = `stock` + 1 WHERE `id` = ".mysqli_real_escape_string($con, $oData["product_id_catalog"]));

			}
			
			
		}
		if($oData["order_paymentmethod"] == 1) deleteReceipt($oData["order_informed_image"]); 
		$sql = "UPDATE orders SET `order_status` = '3', `order_status_change` = NOW() WHERE `order_id` = '".mysqli_real_escape_string($con, $orderid)."';";
		return mysqli_query($con, $sql);
	} else return false;
}


function deleteReceipt($filename) 
{
	if($filename != "") {
		if(file_exists(ROOT_LEVEL."data/img/payment_receipts/".$filename)) {
			unlink(ROOT_LEVEL."data/img/payment_receipts/".$filename);
		}
	}
}


function randomPassword() {
    $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuwxyz0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 10; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function get_url($url) {
	$ch = curl_init ($url);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_exec ($ch);
	if (!curl_errno ($ch)) {
		$url = curl_getinfo ($ch, CURLINFO_EFFECTIVE_URL);
		return $url; 	
		curl_close ($ch);	
	}else{
		curl_close ($ch);	
		return "Error: ".curl_error($ch); 	
	}
}





// *** FUNCIONES DE CÁLCULO DE PRECIOS **** //



/*
Nombre de los errores:

Obteniendo cotizacion:
0xA1 No se pudo contectar a la base de datos
0xA2 La cotización no se actualizó en los ultimos 5 dias

0xB1 Se ha recibido un precio no numerico
0xB2 No se pudo contectar a la base de datos
0xB3 Los porcentajes de ganancia no se obtuvieron correctamente
*/


//error_reporting(0);

/*$connection = mysql_connect("localhost","root","20596");
mysql_select_db("steambuy");
mysql_query("SET NAMES 'utf8'",$connection);*/



/*
Funcion para obtener las cotizaciones de MXN y BRL con respecto al USD
*/
function obtener_cotiz_mxbr($con) {
	$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'brl_quote'");
	$brl_quote = mysqli_fetch_row($query);						
	$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'mxn_quote'");
	$mxn_quote = mysqli_fetch_row($query);	
	return array("br"=>$brl_quote[0], "mx"=>$mxn_quote[0]);
}

/*
Funcion para obtener las alicuotas de ganancia con los precios de región MXN y BRL
*/
function obtener_alicuotas_dto_region($con) {
	$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_menor32'");
	$alicuota_menor32 = mysqli_fetch_row($res);
	$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_mayor32'");
	$alicuota_mayor32 = mysqli_fetch_row($res);
	return array("menor_32_usd"=>$alicuota_menor32[0], "mayor_32_usd"=>$alicuota_mayor32[0]);
}


function getDollarQuote()
{
	global $con;
	if(!$con) return "error 0xA1";
	
	$res1 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'autoupdate_dollar_value'");
	$dollarvalue_autoupdate = mysqli_fetch_row($res1);
	
	if($dollarvalue_autoupdate[0] == 0) {	
		$res2 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'fixed_dollar_value'");
		$dollarQuote = mysqli_fetch_row($res2);
	} else if($dollarvalue_autoupdate[0] == 1) {
		$res3 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'dollar_retrieve_attemps'");
		$attemps = mysqli_fetch_row($res3);
		if($attemps[0] >= 5) {
			return "error 0xA2";	
		} else {
			$res4 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'updated_dollar_value'");
			$dollarQuote = mysqli_fetch_row($res4);
		}
	}
	return floatval($dollarQuote[0]);
}





function quickCalcGame($paymentMethod, $usd)
{
	if(!is_numeric($usd)) return "error 0xB1";
	$quote = getDollarQuote();
	if(!is_numeric($quote)) return $quote;
	return calcGamePrice($paymentMethod, $usd, $quote); 
}


function calcGamePrice($paymentMethod, $gamePrice, $quote) 
{
	// Paymentmethod: 1 = Boleta cuentadigital, 2 = transferencia bancaria
	if(!is_numeric($quote)) return $quote;
	if(!is_numeric($gamePrice)) return "error 0xB1";

    $total = 0;

    if($gamePrice > 0 && $gamePrice < 1.5) {
		
		$total = calcGamePrice2($paymentMethod, $gamePrice, $quote, 0.12);
	
	} else if($gamePrice >= 1.5 && $gamePrice < 2.5) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 1.5, $quote, 0.12);
        $secondprice = calcGamePrice2($paymentMethod, 2.5, $quote, 0.24);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 100;
        $total = (($gamePrice - 1.5) * 100) * $pesosPerCent + $firstprice;

	} else if($gamePrice >= 2.5 && $gamePrice < 5) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 2.5, $quote, 0.24);
        $secondprice = calcGamePrice2($paymentMethod, 5, $quote, 0.23);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 250;
        $total = (($gamePrice - 2.5) * 100) * $pesosPerCent + $firstprice;	
	
	} else if($gamePrice >= 5 && $gamePrice < 7.5) { 

		$firstprice = calcGamePrice2($paymentMethod, 5, $quote, 0.23);
        $secondprice = calcGamePrice2($paymentMethod, 7.5, $quote, 0.22);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 250;
        $total = (($gamePrice - 5) * 100) * $pesosPerCent + $firstprice;		
	
	} else if($gamePrice >= 7.5 && $gamePrice < 10) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 7.5, $quote, 0.22);
        $secondprice = calcGamePrice2($paymentMethod, 10, $quote, 0.21);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 250;
        $total = (($gamePrice - 7.5) * 100) * $pesosPerCent + $firstprice;	
	
	} else if($gamePrice >= 10 && $gamePrice < 15) {
		
		$firstprice = calcGamePrice2($paymentMethod, 10, $quote, 0.21);
        $secondprice = calcGamePrice2($paymentMethod, 15, $quote, 0.20);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 10) * 100) * $pesosPerCent + $firstprice;	
		 
	} else if($gamePrice >= 15 && $gamePrice < 20) {
		
		$firstprice = calcGamePrice2($paymentMethod, 15, $quote, 0.20);
        $secondprice = calcGamePrice2($paymentMethod, 20, $quote, 0.19);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 15) * 100) * $pesosPerCent + $firstprice;	
		 
	} else if($gamePrice >= 20 && $gamePrice < 25) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 20, $quote, 0.19);
        $secondprice = calcGamePrice2($paymentMethod, 25, $quote, 0.185);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 20) * 100) * $pesosPerCent + $firstprice;	
	
	} else if($gamePrice >= 25 && $gamePrice < 30) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 25, $quote, 0.185);
        $secondprice = calcGamePrice2($paymentMethod, 30, $quote, 0.18);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 25) * 100) * $pesosPerCent + $firstprice;	
	
	} else if($gamePrice >= 30 && $gamePrice < 35) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 30, $quote, 0.18);
        $secondprice = calcGamePrice2($paymentMethod, 35, $quote, 0.175);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 30) * 100) * $pesosPerCent + $firstprice;	
	
	} else if($gamePrice >= 35 && $gamePrice < 40) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 35, $quote, 0.175);
        $secondprice = calcGamePrice2($paymentMethod, 40, $quote, 0.17);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 35) * 100) * $pesosPerCent + $firstprice;	
	
	} else if($gamePrice >= 40 && $gamePrice < 45) {
		
		$firstprice = calcGamePrice2($paymentMethod, 40, $quote, 0.17);
        $secondprice = calcGamePrice2($paymentMethod, 45, $quote, 0.165);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 40) * 100) * $pesosPerCent + $firstprice;	
		 
	} else if($gamePrice >= 45 && $gamePrice < 50) {
		
		$firstprice = calcGamePrice2($paymentMethod, 45, $quote, 0.165);
        $secondprice = calcGamePrice2($paymentMethod, 50, $quote, 0.16);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 45) * 100) * $pesosPerCent + $firstprice;	
		
	} else if($gamePrice >= 50 && $gamePrice < 55) { 
	
		$firstprice = calcGamePrice2($paymentMethod, 50, $quote, 0.16);
        $secondprice = calcGamePrice2($paymentMethod, 55, $quote, 0.155);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 50) * 100) * $pesosPerCent + $firstprice;	
		
	} else if($gamePrice >= 55 && $gamePrice < 60) {
		
		$firstprice = calcGamePrice2($paymentMethod, 55, $quote, 0.155);
        $secondprice = calcGamePrice2($paymentMethod, 60, $quote, 0.15);
        $difference = $secondprice - $firstprice;
        $pesosPerCent = $difference / 500;
        $total = (($gamePrice - 55) * 100) * $pesosPerCent + $firstprice;	
		
	} else if($gamePrice >= 60) {	
	
		$total = calcGamePrice2($paymentMethod, $gamePrice, $quote, 0.15);
	}

	return round($total, 1);   
	
}

function calcGamePrice2($paymentMethod, $gamePrice, $quote, $profitpercentage) 
{
	// Paymentmethod: 1 = Boleta cuentadigital, 2 = transferencia bancaria

	if($paymentMethod == 1) {
		
		$productCost = $gamePrice * $quote; // El costo del juego final en pesos
		$profit = $productCost * $profitpercentage; // La ganancia: un porcentaje sobre el costo final en pesos
		$total = -1.05086 * ((-1 * ($profit + $productCost)) - 1.5125) + 1; // Precio final a vender; se le suman los costos de cuentadigital
		return $total;
		
	} else if($paymentMethod == 2) {
		$productCost = $gamePrice * $quote;
		$profit = $productCost * $profitpercentage;
		$total = -1.015 * (-1 * ($profit + $productCost)) + 1;
		return $total;
	}
}


?>