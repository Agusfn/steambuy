
$(document).ready(function(e) {
    
	$("#search_input").keyup(function() {
		
		$("#result_box").empty();
		if($(this).val() == 0) return;
		
		$.ajax({
			data:{search_query:$("#search_input").val()},
			url:"scripts/ajax_support_topics.php",
			type:"post",
			
			beforeSend: function() {
				$("#load_icon").css("display","inline-block");
			},
			
			success: function(response) {
				$("#load_icon").css("display","none");
				if(response != 0) {
					var qarray = JSON.parse(response);
					qarray.forEach(function(entry) {	
						$("#result_box").append("<a href='../faq/?v="+entry["order"]+"#"+entry["order"]+"' target='_blank' class='link'><div class='result'>"+entry["question"]+"</div></a>");
					});
				} else {
					$("#result_box").html("No se encontraron resultados.<br/> Si no encuentras tu respuesta revisa la <a href='../faq/'>lista completa</a> de preguntas frecuentes.");
				}
			}
		});
		
	});
	
});