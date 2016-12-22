<?php

// Si el producto: está activo, no es stock agotado, no es de steam con problema de actualización
$basic_product_filter = "product_enabled = 1 ".
						"AND NOT (product_has_limited_units = 1 AND product_limited_units = 0) ".
						"AND NOT (product_sellingsite = 1 AND product_update_error = 1)";




function display_catalog_product($pData, $size = "") {
	if($size == "sm") $size_class = "cpg-product-sm";
	else if($size == "lg") $size_class = "cpg-product-lg";
	else $size_class = "";
	?>
	<a href="juegos/<?php echo $pData["product_id"]; ?>/"><div class="cpg-product <?php echo $size_class; ?>">
		<div class="cpg-product-overlay"></div>
    	<div class="cpg-product-info">
        	<div class="cpg-product-name"><?php echo $pData["product_name"]; ?></div>
            <div class="cpg-product-drm"><img <?php
            if($pData["product_platform"] == 1) echo "src='global_design/img/icons/steam_22x22.png' alt='steam'";
			else if($pData["product_platform"] == 2) echo "src='global_design/img/icons/origin_22x22.png' alt='origin'";
			?>></div>
        </div>
        <div class="cpg-product-price">
			<?php
            if($pData["product_has_customprice"] == 1 && $pData["product_customprice_currency"] == "ars") {
                echo "<div class='cpg-lastprice'>&#36;".$pData["product_finalprice"].($size!="sm" ? " ARS" : "")."</div>";
            } else if(($pData["product_external_limited_offer"] == 0 && $pData["product_has_customprice"] == 0) || $pData["product_sellingsite"] == 4) {
                echo "<div class='cpg-lastprice'>&#36;".quickCalcGame(1,$pData["product_finalprice"]).($size!="sm" ? " ARS" : "")."</div>";
            } else if($pData["product_has_customprice"] == 1 || $pData["product_external_limited_offer"] == 1) {
                echo "<div class='cpg-firstprice'>&#36;".quickCalcGame(1,$pData["product_listprice"]).($size!="sm" ? " ARS" : "")."</div>
                <div class='cpg-lastprice'>&#36;".quickCalcGame(1,$pData["product_finalprice"]).($size!="sm" ? " ARS" : "")."</div>";	
            }
            ?>
		</div>
		<img class="cpg-product-img" src="data/img/game_imgs/<?php 
		if($size == "sm") echo "224x105/";
		else if($size == "lg") echo "320x149/";
		else echo "240x112/";
		echo $pData["product_mainpicture"]; ?>" alt="<?php echo $pData["product_name"]; ?>">
	</div></a>							
	<?php
}


?>