<?php

require_once("mysql_connection.php");
require_once("main_purchase_functions.php");



if(isset($_POST["price"]) && isset($_POST["pay_method"]))
{

	if($_POST["pay_method"] == 1 || $_POST["pay_method"] == 2) {
		$result = quickCalcGame($_POST["pay_method"], $_POST["price"]);
	} else {
		echo "error paymethod";
		return;	
	}
	
	if(!isset($_POST["admin_query"])) {
		echo $result;
	} else {
		$ingreso = $result - ((0.04 * $result + 1.25) + (0.04 * $result + 1.25) * 0.21); // El ingreso neto por el juego (pago - tarifa [0.4% + $1.25 + IVA])
		$ganancia = round($ingreso - ($_POST["price"] * getDollarQuote() * 1.35), 2); // Cotización prevista para inicio de febrero. Actualizar!!
		echo "Cobrar:  $".$result.". Ganancia:  $".$ganancia;
	}
}

?>