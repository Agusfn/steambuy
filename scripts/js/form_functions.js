// JavaScript Document
// var form = document.buyform;

//Mostrar botones de comrpar en catalogo de juegos en oferta


//**********
var last_calculated_prices = new Array();
last_calculated_prices[0] = 0;
last_calculated_prices[1] = 1;

/* OCB VARIABLES */
var displaying_ocb = false;
var valid_name = false;
var valid_email = false;
var valid_phone = false;
var valid_gamename = false;
var valid_gameurl = false;
var valid_gameprice = false;

var ocb_last_calc_price = new Array();
ocb_last_calc_price[0] = 0;
ocb_last_calc_price[1] = 1;
/*****************/

var error_text;
var displayingErrorBox = false;


$(document).ready(function() {

	$('html').click(function() {
		if(displayingErrorBox == true) {
			displayingErrorBox = false;
			$(".form_error_box").fadeOut(150);
		}
	 });

  $(".ocb_cancellabel").click(function() { 
		displaying_ocb = false;
		$(".blackout").fadeOut(100);
		$(".order_confirmation_box").fadeOut(200)  
  });
  

});

window.onbeforeunload = function (evt)
{
	if(displaying_ocb == true) {
		var message = '¿Deseas salir?';
		if (typeof evt == 'undefined'){
		evt = window.event;}
		if(evt){
		evt.returnValue = message;}
		return message;
	}
}

// **** Funciones de la calculadora **** //

function calculateUsdPrice()
{

	var usd = $("#calcinput").val();
										
	if (usd == "0.00" || usd == 0 || isNaN(usd)) {
    	$("#calculatedprice").html("<span style='color:#333;font-size:14px'>Ingrese el precio del juego</span>");
        $("#calcinput").val("0.00");
        return;
    } else if(usd > 500) {
    	$("#calculatedprice").html("<span style='color:#333;font-size:14px'>Ingrese un valor de hasta 500 usd</span>");
        return;
	}
	
	if(last_calculated_prices[0] == usd) {
    	$("#calculatedprice").html("Juego de  <span style='color:#060'><strong>U&#36;S " + parseFloat(usd) + "</span>: <span style='color:#A00'>" + last_calculated_prices[1] + " ARS</span></strong>");
	} 
	else 
	{ 
		var parametros = {
			"price" : usd,
			"pay_method" : parseInt($("#pay_method").val())
		};
		$.ajax({
			data:  parametros,
			url:   '../shared_scripts/ajax_output_prices.php',
			type:  'post',
												
			beforeSend: function () {
				$("#calculatedprice").html("<div style='margin: 2px 0 0 20px;'><img src='../shared_design/ajax-loader.gif' alt='cargando..'/></div>");
			},
			success:  function (response) {
				if(response.indexOf("error") == -1){
        	        last_calculated_prices[0] = usd;
            	    last_calculated_prices[1] = response;
                    $("#calculatedprice").html("Juego de  <span style='color:#060'><strong>U&#36;S " + parseFloat(usd) + "</span>: <span style='color:#A00'>" + response + " ARS</span></strong>");
                }else{
                	$("#calculatedprice").html("<span style='color:#333;font-size:14px'>" + response + "</span>");
                }
			}
		});
		
	}
}

function limitInputChars(e, obj) {
	
	if (e.keyCode == 13) {
        calculateUsdPrice();
		$("#calcinput").blur();
        return false;
    }
	
	var sKey = -1;
    var bResult = true;
    var bPunto = (obj.value.indexOf(".") != -1);
    var lCantChars = obj.value.length;

	if (window.event){
		sKey = e.keyCode;
	} else if (e.which) {
		sKey = e.which;
	}
	if (sKey > 20) {
		if (((sKey < 48 || sKey > 57) && (sKey != 46 || bPunto)) || (!bPunto && lCantChars > 19 && sKey != 46)) {
			bResult = false;
		} }
	return bResult;
}

