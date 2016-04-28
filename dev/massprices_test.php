<form action="" method="post">
<textarea name="url"></textarea>
<input type="submit" />
</form>

<?php


if(isset($_POST["url"])) {
	
	$urls = preg_split("/\\r\\n|\\r|\\n/", $_POST['url']);
	
	
	foreach($urls as $url) 
	{
		
		$htmlPage = get_data($url);
	
		//------- Analizar documento HTML -------//
		
		$dom = new DOMDocument();
		@$dom->loadHTML($htmlPage);
		$xpath = new DOMXPath($dom);
		$nodes = $xpath->query("(//div[@class='game_area_purchase_game'])[1]//div[@class='discount_prices']/div");
		
		$status = 0; // Status: 0 = error, 1 = sin oferta, 2 = en oferta
		// Los nodos en este nivel representan los dos precios si está en oferta: el de lista y el de oferta.
		// 0 nodos = no está en oferta, 2 nodos = está en oferta
		if($nodes->length == 2) { // Precios obtenidos en oferta
			$status = 2;
			foreach ($nodes as $node) {
				$offerPrices[] = $node->textContent;		
			} 	
		} else if($nodes->length == 0) { // No se obtuvieron precios en oferta, busca precio sin oferta.
			$nodes2 = $xpath->query("(//div[@class='game_area_purchase_game'])[1]//div[contains(concat(' ', @class, ' '), ' game_purchase_price ')]");
			if($nodes2->length == 1) { // Precio sin oferta encontrado
				$status = 1;
				foreach ($nodes2 as $node) {
					$listPrice = $node->textContent;	
				} 	
			} else if($nodes2->length == 0) { // No se pudo obtener el precio del juego	
				$status = 0;
			}
		}
		
		if($status == 1) {
			$gameListPrice = preg_replace("/[^0-9.]/", "", $listPrice);
			echo $gameListPrice . "-----" . $url . "<br/>";
		} else if($status == 2) {
			$gameOfferListPrice = preg_replace("/[^0-9.]/", "", $offerPrices[0]);
			$gameOfferPrice = preg_replace("/[^0-9.]/", "", $offerPrices[1]);
			echo "lista:".$gameOfferListPrice.",oferta:".$gameOfferPrice."-----".$url."<br/>";
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
