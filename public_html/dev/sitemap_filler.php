<?php

require_once("../global_scripts/php/mysql_connection.php");


$res = mysqli_query($con, "SELECT * FROM products ORDER BY product_id ASC");

$doc = new DOMDocument();
$doc->load("../sitemap.xml");
$urlset = $doc->documentElement;
//$attr = $doc->createAttribute("xmlns:image");
//$attr->nodeValue = "http://www.google.com/schemas/sitemap-image/1.1";
//$urlset->setAttributeNode($attr);


while($pData = mysqli_fetch_assoc($res)) {
	

	$url = $doc->createElement("url");
	$loc = $doc->createElement("loc", "http://steambuy.com.ar/juegos/".$pData["product_id"]."/");
	$changefreq = $doc->createElement("changefreq","monthly");
	$priority = $doc->createElement("priority","0.8");
	$image = $doc->createElement("image:image");
	$imageloc = $doc->createElement("image:loc", "http://steambuy.com.ar/data/img/game_imgs/small/".$pData["product_mainpicture"]);
	
	$urlset->appendChild($url);
	$url->appendChild($loc);
	$url->appendChild($changefreq);
	$url->appendChild($priority);
	$url->appendChild($image);
	$image->appendChild($imageloc);
	
	
}

file_put_contents("../sitemap.xml", $doc->saveXML());


?>