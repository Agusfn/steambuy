							<?php
							/* Cuadro de detalle del precio del producto. En los pasos 1 y 2 de compra se muestra.
							Variables necesarias:  $productData, $productArsPrices, $validCoupon, $purchase (clase), $couponDiscount, $productFinalArsPrice
							*/
							?>
                            
                            <div class="purchase-detail">
                                
                                <div style="height:280px;"> <!-- espacio para productos -->
                                
                                    <div class="purchase-product clearfix">
                                        <div class="pp-img"><img src="../data/img/game_imgs/224x105/<?php echo $productData["product_mainpicture"]; ?>" alt="<?php echo $productData["product_name"]; ?>"/></div>
                                        <div style="float: left;margin-left: 20px;">
                                        	<div class="pp-name"<?php 
                                            if(strlen($productData["product_name"]) > 28) {
                                            	echo " style='margin-top:5px;font-size:14px;'";	
                                            } ?>>
                                            	<?php echo $productData["product_name"]; ?>
                                            </div>
                                            <div class="pp-drm"><?php if($productData["product_platform"] == 1) echo "Activable en Steam"; else if($productData["product_platform"] == 2) echo "Activable en Origin"; ?></div>
										</div>
                                        <?php
                                        if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") {
                                            echo "<div class='pp-price'>&#36;".$productData["product_finalprice"]."</div>";
                                        } else {
											if($productData["product_has_customprice"] == 1 || $productData["product_external_limited_offer"] == 1) {
												echo "
												<div class='pp-price' style='margin-top:15px;'>
													<div class='pp-listprice'>&#36;".quickCalcGame(1, $productData["product_listprice"])."</div>
													&#36;".$productArsPrices["ticket_price"]."
												</div>";
											} else {
												echo "<div class='pp-price'>&#36;".$productArsPrices["ticket_price"]."</div>";
											}
										}
                                        ?>
                                    </div>
                                    

                                </div>

                                <div class="price-detail">
                                	<?php
									$showTransfDiscLine = true;
									if(isset($payment_method)) {
										if($payment_method == 1) $showTransfDiscLine = false;
									}
									if($showTransfDiscLine) {
										?>
										<div id="row-transfer-discount">Descuento pago transf. bancaria <span id="transfer-discount-ammount"><?php echo "-&#36;".$transferDiscount; ?></span></div>
										<?php
                                    }

									if($validCoupon) {
										if($showTransfDiscLine) {
											?>
											<div id="row-subtotal">Subtotal<span id="subtotal-ammount"><?php echo "&#36;".$productArsPrices["transfer_price"]; ?></span></div>
                                            <?php
										}
										?>
										<div>Descuento cupón <?php echo $purchase->couponData["coupon_code"]." ".$purchase->couponData["coupon_discount_percentage"]."%"; ?>
										<span id='coupon-discount-ammount'><?php echo "-&#36;".$couponDiscount; ?></span></div>	
                                        <?php
										
									}
									?>
                                    <div class="row-total">Total<span id="total-ammount">$<?php echo $productFinalArsPrice; ?> ARS</span></div>
                                    
                                </div>
                                	
                            </div>
                            
                            <div class="divide_bar"></div>