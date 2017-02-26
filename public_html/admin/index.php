<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");

$config = include("../global_scripts/config.php");

$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}



if($admin == false) {

	$clientIp = $_SERVER["REMOTE_ADDR"];
	
	if(($query = mysqli_query($con, "SELECT * FROM logintries WHERE ip = '".$clientIp."'")) == true) {
		if(mysqli_num_rows($query) == 0) {
			$logged_once = false;
			$accumulated_failed_tries = 0;
		} else {
			$logged_once = true;
			$loginData = mysqli_fetch_assoc($query);
			$accumulated_failed_tries = intval($loginData["failed_tries"]);
		}
	} else {
		echo "Error obteniendo datos de login";
		exit;	
	}
	
	if(isset($_POST["password"])) {
		if($accumulated_failed_tries < 5) {
			$safePassword = mysqli_real_escape_string($con,$_POST["password"]);
			$hashedpass = sha1(md5($_POST["password"].".39667418")).sha1(strrev($_POST["password"]));		
			if($hashedpass == $hash) {
				if($logged_once == false) {
					mysqli_query($con, "INSERT INTO logintries (ip_index, ip, failed_tries, passwords, last_try) 
					VALUES (NULL, '".$clientIp."', 0, '(OK)', NOW())");
				} else if($logged_once == true) {
					mysqli_query($con, "UPDATE logintries SET failed_tries = ".$accumulated_failed_tries.", passwords = '".mysqli_real_escape_string($con, $loginData["passwords"])." || ', last_try = NOW() 
					WHERE ip = '".$clientIp."'") or die(mysqli_error($con));
				}
				$_SESSION["apw"] = $hash;
				setcookie("apw", $hash, time() + (60 * 60 * 24 * 90),"/");
				$admin = true;
			} else {
				if($logged_once == false) {
					mysqli_query($con, "INSERT INTO logintries (ip_index, ip, failed_tries, passwords, last_try) 
					VALUES (NULL, '".$clientIp."', 1, '".$safePassword."', NOW())");
					$accumulated_failed_tries = 1;
				} else if($logged_once == true) {
					$accumulated_failed_tries += 1;
					mysqli_query($con, "UPDATE logintries SET failed_tries = ".$accumulated_failed_tries.", passwords = '".mysqli_real_escape_string($con, $loginData["passwords"])." || ".$safePassword."', last_try = NOW() 
					WHERE ip = '".$clientIp."'") or die(mysqli_error($con));
				}
			}
		}
		
		if($admin == true && isset($_GET["redir"])) {
			header("Location: " . $_GET["redir"]);	
		}
		
	}
	?>
    <!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
    	<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="robots" content="noindex, nofollow" />
            <title>Admin Login - SteamBuy</title>
            
            <link rel="shortcut icon" href="../favicon.ico?2"> 
            
            <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
            <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
            <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
            
            <?php
			if($admin == true) {
				?>
                <script type="text/javascript">
				window.location = window.location.href;
				</script>
                <?php	
			}
			?>
            
            <style type="text/css">	
			.sq
			{
				position:absolute;
				top:50%;
				left:50%;
				margin: -97px 0 0 -200px;
				width:400px;
				background-color:#F0F0F0;
				padding:20px;
				text-align: center;
			}
			.sq h1
			{
				font-size:22px;
			}
			.loginbox
			{
				margin: 23px auto 0;
			}
			.loginbox input
			{
				width:220px;
				margin-left: 69px;
			}
			.loginbox .bg-danger
			{
				margin-top:10px;
			}
			.loginbox button
			{
				margin-top:15px;	
			}
			</style>
    	</head>
        <body>
        	<div class="panel panel-default sq">
            	<?php
				if($accumulated_failed_tries >= 5) {
					echo "El inicio de sesión se te ha sido bloqueado&nbsp;&nbsp;<span class='glyphicon glyphicon-lock' style='display:inline-block;'></span>";
				} else {
					?>
                   	<h1>Identificarse como administrador</h1>
                	<div class="loginbox">
                        <form action="" method="post">
                            <input type="password" class="form-control" name="password" />
                            <?php
							if(isset($_POST["password"])) {
								if($accumulated_failed_tries > 0) {
									echo "<p class='bg-danger'>Contraseña incorrecta. Intento ".$accumulated_failed_tries." de 5.</p>";
								}	
							}
                            ?>
                            <button class="btn btn-primary" type="submit">Iniciar sesión</button>
                        </form>
                	</div> 
                    <?php
				}
				?>
            </div>
        </body>
    </html>
    
    <?php
	
	
} else if($admin == true) {

	
	if(isset($_POST["dollar_value_update"])) {
		if($_POST["dollar_value_update"] == "fixed" && isset($_POST["fixed_value"])) {
			mysqli_query($con, "UPDATE settings SET `value` = 0 WHERE `name` = 'autoupdate_dollar_value'");
			mysqli_query($con, "UPDATE settings SET `value` = '".mysqli_real_escape_string($con, $_POST["fixed_value"])."' WHERE `name` = 'fixed_dollar_value'");
		} else if($_POST["dollar_value_update"] == "automatic") {
			mysqli_query($con, "UPDATE settings SET `value` = 1 WHERE `name` = 'autoupdate_dollar_value'");
		}
	} else if(isset($_POST["active_service"])) {
		if($_POST["active_service"] == 0 || $_POST["active_service"] == 1) {
			mysqli_query($con, "UPDATE settings SET `value` = ".mysqli_real_escape_string($con, $_POST["active_service"])." WHERE `name` = 'service_enabled'");
		}
	} else if(isset($_POST["brl_quote"])) {
		if(is_numeric($_POST["brl_quote"])) {
			mysqli_query($con, "UPDATE settings SET `value` = ".$_POST["brl_quote"]." WHERE `name` = 'brl_quote'");	
		}
	} else if(isset($_POST["mxn_quote"])) {
		if(is_numeric($_POST["mxn_quote"])) {
			mysqli_query($con, "UPDATE settings SET `value` = ".$_POST["mxn_quote"]." WHERE `name` = 'mxn_quote'");	
		}
	} else if(isset($_POST["alicuota_menor32"])) {
		if(is_numeric($_POST["alicuota_menor32"])) {
			mysqli_query($con, "UPDATE settings SET `value` = ".$_POST["alicuota_menor32"]." WHERE `name` = 'alicuota_menor32'");	
		}
	} else if(isset($_POST["alicuota_mayor32"])) {
		if(is_numeric($_POST["alicuota_mayor32"])) {
			mysqli_query($con, "UPDATE settings SET `value` = ".$_POST["alicuota_mayor32"]." WHERE `name` = 'alicuota_mayor32'");	
		}
	}
	
	
	
	
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
		
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            
			<meta name="robots" content="noindex, nofollow" />
            
            <title>Panel de administración - SteamBuy</title>


			<link rel="shortcut icon" href="../favicon.ico?2"> 
			
			<link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
			<link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
			<link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
			<link rel="stylesheet" href="design/admin_pg.css?2" type="text/css">
			
			<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
			<script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
			<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
			<script type="text/javascript" src="scripts/js/adminpg_scripts.js?2"></script>

		</head>
		
		<body>
			
            <div class="modal fade" id="modal_banlist" tabindex="-1" role="dialog" aria-labelledby="banlist" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="myModalLabel">Banlist</h4>
                        </div>
                        <div class="modal-body">
                            <div style="margin-bottom:15px;">
                                IP: <input type="text" class="form-control" id="banform_ip" style="display:inline-block" />&nbsp;&nbsp;
                                Razón: <input type="text" class="form-control" id="banform_reason" style="display:inline-block" />
                                <button class="btn btn-warning" id="banform_addban"><span class="glyphicon glyphicon-plus"></span> Añadir IP</button>
 							</div>
							<table class="table table-striped table-condensed table-bordered data_table" style="width:558px;">
								<col width="40px">
                                <col width="179px">
                                <col width="299px">
                                <col width="39px">
								<thead>
                                	<tr>
                                    	<th>ID</th>
                                        <th>IP</th>
                                        <th>Razón</th>
                                        <th></th>
                                    </tr>
								</thead>
                                <tbody class="banlist_tbody">
                                </tbody>
							</table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
    		
			<?php require_once("../global_scripts/php/header.php"); ?>
			
			<div class="wrapper">
				
				<div class="main_content">
					
					<table class="main_table">
                    <col width="660px">
                    <col width="318px">
                    <tbody>
                        <tr>
                        	<td class="box_subtitle">
                            Pedidos de juego en oferta que hay que reservar <div style="float:right;margin-right:20px;"><a href="pedidos.php?type=1">Ver todos</a></div>
                            </td>
                            <td class="box_subtitle">
                            Últimos accesos al panel
                            </td>
                        </tr>
                        <tr>
                        	<td>
                                <table class="table table-striped table-condensed table-bordered data_table of_hidden" style="width:648px; font-size:13px;">
                                    <col width="50px">
                                    <col width="90px">
                                    <col width="192px">
                                    <col width="38px">
                                    <col width="50px">
                                    <col width="55px">
                                    <col width="55px">
                                    <col width="55px">
                                    <col width="45px">
                                    <col width="18px">
									<thead>
                                        <tr>
                                          <th>ID</th>
                                          <th>Fecha</th>
                                          <th>Nombre</th>
                                          <th>Sitio</th>
                                          <th>USD</th>
                                          <th>ARS</th>
                                          <th>Inform.</th>
                                          <th>Reserv.</th>
                                          <th>Medio</th>
                                          <th></th>
                                        </tr>
                                  	</thead>
								</table>
                                <div class="table_scroll_container">
								<table class="table table-striped table-condensed table-bordered data_table of_hidden" style="width:630px">
                                	<col width="50px">
                                    <col width="90px">
                                    <col width="192px">
                                    <col width="38px">
                                    <col width="50px">
                                    <col width="55px">
                                    <col width="55px">
                                    <col width="55px">
                                    <col width="45px">
                                    <tbody>
										<?php
                                        $res1 = mysqli_query($con, "SELECT * FROM orders WHERE order_status = 1 AND product_limited_discount = 1 AND (order_informedpayment = 1 OR order_confirmed_payment = 1) AND order_reserved_game = 0 ORDER BY product_name ASC");
                                        while($orders = mysqli_fetch_assoc($res1)) {
											?>
											<tr <?php if($orders["order_confirmed_payment"] == 1) echo "class='green_row'"; ?>>
												<td><a href="pedido.php?orderid=<?php echo $orders["order_id"]; ?>"><?php echo $orders["order_id"]; ?></a></td>
												<td><?php echo date("d/m/Y", strtotime($orders["order_date"])); ?></td>
												<td><?php echo $orders["product_name"]; ?></td>
												<td align="center"><a href="<?php echo $orders["product_site_url"]; ?>" target="_blank"><img src="../global_design/img/icons/<?php 
												if($orders["product_sellingsite"] == 1) echo "steam";
												else if($orders["product_sellingsite"] == 2) echo "amazon";
												else if($orders["product_sellingsite"] == 3) echo "humblebundle";
												else if($orders["product_sellingsite"] == 4) echo "bundlestars";
												 ?>_22x22.png" alt="site" /></a></td>
												<td><?php if(floatval($orders["product_usdprice"]) > 0) echo $orders["product_usdprice"]; ?></td>
												<td><?php echo $orders["product_arsprice"]; ?></td>
												<td align="center"><?php 
												if($orders["order_informedpayment"] == 1) echo "<a href='http://steambuy.com.ar/data/img/payment_receipts/".$orders["order_informed_image"]."' target='_blank'><strong>SI</strong></a>";
												else if($orders["order_informedpayment"] == 0) echo "No";
												?></td>
												<td align="center"><?php 
												if($orders["order_reserved_game"] == 1) echo "<strong>SI</strong>";
												else if($orders["order_reserved_game"] == 0) echo "No";
												?></td>
												<td align="center"><img src="design/img/<?php if($orders["order_paymentmethod"] == 1) echo "boleta";
												else if($orders["order_paymentmethod"] == 2) echo "transferencia"; ?>.png" /></td>
											</tr>
											<?php
										}
                                        ?>
									</tbody>
								</table>
                                </div>
                            </td>
                            <td>
                            	<table class="table table-striped table-condensed table-bordered data_table" style="width:302px;font-size:13px">
                                    <col width="87px">
                                    <col width="35px">
                                    <col width="100px">
                                    <col width="79px">
                                    <col width="18px">
									<thead>
                                        <tr>
                                          <th>IP</th>
                                          <th>Fails</th>
                                          <th>Historial</th>
                                          <th>Ult. intento</th>
                                          <th></th>
                                        </tr>
                                  	</thead>
								</table>
                                <div class="table_scroll_container" style="width: 319px;height:250px;">
                                    <table class="table table-striped table-condensed table-bordered data_table" style="width:302px;font-size:13px">
                                        <col width="87px">
                                        <col width="35px">
                                        <col width="100px">
                                        <col width="79px">
                                        <tbody>
                                            <?php
											$res2 = mysqli_query($con, "SELECT * FROM logintries ORDER BY last_try DESC");
											while($tries = mysqli_fetch_assoc($res2)) {
												echo "<tr>
													<td>".$tries["ip"]."</td>
													<td>".$tries["failed_tries"]."</td>
													<td>".$tries["passwords"]."</td>
													<td>".date("d/m/Y H:i:s", strtotime($tries["last_try"]))."</td>
												</tr>";
											}
											?>
										</tbody>
                                    </table>
                                </div>
                                <div style="margin:10px;">
                                	<?php
									echo "
									<div><strong>CD1 ".date("m/y").":</strong> &#36;".round($config["cd1_balance"],3)."</div>
                                	<div><strong>CD2 ".date("m/y").":</strong> &#36;".round($config["cd2_balance"],3)."</div>
                                    <div><strong>CD3 ".date("m/y").":</strong> &#36;".round($config["cd3_balance"],3)."</div>";
									
									$query2 = mysqli_query($con, "SELECT SUM(net_ammount) FROM `cd_payments` WHERE `date` = CURRENT_DATE - INTERVAL 1 DAY");
									$ammount = mysqli_fetch_row($query2);
									echo "<div style='margin-top:20px'><strong>Acreditado ayer:</strong> &#36;".($ammount[0]==NULL ? 0 : $ammount[0])."</div>";
									
									$query2 = mysqli_query($con, "SELECT SUM(net_ammount) FROM `cd_payments` WHERE `date` = CURRENT_DATE - INTERVAL 2 DAY");
									$ammount = mysqli_fetch_row($query2);
									echo "<div><strong>Acreditado hace 2 días:</strong> &#36;".($ammount[0]==NULL ? 0 : $ammount[0])."</div>";
									?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                        	<td class="box_subtitle">
                            Productos del catálogo en stock <div style="float:right;margin-right:20px;"><a href="products/">Modificar catálogo</a></div>
                            </td>
                            <td class="box_subtitle">
                            Configuración de la página
                            </td>
                        </tr>
                        <tr>
                        	<td>
                                <table class="table table-striped table-condensed table-bordered data_table of_hidden" style="width:648px;font-size:13px;">
                                        <col width="30px">
                                        <col width="193px">
                                        <col width="49px">
                                        <col width="40px">
                                        <col width="40px">
                                        <col width="42px">
                                        <col width="59px">
                                        <col width="49px">
                                        <col width="64px">
                                        <col width="64px">
                                        <col width="18px">
                                        <thead>
                                            <tr>
                                              <th>ID</th>
                                              <th>Nombre</th>
                                              <th>Rating</th>
                                              <th>Platf.</th>
                                              <th>Site</th>
                                              <th>Stock</th>
                                              <th>Custom<br/>price</th>
                                              <th>Oferta<br/>lim ext.</th>
                                              <th>Precio<br/>lista</th>
                                              <th>Precio<br/>final</th>
                                              <th></th>
                                            </tr>
                                        </thead>
                                    </table>
                                <div class="table_scroll_container" style="height:440px;">
                                    <table class="table table-striped table-condensed table-bordered data_table of_hidden" style="width:630px">
										<col width="30px">
                                        <col width="193px">
                                        <col width="49px">
                                        <col width="40px">
                                        <col width="40px">
                                        <col width="42px">
                                        <col width="59px">
                                        <col width="49px">
                                        <col width="64px">
                                        <col width="64px">
										<tbody>
                                        
                                        	<?php
											$res3 = mysqli_query($con, "SELECT * FROM products WHERE product_enabled = 1 AND product_has_limited_units = 1 ORDER BY product_limited_units DESC");
											while($products = mysqli_fetch_assoc($res3)) {
												?>
                                                <tr>
                                                    <td><a href="#"><?php echo $products["product_id"]; ?></a></td>
                                                    <td><?php echo $products["product_name"]; ?></td>
                                                    <td align="center"><?php echo $products["product_rating"]; ?></td>
                                                    <td align="center"><img src="../global_design/img/icons/<?php
													if($products["product_platform"] == 1) echo "steam";
													else if($products["product_platform"] == 2) echo "origin"; ?>_22x22.png" /></td>
                                                    <td align="center"><a href="<?php echo $products["product_site_url"]; ?>" target="_blank"><img src="../global_design/img/icons/<?php
													if($products["product_sellingsite"] == 1) echo "steam";
													else if($products["product_sellingsite"] == 2) echo "amazon";
													else if($products["product_sellingsite"] == 3) echo "humblebundle";
													else if($products["product_sellingsite"] == 4) echo "bundlestars"; ?>_22x22.png" /></a></td>
                                                    <td align="center"><?php echo $products["product_limited_units"]; ?></td>
                                                    <td align="center"><?php 
													if($products["product_has_customprice"] == 1) echo "<strong>SI</strong>";
													else if($products["product_has_customprice"] == 0) echo "No"; ?></td>
                                                    <td align="center"><?php 
													if($products["product_external_limited_offer"] == 1) echo "<strong>SI</strong>";
													else if($products["product_external_limited_offer"] == 0) echo "No"; ?></td>
                                                    <td><?php if($products["product_listprice"] > 0) echo $products["product_listprice"] . " usd"; ?></td>
                                                    <td><?php 
													if($products["product_has_customprice"] == 1 && $products["product_customprice_currency"] == "ars") echo $products["product_finalprice"]." ars";
													else echo $products["product_finalprice"]." usd";
													?></td>
												</tr> 
                                                <?php		
											}
											?>
										</tbody>
									</table>
                                </div>
                            </td>
                            <td>
                            	
							<?php
								$res4 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'autoupdate_dollar_value'");
								$auto_update = mysqli_fetch_row($res4);
								$res5 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'updated_dollar_value'");
								$updated_value = mysqli_fetch_row($res5);
								$res6 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'fixed_dollar_value'");
								$fixed_value = mysqli_fetch_row($res6);
								$res7 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'dollar_retrieve_attemps'");
								$attemps = mysqli_fetch_row($res7);
								?>
                            	<div style="font-weight:bold; text-decoration:underline">Cotización del dolar:</div>
                                <form action="" method="post">
                                    <div class="radio" style="margin-top:10px;">
                                      <label><input type="radio" name="dollar_value_update" value="automatic" <?php if($auto_update[0] == 1) echo "checked"; ?>>Cotización automática</label>
                                    </div>
                                    <strong>Cotiz:</strong> <?php echo $updated_value[0]; ?> ars<br/>
                                   	<strong>Intentos fallidos:</strong> <?php echo $attemps[0]; ?><br/>
                                    <a href="../global_scripts/php/update_quote_tags.php?redir=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>"><strong>Actualizar</strong></a>
                                    
                                    <div style="height:32px;margin:10px 0;">
                                        <div class="radio" style="float:left; margin-top:5px;">
                                            <label><input type="radio" name="dollar_value_update" value="fixed" <?php if($auto_update[0] == 0) echo "checked"; ?>>Cotización fija</label>
                                        </div>
                                        <div style="float:right;margin-right:30px;">U$S&nbsp;
                                        	<input type="text" name="fixed_value" id="input_fixedquote" class="form-control input-sm" value="<?php echo $fixed_value[0]; ?>" <?php
											if($auto_update[0] == 1) echo "disabled"; ?>/>
                                        </div>
                                    </div>
                                    <input type="submit" class="btn btn-primary" value="Guardar cotización" />
                                </form><br/>
                                
                                <?php
								$res8 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'service_enabled'");
								$service_enabled = mysqli_fetch_row($res8);
								
								
								$res9 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'brl_quote'");
								$brl_quote = mysqli_fetch_row($res9);
								
								$res10 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'mxn_quote'");
								$mxn_quote = mysqli_fetch_row($res10);
								
								$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_menor32'");
								$alicuota_menor32 = mysqli_fetch_row($query);
								$query = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_mayor32'");
								$alicuota_mayor32 = mysqli_fetch_row($query);
								?>
                                
                                <span style="font-weight:bold; text-decoration:underline">Servicio de reventa activo:</span>
                                <form action="" method="post" style="margin-top:5px;">
                                    <div class="radio" style="display:inline-block">
                                        <label><input type="radio" name="active_service" value="1" <?php if($service_enabled[0] == 1) echo "checked"; ?>>Si</label>
                                    </div>&nbsp;&nbsp;&nbsp;&nbsp;
                                    <div class="radio" style="display:inline-block">
                                        <label><input type="radio" name="active_service"value="0" <?php if($service_enabled[0] == 0) echo "checked"; ?>>No</label>
                                    </div>
                                    <input type="submit" class="btn btn-primary btn-sm" value="Guardar estado" style="margin-left:50px;" />
                                </form>
                                
                                <div style="margin-top:18px;line-height:14px">
                                	 <span style="font-weight:bold; text-decoration:underline">Calcular precio de juego:</span><br/><br/>
                                	U$S&nbsp;<input type="text" class="form-control" id="calculator_input" placeholder="Monto" onfocus="$(this).val('');" onkeypress="return limitInputChars(event, this);" onblur="applyFormat(this);"/>
                                    <input type="button" class="btn btn-success" value="Calcular" id="calculator_button" />
                                </div>
                                <div style="margin-top:18px">
                               		Cotiz BRL 1 usd:
                                    <form action="" method="post">
                                    <input type="text" name="brl_quote" class="form-control" style="width:70px;margin-right:10ox;display:inline-block" value="<?php echo $brl_quote[0]; ?>">
                                    <input type="submit" class="btn btn-sm btn-primary" value="OK" style="display:inline-block">
                                    </form>
                                </div>
                                <div style="margin-top:18px">
                               		Cotiz MXN 1 usd:
                                    <form action="" method="post">
                                    <input type="text" name="mxn_quote" class="form-control" style="width:70px;margin-right:10ox;display:inline-block" value="<?php echo $mxn_quote[0]; ?>">
                                    <input type="submit" class="btn btn-sm btn-primary" value="OK" style="display:inline-block">
                                    </form>
                                </div>
                                <div style="margin-top:18px">
                               		Alicuota juegos &lt;32usd:
                                    <form action="" method="post">
                                    <input type="text" name="alicuota_menor32" class="form-control" style="width:70px;margin-right:10ox;display:inline-block" value="<?php echo $alicuota_menor32[0]; ?>">
                                    <input type="submit" class="btn btn-sm btn-primary" value="OK" style="display:inline-block">
                                    </form>
                                </div>
                                <div style="margin-top:18px">
                               		Alicuota juegos &gt;32 usd:
                                    <form action="" method="post">
                                    <input type="text" name="alicuota_mayor32" class="form-control" style="width:70px;margin-right:10ox;display:inline-block" value="<?php echo $alicuota_mayor32[0]; ?>">
                                    <input type="submit" class="btn btn-sm btn-primary" value="OK" style="display:inline-block">
                                    </form>
                                </div>
                                <div style="text-align:center;margin-top:25px;"><button class="btn btn-lg btn-warning" id="button_banlist" data-toggle="modal" data-target="#modal_banlist">Ver banlist</button></div>
                            </td>
                        </tr>
                    </tbody>
                    </table>
				
				
				</div><!-- End main content -->
				
				<?php require_once("../global_scripts/php/footer.php"); ?>
				
			</div><!-- End container -->
		</body>
		
		
	</html>	
	<?php
}
?>
