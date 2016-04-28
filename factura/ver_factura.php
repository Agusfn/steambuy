<?php
header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");



$error = 0; // 0= Todo OK, 1= No se indicó ni order ni pass, 2=combinacion de id y pass incorrecta, 3=pedido no concretado, 4=pedido previo a la fecha de inicio de fact, 5=no se encontró la factura


if(isset($_POST["order_id"]) && isset($_POST["order_pass"])) {
	$sql = "SELECT count(*) AS num, `order_status`, `order_status_change` FROM `orders` WHERE `order_id`='".mysqli_real_escape_string($con, $_POST["order_id"])."' AND `order_password`='".mysqli_real_escape_string($con, $_POST["order_pass"])."'";
	$query = mysqli_query($con, $sql);
	$data = mysqli_fetch_assoc($query);
	if($data["num"] == 1) {
		if($data["order_status"] == 2) {
			if(strtotime($data["order_status_change"]) > strtotime("2015-08-01 00:00:00")) {
				$sql2 = "SELECT * FROM `facturas` WHERE `factura_pedidoasoc` = '".mysqli_real_escape_string($con, $_POST["order_id"])."'";
				$query2 = mysqli_query($con, $sql2);
				if(mysqli_num_rows($query2) == 1) {
					$fData = mysqli_fetch_assoc($query2);
				} else $error = 5;
			} else $error = 4;
		} else $error = 3;
	} else $error = 2;
} else $error = 1;

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="robots" content="noindex, nofollow" />
    
    <title>Factura - SteamBuy</title>
    <link rel="stylesheet" href="design/invoice_design.css" />
    <link rel="stylesheet" type="text/css" href="design/print.css" media="print" />
    
    <link rel="shortcut icon" href="../favicon.ico?2"> 
</head>

<body>
	<?php
    if($error == 1) {
		echo "<div class='alert'>No se proporcionó la ID y la clave de pedido, no se envió el formulario como corresponde.</div>";
	} else if($error == 2) {
		echo "<div class='alert'>La combinación de ID y clave de pedido es incorrecta.</div>";
	} else if($error == 3) {
		echo "<div class='alert'>El pago del pedido no se ha acreditado o el pedido no ha sido enviado aún.</div>";
	} else if($error == 4) {
		echo "<div class='alert'>El pedido se acreditó en una fecha anterior a la fecha de inicios de facturación web.</div>";
	} else if($error == 5) {
		sleep(7);
		echo "<div class='alert'>Ocurrió un error de comunicación con el servidor. SOAP Request Timed Out servicios1.afip.gov.ar.</div>";
	} else if($error == 0) {
		sleep(3);
		?>
        <div class='print'><button onclick='window.print();'>Imprimir factura</button></div>
        <div class="wrapper">
            <div class="invoice_head">
                <div class="invoice_head_left">
                    <img src="design/logo-complete.png" alt="SteamBuy" class="logo"/>
                    <div style="margin: 22px 0px 0px 25px;">
                        <strong>CUIT:</strong> <?php echo $fData["factura_cuit"]; ?><br/>
                        <strong>Ingresos Brutos: </strong> <?php echo $fData["factura_iibb"]; ?><br/>
                        <strong>Cond. frente al IVA:</strong> Responsable monotributo
                    </div>
                </div>
                    
                <div class="invoice_head_right">
                    <div class="invoice_type">FACTURA C</div>
                    <div style="margin-top:30px;">
                        <div><strong>Pto. de venta:</strong> <?php echo sprintf("%04d", $fData["factura_ptovta"]); ?> <span style="margin-left:20px;"><strong>Nro. de cbte:</strong> <?php echo sprintf("%09d", $fData["factura_nro"]); ?></span></div>
                        <strong>Fecha de emisión:</strong> <?php echo date("d/m/Y", strtotime($fData["factura_fecha"])); ?><br/>
                        <strong>CAE:</strong> <?php echo $fData["factura_cae"]; ?><br/>
                        <strong>Fecha vto. CAE: </strong> <?php echo date("d/m/Y", strtotime($fData["factura_vtocae"])); ?>
                    </div>
                </div>    
            </div>
            <div class="invoice_buyer_info">
                <div style="text-align:center;margin-bottom:5px;">Datos del receptor:</div>
                <span><strong>Cond. frente al IVA: </strong>Consumidor final</span><span style="margin-left:25px;"><strong>Doc:</strong> -</span><span style="margin-left:25px;line-height: 1.39em;"><strong>Apellido y nombre:</strong> <?php echo $fData["factura_receptor"]; ?></span>
            </div>
            <div class="invoice_body">
                <table>
                	<thead><tr><th>Cód.</th><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
                    <tbody>
						<?php
                        $productos = explode(",", $fData["factura_productos"]);
                        foreach($productos as $producto) {
                            $datos = explode("|", $producto);
                            ?>
                            <tr>
                                <td><?php echo intval($datos[0]) > 0 ? $datos[0] : "n/a"; ?></td>
                                <td><?php echo $datos[1]; ?></td>
                                <td align="right"><?php echo $datos[2]."&nbsp;"; echo intval($datos[2]) > 1 ? "unidades" : "unidad"; ?></td>
                                <td align="right"><?php echo number_format(floatval($datos[3]), 2, ",", ""); ?></td>
                                <td align="right"><?php echo number_format(round($datos[2]*$datos[3],2), 2, ",", ""); ?></td>
                            </tr> 
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="invoice_footer">
                <div class="invoice_totals">
                    <div style="float:right;line-height: 1.6em;height:72px;width:300px; text-align:right">
                        <div style="float:left;">
                            <strong>Subtotal:</strong>&nbsp;&nbsp;$<br/>
                            <strong>Importe otros tributos:</strong>&nbsp;&nbsp;$<br/>
                            <strong>Importe total:</strong>&nbsp;&nbsp;$<br/>
                        </div>
                        <div style="float:right;">
                            <span><?php echo number_format($fData["factura_total"], 2, ",", ""); ?></span><br/>
                            <span>0</span><br/>
                            <span style="font-size:16px"><strong><?php echo number_format($fData["factura_total"], 2, ",", ""); ?></strong></span>
                        </div>
                    </div>
                </div>
                <div style="padding:13px 25px;height:75px;">
                    <div class="barcode_area">
                    	<?php
						$barcode = $fData["factura_cuit"]."11".sprintf("%04d", $fData["factura_ptovta"]).$fData["factura_cae"].date("Ymd",strtotime($fData["factura_vtocae"]));
						// Obtener dígito verificador
						$i=0;
						$sum1=0;
						while($i <= strlen($barcode) - 1) {
							$sum1 += intval(substr($barcode, $i, 1));
							$i+=2;
						}
						$i=1;
						$sum2=0;
						while($i <= strlen($barcode) - 1) {
							$sum2 += intval(substr($barcode, $i, 1));
							$i+=2;
						}
						$sum3 = ($sum1*3) + $sum2;
						$digit_verif = (10 - ($sum3 % 10));
						$barcode .= $digit_verif;
						?>
                        <img src="barcode.php?text=<?php echo $barcode; ?>&codetype=code25&size=50" alt="codigo de barras" />
                        <div><?php echo $barcode; ?></div>
                    </div>
                    <div style="float:right;margin: 11px 58px 0px 0px;">
                        <img src="design/logoafip.png" style="width:160px;" alt="afip" />
                        <div style="color: #555;font-weight: bold;font-style: italic;font-size:14px;">Comprobante autorizado</div>
                    </div>
                    
                </div>
            </div>
            
        </div>
        <?php
	}
    ?>

	

</body>
</html>
