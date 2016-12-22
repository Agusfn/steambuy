<?php
session_start();

define("ROOT_LEVEL", "");

header("Content-Type: text/html; charset=UTF-8");

require_once("global_scripts/php/client_page_preload.php");
require_once("global_scripts/php/admlogin_functions.php");
require_once("global_scripts/php/main_purchase_functions.php");
require_once("resources/php/catalog_functions.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


// si hay un evento de ofertas de steam, esto lo que hace es agregar un expositor de juegos en la página ppal
$steam_sales_event = false;
$steam_sales_featured_items = 12;

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
        
        
        <link rel="shortcut icon" href="favicon.ico?2"> 
     
        <link rel="stylesheet" href="global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="global_design/css/main.css?2.01" type="text/css">
        <link rel="stylesheet" href="design/css/main_page.css?2" type="text/css">

		<script type="text/javascript" src="global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>      
		<script type="text/javascript" src="global_scripts/js/global_scripts.js"></script>
        <script type="text/javascript" src="scripts/js/main_page_2.js"></script>

    </head>
    
    <body>
    
    	<div class="modal fade" id="game_form_modal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
  			<div class="modal-dialog">
    			<div class="modal-content">
      				<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        				<h4 class="modal-title" id="ModalLabel">Formulario de compra de juegos <span style="margin-left:20px;"><i class="fa fa-question question_info w_tooltip" data-toggle="tooltip" data-placement="bottom" title="Con este formulario podés generar boletas de pago para comprar juegos de las tiendas Steam o Amazon que no se encuentren en nuestro catálogo."></i></span></h4>
     				</div>
      				<div class="modal-body" style="transition:height 0.7s ease-out;">
                        <input type="hidden" value="<?php echo $_SERVER["REMOTE_ADDR"]; ?>" id="client_ip">
                        <div id="gf_first_form">
                            <div style="font-size: 13px;margin: 0px 0px 14px;color: #148335;font-weight: bold;">Hacé <a href="faq/#5" target="_blank">click aquí</a> para saber cómo usar este formulario y qué completar en los campos.</div>
                            <div class="form_row">
                                <div class="form_input_left">
                                    <div class="form_label_input">Nombre y apellido</div>
                                    <input type="text" name="name" class="form-control" id="gf_input_name" <?php
                                    if(isset($_COOKIE["client_name"])) echo "value = '".$_COOKIE["client_name"]."'"; ?>>
                                </div>
                                <div class="form_input_right">
                                    <div class="form_label_input">Dirección e-mail</div>
                                    <input type="text" name="email" class="form-control" id="gf_input_email" <?php
                                    if(isset($_COOKIE["client_email"])) echo "value = '".$_COOKIE["client_email"]."'"; ?>>
                                </div>
                            </div>
                            <div class="form_row">
                                <div class="form_input_left">
                                    <div class="form_label_input">Nombre del juego</div>
                                    <input type="text" name="gamename" class="form-control" id="gf_input_gamename">
                                </div>
                                <div class="form_input_right">
                                    <div class="form_label_input">Sitio de venta del juego</div>
                                    <select class="form-control"  id="gf_input_sellingsite">
                                        <option>Steam</option>
                                        <option>Amazon</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form_row">
                                <div class="form_input_left">
                                    <div class="form_label_input">URL de tienda del juego</div>
                                    <input type="text" class="form-control" id="gf_input_gameurl" placeholder="Ej: http://store.steampowered.com/app/440/">
                                </div>
                                <div class="form_input_right">
                                    <div class="form_label_input">Precio actual del juego</div>
                                    U$S <input type="text" class="form-control" id="gf_input_gameprice" placeholder="Monto" onfocus="$(this).val('');" onkeypress="return limitInputChars(event, this);" onblur="applyFormat(this);">
                                </div>
                            </div>
                            <div class="form_row" style="margin:0;">
                                <div class="form_input_left">
                                    <div class="form_label_input">Juego en oferta limitada</div>
                                    <select class="form-control" id="gf_input_gamediscount">
                                        <option>No</option>
                                        <option>Si</option>
                                    </select>
                                </div>
                                
                                <div class="form_input_right">
                                <div class="checkbox" style="margin-top:10px;color: rgba(57, 94, 143, 1);"><label>
                                    <input type="checkbox" value="" id="gf_input_rememberdata" <?php if(isset($_COOKIE["client_name"]) && isset($_COOKIE["client_email"])) echo "checked"; ?> >
                                    Recordar el nombre y el e-mail para las <br/>próximas compras.
                                </label></div>
                                </div>
                                
                            </div>
                            <div class="alert alert-warning" id="gf_offer_warning">Si el juego tiene una oferta limitada,<strong> deberás informar el pago antes de que termine la oferta
                            del mismo</strong> (en la tienda de Steam se puede ver cuándo finaliza) para que te lo guardemos antes de que finalice, <strong>de lo contrario podrás PERDER la oferta, debiendo elegir otro/s producto/s</strong>.</div>
                            <div class="alert alert-danger" id="gf_error_list"><span class="glyphicon glyphicon-remove" style="float:right;cursor:pointer;" onClick="$(this).parent('#gf_error_list').slideUp('slow');"></span><ul></ul></div>
                        </div>
                        
                        <div id="gf_second_form">
                        
                        	<h4>Elige un medio de pago y confirma los datos:</h4>
                        	<div style="height:266px">

                                <div class="gf_left">
                                    <div class="list-group gf_payment_options" id="gf_paymentoptions">

                                        <a href="javascript:void(0);" class="list-group-item active">
                                            <div style="height: 30px;">
                                                <div style="float:left;font-size:17px;">Cupón de pago</div><div id="gf_arsprice1" class="gf_payoption_arsprice">$0 ARS</div>
                                            </div>
                                            <p class="list-group-item-text">Abona en <strong>Rapipago</strong>, <strong>Pago Fácil</strong> u otras sucursales presentando un cupón de pago. Después de entre 12 y 48 hs. hábiles se acreditará el 
                                            pago y recibirás el juego. </p>
                                        </a>
                                        <a href="javascript:void(0);" class="list-group-item">
                                            <div style="height: 30px;">
                                                <div style="float:left;font-size:17px;">Transferencia bancaria</div><div id="gf_arsprice2" class="gf_payoption_arsprice">$0 ARS</div>
                                            </div>
                                            <p class="list-group-item-text">Realiza un depósito bancario o haz una transferencia por home banking sin moverte de tu casa. En un máximo de 12 horas hábiles recibirás el juego.</p>
                                        </a>
                                    </div>
    
                                </div>
                                <div class="gf_right">
                                	<div class="gf_confirmationdata" style="text-decoration:underline; margin-top:-4px">Datos del comprador:</div>
                                    <div class="gf_confirmationdata"><strong>Nombre:</strong> <span id="gf_sf_confirmation_name"></span></div>
                                    <div class="gf_confirmationdata"><strong>E-mail:</strong> <span id="gf_sf_confirmation_email"></span></div>
                                    <div class="gf_confirmationdata" style="margin-top:15px; text-decoration:underline">Datos del juego:</div>
                                    <div class="gf_confirmationdata"><strong>Nombre:</strong> <span id="gf_sf_confirmation_gamename"></span></div>
                                    <div class="gf_confirmationdata"><strong>Sitio de venta:</strong> <span id="gf_sf_confirmation_gamesite"></span></div>
                                    <div class="gf_confirmationdata" style="height:auto !important;"><strong>URL de tienda:</strong> <input type="text" class="form-control" id="gf_sf_confirmation_gameurl" readonly></div>
                                    <div class="gf_confirmationdata"><strong>Precio actual en tienda:</strong> <span id="gf_sf_confirmation_gameprice"></span></div>
                                    <div class="gf_confirmationdata"><strong>En oferta:</strong> <span id="gf_sf_confirmation_gameoffer"></span></div>
                                </div>
                            </div>
                            <div class="alert alert-warning" id="gf_sf_repeatwarning">Parece que ya realizaste un pedido por este juego a este e-mail recientemente, te recomendamos usar otro e-mail para realizar pedidos repetidos
                            ya que Steam no permite enviar más de una misma copia a un mismo e-mail por un período de tiempo.</div>
                            
                        </div>
                        
                        <div id="gf_third_form">
                        	<h4>El pedido se ha generado</h4>
                            <div class="gf_tf_ticketinstructions">Se ha generado tu pedido de <strong><span class="gf_tf_gamearsprice">$0 ARS</span></strong> por el juego <span class="gf_tf_gamename">x</span>, el siguiente paso es imprimir y abonar el cupón de pago en cualquier sucursal de <strong>Rapipago</strong>,
                            <strong>Pago Fácil</strong>, <strong>Ripsa</strong>, <strong>Cobroexpress</strong>, <strong>Bapropagos</strong>, u otras cadenas de pago especficadas en la boleta o cupón de pago.<br></div>
                        	
                            <div class="gf_tf_transferinstructions">Se ha generado tu pedido de <strong><span class="gf_tf_gamearsprice">$0 ARS</span></strong> por el juego <span class="gf_tf_gamename">x</span>, el siguiente paso es <strong>realizar el depósito o transferencia bancaria a la cuenta
                            bancaria especificada a continuación</strong>.<br></div>

                            <div class="gf_tf_ticketdata">
                                <a href="#" target="_blank" class="btn btn-primary btn-lg" id="gf_tf_ticket_button">Ver cupón de pago&nbsp;&nbsp;<span class="glyphicon glyphicon-barcode"></span></a>
                                <br/><a href="#" target="_blank" id="gf_tf_ticketformat">Ver en formato ticket</a>
                            </div>
                            
                            <div class="gf_tf_transferdata">
                            	<div><strong>Banco:</strong> ICBC</div>
                                <div><strong>Cuenta:</strong> <span id="gf_tf_bank_account"></span></div>
                                <div><strong>CBU:</strong> <span id="gf_tf_bank_account_cbu"></span></div>
                                <div><strong>Titular:</strong> <span id="gf_tf_bank_account_owner"></span></div>
                                <div><strong>CUIL:</strong> <span id="gf_tf_bank_account_cuil"></span></div>
                                <div><strong>Monto:</strong> <span class="gf_tf_gamearsprice">$0 ARS</span></div>
                            </div>
                            
                        	<div class="gf_tf_transferinstructions">Una vez hecha la transferencia, envia una foto o imágen en la sección de <a href="informar/" target="_blank">informar pago</a> para que identifiquemos tu pago. 
                            El juego se enviará <strong>dentro de las siguientes 12 horas hábiles</strong> de haber recibido el pago (el pago es instantáneo en horario hábil).</div>
                            
                            <div class="gf_tf_ticketinstructions">Una vez abonado, <strong>el pago tomará entre 12 y 48 horas en acreditarse</strong> automáticamente, es entonces cuando se enviará el juego,<strong> por lo general al mediodía del día siguiente</strong> de abonar.</div>

                             <div class="alert alert-warning gf_tf_offerwarning"> El juego tiene un descuento de tiempo limitado, informá el pago 
                            antes de que termine la oferta (revisa en el <a href="" target="_blank" id="gf_tf_site_url">sitio de venta</a> cuándo finaliza) en la sección de <a href="informar/" target="_blank">informar pago</a>, para asegurarte de que
                            te guardemos el juego, <strong>de lo contrario podrás perder la oferta</strong> y deberás elegir otro/s producto/s.</div>
                            
                            <div class="alert alert-info" style="font-size: 14px;margin-top:15px; text-align:justify">
                            	El ID de tu pedido es <strong><span id="gf_tf_orderid"></span></strong> y la clave es <strong><span id="gf_tf_orderpass"></span></strong>. Estos datos se requieren en caso de informar un pago, 
                                cancelar un pedido, o para asistencia. Se te ha enviado un mensaje al e-mail <strong><span id="gf_tf_clientemail"></span></strong> con esta información.
                            </div>
                            
                            <div class="alert alert-danger" style="font-size: 14px;margin-top:10px; text-align:justify;display:none;" id="gf_tf_mailerror">Ha ocurrido un error enviando el e-mail con los datos del pedido, te recomendamos guardar el <strong>ID</strong> y <strong>clave de pedido</strong>
                            mostrados en este cuadro, disculpa las molestias.</div>
                        </div>
                        
                    </div>
                  	<div class="modal-footer">
                    	<div id="gf_order_price"><strong>Total:</strong> $0 ARS</div>
                    	<i class="fa fa-spinner fa-spin fa-lg" id="gf_loadicon"></i>
                        <button type="button" class="btn btn-default" data-dismiss="modal" id="gf_button_cancel">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="gf_button_confirm">Siguiente</button>
                  	</div>
            	</div>
          	</div>
        </div>

		<?php require_once("global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">

				<?php
                if($steam_sales_event) {
                ?>
                    <div class="event_title">REBAJAS DE VERANO DE STEAM<div class="event_duration">desde el 23 de junio hasta el 4 de julio</div></div>
                    <div class="catalog-panel" style="margin:25px 0;">
                        <div class="cp-top">
                            <div class="cp-title">OFERTAS DESTACADAS DE HOY</div>
                        </div>
                        <div class="cp-content">

                            <?php
							$sql = "SELECT * FROM `products` WHERE ".$basic_product_filter." ORDER BY `product_rating` DESC LIMIT ".$steam_sales_featured_items;
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
							$cant_productos = $prod_por_pag * $paginas;

							$sql = "SELECT * FROM `products` WHERE ".$basic_product_filter." ORDER BY `product_rating` DESC LIMIT ".($steam_sales_event ? $steam_sales_featured_items."," : "").$cant_productos;
							$res = mysqli_query($con, $sql);
                            $i = 0;

                            while($pData = mysqli_fetch_assoc($res)) 
                            {
								$i++;
								$displayedProducts[] = $pData["product_id"];
								if(is_int(($i-1)/$prod_por_pag)) {
									echo "<div class='item".($i==1?" active":"")."'>";	
								}
								display_catalog_product($pData);
								if(is_int($i/$prod_por_pag)) {
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
								else echo "<div class='cp-title'>Ofertas propias aleatorias<a href='juegos/?st=0&amz=0&hb=0&bs=0&gm=0&pg=0'><div class='cp-viewmore'>Ver todas</div></a></div>";
								?>
                            </div>

                            <div class="cp-content">
                                
                                <div id="carousel-random" class="carousel slide" data-ride="carousel" data-interval="false">
                                	<div class="carousel-inner" role="listbox">

										<?php
                                        $filas = 4;
                                        $paginas = 2;
                                        $prod_por_pag = $filas*3; // 3 columnas
                                        $cant_productos = $prod_por_pag * $paginas;

										if($steam_sales_event) { // Si hay evento de ofertas se muestran aleatorias de Steam
											$sql = "SELECT * FROM products WHERE ".$basic_product_filter." AND (product_has_customprice = 1 OR product_external_limited_offer = 1) ORDER BY RAND() LIMIT 40";
										} else { // Si no hay evento, se muestran aleatorias de SteamBuy
											$sql = "SELECT * FROM products WHERE ".$basic_product_filter." AND product_has_customprice = 1 ORDER BY RAND() LIMIT 40";
										}
						                
										$query = mysqli_query($con, $sql);
                                        $i = 0;

										while($pData = mysqli_fetch_assoc($query)) 
                                        {
											if($i < $cant_productos && !in_array($pData["product_id"],$displayedProducts)) 
											{
												$i++;
												$displayedProducts[] = $pData["product_id"];
												if(is_int(($i-1)/$prod_por_pag)) {
													echo "<div class='item".($i==1?" active":"")."'>";	
												}
												display_catalog_product($pData, "sm");
												if(is_int($i/$prod_por_pag)) {
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
                                    <div class="cp-title">Ofertas externas</div>
                                </div>
                                <div class="cp-content">
                                    <?php
                                    $filas = 3;
                                    $cant_productos = $filas * 3; // 3 columnas
                                    
                                    $sql = "SELECT * FROM products WHERE ".$basic_product_filter." AND ((product_external_limited_offer = 1 AND NOT product_has_customprice = 1) OR ((product_sellingsite = 3 OR product_sellingsite = 4) AND product_external_limited_offer = 1))
                                     ORDER BY product_rating DESC LIMIT 35";
                                    
                                    $query = mysqli_query($con, $sql);
                                    $i = 0;
                                    while($pData = mysqli_fetch_assoc($query)) 

                                    {
                                        if($i <$cant_productos && !in_array($pData["product_id"],$displayedProducts)) 
                                        {
											$i++;
                                            $displayedProducts[] = $pData["product_id"];
                                            display_catalog_product($pData, "sm");									
                                        }
                                    }
                                    ?> 
                                </div>
                            </div>
                        <?php
						}
						?>
                        <?php
						if($steam_sales_event) {
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
                                        
                                        $sql = "SELECT * FROM `products` WHERE ".$basic_product_filter." AND (`product_external_limited_offer`=1 OR `product_has_customprice`=1) AND `product_tags` LIKE '%".$tag."%' ORDER BY RAND() LIMIT ".$cant_productos;
                                        
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
                            <div class="panel-heading">Calculadora de precios<i class="fa fa-question question_info w_tooltip" style="float: right; margin: 3px 0px 0px;" data-toggle="tooltip" data-placement="top" title="Calcula para referencia el precio final en pesos de cualquier juego o pack de Steam o Amazon a partir de su precio en USD"></i></div>
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
            
        	<?php require_once("global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>