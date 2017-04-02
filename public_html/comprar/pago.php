<?php
/*
Página de 1er paso de compra de productos.

datos por post: 
product_id: ID de producto a comprar. 
coupon_code: cupon de descuento (opcional)


<a futuro puede ser id de carrito, o un array de id's de productos>

*/

session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");
$config = include("../global_scripts/config.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


// Obtenemos el ID del producto de post o sesión

if(isset($_POST["product_id"])) {
	$product_id = $_POST["product_id"];
} else {
	header("Location: ../");
	exit;
}




// Analizamos existencia y validez del producto.


$purchase = new Purchase($con);

if($product_exists = $purchase->checkProductPurchasable($product_id)) {
	
	$productData = $purchase->productData;
	
	if($productArsPrices = $purchase->calcProductFinalArsPrices()) {
		
		$transferDiscount = $productArsPrices["ticket_price"] - $productArsPrices["transfer_price"];
		$_SESSION["ticket_price"] = $productArsPrices["ticket_price"]; // Se guarda para comparar si en el próximo paso cambió el precio
	
		// Revisamos si hay un cupón de descuento y si es válido
		$couponSent = false;
		$validCoupon = false;
		if(isset($_POST["coupon_code"])) {
			$couponSent = true;
			if($validCoupon = $purchase->checkCouponValidity($_POST["coupon_code"])) {
				$couponTicketDiscount = round($productArsPrices["ticket_price"] * ($purchase->couponData["coupon_discount_percentage"]/100) ,2);
			}
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
        <link rel="stylesheet" href="design/purchase_pg.css" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js"></script>
		<script type="text/javascript" src="scripts/purchase_pg.js"></script>

    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">

                <?php
				
				if(!$product_exists) {
					echo "<div class='alert alert-danger' style='margin: 50px;text-align: center;'>No se encontró un producto válido o está fuera de stock.</div>";
				} else if(!$productArsPrices) {
					echo "<div class='alert alert-danger' style='margin: 50px;text-align: center;'>Ocurrió un problema obteniendo los precios de este producto.</div>";
				} else {
						?>
                        <div class="purchase_steps">
							<div class="step current_step">Elegir medio de pago</div>
							<div class="spacer"></div>
							<div class="step">Ingresar datos</div>
							<div class="spacer"></div>
							<div class="step">Instrucciones de compra</div>
						</div>

						<div class="product_info">
							<div class="pi_img"><img src="../data/img/game_imgs/<?php echo $productData["product_mainpicture"]; ?>" alt="<?php echo $productData["product_name"]; ?>"/></div>
							<div class="pi_name"><span style="font-size:19px;"><?php echo $productData["product_name"]; ?></span>
							<div class="pi_drm"><?php if($productData["product_platform"] == 1) echo "Activable en Steam"; else if($productData["product_platform"] == 2) echo "Activable en Origin"; ?></div></div>
							<div class="pi_price"><?php
							if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") {
								echo $productData["product_finalprice"]." ARS";
							} else echo "&#36;".$productArsPrices["ticket_price"]." ARS"
							?></div>
                            <?php
							if($productData["product_sellingsite"] == 3) {
								echo "<div class='pi_offerband'>En oferta de Humble Bundle</div>";
							} else if($productData["product_sellingsite"] == 4) {
								echo "<div class='pi_offerband'>En oferta de Bundlestars</div>";
							} else if($productData["product_has_customprice"] == 1) {
								echo "<div class='pi_offerband'>En oferta de SteamBuy</div>";
							} else if($productData["product_external_limited_offer"] == 1) {
								if($productData["product_sellingsite"] == 1) {
									echo "<div class='pi_offerband'>En oferta de Steam</div>";
								} else if($productData["product_sellingsite"] == 2) {
									echo "<div class='pi_offerband'>En oferta de Amazon</div>";
								} 
							}
							?>
                        </div>		
						
                        <div class="clearfix" style="margin:30px 30px 0 30px;">
                            
                            <div class="payment-options">
                            	<h4 style="margin-bottom:8px;">Medio de pago</h4>
                                <div class="list-group">
                                    <a href="javascript:void(0);" class="list-group-item active" id="payoption1">
                                        <div style="height: 32px;"><h4 class="list-group-item-heading">Cupón de pago</h4></div>
                                        <p class="list-group-item-text">Abona en <strong>Rapipago</strong>, <strong>Pago Fácil</strong>, <strong>Provincia Pagos</strong>, <strong>Cobro Express</strong> u otras sucursales 
                                        presentando un cupón de pago. Luego de entre 1 y 48 hs se acreditará el pago y recibirás el juego. </p>
                                    </a>
                                    <a href="javascript:void(0);" class="list-group-item" id="payoption2">
                                        <div style="height: 32px;"><h4 class="list-group-item-heading">Transferencia bancaria</h4></div>
                                        <p class="list-group-item-text">Realiza un depósito bancario o haz una transferencia por home banking sin moverte de tu casa. Entre 1-24hs luego de acreditarse recibirás el juego.</p>
                                    	<div class="banktransfer-discount-tag">-$<?php echo $transferDiscount; ?> dto.</div>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="discount-coupon-options">
                            	<?php
								if($couponSent && $validCoupon) {
									echo "Cupón <strong>".$purchase->couponData["coupon_code"]."</strong> -".$purchase->couponData["coupon_discount_percentage"]."% dto.";
									?>
                                    <form action="" method="post" style="display:inline-block;">
                                    	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                                    	<button class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Eliminar"><span class="glyphicon glyphicon-remove"></span></button>
                                    </form>
                                    <?php
								} else {
									?>
                                    Si tienes un cupón de descuento ingrésalo a continuación:
                                    <form method="post" action="">
                                    <div class="input-group" style="margin-top:10px;">
                                        <input type="text" class="form-control" name="coupon_code" placeholder="Código de cupón">
                                        <span class="input-group-btn">
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                                            <input class="btn btn-default" type="submit" value="Aplicar" />
                                        </span>
                                    </div>
                                    </form>
                                    <?php
                                    if($couponSent && !$validCoupon) 
                                    {
                                        ?>
                                        <div class="alert alert-danger alert-dismissable" style="margin-top:10px;" >
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <?php
                                            if($purchase->couponCheckError == 1) {
                                                echo "El cupón de dto. no existe o expiró";	
                                            } else if($purchase->couponCheckError == 2) {
                                                echo "El cupón de dto. no aplica para este producto";
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
								}
								?>
                            </div>
                            
                        </div>

						<hr style="margin-top: 27px;margin-bottom: 0;border-color:#e3e3e3" />

						<div class="purchase_footer clearfix">
							<div class="price-detail">
                            	<table>
                                	<tr><td style="width:400px;"><?php echo $productData["product_name"]; ?></td><td>$<?php echo $productArsPrices["ticket_price"]; ?></td></tr>
                                    <tr id="row-transfer-discount"><td style="width:400px;">Descuento pago transf. bancaria</td><td id="transfer-discount-ammount"></td></tr>
                                    <tr id="row-subtotal" class="count-total"><td style="width:400px;"><strong>Subtotal</strong></td><td id="subtotal-ammount"></td></tr>
                                    <?php
									if($couponSent && $validCoupon) {
										echo "
										<tr>
											<td style='width:400px;'>Descuento cupón ".$purchase->couponData["coupon_code"]." ".$purchase->couponData["coupon_discount_percentage"]."%</td>
											<td id='coupon-discount-ammount'>-$".$couponTicketDiscount."</td>
										</tr>";
									}
									?>
                                    <tr class="count-total">
                                    	<td style="width:400px;"><span style="font-size:16px;font-weight:bold">Total</span></td>
                                        <td><span style="font-size:16px;font-weight:bold" id="final-price-ars">$<?php 
										if($validCoupon) echo $productArsPrices["ticket_price"] - $couponTicketDiscount;
										else echo $productArsPrices["ticket_price"];
										?> ARS</span></td>
                                    </tr>
                                </table>
                            </div>
							<form action="confirmar.php" method="post" id="buyform">
                            	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>"/>
                                <input type="hidden" name="payment_method" id="payment_method" value="1" />
                                <?php
								if($validCoupon) echo "<input type='hidden' name='coupon_code' value='".$_POST["coupon_code"]."'/>";
								?>
								<input type="submit" class="btn btn-success btn-lg" id="proceedbtn" value="Continuar" />
							</form>
						</div>
						<?php	
					
				}

				if($product_exists && $productArsPrices !== false) {
					echo "
					<script type='text/javascript'>
						var product_price = ".$productArsPrices["ticket_price"].";
						var transfer_discount = ".$transferDiscount.";
						var discount_coupon = ".($validCoupon ? "true" : "false").";\n";
						if($validCoupon) echo "var coupon_disc_percent = ".$purchase->couponData["coupon_discount_percentage"].";\n";
					echo "</script>";
				}
		?>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>