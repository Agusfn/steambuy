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

/*var_dump($_POST);

var_dump($_FILES);*/

$informError = -1; 
/* -1 = no enviado, 0 = correcto, 1 = pedido no existe, 2 = pass incorrecta, 3 = pedido no activo, 4 = juego no en oferta, 5 = pago ya informado (ELIMINADO)
6 = error en la imágen, 7 = el archivo no es una img, 8 = img muy grande, 9 = error subiendo imágen
*/
if(isset($_POST["order_id"]) && isset($_POST["order_password"]))
{

	$imgFileType = $_FILES["order_inform_img"]["type"];
	$imgFileSize = intval($_FILES["order_inform_img"]["size"]);
	$imgFileName = $_FILES["order_inform_img"]["name"];
	
	$replaced = false;
	
	$sql = "SELECT * FROM `orders` WHERE `order_id` = '".mysqli_real_escape_string($con, $_POST["order_id"])."'";
	$query = mysqli_query($con, $sql);
	if(mysqli_num_rows($query) == 1) {
		$orderData = mysqli_fetch_assoc($query);
		if($_POST["order_password"] === $orderData["order_password"]) {
			if($orderData["order_status"] == 1) {
				if($orderData["product_limited_discount"] == 1 || $orderData["order_paymentmethod"] == 2) {
						if($_FILES["order_inform_img"]["error"] == 0) {
							if($imgFileType == "image/png" || $imgFileType == "image/jpeg") {
								if($imgFileSize < 2097153) {
									if($orderData["order_informedpayment"] == 1) $replaced = true;
									$split = explode("image/",$imgFileType);
									$filename = $_POST["order_id"] . "." . $split[1];
									if(move_uploaded_file($_FILES["order_inform_img"]["tmp_name"], "../data/img/payment_receipts/" . $filename)) {
										$sql = "UPDATE `orders` SET `order_informedpayment` = '1', `order_informed_date` = NOW(), `order_informed_image` = '".mysqli_real_escape_string($con, $filename)."' 
										WHERE `order_id` = '".mysqli_real_escape_string($con, $_POST["order_id"])."';";
										mysqli_query($con, $sql);
										$informError = 0;
									} else {
										$informError = 9;
									}
								} else {
									$informError = 8;
								}
							} else {
								$informError = 7;
							}
						} else {
							$informError = 6;
						}
				} else {
					$informError = 4;
				}
			} else {
				$informError = 3;
			}
		} else {
			$informError = 2;
		}
	} else if(mysqli_num_rows($query) == 0) {
		$informError = 1;
	}
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Informar pago - SteamBuy</title>
        
        <meta name="description" content="Si pediste un juego en oferta limitada, informa el pago aquí para que sea reservado y no perderte la oferta.">
        <meta name="keywords" content="steambuy,informar,pago,oferta limitada,descuento,steam,amazon">
        
        <meta property="og:title" content="Informar pago" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/informar/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Si pediste un juego en oferta limitada, informa el pago aquí para que sea reservado y no perderte la oferta." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/informar/">
        <meta name="twitter:title" content="Informar pago">
        <meta name="twitter:description" content="Si pediste un juego en oferta limitada, informa el pago aquí para que sea reservado y no perderte la oferta.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Informar pago">
        <meta itemprop="description" content="Si pediste un juego en oferta limitada, informa el pago aquí para que sea reservado y no perderte la oferta.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/inform_pg.css?2" type="text/css">
        
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
		<script type="text/javascript" src="scripts/inform_pg.js?2"></script>
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="title">Informar pago</h3>
            	
                <div style="height:200px;margin-bottom:25px;">
                	<div class="infobox">
                    	Informa el pago si el juego pedido posee una oferta externa de tiempo limitado (por ej. de Steam) o si pagaste un juego por transferencia bancaria.
                        Ingresa el <strong>ID</strong> y <strong>clave de pedido</strong> (enviados a tu e-mail al generar el pedido) y selecciona la imágen a enviar. <a href="../faq/#18" target="_blank">Más información</a>.<br/>
                        Si realizaste un informe incorrecto, podés reemplazarlo con reenviando este formulario.
                    </div>
                    <div class="form"><form action="" method="post" id="form" enctype="multipart/form-data">
                    	<div style="float:left;width:170px;">
                            ID de pedido:
                            <input type="text" name="order_id" class="form-control" id="input_id" autocomplete="off" <?php if($informError > 0) echo "value='".$_POST["order_id"]."'"; ?> />
                            Clave de pedido:
                            <input type="text" name="order_password" class="form-control" id="input_password" autocomplete="off" <?php if($informError > 0) echo "value='".$_POST["order_password"]."'"; ?>/>
                        </div>
                        <div style="float:right;">
                            Imágen de comprobante de pago:
                            <input type="file" name="order_inform_img" style="margin-top:15px;width: 335px;" accept="image/x-png, image/jpeg"/>
                            <input type="button" class="btn btn-primary btn-lg" id="button_submit" value="Enviar"/>
                        </div>
                    </form></div>
                   
                </div>
                <?php
				if($informError == 0) {
					if($replaced) {
						echo "<div class='alert alert-success' style='margin:0 40px 10px 40px;'>El comprobante del pedido <strong>".$_POST["order_id"]."</strong> se reenvió correctamente, reemplazando al enviado anteriormente. ";
					} else {
						echo "<div class='alert alert-success' style='margin:0 40px 10px 40px;'>El comprobante del pedido <strong>".$_POST["order_id"]."</strong> se envió correctamente. ";
					}
					if($orderData["order_type"] == 1) {
						if($orderData["product_limited_discount"] == 1) {
							echo "Si el comprobante es válido y se envió a tiempo, el juego será reservado y lo recibirás sin problemas.";
						}
						if($orderData["order_paymentmethod"] == 1) {
							
							$split = explode("?id=", $orderData["order_purchaseticket"]);
							
							echo " <strong>Los pedidos se envían entre 12 y 48 hs luego de abonada la boleta de pago</strong>. Podés revisar el estado de tu pago en el siguiente <a href='https://www.cuentadigital.com/area.php?name=Search&query=".$split[1]."' target='_blank'>enlace</a>.";
						} else if($orderData["order_paymentmethod"] == 2) {
							echo " Los pedidos por transf./depósito se envían <strong>durante las siguientes 12 horas hábiles luego de acreditado</strong>.";
						}
					}
					echo "</div>";	
				}
				?>
                 <div id="error_list" class="alert alert-danger" <?php if($informError > 0) echo "style='display:block;'"; ?>>
                    <span class="glyphicon glyphicon-remove" onclick="$(this).parent('div').slideUp('slow');" style="float:right;cursor:pointer;"></span>
                    <ul>
						<?php
                        if($informError == 1) {
                            echo "<li>El pedido ID <strong>".$_POST["order_id"]."</strong> no existe.</li>";
                        } else if($informError == 2) {
                            echo "<li>La clave del pedido ID ".$_POST["order_id"]." es incorrecta.</li>";
                        } else if($informError == 3) {
							if($orderData["order_status"] == 2) {
								echo "<li>El pedido ID ".$_POST["order_id"]." se encuentra en estado concretado.</li>";
							} else if($orderData["order_status"] == 3) {
								echo "<li>El pedido ID ".$_POST["order_id"]." se encuentra en estado cancelado.</li>";
							}
                        } else if($informError == 4) {
							if($orderData["order_type"] == 1) {
								echo "<li>El juego no posee una oferta externa de tiempo limitado, <strong>no es necesario informar el pago</strong>, aguarda a que se acredite y/o sea enviado.</li>";
							} else if($orderData["order_type"] == 2) {
								echo "<li>No es necesario informar el pago para los envíos de saldo PayPal a menos que se abone por transferencia bancaria.</li>";
							}
                        } else if($informError == 6) {
                            echo "<li>No se envió ningún archivo u ocurrió un error, reintenta por favor.</li>";
                        } else if($informError == 7) {
							echo "<li>No se envió una imágen o el archivo enviado no es una imágen.</li>";
                        } else if($informError == 8) {
							 echo "<li>La imágen enviada es muy grande (tamaño máximo: 2MB)</li>";
                        } else if($informError == 9) {
                            echo "<li>Ocurrió un error almacenando la imágen, por favor reintenta la operación.</li>";
                        } 
                        ?>
                    </ul>
                </div>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>