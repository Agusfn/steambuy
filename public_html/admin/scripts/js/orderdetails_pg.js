$(document).ready(function(e) {
	
	$("#reject_inform").tooltip();
	
	$('input:radio[name=expiration_type]').val(['time']);
	
	var notify_email = true;
	
	// Modals
	
	$("input[name='expiration_type']").change(function() {
		if($(this).attr("value") == "time") {
			$(".inform_status_box, #offer_end_time").hide();
			$("#reject_inform_reason").hide();
		} else if($(this).attr("value") == "offer_end") {
			if($('input[name="inform_status"]:checked').val() == "invalid_inform") {
				$("#reject_inform_reason").show();
			}
			$(".inform_status_box, #offer_end_time").show();
		}
		$("#expire_modal_submit").attr("disabled",false);
	});
	
	$("input[name='inform_status']").change(function() {
		if($(this).attr("value") == "no_inform") {
			$("#reject_inform_reason").hide();
		} else if($(this).attr("value") == "invalid_inform") {
			$("#reject_inform_reason").show();
		} else if($(this).attr("value") == "late_inform") {
			$("#reject_inform_reason").hide();
		}
	});	

	
	$("#expire_modal_submit").click(function(e) {
		
		if(!confirm("Continuar?")) {
			return;
		}
		/*
		action=2,
		reason=
		exp_type:1,2
		inform_status:1,2,3
		offer_endtime
		reject_reason
		*/
        var exp_type = $('input[name="expiration_type"]:checked').val();

		if(exp_type == "time") {
			var data = {"exp_type":"1"};
			$("#input_action").val(2);
			$("#input_data").val(JSON.stringify(data));
			$("#main_form").submit();
		} else if(exp_type == "offer_end") {
			
			inform_status = $('input[name="inform_status"]:checked').val();
			
			if(inform_status == "no_inform") {
				var data = {"exp_type":"2", "inform_status":"1", "offer_endtime":$("#offer_end_time input").val()};
				$("#input_action").val(2);
				$("#input_data").val(JSON.stringify(data));
				$("#main_form").submit();
			} else if(inform_status == "invalid_inform") {
				if($("#reject_inform_reason input").val() == 0) {
					alert("Ingresa un motivo de rechazo de informe de pago.");
				} else {
					var data = {"exp_type":"2", "inform_status":"2", "offer_endtime":$("#offer_end_time input").val(), "reject_reason":$("#reject_inform_reason input").val()};
					$("#input_action").val(2);
					$("#input_data").val(JSON.stringify(data));
					$("#main_form").submit();
				}
			} else if(inform_status == "late_inform") {
				if($("#offer_end_time input").val() == 0) {
					alert("Ingresa la fecha de fin de oferta.");
				} else {
					var data = {"exp_type":"2", "inform_status":"3", "offer_endtime":$("#offer_end_time input").val()};
					$("#input_action").val(2);
					$("#input_data").val(JSON.stringify(data));
					$("#main_form").submit();
				}
			} else {
				alert("Seleccioná una opción de estado de informe de pago");	
			}
			
		}
		

    });
	
	
	$("#opt_changeorder").click(function(e) {
		if($(".new_name_field input").val() == 0) {
			alert("Ingresa un nombre");
			return;	
		} else if($(".new_price_field input").val() != 0 && isNaN($(".new_price_field input").val())) {
			alert("Ingresa un precio correcto");
			return;	
		}
		if(confirm("¿Cambiar pedido?")) {
			var data = {"change_type":"1", "new_product_name":$(".new_name_field input").val(), "new_order_price":$(".new_price_field input").val()};			
			if(orderstatus == 1) { // Si el pedido está activo
				$("#input_action").val(3);
				$("#input_data").val(JSON.stringify(data));
			} else if(orderstatus == 3) { // Si está cancelado
				var data = {"new_product_name":$(".new_name_field input").val(), "new_order_price":$(".new_price_field input").val()};			
				$("#input_action").val(9);
				$("#input_data").val(JSON.stringify(data));
			}
			$("#main_form").submit();
		}	
    });
	
	
	$("#opt_sendkeys").click(function(e) {
        if($("#game_keys").val() == 0) {
			alert("Ingresa las keys/links");
			return;	
		}
		if(confirm("¿Enviar keys/links?")) {	
			$("#input_action").val(6);
			var data = {"product_keys":$("#game_keys").val()};
			$("#input_data").val(JSON.stringify(data));
			$("#main_form").submit();
		}
    });
	
	// Fin modals
		
	
	$("#opt_cancelorder").click(function(e) {
        var reason = prompt("Motivo de cancelación:");
		if(reason != null) {
			if(reason == 0) {
				alert("Ingresa un motivo");
				return;
			}
			var data = {"cancel_reason":reason};
			$("#input_action").val(1);
			$("#input_data").val(JSON.stringify(data));
			$("#main_form").submit();
		}
		
    });

	$("#change_buyer_email").click(function(e) {
        var new_email = prompt("Nuevo e-mail");
		if(new_email != null) {
			if(new_email == 0) {
				alert("Ingresa una dirección e-mail");
				return;	
			}
			var data = {"change_type":"2", "new_buyer_email":new_email};
			$("#input_action").val(3);
			$("#input_data").val(JSON.stringify(data));
			$("#main_form").submit();
		}
    });
	$("#change_buyer_name").click(function(e) {
        var new_name = prompt("Nuevo nombre");
		if(new_name != null) {
			if(new_name == 0) {
				alert("Ingresa un nombre");
				return;	
			}
			var data = {"change_type":"3", "new_buyer_name":new_name};
			$("#input_action").val(3);
			$("#input_data").val(JSON.stringify(data));
			$("#main_form").submit();
		}
    });



	$("#opt_reserveorder").click(function(e) {
        if(confirm("¿Marcar como reservado?")) {
			$("#input_action").val(4);
			$("#main_form").submit();	
		}
    });
	
	
	$("#opt_concreteorder").click(function(e) {
        if(confirm("¿Marcar como concretado?")) {
			$("#input_action").val(5);
			$("#main_form").submit();	
		}
    });

	
	$("#opt_reactivateorder").click(function(e) {
        if(confirm("¿Reactivar pedido?")) {
			$("#input_action").val(8);
			$("#main_form").submit();
		}
    });
	
	
	$("#opt_toggle_notify").click(function(e) {
        if(notify_email == true) {
			notify_email = false;
			$(this).removeClass("active");
			$(this).attr("title", "Informar por e-mail: Inactivo").tooltip("fixTitle").tooltip("show");
			$("#input_notify").val("0");
		} else if(notify_email == false) {
			notify_email = true;
			$(this).addClass("active");
			$(this).attr("title", "Informar por e-mail: Activo").tooltip("fixTitle").tooltip("show");
			$("#input_notify").val("1");
		}
    });
	
	$("#reject_inform").click(function(e) {
        var reason = prompt("Motivo de rechazo de informe de pago:");
		if(reason != null) {
			if(reason == 0) {
				alert("Ingresa un motivo");
				return;
			}
			var data = {"reject_reason":reason};
			$("#input_action").val(7);
			$("#input_data").val(JSON.stringify(data));
			$("#main_form").submit();
		}
    });	


	$("#copy_name_btn").click(function(e) {
        copyTextToClipboard($("#client_first_name").val());
    });
	
	$("#copy_order_data").click(function(e) {
        copyTextToClipboard($("#client_email").text() + "|" + $("#order_id").text() + "|" + $("#client_first_name").val());
    });
	
	$("#copy_email_btn").click(function(e) {
        copyTextToClipboard($("#client_email").text());
    });
	
	$("#copy_steamurl_btn").click(function(e) {
        copyTextToClipboard($("#client_steamurl").val());
    });	
	
});


