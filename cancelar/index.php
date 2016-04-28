<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}

$formError = -1; // -1 = no enviado, 0 = correcto, 1 = pedido no existe, 2 = pass incorrecta, 3 = pedido no activo, 4 = pago realizado y notificado
if(isset($_POST["order_id"]) && isset($_POST["order_password"]))
{

	$sql = "SELECT * FROM `orders` WHERE order_id = '".mysqli_real_escape_string($con, $_POST["order_id"])."'";
	$query = mysqli_query($con, $sql);
	if(mysqli_num_rows($query) == 1) {
		$orderData = mysqli_fetch_assoc($query);
		if($_POST["order_password"] == $orderData["order_password"]) {
			if($orderData["order_status"] == 1) {
				if($orderData["order_informedpayment"] == 0) {
					cancelOrder($_POST["order_id"]);
					$formError = 0;
				} else {
					$formError = 4;
				}
			} else {
				$formError = 3;
			}
		} else {
			$formError = 2;
		}
	} else if(mysqli_num_rows($query) == 0) {
		$formError = 1;
	}
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Cancelar un pedido - SteamBuy</title>
        
        <meta name="description" content="Si quieres cancelar un pedido de compra en SteamBuy utiliza este formulario.">
        <meta name="keywords" content="steambuy,cancelar,pedido,juego">
        
        <meta property="og:title" content="Cancelar un pedido" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/cancelar/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Si quieres cancelar un pedido de compra en SteamBuy utiliza este formulario." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/cancelar/">
        <meta name="twitter:title" content="Cancelar un pedido">
        <meta name="twitter:description" content="Si quieres cancelar un pedido de compra en SteamBuy utiliza este formulario.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Cancelar un pedido">
        <meta itemprop="description" content="Si quieres cancelar un pedido de compra en SteamBuy utiliza este formulario.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/cancel_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
		<script type="text/javascript" src="scripts/cancel_pg.js?2"></script>
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="title">Cancelar pedido</h3>            
            
            	<div style="height:170px;margin-bottom: 20px;">
                	<div class="infobox">
                    	Si no vas a pagar o querés cancelar el pedido <strong>te pedimos que lo canceles con este formulario</strong>, si ya lo pagaste <a href="../soporte/">contáctanos</a> para pedir un reembolso o cambio de producto.
                    </div>
                    <form action="" method="post" id="form">
                        ID de pedido:
                        <input type="text" class="form-control" name="order_id" id="input_id" autocomplete="off" <?php if($formError > 0) echo "value='".$_POST["order_id"]."'"; ?>/>
                        Clave de pedido:
                        <input type="text" class="form-control" name="order_password" id="input_password" autocomplete="off" <?php if($formError > 0) echo "value='".$_POST["order_password"]."'"; ?>/>
                        <input type="button" class="btn btn-primary" id="button_submit" value="Cancelar pedido"/>
                    </form>
                </div>
                <?php
				if($formError == 0) {
					echo "<div class='alert alert-success' style='margin:0 40px 10px 40px;'>Se ha cancelado el pedido ID ".$_POST["order_id"]." correctamente.</div>";	
				}
				?>
				<div id="error_list" class="alert alert-danger" <?php if($formError > 0) echo "style='display:block;'"; ?>>
                    <span class="glyphicon glyphicon-remove" onclick="$(this).parent('div').slideUp('slow');" style="float:right;cursor:pointer;"></span>
                    <ul><?php
                    if($formError == 1) {
						echo "<li>El pedido ID <strong>".$_POST["order_id"]."</strong> no existe.</li>";
					} else if($formError == 2) {
						echo "<li>La clave del pedido ID ".$_POST["order_id"]." es incorrecta.</li>";
					} else if($formError == 3) {
						if($orderData["order_status"] == 2) {
							echo "<li>El pedido ID ".$_POST["order_id"]." no se puede cancelar debido a que ya fue enviado.</li>";
						} else if($orderData["order_status"] == 3) {
							echo "<li>El pedido ID ".$_POST["order_id"]." no se puede cancelar porque ya está cancelado.</li>";
						}
					} else if($formError == 4) {
						echo "<li>Se ha informado el pago de este pedido, si pagaste y querés cancelar el pedido, <a href='../soporte/' target='_blank'>contáctanos</a> para pedir
						un reembolso o cambio de productos.</li>";
					}
					?></ul>
                </div>
            
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>





















