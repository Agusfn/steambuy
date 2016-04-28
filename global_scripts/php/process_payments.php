<?php
ini_set('max_execution_time', 500);
define("ROOT_LEVEL","../../");
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once("mysql_connection.php");

if(!$con) exit;
$config = include("../config.php");

require_once("../AfipWs/AfipWs.php");


echo date("d-m-Y")."<br/><br/>";

//$paymentlist[1] = file_get_contents("cd_api_test3.txt"); 
$paymentlist[1] = file_get_contents("https://www.cuentadigital.com/exportacion.php?control=ef67b67798e79b6ebd0250074755b12d"); // Cuenta 1: agusfn
$paymentlist[2] = file_get_contents("https://www.cuentadigital.com/exportacion.php?control=19d5f8f483c7a433a3ffcad2e6823ac8"); // Cuenta 2: tomasfn 
$paymentlist[3] = file_get_contents("https://www.cuentadigital.com/exportacion.php?control=59905e7725bfaedb84d1e55f210c962c"); // Cuenta 3: rfn07




// Loguear todos los pagos en la DB y anotar los nuevos pagos en la tabla de pagos diaria

if(date("m", strtotime($config["payments_last_revised"])) != date("m") ) {
	$config["cd1_balance"] = 0;
	$config["cd2_balance"] = 0;
	$config["cd3_balance"] = 0;
} else {
	// Revisar si las cantidades logueadas en config coinciden con las registradas en la DB
	for($i=1;$i<=sizeof($paymentlist);$i++) {
		$pquery = mysqli_query($con, "SELECT SUM(net_ammount) FROM `cd_payments` WHERE `cd_account`=".$i." AND MONTH(`date`)=".date("n")." AND year(`date`)=".date("Y"));
		$sum = mysqli_fetch_row($pquery);
		if($config["cd".$i."_balance"] < ($sum[0] - 20) || $config["cd".$i."_balance"] > ($sum[0] + 20)) {
			file_put_contents("cuentadigital_error_log.txt", date("d/m/Y h:i:s")." - CD".$i.". Monto DB: ".$sum[0].", Monto log: ".$config["cd".$i."_balance"].". Log actualizado a monto DB.  \r\n", FILE_APPEND);	
			$config["cd".$i."_balance"] = $sum[0];
		}
	}
	
}

if($config["payments_last_revised"] == date("d-m-Y")) {
	$paymentsTable = $config["today_payments"]; 
	$namesTable = $config["today_payments_names"];
} else {
	$paymentsTable = ""; 
	$namesTable = "";
}



$afip = false;
try {
	$afipWs = new AfipWsfe();
	$afip = true;

} catch(Exception $e) {
	file_put_contents("afipWs_fails.txt", date("d/m/Y H:i:s")."\r\n".$e->getMessage()."\r\n\r\n\r\n", FILE_APPEND);
	echo $e->getMessage();
	
}


$cuit = array("1"=>"20396674182", "2"=>"20375378117");

