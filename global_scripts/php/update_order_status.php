<?php
require_once("mysql_connection.php");

if(!$con) exit;


$paymentlist[1] = file_get_contents("https://www.cuentadigital.com/exportacion.php?control=59905e7725bfaedb84d1e55f210c962c"); // Cuenta RFN07
$paymentlist[2] = file_get_contents("https://www.cuentadigital.com/exportacion.php?control=ef67b67798e79b6ebd0250074755b12d"); // Cuenta AGUSFN


$rows = "";
$namesTableRows = "";

$totalPayments = 0;
$sitePayments = 0;

for($e=1;$e<=sizeof($paymentlist);$e++) {
	$payments = explode("\n",$paymentlist[$e]);

	if(sizeof($payments) == 1) {
		if($payments[0] == "") continue;	
	}
	
	$totalPayments += (sizeof($payments) - 1);
	
	for($i=sizeof($payments)-1;$i>=0;$i--) {
		if($payments[$i] != "") {
			$paymentinfo = explode("|",$payments[$i]);
			//var_dump($paymentinfo);
			if(sizeof($paymentinfo) == 6) {
				if(substr($paymentinfo[4], 0, 2) == "ID" && $paymentinfo[0] == date("dmY")) {
					$infoSplit = explode("-",$paymentinfo[4]);
					$orderid = $infoSplit[1]; 
					$res = mysqli_query($con, "SELECT `product_name` FROM `orders` WHERE `order_purchaseticket` LIKE '%".mysqli_real_escape_string($con, $paymentinfo[3])."%' AND 
					`order_id` = '".mysqli_real_escape_string($con, $orderid)."' AND `order_confirmed_payment` = 0");
					if(mysqli_num_rows($res) == 1) {
						$pName = mysqli_fetch_row($res);
						$sitePayments += 1;
						echo $orderid." ok<br/>";
						mysqli_query($con, "UPDATE `orders` SET `order_confirmed_payment` = 1 WHERE `order_id`='".mysqli_real_escape_string($con, $orderid)."'");
						$rows .= '<tr><td><span style="font-size:15px;">'.$orderid.'</span></td><td><span style="font-size:15px;text-align:center;">'.date("d/m/Y").'</span></td><td><span style="background-color:#DAEEF3;font-size:15px;text-align:center;">'.str_replace(".",",",$paymentinfo[2]).'</span></td></tr>';
						$namesTableRows .= "<tr><td><span style='font-size:15px;'>".$pName[0]."</a></td></tr>";
					}
				}
			}
		}
	}
}

mysqli_query($con, "UPDATE `settings` SET `value` = ".$totalPayments." WHERE `name`='today_totalpayments'");


if($rows != "") {
	
	$res1 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'payments_last_revised'");
	$date = mysqli_fetch_row($res1);
	
	if($date[0] == date("d/m/Y")) {
		
		$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'today_payments'");
		$current_table = mysqli_fetch_row($res);
		$res2 = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'today_payments_names'");
		$current_names_table = mysqli_fetch_row($res2);
		
		$updated_table = substr($current_table[0], 0, strlen($current_table[0]) - 8).$rows."</table>";
		$updated_names_table = substr($current_names_table[0], 0, strlen($current_names_table[0]) - 8).$namesTableRows."</table>";
		
		echo "
		<div style='width: 500px;margin-top: 14px;'>
			<div style='float:left;'>".$updated_names_table."</div>
			<div style='float:right;'>".$updated_table."</div>
		</div>";
		

		mysqli_query($con, "UPDATE `settings` SET `value` = '".mysqli_real_escape_string($con, $updated_table)."' WHERE `name` = 'today_payments'");
		mysqli_query($con, "UPDATE `settings` SET `value` = '".mysqli_real_escape_string($con, $updated_names_table)."' WHERE `name` = 'today_payments_names'");
		
		mysqli_query($con, "UPDATE `settings` SET `value` = `value`+".$sitePayments." WHERE `name`='today_sitepayments'");
	
	} else {
		
		$table = "<table>".$rows."</table>";
		$names_table = "<table>".$namesTableRows."</table>";
		
		mysqli_query($con, "UPDATE `settings` SET `value` = '".mysqli_real_escape_string($con, $table)."' WHERE `name` = 'today_payments'");
		mysqli_query($con, "UPDATE `settings` SET `value` = '".mysqli_real_escape_string($con, $names_table)."' WHERE `name` = 'today_payments_names'");
		
		//echo $table."<br/><pre>".htmlentities($sql)."</pre>";
		
		mysqli_query($con, "UPDATE `settings` SET `value` = '".date("d/m/Y")."' WHERE `name` = 'payments_last_revised'");
		
		mysqli_query($con, "UPDATE `settings` SET `value` = ".$sitePayments." WHERE `name`='today_sitepayments'");
	}	
	
}



// Añadir cancelador de pedidos cuando se verifique que el código de arriba funciona bien


?>