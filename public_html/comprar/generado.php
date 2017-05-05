<?php
/*
Página de 3er paso de compra de productos.

datos por $_post: product_id, payment_method, coupon_code (opcional), buyer_name, buyer_email, remember_data (opcional)
dato por $_session: purchase_pending = 1
*/

session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/purchase-functions.php");
require_once("../global_scripts/email/mailer.php");
require_once("resources/php/order-validation-tools.php");
$config = include("../global_scripts/config.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


// Obtenemos el ID del producto y medio de pago

if(isset($_POST["product_type"]) && isset($_POST["product_id"]) && isset($_POST["payment_method"]) && isset($_POST["buyer_name"]) && isset($_POST["buyer_email"])) {
	
	$product_type = $_POST["product_type"];
	$product_id = $_POST["product_id"];
	$payment_method = $_POST["payment_method"];
	$buyer_name = $_POST["buyer_name"];
	$buyer_email = $_POST["buyer_email"];
	
} else {
	header("Location: ../");
	exit;
}


// Validar nombre, email, mail (si no cumple muestra error y termina script)
validateInitialData($buyer_name, $buyer_email, $payment_method);


// Validar var de sesión (permite que sólo se puedan generar pedidos si se lo hizo vía pasos de compra, y evita pedidos repetidos al refreshear
$session_auth = false;
if(isset($_SESSION["purchase_pending"])) {
	if($_SESSION["purchase_pending"] == 1) {
		$session_auth = true;
	}
	//unset($_SESSION["purchase_pending"]);
}


if($session_auth) {
	
	// Analizamos existencia y validez del producto.
	$purchase = new Purchase($con);
	
	if($product_exists = $purchase->checkProductPurchasable($product_type, $product_id)) {
		
		$productData = $purchase->productData;
		
		
		// Chequear que se haya enviado la SteamURL si era necesaria.
		if($product_type == 1 && $productData["product_sellingsite"] == 1 && $productData["product_has_limited_units"] == 0) {
			if(!isset($_POST["buyer_steamurl"])) {
				header("Location: ../");
				exit;
			} else $buyer_steamurl = $_POST["buyer_steamurl"];
		} else $buyer_steamurl = "";
		
		
		if($productArsPrices = $purchase->calcProductFinalArsPrices()) {
			
			
			// Chequear si el precio del juego cambió
			$priceChange = false;
			if(isset($_SESSION["ticket_price_".$product_id])) {
				if($_SESSION["ticket_price_".$product_id] != $productArsPrices["ticket_price"]) {
					$priceChange = true;
				}
				unset($_SESSION["ticket_price_".$product_id]);
			}
			
			if(!$priceChange) {
				
				// Revisamos si hay un cupón de descuento válido
				$sentCoupon = false;
				$validCoupon = false;
				$couponCode = ""; // para guardar en db
				$couponDiscount = 0; // monto en pesos para este producto
				if(isset($_POST["coupon_code"])) {
					
					$sentCoupon = true;
					
					if($validCoupon = $purchase->checkCouponValidity($_POST["coupon_code"])) {
						$couponCode = $_POST["coupon_code"];
						if($payment_method == 1) {
							$couponDiscount = round($productArsPrices["ticket_price"] * ($purchase->couponData["coupon_discount_percentage"]/100), 1);
						} else if($payment_method == 2) {
							$couponDiscount = round($productArsPrices["transfer_price"] * ($purchase->couponData["coupon_discount_percentage"]/100), 1);
						}
						
					}
				}

				// Calculamos precio final
				if($payment_method == 1) $productFinalArsPrice = $productArsPrices["ticket_price"] - $couponDiscount;
				else if($payment_method == 2) $productFinalArsPrice = $productArsPrices["transfer_price"] - $couponDiscount;

				
			}	
		}
	}
}




$error = 1;
if($session_auth && $product_exists && $productArsPrices != false && !$priceChange && (!$sentCoupon || ($sentCoupon && $validCoupon))) {
	
	$error = 0;
	
	// Chequear si se quieren guardar los datos del comprador
	checkRememberBuyerData($buyer_name, $buyer_email, $buyer_steamurl);

	
	if($product_type == 1) {
		
		$product_name = $purchase->productData["product_name"];
		
		if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") $usdPriceRef = 0;
		else $usdPriceRef = $productData["product_finalprice"]; 
		
		// Generar orden
		if($purchase->createGameOrder($payment_method, $product_name, $product_id, $productData["product_sellingsite"], $productData["product_site_url"], 
		$productData["product_external_limited_offer"], $usdPriceRef, $productFinalArsPrice, $buyer_name, $buyer_email, $buyer_steamurl, $_SERVER["REMOTE_ADDR"], $couponCode, $couponDiscount))
		{
			
			$mail_data = array(
				"receiver_name"=>$buyer_name,
				"order_id"=>$purchase->orderInfo["order_id"],
				"order_password"=>$purchase->orderInfo["order_password"],
				"product_name"=>$product_name,
				"order_ars_price"=>$productFinalArsPrice,
				"payment_method"=>$payment_method,
				"product_external_discount"=>$productData["product_external_limited_offer"],
				"product_sellingsite"=>$productData["product_sellingsite"],
				"product_site_url"=>$productData["product_site_url"],
				"order_fromcatalog"=>1
			);
			if($payment_method == 1) {
				$mail_data["order_purchaseticket_url"] = $purchase->orderInfo["order_purchaseticket"];	
			}
			if($productData["product_external_limited_offer"] == 1) {
				$mail_data["product_external_offer_endtime"] = $productData["product_external_offer_endtime"];	
			}
							
			$mail = new Mail;
			$mail->prepare_email("pedido_juego_generado", $mail_data);
			$mail->add_address($buyer_email, $buyer_name);
						
			if(!@$mail->send()) $mailError = true;
			else $mailError = false;
				
			$order_error = 0;
			$orderinfo = $purchase->orderInfo;
			
		} else $order_error = $purchase->error;
		
	} else if($product_type == 2) {

		$product_name = $purchase->productData["name"];
		
		// Generar orden
		if($purchase->createGiftCardOrder($payment_method, $product_name, $product_id, $productFinalArsPrice, $buyer_name, $buyer_email, $_SERVER["REMOTE_ADDR"], $couponCode, $couponDiscount))
		{
			
			$mail_data = array(
				"receiver_name"=>$buyer_name,
				"order_id"=>$purchase->orderInfo["order_id"],
				"order_password"=>$purchase->orderInfo["order_password"],
				"product_name"=>$product_name,
				"order_ars_price"=>$productFinalArsPrice,
				"payment_method"=>$payment_method
			);
			if($payment_method == 1) {
				$mail_data["order_purchaseticket_url"] = $purchase->orderInfo["order_purchaseticket"];	
			}
			
			$mail = new Mail;
			$mail->prepare_email("pedido_giftcard_generado", $mail_data);
			$mail->add_address($buyer_email, $buyer_name);
									
			if(!@$mail->send()) $mailError = true;
			else $mailError = false;
				
			$order_error = 0;
			$orderinfo = $purchase->orderInfo;
			
		} else $order_error = $purchase->error;
	}



}



?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />
        
        <title><?php if($error) echo "Pedido - SteamBuy"; else echo "Pedido generado - SteamBuy"; ?></title>
        
        
        <link rel="shortcut icon" href="../favicon.ico">
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css" type="text/css">
        <link rel="stylesheet" href="resources/css/shared-steps.css" type="text/css">
		<link rel="stylesheet" href="resources/css/step3.css" type="text/css">
        
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../resources/js/global-scripts.js"></script>
        

    </head>
    
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
            
            	<?php
				if(!$session_auth) {
					echo "<div class='alert alert-danger' style='margin:50px'>Ha expirado la sesión, puede deberse a que ya realizaste este pedido, 
					o estás intentando generar más de un pedido simultáneamente. <a href='../'>Volver a la tienda</a>.</div>";
				} else if(!$product_exists) {
					echo "<div class='alert alert-danger' style='margin:50px'>El producto no es válido o no existe. <a href='../'>Volver a la tienda</a>.</div>";
				} else if(!$productArsPrices) {
					echo "<div class='alert alert-danger' style='margin:50px'>Hubo un problema obteniendo el precio de este juego. <a href='../'>Volver a la tienda</a>.</div>";
				} else if($priceChange) {
					echo "<div class='alert alert-danger' style='margin:50px'>El precio del juego cambió durante el proceso de compra. <a href='../'>Volver a la tienda</a>.</div>";
				} else if($sentCoupon && !$validCoupon) {
					echo "<div class='alert alert-danger' style='margin:50px'>El cupón de descuento no es válido. <a href='../'>Volver a la tienda</a>.</div>";
				} else {
					
					if($order_error !== 0) {
						echo "<div class='alert alert-danger' style='margin:50px'>Ha ocurrido un error generando el pedido (".$order_error."). <a href='../'>Volver a la tienda</a>.</div>";
					} else if($order_error === 0) {
						?>
                        
                        <div class="purchase_steps">
                            <div class="step previous_step">Elegir medio de pago</div>
                            <div class="spacer"></div>
                            <div class="step previous_step">Ingresar datos</div>
                            <div class="spacer"></div>
                            <div class="step current_step">Instrucciones de pago</div>
                        </div>
                        
                            <div class="purchase_instructions">
                                <h4 class="pi_title">El pedido se ha generado</h4>
                                <div class="alert alert-info" style="font-size: 14px;margin-top:30px; text-align:justify">
                                	Guarda estos datos para poder informar el pago luego, revisar los detalles de tu pedido, o para soporte:<br/>
                                    ID de pedido: <strong><?php echo $orderinfo["order_id"]; ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;clave de pedido: <strong><?php echo $orderinfo["order_password"]; ?></strong> <br/>
                                </div>
                                <?php
								if($payment_method == 1) {
									?>
									<div class="pi_instructions">Se ha generado tu pedido de <strong>$<?php echo $productFinalArsPrice; ?> ARS</strong> por <strong><?php echo $product_name; ?></strong>, el siguiente paso es imprimir y abonar el cupón de pago en cualquier sucursal de <strong>Rapipago</strong>,
                                    <strong>Pago Fácil</strong>, <strong>Ripsa</strong>, <strong>Cobroexpress</strong>, <strong>Bapropagos</strong>, u otras cadenas de pago especficadas en la boleta o cupón de pago.<br></div>
                                    
                                    <div style="text-align:center; margin:25px 0;">
                                    	<a href="<?php echo $orderinfo["order_purchaseticket"]; ?>" target="_blank" class="btn btn-primary btn-lg">Ver cupón de pago&nbsp;&nbsp;<span class="glyphicon glyphicon-barcode"></span></a>
                                        <br/><a href="<?php 
										$split = explode("?id=",$orderinfo["order_purchaseticket"]);
										echo "https://www.cuentadigital.com/ticket.php?id=".substr($split[1], 4, 8);
										 ?>" target="_blank">Ver en formato ticket</a>
                                    </div>  
                                        
                                    <div class="pi_instructions">Una vez abonado, <strong>el pago tomará entre 12 y 48 horas en acreditarse</strong> automáticamente, normalmente al día siguiente está acreditado. <br/>
                                    El pedido será enviado por e-mail o por Steam (según sea el caso) durante día en que se acredita el pago. Podés ver el estado de tu pago en el <a href="https://www.cuentadigital.com/area.php?name=Search&query=<?php echo $split[1]; ?>" target="_blank">siguiente enlace</a>, que también se te ha sido enviado por e-mail.</div>                                   
                                    <?php
								} 
								else if($payment_method == 2) 
								{
									?>
                                    <div class="pi_instructions">Se ha generado tu pedido de <strong>$<?php echo $productFinalArsPrice; ?> ARS</strong> por <strong><?php echo $product_name; ?></strong>, el siguiente paso es <strong>realizar el depósito o transferencia bancaria</strong> a la cuenta
                                    bancaria <strong>especificada a continuación</strong>.<br></div>

                                    <div class="pi_transferdata">
                                        <div><strong>Banco:</strong> ICBC</div>
                                        <div><strong>Cuenta:</strong> Caja de ahorro $ 0849/01118545/07</div>
                                        <div><strong>CBU:</strong> 0150849701000118545070</div>
                                        <div><strong>Titular:</strong> Rodrigo Fernandez Nuñez</div>
                                        <div><strong>CUIL:</strong> 23-35983336-9</div>
                                        <div><strong>Monto:</strong> $<?php echo $productFinalArsPrice; ?> ARS</div>
                                    </div>
                                    
                                    <div class="pi_instructions">Una vez realizada la transferencia o depósito, <strong>envía una foto o imágen del comprobante de pago</strong> en la sección de <strong><a href="../informar/" target="_blank">informar pago</a></strong> para que identifiquemos tu pago. 
                                    El juego se enviará por e-mail o por Steam (según sea el caso) <strong>dentro de las siguientes 12 horas hábiles</strong> de haberse acreditado el pago (las transferencias son instantáneas en horario hábil).</div>                                 
                                    <?php
								}
								
								if($product_type == 1 && $productData["product_sellingsite"] == 1 && $productData["product_has_limited_units"] == 0) {
									echo "<div class='alert alert-info' style='margin-top: 15px;padding-bottom: 15px;'>Este juego se envía como Steam gift, para adelantar el proceso de envío, envianos una solicitud de amistad de Steam con la cuenta que deseás recibir el juego a nuestra cuenta: <a href='http://steamcommunity.com/id/steambuyarg/' target='_blank'><span class='label label-primary'>http://steamcommunity.com/id/steambuyarg/</span></a></div>";	
								}
								
								
								if($product_type == 1 && $productData["product_external_limited_offer"] == 1) 
								{
									?>
									<div class="alert alert-warning pi_offerwarning">
										<?php
                                        if($productData["product_sellingsite"] == 1) {
                                            echo "Este juego posee una oferta limitada de reventa de Steam. Es necesario que el pago se encuentre <strong>acreditado con tiempo antes de que la oferta finalice</strong>. De lo contrario deberás elegir un cambio de pedido o un reembolso. ";
                                        } else {
                                            ?>
                                            Este juego posee una oferta externa de tiempo limitado, deberás <strong><a href="../informar/" target="_blank">informar el pago</a></strong> 
                                            antes de que termine la oferta para que te lo reservemos.&nbsp;
                                            <?php
                                        }
    
                                        $end_hour = date("H:i:s", strtotime($productData["product_external_offer_endtime"]));
    
                                        if($productData["product_external_offer_endtime"] != "0000-00-00 00:00:00" && $end_hour != "00:00:00") {
                                            echo "La oferta de este juego finaliza el <strong>".date("d/m/y H:i:s", strtotime($productData["product_external_offer_endtime"]))."</strong>.";
                                        } else if($productData["product_external_offer_endtime"] != "0000-00-00 00:00:00") {
                                            echo "La oferta de este juego finaliza el <strong>".date("d/m/y", strtotime($productData["product_external_offer_endtime"]))." (medianoche)</strong>.";
                                        } else if($productData["product_sellingsite"] == 2) {
                                            echo "Te recomendamos informar el pago lo antes posible ya que Amazon no especifica la fecha de fin de oferta de este juego.";
                                        } else {
                                            echo "Revisa la <a href='".$productData["product_site_url"]."' target='_blank'>página de venta</a> externa del producto para saber cuándo finaliza la oferta.";	
                                        }
                                        ?>
                                    </div>
									<?php	
								}

								if($mailError) {
									?>
                                    <div class="alert alert-danger" style="font-size: 14px;margin-top:30px; text-align:justify">Ha ocurrido un error intentando enviar el e-mail con los datos de pedido, tomá nota del <strong>ID</strong> y <strong>clave de pedido</strong> de esta página ya que <strong>no se pudieron enviar por e-mail</strong>, disculpa las molestias.</div>
                                	<?php	
								}
								?>
                                                                
                                <div class="pi_return"><a href="../">Volver a la página principal</a></div>
							</div>
                        
                        
                        <?php
					}
				}
				?>

			</div>
            
            <?php require_once("../global_scripts/php/footer.php"); ?>
            
		</div>
	</body>
</html>