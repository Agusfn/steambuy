<?php

require_once("../global_scripts/php/steam_product_fetch.php");

if(isset($_POST["link"])) {
	$info = ssf_getpriceinfo($_POST["link"], "br");
	print_r($info);
}




?>

<html>
<body>

<form action="" method="post">
Link:
<input type="text" name="link" />
<input type="submit" />
</form>

</body>
</html>