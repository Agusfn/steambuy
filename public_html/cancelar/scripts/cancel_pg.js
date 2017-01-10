
$(document).ready(function(e) {
    $("#button_submit").click(function(e) {
        var error_text = "";
        if($("#input_id").val() == 0) {
			error_text = "<li>Ingresa la ID de pedido</li>";
		}
		if($("#input_password").val() == 0) {
			error_text += "<li>Ingresa la clave de pedido</li>";	
		}
		
		if(error_text == 0) {
			$("#input_id").val($("#input_id").val().trim());
			$("#input_password").val($("#input_password").val().trim());
			$("#form").submit();
		} else {
			$("#error_list ul").html(error_text);
			$("#error_list").slideDown("slow");
		}
    });
});