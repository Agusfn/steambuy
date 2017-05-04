$(document).ready(function(e) {
    $("#submit-btn").click(function(e) {
        
		var pass = $("#password1").val();
		
		if(pass.length < 6 || pass.length > 40 || !/[a-zA-Z]/.test(pass) || !/[^a-zA-Z]/.test(pass)) {
			alert("Ingresa una contraseña de mínimo 6 caracteres, que contenga al menos una letra y al menos un dígito/símbolo.");
			return;
		}
		
		if($("#password2").val() !== pass) {
			alert("Las dos contraseñas ingresadas no coinciden.");
			return;	
		}
		
		$("#recover-form").submit();
		
		
    });
});