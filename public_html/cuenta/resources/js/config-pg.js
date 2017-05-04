$(document).ready(function(e) {
    
	$("#open-edit-name").click(function(e) {
        $("#change-name-modal").modal("show");
    });
	
	$("#open-edit-password").click(function(e) {
        $("#change-password-modal").modal("show");
    });
	
	
	$("#change-name-submit").click(function(e) {
        
		$("#user-new-name").val(formato_nombre($("#user-new-name").val()));
		$("#user-new-lastname").val(formato_nombre($("#user-new-lastname").val()));

		var name = $("#user-new-name").val();
		var lastname = $("#user-new-lastname").val();
		
		var patt = /^[A-záéíóúüñÁÉÍÓÚÜÑ ]{3,17}$/;
		if(!patt.test(name)) {
			alert("Ingresa un nombre válido");
			return;
		}
		patt = /^[A-záéíóúüñÁÉÍÓÚÜÑ ]{3,20}$/;
		if(!patt.test(lastname)) {
			alert("Ingresa un apellido válido");
			return;
		}
		
		$("#change-name-submit").prop("disabled",true);
		$.ajax({
			data: { data:"name", name: name, lastname:lastname },
			url: root_abs_path + "cuenta/resources/php/ajax-edit-user.php",
			type: "POST",
			success: function(response) {
				console.log(response);
				result = parseJSON(response);
				if(result != false) {
					
					$("#change-name-submit").prop("disabled",false);
					
					if(result["success"] == true) {
						$("#change-name-modal").modal("hide");
						$("#user-name").text(name + " " + lastname);
					} else {
						alert(result["error_text"]);	
					}
					
				} else {
					alert("Ocurrió un error intentando cambiar los datos, intenta nuevamente más tarde.");	
				}
			}
		});

    });
	
	
	$("#change-password-submit").click(function(e) {
		
		
		var old_pass = $("#user-old-pass").val();
		var new_pass = $("#user-new-pass1").val();
		
		if(old_pass == 0) {
			alert("Ingresa tu contraseña actual.");
			return;
		}

		if(new_pass.length < 6 || new_pass.length > 40 || !/[a-zA-Z]/.test(new_pass) || !/[^a-zA-Z]/.test(new_pass)) {
			alert("Ingresa una contraseña valida, de como mínimo 6 caracteres, que contenga al menos una letra y al menos un dígito o símbolo.");
			return;
		}
		if($("#user-new-pass2").val() !== new_pass) {
			alert("La contraseña no coincide.");
			return;
		}
		
		$("#change-password-submit").prop("disabled",true);
		$.ajax({
			data: { data:"password", old_password: old_pass, new_password: new_pass },
			url: root_abs_path + "cuenta/resources/php/ajax-edit-user.php",
			type: "POST",
			success: function(response) {
				console.log(response);
				result = parseJSON(response);
				if(result != false) {
					
					$("#change-password-submit").prop("disabled",false);
					
					if(result["success"] == true) {
						$("#change-password-modal").modal("hide");
					} else {
						alert(result["error_text"]);	
					}
					
				} else {
					alert("Ocurrió un error cambiando la contraseña, intenta nuevamente más tarde.");	
				}
			}
		});		
			
	});
	
	
});



function formato_nombre(nombre) {
	var mayus = nombre.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	return mayus.trim().replace(/\s+/g, " ");
}
