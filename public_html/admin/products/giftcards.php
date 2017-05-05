<?php
session_start();

define("ROOT_LEVEL", "../../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../../global_scripts/php/client_page_preload.php");
require_once("../../global_scripts/php/admlogin_functions.php");
require_once("../../global_scripts/php/purchase-functions.php");


$admin = false;
if(!isAdminLoggedIn()) {
	header("Location: ../../");
	exit;	
} else $admin = true;




?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />

        <title>Gift Cards - SteamBuy Admin</title>
        
        
        <link rel="shortcut icon" href="../../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../../global_design/css/main.css?2" type="text/css">
        

        
		<script type="text/javascript" src="../../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../../resources/js/global-scripts.js?2"></script>

    </head>
    
    <body>
		<?php require_once("../../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
            	<ol class="breadcrumb">
                  <li><a href="../">Panel admin</a></li>
                  <li class="active">Giftcards</li>
                </ol>
                <table class="table">
                	<thead>
                    	<th>Tipo</th><th>Monto</th><th>Nombre</th><th>Stock</th><th>Precio venta usd</th>
                    </thead>
                	<tbody>
						<?php
                        $sql = "SELECT * FROM `products_giftcards` ORDER BY `usd_ammount` ASC";
                        $query = mysqli_query($con, $sql);
                        
                        while($gcardData = mysqli_fetch_assoc($query)) {
                            ?>
                            <tr>
                            <td><?php
                            if($gcardData["type"] == 1) echo "Steam Wallet";
							?></td>
                            <td><?php echo $gcardData["usd_ammount"]; ?> USD</td>
                            <td><?php echo $gcardData["name"]; ?></td>
                            <td><?php echo $gcardData["stock"]; ?></td>
                            <td><?php echo $gcardData["selling_price_usd"]; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>

                
            </div><!-- End main content -->
        	<?php require_once("../../global_scripts/php/footer.php"); ?>
        </div><!-- End container -->
    </body>
    
    
</html>





















