<?php

date_default_timezone_set("America/Argentina/Buenos_Aires");
header("Content-Type: text/html; charset=UTF-8");

define("ROOT_LEVEL", "../");

require_once("../global_scripts/php/steam_product_fetch.php");

?>
<form action="" method="post">
<input type="text" name="steam_url" />
<input type="submit" />
</form>
<?php

if(isset($_POST["steam_url"])) 
{
	$product = new steamProduct($_POST["steam_url"]);
	if($product->loadError == 0) {
		//$product->saveHeaderImg($product->getName());
        echo "<strong>Nombre:</strong>".$product->getName()."<br/>";
        echo "<strong>Descripciópn:</strong><br/><br/>------<br/><textarea>".$product->getDescription()."</textarea><br/>------<br/><br/>";
        echo "<strong>Price info:</strong>";
		print_r($product->getPriceInfo());
		echo "<br/>";
        echo "<strong>Screenshots CSV:</strong>".$product->getScreenshots(5)."<br/>";
        echo "<strong>Tags CSV:</strong>".$product->getTags(10)."<br/>";
		
	}
}

?>