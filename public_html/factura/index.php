<?php
require_once("../../config.php");
require_once(ROOT."app/lib/user-page-preload.php");

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="description" content="Sección de juegos de SteamBuy">
        <meta name="keywords" content="juegos,comprar,steam,origin,amazon">
        <meta name="robots" content="noindex, nofollow" />

        <title>Buscar factura - SteamBuy</title>
        
		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>
        
        <style type="text/css">
		.form {
			margin: 20px auto;
			width:200px;
			text-align:center;
		}
		.form button {
			margin-top:10px;
		}
		</style>
        


    </head>
    
    <body>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
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
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>