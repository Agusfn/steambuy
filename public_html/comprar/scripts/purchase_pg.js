
$(document).ready(function(e) {
	
	
	
	$("#payoption1").click(function(e) {
        $("#payoption2").removeClass("active");
		$(this).addClass("active");
		$("#payment_method").val(1);
		
		var final_price = product_price;
		
		$("#row-transfer-discount").hide();	
		if(discount_coupon) {
			$("#row-subtotal").hide();
			
			var coupon_discount_ammount = num_round(final_price*(coupon_disc_percent/100));
			$("#coupon-discount-ammount").text("-$" + coupon_discount_ammount + " ARS");
			
			final_price = num_round(final_price - coupon_discount_ammount);
		}
		$("#final-price-ars").text("$" + final_price + " ARS");
    });
	
	
	
	$("#payoption2").click(function(e) {
		
        $("#payoption1").removeClass("active");
		$(this).addClass("active");
		$("#payment_method").val(2);
		
		var final_price = num_round(product_price - transfer_discount);
		
		$("#row-transfer-discount").show();
		$("#transfer-discount-ammount").text("-$"+transfer_discount);
		
		if(discount_coupon) {
			$("#row-subtotal").show();
			$("#subtotal-ammount").text("$"+final_price);
			
			var coupon_discount_ammount = num_round(final_price*(coupon_disc_percent/100));
			$("#coupon-discount-ammount").text("-$" + coupon_discount_ammount + " ARS");
			
			final_price = num_round(final_price - coupon_discount_ammount);
			
		}
		$("#final-price-ars").text("$" + final_price + " ARS");
		
    });
});


function validateEmail(email) { 
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
} 

function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

function num_round(num) {
	return Math.round(num * 10) / 10;	
}