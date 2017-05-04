<?php
require_once "../../../config.php";
require_once ROOT."app/lib/user-page-preload.php";

$login->restricted_page($loggedUser, 3, true);



$error = -1;

if(isset($_POST["cd_number"]) && isset($_POST["date"]) && isset($_POST["ammount"]) && isset($_POST["invoice_number"]) && isset($_POST["site_payment"]) && isset($_POST["order_id"])) {
	
	if(is_numeric($_POST["cd_number"]) && is_numeric($_POST["ammount"]) && is_numeric($_POST["invoice_number"]) && is_numeric($_POST["site_payment"]) && preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $_POST["date"])) {
		
		$sql = "INSERT INTO `cd_payments` (`number`, `cd_account`, `date`, `net_ammount`, `invoice_number`, `site_payment`, `order_id`, `description`, `price_warning`)
		VALUES (NULL, ".$_POST["cd_number"].", '".$_POST["date"]."', ".$_POST["ammount"].", '".$_POST["invoice_number"]."', 
		".$_POST["site_payment"].", '".mysqli_real_escape_string($con, $_POST["order_id"])."', '', 0);";
		
		$sql2 = "UPDATE `orders` SET `order_confirmed_payment`=1 WHERE `order_id`='".mysqli_real_escape_string($con, $_POST["order_id"])."'";	
		
		if(mysqli_query($con, $sql) && mysqli_query($con, $sql2)) $error = 0;
	
	} else $error = 1;
	
}





$title = "Cargar pago - SteamBuy";
ob_start();

?>
	<ol class="breadcrumb">
		<li><a href="../">Panel admin</a></li>
		<li class="active">Cargar pago de pedido</li>
	</ol>
    <?php
	if($error == 0) {
		echo "<div class='alert alert-success'>Hecho!</div>";	
	} else if($error == 1) echo "<div class='alert alert-danger'>Datos invalidos</div>";	
	?>
	<form method="post" action="" style="width:320px;margin:0 auto;">
    	<div>
            Nro cuentadigital (1-3):<br/>
            <input class="form-control" type="text" name="cd_number" />
        </div>
        <div>
            Fecha (AAAA-MM-DD):<br/>
            <input class="form-control" type="text" name="date" />
        </div>
        <div>
            Monto acreditado (decimal con punto):<br/>
            <input class="form-control" type="text" name="ammount" />
        </div>
        <div>
            Nro boleta de pago:<br/>
            <input class="form-control" type="text" name="invoice_number" />
        </div>
        <div>
            Pago de un pedido generado por el sitio web (1-0):<br/>
            <input class="form-control" type="text" name="site_payment" />
        </div>
        <div>
            Order ID (si es del sitio):<br/>
            <input class="form-control" type="text" name="order_id" />
    	</div>
        <input type="submit" class="btn btn-primary" value="Cargar pedido" style="margin: 10px auto;display:block;" />
    </form>
<?php
$content = ob_get_clean();	



$template = new DisplayTemplate;
$template->insert_content($content, $title);
$template->display_rendered_html();


?>