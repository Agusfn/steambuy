<?php
/*
Página de 2do paso de compra de productos.

datos por post: 
product_id: ID de producto a comprar. 
payment_method: medio de pago (1: boleta, 2: transf)
coupon_code: cupon de descuento (opcional)

*/

session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");
$config = include("../global_scripts/config.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


// Obtenemos el ID del producto y medio de pago

if(isset($_POST["product_id"]) && isset($_POST["payment_method"])) {
	$product_id = $_POST["product_id"];
	$payment_method = $_POST["payment_method"];
} else {
	header("Location: ../");
	exit;
}




// Analizamos existencia y validez del producto.


$purchase = new Purchase($con);

if($product_exists = $purchase->checkProductPurchasable($product_id)) {
	
	$productData = $purchase->productData;
	if($productArsPrices = $purchase->calcProductFinalArsPrices()) {
		
	
		if($payment_method == 2) {
			$transferDiscount = $productArsPrices["ticket_price"] - $productArsPrices["transfer_price"];
		}
		
		$priceChangeWarning = false;
		if(isset($_SESSION["ticket_price"])) {
			if($_SESSION["ticket_price"] != $productArsPrices["ticket_price"]) $priceChangeWarning = true;
		}
		
		// Revisamos si hay un cupón de descuento y si es válido
		$couponSent = false;
		$validCoupon = false;
		if(isset($_POST["coupon_code"])) {
			$couponSent = true;
			if($validCoupon = $purchase->checkCouponValidity($_POST["coupon_code"])) {
				if($payment_method == 1) {
					$couponDiscount = round($productArsPrices["ticket_price"] * ($purchase->couponData["coupon_discount_percentage"]/100) ,2);
				} else if($payment_method == 2) {
					$couponDiscount = round($productArsPrices["transfer_price"] * ($purchase->couponData["coupon_discount_percentage"]/100) ,2);
				}
				
			}
		}		
		
		
	}
}
?>


<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />
        
        <title><?php
        if($product_exists) echo "Comprar ".$productData["product_name"]." - SteamBuy";	
		else echo "Error de compra - SteamBuy";	
		?></title>
        
        
        <link rel="shortcut icon" href="../favicon.ico">
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css" type="text/css">
        <link rel="stylesheet" href="design/purchase_pg.css" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js"></script>
		<script type="text/javascript" src="scripts/purchase_pg.js"></script>

    </head>
    