var pay_method;

$(document).ready(function(e) {
    $(".payment_options > div > a").click(function(e) {
     	$("#payoption1, #payoption2").removeClass("active");
		$(this).addClass("active");
		pay_method = parseInt($(this).index()) + 1;
		$("#total_price").text("$"+price[pay_method]+" ARS");
		$("#paymethod").val(pay_method);
    });
	

});


function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 

function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}
