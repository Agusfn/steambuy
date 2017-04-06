<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/purchase-functions.php");

$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}

/*
Busqueda de juegos


Parámetros get:

order: orden (1: relevancia, 2: menor precio, 3: mayor precio)

int_stock: ofertas internas de stock
int_tmpo: ofertas internas de tiempo limitado
int_undef: ofertas internas indefinidas
oft_ext: ofertas externas
sin_oft: sin oferta

gm: game mode. (1: Singleplayer, 2: multiplayer, 3: coop)

pg: paginación
q: término de búsqueda
*/


if(isset($_GET["order"])) {
	if(is_numeric($_GET["order"]) && $_GET["order"] >= 1 && $_GET["order"] <= 3) {
		$filter_order = $_GET["order"];
	} else $filter_order = 1;
} else $filter_order = 1;


if(isset($_GET["int_stock"])) {
	if($_GET["int_stock"] == 1) $oferta_interna_stock = 1;
	else $oferta_interna_stock = 0;
} else $oferta_interna_stock = 1;

if(isset($_GET["int_tmpo"])) {
	if($_GET["int_tmpo"] == 1) $oferta_int_tiempo_lim = 1;
	else $oferta_int_tiempo_lim = 0;
} else $oferta_int_tiempo_lim = 1;

if(isset($_GET["int_undef"])) {
	if($_GET["int_undef"] == 1) $oferta_interna_indef = 1;
	else $oferta_interna_indef = 0;
} else $oferta_interna_indef = 1;

if(isset($_GET["oft_ext"])) {
	if($_GET["oft_ext"] == 1) $oferta_externa = 1;
	else $oferta_externa = 0;
} else $oferta_externa = 1;

if(isset($_GET["sin_oft"])) {
	if($_GET["sin_oft"] == 1) $sin_oferta = 1;
	else $sin_oferta = 0;
} else $sin_oferta = 1;



if(isset($_GET["gm"])) {
	if(is_numeric($_GET["gm"]) && $_GET["gm"] >= 0 && $_GET["gm"] <= 3) {
		$filter_gameMode = $_GET["gm"];
	} else $filter_gameMode = 0;
} else $filter_gameMode = 0;


if(isset($_GET["pg"])) {
	if(is_numeric($_GET["pg"])) {
		if(is_int($_GET["pg"] / 20)) {
			$current_page = $_GET["pg"];
		} else $current_page = 0;	
	} else $current_page = 0;
} else $current_page = 0;


$searching = 0; // 0 = no se busca, 1 = busqueda por nombre/tags/etc, 2 = busqueda por tags exclusivamente
if(isset($_GET["q"])) {
	if($_GET["q"] != "") $searching = 1;	
} else if(isset($_GET["tag"])) {
	if($_GET["tag"] != "") $searching = 2;	
}


/********** Armar query ************/
// 0, acción a realizar (obtener sólo cantidad, u obtener todos los datos)
$sql0a = "SELECT COUNT(*)";
$sql0b = "SELECT *";
			
// 1, estructura fundamental
$sql1 = " FROM `products` WHERE `product_enabled` = 1 AND NOT (`product_has_limited_units` = 1 AND `product_limited_units` = 0) AND `product_update_error` = 0";
				
// 2, filtros
$sql2 = "";
if($oferta_interna_stock == 0) $sql2 .= " AND NOT (`product_has_customprice` = 1 AND `product_has_limited_units` = 1)";
if($oferta_int_tiempo_lim == 0) $sql2 .= " AND NOT (`product_has_customprice` = 1 AND `product_external_limited_offer` = 1)";
if($oferta_interna_indef == 0) $sql2 .= " AND NOT (`product_has_customprice` = 1 AND `product_external_limited_offer` = 0 AND `product_has_limited_units` = 0)";
if($oferta_externa == 0) $sql2 .= " AND NOT (`product_has_customprice` = 0 AND `product_external_limited_offer` = 1)";
if($sin_oferta == 0) $sql2 .= " AND NOT (`product_has_customprice` = 0 AND `product_external_limited_offer` = 0)";
				
