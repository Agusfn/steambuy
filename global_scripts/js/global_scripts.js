$(document).ready(function() {  
	
	$(".button").on('dragstart', function(event) { event.preventDefault(); });
	//$('.w_tooltip').tooltip();
	$('[data-toggle="tooltip"]').tooltip()
	
	var stickyNavTop = $('.nav_bar').offset().top;  
      
	var stickyNav = function(){  
		if($(window).width() > 1000) {
			var scrollTop = $(window).scrollTop();  
			if (scrollTop > stickyNavTop) {   
				if(!$(".nav_bar").hasClass("nav_sticky")) {
					$('.nav_bar').addClass('nav_sticky');  
				}
			} else {  
				if($(".nav_bar").hasClass("nav_sticky")) {
					$('.nav_bar').removeClass('nav_sticky'); 
				}
			}  
		} else {
			if($(".nav_bar").hasClass("nav_sticky")) {
				$('.nav_bar').removeClass('nav_sticky'); 
			}
		}
    	
    };  
    stickyNav();  
    $(window).scroll(function() {  
        stickyNav();  
    });  
	$(window).resize(function(e) {
        if($(window).width() > 1000) {
			if($(".nav_bar").hasClass("nav_top")) {
				$('.nav_bar').removeClass('nav_top'); 
			} 
		} else {
			if(!$(".nav_bar").hasClass("nav_top")) {
				$('.nav_bar').addClass('nav_top'); 
			}
			if($(".nav_bar").hasClass("nav_sticky")) {
				$('.nav_bar').removeClass('nav_sticky'); 
			}
		}
    });
	

});  


function limitInputChars(e, obj) {
	if (e.keyCode == 13) {
        $(obj).blur();
        return false; // Se debe llamar al evento aparte desde la página donde haya algun botón de calculadora para el cálculo
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
		} 
	}
	return bResult;
}
function applyFormat(oText) 
{
	if(oText.value == 0) {
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