
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
			$("#coupon-discount-ammount").text("-$" + coupon_discount_ammount);
			
			final_price = num_round(final_price - coupon_discount_ammount);
		}
		$("#total-ammount").text("$" + final_price + " ARS");
    });
	
	
	
	$("#payoption2").click(function(e) {
		
        $("#payoption1").removeClass("active");
		$(this).addClass("active");
		$("#payment_method").val(2);
		
		var final_price = num_round(product_price - transfer_discount);
		
		$("#row-transfer-discount").show();
		
		if(discount_coupon) {
			$("#row-subtotal").show();
			$("#subtotal-ammount").text("$"+final_price);
			
			var coupon_discount_ammount = num_round(final_price*(coupon_disc_percent/100));
			$("#coupon-discount-ammount").text("-$" + coupon_discount_ammount);
			
			final_price = num_round(final_price - coupon_discount_ammount);
			
		}
		$("#total-ammount").text("$" + final_price + " ARS");
		
    });
});




function num_round(num) {
	return Math.round(num * 10) / 10;	
}