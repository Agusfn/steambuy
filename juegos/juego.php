<?php
session_start();

define("ROOT_LEVEL", "../../");

header("Content-Type: text/html; charset=UTF-8");

date_default_timezone_set("America/Argentina/Buenos_Aires");

if(!isset($_GET["id"])) {
	header("Location: index.php");	
}

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


$gameFound = false;
if(is_numeric($_GET["id"])){
	$query = mysqli_query($con, "SELECT * FROM `products` WHERE `product_id` = '" . mysqli_real_escape_string($con, $_GET["id"]) . "'");
	$gameData = mysqli_fetch_assoc($query);
	if(mysqli_num_rows($query) == 1){
		$gameFound = true;
	}
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <?php
        if(!$gameFound) {
			?>
            <meta name="robots" content="noindex, nofollow" />
			<title>Producto no encontrado - SteamBuy</title>
            <?php		
		} else {
			
			if(strlen($gameData["product_description"]) > 120) {
				$stringCut = substr(strip_tags($gameData["product_description"]), 0, 120);
				$shortDesc = substr($stringCut, 0, strrpos($stringCut, " "))."..."; 
			} else $shortDesc = strip_tags($gameData["product_description"]);
			?>
            <title><?php echo $gameData["product_name"]; ?> - SteamBuy</title>
            <meta name="description" content="<?php echo $shortDesc; ?>">
        	<meta name="keywords" content="juego,comprar,tarjeta,crédito,rapipago,ripsa,pago fácil,<?php echo $gameData["product_tags"]; ?>">
            
            <meta property="og:title" content="<?php echo $gameData["product_name"]; ?>" />
            <meta property="og:type" content="website" />
            <meta property="og:url" content="http://steambuy.com.ar/juegos/<?php echo htmlspecialchars($_GET["id"]); ?>/" />
            <meta property="og:image" content="http://steambuy.com.ar/data/img/game_imgs/<?php echo $gameData["product_mainpicture"]; ?>" />
            <meta property="og:site_name" content="SteamBuy" />
            <meta property="og:description" content="<?php echo $shortDesc; ?>" />
            
            <meta name="twitter:card" content="summary">
            <meta name="twitter:url" content="http://steambuy.com.ar/juegos/<?php echo htmlspecialchars($_GET["id"]); ?>/">
            <meta name="twitter:title" content="<?php echo $gameData["product_name"]; ?>">
            <meta name="twitter:description" content="<?php echo $shortDesc; ?>">
            <meta name="twitter:image" content="http://steambuy.com.ar/data/img/game_imgs/<?php echo $gameData["product_mainpicture"]; ?>">
            
            <meta itemprop="name" content="<?php echo $gameData["product_name"]; ?>">
            <meta itemprop="description" content="<?php echo $shortDesc; ?>">
            <meta itemprop="image" content="http://steambuy.com.ar/data/img/game_imgs/<?php echo $gameData["product_mainpicture"]; ?>">
 
            <?php
		}
		?>

        <link rel="shortcut icon" href="../../favicon.ico?2">
        
        <link rel="stylesheet" href="../../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="../design/css/product_info_page.css?2" type="text/css">
        
		<script type="text/javascript" src="../../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../../global_scripts/js/global_scripts.js?2"></script>
		
        <?php
		if($gameFound == true) 
		{
			$limitedOffer = 0;
            if($gameData["product_external_limited_offer"] == 1 && $gameData["product_external_offer_endtime"] != "0000-00-00 00:00:00") {
            	$discount_end_time = strtotime($gameData["product_external_offer_endtime"]);
				$end_hour = date("H:i:s", $discount_end_time );
				if($end_hour == "00:00:00" || ($discount_end_time - time()) > 172800) { // Si termina a las 00 hs o si faltan mas de 48hs se muestra solo fecha
					$limitedOffer = 1;
					$end_date = date("d/m/y", $discount_end_time);
				} else { 
					$limitedOffer = 2;
				}
				
			}	
			?>
			<script type="text/javascript">
				<?php
				if($limitedOffer == 2) {
					?>
					var offer_end_datetime = new Date(<?php echo $discount_end_time * 1000; ?>);
					offer_end_datetime = offer_end_datetime.getTime();
					var current_date = new Date();
					current_date = parseInt(current_date.getTime());
					
					var _second = 1000;
					var _minute = _second * 60;
					var _hour = _minute * 60;
					var _day = _hour * 24;

					function updateCountdown() 
					{
						current_date += 1000;
						var difference = offer_end_datetime - current_date;
						//alert(difference);
						if(difference > 0) 
						{
							var hours = Math.floor(difference / _hour);
							if(hours.toString().length == 1) hours = "0" + hours;
							var minutes = Math.floor((difference % _hour) / _minute);
							var seconds = Math.floor((difference % _minute) / _second);
							$("#product_offer_countdown").text(hours + ":" + ("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2));
						} else {
							clearInterval(updateTimer);
							$("#product_offer_countdown").text("00:00:00");
							
						}
					}
					<?php
				}
				?>
				$(document).ready(function() {
					$('.carousel').carousel();
					<?php if($limitedOffer == 2)  echo "var updateTimer = setInterval(updateCountdown, 1000);"; ?>
				});
            </script>
		<?php	
		}
		
		?>

    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                
				<?php if($gameFound == true)
				{
					if($gameData["product_tags"] != "") $gameTags = true;
					else $gameTags = false;
					
					$outOfStock = 0;
					if($gameData["product_has_limited_units"] == 1 && $gameData["product_limited_units"] == 0) $outOfStock = 1;
					
					?>
                        <div class="product_info_top">
                            <div class="pit_left">
                                <div class="product_name"><?php echo $gameData["product_name"]; ?></div>
								<div class="purchase_info">
                                
                                	<?php
									if($gameData["product_enabled"] == 0) {
										echo "<div class='out_of_stock'>Disculpa, este producto no está disponible.</div>";
									} else if($outOfStock) {
										echo "<div class='out_of_stock'>Disculpa, este producto está fuera de stock.</div>";
									} else {
										?>
                                        <form action="../comprar/" method="post">
                                        	<input type="hidden" name="gameid" value="<?php echo htmlspecialchars($_GET["id"]); ?>"/>
                                            <button type="submit" class="btn btn-success btn_purchase">Comprar juego <span class="glyphicon glyphicon-shopping-cart"></span></button>
                                        </form>
                                    
										<?php
                                        if($gameData["product_has_customprice"] == 1 && $gameData["product_customprice_currency"] == "ars") {
                                            echo "<div class='price_normal'>&#36;".$gameData["product_finalprice"]." ARS</div>";
                                        } else if(($gameData["product_has_customprice"] == 0 && $gameData["product_external_limited_offer"] == 0) || $gameData["product_sellingsite"] == 4) {
                                            echo "<div class='price_normal'>&#36;".quickCalcGame(1,$gameData["product_finalprice"])." ARS <span>(".$gameData["product_finalprice"]." usd)</span></div>";
                                        }  else if($gameData["product_external_limited_offer"] == 1 || $gameData["product_has_customprice"] == 1) {
											$ars_listprice = quickCalcGame(1,$gameData["product_listprice"]);
											$ars_finalprice = quickCalcGame(1,$gameData["product_finalprice"]);
                                        	?>
                                            <div class="pricebox_discount">
                                                <div class="pd_percent">-<?php echo round(100-floatval($ars_finalprice * 100 / $ars_listprice)); ?>%</div>
                                                <div class="pd_prices">
                                                    <div class="pd_listprice">$<?php echo $ars_listprice; ?> <span>(<?php echo $gameData["product_listprice"]; ?> usd)</span></div>
                                                    <div class="pd_finalprice">$<?php echo $ars_finalprice; ?> <span>(<?php echo $gameData["product_finalprice"]; ?> usd)</span></div>
                                                </div>
                                            </div>
                                            <?php
                                        }
									}
									?>
								</div>
                                <?php
								
								if(!$outOfStock && $gameData["product_enabled"] != 0) {
									if(($gameData["product_sellingsite"] == 3 || $gameData["product_sellingsite"] == 4) || $gameData["product_has_customprice"] == 1 || $gameData["product_external_limited_offer"] == 1) 
									{
										if($gameData["product_sellingsite"] == 3) {
											echo "<div class='discount_info di_humblebundle'>Oferta Humble Bundle";
										} else if($gameData["product_sellingsite"] == 4) {
											echo "<div class='discount_info di_bundlestars'>Oferta Bundlestars";
										} else if($gameData["product_has_customprice"] == 1) {
											echo "<div class='discount_info di_steambuy'>En oferta de SteamBuy";
										} else if($gameData["product_external_limited_offer"] == 1) {
											if($gameData["product_sellingsite"] == 1) {
												echo "<div class='discount_info di_steam'>En oferta de Steam";
											} else if($gameData["product_sellingsite"] == 2) {
												echo "<div class='discount_info di_amazon'>En oferta de Amazon";
											}
										}
										if($limitedOffer == 1) {
											echo ". <span style='font-size:13px;'>La oferta finaliza el ".$end_date.".</span>";
										} else if($limitedOffer == 2){
											echo ". <span style='font-size:13px;'>La oferta finaliza en <span id='product_offer_countdown'>00:00:00</span>.</span>";
										}
										if($gameData["product_has_limited_units"] == 1) {
											echo "<div class='ltd_stock'>STOCK LIMITADO</div>";
										} 
										echo "</div>";
									}
								}
								?>
								
							</div>
                            <img class="product_picture" src="../../data/img/game_imgs/<?php echo $gameData["product_mainpicture"]?>" alt="<?php echo $gameData["product_name"]?>" />
                        </div>
                        <div class="product_info_middle">
                        	
                            <div class="pim_left" <?php if($gameTags == false) echo "style='margin: 0 27px 15px 0;'"; ?>>
                            	
                                
								<div class="panel panel-default panel_img">
                                	<div class="panel-heading">Capturas</div>
                                	<div class="panel-body">
                  
                                        <div id="product_picture_carousel" class="carousel slide">
                                          	<ol class="carousel-indicators">
                                            	<?php
												$pics = explode(";", $gameData["product_pics"]);
												
												for($i=0;$i<sizeof($pics);$i++) {
													?>
                                                    <li data-target="#product_picture_carousel" data-slide-to="<?php echo $i; ?>" <?php if($i == 0) echo "class='active'"; ?>></li>
                                                    <?php	
												}
												?>
                                         	</ol>
											<div class="carousel-inner">
                                            <?php
											for($i=0;$i<sizeof($pics);$i++) {
												if((strpos($pics[$i], "cdn.akamai.steamstatic.com") !== false) || (strpos($pics[$i], "steampowered.com") !== false)) { ?>
                                                    <div class="item<?php if($i == 0) echo " active"; ?>"><a href="<?php echo $pics[$i]."1920x1080.jpg"; ?>" target='_blank'><img src="<?php echo $pics[$i]."600x338.jpg"; ?>" alt="captura <?php echo ($i+1); ?>"></a></div>
                                                    <?php
												} else { ?>
                                                    <div class='item<?php if($i == 0) echo " active"; ?>'><a href="<?php echo $pics[$i]; ?>" target="_blank"><img src="<?php echo $pics[$i]; ?>" alt="captura <?php echo $gameData["product_name"]; ?>"></a></div>
                                                    <?php
												}
											}
											?>
											</div>
                                          	<a class="left carousel-control" href="#product_picture_carousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
                                          	<a class="right carousel-control" href="#product_picture_carousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
										</div>
                                	</div>
								</div>
                            </div>
                            <div class="pim_right">
                            	
                                  
                                <div class="panel panel-default">
                                	<div class="panel-heading">Propiedades</div>
                                	<div class="panel-body" style="padding:14px 7px;">
                                    
                                    	<ul class="product_feature_list">
                                        	<?php
											if($gameData["product_platform"] == 1) {
												echo "<li><div class='pfl_icon'><img src='../../global_design/img/icons/game_properties/steam.png' class='pfl_iconimg' alt='steam'></div><div class='pfl_text'>Activable en Steam</div></li>";
											} else if($gameData["product_platform"] == 2) {
												echo "<li><div class='pfl_icon'><img src='../../global_design/img/icons/game_properties/origin.png' class='pfl_iconimg' alt='origin'></div><div class='pfl_text'>Activable en Origin</div></li>";
											}
											if($gameData["product_singleplayer"] == 1) {
												echo "<li><div class='pfl_icon'><img src='../../global_design/img/icons/game_properties/singleplayer.png' class='pfl_iconimg' alt='single player'></div><div class='pfl_text'>Un jugador</div></li>";
											}
											if($gameData["product_multiplayer"] == 1) {
												echo "<li><div class='pfl_icon'><img src='../../global_design/img/icons/game_properties/multiplayer.png' class='pfl_iconimg' alt='multijugador'></div><div class='pfl_text'>Multijugador</div></li>";
											}
											if($gameData["product_cooperative"] == 1) {
												echo "<li><div class='pfl_icon'><img src='../../global_design/img/icons/game_properties/multiplayer.png' class='pfl_iconimg' alt='cooperativo'></div><div class='pfl_text'>Cooperativo</div></li>";
											}
											
											if($gameData["product_site_url"] != "") {
												if($gameData["product_sellingsite"] != 3) {
													echo "<li><div class='pfl_text'><a href='".$gameData["product_site_url"]."' target='_blank'>";
													if($gameData["product_sellingsite"] == 1) {
														echo "Ver producto en Steam";
													} else if($gameData["product_sellingsite"] == 2) {
														echo "Ver producto en Amazon";
													} else if($gameData["product_sellingsite"] == 4) {
														echo "Ver producto en Bundlestars";
													} else if($gameData["product_sellingsite"] == 5) {
														echo "Ver producto en Origin";
													}
													echo "</a></div></li>";	
												}
											}
											
											?>
                                        </ul>
                                    </div>
                                </div>
                                
                                <?php
								if($gameTags) 
								{
								?>
                                    <div class="panel panel-default panel_tags">
                                        <div class="panel-heading">Tags</div>
                                        <div class="panel-body">
											<?php
                                            $tags = explode(",", $gameData["product_tags"]);
                                            foreach($tags as $tag) {
                                                echo "<a href='../?tag=".$tag."'><div class='product_tag'>".$tag."</div></a>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php
								}
								?>  
                            </div>
                        </div>
                        
                        <div <?php if($gameTags == false) echo "class='description_text_wrap'"; else echo "style='margin: 0 25px;'"; ?>>
                        	<div class="product_description">
							<?php 
							if(strpos($gameData["product_description"], "game_area_description") !== false) {
								echo $gameData["product_description"]; 
							} else echo nl2br($gameData["product_description"]); 
							?>
                            </div>
                        </div>	
                    <?php 
				}
				else
				{
				?>
                	<div class="notfound">El producto no existe
                    <br/><a href="../../"><span style="font-size:16px;">Volver a la página principal</span></a></div>
                    
                <?php 
				}
				?>
            
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>