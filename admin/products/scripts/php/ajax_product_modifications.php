<?php

if(!isset($_POST["action"])) return;

require_once("../../../../global_scripts/php/mysql_connection.php");

$result = 0;

if($_POST["action"] == "reorder" && isset($_POST["products_array"])) 
{
	$ordered_products = $_POST["products_array"];	
	for($i = 0; $i < count($ordered_products); $i++)
	{
		$productid = substr($ordered_products[$i],1);
		$sql = "UPDATE `products` SET `product_rating` = " . intval(count($ordered_products) - $i) . " WHERE product_id = " . $productid;
		if(mysqli_query($con, $sql)) $result = 1;
		else $result = 0;
	}
} else if($_POST["action"] == "get" && isset($_POST["product_id"])) {
	
	$sql = "SELECT * FROM `products` WHERE `product_id` = " . $_POST["product_id"];
	$res = mysqli_query($con, $sql);
	$product_data = mysqli_fetch_assoc($res);
	$result = json_encode($product_data);
	
} else if($_POST["action"] == "set" && isset($_POST["product_data"])) {
	
	$pdata = $_POST["product_data"];
	$sql = "UPDATE `products` SET `product_enabled` = ".$pdata["product_enabled"].", `product_update_error` = 0, `product_name` = '".mysqli_real_escape_string($con, $pdata["product_name"])."', 
	`product_platform` = ".$pdata["product_platform"].", `product_sellingsite` = ".$pdata["product_sellingsite"].", `product_site_url` = '".$pdata["product_site_url"]."', 
	`product_has_limited_units` = ".$pdata["product_has_limited_units"].", `product_limited_units` = ".$pdata["product_limited_units"].", 
	`product_has_customprice` = ".$pdata["product_has_customprice"].", `product_customprice_currency` = '".$pdata["product_customprice_currency"]."', 
	`product_external_limited_offer` = ".$pdata["product_external_limited_offer"].", `product_external_offer_endtime` = '".$pdata["product_external_offer_endtime"]."', 
	`product_listprice` = ".$pdata["product_listprice"].", `product_steam_discount_price`=".$pdata["product_steam_discount_price"].", `product_finalprice` = ".$pdata["product_finalprice"].", `product_mainpicture` = '".$pdata["product_mainpicture"]."', 
	`product_pics` = '".$pdata["product_pics"]."', `product_description` = '".mysqli_real_escape_string($con, $pdata["product_description"])."',
	`product_tags` = '".mysqli_real_escape_string($con, $pdata["product_tags"])."', `product_singleplayer` = ".$pdata["product_singleplayer"].", `product_multiplayer` = ".$pdata["product_multiplayer"].", 
	`product_cooperative` = ".$pdata["product_cooperative"]." WHERE `product_id` = ".$pdata["product_id"].";";
	if(mysqli_query($con, $sql)) $result = 1;
	else $result = mysql_error($con);	
	
	// Crear miniatura de imágen si no existe (cuando se añaden manualmente los juegos)
	if(!file_exists("../../../../data/img/game_imgs/small/".$pdata["product_mainpicture"]) && $pdata["product_mainpicture"] != "") {
		$mainImgPath = "../../../../data/img/game_imgs/".$pdata["product_mainpicture"];
		$miniaturaPath = "../../../../data/img/game_imgs/small/".$pdata["product_mainpicture"];
		$newSmallImg = imagecreatetruecolor(198, 93);
		imagecopyresampled($newSmallImg, imagecreatefromjpeg($mainImgPath), 0, 0, 0, 0, 198, 93, 460, 215);
		imagejpeg($newSmallImg, $miniaturaPath, 98);
	}
	
} else if($_POST["action"] == "insert" && isset($_POST["product_data"])) {
	
	$pdata = $_POST["product_data"];
	$sql = "INSERT INTO `products` (
	`product_id`, `product_enabled`, `product_update_error`, `product_name`, `product_rating`, `product_platform`, `product_sellingsite`, `product_site_url`, 
	`product_has_limited_units`, `product_limited_units`, `product_has_customprice`, `product_customprice_currency`, `product_external_limited_offer`, 
	`product_external_offer_endtime`, `product_listprice`, `product_steam_discount_price`, `product_finalprice`, `product_mainpicture`, `product_pics`, `product_description`, `product_tags`,
	`product_singleplayer`, `product_multiplayer`, `product_cooperative`) 
	VALUES (NULL, ".$pdata["product_enabled"].", 0, '".mysqli_real_escape_string($con, $pdata["product_name"])."', 0, ".$pdata["product_platform"].", 
	".$pdata["product_sellingsite"].", '".$pdata["product_site_url"]."', ".$pdata["product_has_limited_units"].", ".$pdata["product_limited_units"].", 
	".$pdata["product_has_customprice"].", '".$pdata["product_customprice_currency"]."', ".$pdata["product_external_limited_offer"].", 
	'".$pdata["product_external_offer_endtime"]."', ".$pdata["product_listprice"].", ".$pdata["product_steam_discount_price"].", ".$pdata["product_finalprice"].", '".$pdata["product_mainpicture"]."', 
	'".$pdata["product_pics"]."', '".mysqli_real_escape_string($con, $pdata["product_description"])."', '".mysqli_real_escape_string($con, $pdata["product_tags"])."',
	".$pdata["product_singleplayer"].", ".$pdata["product_multiplayer"].", ".$pdata["product_cooperative"].");";
	if(mysqli_query($con, $sql)) {
		$result = 1;
		

		// Crear miniatura de imágen si no existe (cuando se añaden manualmente los juegos)
		if(!file_exists("../../../../data/img/game_imgs/small/".$pdata["product_mainpicture"]) && $pdata["product_mainpicture"] != "") {
			$mainImgPath = "../../../../data/img/game_imgs/".$pdata["product_mainpicture"];
			$miniaturaPath = "../../../../data/img/game_imgs/small/".$pdata["product_mainpicture"];
			$newSmallImg = imagecreatetruecolor(198, 93);
			imagecopyresampled($newSmallImg, imagecreatefromjpeg($mainImgPath), 0, 0, 0, 0, 198, 93, 460, 215);
			imagejpeg($newSmallImg, $miniaturaPath, 98);
		}
		
		// Añadir url a sitemap
		$doc = new DOMDocument();
		$doc->load("../../../../sitemap.xml");
		$urlset = $doc->documentElement;
		
		$url = $doc->createElement("url");
		$loc = $doc->createElement("loc", "http://steambuy.com.ar/juegos/".mysqli_insert_id($con)."/");
		$changefreq = $doc->createElement("changefreq","monthly");
		$priority = $doc->createElement("priority","0.8");
		$image = $doc->createElement("image:image");
		$imageloc = $doc->createElement("image:loc", "http://steambuy.com.ar/data/img/game_imgs/small/".$pdata["product_mainpicture"]);
		
		$urlset->appendChild($url);
		$url->appendChild($loc);
		$url->appendChild($changefreq);
		$url->appendChild($priority);
		$url->appendChild($image);
		$image->appendChild($imageloc);
		
		file_put_contents("../../../../sitemap.xml", $doc->saveXML());
		
	} else $result = mysqli_error($con);
	
} else if ($_POST["action"] == "activate" && isset($_POST["product_id"])) {
	
	$sql = "UPDATE catalog_products SET product_enabled = 1 WHERE product_id = ".$_POST["product_id"];
	if(mysql_query($sql,$connection)) {
		$result = 1;
	}	
} else if($_POST["action"] == "delete" && isset($_POST["product_id"]) && isset($_POST["image"])) {
	
	$sql = "DELETE FROM `products` WHERE `product_id` = ".mysqli_real_escape_string($con,$_POST["product_id"]);

	$query2 = mysqli_query($con, "SELECT COUNT(*) FROM `products` WHERE `product_mainpicture`='".mysqli_real_escape_string($con,$_POST["image"])."' AND NOT `product_id`=".mysqli_real_escape_string($con,$_POST["product_id"])."");
	$count = mysqli_fetch_row($query2);
	if($count[0] == 0) {
		if(file_exists("../../../../data/img/game_imgs/".$_POST["image"])) {
			unlink("../../../../data/img/game_imgs/".$_POST["image"]);
		}
		if(file_exists("../../../../data/img/game_imgs/small/".$_POST["image"])) {
			unlink("../../../../data/img/game_imgs/small/".$_POST["image"]);
		}
	}
	
	if(mysqli_query($con, $sql)) $result = 1;
	else $result = mysqli_error($con);
}

echo $result;

?>