<?php
require_once "../../../config.php";
require_once ROOT."app/lib/user-page-preload.php";


// Solo usuarios logueados
$login->restricted_page($loggedUser, 0, true);


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Configuración de cuenta - SteamBuy</title>

		<meta name="robots" content="noindex, nofollow" />

		<?php require_once ROOT."app/template/essential-page-includes.php"; ?>
		
        <link rel="stylesheet" href="../resources/css/acc-pages.css" type="text/css">
		<link rel="stylesheet" href="../resources/css/config-pg.css" type="text/css">
        
        <script type="text/javascript" src="../resources/js/config-pg.js"></script>
        
    </head>
    
    <body>
    	<div class="modal fade" id="change-name-modal" tabindex="-1" role="dialog" aria-labelledby="change-name-modal-title" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="change-name-modal-title">Modificar nombre y apellido</h4>
                    </div>
                    <div class="modal-body">
                    	<div class="form-container">
                            Nombre:
                            <input type="text" id="user-new-name" class="form-control" style="margin-bottom:10px;" value="<?php echo $loggedUser->userData["name"]; ?>" maxlength="17" />
                            Apellido:
                            <input type="text" id="user-new-lastname" class="form-control" value="<?php echo $loggedUser->userData["lastname"]; ?>" maxlength="20" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" id="change-name-submit">Aplicar</button>
                    </div>
                </div>
            </div>
       	</div>
		<div class="modal fade" id="change-password-modal" tabindex="-1" role="dialog" aria-labelledby="change-password-modal-title" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                        <h4 class="modal-title" id="change-password-modal-title">Modificar contraseña</h4>
                    </div>
                    <div class="modal-body">
                    	<div class="form-container">
                            Contraseña anterior:
                            <input type="password" id="user-old-pass" class="form-control" style="margin-bottom:10px;" maxlength="40" />
                            Nueva contraseña:
                            <input type="password" id="user-new-pass1" class="form-control" style="margin-bottom:10px;" maxlength="40" />
                            Repetir nueva contraseña:
                            <input type="password" id="user-new-pass2" class="form-control" maxlength="40" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" id="change-password-submit">Aplicar</button>
                    </div>
                </div>
        	</div>
		</div>
        
		<?php require_once(ROOT."app/template/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">


                    <ul class="nav nav-tabs" role="tablist">
                        <li><a href="../pedidos/"><span class="glyphicon glyphicon-list-alt"></span>&nbsp;&nbsp;Pedidos</a></li>
                        <li class="active"><a href=""><i class="fa fa-cog"></i>&nbsp;&nbsp;Cuenta</a></li>
                    </ul>
                    
                    <div class="tab_content">
                    
                        <table class="data_table">
                            <col width="320">
                            <col width="400">
                            <tr>
                                <td>E-mail de la cuenta:</td>
                                <td><span id="user-email"><?php echo $loggedUser->userData["email"]; ?></span></td>
                            </tr>
                            <tr>
                                <td>Nombre y apellido:</td>
                                <td><span id="user-name"><?php echo $loggedUser->userData["name"]." ".$loggedUser->userData["lastname"]; ?></span> <span style="font-size:13px">(<a href="javascript:void(0);" id="open-edit-name">editar</a>)</span></td>
                            </tr>
                            <tr>
                                <td>Contraseña:</td>
                                <td><span id="user-password">********</span> <span style="font-size:13px">(<a href="javascript:void(0);" id="open-edit-password">editar</a>)</span></td>
                            </tr>
                        </table>
                    
                    </div>
                    
                    
            </div><!-- End main content -->
            
        	<?php require_once(ROOT."app/template/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>