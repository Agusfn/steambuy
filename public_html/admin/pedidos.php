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
} else {
	header("Location: index.php?redir=".urlencode($_SERVER["REQUEST_URI"]));	
	exit;
}

$type = 1;
if(isset($_GET["type"])) {
	if($_GET["type"] == 1 || $_GET["type"] == 2 || $_GET["type"] == 3) $type = $_GET["type"];	
}

if(isset($_GET["pg"])) {
	if(is_int($_GET["pg"] / 50)) {
		$current_page = $_GET["pg"];
	} else {
		$current_page = 0;	
	}
} else $current_page = 0;	


$extra_query = "";
if(isset($_GET["filter"])) { // Filter: 1= sólo pedidos acreditados (boleta), 2=sólo pedidos por transferencias, 3= acreditados o informados
	if($_GET["filter"] == 1) {
		$extra_query = " AND (`order_confirmed_payment`=1 OR (`order_paymentmethod`=2 AND `order_informedpayment`=1))";
	} else if($_GET["filter"] == 2) {
		$extra_query = " AND `order_paymentmethod`=2";
	} else if($_GET["filter"] == 3) {
		$extra_query = " AND (`order_confirmed_payment`=1 OR `order_informedpayment`=1)";
	}
}

// Orden. 1=ordenar por nombre del juego alfabeticamente, 2=ordenar por e-mail, 3=ordenar por precio menor, 4=ordenar por precio mayor
if(isset($_GET["order"])) {
	if($_GET["order"] == 1) {
		$extra_query .= " ORDER BY `product_name` ASC";
	} else if($_GET["order"] == 2) {
		$extra_query .= " ORDER BY `buyer_email` ASC";
	} else if($_GET["order"] == 3) {
		$extra_query .= " ORDER BY `product_arsprice` ASC";
	} else if($_GET["order"] == 4) {
		$extra_query .= " ORDER BY `product_arsprice` DESC";
	} else {
		if($type == 1) $extra_query .= " ORDER BY order_number DESC";	
		else $extra_query .= " ORDER BY order_status_change DESC";	
	}
} else {
	if($type == 1) $extra_query .= " ORDER BY order_number DESC";	
	else $extra_query .= " ORDER BY order_status_change DESC";
}


// Consulta para obtener número total de pedidos
if($type == 1) $sql = "SELECT count(*) FROM orders WHERE order_status = 1".$extra_query;
else if($type == 2 || $type == 3) $sql = "SELECT count(*) as `number` FROM orders WHERE order_status = ".$type.$extra_query;
$count_res = mysqli_query($con, $sql);
$count = mysqli_fetch_row($count_res);

