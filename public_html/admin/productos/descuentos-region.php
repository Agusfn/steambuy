<?php
ini_set('max_execution_time', 600);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once("../../../config.php");

require_once(ROOT."app/lib/user-page-preload.php");

$login->restricted_page($loggedUser, 3, true);


require_once(ROOT."app/lib/purchase-functions.php");
require_once(ROOT."app/lib/steam-product-fetch.php");
require_once(ROOT."app/lib/mass-discounts.php");




$error = -1; // Error inicial:  -1: no se está procesando (no se envio form), 0: ok para proceder, 1: las alicuotas no son numericas 

if(isset($_POST["more32_profit"]) && isset($_POST["less32_profit"]) && isset($_POST["ignored_games"])) {
	
	$ignored_games = explode(",", $_POST["ignored_games"]);
	
	$start_from = $_POST["start_from"];
	$limit = $_POST["limit"];
	
	if(is_numeric($_POST["less32_profit"]) && is_numeric($_POST["more32_profit"]) && is_numeric($start_from) && is_numeric($limit)) {	 
		
		$alic_proces = array("menor_32_usd"=>$_POST["less32_profit"], "mayor_32_usd"=>$_POST["more32_profit"]);
		$mxbr_cotiz = obtener_cotiz_mxbr($con);
		
		if(isset($_POST["force_update"])) $force_update = true;
		else $force_update = false;
		
		if(isset($_POST["ignore_stock"])) $ignore_stock_games = true;
		else $ignore_stock_games = false;

		$error = 0; 
	} else $error = 1;
	
}


// Obtener alicuotas ganancia a juegos c/ dtos de region
$alicuotas = obtener_alicuotas_dto_region($con);

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Descuentos masivos</title>
</head>
    
<body>
	<?php
    if($error == 0) {
		reducir_precios($con, $mxbr_cotiz, $alic_proces, $ignored_games, $ignore_stock_games, $force_update, $start_from, $limit);
	} else if($error == 1) {
		echo "Los datos ingresados no son numericos.";
    } else {
        ?>
        Si comienza o finaliza una oferta de Steam, dejar que se refleje en el sitio primero antes de usar esta herramienta<br/><br/>
        <form action="" method="post">
        	Alicuota juegos &gt; 32 usd:<br/>
            <input type="text" name="more32_profit" value="<?php echo $alicuotas["mayor_32_usd"]; ?>" /><br/>
            Alicuota juegos &lt; 32 usd:<br/>
            <input type="text" name="less32_profit" value="<?php echo $alicuotas["menor_32_usd"]; ?>" /><br/>
            <input type="checkbox" name="force_update" />Forzar actualización de precio (si un juego tiene una oferta más baja que la sugerida, le pone el precio sugerido igual)<br/>
            <input type="checkbox" name="ignore_stock" />Ignorar juegos en oferta de Stock (se conservan)<br/><br/>
            Ignorar juegos (separar IDs con coma)<br/>
            <input type="text" name="ignored_games" value="38"/><br/>
            Arrancar desde el juego (a partir del mas rateado)<br/>
            <input type="text" name="start_from" value="0"/><br/>
            Límite cantidad de juegos (0: sin limite):<br/>
            <input type="text" name="limit" value="0"/><br/><br/>
            <input type="submit" value="Comenzar" />
        </form>
        <?php
    } ?>

</body>
</html>