function applyFormat(oText) 
{
	if(oText.value == 0)
	{
		oText.value = "0.00";
		return 
	}
	var aDec = oText.value.split('.');
	if(aDec.length > 1) {
    	if(aDec[1].length == 1) {
    	oText.value = aDec[0] + '.' + aDec[1].split('.')[0] + '0';
 		}
		if(aDec[1].length == 0) {
			oText.value = aDec[0] + '.00';
		}
		if(aDec[1].length >= 2) {
			oText.value = aDec[0] + '.' + aDec[1].substr(0,2);
		}
    }else{
		oText.value = aDec + '.00';
    }
}

//*******************//



function processForm()
{
	if(displaying_ocb == true){
		$(".order_confirmation_box").show();
		$(".blackout").show();
		return;
	}
	
	if(displayingErrorBox == true) return;
	
	var form = document.buyform;
	error_text = "";
	

	/* nombre del comprador */
	form.name.value = form.name.value.replace(/\s+/g, " ");
	if(form.name.value != 0){
		if(/^[a-z\sñáéíóú]*$/i.test(form.name.value)){
			valid_name = true;
		}else{
			valid_name = false;
			error_text = "<li>Ingresa el nombre y apellido correctamente.</li>";
		}
	}else{
		valid_name = false;
		error_text = "<li>Ingresa tu nombre y apellido.</li>";
	}

	/* email del comprador */
	if(form.email.value != 0){
		if(is_valid_email(form.email.value)){
			valid_email = true;
		}else{
			valid_email = false;
			error_text = error_text + "<li>El correo electrónico no es válido.</li>";
		}
	}else{
		valid_email = false;
		error_text = error_text + "<li>Ingresa tu correo electrónico.</li>";
	}
	
	
	/* Telefono */
	if(form.phone.value !=0){
		if(form.phone.value.length > 5 && /^[0-9]+$/.test(form.phone.value)){
			valid_phone = true;
		}else{
			error_text = error_text + "<li>El teléfono ingresado es muy corto o posee caracteres inválidos.</li>";
			valid_phone = false;
		}
	}else{
		valid_phone = true;
	}
	
	
	/* nombre del juego */
	form.gamename.value = form.gamename.value.replace(/\s+/g, " ");
	if(form.gamename.value != 0){
		valid_gamename = true;
	}else{
		valid_gamename = false;
		error_text = error_text + "<li>Ingresa el nombre del juego.</li>";
	}
	
	
	
	
	/* URL del juego */
	if(form.gameurl.value != 0){
		if(form.gameurl.value.match(/[-a-zA-Z0-9@:%_\+.~#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~#?&//=]*)?/gi)){
			var e = $("#input_buysite option:selected").val();
			if(e == "Tienda de Steam")
			{
				if(form.gameurl.value.indexOf("store.steampowered.com") != -1){
					valid_gameurl = true;
				}else{
					valid_gameurl = false;
					error_text = error_text + "<li>La URL del juego no es de Steam.</li>";						
				}
			}
			else if(e == "Amazon")
			{
				if(form.gameurl.value.indexOf("amazon.com") != -1){
					valid_gameurl = true;
				}else{
					valid_gameurl = false;
					error_text = error_text + "<li>La URL del juego no es de Amazon.</li>";							
				}				
			}
			/*else if(e == "Green Man Gaming")
			{
				if(form.gameurl.value.indexOf("greenmangaming.com") != -1){
					valid_gameurl = true;
				}else{
					valid_gameurl = false;
					error_text = error_text + "<li>La URL del juego no es de Green Man Gaming.</li>";						
				}		
			}*/
		}else{
			valid_gameurl = false;
			error_text = error_text + "<li>La URL del juego ingresada no es válida.</li>";			
		}
	}else{
		valid_gameurl = false;
		error_text = error_text + "<li>Ingresa la URL del sitio de venta del juego.</li>";		
	}
	

	/* Precio del juego */
	if(form.gameprice.value != 0)
	{
		if(form.gameprice.value <= 200){
			valid_gameprice = true;
		}else{
			valid_gameprice = false;
			error_text = error_text + "<li>El precio del juego no puede superar los 200 dolares.</li>";				
		}
	}else{
		valid_gameprice = false;
		error_text = error_text + "<li>Ingresa el precio del juego.</li>";		
	}
	
	
	
	if(valid_name == true && valid_email == true && valid_phone == true && valid_gamename == true && valid_gameurl == true && valid_gameprice == true)
	{
		$(".ocb_button").css("visibility","hidden");
		
		$("#ocb_value_gamename").text(form.gamename.value);
		$("#ocb_value_gamesite").text($("#input_buysite").val());
		$("#ocb_value_gameurl").val(form.gameurl.value);
		$("#ocb_value_gamediscount").text($("#input_gamediscount").val());
		if($("#input_gamediscount").val() == "Si" && form.discountend.value != 0){
			$("#ocb_value_discountend").text(form.discountend.value);
			$("#ocb_hideable_line1").css("display","block");			

		}else{
			$("#ocb_hideable_line1").css("display","none");
		}
		$("#ocb_value_gameprice").text("U$S " + form.gameprice.value);
		$("#ocb_value_name").text(form.name.value);
		$("#ocb_value_email").text(form.email.value);
		
		if(form.phone.value != 0){
			$("#ocb_value_phone").text(form.phone.value);
			$("#ocb_hideable_line2").css("display","block");
		}else{
			$("#ocb_hideable_line2").css("display","none");
		}

		
		
		var str = form.gameprice.value;		
		if(ocb_last_calc_price[0] == str){
			$("#ocb_value_total").text("$ " + ocb_last_calc_price[1] + " (Pesos Argentinos)");
			$(".ocb_button").css("visibility","visible");
		}else{
			
			var parametros = {
				"price" : str,
				"pay_method" : parseInt($("#pay_method").val())
			};
			$.ajax({
				data:  parametros,
				url:   '../shared_scripts/ajax_output_prices.php',
				type:  'post',
													
				beforeSend: function () {
					$("#ocb_value_total").html("<img style='margin-top:4px' src='../shared_design/ajax-loader2.gif' alt='cargando..'/>");	
				},
				success:  function (response) {
					if(response.indexOf("error") == -1){
						
						$("#ocb_value_total").text("$ " + response + " (Pesos Argentinos)");
						ocb_last_calc_price[0] = str;
						ocb_last_calc_price[1] = response;
						$(".ocb_button").css("visibility","visible");	
						
					}else{
						$("#ocb_value_total").text(response);
					}
				}
			});	
		}
		
	
		displaying_ocb = true;
		$(".blackout").fadeIn(100);
		$(".order_confirmation_box").fadeIn(200);
	}
	else
	{
		$("#errbox_errorlist").html("<ul>" + error_text + "</ul>");
		$(".form_error_box").fadeIn(150);
		var timeout = window.setTimeout(function() {
		displayingErrorBox = true;
		}, 300);		
	}
	
}

function inputBuySiteChange()
{
	var form = document.buyform;
	
	var e = $("#input_buysite option:selected").val();
	
	if(e == "Tienda de Steam")
	{
		$("#label_gameurl").text("Url del juego en Steam");
		form.gameurl.setAttribute('placeholder',"Ej: http://store.steampowered.com/app/4000/");
	}
	else if(e == "Amazon")
	{
		$("#label_gameurl").text("Url del juego en Amazon");
		form.gameurl.setAttribute('placeholder',"Ej: http://www.amazon.com/Call-Duty-Black-Ops-Download/dp/B007Z3RN2I/");
	}
	/*else if(e == "Green Man Gaming")
	{
		$("#label_gameurl").text("Url del juego en Green Man Gaming");
		form.gameurl.setAttribute('placeholder',"Ej: http://www.greenmangaming.com/s/ar/en/pc/games/strategy/company-heroes-2/");		
	}*/
}



function is_valid_email(email) {
	if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) return true;
	else return (false);
}