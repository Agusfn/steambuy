// JavaScript Document

$(document).ready(function(e) {
    $("input[name='dollar_value_update']").change(function() {
		if($(this).attr("value") == "fixed") {
			$("#input_fixedquote").prop("disabled", false);
		} else if($(this).attr("value") == "automatic") {
			$("#input_fixedquote").prop("disabled", true);
		}
	});
	
	$("#calculator_button").click(function() {
		var ammount = $("#calculator_input").val();
		if (ammount == "0.00" || ammount == 0 || isNaN(ammount)) {
			alert("Ingresa un precio");
        	return;
		}
		ammount = parseFloat(ammount);
		var parametros = {"price" : ammount, "pay_method" : 1, "admin_query":1};
		$.ajax({
			data:  parametros,
			url:   '../global_scripts/php/ajax_output_prices.php',
			type:  'post',
												
			beforeSend: function () {
				$("#calculator_button").css("cursor","progress");
			},
			success:  function (response) {
				alert(response);
				$("#calculator_button").css("cursor","default");
			}
			});
	});
	
	
	$('#modal_banlist').on('show.bs.modal', function (e) {
  		$.ajax({
			data: {"get_banlist":1},
			url: "scripts/php/ajax_banlist.php",
			type:"POST",
			
			beforeSend: function() {
				$("#modal_banlist").css("cursor","wait");
			},
			success:function(response) {
				if(response != "empty") {
					bans = JSON.parse(response);
					bans.forEach(function(entry) {
						$(".banlist_tbody").append("<tr id='"+entry["id"]+"'><td>"+entry["id"]+"</td><td>"+entry["ip"]+"</td><td>"+entry["reason"]+"</td><td align='center'><span class='glyphicon glyphicon-remove banlist_remove'></span></td></tr>");
					});	
				} else $(".banlist_tbody").append("<tr class='nobans'><td colspan='4' align='center'>No hay bans</td></tr>");
				$("#modal_banlist").css("cursor","default");
			}
		});
			
	});
	
	
	$('#modal_banlist').on('hidden.bs.modal', function (e) {
  		$(".banlist_tbody").empty();
			
	});
	
	
	$(".banlist_remove").live("click", function() {
		
		var row = $(this).closest("tr");
        var id = $(this).closest("tr").attr("id");
		
		$.ajax({
			data: {"delete_ban":1, "ban_id":id},
			url: "scripts/php/ajax_banlist.php",
			type:"POST",
			
			beforeSend: function() {
				$("#modal_banlist").css("cursor","wait");
			},
			success:function(response) {
				if(response == "ok") {
					row.fadeOut(400, function(){
						row.remove();
					});
				} else alert(response);	
				$("#modal_banlist").css("cursor","default");
			}
		});
		
    });
	
	$("#banform_addban").click(function(e) {
        if($("#banform_ip").val() == 0 || $("#banform_reason").val() == 0) {
			alert("Completá los datos");	
		} else {
			$.ajax({
				data: {"add_ban":1, "ban_ip":$("#banform_ip").val(), "ban_reason":$("#banform_reason").val()},
				url: "scripts/php/ajax_banlist.php",
				type:"POST",
			
				beforeSend: function() {
					$("#modal_banlist").css("cursor","wait");
				},
				success:function(response) {
					$("#modal_banlist").css("cursor","default");
					if(!isNaN(response)) {
						$(".nobans").remove();
						$(".banlist_tbody").append("<tr id='"+response+"'><td>"+response+"</td><td>"+$("#banform_ip").val()+"</td><td>"+$("#banform_reason").val()+"</td><td align='center'><span class='glyphicon glyphicon-remove banlist_remove'></span></td></tr>");
						$("#banform_ip").val("");
						$("#banform_reason").val("");
					} else {
						alert(response);	
					}
					
				}
			});	
		}
    });
	
});