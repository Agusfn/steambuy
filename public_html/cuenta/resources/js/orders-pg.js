$(document).ready(function() {
	
	/*$(".inform-payment").click(function(e) {
        alert(1);
    });*/
	
	$( document ).on( "click", "a.inform-payment", function() {
		$("#inform-payment-modal").modal("show");
		var order_id = $(this).closest("tr").find(".order-id").text();
		var product_name = $(this).closest("tr").find(".product-name").text();
		$("#inform-product-name").text(product_name);
		$("#orderid-inform").val(order_id);
	});
	
	$( document ).on( "click", "a.cancel-order", function() {
		
		var order_id = $(this).closest("tr").find(".order-id").text();
		var status_lbl = $(this).closest("tr").find(".order-status");
		var options_btn = $(this).closest("tr").find(".btn-group");
		
		if(confirm("Cancelar pedido " + order_id + "?")) {
			$.ajax({
				data: { order_id: order_id},
				url: root_abs_path + "cuenta/resources/php/ajax-cancel-order.php",
				type: "POST",
				success: function(response) {
					result = parseJSON(response);
					if(result != false) {
					
						if(result["success"] == true) {
							status_lbl.html("<span style='color:#B10000;'>Cancelado</span>");
							options_btn.remove();
						}
					}
				}
			});
		}
	});
	
	
	$("#submit-inform").click(function(e) {
		if($('#input-file-inform').get(0).files.length === 0) {
			alert("Selecciona un archivo");
			return;
		}
		$("#inform-form").submit();
    });
	
	
	
});

function disable_pagination(status, num = false) {
	if(status) {
		$(".pagination li").addClass("disabled");
		if(num) {
			$("li[data-lp='"+num+"']").removeClass("disabled");	
		}
	} else {
		$(".pagination li").removeClass("disabled");
	}
}