if($count[0] > 0) {
	// Consulta base
	$sql1 = "SELECT * FROM orders WHERE order_status = ".$type.$extra_query;
	// Paginación
	$sql2 = " LIMIT ".$current_page.", 50";
	
	$res = mysqli_query($con, $sql1.$sql2);
	
	$totalpages = floor($count[0] / 50);
	if(($count[0] % 50) > 0) $totalpages += 1;
	
	$current_real_pg = ($current_page / 50) + 1; // Número de página (1, 2, 3, 4, 5...)
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <meta name="robots" content="noindex, nofollow" />
        
        <title><?php if($type == 1) echo "Pedidos activos";
		else if($type == 2) echo "Pedidos concretados";
		else if($type == 3) echo "Pedidos cancelados"; ?> - Panel Admin</title>
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/orderslist_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
		<script type="text/javascript" src="scripts/js/orderlist_scripts.js?2"></script>
    </head>
    
    <body>
		<div class="modal fade" id="order_links_modal" tabindex="-1" role="dialog" aria-labelledby="order_links_title" aria-hidden="true">
			<div class="modal-dialog" style="width:900px;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="order_links_title">Links de pedidos</h4>
					</div>
					<div class="modal-body">
                    	<div id="order_count">0 productos listados:</div>
                        <table class="order_links_table">
                            <col width='390px'><col width="65px"><col width="375px"><col width="30px">
                        </table>
                        <div style="margin-top:20px;">
                        	<button class="btn btn-success" id="open_steam_urls">Abrir URLs Steam</button>
                            <button class="btn btn-success" id="open_steamdb_urls">Abrir URLs SteamDB</button>
                            <button class="btn btn-primary w_tooltip" id="copy_steam_links" data-toggle="tooltip" data-placement="top" title="Lista de links para copiar"><i class="fa fa-clipboard"></i></button>
                        </div>
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
            	<div class="orderlist_navbar">
					<div class="ordertypes_bar">
                        <div class="btn-group">
                          <a href="?type=1" class="btn btn-primary <?php if($type == 1) echo "active"; ?>">Activos</a>
                          <a href="?type=2" class="btn btn-primary <?php if($type == 2) echo "active"; ?>">Concretados</a>
                          <a href="?type=3" class="btn btn-primary <?php if($type == 3) echo "active"; ?>">Cancelados</a>
                        </div>
                    </div>
                   	<div class="searchorder_bar">
                    	<form action="pedido.php" method="get" id="searchorder_form" onkeypress="return event.keyCode != 13;">
                        	<input type="text" name="orderid" class="form-control" id="searchorder_input" placeholder="ID/nro boleta/cdkey/link"/>
                            <input type="button" class="btn btn-success" id="searchorder_button" value="Buscar" />
                        </form>
						<!--div class="btn-group">
                        	<button type="button" class="btn btn-success">Buscar</button>
                          	<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            	<span class="caret"></span>
                            	<span class="sr-only">Toggle Dropdown</span>
                         	</button>
                          	<ul class="dropdown-menu">
                            	<li class="dropdown-header">Buscar por:</li>
                                <li><a href="#">ID de pedido</a></li>
                                <li><a href="#">Nombre de producto</a></li>
                                <li><a href="#">E-mail de comprador</a></li>
                                <li><a href="#">Nombre del comprador</a></li>
                                <li><a href="#">Clave de activación</a></li>
                          	</ul>
						</div-->
                    </div> 
                </div>
                <div class="orderlist_optionsbar">
                    <div class="searchinfo_bar">
                        Se encontraron <strong><?php echo $count[0]; ?></strong> pedidos <?php
                        if($type == 1) echo "activos";
                        else if($type == 2) echo "concretados";
                        else if($type == 3) echo "cancelados"; ?>.<br/>
                        <?php
                        if($count[0] > 0) echo "<span style='font-size:14px;'>Mostrando página <strong>".$current_real_pg."</strong> de <strong>".$totalpages."</strong></span>";
                        ?>
                        <div style="margin-top:3px;"><a href="javascript:void(0);" id="today_payments">Ver pagos de hoy</a></div>
                        <div id="today_payments_box"><?php

						if($config["payments_last_revised"] != date("d-m-Y") || $config["today_payments"] == "") {
							echo "No se han acreditado pagos del sitio hoy";
						} else {
							$res1 = mysqli_query($con, "SELECT COUNT(*) FROM `cd_payments` WHERE `date`='".date("Y-m-d")."'");
							$today_payments = mysqli_fetch_row($res1);

							echo "Pagos totales de hoy: <strong>".$today_payments[0]."</strong><br/><br/>";
							echo "
							<div>
								<div style='float:left;margin-right:10px;'><table style='width:330px;'>".$config["today_payments_names"]."</table></div>
								<div style='float:right;'><table>".$config["today_payments"]."</table></div>
							</div>";
						}
                        
                        ?></div>
                    </div>

					<?php
					if($type == 1) {
						?>
                        
                        <div style="float:right;">
                            <div class="btn-group">
                              <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Acción <span class="caret"></span>
                              </button>
                              <ul class="dropdown-menu">
                                <li><a href="javascript:void(0);" id="orderoptions_getlinks">Ver links de productos</a></li>
                                <li><a href="javascript:void(0);" id="orderoptions_viewemails">Ver e-mail compradores</a></li>
                                <li><a href="javascript:void(0);" id="orderoptions_cancelorders">Cancelar</a></li>
                                <li><a href="javascript:void(0);" id="orderoptions_expireorders">Expirar</a></li>
                                <li><a href="javascript:void(0);" id="orderoptions_concreteorders">Marcar como concretado</a></li>
                              </ul>
                            </div>
                        </div>
                        <div class="selected_orders_count">0 pedidos seleccionados</div>                    
                        <?php
					}
					?>
                    <div class="steam_fetch_loading">Cargando precios de Steam</div>

            	</div>
                
            	<table class="table table-striped table-condensed table-bordered data_table of_hidden orders_table">
                	<col width='26px'><col width="53px"><col width="74px"><col width="40px"><col width="45px"><col width="210px"><col width="40px"><col width="30px"><col width="44px"><col width="45px"><col width="54px"><col width="179px"><col width="44px"><col width="44px"><col width="49px">             
                    <thead>
                    	<tr style="font-size:13px; <?php if($type == 2) echo "background-color: rgba(210, 255, 186, 1);";
						else if($type == 3) echo "background-color: rgba(255, 222, 217, 1);"; ?>">
                        	<th><?php if($type == 1) { ?><input type="checkbox" id="order_maincheckbox"/><?php } ?></th><th>ID</th><th>Fecha</th><th>Cat.<br/>ID</th><th>Stock</th><th>Nombre</th><th>Sitio</th><th><span style="font-size:10px;">Stm.<br/>Price</span></th><th style="text-align:center">USD</th><th>Desc.<br/>Lim.</th><th style="text-align:center">ARS</th><th>Correo</th><th>Infor<br/>mado</th><th>Reser<br/>vado</th><th>Medio</th>
                        </tr>
                    </thead>
                	<tbody>
                    	<?php
						if($count[0] > 0) {
							while($order = mysqli_fetch_assoc($res)) 
							{
								?>
								<tr <?php if($order["order_confirmed_payment"] == 1) echo "class='green_row'"; ?>>
                                	<td><?php
                                    if($type == 1) {
										?>
                                        <input type="checkbox" class="select_checkbox" id="<?php echo $order["order_id"]; ?>"/>
                                        <input type="hidden" class="order_current_steam_price" value="<?php echo $order["product_cur_steam_price"]; ?>" />
										<?php
									} 
									?></td>
									<td class="orderid"><a href="pedido.php?orderid=<?php echo $order["order_id"]; ?>&redir=<?php echo urlencode($_SERVER["REQUEST_URI"]); ?>"><?php echo $order["order_id"]; ?></a></td>
									<td><?php echo date("d/m/y", strtotime($order["order_date"])); ?></td>
									<td><?php if($order["product_fromcatalog"] == 1) {
										echo "<a href='products/#".$order["product_id_catalog"]."' target='_blank'>".$order["product_id_catalog"]."</a>";	
									} ?></td>
									<td><?php if($order["product_limited_unit"] > 0) echo $order["product_limited_unit"]; ?></td>
									<td style="font-size:13px;"><span class="order_select"><?php echo $order["product_name"]; ?></span></td>
									<td align="center"><a href="<?php echo $order["product_site_url"]; ?>" class="product_url"><img src="../global_design/img/icons/<?php
									if($order["order_type"] == 2) echo "paypal";
									else if($order["product_sellingsite"] == 1) echo "steam";
									else if($order["product_sellingsite"] == 2) echo "amazon";
									else if($order["product_sellingsite"] == 3) echo "humblebundle";
									else if($order["product_sellingsite"] == 4) echo "bundlestars";
									else if($order["product_sellingsite"] == 5) echo "origin";
									?>_22x22.png"/></a></td>
                                    <td><span class="steam_price"></span></td>
									<td><span style='font-size:13px;'><?php if($order["product_usdprice"] != 0) echo $order["product_usdprice"]; ?></span></td>
									<td align="center" style="font-size:15px;"><?php if($order["order_type"] == 1) {
										if($order["product_limited_discount"] == 0) echo "No";
										else if($order["product_limited_discount"] == 1) echo "<strong>SI</strong>";
									} ?></td>
									<td><?php 
									if($order["order_discount_coupon"] != "") {
										//Calc. porcent. dto.
										$precio_orig = $order["coupon_discount_amt"] + $order["product_arsprice"];
										$porcentaje = round( (100*$order["coupon_discount_amt"]) / $precio_orig);
										echo "<span style='border-bottom: 1px dotted;' data-toggle='tooltip' data-placement='top' title='Dto. cupón ".$order["order_discount_coupon"]." ".$porcentaje."%. Precio orig: &#36;".$precio_orig."'>".$order["product_arsprice"]."</span>";
									} else {
										echo $order["product_arsprice"]; 
									}
									
									?></td>
									<td style="font-size:13px;"><span class="order_email"><?php echo $order["buyer_email"]; ?></span></td>
									<td align="center" style="font-size:15px;"><?php 
									if(($order["order_type"] == 1 && ($order["product_limited_discount"] == 1 || $order["order_paymentmethod"] == 2)) || 
									($order["order_type"] == 2 && $order["order_paymentmethod"] == 2)) {
										if($order["order_informedpayment"] == 0) echo "No";
										if($order["order_informedpayment"] == 1) echo "<a href='../data/img/payment_receipts/".$order["order_informed_image"]."' target='_blank'><strong>SI</strong></a>";
									} ?></td>
									<td align="center" style="font-size:15px;"><?php 
									if($order["order_type"] == 1 && $order["product_limited_discount"] == 1) {
										if($order["order_reserved_game"] == 0) echo "No";
										if($order["order_reserved_game"] == 1) echo "<strong>SI</strong>";
									} ?></td>
									<td align="center"><?php 
									if($order["order_paymentmethod"] == 1) echo "<a href='".$order["order_purchaseticket"]."'><img src='design/img/boleta.png'/></a>";
									else if($order["order_paymentmethod"] == 2) echo "<img src='design/img/transferencia.png'/>";
									?> </td>
								</tr>
								<?php
							}
						} else {
							?>
                            <tr><td colspan="13" style="text-align:center">No se encontraron pedidos</td></tr>
                            <?php	
						}
						?>
                    </tbody>
                </table>
            	
                <?php
				if($count[0] > 0) 
				{
				?>
                    <div class="pagination_bar">
                        <ul class="pagination">
                        <?php
                            $order_type_header = "?type=".$type.(isset($_GET["filter"]) ? "&filter=".urlencode($_GET["filter"]) : "").(isset($_GET["order"]) ? "&order=".urlencode($_GET["order"]): "");
                            if($totalpages > 5) {
                                if($current_real_pg <= 2) $a = 1;
                                else if($current_real_pg >= ($totalpages - 2)) $a = $totalpages - 4;  
                                else $a = $current_real_pg - 2;
                                if($a > 1) echo "<li><a href='".$order_type_header."&pg=0'>&laquo;</a></li>";
                                if($current_real_pg == 1) echo "<li class='disabled'><a href='javascript:void(0);'>&lsaquo;</a></li>";
                                else echo "<li><a href='".$order_type_header."&pg=".($current_page - 50)."'>&lsaquo;</a></li>";
                                for($i=$a;$i<=($a + 4);$i++) {
                                    if($i == $current_real_pg) echo "<li class='active'><a href='javascript:void(0);'>".$i."</a></li>"; 
                                    else echo "<li><a href='".$order_type_header."&pg=".(($i - 1) * 50)."'>".$i."</a></li>"; 
                                }
                                if($current_real_pg == $totalpages) echo "<li class='disabled'><a href='javascript:void(0);'>&rsaquo;</a></li>";
                                else echo "<li><a href='".$order_type_header."&pg=".($current_page + 50)."'>&rsaquo;</a></li>";
                                if(($a + 4) < $totalpages) echo "<li><a href='".$order_type_header."&pg=".(($totalpages - 1) * 50)."'>&raquo;</a></li>";
                            } else {
                                if($current_real_pg == 1) echo "<li class='disabled'><a href='javascript:void(0);'>&lsaquo;</a></li>";
                                else echo "<li><a href='".$order_type_header."&pg=".($current_page - 50)."'>&lsaquo;</a></li>";
                                for($i=1;$i<=$totalpages;$i++) {
                                    if($i == $current_real_pg) {
                                        echo "<li class='active'><a href='javascript:void(0);'>".$i."</a></li>"; 
                                    } else {
                                        echo "<li><a href='".$order_type_header."&pg=".(($i - 1) * 50)."'>".$i."</a></li>"; 
                                    }
                                }
                                if($current_real_pg == $totalpages) echo "<li class='disabled'><a href='javascript:void(0);'>&rsaquo;</a></li>";
                                else echo "<li><a href='".$order_type_header."&pg=".($current_page + 50)."'>&rsaquo;</a></li>";
                            }
                            ?>
                        </ul>
                    </div>
                <?php
				}
				?>
            
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>