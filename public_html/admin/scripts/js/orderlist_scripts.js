// JavaScript Document

var steam_list = "";
var steamdb_list = "";

$(document).ready(function(e) {
    
	
	// Obtener precios de la tienda de steam del momento, via ajax.
	
	var url_list = [];
	$("a.product_url").each(function( index ) {
  		url_list.push($(this).attr("href"));
	});
	$.ajax({
		data:{"urls":JSON.stringify(url_list)},
		url:"scripts/php/ajax_orderlist_pricefetch.php",
		type:"POST",
		
		success: function(response) {
			var res = JSON.parse(response);
			//console.log(res);
			var i=0;
			$("span.steam_price").each(function( index ) {
  				$(this).text(res[i]);
				i++;
			});
			$(".steam_fetch_loading").hide();
		}
			
	});
		

	
	$("#open_steam_urls").click(function(e) {
        var links = steam_list.split("\n");
		links.forEach(function(entry) {
			if(entry != 0) {
				window.open(entry);	
			}
		});
    });
	
	$("#open_steamdb_urls").click(function(e) {
        var links = steamdb_list.split("\n");
		links.forEach(function(entry) {
			if(entry != 0) {
				window.open(entry);	
			}
		});
    });	
	
	$("#copy_steam_links").click(function(e) {
		alert(steam_list);
    });
	
	
	
	
	$("#today_payments").click(function(e) {
        $("#today_payments_box").fadeToggle("fast");
    });
	
	$("#today_payments_box").dblclick(function(e) {
        $(this).selectText();
    });


	
	$("#searchorder_button").click(function(e) {
		$("#searchorder_input").val($("#searchorder_input").val().trim());
		var val = $("#searchorder_input").val();
		if(val.length == 5 || val.length == 6) {
			$("#searchorder_form").submit();
		} else if(val.length == 14 && isInt(val)) {
			$.ajax({
				data:{invoice_number: val},
				url:"scripts/php/ajax_searchorder_invoice.php",
				type:"post",
				success: function(response) {
					var data = JSON.parse(response);
					console.log(data);
					if(data["result"] == 0) {
						alert("No se encontraron pedidos con esa boleta");
					} else if(data["result"] == 1) {
						window.location="pedido.php?orderid="+data["text"];
					} else if(data["result"] == 2) {
						alert("Se encontró más de un pedido con esa boleta:\n"+data["text"]);
					}
				}
			});
		} else if(val.length > 6) {
			$.ajax({
				data:{key: val},
				url:"scripts/php/ajax_searchorder_key.php",
				type:"post",
				success: function(response) {
					if(response == 0) {
						alert("No se encontraron pedidos con esa cd-key o link.");
					} else {
						alert("Los pedidos con esa clave de activación son:\n\n"+response);
					}
					
				}
			});
		}

    });
	
	$("#searchorder_input").keydown(function(e) {
        if(e.keyCode == 13) $("#searchorder_button").trigger("click");
    });
	
	
	
	
	$("#orderoptions_getlinks").click(function(e) {
        var selected_orders = getSelectedOrders();
		if(selected_orders.length == 0) {
			alert("Selecciona algún pedido");
			return;
		} 
		cleanOrderLinksModal();
		$("#order_links_modal").modal("show");
		
		
		var warning = false;
		
		var i = 0;
		var incorrect_links = "";
		
		selected_orders.forEach(function(entry) {

			var product_steam_url = $("#"+entry).closest("tr").children("td").children(".product_url").attr("href");
			product_steam_url = product_steam_url.replace(/\?.*/, "");
			var regex = /^(https?:\/\/)?store\.steampowered\.com\/(sub|app)\/([0-9]{1,10})(\/.*)?$/;			
			var product_cur_price = $("#"+entry).siblings(".order_current_steam_price").val();
			var product_name = $("#"+entry).closest("tr").children("td").children(".order_select").text();
			
			if(regex.test(product_steam_url)) {
				
				i++;
				var matches = product_steam_url.match(regex);
				var steamdb_link = "https://steamdb.info/"+matches[2]+"/"+matches[3]+"/";
				

				steam_list = steam_list+product_steam_url+"    "+product_name+"\n";
				steamdb_list = steamdb_list+steamdb_link+"\n";
				
				if(product_cur_price == 0) product_cur_price = "";

				var new_row = "<tr><td>"+product_name+"</td><td>"+product_cur_price+"</td><td><a href='"+product_steam_url+"' target='_blank'>"+product_steam_url+"</a></td><td><a href='"+steamdb_link+"' target='_blank'><i class='fa fa-database'></i></a></td></tr>";
				$(".order_links_table").append(new_row);				
			} else {
				incorrect_links = incorrect_links + product_name + "\n";
				warning = true;
			}
		});
		
		$("#order_count").text(i+" productos listados");
		
		if(warning) alert("Se omitieron pedido/s seleccionado/s que no son de Steam o tienen la URL mal.\nEstos son:\n"+incorrect_links);

    });
	
	
	
	$("#orderoptions_viewemails").click(function(e) {
        
		var selected_orders = getSelectedOrders();
		if(selected_orders.length == 0) {
			alert("Selecciona algún pedido");
			return;
		} 
		
		var emailList = "";
		
		selected_orders.forEach(function(entry) {
			var email = $("#"+entry).closest("tr").children("td").children(".order_email").text();
			emailList = emailList + "\n" + email;
		});
		
		alert(emailList);
    });
	
	$("#orderoptions_cancelorders").click(function(e) {
        var selected_orders = getSelectedOrders();
		if(selected_orders.length == 0) {
			alert("Selecciona algún pedido");
			return;
		} 
		var orderids = JSON.stringify(selected_orders);
		var razon = prompt("Ingrese el motivo:");
		$.ajax({
			data:{"action":"cancel", "reason":razon, "orders":orderids, "key":"v4d87s3nb12k8f2c7f21b4u1rff8s1yh3"},
			url:"scripts/php/ajax_orderlist_options.php",
			type:"post",
			beforeSend: function() {
				$(".main_content").css("cursor","wait");
			},
			success:function(response) {
				$(".main_content").css("cursor","default");
				$("input:checkbox").prop("checked",false);
				console.log(response);
				var result = JSON.parse(response);
				if(result["error"] == 0) {
					selected_orders.forEach(function(entry) {
						var table_row = $("#"+entry).closest("tr");
						table_row.animate({
							opacity: 'toggle'
						}, 700, 'linear');							
					});
				} else if(result["error"] == 1) {
					alert(result["error_text"]);	
				}
			}
		});	
			
    });
	
	$("#orderoptions_expireorders").click(function(e) {
        var selected_orders = getSelectedOrders();
		if(selected_orders.length == 0) {
			alert("Selecciona algún pedido");
			return;
		} 
		var orderids = JSON.stringify(selected_orders);
		if(confirm("Cancelar "+selected_orders.length+" pedidos?")) {
			$.ajax({
				data:{"action":"expire", "orders":orderids, "key":"v4d87s3nb12k8f2c7f21b4u1rff8s1yh3"},
				url:"scripts/php/ajax_orderlist_options.php",
				type:"post",
				beforeSend: function() {
					$(".main_content").css("cursor","wait");
				},
				success:function(response) {
					$(".main_content").css("cursor","default");
					$("input:checkbox").prop("checked",false);
					console.log(response);
					var result = JSON.parse(response);
					if(result["error"] == 0) {
						selected_orders.forEach(function(entry) {
							var table_row = $("#"+entry).closest("tr");
							table_row.animate({
								opacity: 'toggle'
							}, 700, 'linear');							
						});
					} else if(result["error"] == 1) {
						alert(result["error_text"]);	
					}
				}
			});	
		}
    });
	
	
	$("#orderoptions_concreteorders").click(function(e) {
        
        var selected_orders = getSelectedOrders();
		if(selected_orders.length == 0) {
			alert("Selecciona algún pedido");
			return;
		} 
		var orderids = JSON.stringify(selected_orders);
		if(confirm("Concretar "+selected_orders.length+" pedidos? (Formato GIFT)")) {
			$.ajax({
				data:{"action":"concrete", "orders":orderids, "key":"v4d87s3nb12k8f2c7f21b4u1rff8s1yh3"},
				url:"scripts/php/ajax_orderlist_options.php",
				type:"post",
				beforeSend: function() {
					$(".main_content").css("cursor","wait");
				},
				success:function(response) {
					$(".main_content").css("cursor","default");
					$("input:checkbox").prop("checked",false);
					console.log(response);
					var result = JSON.parse(response);
					if(result["error"] == 0) {
						selected_orders.forEach(function(entry) {
							var table_row = $("#"+entry).closest("tr");
							table_row.animate({
								opacity: 'toggle'
							}, 700, 'linear');							
						});
					} else if(result["error"] == 1) {
						alert(result["error_text"]);	
					}
				}
			});	
		}
		
    });
	
	$("#order_maincheckbox").change(function(e) {
        if($(this).is(":checked")) {
			$(".select_checkbox").prop("checked",true);
		} else {
			$(".select_checkbox").prop("checked",false);
		}
		$(".select_checkbox").trigger("change");
    });
	
	$(".order_select").click(function(e) {
        $(this).closest("tr").children("td").children(".select_checkbox").click();
    });
	
	$(".select_checkbox").change(function(e) {
        var count = $("[class='select_checkbox']:checked").length;
		if(count > 0) {
			$(".selected_orders_count").show();
			$(".selected_orders_count").text(count + " pedidos seleccionados");
		} else {
			$(".selected_orders_count").hide();
		}
    });
	
});


