<?php
require_once("../../../config.php");
require_once(ROOT."app/lib/user-page-preload.php");

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
        
        <?php require_once ROOT."app/template/essential-page-includes.php"; ?>
        
        <link rel="stylesheet" href="resources/css/faq_pg.css" type="text/css">
        
		<script type="text/javascript">
		$(document).ready(function(e) {
            if(window.location.hash) {
				var anchor_str = window.location.hash.substring(1);
				$("#"+anchor_str).collapse("show");
			}
        });
		</script>
    </head>
    
    <body>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="page-title">Preguntas frecuentes</h3>
            	
                <div class="panel-group" id="accordion">
                	<?php 
					require_once("resources/faq.html"); 
					?>
				</div>
					
				
            </div><!-- End main content -->
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>

