$(document).ready(function(e) {
	$("#proceedbtn").click(function(e) {
		var error_text = "";
						
		$("#purchase_name").val(toTitleCase($("#purchase_name").val().trim().replace(/\s+/g, " ")));
		if($("#purchase_name").val() == 0 || $("#purchase_name").val().length < 4) {
			error_text += "<li>Ingresa tu nombre y apellido</li>";
		} else if(!/^[a-z\sñáéíóú]*$/i.test($("#purchase_name").val())) {
			error_text += "<li>Ingresa un nombre y apellido válido</li>";
		}
		if(!validateEmail($("#purchase_email").val())) {
			error_text += "<li>Ingresa un correo electrónico válido</li>";
		}
		
		if($("#tos_checkbox").length) {
			if(!$("#tos_checkbox").is(":checked")) {
				error_text += "<li>Debés aceptar los términos y condiciones para continuar</li>";	
			}
		}
						
		if(error_text == "") {
			$("#error_list").slideUp("fast");
			$.ajax({
				data: {email: $("#purchase_email").val(), product_id: $("#product_id").val()},
				url:"scripts/ajax_duplicated_order_verif.php",
				type:"post",
				
				beforeSend: function() {
					$("#proceedbtn").prop("disabled", true);
				},
				success: function(response) {
					if(response == 0) {
						if(confirm("Parece que ya realizaste un pedido por este juego a este e-mail recientemente, te recomendamos usar otro e-mail para realizar pedidos repetidos ya que Steam no permite enviar más de una misma copia a un mismo e-mail por un período de tiempo, es probable que tengamos problemas a la hora de enviar la segunda copia. ¿Deseas continuar?")) 
						{
							$("#final_form").submit();
						} else {
							$("#proceedbtn").prop("disabled", false);
						}
					} else $("#final_form").submit();
				}
			});
		} else {
			$("#error_list ul").html(error_text);
			$("#error_list").slideDown("slow");
		}			
	});
});