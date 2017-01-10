<?php 
header("Content-Type: text/html;charset=utf-8");

require_once("mysql_connection.php");


$res = mysqli_query($con, "SELECT `value` FROM `settings` WHERE `name` = 'autoupdate_dollar_value'");
$autoUpdate = mysqli_fetch_row($res);

if($autoUpdate[0] == 1) {
	
	$ch = curl_init("http://www.dolarcito.com.ar/?ver=banco&entidad=7");
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:17.0) Gecko/17.0 Firefox/17.0');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$htmlPage = curl_exec($ch);
	curl_close($ch);	
	
	$dom = new DOMDocument();
	@$dom->loadHTML($htmlPage);
	$xpath = new DOMXPath($dom);
	$nodes = $xpath->query('//td[@class="modeda"][2]');
	
	if($nodes->length > 0) {
		$cotiz = floatval(preg_replace("/[^0-9.]*/",'', $nodes->item(0)->textContent));
		if(is_numeric($cotiz)){
			$sql = "UPDATE `settings` SET `value` = '".$cotiz."' WHERE `name` = 'updated_dollar_value' ;";
			mysqli_query($con, "UPDATE `settings` SET `value` = 0 WHERE `name` = 'dollar_retrieve_attemps' ;");		
		}else{
			$sql = "UPDATE `settings` SET `value` = `value` + 1 WHERE `name` = 'dollar_retrieve_attemps' ;";		
		} 
	} else {
		$sql = "UPDATE `settings` SET `value` = `value` + 1 WHERE `name` = 'dollar_retrieve_attemps' ;";
	}
	mysqli_query($con, $sql);
}


$res2 = mysqli_query($con, "SELECT `product_tags` FROM `products` WHERE `product_enabled` = 1");

$wholetags = "";
while($tags = mysqli_fetch_assoc($res2)) {
	if($tags["product_tags"] != "") $wholetags .= $tags["product_tags"].",";
}

$tagarray = explode(",", $wholetags);
$frecuent_tags = array_count_values($tagarray);
arsort($frecuent_tags);
$keys=array_keys($frecuent_tags);

$sqltags = "";
for($i=0;$i<10;$i++) {
	if($i<9) $sqltags .= $keys[$i].",";
	else $sqltags .= $keys[$i];
}

$sqltags =  mb_strtolower($sqltags, 'UTF-8');
$sql = "UPDATE `settings` SET `value` = '".$sqltags."' WHERE `name` = 'frecuent_tags'";
mysqli_query($con, $sql);

/*var_dump($keys);
echo $wholetags."<br/><br/><br/>";
echo $wholetags."<br/><br/>".$sqltags."<br/><br/>".$sql;*/



if(isset($_GET["redir"])) header("Location: " . $_GET["redir"]);


?>