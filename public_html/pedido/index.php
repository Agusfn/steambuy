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
        
        <title>Estado de mi pedido - SteamBuy</title>
        
        <meta name="description" content="Ver estado de mi pedido">
        
        <meta property="og:title" content="Ver mi pedido" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/pedido/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Ver estado de mi pedido" />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/pedido/">
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
        <link rel="stylesheet" href="resources/css/mi-pedido.css" type="text/css">
        
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../resources/js/global-scripts.js"></script>
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
				<h3 class="page-title">Ver información y estado de un pedido</h3>
            	<div style="height:170px;margin-bottom: 20px;">
                    <form action="detalles.php" method="post" id="form">
                        ID de pedido:
                        <input type="text" class="form-control" name="order_id" id="input_id" autocomplete="off" />
                        Clave de pedido:
                        <input type="text" class="form-control" name="order_password" id="input_password" autocomplete="off" />
                        <button type="submit" class="btn btn-primary" id="button_submit">Enviar</button>
                    </form>
                </div>
        
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>