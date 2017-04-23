    <div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="login-modal-title" aria-hidden="true">
		<div class="modal-dialog">
        	<div class="modal-content">
          		<div class="modal-header">
            		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            		<h4 class="modal-title" id="login-modal-title">Iniciar sesión</h4>
          		</div>
          		<div class="modal-body">
          			<form action="<?php echo PUBLIC_URL; ?>cuenta/login.php" method="post" id="login-form">
                    	<div style="text-align:center;margin-bottom:25px;">¿No tienes cuenta? <a href="javascript:void(0);" id="login-swap-register">Regístrate</a></div>
                        Correo electrónico
                        <div class="form-group"><input type="text" name="email" maxlength="60" class="form-control" id="login-email" data-trigger="manual" data-content="Ingresa un e-mail válido" /></div>
                        Contraseña
                        <div class="form-group"><input type="password" name="password" maxlength="40" class="form-control" id="login-password" data-trigger="manual" data-content="Ingresa la contraseña" /></div>
                        <label><input type="checkbox" name="save-credentials"> No cerrar sesión</label>
                        <input type="hidden" name="redir" value="<?php echo $_SERVER["REQUEST_URI"]; ?>" />
						<div class="alert alert-danger" role="alert" id="login-error">
                            <button type="button" class="close" aria-label="Close" onclick="$(this).parent('.alert').hide();"><span aria-hidden="true">&times;</span></button>
                            <span></span>
                        </div>
                        <input type="button" class="btn btn-primary btn-lg" value="Ingresar" id="login-submit" />
                        <div id="login-loading"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
            		</form>
                    
                    
                    <div class="login-recover-form">
                    	<div class="recover-instructions">Ingresa la dirección e-mail de tu cuenta de SteamBuy y recibirás un mensaje con las instrucciones para recuperarla.</div>
                    	Correo electrónico
                        <input type="text" name="email" class="form-control" id="login-recover-email" style="margin-bottom:7px;" />
						<input type="button" class="btn btn-success" value="Recuperar cuenta" id="recover-submit" />
                        <!--div class="alert alert-danger error_list" id="ml_recover_error_list"><span class="glyphicon glyphicon-remove" onClick="$(this).parent('.error_list').slideUp('slow');"></span><span></span></div-->
                    </div>
                    <div class="recover-success">Se ha enviado un mensaje con las instrucciones de recuperación a tu correo electrónico, <strong>si no encuentras el mensaje revisa en la carpeta de spam</strong>. La solicitud expirará dentro de 2 horas.</div>
                    <a href="javascript:void(0);" id="swap-login-recover">¿Olvidaste tu contraseña? Hacé click aquí</a>
          		</div>
        	</div>
      	</div>
    </div>