function copyTextToClipboard(text) {
  var textArea = document.createElement("textarea");

  //
  // *** This styling is an extra step which is likely not required. ***
  //
  // Why is it here? To ensure:
  // 1. the element is able to have focus and selection.
  // 2. if element was to flash render it has minimal visual impact.
  // 3. less flakyness with selection and copying which **might** occur if
  //    the textarea element is not visible.
  //
  // The likelihood is the element won't even render, not even a flash,
  // so some of these are just precautions. However in IE the element
  // is visible whilst the popup box asking the user for permission for
  // the web page to copy to the clipboard.
  //

  // Place in top-left corner of screen regardless of scroll position.
  textArea.style.position = 'fixed';
  textArea.style.top = 0;
  textArea.style.left = 0;

  // Ensure it has a small width and height. Setting to 1px / 1em
  // doesn't work as this gives a negative w/h on some browsers.
  textArea.style.width = '2em';
  textArea.style.height = '2em';

  // We don't need padding, reducing the size if it does flash render.
  textArea.style.padding = 0;

  // Clean up any borders.
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';

  // Avoid flash of white box if rendered for any reason.
  textArea.style.background = 'transparent';


  textArea.value = text;

  document.body.appendChild(textArea);

  textArea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copying text command was ' + msg);
  } catch (err) {
    console.log('Oops, unable to copy');
  }

  document.body.removeChild(textArea);
}