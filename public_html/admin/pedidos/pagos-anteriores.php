<?php
require_once "../../../config.php";
require_once ROOT."app/lib/user-page-preload.php";

$login->restricted_page($loggedUser, 3, true);







$title = "Ver pagos - SteamBuy";
ob_start();

?>
	<ol class="breadcrumb">
		<li><a href="../">Panel admin</a></li>
		<li class="active">Ver pagos anteriores</li>
	</ol>
	<?php
	if(isset($_POST["date"])) {
	
		$date = mysqli_real_escape_string($con, $_POST["date"]);
		
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
	
			echo "Pagos totales del día ".$date." : ".$results."<br/><br/>";
			echo "
			<div class='clearfix' style='width:940px;'>
				<div style='float:left;margin-right:10px;'><table style='width:330px;'>".$order_product_names."</table></div>
				<div style='float:right;'><table>".$order_payment_data."</table></div>
			</div>";
		} else {
			echo "No hay pagos registrados del día ".$date;
		}
		
	}
	?>
	<form method="post" action="" style="width:320px;margin:0 auto;">
		<div>
			Fecha (AAAA-MM-DD):<br/>
			<input class="form-control" type="text" name="date" />
		</div>
		<input type="submit" class="btn btn-primary" value="Ver pagos" style="margin: 10px auto;display:block;" />
	</form>


<?php
$content = ob_get_clean();	



$template = new DisplayTemplate;
$template->insert_content($content, $title);
$template->display_rendered_html();


?>