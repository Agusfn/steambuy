<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Soporte - SteamBuy</title>
        
        <meta name="description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos.">
        <meta name="keywords" content="steambuy,comprar,duda,consulta,ayuda,soporte,contacto">
        
        <meta property="og:title" content="Soporte" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/soporte/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/soporte/">
        <meta name="twitter:title" content="Soporte">
        <meta name="twitter:description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Soporte">
        <meta itemprop="description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">


        <link rel="shortcut icon" href="../favicon.ico"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/support_pg.css?2" type="text/css">

		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
		<script type="text/javascript" src="scripts/supportpg_js.js?2"></script>
        
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
            	<h3 class="main_title">¿Alguna consulta o problema? Consultá aquí mismo</h3>
                <div style="height:427px;margin:20px 0;">
                    <div class="left_column">
                        <div style="font-size:16px;">Con el motivo de mantener un servicio ágil y evitar hacerte esperar una respuesta, te pedimos que revises tu consulta en las preguntas frecuentes
                        antes de contactarnos:</div>
                        
                        <input type="text" class="form-control" id="search_input" placeholder="Escribe tu consulta o palabras clave individuales de la misma" />
                        <i class="fa fa-spinner fa-spin fa-lg" id="load_icon"></i>
                        <div id="result_box"></div>
                    </div>
                    <div class="right_column">
                    	<div style="font-size:17px;color: rgba(38, 126, 25, 1);">¿No encontraste una respuesta? Envianos tu consulta por alguno de los siguientes medios:</div>
                    	<ul class="contact_list">
                        	<li><span class="glyphicon glyphicon-envelope"></span> E-mail: <a href='mailto:contacto@steambuy.com.ar'>contacto@steambuy.com.ar</a></li>
                        	<li><i class="fa fa-facebook"></i> Facebook: <a href="http://facebook.com/steambuy">http://facebook.com/steambuy</a></li>
                        </ul>
                        
                        <div style="margin-top:45px;">Preguntas más consultadas:</div>
                        <div class="frequented_questions">
                        	<?php
							$res = mysqli_query($con, "SELECT * FROM faq ORDER BY visits DESC LIMIT 20");
							while($faq = mysqli_fetch_assoc($res)) {
								echo "<div class='fq_question'><a href='../faq/#".$faq["number"]."' target='_blank'>".$faq["question"]."</a></div>";
							}
							?>
                        </div>
                    </div>
            	</div>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>