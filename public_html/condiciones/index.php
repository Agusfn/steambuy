<?php
require_once("../../config.php");
require_once(ROOT."app/lib/user-page-preload.php");

require_once("../global_scripts/php/purchase-functions.php");


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Términos y condiciones - SteamBuy</title>
        
        <meta name="description" content="Página donde se encuentran los términos y condiciones de compra.">
        <meta name="keywords" content="términos,condiciones,reglas,steambuy,compra,juego">
        
        <meta property="og:title" content="Términos y condiciones" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/condiciones/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Página donde se encuentran los términos y condiciones de compra." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/condiciones/">
        <meta name="twitter:title" content="Términos y condiciones">
        <meta name="twitter:description" content="Página donde se encuentran los términos y condiciones de compra.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Términos y condiciones">
        <meta itemprop="description" content="Página donde se encuentran los términos y condiciones de compra.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        
        <style type="text/css">
		.main_title
		{
			text-align:center;
			margin: 10px 0 20px 0;
			color: rgba(38, 116, 183, 1);
			font-size:20px;
		}
		h4
		{
			margin:15px 0 15px 15px;
		}
		
		.tosbox
		{
			margin:25px auto 35px;
			width:90%;
			font-size:15px;
		}
		</style>
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../resources/js/global-scripts.js?2"></script>

    </head>
    
    <body>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="main_title">Términos y condiciones de compra</h3>
                
                <div class="tosbox">
                    <div style="font-weight:bold; text-align:center;">
                        Al usar este servicio usted acepta los siguientes términos y condiciones.<br />
                        <span style="font-size:12px;">(Actualizado el 29/12/2015)</span>
                    </div>
                    <ol>
                        <h4>Sitio, precios y privacidad:</h4>
                            
                        <li>SteamBuy ofrece un servicio de venta y de reventa a pedido de productos digitales, estos son videojuegos en formato digital, compras de saldo virtual (PayPal), o ítems de valor de un videojuego.</li>
                        <li>La forma de comprar productos es generando "pedidos", un pedido por producto, estos guardan datos del producto y del comprador, información mencionada en el punto siguiente.</li>
                        <li>El sitio pedirá información básica al comprador (nombre, apellido e email) al realizar un pedido, estos datos no serán compartidos con ninguna persona.</li>
                        <li>SteamBuy se reserva el derecho de admisión al sitio respecto a quienes realicen acciones con intención de generar molestia, según la IP que se registre.</li>
                        <li>El precio final de los productos es en Pesos argentinos. Si se trata de un producto por reventa o un envío de saldo, este precio se obtiene a partir de cálculos 
                        realizados por el sitio considerando precio original y su moneda en su sitio de venta. Los cálculos varían según el tipo de producto en venta (Juegos o compras de saldo virtual).</li>
                        
    
                        <h4>Formulacion de pedidos para juegos:</h4>
                            
                        <li>Se permite como máximo hasta 10 pedidos activos simultáneos (sean de juegos o compra de saldo virtual PayPal) por cada email.</li>
                        <li>Los pedidos activos cuyos pagos no se registren a los 5 días de haberse creado expirarán y se cancelarán. Si el comprador hubiera pagado le corresponderá
                        elegir otro/s producto/s por el mismo precio o un reembolso.</li>
                        <li>Si el precio ingresado por el usuario en el formulario de compra difiere con el precio real del juego de la tienda especificada, se cancelará el pedido sin excepción.
                        En caso de haberse pagado, al comprador le corresponderá un reembolso.</li>
                        <li>Si un producto del catálogo posee un precio que difiere erróneamente del precio de su tienda original sin que este haya estado en ese precio en los ultimos días, o si surge un precio de tiempo muy limitado (menor a 3 horas) en otra tienda sea por un error u oferta instantánea,
                        SteamBuy puede rechazar este pedido de pago (mostrando evidencia suficiente en un historial) y al comprador le corresponderá un reembolso.</li>
    
                        <h4>Pagos y envío de productos:</h4>
                        
                        <li>Los productos son enviados al correo electrónico proporcionado por el comprador durante el día en que se registra el pago del respectivo pedido. Si el pago es por cupón de pago,
                        el pago toma entre 12 y 48 horas en acreditarse. En caso de transferencia bancaria o depósito, estas se acreditan en horarios hábiles únicamente, y serán enviados durante las próximas 12
                        horas de haberse acreditado. <strong>(Excepto durante eventos de ofertas, ver puntos 21, 22 y 23)</strong></li>
                        <li>Los juegos digitales se envían en tres formatos diferentes: Regalo de Steam, clave de activación, o link de activación de HumbleBundle.com. El formato lo determinará
                        SteamBuy según la disponibilidad, y el comprador no reclamará este hecho.</li>
                        <li>Si el comprador pide un juego que posee una oferta limitada en una tienda externa, el pago del mismo debe ser informado antes de que finalice, de esta manera SteamBuy 
                        guardará el juego (lo "reservará") para ser enviado cuando se acredite su pago. Si el pago se acredita antes de que finalice la oferta, SteamBuy se compromete a enviar el producto
                        haya informado o no el pago el comprador (Más información en el punto #).</li>
                        <li>Si una clave de activación enviada no funciona, SteamBuy investigará el asunto para determinar si efectivamente la clave fue defectuosa. 
                        Si este es el caso, al comprador le corresponderá un cambio de producto o un reembolso, o si está disponible, una reposición del mismo producto.</li>
                        <li>Si existe alguna bonificación aplicable en la adquisición de productos en tiendas externas, estas le corresponderán a SteamBuy y el comprador no 
                        reclamará este hecho.</li>
                        <li>SteamBuy no se responsabilizará por los posibles problemas si un producto pedido ha sido enviado a un mail erroneamente proporcionado por el comprador. 
                        Si el comprador lo pide, si se trata de un juego en formato de clave, esta se renviará al e-mail del comprador. Si se trata de una compra de saldo PayPal, se intentará 
                        hacer el reembolso y reenvío del pago.</li>
                        <li>SteamBuy se reserva el derecho de cancelar pedidos aún no acreditados ni enviados por motivos que considerará justificables.</li>
                        <br /><a name="descuento"></a>
                        <strong><li>Los informes de pago de juegos que poseen ofertas externas limitadas deben hacerse a más tardar 30 minutos antes de que finalice la oferta <strong>(excepto durante eventos de ofertas, ver puntos 21, 22 y 23)</strong>
                        para que SteamBuy se comprometa a reservar y enviar el juego en oferta (esto no se aplica en ofertas que finalicen en la madrugada). De lo contrario, es probable que se pueda reservar, pero en caso de que no haya sido posible hacer la reserva
                         o no se haya informado el pago, o se haya informado tarde, el comprador deberá pedir un reembolso o cambio de producto. </li>
                        <li>En caso de que le corresponda al comprador un reembolso, los medios de reembolso son: Transferencia bancaria, Mercadopago, o Cuentadigital. 
                        O, en lugar, el comprador puede elegir otro/s producto/s en venta de SteamBuy que sumen el monto pagado en pesos.</li>
                        <li>En caso de solicitar un reembolso por algun problema, si el problema es evidente por parte del comprador, el monto a enviar por el reembolso será el monto pagado
                        por el comprador restando la tarifa del servicio de pagos. Si, en lugar el problema fue por parte de SteamBuy, el monto a reembolsar enviado será el monto completo que el comprador pagó.</li></strong>
                        <br/><br/>
                        <strong>Reembolsos</strong>
                        <li>El reembolso de un pedido podrá ser solicitado por el comprador (vía contacto), una vez acreditado su pago respectivo, únicamente en los siguientes casos: 
                        <br/>•Si el comprador pierde una oferta externa limitada, dejándolo con saldo a favor. 
                        <br/>•En caso de que SteamBuy no pueda brindar un producto solicitado en un pedido por causas no previstas.
                        <br/><br/>
                        No se realizan reembolsos por insatisfacción del comprador con respecto al producto o problemas de rendimiento con el mismo, ni por cualquier otro motivo a los ya nombrados, a menos que sea aceptado por consideración de SteamBuy. 
                        El comprador debe estar al tanto del producto que está comprando.
                        </li><br/><br/>
                        <strong>Eventos de ofertas</strong>
                        <li>Los eventos de ofertas son los días en los que la tienda de Steam realiza descuentos especiales masivos, tales como las ofertas de verano, las ofertas de invierno, las ofertas de primavera, etc.</li>
                        <li><strong>Durante los eventos de ofertas, debido a la alta demanda, los pedidos pueden ser enviados hasta 48 horas luego de acreditado su pago.</strong></li>
                        <li>Durante los eventos de ofertas, SteamBuy puede solicitar el informe de pago con más anticipación que lo indicado en el punto 17 para la reserva de productos en oferta, dichos tiempos son anunciados oportunamente antes de realizar la compra.</li>
                    </ol>  
            	</div>
            
            </div><!-- End main content -->
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>