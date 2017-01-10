<?php
// Este script sirve para cargar a la tabla de pagos, pagos manualmente que no pudieron ser sincronizados con el actualizador de precioas.  
// Además marca al pedido como "acreditado".

require_once("mysql_connection.php");
require_once("admlogin_functions.php");

if(!isAdminLoggedIn()) {
	echo "Denied";
	exit;	
}

$post_vars = array("cd_number", "date", "ammount", "invoice_number", "site_payment", "order_id");

$form_sent = true;
$error = false;
foreach($post_vars as $var) {
	if(!isset($_POST[$var])) {
		$form_sent = false;
		break;
	} else {
		if($_POST[$var] == "") $error = true;
	}
}

if($form_sent) {
	
	if(!$error) {
		$sql = "INSERT INTO `cd_payments` (`number`, `cd_account`, `date`, `net_ammount`, `invoice_number`, `site_payment`, `order_id`, `description`, `price_warning`)
		VALUES (NULL, '".escape($_POST["cd_number"])."', '".escape($_POST["date"])."', ".escape($_POST["ammount"]).", '".escape($_POST["invoice_number"])."', 
		'".escape($_POST["site_payment"])."', '".escape($_POST["order_id"])."', '', 0);";
	
		$sql2 = "UPDATE `orders` SET `order_confirmed_payment`=1 WHERE `order_id`='".escape($_POST["order_id"])."'";	
		
		mysqli_query($con, $sql);
		mysqli_query($con, $sql2);
		
		echo "Hecho!";
	
	} else {
		echo "Error: No se ingresó alguno de los datos.";
	}

}


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Cargar manualmente</title>
</head>

<body>

	<form method="post" action="">
    	<div>
            Nro cuentadigital (1-3):<br/>
            <input type="text" name="cd_number" />
        </div>
        <div>
            Fecha (AAAA-MM-DD):<br/>
            <input type="text" name="date" />
        </div>
        <div>
            Monto acreditado (decimal con punto):<br/>
            <input type="text" name="ammount" />
        </div>
        <div>
            Nro boleta de pago:<br/>
            <input type="text" name="invoice_number" />
        </div>
        <div>
            Pago de un pedido generado por el sitio web (1-0):<br/>
            <input type="text" name="site_payment" />
        </div>
        <div>
            Order ID (si es del sitio):<br/>
            <input type="text" name="order_id" />
    	</div>
        <input type="submit" value="Cargar pedido" />
    </form>

</body>
</html>


<?php

function escape($str) {
	global $con;
	return mysqli_real_escape_string($con, $str);	
}

?>