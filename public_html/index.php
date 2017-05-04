<?php
require_once("../config.php");

require_once(ROOT."app/lib/user-page-preload.php");

require_once(ROOT."app/lib/purchase-functions.php");
require_once("resources/php/catalog_functions.php");



// si hay un evento de ofertas de steam, esto lo que hace es agregar un expositor de juegos en la página ppal
$steam_sales_event = false;
$steam_sales_featured_items = 9;

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Tienda de SteamBuy</title>
        
        <meta name="description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles.">
        <meta name="keywords" content="juegos,comprar,tarjeta,crédito,steam,amazon,humblebundle,bundlestars,rapipago,pago fácil,ripsa,counter strike,oferta,descuento,PayPal">
        
        <meta property="og:title" content="Tienda de SteamBuy" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar">
        <meta name="twitter:title" content="Tienda de SteamBuy">
        <meta name="twitter:description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Tienda de SteamBuy">
        <meta itemprop="description" content="SteamBuy es una tienda donde encontrarás una gran variedad de juegos digitales para PC con medios de pago accesibles.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
		
		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>

        <link rel="stylesheet" href="resources/css/home-page.css" type="text/css">

		<script type="text/javascript" src="resources/js/main-page.js"></script>
		<script type="text/javascript" src="resources/js/price-inpt-fnc.js"></script>

    </head>
    
    <body>
    
		<?php require_once(ROOT."app/template/purchase-form-modal.php"); ?>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">

				<?php
                if($steam_sales_event) {
                ?>
                    <div class="event_title">REBAJAS DE VERANO DE STEAM<div class="event_duration">desde el 22 de diciembre hasta el 2 de enero</div></div>
                    <div class="catalog-panel" style="margin:25px 0;">
                        <div class="cp-top">
                            <div class="cp-title">OFERTAS DESTACADAS DE HOY</div>
                        </div>
                        <div class="cp-content">

                            <?php
							$sql = "SELECT ".$needed_product_data." FROM `products` WHERE ".$basic_product_filter." ORDER BY `product_rating` DESC LIMIT ".$steam_sales_featured_items;
							$query = mysqli_query($con, $sql);
                            
							$results = array(); 
							while($row = mysqli_fetch_assoc($query)) {
								array_push($results, $row);
							}
							shuffle($results);

                            foreach($results as $pData)  {
								$displayedProducts[] = $pData["product_id"];
								display_catalog_product($pData, "lg");
                            }
                            ?>

                        </div>
                    </div>

                    <?php
                }
                ?>
	
				<div class="catalog-panel" style="margin-bottom:30px;">
                	
                    <div class="cp-top">
                    	<div class="cp-title">Lo más destacado<a href="juegos/"><div class="cp-viewmore">Ver todo</div></a></div>
                    </div>
                    
                    <div class="cp-content">

                        <div id="carousel-relevant" class="carousel slide" data-ride="carousel" data-interval="10000">
							<div class="carousel-inner" role="listbox">

                            <?php
							$filas = 3;
							$paginas = 2;
							$prod_por_pag = $filas*4; // 4 columnas
							$max_productos = $prod_por_pag * $paginas;

							$sql = "SELECT ".$needed_product_data." FROM `products` WHERE ".$basic_product_filter." ORDER BY `product_rating` DESC LIMIT ".($steam_sales_event ? $steam_sales_featured_items."," : "").$max_productos;

							$res = mysqli_query($con, $sql);
							$result_ammount = mysqli_num_rows($res);
							
                            $displayed = 0;
                            while($pData = mysqli_fetch_assoc($res)) 
                            {
								if($displayed >= $max_productos) break
								;
								$displayed++;
								$displayedProducts[] = $pData["product_id"];
								if(is_int(($displayed-1)/$prod_por_pag)) {
									echo "<div class='item".($displayed==1?" active":"")."'>";	
								}
								
								display_catalog_product($pData);
								
								if(is_int($displayed/$prod_por_pag)) {
									echo "</div>";	
								} else if(!is_int($displayed/$prod_por_pag) && $displayed == $result_ammount) { // Si es el último elemento de la consulta y no el último de la página, se rellena y se cierra el .item
									$items_restantes = $prod_por_pag - ($displayed % $prod_por_pag);
									for($i=0;$i<$items_restantes;$i++) {
										display_catalog_product(false, "sm");
									}
									echo "</div>";
								}
                            }
                            ?>
							</div>
                        </div>
                        
                    </div>
                    <div class="cp-bottom">
                    	<span class="cp-carousel-pagination">0/0</span>
                    	<span class="cp-carousel-pag-controls">
                        	<a href="#carousel-relevant" role="button" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a href="#carousel-relevant" role="button" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
                        </span>
                    </div>
                </div>
				
                <div class="clearfix">
                
                    <div class="left-column">


                        <div class="catalog-panel">
                            <div class="cp-top">
                            	<?php
								if($steam_sales_event) echo "<div class='cp-title'>Ofertas de Steam aleatorias<a href='juegos/?amz=0&hb=0&bs=0&gm=0&pg=0'><div class='cp-viewmore'>Ver todas</div></a></div>";
								else echo "<div class='cp-title'>Ofertas de stock aleatorias<a href='juegos/?int_tmpo=0&int_undef=0&oft_ext=0&sin_oft=0'><div class='cp-viewmore'>Ver todas</div></a></div>";
								?>
                            </div>

                            <div class="cp-content">
                                
                                <div id="carousel-random" class="carousel slide" data-ride="carousel" data-interval="false">
                                	<div class="carousel-inner" role="listbox">

										<?php
                                        $rows = 3;
                                        $max_pages = 2;
                                        $prodcts_per_pg = $rows*3; // 3 columnas
                                        $max_products = $prodcts_per_pg * $max_pages;

										if($steam_sales_event) { // Si hay evento de ofertas se muestran aleatorias de Steam
											$sql = "SELECT ".$needed_product_data." FROM products WHERE ".$basic_product_filter." AND (product_has_customprice = 1 OR product_external_limited_offer = 1) ORDER BY RAND() LIMIT ".($max_products + 20);
										} else { // Si no hay evento, se muestran aleatorias de SteamBuy
											$sql = "SELECT ".$needed_product_data." FROM products WHERE ".$basic_product_filter." AND `product_has_customprice` = 1 AND `product_has_limited_units` = 1 ORDER BY RAND() LIMIT ".$max_products;
										}
						                
										$query = mysqli_query($con, $sql);
										
										$result_ammount = mysqli_num_rows($query);
										$result = 0;
										$displayed = 0;
										while($pData = mysqli_fetch_assoc($query)) 
                                        {
											$result++;
											if($displayed < $max_products /*&& !in_array($pData["product_id"],$displayedProducts)*/) 
											{
												$displayed++;
												//$displayedProducts[] = $pData["product_id"];
												if(is_int(($displayed-1)/$prodcts_per_pg)) {
													echo "<div class='item".($displayed==1?" active":"")."'>";	
												}
												display_catalog_product($pData, "sm");
		
												if(is_int($displayed/$prodcts_per_pg)) { // Si es el último elemento de la página se cierra el .item del carousel
													echo "</div>";	
												} else if(!is_int($displayed/$prodcts_per_pg) && $result == $result_ammount) { // Si es el último elemento de la consulta y no el último de la página, se rellena y se cierra el .item
													$items_restantes = $prodcts_per_pg - ($displayed % $prodcts_per_pg);
													for($i=0;$i<$items_restantes;$i++) {
														display_catalog_product(false, "sm");
													}
													echo "</div>";
												}
											}
                                        }
                                        ?>
                                    </div>
                                </div>

                                
                            </div>

                            <div class="cp-bottom">
                                <span class="cp-carousel-pagination">0/0</span>
                                <span class="cp-carousel-pag-controls">
                                    <a href="#carousel-random" role="button" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a href="#carousel-random" role="button" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
                                </span>
                            </div>
                        </div>
    					
                        <?php
						
						
						if(!$steam_sales_event) {
						?>
                            <div class="catalog-panel" style="margin-top:25px;">
                                <div class="cp-top">
                                    <div class="cp-title">Ofertas de tiempo limitado aleatorias<a href='juegos/?int_stock=0&int_undef=0&sin_oft=0'><div class='cp-viewmore'>Ver todas</div></a></div>
                                </div>
                                <div class="cp-content">
                                    <?php
                                    $filas = 4;
                                    $cant_productos = $filas * 3; // 3 columnas
                                    
                                    $sql = "SELECT ".$needed_product_data." FROM products WHERE ".$basic_product_filter." AND `product_external_limited_offer` = 1 ORDER BY RAND() LIMIT ".$cant_productos;
                                    
                                    $query = mysqli_query($con, $sql);
                                    $i = 0;
                                    while($pData = mysqli_fetch_assoc($query)) 

                                    {
                                        if($i <$cant_productos /*&& !in_array($pData["product_id"],$displayedProducts)*/) 
                                        {
											$i++;
                                            //$displayedProducts[] = $pData["product_id"];
                                            display_catalog_product($pData, "sm");									
                                        }
                                    }
                                    ?> 
                                </div>
                            </div>
                        <?php
						} else if($steam_sales_event) {
							$tags = array("acción", "aventura", "rol", "estrategia"); // se van a mostrar pequeños catálogos aleatorios de juegos con estos tags
							
							foreach($tags as $tag) {
								?>
                                <div class="catalog-panel" style="margin-top:25px;">
                                    <div class="cp-top">
                                        <div class="cp-title">Juegos de <?php echo $tag; ?> aleatorios en oferta<a href="juegos/?tag=<?php echo $tag; ?>"><div class="cp-viewmore">Ver más</div></a></div>
                                    </div>
                                    <div class="cp-content">
                                        <?php
                                        $filas = 2;
                                        $cant_productos = $filas * 3; // 3 columnas
                                        
                                        $sql = "SELECT ".$needed_product_data." FROM `products` WHERE ".$basic_product_filter." AND (`product_external_limited_offer`=1 OR `product_has_customprice`=1) AND `product_tags` LIKE '%".$tag."%' ORDER BY RAND() LIMIT ".$cant_productos;
                                        
                                        $query = mysqli_query($con, $sql);
                                        $i = 0;
                                        while($pData = mysqli_fetch_assoc($query)) {
                                            if($i <$cant_productos) {
                                                $i++;
                                                display_catalog_product($pData, "sm");									
                                            }
                                        }
                                        ?> 
                                    </div>
                                </div>
								<?php
							}
						}
						?>
                    </div>
                    
                    <div class="right-column">
    					<?php
						/*
                        <!--div class="catalog-panel" style="margin-bottom:30px;">
                            <div class="cp-top-short">
                                <div class="cp-title">Gift cards populares<a href="#"><div class="cp-viewmore">Ver todas</div></a></div>
                            </div>
                            <div class="cp-content" style="height:395px;border-bottom:1px solid #AAA;">
                            
                                <div class="cpl-product">
                                    <div style="float:left;">
                                        <img src="resources/css/img/amazon.png" class="cpl-gftcrd-img">
                                        <div class="cpl-gftcrd-ammount"><span>5</span> USD</div>
                                    </div>
                                    <div class="cpl-gftcrd-name">
                                        <div>Gift card Amazon 5 USD (US Only)</div>
                                    </div>
                                    <div class="cpl-gftcrd-price">$157 <span>ARS</span></div>
                                </div>
                                
                                <div class="cpl-product">
                                    <div style="float:left;">
                                        <img src="resources/css/img/amazon.png" class="cpl-gftcrd-img">
                                        <div class="cpl-gftcrd-ammount"><span>10</span> USD</div>
                                    </div>
                                    <div class="cpl-gftcrd-name">
                                        <div>Gift card Amazon 10 USD (US Only)</div>
                                    </div>
                                    <div class="cpl-gftcrd-price">$200 <span>ARS</span></div>
                                </div>
                                <div class="cpl-product">
                                    <div style="float:left;">
                                        <img src="resources/css/img/amazon.png" class="cpl-gftcrd-img">
                                        <div class="cpl-gftcrd-ammount"><span>20</span> USD</div>
                                    </div>
                                    <div class="cpl-gftcrd-name">
                                        <div>Gift card Amazon 20 USD (US Only)</div>
                                    </div>
                                    <div class="cpl-gftcrd-price">$400 <span>ARS</span></div>
                                </div>
                                <div class="cpl-product">
                                    <div style="float:left;">
                                        <img src="resources/css/img/amazon.png" class="cpl-gftcrd-img">
                                        <div class="cpl-gftcrd-ammount"><span>50</span> USD</div>
                                    </div>
                                    <div class="cpl-gftcrd-name">
                                        <div>Gift card Amazon 50 USD (US Only)</div>
                                    </div>
                                    <div class="cpl-gftcrd-price">$1000 <span>ARS</span></div>
                                </div>
                                <div class="cpl-product" style="border-bottom:none;">
                                    <div style="float:left;">
                                        <img src="resources/css/img/amazon.png" class="cpl-gftcrd-img">
                                        <div class="cpl-gftcrd-ammount"><span>100</span> USD</div>
                                    </div>
                                    <div class="cpl-gftcrd-name">
                                        <div>Gift card Amazon 100 USD (US Only)</div>
                                    </div>
                                    <div class="cpl-gftcrd-price">$2000 <span>ARS</span></div>
                                </div>
    
    
                            </div>
                        </div-->
                        */
						?>
                        <a style="text-decoration:none !important;" href="soporte/"><div class="support-box" style="margin-bottom:30px">
                            <div class="support-box-question"><i class="fa fa-question-circle-o" aria-hidden="true"></i></div>
                            Si tienes alguna consulta o duda, visita nuestra sección de soporte
                        </div></a>
                        
                        <div class="panel panel-default panel_normal">
                            <div class="panel-heading">Calculadora de precios<i class="fa fa-question question_info" style="float: right; margin: 3px 0px 0px;" data-toggle="tooltip" data-placement="top" title="Calcula para referencia el precio final en pesos de cualquier juego o pack de Steam o Amazon a partir de su precio en USD"></i></div>
                            <div class="panel-body">
                                <div class="calcbox_form">
                                    U$S<input type="text" class="form-control" id="calcbox_input" placeholder="Monto" onfocus="$(this).val('');" onkeypress="return limitInputChars(event, this);" onblur="applyFormat(this);" />
                                    <button type="button" class="btn btn-primary" id="calcbox_btn">Calcular</button>
                                    <i class="fa fa-spinner fa-spin fa-lg" id="calcbox_loadicon"></i>
                                </div>
                                <div class="calcbox_respline">
                                    <div class="calcbox_placeholder">Ingresa el precio en dólares</div>
                                    <div class="calcbox_usdammount">0 USD&nbsp;:</div><div class="calcbox_arsresponse">$0 ARS</div>
                                </div>
                            </div>
                        </div>
    
                        <a style="text-decoration:none !important;" href="javascript:void(0);" data-toggle="modal" data-target="#game_form_modal"><div class="game-form-box" style="margin-bottom:20px">
                            ¿El juego que buscás no está en el catálogo? Hacé click aquí para comprarlo
                        </div></a>
                        
                        <div style="height:400px"><a class="twitter-timeline" height="400" href="https://twitter.com/SteamBuy"  data-widget-id="375996099044970496">Tweets por @SteamBuy</a>
                        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
    
                    </div>
				</div>			
            
            </div><!-- End main content -->
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>