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












?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Tarjetas de regalo - SteamBuy</title>
        
        <!--meta name="description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles.">
        <meta name="keywords" content="juegos,comprar,tarjeta,crédito,steam,amazon,humblebundle,bundlestars,rapipago,pago fácil,ripsa,counter strike,oferta,descuento,PayPal">
        
        <meta property="og:title" content="Tienda de SteamBuy" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar">
        <meta name="twitter:title" content="Tienda de SteamBuy">
        <meta name="twitter:description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Tienda de SteamBuy">
        <meta itemprop="description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg"-->
        
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
     
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2.01" type="text/css">
        <link rel="stylesheet" href="resources/css/giftcards-page.css" type="text/css">

		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../resources/js/global-scripts.js"></script>

    </head>
    
    <body>
    
		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">


                <div class="clearfix tab-container">
                
					<!-- Nav tabs -->
                    <ul class="nav nav-pills nav-stacked" role="tablist">
                    	<li role="presentation" class="active"><a href="#steamwallet" aria-controls="home" role="tab" data-toggle="tab">Steam Wallet cards</a></li>
                        <li role="presentation"><a href="#playstation" aria-controls="profile" role="tab" data-toggle="tab">Playstation Store</a></li>
                        <li role="presentation"><a href="#xbox" aria-controls="messages" role="tab" data-toggle="tab">Xbox</a></li>
                        <!--li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Spotify</a></li-->
                    </ul>
                    
                      <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="steamwallet">
                        	
                            <?php
							$sql = "SELECT * FROM `products_giftcards` WHERE `type`=1 AND `stock` > 0 ORDER BY `usd_ammount` ASC";
							$query = mysqli_query($con, $sql);
							while($gcardData = mysqli_fetch_assoc($query)) {
								?>	
                                <a href="../comprar/pago.php?type=2&p_id=<?php echo $gcardData["id"]; ?>"><div class="catalog-element clearfix">
                                   <img src="../resources/css/img/giftcards/steam.png" class="giftcard-img">
                                   <div class="giftcard-ammt"><?php echo $gcardData["usd_ammount"] ?> <span style="font-size:14px">USD</span></div>
                                   <div class="giftcard-name"><?php echo $gcardData["name"] ?></div>
                                   <div class="giftcard-price">$<?php echo quickCalcGame(1, $gcardData["selling_price_usd"]); ?> <span style="font-size:14px">ARS</span></div>
                                </div></a>
                                <?php
							}
							if(mysqli_num_rows($query) == 0) {
								echo "No se encontró stock de estas giftcards, disculpa las molestias.";	
							}
							?>    
                        </div>
                        <div role="tabpanel" class="tab-pane" id="playstation">
                        <?php
							$sql = "SELECT * FROM `products_giftcards` WHERE `type`=2 AND `stock` > 0 ORDER BY `usd_ammount` ASC";
							$query = mysqli_query($con, $sql);
							while($gcardData = mysqli_fetch_assoc($query)) {
								?>	
                                <a href="../comprar/pago.php?type=2&p_id=<?php echo $gcardData["id"]; ?>"><div class="catalog-element clearfix">
                                   <img src="../resources/css/img/giftcards/playstation.png" class="giftcard-img">
                                   <div class="giftcard-ammt"><?php echo $gcardData["usd_ammount"] ?> <span style="font-size:14px">USD</span></div>
                                   <div class="giftcard-name"><?php echo $gcardData["name"] ?></div>
                                   <div class="giftcard-price">$<?php echo quickCalcGame(1, $gcardData["selling_price_usd"]); ?> <span style="font-size:14px">ARS</span></div>
                                </div></a>
                                <?php
							}
							if(mysqli_num_rows($query) == 0) {
								echo "No se encontró stock de estas giftcards, disculpa las molestias.";	
							}
							?> </div>
                        <div role="tabpanel" class="tab-pane" id="xbox">
                        <?php
							$sql = "SELECT * FROM `products_giftcards` WHERE `type`=3 AND `stock` > 0 ORDER BY `usd_ammount` ASC";
							$query = mysqli_query($con, $sql);
							while($gcardData = mysqli_fetch_assoc($query)) {
								?>	
                                <a href="../comprar/pago.php?type=2&p_id=<?php echo $gcardData["id"]; ?>"><div class="catalog-element clearfix">
                                   <img src="../resources/css/img/giftcards/xbox.png" class="giftcard-img">
                                   <div class="giftcard-ammt"><?php echo $gcardData["usd_ammount"] ?> <span style="font-size:14px">USD</span></div>
                                   <div class="giftcard-name"><?php echo $gcardData["name"] ?></div>
                                   <div class="giftcard-price">$<?php echo quickCalcGame(1, $gcardData["selling_price_usd"]); ?> <span style="font-size:14px">ARS</span></div>
                                </div></a>
                                <?php
							}
							if(mysqli_num_rows($query) == 0) {
								echo "No se encontró stock de estas giftcards, disculpa las molestias.";	
							}
							?> </div>
                        <!--div role="tabpanel" class="tab-pane" id="settings">.fdgf..</div-->
                    </div>
                
                </div>



            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>