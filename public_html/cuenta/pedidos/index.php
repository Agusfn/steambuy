<?php
require_once "../../../config.php";
require_once ROOT."app/lib/user-page-preload.php";

// Solo usuarios logueados
$login->restricted_page($loggedUser, 0, true);


// Obtener cantidad de pedidos del usuario.

$sql = "SELECT COUNT(*) FROM `orders` WHERE `associated_userid` = ".$loggedUser->userData["id"];
$query = mysqli_query($con, $sql);
$ammount = mysqli_fetch_row($query);

// Obtener cantidad total de páginas. (20 pedidos por página)

$results_per_page = 20;
$total_pages = ceil($ammount[0] / $results_per_page);


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Mis pedidos - SteamBuy</title>

		<meta name="robots" content="noindex, nofollow" />

		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>
		
        <script type="text/javascript" src="../../resources/vendors/jquery.bootpag.min.js"></script>
        <script type="text/javascript" src="../resources/js/orders-pg.js"></script>
        <script type="text/javascript">
		$(document).ready(function() {
			
			$("#results").load("../resources/php/ajax-get-order-list.php", {'page':1}, function () {
				$('[data-toggle="tooltip"]').tooltip();
			});
			
			$(".pagination").bootpag({
			   total: <?php echo $total_pages; ?>,
			   page: 1,
			   maxVisible: 5
			}).on("page", function(e, num){
				disable_pagination(true, num);
				e.preventDefault();
				$("#results").append("<div id='orders-loading'><i class='fa fa-circle-o-notch fa-spin fa-3x fa-fw'></i></div>");
				$("#results").load("../resources/php/ajax-get-order-list.php", {'page':num}, function() {
					disable_pagination(false);
					$('[data-toggle="tooltip"]').tooltip();
				});
				
			});
			
		});
		</script>
        
        <link rel="stylesheet" href="../resources/css/acc-pages.css" type="text/css">
		<link rel="stylesheet" href="../resources/css/orders-pg.css" type="text/css">
        
    </head>
    
    <body>
        
        <div class="modal fade" id="inform-payment-modal" tabindex="-1" role="dialog" aria-labelledby="inform-payment-modal-title" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="inform-payment-modal-title">Informar pago de <span id="inform-product-name"></span></h4>
                    </div>
                    <div class="modal-body">
                    	<form action="informar-pago.php" method="post" enctype="multipart/form-data" id="inform-form">
                        	<div style="margin-bottom:20px">Envía una foto o scan del comprobante de pago de tu pedido para informar el pago. Si ya informaste el pago, se sobreescribirá el archivo y la fecha previas.</div>
                        	<input type="file" name="inform-image" id="input-file-inform" />
                            <input type="hidden" name="orderid" id="orderid-inform" />
                            <input type="button" class="btn btn-primary" id="submit-inform" value="Enviar comprobante" />
                        </form>
                    </div>
                </div>
            </div>
        </div>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">


                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="../pedidos/"><span class="glyphicon glyphicon-list-alt"></span>&nbsp;&nbsp;Pedidos</a></li>
                        <li><a href="../configuracion/"><i class="fa fa-cog"></i>&nbsp;&nbsp;Cuenta</a></li>
                        <?php //if($user->userData["account_balance"] != 0) echo "<li><a href='../saldo/'><i class='fa fa-usd'></i>&nbsp;&nbsp;Saldo</a></li>";	?>
                    </ul>
                    
                    <div class="tab_content">
                    	<div id="results"><div id='orders-loading'><i class='fa fa-circle-o-notch fa-spin fa-3x fa-fw'></i></div></div>
                        <div style="text-align:center;margin: 30px 0 20px 0;"><div class="pagination"></div></div>
                    </div>
                    
                    
            </div><!-- End main content -->
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>