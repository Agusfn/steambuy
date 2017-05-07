$(document).ready(function(e) {
	$("#proceed-btn").click(function(e) {
		var error_text = "";
						
		var buyer_name = $("#buyer-name").val();
		var buyer_email = $("#buyer-email").val();
		
		$("#buyer-name").val(toTitleCase(buyer_name.trim().replace(/\s+/g, " ")));
		if(buyer_name == 0 || buyer_name.length < 4) {
			error_text += "- Ingresa tu nombre y apellido<br/>";
		} else if(!/^[a-z\sñáéíóú]*$/i.test(buyer_name)) {
			error_text += "- Ingresa un nombre y apellido válido<br/>";
		}
		
		if(!validateEmail(buyer_email)) {
			error_text += "- Ingresa un correo electrónico válido<br/>";
		}
		
		if($("#buyer-steamurl").length) {
			var patt1 = /^(https?:\/\/)?steamcommunity.com\/id\/[a-z0-9]{1,50}(\/.*)?$/gi;
			var patt2 = /^(https?:\/\/)?steamcommunity.com\/profiles\/[0-9]{13,25}(\/.*)?$/gi;
			if( !patt1.test($("#buyer-steamurl").val()) && !patt2.test($("#buyer-steamurl").val()) ) {
				error_text += "- La SteamURL ingresada no es válida. Puedes ver tu url con <a href='http://steamidfinder.com/' target='_blank'>esta herramienta</a>.<br/>";	
			}
		}
		
		if($("#tos_checkbox").length) {
			if(!$("#tos_checkbox").is(":checked")) {
				error_text += "- Debés aceptar los términos y condiciones para continuar<br/>";	
			}
		}
						
		if(error_text == "") {
			$("#error_list").slideUp("fast");
			$("#purchase-form").submit();
		} else {
			$("#error_list p").html(error_text);
			$("#error_list").slideDown("slow");
		}			
	});
});

function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 