<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}

if(isset($_GET["v"])) {
	if(is_numeric($_GET["v"])) mysqli_query($con, "UPDATE `faq` SET `visits` = `visits` + 1 WHERE `order` = ".mysqli_real_escape_string($con, $_GET["v"]).";");
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Preguntas frecuentes - SteamBuy</title>
        
        <meta name="description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes.">
        <meta name="keywords" content="steambuy,preguntas,frecuentes,lista,duda,problema">
        
        <meta property="og:title" content="Preguntas frecuentes" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/faq/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/faq/">
        <meta name="twitter:title" content="Preguntas frecuentes">
        <meta name="twitter:description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Preguntas frecuentes">
        <meta itemprop="description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/faq_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>

		<script type="text/javascript">
		
		$(document).ready(function(e) {
            if(window.location.hash) {
				var qnumber = window.location.hash.substring(1);
				if(qnumber.indexOf("c") > -1) {
					$("#"+qnumber).collapse("show");
				} else {
					$("#c"+qnumber).collapse("show");
				}
			}
        });
		
		</script>
        
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="main_title">Preguntas frecuentes</h3>
            	
                <div class="panel-group" id="accordion">
                	
                   						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c1" name="1">¿Qué es SteamBuy?</a></h4>
                            </div>
                            <div id="c1" class="panel-collapse collapse">
                                <div class="panel-body">SteamBuy es una tienda web donde encontrarás variedad de videojuegos digitales para PC y con medios de pago accesibles y alternativos. Proporcionamos ofertas y promociones de otros sitios ofreciendo venta a pedido de Steam, Amazon y otros sitios, también poseemos abundantes ofertas propias.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c2" name="2">¿Es confiable este servicio?</a></h4>
                            </div>
                            <div id="c2" class="panel-collapse collapse">
                                <div class="panel-body">Desde que comenzamos con el servicio en 2012 hemos satisfecho a miles de clientes, podrás ver testimonios de algunos de nuestros compradores en el muro de nuestra <a href="https://www.facebook.com/steambuy/posts_to_page?ref=notif&amp;notif_t=wall" target="_blank">página de Facebook</a>.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c3" name="3">¿Cuáles son los medios de pago que aceptan?</a></h4>
                            </div>
                            <div id="c3" class="panel-collapse collapse">
                                <div class="panel-body">Los medios de pago que aceptamos son los dos siguientes:<br>
<br>
<ul><li><strong>Cupón de pago:</strong> Consiste en imprimir una boleta de pago (que posee un codigo de barras único) y en abonarla en cualquiera de las sucursales de pago <strong>(Rapipago, Pago Fácil, Ripsa, Cobroexpress, Bapropagos, Cooperativa Obrera, Chubut Pagos, Provincia Pagos, Formo Pagos, PagoListo y PampaPagos)</strong> presentando esta boleta de pago. <strong>El pago es acreditado de forma automática entre las siguientes 12 y 48 horas.</strong></li><br>
<li><strong>Transferencia Bancaria:</strong> Enviando el monto determinado a nuestra cuenta bancaria, el pago se acreditará de forma instantánea en los días hábiles, y <strong>recibirás el producto hasta 12 horás después de que se acredite</strong>. Además el precio de todos los productos es menor que pagando por boleta de pago.</li><br>
<ul></ul></ul></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c4" name="4">¿Cómo compro un juego?</a></h4>
                            </div>
                            <div id="c4" class="panel-collapse collapse">
                                <div class="panel-body">Hay dos formas de comprar juegos en nuestro sitio:<br>
<br>
<ul><li><strong>Desde el catálogo:</strong> Dirigite a la página de inicio y podrás ver el catálogo de juegos, haz click en alguno y luego en el botón de "comprar juego". Allí siguiendo los pasos seleccioná la forma de pago y completá tus datos, de esta forma se generará el pedido.</li><br>
<li><strong>Desde el formulario de compra:</strong> En la pregunta siguiente explicamos cómo se utiliza el formulario.</li></ul><br>
Una vez generado tu pedido, <strong>verás las instrucciones de pago</strong> (que recibirás también por email). Si has optado pagar por boleta de pago recibirás tu boleta para imprimir y abonar en las sucursales de pago.<br>
Si optaste pagar por transferencia bancaria, recibirás los datos de la cuenta bancaria a la cual realizar la transferencia.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c5" name="5">¿Para qué sirve el formulario de compra y cómo compro un juego en él?</a></h4>
                            </div>
                            <div id="c5" class="panel-collapse collapse">
                                <div class="panel-body">Desde el <a href="../#formulario-juegos" target="_blank">formulario de compra de juegos</a> podrás comprar cualquier juego, dlc, o pack de la tienda de Steam, o cualquier juego digital de la tienda de Amazon aunque no esté en nuestro catálogo, simplemente tendrás que completar algunos datos del juego. A continuación te explicamos cómo.<br>
<br>
Para comprar ingresá <strong>tu nombre</strong>, <strong>tu e-mail</strong> (no necesariamente el de Steam) y <strong>los datos del juego</strong> en los campos en donde se los pide. Los datos del juego son: <strong>la tienda externa en donde se vende</strong> (Steam o Amazon), <strong>el link de la misma tienda</strong>, que lo podés conseguir ingresando a Steam o Amazon desde tu navegador y copiando el link del producto,<strong> el precio en USD del juego</strong> en dicha tienda, y por último indica <strong>si el juego en aquella tienda posee oferta limitada</strong>. <br>
<br>
Una vez ingresado los datos, se generará el precio final en pesos a pagar  y podrás elegir el medio de pago (por cupón de pago o transferencia bancaria). Al confirmar el medio de pago se generará el pedido y se te mostrarán las instrucciones de cómo pagar y cuánto tiempo toma.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c8" name="8">Pedí un juego que tiene una oferta limitada que está por terminar, ¿Qué hago?</a></h4>
                            </div>
                            <div id="c8" class="panel-collapse collapse">
                                <div class="panel-body">Si pediste un juego en oferta limitada y ya pagaste, <strong>asegurate de enviar una foto o scan del comprobante de pago en la sección de <a href="../informar/" target="_blank">informar pago</a></strong>, <strong>de esta manera te guardaremos el juego</strong> (a esto lo llamamos "reservar") <strong>y lo enviaremos cuando se acredita el pago</strong>. Si pagaste y la oferta terminó, <a href="mailto:contacto@steambuy.com.ar">contáctanos</a>. Si no pagaste y pensás que no llegarás a informar el pago, no lo hagas, para evitar un reembolso o cambio de producto.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c18" name="18">¿Cómo se informa el pago? ¿Para qué sirve?</a></h4>
                            </div>
                            <div id="c18" class="panel-collapse collapse">
                                <div class="panel-body"><strong>Informar el pago se trata de tomar una foto o un scan al comprobante de pago</strong> que la sucursal de pago te imprime al pagar el ticket, <strong>y subirlo en la sección de <a href="../informar/" target="_blank">informar pago</a></strong>, ingresando el ID y clave del pedido a informar el pago en cuestión, estos dos datos se envían a tu correo electrónico en el momento que se genera el pedido.<br>
<br>
Esto sirve para hacernos saber que el pago fué realizado efectivamente, y <strong>sólo es necesario en caso de que el juego pedido tenga una oferta externa que esté por terminar</strong>, de esta manera, guardaremos el juego y lo entregaremos cuando se acredite el pago.<br>
<br>
La imágen debe poder verse bien y ser legible. Este es un ejemplo de comprobante de pago:<br>
<img src="recursos/comprobante_de_pago.jpg" style="margin-top:15px"></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c20" name="20">¿En qué casos tengo que informar el pago?</a></h4>
                            </div>
                            <div id="c20" class="panel-collapse collapse">
                                <div class="panel-body">El pago se debe informar <strong>si al generar un pedido se lo pide</strong>. Precisamente, en los pedidos de ofertas <strong>externas</strong> que finalicen en cierto tiempo.<br>
Los pagos de pedidos de juegos en stock no deben ser informados ya que no son ofertas limitadas externas, y al generar el pedido, una copia del stock de reserva a la persona que lo generó.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c11" name="11">Ya pagué el pedido, ¿cuándo envían el producto?</a></h4>
                            </div>
                            <div id="c11" class="panel-collapse collapse">
                                <div class="panel-body">Si pagaste <strong>por medio de cupón de pago, el pago toma entre 12 y 48 horas en acreditarse</strong>, <strong>nos comprometemos a enviar todos los productos</strong> (juegos, items, dlcs o saldo PayPal) <strong>el día en que se acredita el pago</strong>, por lo general a la mañana o mediodía.<br>
<br>
Si pagaste por transferencia bancaria, el pago se acreditará únicamente en horario hábil y es de forma instantánea (si se envía en horario no hábil, se deberá esperar a que se acredite). <strong>Enviamos los productos dentro de las siguientes 12 horas de acreditada</strong> la transferencia o depósito.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c13" name="13">¿Cómo puedo saber el estado de mi pago? ¿Está acreditado?</a></h4>
                            </div>
                            <div id="c13" class="panel-collapse collapse">
                                <div class="panel-body">Si pagaste por medio de una boleta de pago, al generar el pedido <strong>debiste recibir un e-mail de cuentadigital.com con un enlace el cual te indica el estado de tu pago</strong>.<br>
<br>
O sinó podés ingresar al siguiente <a href="https://www.cuentadigital.com/area.php?name=Search" target="_blank">link</a> y escribir el número del código de barras en el campo del código. <strong>Hacé click en "Buscar" y te dirá el estado de tu pago.</strong></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c6" name="6">¿Cómo calculo el precio en pesos por un juego que quiero comprar? (Calculadora de precios)</a></h4>
                            </div>
                            <div id="c6" class="panel-collapse collapse">
                                <div class="panel-body">Si el juego deseado no está en el catálogo, <strong>la forma de saber el precio de un juego</strong> (que se puede pedir desde el formulario de compra) <strong>es usando la calculadora de precios</strong>. Esta calculadora se encuentra en la página principal en la columna derecha, debajo de los botones de informar y cancelar pago.<br>
Simplemente, <strong>ingresando el precio en dólares del juego</strong> que estés interesado (sea de Steam o Amazon), <strong>la calculadora te dará el precio final en pesos a pagar por el mismo</strong> (por medio de cupón de pago, por transferencia bancaria el precio es menor).</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c7" name="7">¿En qué formato se envían los juegos?</a></h4>
                            </div>
                            <div id="c7" class="panel-collapse collapse">
                                <div class="panel-body">Los juegos se envían en cualquiera de los siguientes formatos: <strong>regalo de Steam, clave de activación, o link de activación de Humble Bundle</strong>. Los 3 formatos son completamente seguros y permanentes a la hora de activar un juego en la cuenta de Steam o Amazon, según sea el formato.<br>
</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c9" name="9">La oferta del juego que pedí finalizó y ya lo pagué, y no pude informar el pago, ¿Qué hago?</a></h4>
                            </div>
                            <div id="c9" class="panel-collapse collapse">
                                <div class="panel-body">Tendrás que <strong>elegir otros juegos o un reembolso</strong>, <a href="mailto:contacto@steambuy.com.ar">contáctanos</a> informando tu problema. Esto sucede porque <strong>si la oferta finaliza y no reservamos el juego, no lo tendremos para entregartelo</strong>. Por eso requerimos que si se pide un juego en oferta, <strong>se esté seguro que se podrá pagar e informar el pago a tiempo, para que lo reservemos</strong>.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c10" name="10">Recibí un mail diciendo que el juego fue enviado, pero no está en mi cuenta Steam, ¿Cómo lo activo?</a></h4>
                            </div>
                            <div id="c10" class="panel-collapse collapse">
                                <div class="panel-body">Algunos juegos se envían en formato "regalo de Steam", <strong>que consiste en un mensaje de e-mail</strong> con el remitente de "Steam Store". <strong>Dentro de este mensaje se encuentran las instrucciones y un botón para activar el juego en tu cuenta de Steam</strong>.<br>
<br>
<strong>En la siguiente imágen se muestra cómo activar un juego a modo de ejemplo:</strong><br>
<br>
<a href="recursos/tutorial_activacion_gift.jpg" target="_blank"><img src="recursos/tutorial_activacion_gift.jpg" style="width:927px;"></a></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c12" name="12">Pasaron 1 o 2 días desde que pagué y no recibí mi juego, ¿Cuándo me llegará?</a></h4>
                            </div>
                            <div id="c12" class="panel-collapse collapse">
                                <div class="panel-body">Si pagaste por medio de cupón de pago, tené en cuenta que <strong>sólo las cadenas de pago Rapipago y Pago Fácil</strong> toman entre 12 y 48 hs <strong>incluyendo fines de semana y feriados</strong> en acreditar el pago. <strong>El resto de las cadenas</strong> toma entre 12 y 48 horas <strong>hábiles</strong>, es decir que si es fin de semana y pagaste por otra cadena, es probable que la "demora" sea porque aquella cadena de pagos no trabaje los fines de semana.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c17" name="17">¿Qué significa cada bandita de "Oferta" en el catálogo?</a></h4>
                            </div>
                            <div id="c17" class="panel-collapse collapse">
                                <div class="panel-body">En nuestro sitio ofrecemos distintos juegos y distintas ofertas de diferentes tiendas y las distinguimos con estas banditas de oferta. El ícono dentro de cada bandita describe qué tipo de oferta es (ej: oferta de steam, oferta de Amazon, oferta de humble bundle). <br>
<strong>Las banditas de oferta color azul sin ícono se tratan de ofertas propias de SteamBuy.</strong></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c19" name="19">¿Qué son el ID y clave de pedido?</a></h4>
                            </div>
                            <div id="c19" class="panel-collapse collapse">
                                <div class="panel-body">Al generar un pedido, sea para juegos o para saldo PayPal, te proporcionamos el ID de pedido (un número antepuesto por una letra) que identifica el número de pedido, y una clave que consiste en caracteres aleatorios. <br>
<br>
<strong>Estos dos datos son enviados por e-mail al generar un pedido </strong> y son indispensables, se deben guardar y no compartir, ya que es la forma que utilizamos para que los clientes demuestren la propiedad de los pedidos en trámites respecto a los mismos.<br>
</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c21" name="21">¿Cómo puedo comprar juegos de la tienda de Origin?</a></h4>
                            </div>
                            <div id="c21" class="panel-collapse collapse">
                                <div class="panel-body">No vendemos juegos directamente de la tienda de Origin, pero afortunadamente en la <a href="http://www.amazon.com/Game-Downloads/b/ref=nav_shopall_gdown?ie=UTF8&amp;node=979455011" target="_blank">tienda de juegos de Amazon</a> hay una gran variedad de juegos, <strong>tanto para Steam como para Origin</strong>, aquí usando nuestro <a href="../#formulario-juegos" target="_blank">formulario de compra</a> podrás pedir juegos de Origin siguiendo las instrucciones del formulario de compra, y los recibirás como <strong>claves de activación</strong>.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c22" name="22">Hice un pedido por un juego y después el juego en el catálogo cambió su precio o desapareció, ¿Lo recibiré?</a></h4>
                            </div>
                            <div id="c22" class="panel-collapse collapse">
                                <div class="panel-body">Lo más probable es que el juego que pediste estuvo en stock y este se agotó, haciendo que su precio de oferta finalice y vuelva el precio regular, o finalice su disponibilidad.<br>
<strong>Si ya hiciste el pedido, una copia del stock se reserva exclusivamente para vos hasta que expira el pedido, por lo tanto no hay de qué preocuparse, tenés 5 días para pagar el juego.</strong></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c23" name="23">Informé el pago y no recibí el e-mail de que mi juego fué reservado, ¿Recibiré el juego?</a></h4>
                            </div>
                            <div id="c23" class="panel-collapse collapse">
                                <div class="panel-body">Lo más probable es que quede tiempo suficiente de oferta y no reservemos el juego por cuestiones de tiempo y prioridad. Cuando queda relativamente poco tiempo y el pago aún no se acreditó es cuando reservamos los juegos.<br>
<br>
<strong>Informar el pago correctamente y a tiempo garantiza que recibirás el juego en oferta como corresponde.</strong></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c24" name="24">Recibí una clave de activación por e-mail. ¿Qué son? ¿Cómo activo el juego/pack para tenerlo en mi cuenta de Steam/Origin?</a></h4>
                            </div>
                            <div id="c24" class="panel-collapse collapse">
                                <div class="panel-body">Una clave de activación consiste en una combinación única de caracteres, una vez que se utiliza en una cuenta, el código queda inutilizable y la cuenta adquiere el producto nuevo.<br>
<br>
Tanto Steam como Origin tienen la posibilidad de activar juegos por este método.<br>
Para activar una clave de producto en <strong>Steam</strong> sigue estas instrucciones:<br>
<br>
<ol><li>Ejecuta Steam e inicia sesión en tu cuenta.</li><br>
<li>Haz clic en el menú <strong>Juegos</strong>.</li><br>
<li>Elige <strong>Activar un producto en Steam...</strong></li><br>
<li>Ingresa la clave de producto y sigue las instrucciones que aparecerán en pantalla para completar el proceso.</li></ol><br>
<br>
Para activar una clave de producto en <strong>Origin</strong> sigue estas otras instrucciones:<br>
<br>
<ol><li>Inicia sesión en Origin con la cuenta a la que deseas añadir el juego.</li><br>
<li>Haz clic en el menú Origin en la parte superior izquierda.</li><br>
<li>Selecciona <strong>"Canjear código de producto"</strong>.</li><br>
<li>Introduce el código de producto.</li></ol><br>
El juego se añadirá a la sección "Mis juegos" de Origin.</div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c25" name="25">¿Cómo activo un pack/juego de HumbleBundle en Steam?</a></h4>
                            </div>
                            <div id="c25" class="panel-collapse collapse">
                                <div class="panel-body">Es muy simple, cuando recibas el link por parte de SteamBuy, ingresá, escribí tu e-mail y recibirás otro e-mail de Humble Bundle, ingresa al link de ese mensaje, asocia tu cuenta de Steam y listo.<br>
<br>
En el siguiente diagrama explicamos gráficamente cómo hacer esto:<br>
<br>
<a href="recursos/activacion_humblebundle.jpg" target="_blank"><img alt="tutorial activación humble bundle" src="recursos/activacion_humblebundle.jpg" width="926"></a></div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c26" name="26">¿Cómo se calculan los precios? ¿Por qué veo el precio en pesos y el precio en dólares en el catálogo? ¿En qué moneda se venden los juegos?</a></h4>
                            </div>
                            <div id="c26" class="panel-collapse collapse">
                                <div class="panel-body"><strong>Todos los productos los vendemos con el precio final en pesos argentinos (ARS).</strong><br>
<br>
En el catálogo de juegos utilizamos el precio en dólares como referencia, por ejemplo <strong>si el juego se trata de una reventa</strong> (venta directamente desde Steam o Amazon con el precio en USD que posee en esa tienda) usamos el precio en USD de dicha tienda, y <strong>a este se le aplica un cálculo</strong> para pasarlo a pesos, sumarle impuestos, tarifas de servicio de pago, y una comisión. Por esto es que no se debe asumir esa conversión como una conversión oficial del dólar.<br>
Este cálculo es el mismo que se usa en la <strong>calculadora de precios</strong>. </div>
                            </div>
              			</div>
                        						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#c27" name="27">¿Por qué los packs de Humble Bundle en venta son más caros que los demás juegos en cuanto a la conversión de precio?</a></h4>
                            </div>
                            <div id="c27" class="panel-collapse collapse">
                                <div class="panel-body">Humble Bundle impone una limitación muy estricta en la adquisición de estos packs, y al haber poca disponibilidad y mucha demanda, el producto posee más importancia y valor.</div>
                            </div>
              			</div>


				</div>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>

