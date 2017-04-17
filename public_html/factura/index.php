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
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="description" content="Sección de juegos de SteamBuy">
        <meta name="keywords" content="juegos,comprar,steam,origin,amazon">
        <meta name="robots" content="noindex, nofollow" />
        
        
        
        <title>Buscar factura - SteamBuy</title>
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link href="http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css" type="text/css">
        
        <style type="text/css">
		.form
		{
			margin: 20px auto;
			width:200px;
			text-align:center;
		}
		.form button
		{
			margin-top:10px;
		}
		</style>
        
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../resources/js/global-scripts.js"></script>

    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
            <div class="main_content">
            	<h3 style="text-align:center">Buscar una factura de compra</h3>
				<div class="form">
                    <form action="ver_factura.php" method="post">
                        ID de pedido:
                        <input type="text" class="form-control" name="order_id">
                        Clave de pedido:
                        <input type="text" class="form-control" name="order_pass">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>