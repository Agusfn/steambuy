<?php
header("Content-Type: text/html; charset=UTF-8");

require_once("mysql_connection.php");

echo "<html><title>Ver lista de pagos</title></html>";

if(isset($_GET["date"])) {

	$date = mysqli_real_escape_string($con, $_GET["date"]);
	
	$sql = "SELECT * FROM `cd_payments` WHERE `date`='".$date."'";
	$query = mysqli_query($con, $sql);
	
	$results = mysqli_num_rows($query);
	if($results > 0) {
		
		
		$order_product_names = "";
		$order_payment_data = "";
		while($data = mysqli_fetch_assoc($query))
		{
			$query2 = mysqli_query($con, "SELECT `product_name` FROM `orders` WHERE `order_id`='".$data["order_id"]."'");
			if(mysqli_num_rows($query2) == 1) {
				$product_name = mysqli_fetch_row($query2);
				$order_product_names .= "<tr><td><span style='font-size:15px;'>".$product_name[0]."</span></td></tr>";
			} else $order_product_names .= "<tr><td><span style='font-size:15px;'>[no se encontro pedido]</span></td></tr>";

			$query3 = mysqli_query($con, "SELECT COUNT(*) FROM `facturas` WHERE `factura_pedidoasoc`='".$data["order_id"]."'");
			$facturado = mysqli_fetch_row($query3);

			$order_payment_data .= "<tr><td><span style='font-size:15px;'>".($data["order_id"]!="" ? $data["order_id"] : $data["description"])."</span></td><td><span style='font-size:15px;text-align:center;'>".date("d/m/Y",strtotime($data["date"]))."</span></td><td><span style='font-size:15px;text-align:center;'>".$data["cd_account"]."</span></td><td><span style='font-size:15px;text-align:center;'>".($facturado[0] == 1 ? "x" : "&nbsp;")."</span></td><td><span style='background-color:#DAEEF3;font-size:15px;text-align:center;'>".str_replace(".",",",$data["net_ammount"])."</span></td></tr>";

		}

		echo "Pagos totales del día ".$date." : ".$results;
		echo "
		<div style='width:1000px;'>
			<div style='float:left;margin-right:10px;'><table style='width:330px;'>".$order_product_names."</table></div>
			<div style='float:right;'><table>".$order_payment_data."</table></div>
		</div>";
	} else {
		echo "No hay pagos registrados del día ".$date;
	}
	
} else echo "Ingresa fecha";


?>