switch($filter_gameMode) {
	case 1: $sql2 .= " AND `product_singleplayer` = 1"; break;
	case 2: $sql2 .= " AND `product_multiplayer` = 1"; break;
	case 3: $sql2 .= " AND `product_cooperative` = 1"; break;	
}
				
//3, texto de búsqueda
$sql3 = "";
if($searching == 1) {
					
	$split_search = explode(" ", mysqli_real_escape_string($con, $_GET["q"]));
	
	$sql3a = "";
	for($i=0;$i<sizeof($split_search);$i++) {
		if($i>0) $sql3a .= " AND ";
		$sql3a .= "`product_name` LIKE '%".$split_search[$i]."%'";	
	}
	$sql3 = " AND ((".$sql3a.") OR `product_tags` LIKE '%".mysqli_real_escape_string($con, $_GET["q"])."%')";	
} else if($searching == 2) {
		
	$sql3 = " AND `product_tags` LIKE '%".mysqli_real_escape_string($con, $_GET["tag"])."%'";
}
				
//4, orden de los productos
$sql4 = "";
switch($filter_order) {
	case 1: $sql4 .= " ORDER BY `product_rating` DESC"; break;
	case 2: $sql4 .= " ORDER BY (CASE WHEN product_has_customprice = 1 AND product_customprice_currency = 'ars' THEN product_finalprice ELSE product_finalprice * ".getDollarQuote()*1.8." END) ASC"; break;
	case 3: $sql4 .= " ORDER BY (CASE WHEN product_has_customprice = 1 AND product_customprice_currency = 'ars' THEN product_finalprice ELSE product_finalprice * ".getDollarQuote()*1.8." END) DESC"; break;
}
//5, paginación
$sql5 = " LIMIT ".$current_page.", 20";

				
// Obtener cantidad total de resultados
$count_query = mysqli_query($con,$sql0a.$sql1.$sql2.$sql3);
			
				
$count = mysqli_fetch_row($count_query);
$results = $count[0];

$totalpages = floor($results / 20);
if(($results % 20) > 0) $totalpages += 1;

