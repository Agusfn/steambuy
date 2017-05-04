<?php
/*
Página de 1er paso de compra de productos.

datos por post: 
product_id: ID de producto a comprar. 
coupon_code: cupon de descuento (opcional)


<a futuro puede ser id de carrito, o un array de id's de productos>

*/
require_once("../../config.php");
require_once(ROOT."app/lib/user-page-preload.php");

require_once(ROOT."app/lib/purchase-functions.php");

$login->restricted_page($loggedUser, 0);


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
		
		// Guardar precio para comparar en el sgte paso si cambió
		$_SESSION["ticket_price_".$product_id] = $productArsPrices["ticket_price"]; 
	
	
		// Revisamos si hay un cupón de descuento y si es válido
		$couponSent = false;
		$validCoupon = false;
		$couponDiscount = 0; // monto en pesos de dto. para este producto 
		
		if(isset($_POST["coupon_code"])) {
			$couponSent = true;
			if($validCoupon = $purchase->checkCouponValidity($_POST["coupon_code"])) {
				$couponDiscount = round($productArsPrices["ticket_price"] * ($purchase->couponData["coupon_discount_percentage"]/100),  1);
			}
		}
		
		$transferDiscount = $productArsPrices["ticket_price"] - $productArsPrices["transfer_price"];
		$productFinalArsPrice = $productArsPrices["ticket_price"] - $couponDiscount; // Precio final (para boleta, que es lo seleccionado por defecto)
	
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
        
		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>
        
        <link rel="stylesheet" href="resources/css/step1.css" type="text/css">
        <link rel="stylesheet" href="resources/css/shared-steps.css" type="text/css">
		<script type="text/javascript" src="resources/js/step1.js"></script>

    </head>
    
    <body>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
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
							<div class="step">Confirmar datos</div>
							<div class="spacer"></div>
							<div class="step">Instrucciones de pago</div>
						</div>
						
                        
                        <div class="clearfix" style="margin:50px 10px 0 10px;position:relative;">
                        
                        	<?php require_once("resources/php/purchase-detail.php"); ?>
                            
                            <div class="payment-options-column">
								
                                <div class="payment-options">
                                    <div class="list-group">
                                        <a href="javascript:void(0);" class="list-group-item active" id="payoption1">
                                            <div style="height: 25px;"><h4 class="list-group-item-heading">Cupón de pago</h4></div>
                                            <p class="list-group-item-text">Abona en <strong>Rapipago</strong>, <strong>Pago Fácil</strong>, <strong>Provincia Pagos</strong>, <strong>Cobro Express</strong> u otras sucursales 
                                            presentando un cupón de pago. Luego de entre 1 y 48 hs se acreditará el pago y recibirás el juego. </p>
                                        </a>
                                        <a href="javascript:void(0);" class="list-group-item" id="payoption2">
                                            <div style="height: 25px;"><h4 class="list-group-item-heading">Transferencia bancaria</h4></div>
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
                        
                        </div>
					
						<hr style="margin-top: 20px;margin-bottom:0;border-color:#e3e3e3" />

						<div class="purchase_footer clearfix">
							
							<form action="confirmar.php" method="post" id="buyform">
                            	<input type="hidden" name="product_id" value="<?php echo $product_id; ?>"/>
                                <input type="hidden" name="payment_method" id="payment_method" value="1" />
                                <?php
								if($validCoupon) echo "<input type='hidden' name='coupon_code' value='".$_POST["coupon_code"]."'/>";
								?>
								<input type="submit" class="btn btn-success btn-lg" id="proceed-btn" value="Continuar" />
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
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>