var loginrecover = 0;
var login_captcha_loaded = false, need_login_captcha = false;

$(document).ready(function(e) {

	/* LOGIN */

	$("#login-swap-register").click(function(e) {
        $("#login-modal").modal("hide");
		$("#register-modal").modal("show");
    });
	
	$("#swap-login-recover").click(function(e) {
        swapLoginRecover();
    });
	
	$("#login-form input").focus(function(e) {
        if($(this).hasClass("input-has-error")) {
			$(this).removeClass("input-has-error");
			$(this).popover("hide");
		}
    });
	
	$("#login-form input").keydown(function(e) {
        if(e.keyCode == 13) $("#login-submit").trigger("click");	
    });

	
	$("#login-submit").click(function(e) {

		datos = validateLoginForm();
		if(datos !== false) {
			
			set_login_loading_state(true);
			
			$.ajax({
				data: datos,
				url: root_abs_path + "resources/php/ajax-login.php",
				type: "POST",
				success: function(response) {

					console.log(response);
					result = parseJSON(response);
					var error = false;
					
					if(result != false) {
						
						if(result["success"] == true) {
							$("#login-form").submit();
						} else {
							if(result["needs_captcha"] == true) {
								set_login_loading_state(false);	
								need_login_captcha = true;
								displayLoginCaptcha();
							} else {
								error = result["error_text"];
							}
						}
						
					} else error = "Ocurrió un error realizando la solicitud, intenta nuevamente más tarde.";

					if(error != false) {
						set_login_loading_state(false);	
						$("#login-error > span").text(error);
						$("#login-error").show();
						resetLoginCaptcha();
					}
				
				}
			});
		}
    });
	
	/* RECOVER */
	
	
	$("#recover-submit").click(function(e) {
        var email = $("#login-recover-email").val();
		if(email.length > 60 || !valid_email(email)) {
			alert("Ingresa una dirección e-mail válida");
			return;	
		}
		
		$("#login-recover-email, #recover-submit").prop("disabled", true);
		$("#login-modal").css("cursor", "wait");
		
		$.ajax({
			data: {user_email:email},
			url: root_abs_path + "resources/php/ajax-recover-user.php",
			type: "POST",
			success: function(response) {
				
				console.log(response);
				
				$("#login-recover-email, #recover-submit").prop("disabled", false);
				$("#login-modal").css("cursor", "default");
				
				result = parseJSON(response);
				if(result != false) {
					if(result["success"] == true) {
						alert("Se ha enviado un e-mail con las instrucciones de recuperación al correo electrónico indicado.");
					} else {
						alert(result["error_text"]);
					}
				} else {
					alert("Ocurrió un error realizando la solicitud, intenta nuevamente más tarde.");
				}
			}
		});
		
    });

	/* REGISTER */

	$(".register-form input").focus(function(e) {
        if($(this).hasClass("input-has-error")) {
			$(this).removeClass("input-has-error");
			$(this).popover("hide");
			if($(this).is("#register-name")) {
				$("#register-lastname").popover("hide");
			}
		}
    });
	$("#register-accept-tos input").change(function(e) {
        $("#register-accept-tos").popover("hide");
    });	
	
	$("#register-submit").click(function(e) {
		datos = validate_register_form();
		if(datos !== false) {
		
			set_register_loading_state(true);
			
			$.ajax({
				data: datos,
				url: root_abs_path + "resources/php/ajax-register-user.php",
				type: "POST",
				success: function(response) {
					
					result = parseJSON(response);
					if(result != false) {
						if(result["error"] == 0) {
							change_register_form();
							$("#register-email-validtn").text(datos["email"]);
						} else {
							$("#register-error > span").text(result["error_text"]);
							$("#register-error").show();
						}
					} else {
						$("#register-error > span").text("Ocurrió un error realizando la solicitud, intenta nuevamente más tarde.");
						$("#register-error").show();
					}
					set_register_loading_state(false);
				}
			});
			
		}
    });



	
});

/* LOGIN */
function set_login_loading_state(state) {
	if(state) {
		$("#login-modal").data('bs.modal').isShown = false;
		$("#login-modal input, #login-modal button").prop("disabled", true);
		$("#login-loading").show();
	} else {
		$("#login-modal").data('bs.modal').isShown = true;
		$("#login-modal input, #login-modal button").prop("disabled", false);
		$("#login-loading").hide();
	}
}

function validateLoginForm() {
	
	$("#login-error").hide();
	
	var error = false;
	
	var email = $("#login-email").val();
	var password = $("#login-password").val();
	
	if(email.length > 60 || !valid_email(email)) {
		$("#login-email").popover("show").addClass("input-has-error");
		error = true;
	}
	
	if(password.length == 0) {
		$("#login-password").popover("show").addClass("input-has-error");
		error = true;
	}
	
	if(need_login_captcha) {
		var captchaKey = grecaptcha.getResponse(loginCaptcha);
		if(captchaKey.length == 0) {
			error = true;
			alert("Completa el captcha para continuar");
		}
	}
	
	if($("#login-keep-logged").is(":checked")) keep_loggedin = 1;
	else keep_loggedin = 0;
	
	if(error) {
		return false;
	} else {
		$("#login-form input").removeClass("input-has-error").popover("hide");
		if(need_login_captcha) {
			return {email:email, password:password, keep_loggedin:keep_loggedin, captcha_key:captchaKey};
		} else {
			return {email:email, password:password, keep_loggedin:keep_loggedin};
		}
			
	}

}

