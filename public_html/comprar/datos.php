<?php
/*
Página de 2do paso de compra de productos.

datos por post: 
product_id: ID de producto a comprar. 
payment_method: medio de pago (1: boleta, 2: transf)
coupon_code: cupon de descuento (opcional)

*/

session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/purchase-functions.php");
$config = include("../global_scripts/config.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


// Obtenemos el ID del producto y medio de pago

if(isset($_POST["product_id"]) && isset($_POST["payment_method"])) {
	$product_id = $_POST["product_id"];
	$payment_method = $_POST["payment_method"];
	
	if($payment_method != 1 && $payment_method != 2) {
		header("Location: ../");
		exit;
	}
} else {
	header("Location: ../");
	exit;
}


// Analizamos existencia y validez del producto.

$purchase = new Purchase($con);

if($product_exists = $purchase->checkProductPurchasable($product_id)) {
	
	$productData = $purchase->productData;
	if($productArsPrices = $purchase->calcProductFinalArsPrices()) {
		
		// Variable de sesión que se debe tener para generar un pedido.
		$_SESSION["purchase_pending"] = 1;
		
		// price warning por si el precio del juego (por boleta) cambió desde el paso anterior
		$priceChangeWarning = false;
		if(isset($_SESSION["ticket_price_".$product_id])) {
			if($_SESSION["ticket_price_".$product_id] != $productArsPrices["ticket_price"]) $priceChangeWarning = true;
		}
		
		// Revisamos si hay un cupón de descuento válido
		$validCoupon = false;
		$couponDiscount = 0; // monto en pesos para este producto
		if(isset($_POST["coupon_code"])) {
			if($validCoupon = $purchase->checkCouponValidity($_POST["coupon_code"])) {
				if($payment_method == 1) {
					$couponDiscount = round($productArsPrices["ticket_price"] * ($purchase->couponData["coupon_discount_percentage"]/100), 1);
				} else if($payment_method == 2) {
					$couponDiscount = round($productArsPrices["transfer_price"] * ($purchase->couponData["coupon_discount_percentage"]/100), 1);
				}
				
			}
		}
		
		// Calculamos precio final
		if($payment_method == 1) {
			$productFinalArsPrice = $productArsPrices["ticket_price"] - $couponDiscount;
			
		} else if($payment_method == 2) {
			$productFinalArsPrice = $productArsPrices["transfer_price"] - $couponDiscount;
			$transferDiscount = $productArsPrices["ticket_price"] - $productArsPrices["transfer_price"]; // Dto. por transf. Sirve como referencia.
		}
		
		
	}
}
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />
        
        <title><?php
        if($product_exists) echo "Comprar ".$productData["product_name"]." - SteamBuy";	
		else echo "Error de compra - SteamBuy";	
		?></title>
        
        
        <link rel="shortcut icon" href="../favicon.ico">
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css" type="text/css">
        <link rel="stylesheet" href="resources/css/step2.css" type="text/css">
        <link rel="stylesheet" href="resources/css/shared-steps.css" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../resources/js/global-scripts.js"></script>
        
        <script type="text/javascript" src="resources/js/step2.js"></script>

    </head>
    
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
            
           		<?php
				if(!$product_exists) {
					echo "<div class='alert alert-danger' style='margin: 50px;text-align: center;'>No se encontró un producto válido o está fuera de stock.</div>";
				} else if(!$productArsPrices) {
					echo "<div class='alert alert-danger' style='margin: 50px;text-align: center;'>Ocurrió un problema calculando el precio de este producto.</div>";
				} else {
						?>
                        <div class="purchase_steps">
                            <a href="javascript:document.goback.submit();" style="color:inherit !important;"><div class="step previous_step">Elegir medio de pago</div></a>
                            <div class="spacer"></div>
                            <div class="step current_step">Ingresar datos</div>
                            <div class="spacer"></div>
                            <div class="step">Instrucciones de pago</div>
                        </div>
                    	
                        <form name="goback" action="pago.php" method="post">
                        	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                            <?php
							if($validCoupon) echo "<input type='hidden' name='coupon_code' value='".$_POST["coupon_code"]."' />";
							?>
                        </form>
                        
                        <?php
						if($priceChangeWarning) 
						{
						?>
                            <div class="alert alert-warning alert-dismissable" style="margin-top:25px;">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            El precio del producto cambió desde el último paso, esto se puede deber a que haya finalizado/comenzado una oferta en el transcurso del paso anterior.
                            </div>
                        <?php
						}

						if($productData["product_external_limited_offer"] == 1) 
						{
							?>
                       		<div class="alert alert-warning alert-dismissable offer_warning">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                Este juego se encuentra en oferta externa limitada, una vez pagado, deberás informar el pago en la sección de 
                                <a href="../informar/" target="_blank">informar pago</a> antes de que termine la oferta, <strong>de lo contrario la oferta NO será aplicable</strong> y deberás cambiar tu producto.&nbsp;<?php
                                
                                $end_hour = date("H:i:s", strtotime($productData["product_external_offer_endtime"]));
    
                                if($productData["product_external_offer_endtime"] != "0000-00-00 00:00:00" && $end_hour != "00:00:00") {
                                    echo "La oferta de este juego finaliza el <strong>".date("d/m/y H:i:s", strtotime($productData["product_external_offer_endtime"]))."</strong>.";
                                } else if($productData["product_external_offer_endtime"] != "0000-00-00 00:00:00") {
                                    echo "La oferta de este juego finaliza el <strong>".date("d/m/y", strtotime($productData["product_external_offer_endtime"]))." (medianoche)</strong>.";
                                } else if($productData["product_sellingsite"] == 2) {
                                    echo "Te recomendamos informar el pago lo antes posible ya que Amazon no especifica la fecha de fin de oferta de este juego.";
                                } else {
                                    echo "Revisa la <a href='".$productData["product_site_url"]."' target='_blank'>página de venta</a> externa del producto para saber cuándo finaliza la oferta.";	
                                } ?>
                            </div>
                        <?php
						}
						?>
                        <div class="clearfix" style="margin:50px 10px 0 10px;position:relative;">
                        
                        	<?php require_once("resources/php/purchase-detail.php"); ?>
                        
                        	<div class="purchase-form-box">
                            
                            	<div><strong>Medio de pago:</strong> <?php 
								if($payment_method == 1) echo "Cupón de pago";
								else if($payment_method == 2) echo "Transferencia bancaria"; ?>
                                &nbsp;&nbsp;&nbsp;<span style="font-size:12px;">(<a href="javascript:document.goback.submit();">cambiar</a>)</span></div>
                                
                                <form action="generado.php" method="post" id="purchase-form">
                                	<input type="hidden" name="product_id" id="product-id" value="<?php echo $product_id; ?>" />
                                    <input type="hidden" name="payment_method" value="<?php echo $payment_method; ?>" />
                                	<?php
									if($validCoupon) {
										?>
                                        <input type="hidden" name="coupon_code" value="<?php echo $_POST["coupon_code"]; ?>" />
                                        <?php	
									}
									?>
                                    <div style="margin-top:25px">
                                        Nombre y apellido:
                                        <input type="text" name="buyer_name" id="buyer-name" class="form-control" maxlength="30"<?php if(isset($_COOKIE["client_name"])) echo " value = '".$_COOKIE["client_name"]."'"; ?> />
                                    </div>
                                    <div style="margin-top:15px">
                                        Dirección e-mail:
                                        <input type="text" name="buyer_email" id="buyer-email" class="form-control" maxlength="50"<?php if(isset($_COOKIE["client_email"])) echo " value = '".$_COOKIE["client_email"]."'"; ?> />
                                    </div>
                                    <div style="margin-top:25px"><label><input type="checkbox" name="remember_data" <?php if(isset($_COOKIE["client_name"]) && isset($_COOKIE["client_email"])) echo "checked"; ?>> Recordar e-mail y nombre para futuras compras.</label></div>
                            	</form>
                                
                                <div class="alert alert-danger" id="error_list">
                                    <span class="glyphicon glyphicon-remove" style="float:right;cursor:pointer;" onClick="$(this).parent('#error_list').slideUp('slow');"></span>
                                    <p></p>
                                </div>
                            </div>
                            
                            
                        
                        </div>

						<hr style="margin-top: 20px;margin-bottom:0;border-color:#e3e3e3" />

						<div class="purchase_footer clearfix">
							
                            <?php
							if($productData["product_external_limited_offer"] == 1) {
								?>
								<div class="checkbox tos_warning">
									<label><input type="checkbox" id="tos_checkbox"> Acepto los términos y condiciones, y acepto en caso de no informar el pago a tiempo, no recibir este juego, teniendo que elegir un cambio de productos.</label>
                                </div>
								<?php	
							} else {
								echo "<div style='color:#444;float:left'>Al hacer click en 'continuar' das por aceptado los <a href='../condiciones/' target='_blank'>términos y condiciones</a>.</div>";
							}
							?>
                            
								<input type="submit" class="btn btn-success btn-lg" id="proceed-btn" value="Continuar" />
						</div>
                    <?php
				}
				?>
			</div>
            
            <?php require_once("../global_scripts/php/footer.php"); ?>
            
		</div>
	</body>
</html>