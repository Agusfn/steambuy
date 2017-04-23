    <div class="modal fade" id="register-modal" tabindex="-1" role="dialog" aria-labelledby="register-modal-title" aria-hidden="true">
		<div class="modal-dialog">
        	<div class="modal-content">
          		<div class="modal-header">
            		<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            		<h4 class="modal-title" id="register-modal-title">Registrarse en SteamBuy</h4>
          		</div>
          		<div class="modal-body">
          			<div class="register-form">
                    	<div style="text-align: center;margin: 0px -20px 25px;color: #666;">Regístrate en SteamBuy para poder realizar compras en el sitio, sólo toma un minuto.</div>
                    	Correo electrónico
                        <div class="form-group"><input type="text" name="email" maxlength="60" class="form-control " id="register-email" data-trigger="manual" data-content="Ingresa una dirección e-mail válida" /></div>
                        <div class="form-group clearfix">
                        	<div style="float:left;">
                            	Nombre
                                <input type="text" name="name" maxlength="17" class="form-control" id="register-name" />
                            </div>
                        	<div style="float:right;">
                            	Apellido
                                <input type="text" name="lastname" maxlength="20" class="form-control" id="register-lastname" data-trigger="manual" data-content="Ingresa tu nombre y apellido correctamente" />
                            </div>
                        </div>
                        Contraseña
                        <div class="form-group"><input type="password" autocomplete="off" maxlength="40" class="form-control" id="register-password1" data-trigger="manual" data-content="La contraseña debe tener al menos 6 caracteres, y poseer letras y símbolos o numeros" /></div>
						Repetir contraseña
                        <div class="form-group"><input type="password" autocomplete="off" maxlength="40"  class="form-control" id="register-password2" data-trigger="manual" data-content="Reingresa la contraseña correctamente" /></div>
                        <label id="register-accept-tos" style="margin-top: 9px" data-trigger="manual" data-content="Debes aceptar los términos y condiciones para registrarte"><input type="checkbox"> Acepto los <a href="<?php echo PUBLIC_URL; ?>condiciones/" target="_blank">términos y condiciones</a>.</label>
						<div class="alert alert-danger" role="alert" id="register-error">
                            <button type="button" class="close" aria-label="Close" onclick="$(this).parent('.alert').hide();"><span aria-hidden="true">&times;</span></button>
                            <span></span>
                        </div>
                        <input type="button" class="btn btn-success btn-lg" value="Crear cuenta" id="register-submit" />
                        <div id="register-loading"><i class="fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i></div>
            		</div>
                    <div class="register-success-text">
                    	<strong>Gracias por registrarte en SteamBuy!</strong> Hemos enviado un enlace de activación para tu cuenta al correo electrónico 
                        <strong><span id="register-email-validtn"></span></strong>. Una vez activada la cuenta podrás iniciar sesión y comprar en la tienda.
                    </div>
          		</div>
        	</div>
      	</div>
    </div>