function displayLoginCaptcha() {
	$(".login-captcha").show();
	if(!login_captcha_loaded) {
		set_login_loading_state(true);
		$.getScript("https://www.google.com/recaptcha/api.js?onload=captchaCallback&render=explicit", function() {
			set_login_loading_state(false);
			login_captcha_loaded = true;
		});	
	}
}

var captchaCallback = function() {
	loginCaptcha = grecaptcha.render('g-recaptcha', {
          'sitekey' : '6LcaKx4UAAAAAMYRtPdxbMOT1JkGeDywhnB8lYko'
    });
}

function resetLoginCaptcha() {
	
	// Sólo si se viene de enviar un formulario que necesitó uso de captcha se resetea, sino no.
	
	if(need_login_captcha) {
		need_login_captcha = false;
		$(".login-captcha").hide();
		if(login_captcha_loaded) {
			grecaptcha.reset(loginCaptcha) 
		}
	}
}



function swapLoginRecover() {
	if(loginrecover == 0) {
		loginrecover = -1;
		$("#login-modal-title").text("Recuperar cuenta y cambiar contraseña");
		$("#swap-login-recover").text("Volver");
		$("#login-form").fadeOut("fast", function() {
			loginrecover = 1;
			$(".login-recover-form").fadeIn("fast");

		});
	} else if(loginrecover == 1) {
		loginrecover = -1;
		$("#login-modal-title").text("Iniciar sesión");
		$("#swap-login-recover").text("¿Olvidaste tu contraseña? Hacé click aquí");
		$(".login-recover-form").fadeOut("fast", function() {
			loginrecover = 0;
			$("#login-form").fadeIn("fast");
			//if($(".ml_recover_success").is(":visible")) $(".ml_recover_success").hide();
		});
	}
}




/* REGISTER */

/* Funcion para bloquear/desbloquear el modal de registro cuando carga
*/
function set_register_loading_state(state) {
	if(state) {
		$("#register-loading").show();
		$("#register-modal").data('bs.modal').isShown = false;
		$(".register-form input, .register-form button").prop("disabled", true);
	} else {
		$("#register-loading").hide();
		$("#register-modal").data('bs.modal').isShown = true;
		$(".register-form input, .register-form button").prop("disabled", false);
	}
}

/* Validar datos de formulario de registro. 
*/
function validate_register_form() {
	
	var error = false;	

	var email = $("#register-email").val();
	if(email.length > 60 || !valid_email(email)) {
		$("#register-email").popover("show").addClass("input-has-error");
		error = true;
	}
	
	
	$("#register-name").val(formato_nombre($("#register-name").val()));
	$("#register-lastname").val(formato_nombre($("#register-lastname").val()));
	
	var name = $("#register-name").val();
	var lastname = $("#register-lastname").val();
	var patt = /^[A-záéíóúüñÁÉÍÓÚÜÑ ]{3,17}$/;
	if(!patt.test(name)) {
		$("#register-name").addClass("input-has-error");
		$("#register-lastname").popover("show");
		error = true;
	}
	patt = /^[A-záéíóúüñÁÉÍÓÚÜÑ ]{3,20}$/;
	if(!patt.test(lastname)) {
		$("#register-lastname").addClass("input-has-error").popover("show");
		error = true;
	}
	
	
	var pass = $("#register-password1").val();
	if(pass.length < 6 || pass.length > 40 || !/[a-zA-Z]/.test(pass) || !/[^a-zA-Z]/.test(pass)) {
		$("#register-password1").addClass("input-has-error").popover("show");
		error = true;
	}
	if($("#register-password2").val() !== pass) {
		$("#register-password2").addClass("input-has-error").popover("show");
		error = true;
	} else $("#register-password2").removeClass("input-has-error").popover("hide");
	
	
	if(!$("#register-accept-tos input[type='checkbox']").is(":checked")) {
		$("#register-accept-tos").popover("show");
		error = true;
	}
	
	if(error) return false;
	else {
		return {email:email, name:name, lastname:lastname, password:pass}	
	}	
}


function change_register_form() {
	$(".register-form").fadeOut(300, "linear", function() {
		$(".register-success-text").fadeIn(300);
	});
}





// Función para formatear nombres propios sin espacios innecesarios y con iniciales en mayuscula
function formato_nombre(nombre) {
	var mayus = nombre.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	return mayus.trim().replace(/\s+/g, " ");
}


function valid_email(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 


