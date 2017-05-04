<?php
date_default_timezone_set("America/Argentina/Buenos_Aires");


/*
Funcion usando api steam storefront.
region: BR, US, AR, RU, MX, etc
*/

function ssf_getpriceinfo($producturl, $region = "ar") {

	$result["error"] = 0; // Errores. 1=mal link, 2=error en la solicitud, 3=producto inexistente/otro
	
	if(preg_match("#^(https?://)?store\.steampowered\.com/(sub|app)/([0-9]{1,10})(/.*)?$#", $producturl, $matches) && is_numeric($matches[3])) {
		$steamid = $matches[3];
		$type = $matches[2];

		$response = @file_get_contents("http://store.steampowered.com/api/".($type=="app" ? "appdetails/?appids=" : "packagedetails/?packageids=").$steamid."&cc=".$region."&l=spanish".($type=="app"?"&filters=price_overview":""));
		if($response != false) {
			$data = json_decode($response, true);
			//var_dump($data);
			if($data[$steamid]["success"] == true && isset($data[$steamid]["data"]["price".($type=="app"?"_overview":"")])) {
				$result["firstprice"] = roundfunc(intval($data[$steamid]["data"]["price".($type=="app"?"_overview":"")]["initial"]) / 100);
				$result["finalprice"] = roundfunc(intval($data[$steamid]["data"]["price".($type=="app"?"_overview":"")]["final"]) / 100);
				$result["currency"] = $data[$steamid]["data"]["price".($type=="app"?"_overview":"")]["currency"];
			} else $result["error"] = 3;
		} else $result["error"] = 2;
	} else $result["error"] = 1;
	return $result;
}



// API web scrapping (API 2)
class steamProduct {

	public $loadError; // 0=sin problemas, 1=la url no es correcta, 2=error cargando html
	public $product_url;
	public $product_type;
	public $product_id;
	public $product_htmlpage;
	public $xpath;
	
	public function __construct($producturl) {
		if(preg_match("#^(https?://)?store\.steampowered\.com/(sub|app)/([0-9]{1,10})(/.*)?$#", $producturl, $matches)) {
			$this->product_url = $producturl;
			$this->product_type = $matches[2];
			$this->product_id = $matches[3];
			$htmlPage = get_htmlpage("http://store.steampowered.com/".$this->product_type."/".$this->product_id."/?cc=ar&l=spanish");
			$this->product_htmlpage = $htmlPage;
			if($htmlPage != false) {
				$dom = new DOMDocument();
				@$dom->loadHTML($htmlPage);
				$this->xpath = new DOMXPath($dom);
			} else $this->loadError = 2;
		} else $this->loadError = 1;
	}
	
	/*public function getAllInfo() {
		
	}*/
	
	public function getName() {
		$titleNodes = $this->xpath->query("//div[@class='apphub_AppName']");
		if($titleNodes->length == 1) {
			return trim($titleNodes->item(0)->textContent);	
		} else {
			$titleNodes = $this->xpath->query("//h2[@class='pageheader']");
			if($titleNodes->length == 1) {
				$name = trim(utf8_decode($titleNodes->item(0)->textContent));
				if($name == "Ups... ¡Perdón!") return false;
				return $name;
			} else return false;
		}
	}
	