// Ejecutar query principal
if($results > 0) {
	$sql = $sql0b.$sql1.$sql2.$sql3.$sql4.$sql5;
	$query = mysqli_query($con, $sql);
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title><?php 
		if($searching == 1) echo "Buscar '".htmlspecialchars($_GET["q"])."' - SteamBuy"; 
		else if($searching == 2) echo "Categoría ".htmlspecialchars($_GET["tag"])." - SteamBuy";
		else echo "Catálogo de juegos - SteamBuy"; ?></title>
        
        <meta name="description" content="Encuentra los juegos que buscas en el catálogo de juegos.">
        <meta name="keywords" content="juegos,comprar,steam,origin,amazon,buscar,hallar,tarjeta,crédito,pago fácil,rapipago,ripsa">
        
        <meta property="og:title" content="Catálogo de juegos" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/juegos/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Encuentra los juegos que buscas en el catálogo de juegos." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/juegos/">
        <meta name="twitter:title" content="Catálogo de juegos">
        <meta name="twitter:description" content="Encuentra los juegos que buscas en el catálogo de juegos.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Catálogo de juegos">
        <meta itemprop="description" content="Encuentra los juegos que buscas en el catálogo de juegos.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/css/catalog_search_page.css?2" type="text/css">
        
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
        <script type="text/javascript" src="scripts/js/catalog_scripts.js?2"></script>
        
        <script type="text/javascript">
		<?php
		if($searching == 1) echo "var so_query = 'q=".urlencode($_GET["q"])."';\n";
		else if($searching == 2) echo "var so_query = 'tag=".urlencode($_GET["tag"])."';\n";
		else echo "var so_query = '';\n";
			
		echo "var so_order = '&order=".$filter_order."';\n";
		
		if($oferta_interna_stock == 1) echo "var int_stock = '';\n";
		else if($oferta_interna_stock == 0) echo "var int_stock = '&int_stock=0';\n";
		
		if($oferta_int_tiempo_lim == 1) echo "var int_tmpo = '';\n";
		else if($oferta_int_tiempo_lim == 0) echo "var int_tmpo = '&int_tmpo=0';\n";
		
		if($oferta_interna_indef == 1) echo "var int_undef = '';\n";
		else if($oferta_interna_indef == 0) echo "var int_undef = '&int_undef=0';\n";
		
		if($oferta_externa == 1) echo "var oft_ext = '';\n";
		else if($oferta_externa == 0) echo "var oft_ext = '&oft_ext=0';\n";
		
		if($sin_oferta == 1) echo "var sin_oft = '';\n";
		else if($sin_oferta == 0) echo "var sin_oft = '&sin_oft=0';\n";
		
		echo "var so_f_gm = '&gm=".$filter_gameMode."';\n
		
		var so_pg = '&pg=0';\n
		";
		?>	
		</script>

    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                <?php
				if($results > 0) {
					if($searching == 1) echo "<div style='font-size:14px;text-align:center;'>Se encontraron ".$results." resultados, mostrando página ".(($current_page / 20) + 1)." de ".$totalpages."</div>";	
					else if($searching == 2) echo "<div style='font-size:14px;text-align:center;'>Categoría '".htmlspecialchars($_GET["tag"])."', se encontraron ".$results." resultados</div>";
				}
				?>
                <div class="search-toolbar">
                	<div class="order_label">Ordenar:</div>
         			
                    <div class="btn-group btn-group-sm" id="filter_order_buttons" style="margin: 6px 0 0 13px;">
						<button type="button" class="btn btn-default <?php if($filter_order == 1) echo "active"; ?>">Relevancia</button>
                        <button type="button" class="btn btn-default <?php if($filter_order == 2) echo "active"; ?>">Menor precio</button>
                      	<button type="button" class="btn btn-default <?php if($filter_order == 3) echo "active"; ?>">Mayor precio</button>  
					</div>
                    
                    <div class="btn-group filter-prices-btn">
						<button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Filtrar ofertas <span class="caret"></span>
                      	</button>
						<ul class="dropdown-menu">
							<div class="checkbox"><label><input type="checkbox" id="filter-limited-stock" <?php if($oferta_interna_stock) echo "checked"; ?>> Ofertas propias de stock limitado</label></div>
                            <div class="checkbox"><label><input type="checkbox" id="filter-limited-time" <?php if($oferta_int_tiempo_lim) echo "checked"; ?>> Ofertas propias de tiempo limitado</label></div>
                            <div class="checkbox"><label><input type="checkbox" id="filter-undefined" <?php if($oferta_interna_indef) echo "checked"; ?>> Ofertas propias indefinidas</label></div>
                            <div class="checkbox"><label><input type="checkbox" id="filter-external-discount" <?php if($oferta_externa) echo "checked"; ?>> Ofertas externas</label></div>
                            <div class="checkbox"><label><input type="checkbox" id="filter-no-discount" <?php if($sin_oferta) echo "checked"; ?>> Sin oferta</label></div>
                            <div class="clearfix"><button class="btn btn-primary btn-sm" id="apply-discount-filter">Aplicar</button></div>
                      	</ul>
                    </div>
					
                    <div class="btn-group" id="filter_gamemode_buttons">
						<button type="button" class="btn btn-default w_tooltip <?php if($filter_gameMode == 1) echo "active"; ?>" data-toggle="tooltip" data-placement="bottom" <?php if($filter_gameMode == 1) echo "title='Mostrando sólo con singleplayer'"; else echo "title='Un jugador'"; ?> data-container="body"><img src="../global_design/img/icons/game_properties/singleplayer.png" alt="single player"/></button>
                        <button type="button" class="btn btn-default w_tooltip <?php if($filter_gameMode == 2) echo "active"; ?>" data-toggle="tooltip" data-placement="bottom" <?php if($filter_gameMode == 2) echo "title='Mostrando sólo con multijugador'"; else echo "title='Multijugador'"; ?> data-container="body"><img src="../global_design/img/icons/game_properties/multiplayer.png" alt="multijugador"/></button>
                        <button type="button" class="btn btn-default w_tooltip <?php if($filter_gameMode == 3) echo "active"; ?>" data-toggle="tooltip" data-placement="bottom" <?php if($filter_gameMode == 3) echo "title='Mostrando sólo con cooperativo'"; else echo "title='Cooperativo'"; ?> data-container="body"><img src="../global_design/img/icons/game_properties/multiplayer.png" alt="cooperativo"/></button> 
					</div>
          
                </div>
                
                <?php
				if($results > 0) 
				{
					?>
                    <div class="search_results">
                        <?php
                        $i = 0;
                        while($gameData = mysqli_fetch_assoc($query)) {
                            $i++;
                            ?>
                            <a href="../juegos/<?php echo $gameData["product_id"]; ?>/"><div class="game_result gr_hover">
                                <?php 
                                if($gameData["product_sellingsite"] == 3) {
									echo "<div class='cp-ribbon-wrapper'><div class='cp-ribbon cp-rib-humblebundle'><div class='wo_img'>Humble Bundle</div></div></div>";
								} else if($gameData["product_sellingsite"] == 4) {
									echo "<div class='cp-ribbon-wrapper'><div class='cp-ribbon cp-rib-bundlestars'><div class='wo_img'>Bundlestars</div></div></div>";
								} else if($gameData["product_has_customprice"] == 1) {
                                    echo "<div class='cp-ribbon-wrapper'><div class='cp-ribbon cp-rib-steambuy'><div class='wo_img'>OFERTA</div></div></div>";
                                } else if($gameData["product_external_limited_offer"] == 1){
                                    if($gameData["product_sellingsite"] == 1) {
                                        echo "<div class='cp-ribbon-wrapper'><div class='cp-ribbon cp-rib-steam'><img src='../global_design/img/icons/steam_transparent_22x22.png' width='19' alt='oferta steam' /><div class='w_img'>OFERTA</div></div></div>";
                                    } else if($gameData["product_sellingsite"] == 2) {
                                        echo "<div class='cp-ribbon-wrapper'><div class='cp-ribbon cp-rib-amazon'><img src='../global_design/img/icons/amazon_transparent_22x22.png' width='19' alt='oferta amazon' /><div class='w_img'>OFERTA</div></div></div>";
                                    }
                                }
                                
                                echo "<img class='game_image' src='../data/img/game_imgs/small/".$gameData["product_mainpicture"]."' alt='".$gameData["product_name"]."' />"; 
                                echo "<div class='game_name'>".$gameData["product_name"]."</div>";
                                
                                if($gameData["product_has_customprice"] == 1 && $gameData["product_customprice_currency"] == "ars") {
									echo "<div class='game_price_normal'>&#36;".$gameData["product_finalprice"]." ARS</div>";
								} else if(($gameData["product_external_limited_offer"] == 0 && $gameData["product_has_customprice"] == 0) || $gameData["product_sellingsite"] == 4) {
									echo "<div class='game_price_normal'>&#36;".quickCalcGame(1,$gameData["product_finalprice"])." ARS <span>(".$gameData["product_finalprice"]." usd)</span></div>";
                                } else if($gameData["product_has_customprice"] == 1 || $gameData["product_external_limited_offer"] == 1) {
                                    echo "<div class='game_price_discount'>
                                        <div class='gpd_percent'>-".round(100 - ($gameData["product_finalprice"] * 100 / $gameData["product_listprice"]))."%</div>
                                        <div style='float:right;max-width:150px;max-height:37px;overflow:hidden;'>
                                            <div class='gpd_listprice'>$".quickCalcGame(1,$gameData["product_listprice"])." (".$gameData["product_listprice"]." usd)</div>
                                            <div class='gpd_offerprice'>$".quickCalcGame(1,$gameData["product_finalprice"])." ARS <span>(".$gameData["product_finalprice"]." usd)</span></div>
                                        </div>
                                    </div>";
                                } 
                                
                                if($gameData["product_platform"] == 1) {
                                    echo "<img class='game_platform' src='../global_design/img/icons/steam_20x20.png' alt='steam' />";
                                } else if($gameData["product_platform"] == 2) {
                                    echo "<img class='game_platform' src='../global_design/img/icons/origin_20x20.png' alt='origin' />";
                                }
                                
                                ?>
                            
                            </div></a>
                            
                            <?php	
                        }
                        
                        for($i2=0;$i2<20-$i;$i2++) {
                            echo "<div class='game_result'></div>";	
                        }
                        
                        ?>
     
                       
                    </div>
                    <div class="search_pagination_bar">
                        <ul class="pagination"> 
                        <?php
						
						$current_real_pg = ($current_page / 20) + 1; // Número de página (1, 2, 3, 4, 5...)
						
						$q = "";
						if($searching == 1) $q = "q=".urlencode($_GET["q"])."&";
						else if($searching == 2) $q = "tag=".urlencode($_GET["tag"])."&";
						$current_filters = "?".$q."order=".$filter_order."&int_stock=".$oferta_interna_stock."&int_tmpo=".$oferta_int_tiempo_lim."&int_undef=".$oferta_interna_indef."&oft_ext=".$oferta_externa."&sin_oft=".$sin_oferta."&gm=".$filter_gameMode;
						
						if($totalpages > 5) {
							
							if($current_real_pg <= 2) $a = 1;
							else if($current_real_pg >= ($totalpages - 2)) $a = $totalpages - 4;  
							else $a = $current_real_pg - 2;
							
							if($a > 1) echo "<li><a href='".$current_filters."&pg=0' data-toggle='tooltip' data-placement='top' title='Primera página' class='w_tooltip'>&laquo;</a></li>";
							
							if($current_real_pg == 1) echo "<li class='disabled'><a href='javascript:void(0);'>&lsaquo;</a></li>";
							else echo "<li><a href='".$current_filters."&pg=".($current_page - 20)."' data-toggle='tooltip' data-placement='top' title='Página anterior' class='w_tooltip'>&lsaquo;</a></li>";
							
							for($i=$a;$i<=($a + 4);$i++) {
								if($i == $current_real_pg) echo "<li class='active'><a href='javascript:void(0);'>".$i."</a></li>"; 
								else echo "<li><a href='".$current_filters."&pg=".(($i - 1) * 20)."'>".$i."</a></li>"; 
							}
							
							if($current_real_pg == $totalpages) echo "<li class='disabled'><a href='javascript:void(0);'>&rsaquo;</a></li>";
							else echo "<li><a href='".$current_filters."&pg=".($current_page + 20)."' data-toggle='tooltip' data-placement='top' title='Página siguiente' class='w_tooltip'>&rsaquo;</a></li>";
							
							if(($a + 4) < $totalpages) echo "<li><a href='".$current_filters."&pg=".(($totalpages - 1) * 20)."' data-toggle='tooltip' data-placement='top' title='Última página' class='w_tooltip'>&raquo;</a></li>";
							
						} else {
							
							if($current_real_pg == 1) echo "<li class='disabled'><a href='javascript:void(0);'>&lsaquo;</a></li>";
							else echo "<li><a href='".$current_filters."&pg=".($current_page - 20)."' data-toggle='tooltip' data-placement='top' title='Página anterior' class='w_tooltip'>&lsaquo;</a></li>";
							
							for($i=1;$i<=$totalpages;$i++) {
								if($i == $current_real_pg) {
									echo "<li class='active'><a href='javascript:void(0);'>".$i."</a></li>"; 
								} else {
									echo "<li><a href='".$current_filters."&pg=".(($i - 1) * 20)."'>".$i."</a></li>"; 
								}
							}
							if($current_real_pg == $totalpages) echo "<li class='disabled'><a href='javascript:void(0);'>&rsaquo;</a></li>";
							else echo "<li><a href='".$current_filters."&pg=".($current_page + 20)."' data-toggle='tooltip' data-placement='top' title='Página siguiente' class='w_tooltip'>&rsaquo;</a></li>";
						}
						?>
                          
                        </ul>
                    </div>
                <?php
				} else if($results == 0) {
					echo "<div style='text-align: center;margin: 30px 0px;font-size: 18px;'>No se encontraron resultados.<br/> <span style='font-size:16px;'>Usa el <a href='../#formulario-juegos'>formulario de compra</a> para comprar el juego que buscas.</span></div>";	
				}
				
				?>

            </div><!-- End main content -->
            
        	<?php 
			
			require_once("../global_scripts/php/footer.php"); 
			//echo $sql1.$sql2.$sql3.$sql4.$sql5;?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>