for($e=1;$e<=sizeof($paymentlist);$e++) {

	$payments = explode("\n",$paymentlist[$e]);

	if(sizeof($payments) == 0) continue;
	else if(sizeof($payments) == 1) {
		if($payments[0] == "") continue;	
	}
	
	
	if($afip && $e != 3) {
		$afip_query = mysqli_query($con, "SELECT COALESCE(SUM(`factura_total`), 0) FROM `facturas` WHERE  month(`factura_fecha`) = ".date("m")." AND year(`factura_fecha`) = ".date("Y")." AND `factura_cuit`='".$cuit[$e]."'");
		$result = mysqli_fetch_row($afip_query);
		$facturado[$e] = $result[0];
	}
	
	$payments = array_reverse(array_filter($payments));
	
	$query = mysqli_query($con, "SELECT COUNT(*) FROM `cd_payments` WHERE `cd_account`=".$e." AND `date`='".date("Y-m-d")."'");
	$payment_number = mysqli_fetch_row($query); // Número de pago en la cuenta $e del día de la fecha

	$count = 0;
	for($i=$payment_number[0];$i<sizeof($payments);$i++) {
		
		$payment_info = explode("|", $payments[$i]);
		if(sizeof($payment_info) == 6) {
			if($payment_info[0] == date("dmY")) {
				
				$count += 1;
				$price_warning = 0;
				$pedido_facturado = false;
				
				if(preg_match("#^ID-([JP][0-9]{5,6})-.*USD-(.*)ARS$#", $payment_info[4], $matches)) {  // Identificar si pertenece al sitio o no 

					$query2 = mysqli_query($con, "SELECT * FROM `orders` WHERE `order_purchaseticket` LIKE '%".$payment_info[3]."' AND `order_id`='".$matches[1]."'");
					if(mysqli_num_rows($query2) == 1) {
						
						$pData = mysqli_fetch_assoc($query2);
						$log_product_name = $pData["product_name"];
						if($pData["order_confirmed_payment"] == 0) {
							
							
							// Actualizar pedido marcar como 'acreditado'
							mysqli_query($con, "UPDATE `orders` SET `order_confirmed_payment`=1 WHERE `order_id`='".$pData["order_id"]."'");
							$log_recipient = $matches[1];
							
							
							// Facturación
							if($afip && $e != 3) {
								if($pData["order_type"] == 1 && $pData["product_arsprice"] < 800 && ($facturado[$e] + $pData["product_arsprice"]) < 31500) {
									$monto_fact = round($pData["product_arsprice"], 2);
									if($cbte = $afipWs->generarCbte($e, 11, $monto_fact)) {
										$facturado[$e] += $monto_fact;
										$productos = ($pData["product_fromcatalog"] ? $pData["product_id_catalog"] : "0")."|".mysqli_real_escape_string($con, $pData["product_name"])."|1|".$monto_fact; // cod1|producto1|cantidad1|precio1,cod2|producto2|cantidad2|monto2|etc
										$sql_factura = "INSERT INTO `facturas` (`factura_id`,`factura_cuit`,`factura_iibb`,`factura_ptovta`,`factura_nro`,`factura_fecha`,`factura_cae`,`factura_vtocae`,`factura_receptor`,`factura_productos`,`factura_total`,`factura_pedidoasoc`)
										VALUES (NULL, '".$afipWs->CUIT[$e]."', '".$afipWs->IIBB[$e]."', 2, ".$cbte["nro"].", '".date("Y-m-d",strtotime($cbte["fecha"]))."', '".$cbte["CAE"]."', 
										'".date("Y-m-d",strtotime($cbte["vtoCAE"]))."', '".$pData["buyer_name"]."', '".$productos."', ".$monto_fact.", '".$pData["order_id"]."')";
										mysqli_query($con, $sql_factura);
										$pedido_facturado = true;
									} else {
										file_put_contents("afipWs_fails.txt", date("d/m/Y H:i:s")."\r\n".$afipWs->error_text."\r\n\r\n\r\n", FILE_APPEND);	
									}
								}
							}
							
						} else $log_recipient = $matches[1]." (repetido)";
					} else {
						$log_product_name = "&nbsp;";
						$log_recipient = $matches[1]." (no se encuentra el pedido)";
					}
					
					$orig_ammount = floatval($matches[2]);
					$paid_ammount = floatval($payment_info[1]);	
					$price_warning = ($paid_ammount < ($orig_ammount - 3) || $paid_ammount > ($orig_ammount + 3)) ? 1 : 0;
					$sql = "INSERT INTO `cd_payments` (`number`, `cd_account`, `date`, `net_ammount`, `invoice_number`, `site_payment`, `order_id`, `description`, `price_warning`) 
					VALUES (NULL, ".$e.", NOW(), ".floatval($payment_info[2]).", '".$payment_info[3]."', 1, '".$matches[1]."', '', ".$price_warning.")";
				} else {
					$log_product_name = "&nbsp;";
					$log_recipient = $payment_info[4];

					$sql = "INSERT INTO `cd_payments` (`number`, `cd_account`, `date`, `net_ammount`, `invoice_number`, `site_payment`, `order_id`, `description`, `price_warning`) 
					VALUES (NULL, ".$e.", NOW(), ".floatval($payment_info[2]).", '".$payment_info[3]."', 0, '', '".$payment_info[4]."', 0)";
				}
				
				mysqli_query($con, $sql);
				$config["cd".$e."_balance"] += floatval($payment_info[2]);

				$namesTable .= '<tr><td><span style="font-size:15px;">'.$log_product_name.($price_warning ? " [Revisar precio!]" : "").'</span></td></tr>';
				$paymentsTable .= '<tr><td><span style="font-size:15px;">'.$log_recipient.'</span></td><td><span style="font-size:15px;text-align:center;">'.date("d/m/Y").'</span></td><td><span style="font-size:15px;text-align:center;">'.$e.'</span></td><td><span style="font-size:15px;text-align:center;">'.($pedido_facturado ? "x" : "&nbsp;").'&nbsp;</span></td><td><span style="background-color:#DAEEF3;font-size:15px;text-align:center;">'.str_replace(".",",",floatval($payment_info[2])).'</span></td></tr>';
			}
		}
	}
	if($count > 0) echo "Registrados ".$count." pagos nuevos de la cuenta ".$e."<br/>";
	file_put_contents("facturado.txt", date("d/m/Y H:i:s")." -- Cta ".$e." fact hasta el momento del mes: $".$facturado[$e]." \r\n\r\n", FILE_APPEND);	

}

//var_dump($facturado);

$config["today_payments"] = $paymentsTable;
$config["today_payments_names"] = $namesTable;
$config["payments_last_revised"] = date("d-m-Y");
saveConfig($config);

echo "--- Tabla de pagos de hoy hasta ahora ---<br/>
<div style='width:700px;'>
	<div style='float:left;width:320px;'><table>".$config["today_payments_names"]."</table></div>
	<div style='float:right;width:360px;'><table>".$config["today_payments"]."</table></div>
</div>
";


function saveConfig($config) {
	file_put_contents("../config.php", "<?php return ".var_export($config, true)."; ?>");
}

?>