	public function saveHeaderImg($raw_gamename) {
		$imageLocation = "http://cdn.akamai.steamstatic.com/steam/".$this->product_type."s/".$this->product_id."/header".($this->product_type == "sub" ? "_ratio" : "").".jpg";
		$fileName = sanitize($raw_gamename).".jpg";
		$finalImgPath = ROOT_LEVEL."data/img/game_imgs/".$fileName;
		$finalSmImgPath = ROOT_LEVEL."data/img/game_imgs/small/".$fileName;

		if(checkRemoteFile($imageLocation)) {
			if(!file_put_contents($finalImgPath, file_get_contents($imageLocation))) return false;
		} else if($this->product_type == "sub") { // Si no se puede guardar la imagen de header en un pack, debe ser que no existe, se achica y guarda la larga
			$subImgLoc = "http://cdn.akamai.steamstatic.com/steam/subs/".$this->product_id."/header.jpg";
			if(!checkRemoteFile($subImgLoc)) return false;
			list($width, $height) = getimagesize($subImgLoc);
			$wide_img = imagecreatefromjpeg($subImgLoc);
			$cropped = imagecreatetruecolor(460, 215);
			imagecopyresampled($cropped, $wide_img, 0, 0, 0, 0, 460, 215, $width, $height);
			if(!imagejpeg($cropped, $finalImgPath, 98)) return false;
		} else return false;
		
		save_game_image_rescaled($finalImgPath, ROOT_LEVEL."data/img/game_imgs/small/".$fileName, 198, 93);
		save_game_image_rescaled($finalImgPath, ROOT_LEVEL."data/img/game_imgs/224x105/".$fileName, 224, 105);
		save_game_image_rescaled($finalImgPath, ROOT_LEVEL."data/img/game_imgs/240x112/".$fileName, 240, 112);
		save_game_image_rescaled($finalImgPath, ROOT_LEVEL."data/img/game_imgs/320x149/".$fileName, 320, 149);
		
		return $fileName;
	}
	
	public function getDescription() {
		if($this->product_type == "app") {
			$descriptionNodes = $this->xpath->query("//div[@id='game_area_description']");
			if($descriptionNodes->length == 1) {
				$gameDescription = $descriptionNodes->item(0)->ownerDocument->saveHTML($descriptionNodes->item(0));
				return preg_replace("/<h2>.*<\/h2>/", "", $gameDescription);
			} else return false;
		} else if($this->product_type == "sub") {		
			$packageContentNodes = $this->xpath->query("//div[@class='leftcol game_description_column']/div[contains(@class, 'tab_item')]//div[@class='tab_item_name']");	
			$packageContentUrlsNodes = $this->xpath->query("//div[@class='leftcol game_description_column']/div[contains(@class, 'tab_item')]/a/@href");	
			if((intval($packageContentNodes->length) == intval($packageContentUrlsNodes->length)) && intval($packageContentNodes->length) > 0) {	
				$gameDescription = "<h4>Este pack contiene los siguientes juegos o DLCs:</h4>\n<ul>";	
				for($i=0;$i<$packageContentNodes->length;$i++) {
					$gameDescription .= "<li><a href='".$packageContentUrlsNodes->item($i)->textContent."' target='_blank'>".$packageContentNodes->item($i)->textContent."</a></li>";	
				}	
				$gameDescription .= "</ul>";
				return $gameDescription;
			} else return false;
		}
	}
	
	public function getPriceInfo($round = false) {
		$result = array("error"=>0); 
		/* Errrores: 0=sin error, 1=se modificó la estructura HTML, 2= juego free to play
		Devuelve: product_discount, product_firstprice, product_finalprice, product_discount_endtime */
		
		$nodes = $this->xpath->query("(//div[@class='game_area_purchase_game'])[1]//div[@class='discount_prices']/div"); // En oferta
		
		if($nodes->length == 2) {
			$result["product_discount"] = 1;		
			$result["product_firstprice"] = floatval(preg_replace("/[^0-9.]/", "", $nodes->item(0)->textContent));
			$result["product_finalprice"] = floatval(preg_replace("/[^0-9.]/", "", $nodes->item(1)->textContent));
			if($round) {
				$result["product_firstprice"] = roundfunc($result["product_firstprice"]);
				$result["product_finalprice"] = roundfunc($result["product_finalprice"]);
			}
			if(preg_match("#InitDailyDealTimer\( [$]DiscountCountdown, ([0-9]{10})#", $this->product_htmlpage, $matches)) {
				$result["product_discount_endtime"] = date("Y-m-d H:i:s", $matches[1]);
			} else $result["product_discount_endtime"] = "n/a";
		} else if($nodes->length == 0) {
			$nodes2 = $this->xpath->query("(//div[@class='game_area_purchase_game'])[1]//div[@class='game_purchase_price price']"); // Sin oferta

			if($nodes2->length == 1) { 
				$result["product_discount"] = 0;
				$result["product_finalprice"] = floatval(preg_replace("/[^0-9.]/", "", $nodes2->item(0)->textContent));
				if($round) $result["product_finalprice"] = roundfunc($result["product_finalprice"]);
				$result["product_firstprice"] = $result["product_finalprice"];
				if($result["product_finalprice"] == 0) $result["error"] = 2;	
			} else $result["error"] = 1;
		} else $result["error"] = 1;
		return $result;
	}
	
