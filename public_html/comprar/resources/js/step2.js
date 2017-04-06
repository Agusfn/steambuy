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
		
		if($("#tos_checkbox").length) {
			if(!$("#tos_checkbox").is(":checked")) {
				error_text += "- Debés aceptar los términos y condiciones para continuar<br/>";	
			}
		}
						
		if(error_text == "") {
			$("#error_list").slideUp("fast");
			$.ajax({
				data: {email: buyer_email, product_id: $("#product-id").val()},
				url:"resources/php/ajax-dup-order-check.php",
				type:"post",
				
				beforeSend: function() {
					$("#proceed-btn").prop("disabled", true);
				},
				success: function(response) {
					if(response == 0) {
						if(confirm("Parece que ya realizaste 2 o más pedidos por este juego a este e-mail recientemente, te recomendamos usar otro e-mail para realizar pedidos repetidos ya que Steam no permite enviar más de una misma copia a un mismo e-mail por un período de tiempo. ¿Deseas continuar?")) 
						{
							$("#purchase-form").submit();
						} else {
							$("#proceed-btn").prop("disabled", false);
						}
					} else $("#purchase-form").submit();
				}
			});
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