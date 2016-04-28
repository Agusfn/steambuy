
<form action="" method="post">
<input type="text" name="url" />
<input type="submit" />
</form>

<?php

if(isset($_POST["url"])) {
	

	$htmlPage = get_data($_POST["url"] . "?cc=ar");
	
	$AdaptedHtml = str_replace("<","&lt;",$htmlPage);
	echo "<textarea>" . $AdaptedHtml . "</textarea><br/>";

	$dom = new DOMDocument();
	@$dom->loadHTML($htmlPage);
	$xpath = new DOMXPath($dom);
		

	$nodes = $xpath->query("(//div[@class='game_area_purchase_game'])[1]//div[@class='discount_prices']/div");
	// Los nodos en este nivel representan los dos precios si está en oferta, el de lista y el de oferta.
	if($nodes->length == 2) {
		
		echo "En oferta <br/>";
		$gameOfferListPrice = preg_replace("/[^0-9.]/", "", $nodes->item(0)->textContent);
		$gameOfferPrice = preg_replace("/[^0-9.]/", "", $nodes->item(1)->textContent);
		echo $gameOfferListPrice . "," . $gameOfferPrice;
		
	} else if($nodes->length == 0) {

		$nodes2 = $xpath->query("(//div[@class='game_area_purchase_game'])[1]//div[contains(concat(' ', @class, ' '), ' game_purchase_price ')]");
		
		if($nodes2->length == 1) {
			echo "Sin oferta <br/>";
			
			$gameListPrice = preg_replace("/[^0-9.]/", "", $nodes2->item(0)->textContent);
			echo $gameListPrice;
		} else if($nodes2->length == 0) { // No se encontró el precio	
			echo "Error";
		}

	}
	
	

}

function get_data($url)
{
    $ch = curl_init();
    $timeout = 15;
    curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_PROXY, "190.105.178.164:80");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
	curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
	curl_setopt($ch, CURLOPT_COOKIE, "birthtime=652950001;");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 

    $data = curl_exec($ch);
    //var_dump(curl_getinfo($ch));
    curl_close($ch);
    return $data;
}

?>

