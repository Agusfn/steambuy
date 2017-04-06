<?php
session_start();

define("ROOT_LEVEL", "../../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../../global_scripts/php/client_page_preload.php");
require_once("../../global_scripts/php/admlogin_functions.php");
require_once("../../global_scripts/php/purchase-functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
} else {
	header("Location: index.php?redir=".urlencode($_SERVER["REQUEST_URI"]));	
}


$sql = "SELECT * FROM products ORDER BY product_rating DESC";
$query = mysqli_query($con, $sql);

$gameCount = mysqli_num_rows($query);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />

        <title>Modificar catálogo - SteamBuy Admin</title>
        
        
        <link rel="shortcut icon" href="../../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/jquery-ui-1.11.0/jquery-ui.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/cat_manager_pg.css?2" type="text/css">
        
        <style type="text/css">
		.product_container {
			<?php 
			$height = ceil($gameCount/9) * 107;
			echo "height:".$height."px";	
			?>
		}
		</style>
        
		<script type="text/javascript" src="../../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../../global_design/jquery-ui-1.11.0/jquery-ui.min.js"></script>
        <script type="text/javascript" src="scripts/js/datetime_scripts.js"></script>  
        <script type="text/javascript" src="../../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../../global_scripts/js/global_scripts.js?2"></script>
        <script type="text/javascript" src="scripts/js/cat_manager_js.js?2"></script>
        <?php
		$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'brl_quote'");
		$brl_quote = mysqli_fetch_row($res);
								
		$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'mxn_quote'");
		$mxn_quote = mysqli_fetch_row($res);
		
		$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_menor32'");
		$alicuota_menor32 = mysqli_fetch_row($res);
		
		$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name`='alicuota_mayor32'");
		$alicuota_mayor32 = mysqli_fetch_row($res);
		?>
        <script type="text/javascript">
			var brl_quote = <?php echo $brl_quote[0]; ?>;
			var mxn_quote = <?php echo $mxn_quote[0]; ?>;
			var alicuota_mayor32 = <?php echo $alicuota_mayor32[0]; ?>;
			var alicuota_menor32 = <?php echo $alicuota_menor32[0]; ?>;
		</script>
    </head>
    
    <body>

		<div class="modal fade" id="modal_productdata" tabindex="-1" role="dialog" aria-labelledby="modal_title" aria-hidden="true">
        	<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="modal_title">Modificar producto</h4>
					</div>
					<div class="modal-body">
                        
                        <div style="height:52px;">
                        	<div style="float:left;width: 260px;">
                            	<span>Nombre del producto</span>
                                <input type="text" class="form-control" id="mpd_productname" />
                            </div>
                        	<div id="mpd_productid">ID: </div>
                            <div style="float:left;width: 155px;margin-left:35px;">
                            	<span>Estado del producto</span>
                                <select class="form-control" id="mpd_activestate">
                                	<option>Activado</option>
                                    <option>Desactivado</option>
                                </select>
                            </div>
                           	<button class="btn btn-danger btn-lg" id="mpd_delete_product"><span class="glyphicon glyphicon-trash"></span></button>
                        </div>
                        
                        <div class="mpd_line">
                        	<div style="float:left;width: 115px;">
                            	<span>Plataforma</span>
                                <select class="form-control" id="mpd_platform">
                                	<option>Steam</option>
                                    <option>Origin</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:20px;width: 143px;">
                            	<span>Sitio de venta</span>
                                <select class="form-control" id="mpd_sellingsite">
                                	<option>Steam Store</option>
                                    <option>Amazon</option>
                                    <option>Humblebundle</option>
                                    <option>Bundlestars</option>
                                    <option>Origin (poco uso)</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:20px;width: 358px;">
                            	<span>URL sitio de venta&nbsp;&nbsp;<a id="steam_link" href="#" target="_blank">Abrir</a>&nbsp;&nbsp;&nbsp;<a id="steamdb_link" href="#" target="_blank">SteamDB</a></span>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="mpd_site_url" placeholder="URL de tienda de Steam" />
                                    <span class="input-group-btn"><button class="btn btn-primary" id="mpd_get_product_data"><span class="glyphicon glyphicon-refresh"></span></button></span>
                                </div>
                            </div>
 
                        </div>
                        
                        <div class="mpd_line">
                        	<div style="float:left;">
                            	<span>Límite de unidades</span>
                                <select class="form-control" id="mpd_has_limitedunits">
                                	<option>No</option>
                                    <option>Sí</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:20px;">
                            	<span>Nº unidades lim.</span>
                                <input type="text" class="form-control" id="mpd_limitedunits" value="0" disabled />
                            </div>
                            <div style="float:left;margin-left:65px;">
                            	<span>Precio personaliz.</span>
                                <select class="form-control" id="mpd_has_customprice">
                                	<option>No</option>
                                    <option>Sí</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:20px;">
                            	<span>Moneda precio pers.</span>
                                <select class="form-control" id="mpd_customprice_currency" disabled>
                                	<option>USD</option>
                                    <option>ARS</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mpd_line">
                        	<div style="float:left;">
                            	<span>Oferta ext. limit.</span>
                                <select class="form-control" id="mpd_ext_limitedoffer">
                                	<option>No</option>
                                    <option>Sí</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:15px;width: 175px;">
                            	<span>Fecha fin de oferta</span>
                                <input type="text" class="form-control" id="mpd_limitedoffer_endtime" value="0000-00-00 00:00:00" disabled />
                            </div>
                            <div style="float:left;margin-left:33px;">
                            	<span>Precio de lista</span><br/>
                                <span style="font-size:11px;">USD</span>&nbsp;<input type="text" class="form-control" id="mpd_listprice" />
                            </div>
                            <div style="float:left;margin-left:15px;">
                            	<span>Precio final</span>&nbsp;<span style="font-size:10px">(<a href="javascript:void(0);" id="cheap_price">Cheap</a>)</span><br/>
                                <span style="font-size:11px;" class="customprice_currency">USD</span>&nbsp;<input type="text" class="form-control" id="mpd_finalprice" />
                            </div>
                            <div style="float:left;margin:6px 0 0 9px;font-size:13px;">Ofer. Stm:<span class="w_tooltip" data-toggle="tooltip" data-placement="top" title="Este campo indica el precio en oferta en Steam, si la hay, sino es cero. No modificar!"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></span> 
                            <input type="text" class="form-control input-sm w_tooltip" id="mpd_steam_discount_price" /></div>
                        </div>
                        
                        <div class="mpd_line">
                            Screenshots:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="mpd_screenshots_details"></span>
                            <div class="input-group">
                            	<input type="text" class="form-control" id="mpd_pics"/>
                                <div class="input-group-btn"><button class="btn btn-primary" id="mpd_get_tagpics"><span class="glyphicon glyphicon-retweet"></span></button></div>
                            </div>
                            
                        </div>
                        
                        <div class="mpd_line">
                        	<div style="float:left;width:285px;">
                            	<span>Tags</span>
                               	<input type="text" class="form-control" id="mpd_tags" />
                            </div>
                            <div style="float:left;margin-left:30px;">
                            	<span>Singleplayer</span><br/>
                                <select class="form-control" id="mpd_sp">
                                	<option>No</option>
                                    <option>Sí</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:30px;">
                            	<span>Multiplayer</span><br/>
                                <select class="form-control" id="mpd_mp">
                                	<option>No</option>
                                    <option>Sí</option>
                                </select>
                            </div>
                            <div style="float:left;margin-left:30px;">
                            	<span>Cooperativo</span><br/>
                                <select class="form-control" id="mpd_coop">
                                	<option>No</option>
                                    <option>Sí</option>
                                </select>
                            </div>
                        </div>
                       	
                        <div class="mpd_bottom">
                        	<div style="float:left;width:225px;">
                            	<span>Imágen principal &nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" id="recargar_img">Cargar</a></span>
                                <input type="text" class="form-control" id="mpd_mainpicture" />
                                <img src="" alt="imágen" id="product_img_mainpic" />
                            </div>
                            <div style="margin-left: 245px;">
                            	<span>Descripción&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(<a href="javascript:void(0);" onClick="$('#mpd_description').val('<h4>Este pack contiene los siguientes juegos:</h4>\n<ul>\n<li></li>\n</ul>');">pack</a>)</span>
                            	<textarea class="form-control" id="mpd_description"></textarea>
                            </div>
                        </div>
                        <input type="hidden" name="hidden_productid" value="" />
                        
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        				<button type="button" class="btn btn-primary" id="mpd_btn_save">Guardar</button>
					</div>
				</div>
			</div>
		</div>

		<?php require_once("../../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <div style="height:30px;">
                    <div class="btn-group" style="float:right;margin-right: 30px;">
                    	<button class="btn btn-primary" id="btn_insert"><span class="glyphicon glyphicon-plus"></span></a>&nbsp;Insertar producto</button>
                        <button class="btn btn-success" id="btn_reorder">Aplicar orden</button>
                    </div>
                    <div style="font-size:19px;float:right;margin-right: 85px;">Ordenar catálogo por relevancia</div>
                    <!--span class="btn_insert">Insertar</span>
                    <input class="custombutton btn_applyorder" type="button" value="Aplicar orden" onClick="applyProductsOrder();" /-->
                </div>

                <div class="product_container">
                	<ul id="sortable">

                        <?php
						while($products = mysqli_fetch_assoc($query))
						{	
							?>
							<li id="<?php echo "p".$products["product_id"]; ?>" class="pc_product" <?php 
							if($products["product_enabled"] == 0) echo "style='background-color: rgba(248, 215, 215, 1);'";
							else if(strpos($products["product_tags"], "superoferta") !== false) echo "style='background-color: rgba(197, 216, 246, 1);'"; ?>>
                                <div class="pcp_top">
                                    <div style="float:left;width: 20px; height:36px;">
                                        <div style="font-size:12px;"><?php echo $products["product_rating"]; ?></div>
                                        <img src="../../global_design/img/icons/<?php
                                        if($products["product_sellingsite"] == 1) echo "steam";
										else if($products["product_sellingsite"] == 2) echo "amazon";
										else if($products["product_sellingsite"] == 3) echo "humblebundle";
										else if($products["product_sellingsite"] == 4) echo "bundlestars";
										else if($products["product_sellingsite"] == 5) echo "origin";
										?>_22x22.png" class="pcp_sellingsite"/>
                                    </div>
                                    <img src="../../data/img/game_imgs/small/<?php echo $products["product_mainpicture"]; ?>" class="pcp_img" />
                                </div>
                                <div class="pcp_middle"><?php 
								echo ($products["product_update_error"] ? "<strong>(x)</strong>" : "").$products["product_name"]; ?></div>
                                <div class="pcp_bottom">
									<?php
									if($products["product_has_limited_units"] == 1) {
										?>
                                        <div class="pcp_stock">
                                        	<div style="font-weight:bold;">STOCK</div>
                                        	<div><?php echo $products["product_limited_units"]; ?></div>
                                    	</div>
                                        <?php	
									}
									?>   
                                    <div class="pcp_price">
                                    	<?php
										if(($products["product_has_customprice"] == 1 || $products["product_external_limited_offer"] == 1) && $products["product_listprice"] != 0) {
											echo "<div style='text-decoration:line-through; color:#666'>".$products["product_listprice"]."<span style='font-size:8px;'> USD</span></div>";
											echo "<div>".$products["product_finalprice"]."&nbsp;<span style='font-size:8px;'>";
											if($products["product_has_customprice"] == 1 && $products["product_customprice_currency"] == "ars") {
												echo "ARS</span></div>";
											} else {
												echo "USD</span></div>";
											}
										} else {
											echo "<div style='font-size: 14px; margin-top:4px;'>".$products["product_finalprice"]."&nbsp;<span style='font-size:10px;'>";
											if($products["product_has_customprice"] == 1 && $products["product_customprice_currency"] == "ars") {
												echo "ARS</span></div>";
											} else {
												echo "USD</span></div>";
											}
										}
										?>
                                        
                                    </div>
                                </div>
							</li>
							<?php
						}
						?>
                	</ul>
				</div>
            </div><!-- End main content -->
        	<?php require_once("../../global_scripts/php/footer.php"); ?>
        </div><!-- End container -->
    </body>
    
    
</html>





















