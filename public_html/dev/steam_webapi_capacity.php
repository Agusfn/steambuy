<?php

for($i=0;$i<1000;$i++) {
	echo "<b>".$i."</b><br/>";
	echo file_get_contents("http://store.steampowered.com/api/appdetails/?appids=10&cc=ar&filters=price_overview");
	echo "<br/><b>----</b><br/><br/>";
}



?>