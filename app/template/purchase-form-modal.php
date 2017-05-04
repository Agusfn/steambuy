    	<div class="modal fade" id="game_form_modal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel" aria-hidden="true">
  			<div class="modal-dialog">
    			<div class="modal-content">
      				<div class="modal-header">
        				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        				<h4 class="modal-title" id="ModalLabel">Formulario de compra de juegos <span style="margin-left:20px;"><i class="fa fa-question question_info" data-toggle="tooltip" data-placement="bottom" title="Con este formulario podés generar boletas de pago para comprar juegos de las tiendas Steam o Amazon que no se encuentren en nuestro catálogo."></i></span></h4>
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
                            
                            <div class="alert alert-info" style="font-size: 14px;margin:15px 0; text-align:justify">
                            	Guarda estos datos para poder informar el pago luego, revisar los detalles de tu pedido, o para soporte. ID de pedido: <strong><span id="gf_tf_orderid"></span></strong>&nbsp;&nbsp;&nbsp;clave: <strong><span id="gf_tf_orderpass"></span></strong>
                            </div>
                            
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