	public function getScreenshots($max_ammount) {
		if($this->product_type != "app") return false;
		$screenshotNodes = $this->xpath->query("//div[@id='highlight_strip_scroll']//div[@class='highlight_strip_item highlight_strip_screenshot']/img/@src");
		$links = "";
		if($screenshotNodes->length >= $max_ammount) {
			$a = randomGen(0, $screenshotNodes->length - 1, $max_ammount);
			for($i=0;$i<$screenshotNodes->length;$i++) {
				if(in_array($i,$a)) { 
					$links .= preg_replace("/116x65.*/", "", $screenshotNodes->item($i)->nodeValue).";";
				}
			}	
		} else if($screenshotNodes->length > 0)  { 
			for($i=0;$i<$screenshotNodes->length;$i++) {
				$links .= preg_replace("/116x65.*/", "", $screenshotNodes->item($i)->nodeValue).";";
			}	
		} else return false;
		return substr($links, 0, strlen($links)-1); // Devuelve las imágenes en CSV para añadir a la DB (pic1;pic2;pic3..)
	}
	
	public function getTags($max_ammount) {
		if($this->product_type != "app") return false;
		$tagNodes = $this->xpath->query("//div[@class='glance_tags popular_tags']/a[@class='app_tag']");
		$tags = "";
		if($tagNodes->length >= $max_ammount) {
			for($i=0;$i<$max_ammount;$i++) {
				$tags .= trim($tagNodes->item($i)->textContent).",";	
			}
		} else if($tagNodes->length > 0) {
			foreach($tagNodes as $node) {
				$tags .= trim($node->textContent).",";	
			}
		}
		return substr($tags, 0, strlen($tags)-1); // Devuelve los tags en CSV
	}
	
	
	
}


function save_game_image_rescaled($orig_path, $new_path, $new_width, $new_height) {
	if(file_exists($orig_path)) {
		$smallImg = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($smallImg, imagecreatefromjpeg($orig_path), 0, 0, 0, 0, $new_width, $new_height, 460, 215); // TODAS LAS IMG TIENEN 460x215
		return imagejpeg($smallImg, $new_path, 98);
	} else return false;
}


function roundfunc($x){
  return round($x * 2, 1) / 2;
}


function randomGen($min, $max, $quantity) {
    $numbers = range($min, $max);
    shuffle($numbers);
    return array_slice($numbers, 0, $quantity);
}


function get_htmlpage($url)
{
	$ch = curl_init();
	$timeout = 15;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
	curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
	curl_setopt($ch, CURLOPT_COOKIE, "birthtime=652950001;lastagecheckage=1-January-1900;mature_content=1");
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
	
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}


function startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle) {
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

function sanitize($string) {
	$match = array("/\s+/","/[^a-zA-Z0-9\-]/","/-+/","/^-+/","/-+$/");
	$replace = array("-","","-","","");
	$string = preg_replace($match,$replace, $string);
	$string = strtolower($string);
	return $string;
}


function checkRemoteFile($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(curl_exec($ch)!==FALSE)  return true;
    else return false;
}

?>