<?php
/*
Template usado para mostrar mensajes de error/exito en diferentes circunstancias. Podrá usarse a futuro para mostrar todas las páginas del sitio?
*/
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title><?php echo $page_title; ?></title>

		<meta name="robots" content="noindex, nofollow" />

		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>
  
    </head>
    
    <body>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
				<?php echo $content_html; ?>
            </div>
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div>
    </body>
    
    
</html>