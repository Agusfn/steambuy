<?php
session_start();

define("ROOT_LEVEL", "../../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../../global_scripts/php/client_page_preload.php");
require_once("../../global_scripts/php/admlogin_functions.php");
require_once("../../global_scripts/php/main_purchase_functions.php");
require_once("../../global_scripts/email/mailer.php");
$config = include("../../global_scripts/config.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


if(isset($_POST["gameid"])) 
{
	$stage = 1;
	if(isset($_POST["stage"])) {
		if($_POST["stage"] == 1 || $_POST["stage"] == 2 || $_POST["stage"] == 3) $stage = $_POST["stage"];	
	}
	$error = 0;


	$res = mysqli_query($con, "SELECT * FROM products WHERE product_id = ".mysqli_real_escape_string($con, $_POST["gameid"])." AND product_enabled = 1 AND (product_has_limited_units = 0 OR (product_has_limited_units = 1 AND product_limited_units > 0))");
	if(mysqli_num_rows($res) == 1) 
	{
		$productData = mysqli_fetch_assoc($res);
		
		if($stage == 1) {
			if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") {
				$ticketPrice = $productData["product_finalprice"];
				$transferPrice =  $productData["product_finalprice"] - ($productData["product_finalprice"] * 0.0484 + 1.5125);
				$transferPrice = round(1.015 * $transferPrice, 1);
			} else {
				$ticketPrice = quickCalcGame(1, $productData["product_finalprice"]);	
				$transferPrice = quickCalcGame(2, $productData["product_finalprice"]);
			}
		} else if($stage == 2) {
			if(isset($_POST["paymethod"])) {
				if($_POST["paymethod"] != 1 && $_POST["paymethod"] != 2) {
					echo "Error 2: Datos de medio de pago erróneos.";
					return;	
				} else {
					$randCode = sha1("sal".rand(1,99999));
					$_SESSION["randcode"] = $randCode;
					if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") {
						if($_POST["paymethod"] == 1) {
							 $finalPrice = $productData["product_finalprice"];
						} else if($_POST["paymethod"] == 2) {
							$finalPrice =  $productData["product_finalprice"] - ($productData["product_finalprice"] * 0.0484 + 1.5125);
							$finalPrice = round(1.015 * $finalPrice, 1);
						}
					} else {
						$finalPrice = quickCalcGame($_POST["paymethod"], $productData["product_finalprice"]);	
					}
				}
			} else {
				echo "Error 2: Datos de medio de pago erróneos.";
				return;	
			}
		} else if($stage == 3) {

			if(isset($_POST["paymethod"]) && isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["randcode"]) && isset($_SESSION["randcode"])) 
			{
				if($_POST["randcode"] == $_SESSION["randcode"])
				{
					// Creación de pedido
					$pay_method = $_POST["paymethod"];
					if($pay_method != 1 && $pay_method != 2) {
						echo "Error de datos: medio de pago inválido. Reintenta la operación.";
						return;
					}
					// nombre
					$clientName = $_POST["name"];
					if(preg_match("/^[a-z\sñáéíóú]*$/i",$clientName) == false) {
						echo "Error de datos: nombre incorrecto o caracateres inválidos. Reintenta la operación.";
						return;
					}
					// email
					$clientEmail = $_POST["email"];
					if(!preg_match("/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",$clientEmail) || $clientEmail == ""){
						echo "Error de datos: mail incorrecto. Reintenta la operación.";
						return;
					}
					
					$res2 = mysqli_query($con, "SELECT COUNT(*) FROM orders WHERE order_status = 1 AND buyer_email = '".mysqli_real_escape_string($con, $clientEmail)."'");
					$count = mysqli_fetch_row($res2);
					
					if($count[0] < 20) {
						
						// Precio USD
						$gameUsdPrice = 0;
						if($productData["product_has_customprice"] == 0 || ($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "usd")) {
							$gameUsdPrice = $productData["product_finalprice"];	
						}
						
						// Precio ARS
						if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") {
							if($pay_method == 1) {
								$gameArsPrice = $productData["product_finalprice"];
							} else if($pay_method == 2) {
								$gameArsPrice =  $productData["product_finalprice"] - ($productData["product_finalprice"] * 0.0484 + 1.5125);
								$gameArsPrice = round(1.015 * $gameArsPrice, 1);
							}
						} else {
							$gameArsPrice = quickCalcGame($pay_method, $productData["product_finalprice"]);	
						}
						
						// Recordar nombre y e-mail
						if(isset($_POST["rememberdata"])) {
							setcookie("client_name", $clientName, time() + 5184000, "/");
							setcookie("client_email", $clientEmail, time() + 5184000, "/");	
						} else {
							if(isset($_COOKIE["client_name"])) {
							  unset($_COOKIE["client_name"]);
							  setcookie("client_name", "", time() - 3600, "/"); 
							}
							if(isset($_COOKIE["client_email"])) {
							  unset($_COOKIE["client_email"]);
							  setcookie("client_email", "", time() - 3600, "/"); 
							}
						}
						
						// Enviar e-mail
						// <scripts para enviar email>
						
						// Generar orden
						$order = new order($con);
						if($order->createGameOrder($pay_method, mysqli_real_escape_string($con, $productData["product_name"]), mysqli_real_escape_string($con, $_POST["gameid"]), $productData["product_sellingsite"], 
						mysqli_real_escape_string($con, $productData["product_site_url"]), $productData["product_external_limited_offer"], $gameUsdPrice, $gameArsPrice, mysqli_real_escape_string($con, $clientName),
						mysqli_real_escape_string($con, $clientEmail), $_SERVER["REMOTE_ADDR"]))
						{

							$mail_data = array(
								"receiver_name"=>$clientName,
								"order_id"=>$order->orderInfo["order_id"],
								"order_password"=>$order->orderInfo["order_password"],
								"product_name"=>$productData["product_name"],
								"order_ars_price"=>$gameArsPrice,
								"payment_method"=>$pay_method,
								"product_external_discount"=>$productData["product_external_limited_offer"],
								"product_sellingsite"=>$productData["product_sellingsite"],
								"product_site_url"=>$productData["product_site_url"],
								"order_fromcatalog"=>1
							);
							if($pay_method == 1) {
								$mail_data["order_purchaseticket_url"] = $order->orderInfo["order_purchaseticket"];	
							}
							if($productData["product_external_limited_offer"] == 1) {
								$mail_data["product_external_offer_endtime"] = $productData["product_external_offer_endtime"];	
							}
							
							$mail = new Mail;
							$mail->prepare_email("pedido_juego_generado", $mail_data);
							$mail->add_address($clientEmail, $clientName);
							
							if(!@$mail->send()) $mailError = true;
							else $mailError = false;
							
							
							$order_error = 0;
							$orderinfo = $order->orderInfo;
						} else $order_error = $order->error;
					} else {
						$error = 3;
					}
				} else {
					$error = 2;
				}
				$_SESSION["randcode"] = "";
			} else {
				echo "<h3>Ocurrió un error, vuelve atrás y reintenta la operación por favor.</h3>";	
				return;
			}
		}
	} else $error = 1;
	
} else {
	header("Location: ../");	
	return;
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />
        
        <title><?php
        if($error == 0) {
			if($stage == 3) {
				echo "Pedido generado - SteamBuy";	
			} else {
				echo "Comprar ".$productData["product_name"]." - SteamBuy";	
			}
		} else {
			echo "Error de compra - SteamBuy";	
		}
		?></title>
        
        
        <link rel="shortcut icon" href="../../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/css/main.css?2.01" type="text/css">
        <link rel="stylesheet" href="design/purchase_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../../global_scripts/js/global_scripts.js?2"></script>
		<script type="text/javascript" src="scripts/purchase_pg.js?2"></script>

        <?php
		if($stage == 1) {
            echo "
			<script type='text/javascript'>
			var price = new Array();
			price[1] = ".$ticketPrice.";
			price[2] = ".$transferPrice.";
			</script>";
		} else if($stage == 2) {
			?>
            <script type="text/javascript" src="scripts/purchase_pg_2.js"></script>
            <?php
		} 
		?>
        
    </head>
    
    <body>

		<?php require_once("../../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                
                
                <?php
				
				if($error == 1) {
					echo "<div class='alert alert-danger'>No se encontró el producto o está fuera de stock</div>";
				} else {
				
					if($stage == 1) {
						?>
                        <div class="purchase_steps">
							<div class="step current_step">Elegir medio de pago</div>
							<div class="spacer"></div>
							<div class="step">Ingresar datos</div>
							<div class="spacer"></div>
							<div class="step">Instrucciones de compra</div>
						</div>
                        <?php
					} else if($stage == 2) {
						?>
                        <div class="purchase_steps">
							<div class="step previous_step">Elegir medio de pago</div>
							<div class="spacer"></div>
							<div class="step current_step">Ingresar datos</div>
							<div class="spacer"></div>
							<div class="step">Instrucciones de compra</div>
						</div>
                        <?php
					} else if($stage == 3) {
						?>
                        <div class="purchase_steps">
							<div class="step previous_step">Elegir medio de pago</div>
							<div class="spacer"></div>
							<div class="step previous_step">Ingresar datos</div>
							<div class="spacer"></div>
							<div class="step current_step">Instrucciones de compra</div>
						</div>
                        <?php
					}
					
					if($stage == 1 || $stage == 2) {
						?>	
						<div class="product_info">
							<div class="pi_img"><img src="../../data/img/game_imgs/<?php echo $productData["product_mainpicture"]; ?>" alt="<?php echo $productData["product_name"]; ?>"/></div>
							<div class="pi_name"><span style="font-size:19px;"><?php echo $productData["product_name"]; ?></span>
							<div class="pi_drm"><?php if($productData["product_platform"] == 1) echo "Activable en Steam"; else if($productData["product_platform"] == 2) echo "Activable en Origin"; ?></div></div>
							<div class="pi_price"><?php
							if($productData["product_has_customprice"] == 1 && $productData["product_customprice_currency"] == "ars") {
								echo $productData["product_finalprice"]." ARS";
							} else echo $productData["product_finalprice"]." USD";
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
						<?php
					}
					if($stage == 1) {
						?>
						<div class="payment_options">
							<div class="list-group">
								<a href="javascript:void(0);" class="list-group-item active" id="payoption1">
									<div style="height: 32px;"><h4 class="list-group-item-heading">Cupón de pago</h4> <div class="list_group_price">$<?php echo $ticketPrice; ?></div></div>
									<p class="list-group-item-text">Abona en <strong>Rapipago</strong>, <strong>Pago Fácil</strong>, <strong>Ripsa</strong>, <strong>BaproPagos</strong> u otras sucursales presentando un cupón de pago. Después de entre 12 y 48 hs. hábiles se acreditará el 
									pago y recibirás el juego. </p>
								</a><a href="javascript:void(0);" class="list-group-item" id="payoption2">
									<div style="height: 32px;"><h4 class="list-group-item-heading">Transferencia bancaria</h4> <div class="list_group_price">$<?php echo $transferPrice; ?></div></div>
									<p class="list-group-item-text">Realiza un depósito bancario o haz una transferencia por home banking sin moverte de tu casa. En un máximo de 12 horas hábiles luego de acreditarse recibirás el juego.</p>
								</a>
							</div>
						</div>
						<?php	
					} else if($stage == 2) {
						?>
                        <div style="height:30px;margin-bottom:15px;">
                            <div class="pay_method"><?php
                            if($_POST["paymethod"] == 1) echo "Cupón de pago"; 
							else if($_POST["paymethod"] == 2) echo "Transferencia bancaria";
							?></div>
                            <div class="form_title">Ingresa tus datos</div>
                        </div>
                        <form action="" method="post" id="final_form">
                        	<input type="hidden" name="randcode" value="<?php echo $randCode; ?>"/>
                         	<input type="hidden" name="gameid" id="product_id" value="<?php echo $_POST["gameid"]; ?>" />
                        	<input type="hidden" name="stage" value="3"/>
                        	<input type="hidden" name="paymethod" value="<?php echo $_POST["paymethod"]; ?>" />
                            <div style="height:60px;">
                                <div style="float:left;">Nombre y apellido<input type="text" class="form-control" name="name" id="purchase_name" <?php
                                if(isset($_COOKIE["client_name"])) echo "value = '".$_COOKIE["client_name"]."'"; ?> /></div>
                                <div style="float:left;margin-left:125px;">Dirección e-mail<input type="text" class="form-control" name="email" id="purchase_email" <?php
                                if(isset($_COOKIE["client_email"])) echo "value = '".$_COOKIE["client_email"]."'"; ?>/></div>
                            </div>
                            <div class="checkbox"><label><input type="checkbox" name="rememberdata" <?php if(isset($_COOKIE["client_name"]) && isset($_COOKIE["client_email"])) echo "checked"; ?>> Recordar e-mail y nombre para futuras compras.</label></div>
						</form>
                        <?php
						if($productData["product_external_limited_offer"] == 1) {
							?>
                       		<div class="alert alert-warning offer_warning">Este juego se encuentra en oferta externa limitada, una vez pagado, deberás informar el pago en 
                            la sección de <a href="../../informar/" target="_blank">informar pago</a> antes de que termine la oferta, <strong>de lo contrario la oferta NO será aplicable</strong> y deberás cambiar tu producto contactándonos.&nbsp;<?php
							
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

							?></div>
                            <div class="checkbox tos_warning">
                            	<label><input type="checkbox" id="tos_checkbox"> <strong>Acepto los términos y condiciones, y acepto en caso de no informar el pago a tiempo, no recibir este juego, teniendo que elegir un cambio de productos.</strong></label>
                          	</div>
                            <?php	
						} else {
							echo "<div class='tos_warning'>Al hacer click en 'generar pedido' das por aceptado los <a href='../../condiciones/' target='_blank'>términos y condiciones</a>.</div>";
							
						}
						?>
                        <div class="alert alert-danger" id="error_list"><span class="glyphicon glyphicon-remove" style="float:right;cursor:pointer;" onClick="$(this).parent('#error_list').slideUp('slow');"></span><ul></ul></div>
                        <?php
					} else if($stage == 3) {
						if($error == 2) {
							echo "<div class='alert alert-danger' style='margin: 20px;'>La sesión ha expirado, si ya realizaste el pedido revisa tu e-mail donde debiste recibir los datos del mismo, de lo contrario <a href='javascript:history.go(-1);'>reintenta la operación</a> y reenvía los datos.</div>";
						} else if($error == 3) { 
							echo "<div class='alert alert-danger' style='margin: 20px;'>Has llegado a la cantidad máxima de pedidos activos sin concretar (20). Paga o cancela algunos para realizar más.</div>";
						} else if($order_error != "0") {
							if($admin == true) {
								echo "<div class='alert alert-danger' style='margin: 20px;'>Ha ocurrido un error generando el pedido: ".$order_error.".</div>";
							} else {
								echo "<div class='alert alert-danger' style='margin: 20px;'>Ha ocurrido un error generando el pedido, reintenta la operación o contacta al soporte.</div>";
							}
						} else {
							?>
                            <div class="purchase_instructions">
                                <h4 class="pi_title">El pedido se ha generado</h4>
                                
                                <?php
								if($pay_method == 1) {
									?>
									<div class="pi_instructions">Se ha generado tu pedido de <strong>$<?php echo $gameArsPrice; ?> ARS</strong> por el juego <strong><?php echo $productData["product_name"]; ?></strong>, el siguiente paso es imprimir y abonar el cupón de pago en cualquier sucursal de <strong>Rapipago</strong>,
                                    <strong>Pago Fácil</strong>, <strong>Ripsa</strong>, <strong>Cobroexpress</strong>, <strong>Bapropagos</strong>, u otras cadenas de pago especficadas en la boleta o cupón de pago.<br></div>
                                                                            
                                    <div style="text-align:center; margin:25px 0;">
                                    	<a href="<?php echo $orderinfo["order_purchaseticket"]; ?>" target="_blank" class="btn btn-primary btn-lg">Ver cupón de pago&nbsp;&nbsp;<span class="glyphicon glyphicon-barcode"></span></a>
                                        <br/><a href="<?php 
										$split = explode("?id=",$orderinfo["order_purchaseticket"]);
										echo "https://www.cuentadigital.com/ticket.php?id=".substr($split[1], 4, 8);
										 ?>" target="_blank">Ver en formato ticket</a>
                                    </div>  
                                        
                                    <div class="pi_instructions">Una vez abonado, <strong>el pago tomará entre 12 y 48 horas en acreditarse</strong> automáticamente, normalmente al día siguiente está acreditado. El pedido será enviado vía correo electrónico (dentro estarán las instrucciones de activación) durante día en que se acredita el pago. Podés ver el estado de tu pago en el <a href="https://www.cuentadigital.com/area.php?name=Search&query=<?php echo $split[1]; ?>" target="_blank">siguiente enlace</a>, que también se te ha sido enviado por e-mail.</div>                                   
                                    <?php
								} 
								else if($pay_method == 2) 
								{
									?>
                                    <div class="pi_instructions">Se ha generado tu pedido de <strong>$<?php echo $gameArsPrice; ?> ARS</strong> por el juego <strong><?php echo $productData["product_name"]; ?></strong>, el siguiente paso es <strong>realizar el depósito o transferencia bancaria</strong> a la cuenta
                                    bancaria <strong>especificada a continuación</strong>.<br></div>

                                    <div class="pi_transferdata">
                                        <div><strong>Banco:</strong> ICBC</div>
                                        <div><strong>Cuenta:</strong> Caja de ahorro $ 0849/01118545/07</div>
                                        <div><strong>CBU:</strong> 0150849701000118545070</div>
                                        <div><strong>Titular:</strong> Rodrigo Fernandez Nuñez</div>
                                        <div><strong>CUIL:</strong> 23-35983336-9</div>
                                        <div><strong>Monto:</strong> $<?php echo $gameArsPrice; ?> ARS</div>
                                    </div>
                                    
                                    <div class="pi_instructions">Una vez realizada la transferencia o depósito, <strong>envía una foto o imágen del comprobante de pago</strong> en la sección de <strong><a href="../../informar/" target="_blank">informar pago</a></strong> para que identifiquemos tu pago. 
                                    El juego se enviará <strong>dentro de las siguientes 12 horas hábiles</strong> de haberse acreditado el pago (las transferencias son instantáneas en horario hábil).</div>                                 
                                    <?php
								}
								
								if($productData["product_external_limited_offer"] == 1) {
									?>
									<div class="alert alert-warning pi_offerwarning">Este juego posee una oferta externa de tiempo limitado, deberás <strong><a href="../../informar/" target="_blank">informar el pago</a></strong> 
                                    antes de que termine la oferta para que te lo reservemos.&nbsp;<?php
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
									?></div>
									<?php	
								}
								?>
                                
                                <div class="alert alert-info" style="font-size: 14px;margin-top:30px; text-align:justify">
                                    El ID de tu pedido es <strong><?php echo $orderinfo["order_id"]; ?></strong> y la clave es <strong><?php echo $orderinfo["order_password"]; ?></strong>. Estos datos se requieren en caso de informar un pago, 
                                    cancelar un pedido, o para asistencia. Se te ha enviado un mensaje al e-mail <strong><?php echo $clientEmail; ?></strong> con esta información.
                                </div>
   
                                <?php
								if($mailError) {
									?>
                                    <div class="alert alert-danger" style="font-size: 14px;margin-top:30px; text-align:justify">Ha ocurrido un error intentando enviar el e-mail con los datos de pedido, tomá nota del <strong>ID</strong> y <strong>clave de pedido</strong> de esta página ya que <strong>no se pudieron enviar por e-mail</strong>, disculpa las molestias.</div>
                                	<?php	
								}
								?>
                                                                
                                <div class="pi_return"><a href="../../">Volver a la página principal</a></div>
							</div>
							<?php
						}
					}
					if($stage == 1 || $stage == 2) {
						?>
						<div class="purchase_footer">
							<div class="footer_totalprice"><strong>Total:</strong> <span id="total_price">$<?php 
							if($stage == 1) echo $ticketPrice;
							else if($stage == 2) echo $finalPrice; ?> ARS</span></div>
							<form action="" method="post" id="buyform">
                            	<input type="hidden" name="gameid" value="<?php echo $_POST["gameid"]; ?>"/>
                            	<input type="hidden" name="stage" value="<?php
                                if($stage == 1) echo "2";
								else if($stage == 2) echo "3";
								?>"/>
                                <input type="hidden" name="paymethod" id="paymethod" value="1" />
								<input type="<?php if($stage == 1) echo "submit"; else echo "button"; ?>" class="btn btn-success btn-lg" id="proceedbtn" value="<?php
                                if($stage == 1) echo "Continuar";
								if($stage == 2) echo "Generar pedido";
								?>" />
							</form>
						</div>
						<?php	
					}
					
				}
				?>
                
            </div><!-- End main content -->
            
        	<?php require_once("../../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>