function getSelectedOrders() {
	var selected = [];
	$(".select_checkbox:checked").each(function(index, element) {
        selected.push($(this).attr("id"));
    });
	return selected;
}


function cleanOrderLinksModal() {
	$(".order_links_table tr").remove();
	steam_list = "";
	steamdb_list = "";
	$("#order_count").text("");
}


function isInt(n) {
   return n % 1 === 0;
}


jQuery.fn.selectText = function(){
   var doc = document;
   var element = this[0];
   console.log(this, element);
   if (doc.body.createTextRange) {
       var range = document.body.createTextRange();
       range.moveToElementText(element);
       range.select();
   } else if (window.getSelection) {
       var selection = window.getSelection();        
       var range = document.createRange();
       range.selectNodeContents(element);
       selection.removeAllRanges();
       selection.addRange(range);
   }
};




function copyTextToClipboard(text) {
  var textArea = document.createElement("textarea");

  //
  // *** This styling is an extra step which is likely not required. ***
  //
  // Why is it here? To ensure:
  // 1. the element is able to have focus and selection.
  // 2. if element was to flash render it has minimal visual impact.
  // 3. less flakyness with selection and copying which **might** occur if
  //    the textarea element is not visible.
  //
  // The likelihood is the element won't even render, not even a flash,
  // so some of these are just precautions. However in IE the element
  // is visible whilst the popup box asking the user for permission for
  // the web page to copy to the clipboard.
  //

  // Place in top-left corner of screen regardless of scroll position.
  textArea.style.position = 'fixed';
  textArea.style.top = 0;
  textArea.style.left = 0;

  // Ensure it has a small width and height. Setting to 1px / 1em
  // doesn't work as this gives a negative w/h on some browsers.
  textArea.style.width = '2em';
  textArea.style.height = '2em';

  // We don't need padding, reducing the size if it does flash render.
  textArea.style.padding = 0;

  // Clean up any borders.
  textArea.style.border = 'none';
  textArea.style.outline = 'none';
  textArea.style.boxShadow = 'none';

  // Avoid flash of white box if rendered for any reason.
  textArea.style.background = 'transparent';


  textArea.value = text;

  document.body.appendChild(textArea);

  textArea.select();

  try {
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copying text command was ' + msg);
  } catch (err) {
    console.log('Oops, unable to copy');
  }

  document.body.removeChild(textArea);
}