<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/purchase-functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}



if(isset($_POST["order_id"]) && isset($_POST["order_password"])) {
	$order_id = $_POST["order_id"];
	$order_pass = $_POST["order_password"];
} else if(isset($_GET["id"]) && isset($_GET["clave"])) {
	$order_id = $_GET["id"];
	$order_pass = $_GET["clave"];
} else {
	header("Location: index.php");
	exit;
}

$error = 0;

$sql = "SELECT * FROM `orders` WHERE `order_id`='".mysqli_real_escape_string($con, $order_id)."' AND BINARY `order_password`='".mysqli_real_escape_string($con, $order_pass)."'";
$query = mysqli_query($con, $sql);
if(mysqli_num_rows($query) == 1) {
	$order_data = mysqli_fetch_assoc($query);
} else $error = 1;


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Mi pedido - SteamBuy</title>
        
        <meta name="description" content="Detalles de mi pedido.">
        
        <meta property="og:title" content="Ver mi pedido" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/pedido/detalles.php" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Ver estado de mi pedido" />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/pedido/detalles.php">
        <meta name="twitter:title" content="Ver mi pedido">
        <meta name="twitter:description" content="Ver estado de mi pedido">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Ver mi pedido">
        <meta itemprop="description" content="Ver estado de mi pedido">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <link rel="shortcut icon" href="../favicon.ico"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css" type="text/css">
        <link rel="stylesheet" href="resources/css/detalles.css" type="text/css">
        
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js"></script>
    </head>
    
    <body>
		<?php
		if($error == 0) {
			if($order_data["order_paymentmethod"] == 2) {
			?>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Datos cuenta bancaria</h4>
                  </div>
                  <div class="modal-body">
                    <?php
					$bank_acc = file_get_contents("../global_scripts/cta-bancaria.html");
					echo $bank_acc;
					?>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                  </div>
                </div>
              </div>
            </div>
            <?php
			}
		}
		?>


		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">

				<?php
				if($error == 1) {
					echo "<div class='alert alert-danger' style='margin: 50px 20px;'>No se ha encontrado un pedido con la combinación de ID y clave ingresada. <a href='index.php'>Reingresa los datos</a>.</div>";
				} else if($error == 0) {
					?>
                    
                    <h3 class="page-title">Mi pedido</h3>
                    
                    <?php
					if($order_data["order_status"] == 1 && $order_data["product_limited_discount"] == 1 && $order_data["order_informedpayment"] == 0 && $order_data["order_confirmed_payment"] == 0) {
					?>
                        <div class="alert alert-warning alert-dismissible" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                          El juego pedido tiene una oferta externa de tiempo limitado. Si se acerca el fin de la oferta, informa el pago antes de que termine para no perderla y reservar tu copia.
                        </div>
                    <?php
					}
					?>
                    <div style="padding:0 20px 40px 20px;">
                        
                        <div class="clearfix" style="margin-top:30px;">
                         	<div style="float:left;margin-left:110px;">
                            	<span class="order-detail-title">ID de pedido:</span>&nbsp;&nbsp;<?php echo $order_data["order_id"]; ?>
                            </div>
                            <div style="float: left;margin-left: 125px;">
                            	<span class="order-detail-title">Estado del pedido:</span>&nbsp;&nbsp;
                                <?php
								if($order_data["order_status"] == 1) {
									?>
									<span class='dotted' style='font-size:16px;color:#2E7EDA;' data-toggle='tooltip' data-placement='top' title='<?php 
									if($order_data["order_paymentmethod"] == 1) {
										if($order_data["order_confirmed_payment"] == 0) {
											echo "Esperando la acreditación del pago. Una vez acreditado, el pedido normalmente se envía en el día.";	
										} else if($order_data["order_confirmed_payment"] == 1) {
											echo "El pedido está cursado para ser enviado. Deberías estar recibiendo el pedido por e-mail en el día, o como máximo a las 48 hs siguientes de acreditado.";
										}
									} else if($order_data["order_paymentmethod"] == 2) {
										if($order_data["order_informedpayment"] == 0) {
											echo "Esperando la realización del informe de pago para continuar";
										} else if($order_data["order_informedpayment"] == 0) {
											echo "Estamos revisando tu pago, deberías recibir el pedido en el día en que se nos acredita el pago, o como máximo dentro de las 48hs siguientes.";
										}
									}
									
									?>'>Activo</span>
									<?php
								} else if($order_data["order_status"] == 2) {
									echo "<span class='dotted' style='font-size:16px;color: #229E12;' data-toggle='tooltip' data-placement='top' title='Se han enviado los productos al e-mail registrado en el pedido el ".date("d/m/y H:i:s",strtotime($order_data["order_status_change"])).".'>Concretado</span>";	
								} else if($order_data["order_status"] == 3) {
									echo "<span class='dotted' style='font-size:16px;color: #AE3333;' data-toggle='tooltip' data-placement='top' title='El pedido expiró o se canceló por otro motivo. Si tienes algun problema con el pedido contáctanos.'>Cancelado</span>";	
								}
								
								?>
                            </div>
                        </div>
                        
                    	<div class="clearfix" style="margin-top:40px;">
                        	<div style="float: left;margin-left: 110px;">
                            	<div class="order-detail-title">Nombre del producto:</div>
                            	<div class="product_name"><?php echo $order_data["product_name"]; ?></div>
                            </div>
                            <div style="float: right;margin-right: 110px;">
                        		<div class="order-detail-title" style="text-align:right">Total:</div>
                                <div class="order_price">$<?php echo $order_data["product_arsprice"]; ?> ARS</div>
                        	</div>
                        </div>
                        
                    	<div class="clearfix" style="margin-top:40px;">
                        	<div style="float: left;margin-left: 110px;">
                            	<div class="order-detail-title">Medio de pago:</div>
									<?php
                                    if($order_data["order_paymentmethod"] == 1) {
                                        echo "<a href='".$order_data["order_purchaseticket"]."' target='_blank'><span class='glyphicon glyphicon-barcode'></span> Boleta de pago</a>";
                                    } else if($order_data["order_paymentmethod"] == 2) { 
										?>
                                        Depósito/transferencia bancaria<br/>
                                        <span style="font-size:13px;">(<a href="javascript:void(0)" data-toggle="modal" data-target="#myModal">Ver datos de cta.</a>)</span>
                                        <?php
                                    }
                                   ?>
                                   
                            </div>
                            <div style="float: right;margin-right: 110px;">
                            	<div class="order-detail-title" style="text-align:right">Estado del pago:</div>
								<?php
                                if($order_data["order_paymentmethod"] == 1) {
									if($order_data["order_confirmed_payment"] == 1) {
										echo "<span style='color:#229E12;'>Acreditado <span class='glyphicon glyphicon-ok'></span></span>";
									} else if($order_data["order_confirmed_payment"] == 0) {
										if($order_data["order_status"] == 1) {
											echo "<span class='dotted' data-toggle='tooltip' data-placement='top' title='Una vez pagada la boleta, el pago se acreditará entre 0 y 24hs después.'>Pendiente</span> <span class='glyphicon glyphicon-time'></span>";
										} else {
											echo "No acreditado";
										}
									}
                                } else if($order_data["order_paymentmethod"] == 2) {
									if($order_data["order_status"] == 1) {
										if($order_data["order_informedpayment"] == 0) {
											echo "<span class='dotted' data-toggle='tooltip' data-placement='top' title='Esperando el informe de pago'>Pendiente</span> <span class='glyphicon glyphicon-time'></span>";
										} else if($order_data["order_informedpayment"] == 1) {
											echo "<span class='dotted' data-toggle='tooltip' data-placement='top' title='Estamos revisando el pago'>Pendiente <span class='glyphicon glyphicon-time'></span></span>";
										}
									} else if($order_data["order_status"] == 2) {
										echo "<span style='color:#229E12;' class='dotted' data-toggle='tooltip' data-placement='top' title='El pedido se envió y el pago se acreditó'>Acreditado</span>";
									} else if($order_data["order_status"] == 3) {
										echo "No acreditado";
									}
								}
                                ?>
                            </div>

                        </div>

                    	<div class="clearfix" style="margin-top:40px;">
                        	<div style="float: left;margin-left: 110px;">
                            	<div class="order-detail-title">Oferta ext. limitada:</div>
                            	<div style="font-size:16px;"><?php
                                if($order_data["product_limited_discount"] == 1) {
									echo "<span class='dotted' data-toggle='tooltip' data-placement='top' title='El pedido tiene una oferta de reventa que termina en un horario en particular. Informa el pago antes del horario designado para no perder la oferta.'>Sí</span>";
								} else echo "No";
								?></div>
                            </div>
                            <?php
							if($order_data["product_limited_discount"] == 1 || $order_data["order_paymentmethod"] == 2) {

                                if($order_data["order_informedpayment"] == 1) {
                                    ?>
                                    <div style="float: right;margin: 0 110px 0 35px;">
                                        <div class="order-detail-title" style="text-align:right">Fecha y hora informe:</div>
                                        <div><?php echo date("d/m/y H:i:s", strtotime($order_data["order_informed_date"])); ?></div>
                                    </div>
                                    <?php
                                }
								?>
                                <div style="float: right;margin-right: 110px;">
                                    <div class="order-detail-title" style="text-align:right">Pago informado:</div>
                                    <div style="font-size:16px;"><?php
                                    if($order_data["order_informedpayment"] == 1) echo "Sí"; 
									else {
										echo "No";
										if($order_data["order_status"] == 1) echo "&nbsp;&nbsp;<span style='font-size:13px'>(<a href='../informar/'>Informar</a>)</span>";
									}
									?></div>
                                </div>
                                <?php
							}
							
							?>

                        </div>
                        
                    	<div class="clearfix" style="margin-top:40px;">
                        	<div style="float: left;margin-left: 110px;">
                            	<div class="order-detail-title">Nombre destinatario:</div>
                            	<div style="font-size:16px;"><?php echo $order_data["buyer_name"]; ?></div>
                            </div>
                        	<div style="float:right;margin-right: 110px;">
                            	<div class="order-detail-title" style="text-align:right">E-mail envío:</div>
                            	<div style="font-size:16px;"><?php echo $order_data["buyer_email"]; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php
				}
				
				?>
        
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>