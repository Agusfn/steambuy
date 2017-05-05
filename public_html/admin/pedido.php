<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
} else {
	header("Location: index.php?redir=".urlencode($_SERVER["REQUEST_URI"]));	
}

$orderFound = false;

if(isset($_GET["orderid"])) {
	$sql = "SELECT * FROM orders WHERE order_id = '".mysqli_real_escape_string($con, $_GET["orderid"])."'";	
	$res = mysqli_query($con, $sql);
	if(mysqli_num_rows($res) == 1) {
		$orderData = mysqli_fetch_assoc($res);
		$orderFound = true;
	}
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <meta name="robots" content="noindex, nofollow" />
        
        <title>
        <?php if($orderFound) echo "Pedido ".$_GET["orderid"]." - Panel Admin";		else echo "Pedido no encontrado - Panel Admin"; ?>
        </title>
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/orderdetails_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../resources/js/global-scripts.js?2"></script>
		<script type="text/javascript" src="scripts/js/orderdetails_pg.js?2"></script>
        
        <?php
		if($orderFound == true) echo "<script type='text/javascript'>var orderstatus = ".$orderData["order_status"].";</script>";
		?>
        
    </head>
    
    <body>
    	<?php
		if($orderFound == true) {
			?>
            
			<div class="modal fade" id="expire_order_modal" tabindex="-1" role="dialog" aria-labelledby="expire_order_title" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
							<h4 class="modal-title" id="expire_order_title">Expirar pedido</h4>
						</div>
						<div class="modal-body">
							<div style="height:100px;">
                            	<div style="float:left;">
                                	<div class="radio"><label><input type="radio" name="expiration_type" value="time">Expiración de tiempo (5 días)</label></div>
                                    <?php
									if($orderData["product_limited_discount"] == 1) {
										echo "<div class='radio'><label><input type='radio' name='expiration_type' value='offer_end'>Oferta externa limitada finalizada</label></div>"; 
									} ?>
                                </div>
                                <div class="inform_status_box">
                                	<?php
									if($orderData["order_informedpayment"] == 0) {
										?>
                                		<div class="radio"><label><input type="radio" name="inform_status" value="no_inform">No se informó el pago</label></div>
                                    	<?php
									}
									if($orderData["order_informedpayment"] == 1) {
										?>
                                        <div class="radio"><label><input type="radio" name="inform_status" value="invalid_inform">Comprobante inválido</label></div>
                                        <div class="radio"><label><input type="radio" name="inform_status" value="late_inform">Informó tarde</label></div>                                        	
										<?php
									}
									?>
                                </div>
                            </div>
                            <div id="offer_end_time">Momento de finalización de oferta:
                            <input type="text" class="form-control" placeholder="Ej: jueves 21 a las 15 horas ó 21/05/15 15hs" /></div>
                            <div id="reject_inform_reason">Motivo de invalidéz de comprobante de pago:
                            <input type="text" class="form-control" />
                            </div>
                            
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
							<button type="button" class="btn btn-primary" id="expire_modal_submit">Cancelar pedido</button>
						</div>
					</div>
				</div>
			</div>
            
			<div class="modal fade" id="change_order_modal" tabindex="-1" role="dialog" aria-labelledby="change_order_title" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
							<h4 class="modal-title" id="change_order_title">Cambiar producto/s<?php if($orderData["order_status"] == 3) echo " y reactivar pedido"; ?></h4>
						</div>
						<div class="modal-body">
							<div class="new_name_field">Nuevo/s producto/s:<input type="text" class="form-control"/></div>
                            <div class="new_price_field">Nuevo precio en ARS del pedido (opcional):<input type="text" class="form-control" placeholder="123.45"/></div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
							<button type="button" class="btn btn-primary" id="opt_changeorder" >Aceptar</button>
						</div>
					</div>
				</div>
			</div>
	
			<div class="modal fade" id="send_keys_modal" tabindex="-1" role="dialog" aria-labelledby="send_keys_title" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
							<h4 class="modal-title" id="send_keys_title">Enviar keys o links</h4>
						</div>
						<div class="modal-body">
							Claves o keys a enviar:
							<textarea type="text" class="form-control" id="game_keys"><?php echo $orderData["product_name"]."=="; ?></textarea>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
							<button type="button" class="btn btn-primary" id="opt_sendkeys">Aceptar</button>
						</div>
					</div>
				</div>
			</div>

		<?php 
		}
		
		 require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                <?php
				if($orderFound == true) {
					?>
                    <div class="orderoptions_navbar">
                    	<?php
						if($orderData["order_status"] == 1) {
							?>
                           <div class="btn-group" style="margin-left: 40px;">
                                <button class="btn btn-danger" id="opt_cancelorder">Cancelar pedido</button>
                                <button class="btn btn-danger" id="opt_expireorder" data-toggle="modal" data-target="#expire_order_modal">Expirar</button>
							</div>
                            <div class="dropdown" style="display:inline-block;margin-left: 40px;">
                            	<button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Cambiar pedido<span class="caret"></span></button>
                              	<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                                	<li><a href="javascript:void(0);" data-toggle="modal" data-target="#change_order_modal">Cambiar producto</a></li>
                                    <li><a href="javascript:void(0);" id="change_buyer_email">Cambiar e-mail del comprador</a></li>
                                    <li><a href="javascript:void(0);" id="change_buyer_name">Cambiar nombre del comprador</a></li>
                              	</ul>
                           	</div>
                            <?php if($orderData["product_limited_discount"] == 1 && $orderData["order_reserved_game"] == 0) echo "<button class='btn btn-primary' style='margin-left: 40px;' id='opt_reserveorder'>Marcar como reservado</button>"; ?>
							<button class="btn btn-primary active" id="opt_toggle_notify" data-toggle="tooltip" data-placement="bottom" title="Informar por e-mail: Activo"><span class="glyphicon glyphicon-envelope"></span></button>                            
                            <div class="btn-group" style="float:right;margin-right: 30px;">
                                <button class="btn btn-success" id="opt_concreteorder">Concretar pedido</button>
                                <button class='btn btn-success' data-toggle='modal' data-target='#send_keys_modal'>Keys/Link</button>
                            </div>   
                            
                            <?php	
						} else if($orderData["order_status"] == 3) {
							?>
                            <div class="btn-group" style="margin-left: 40px;">
                                <button class="btn btn-primary" id="opt_reactivateorder">Reactivar pedido</button>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#change_order_modal">Reactivar y cambiar producto</button>
                            </div>
                            <?php
						} else if($orderData["order_status"] == 2) {
							echo "No hay acciones disponibles";	
						}
						?>
                        
               		</div>
                    
                    <form id="main_form" action="process.php" method="post">
                    	<input type="hidden" name="orderid" id="input_orderid" value="<?php echo $_GET["orderid"]; ?>" />
                    	<input type="hidden" name="action" id="input_action" value="" />
                        <input type="hidden" name="data" id="input_data" value="" />
                        <input type="hidden" name="notify" id="input_notify" value="1" />
                        <input type="hidden" name="redir" value="<?php if(isset($_GET["redir"])) echo $_GET["redir"]; ?>" />
                    </form>
                    <table class="table" style="table-layout: fixed;margin-top:30px;">

                    	<tr>
                        	<td style="font-size:16px">
                            <button class="btn btn-success" id="copy_order_data" data-toggle="tooltip" data-placement="top" title="Copiar datos"><i class="fa fa-clipboard"></i></button>
                            <strong>Pedido ID:</strong> <span id="order_id"><?php echo $orderData["order_id"]; ?></span></td>
                            <td style="font-size:16px;"><strong>Clave:</strong> <?php echo $orderData["order_password"]; ?></td>
                            <td style="font-size:16px;"><strong>Estado:</strong> <?php 
							if($orderData["order_status"] == 1) {
								echo "<span style='color: rgba(43, 112, 191, 1);'>Activo</span>";	
							} else if($orderData["order_status"] == 2) {
								echo "<span style='color:rgba(30, 156, 22, 1);' class='underln' data-toggle='tooltip' data-placement='top' title='".date("d/m/Y H:i:s",strtotime($orderData["order_status_change"]))."'>Concretado</span>";	
							} else if($orderData["order_status"] == 3) {
								echo "<span style='color: rgba(198, 67, 67, 1);' class='underln' data-toggle='tooltip' data-placement='top' title='".date("d/m/Y H:i:s",strtotime($orderData["order_status_change"]))."'>Cancelado</span>";	
							} 
							?></td>
                            <td><strong>Forma pago:</strong> <?php
							$split2 = explode("?id=",$orderData["order_purchaseticket"]);
                            if($orderData["order_paymentmethod"] == 1) echo "<a target='_blank' href='".$orderData["order_purchaseticket"]."'>Boleta de pago</a>&nbsp;<span style='font-size:13px'>(<a href='https://www.cuentadigital.com/area.php?name=Search&query=".$split2[1]."' target='_blank'>R</a>)</span>";
							else if($orderData["order_paymentmethod"] == 2) echo "Transferencia bancaria";

							if($orderData["order_confirmed_payment"] == 1) {
								$paymentDate = "";
								if($orderData["order_payment_time"] != "0000-00-00 00:00:00") {
									$paymentDate = "class='underln' data-toggle='tooltip' data-placement='top' title='".date("d/m/y H:i:s", strtotime($orderData["order_payment_time"]))."'";
								}
								echo "<br/><span style='color: #118709;font-size: 13px;'".$paymentDate.">[ACREDITADO]</span>";
							}
							?></td>
                        </tr>
                        <tr>
                            <td><strong>Fecha y hora:</strong> <?php echo date("d/m/y H:i:s", strtotime($orderData["order_date"])); ?></td>
                            <td colspan="2"><strong>Nombre producto:</strong> <input type="text" class="form-control product_name selected" value="<?php echo $orderData["product_name"]; ?>" readonly/></td>
                            <td><strong>ID prod. catálogo:</strong> <?php 
							if($orderData["product_fromcatalog"] == 1) {
								echo "<a href='products/#".$orderData["product_id_catalog"]."' target='_blank'>".$orderData["product_id_catalog"]."</a>";
								if($orderData["product_limited_unit"] > 0) echo "&nbsp; (<span style='font-size:12px;font-weight:bold;'>STOCK:</span> ".$orderData["product_limited_unit"].")";
							} else echo "Pedido por formulario";
							?></td>
                    	</tr>
                        <tr>
							<td style="font-size:16px;"><strong>Precio USD:</strong> <?php if($orderData["product_usdprice"] > 0) echo $orderData["product_usdprice"]; ?></td>
                            <td style="font-size:16px;"><strong>Precio ARS:</strong> $<?php echo $orderData["product_arsprice"]; ?></td>
                            <td><strong>Sitio de venta:</strong> <a href="<?php echo $orderData["product_site_url"]; ?>"><img src="../global_design/img/icons/<?php 
							if($orderData["product_sellingsite"] == 1) echo "steam"; 
							else if($orderData["product_sellingsite"] == 2) echo "amazon";
							else if($orderData["product_sellingsite"] == 3) echo "humblebundle";
							else if($orderData["product_sellingsite"] == 4) echo "bundlestars";
							else if($orderData["product_sellingsite"] == 5) echo "origin";
							?>_22x22.png"></a></td>
                            <td><strong>Oferta ext. limitada:</strong> <?php 
							if($orderData["product_limited_discount"] == 1) echo "Sí";
							else if($orderData["product_limited_discount"] == 0) echo "No";
							?></td>
                        </tr>
                        <tr>
                        	<td><strong>Pago informado:</strong> <?php 
							if($orderData["product_limited_discount"] == 1 || $orderData["order_paymentmethod"] == 2) {
								if($orderData["order_informedpayment"] == 0) echo "No";
								if($orderData["order_informedpayment"] == 1) echo "SÍ (<a href='../data/img/payment_receipts/".$orderData["order_informed_image"]."' target='_blank'>ver img</a>) <span class='glyphicon glyphicon-remove' data-toggle='tooltip' data-placement='top' title='Rechazar informe de pago' id='reject_inform'></span>";
							} else echo "No necesario"; ?></td>
                            <td><strong>Inform. fecha:</strong> <?php
                            if($orderData["order_informed_date"] != "0000-00-00 00:00:00") echo date("d/m/y H:i:s", strtotime($orderData["order_informed_date"]));
							else echo "N/A";
							?></td>
                            <td><strong>Juego reservado:</strong> <?php 
							if($orderData["product_limited_discount"] == 1 || $orderData["order_paymentmethod"] == 2) {
								if($orderData["order_reserved_game"] == 0) echo "No";
								if($orderData["order_reserved_game"] == 1) echo "SÍ";
							} else echo "No necesario"; ?></td>
                            <td style="word-break: break-all;"><strong>Keys/Links enviados:</strong><br/><?php
                            if($orderData["order_sentkeys"] != "") {
								echo nl2br($orderData["order_sentkeys"]);
							} else echo "N/A";
							?></td>
                        </tr>
                        <tr>
                            <td><strong>Forma de envío:</strong> <?php
                            if($orderData["order_send_method"] == 0) echo "Tradicional";
							else if($orderData["order_send_method"] == 1) echo "Steam Gift Friend";
							?></td>
                            <?php
							if($orderData["order_send_method"] == 1) {
								?>
                                <td colspan="3"><button class="btn btn-success" id="copy_steamurl_btn" data-toggle="tooltip" data-placement="top" title="Copiar SteamURL"><i class="fa fa-clipboard"></i></button>
                                &nbsp;&nbsp;SteamURL: <input type="text" class="form-control" id="client_steamurl" style="display:inline-block;width:450px;" value="<?php echo $orderData["buyer_steam_url"]; ?>" readonly  />
                                </td>
                                <?php	
							}
							?>
                        </tr>
                        <tr>
                        	<?php $split = explode(" ", $orderData["buyer_name"], 2); ?>
                        	<td>
                            	<button class="btn btn-success" id="copy_name_btn" data-toggle="tooltip" data-placement="top" title="Copiar nombre"><i class="fa fa-clipboard"></i></button>
                            	<strong>Nombre comprador:</strong> 
                                <input type="hidden" id="client_first_name" value="<?php echo $split[0]; ?>"/>
                            	<div><?php echo $orderData["buyer_name"]; ?></div>
                            </td>
                            
                            <td colspan="2">
                                <button class="btn btn-success" id="copy_email_btn" data-toggle="tooltip" data-placement="top" title="Copiar e-mail"><i class="fa fa-clipboard"></i></button>
                            	<strong>Email comprador:</strong>&nbsp;&nbsp;&nbsp;<div id="client_email"><?php echo $orderData["buyer_email"]; ?></div>
                            </td>
                            <td><strong>IP Comprador:</strong> <?php echo $orderData["buyer_ip"]; ?></td>
                        </tr>
                        <?php
						$sql = "SELECT COUNT(*) FROM `facturas` WHERE `factura_pedidoasoc` = '".$orderData["order_id"]."'";
						$query = mysqli_query($con, $sql);
						$count = mysqli_fetch_row($query);
						if($count[0] == 1) {
							echo "<tr><td><strong>Facturado</strong></td></tr>";
						}
						?>
                    </table>
                    
                    <?php
				} else {
					echo "<div style='text-align:center;margin:20px 0;'><h4>No se encontró el pedido o no se proporcionó la ID</h4></div>";
				}
				
				?>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>