<?php
/*
Página panel admin.
Niveles admin:
1=simple, puede ver detalles de pedidos/productos, no modificar nada.
2=puede modificar
*/

require_once("../../config.php");

require_once(ROOT."app/lib/user-page-preload.php");

$login->restricted_page($loggedUser, 1, true);


$mysql = new MysqlHelp($con);


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Panel de administracion</title>
        
		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>
		
        <link rel="stylesheet" href="resources/css/admin-panel.css" type="text/css">

    </head>
    
    <body>

		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">

				<div id="main-tab-panel">
					<ul class="nav nav-tabs" role="tablist">
                    	<li role="presentation" class="active"><a href="#pedidos" aria-controls="home" role="tab" data-toggle="tab" style="margin-left: 30px;">Pedidos</a></li>
                    	<li role="presentation"><a href="#productos" aria-controls="profile" role="tab" data-toggle="tab">Productos</a></li>
                    	<li role="presentation"><a href="#usuarios" aria-controls="messages" role="tab" data-toggle="tab">Usuarios</a></li>
                    	<li role="presentation"><a href="#configuracion" aria-controls="settings" role="tab" data-toggle="tab">Configuracion</a></li>
                  	</ul>

                  	<div class="tab-content">
                        <div role="tabpanel" class="tab-pane fade in active" id="pedidos">
                        	<?php require_once(ROOT."app/template/admin-panel/pedidos-tab.php"); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="productos">
                        	<?php require_once(ROOT."app/template/admin-panel/productos-tab.php"); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="usuarios">
                        	<?php require_once(ROOT."app/template/admin-panel/usuarios-tab.php"); ?>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="configuracion">
                        
                        </div>
                  	</div>
                
                </div>


            </div><!-- End main content -->
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>