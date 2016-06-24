// JavaScript Document


var insert_mode = false;
var current_id = 0;

$(document).ready(function() {
	
	$("#sortable").sortable();
	$("#sortable").disableSelection();
	$('#mpd_limitedoffer_endtime').datetimepicker({
		dateFormat: "yy-mm-dd",
		timeFormat: 'HH:mm:ss',
		stepSecond: 10
	});
	
	if(window.location.hash) {
		var productid = window.location.hash.substr(1);
		 $("#modal_productdata").modal("show");
		$("#modal_title").text("Modificar producto");
		$("#mpd_productid").show();
		insert_mode = false;
		current_id = productid;
		loadProductInfo(productid);
	}
	
	$("#btn_reorder").click(function(e) {
        applyProductsOrder();
    });
	
	$("#btn_insert").click(function(e) {
        $("#modal_productdata").modal("show");
		$("#modal_title").text("Insertar producto");
		$("#mpd_productid").hide();
		insert_mode = true;
    });
	
	$(".pc_product").dblclick(function(e) {
        $("#modal_productdata").modal("show");
		$("#modal_title").text("Modificar producto");
		$("#mpd_productid").show();
		insert_mode = false;
		current_id = $(this).attr("id").substr("1");
		loadProductInfo(current_id);
    });	
	
	$("#modal_productdata").on('hidden.bs.modal', function (e) {
		cleanProductDetailsModal();
	})
	

	/** Eventos dentro del cuadro de edición de productos **/

	$("#mpd_sellingsite").change(function() { 
		if($(this).prop("selectedIndex") == 0) $("#mpd_site_url").attr("placeholder","URL de tienda de Steam");
		else if($(this).prop("selectedIndex") == 1) $("#mpd_site_url").attr("placeholder","URL de tienda de Amazon");
		else if($(this).prop("selectedIndex") == 2) $("#mpd_site_url").attr("placeholder","URL de HumbleBundle");
		else if($(this).prop("selectedIndex") == 3) $("#mpd_site_url").attr("placeholder","URL de BundleStars");
		else if($(this).prop("selectedIndex") == 4) $("#mpd_site_url").attr("placeholder","URL de Origin");
	});

	$("#mpd_has_limitedunits").change(function() { 
		if($(this).prop("selectedIndex") == 0) $("#mpd_limitedunits").prop("disabled", true);	
		else if($(this).prop("selectedIndex") == 1) $("#mpd_limitedunits").prop("disabled", false);
	});
	
	$("#mpd_has_customprice").change(function() { 
		if($(this).prop("selectedIndex") == 0) {
			$("#mpd_customprice_currency").prop("disabled", true);	
			$(".customprice_currency").text("USD");
		} else if($(this).prop("selectedIndex") == 1)  {
			$("#mpd_customprice_currency").prop("disabled", false);
			if($("#mpd_customprice_currency").prop("selectedIndex") == 0) $(".customprice_currency").text("USD");
			else if($("#mpd_customprice_currency").prop("selectedIndex") == 1) $(".customprice_currency").text("ARS");
		}
	});
	
	$("#mpd_customprice_currency").change(function(e) {
		if($("#mpd_has_customprice").prop("selectedIndex") == 1) {
			if($(this).prop("selectedIndex") == 0) $(".customprice_currency").text("USD");
			else if($(this).prop("selectedIndex") == 1) $(".customprice_currency").text("ARS");
		}
    });
	
	$("#mpd_ext_limitedoffer").change(function() { 
		if($(this).prop("selectedIndex") == 0) {
			$("#mpd_limitedoffer_endtime").prop("disabled", true);	
			$("#mpd_limitedoffer_endtime").val("0000-00-00 00:00:00");
		} else if($(this).prop("selectedIndex") == 1) $("#mpd_limitedoffer_endtime").prop("disabled", false);
	});

	$("#mpd_mainpicture").change(function () {
		$("#product_img_mainpic").attr("src","../../data/img/game_imgs/" + $("#mpd_mainpicture").val());
	});
	
	$("#mpd_pics").change(function(e) {
        var imgs = $(this).val().split(";");
		var imglinks = "";
		var i = 0;
		imgs.forEach(function(entry) {
			i += 1;
			if(entry.indexOf("steamstatic.com") > -1) imglinks += "<a href='"+entry+"1920x1080.jpg' target='_blank'>"+i+"</a>&nbsp;";
			else imglinks += "<a href='"+entry+"' target='_blank'>"+i+"</a>&nbsp;";
		});
		$("#mpd_screenshots_details").html("Imgs: "+imglinks);
    });
	
	
	
	$("#mpd_get_product_data").click(function() { 
		var siteurl = $("#mpd_site_url").val()
		if(siteurl == 0 || siteurl.indexOf("store.steampowered.com") == -1) {
			alert("Ingresa una URL de Steam");
			return;	
		}
		$("#modal_productdata").addClass("wait");
		fetchProductInfo(siteurl);
	});	
	
	$("#cheap_price").click(function(e) {
		
		$("#modal_productdata").addClass("wait");
		$("#modal_productdata").data('bs.modal').isShown = false;
 		$.ajax({
			data:  { steam_url: $("#mpd_site_url").val(), cheap_prices:"1" },
			url:   'scripts/php/ajax_fetch_product_info.php',
			type:  'post',
										
			success:  function (response) {
				$("#modal_productdata").data('bs.modal').isShown = true;
				$("#modal_productdata").removeClass("wait");
				var pinfo = JSON.parse(response);
				console.log(pinfo);
				if(pinfo["mxn_price"]["error"] == 0 && pinfo["brl_price"]["error"] == 0) {
					
					
					var br_usd_price = pinfo["brl_price"]["finalprice"]/brl_quote;
					var mx_usd_price = pinfo["mxn_price"]["finalprice"]/mxn_quote;

					var cheap_price = 0;
					var selling_price = 0;
					if(br_usd_price < mx_usd_price) cheap_price = br_usd_price; else cheap_price = mx_usd_price;
					
					if(cheap_price < 32) selling_price = cheap_price * 1.34;
					else if(cheap_price >= 32) selling_price = cheap_price * 1.39;

					if(selling_price < $("#mpd_finalprice").val()) {
						$("#mpd_finalprice").val(selling_price.toFixed(2));
						$("#mpd_has_customprice")[0].selectedIndex = 1;
						$("#mpd_customprice_currency")[0].selectedIndex = 0;
						$("#mpd_customprice_currency").prop("disabled", false).trigger("change");
					} else alert("El precio sugerido via brl/mxn es mayor al precio actual ("+selling_price.toFixed(2)+")");
					
					
				} else alert("Error obteniendo precios mxn y brl");
			}
		});
 
    });

	$("#mpd_get_tagpics").click(function(e) {
		if($("#mpd_site_url").val() == 0) {
			alert("Ingresa una URL");
			return;	
		}
		
		$("#modal_productdata").addClass("wait");
		$("#modal_productdata").data('bs.modal').isShown = false;
		
		$.ajax({
			data:  { steam_url: $("#mpd_site_url").val(), data_requested:"t,s" },
			url:   'scripts/php/ajax_fetch_product_info.php',
			type:  'post',
										
			success:  function (response) {
				$("#modal_productdata").data('bs.modal').isShown = true;
				$("#modal_productdata").removeClass("wait");
				var pinfo = JSON.parse(response);
				console.log(pinfo);
				if(pinfo["error"] == 0) {
					if(pinfo["product_screenshots"]["error"] == 0) {
						$("#mpd_pics").val(pinfo["product_screenshots"]["value"]);
						$("#mpd_pics").trigger("change");
					}
					if(pinfo["product_tags"]["error"] == 0) {
						$("#mpd_tags").val(pinfo["product_tags"]["value"]);	
					}
				} else alert("Error cargando producto en clase steamProduct");
			}
		});
    });
	
	$("#recargar_img").click(function() {
		if($("#mpd_site_url").val() == 0) {
			alert("Ingresa una URL");
			return;	
		}
		
		$("#modal_productdata").addClass("wait");
		$("#modal_productdata").data('bs.modal').isShown = false;
		
		$.ajax({
			data:  { steam_url: $("#mpd_site_url").val(), data_requested:"n,h" }, // Hay que pedir el nombre para obtener el nombre de la imagen
			url:   'scripts/php/ajax_fetch_product_info.php',
			type:  'post',
										
			success:  function (response) {
				$("#modal_productdata").data('bs.modal').isShown = true;
				$("#modal_productdata").removeClass("wait");
				console.log(response);
				var pinfo = JSON.parse(response);
				if(pinfo["error"] == 0 && pinfo["product_image"]["error"] == 0) {
					$("#mpd_mainpicture").val(pinfo["product_image"]["filename"]);
					$("#product_img_mainpic").attr("src","../../data/img/game_imgs/" + pinfo["product_image"]["filename"]+"?"+rand(1,999999));
				} else alert("Error cargando img");
			}
		});
	});
	
	$("#mpd_btn_save").click(function() { 
		$("#mpd_btn_save").attr("disabled",true);
		$("#modal_productdata").addClass("wait");
		$("#modal_productdata").data('bs.modal').isShown = false;
		if(insert_mode == false) saveProductInfo();
		else if(insert_mode == true) insertProduct();
	});
	
	
	$("#mpd_delete_product").click(function() {
		if(confirm("Eliminar producto ID "+current_id+"?")) {
			var imgname = $("#mpd_mainpicture").val();
			$("#modal_productdata").addClass("wait");
			$.ajax({
				data:  { product_id: current_id, action: "delete", image: imgname },
				url:   'scripts/php/ajax_product_modifications.php',
				type:  'post',				
				success:  function (response) {
					$("#modal_productdata").removeClass("wait");
					if(response == 1) location.reload();
					else alert(response);	
				}
			});
		}
	});

	/****/
	
});


function applyProductsOrder()
{
	var result = $("#sortable").sortable("toArray");
	$.ajax({
		data:  { products_array: result, action: "reorder" },
		url:   "scripts/php/ajax_product_modifications.php",
		type:  "post",				
		beforeSend: function() {
			$("#btn_reorder").addClass("wait");
		},
		success:  function (response) {
			if(response == 1) location.reload();
			else alert(response);
		}
	});
}

function cleanProductDetailsModal()
{
	$("#modal_productdata input").val("");
	$("#modal_productdata select").prop("selectedIndex", 0);
	$("#mpd_steam_discount_price").val("");

	$("#mpd_delete_product").hide();
	$("#mpd_productid").html("ID: ");
	$("#mpd_site_url").attr("placeholder","URL de tienda de Steam");
	$("#mpd_limitedunits").prop("disabled", true);
	$("#mpd_limitedunits").val(0);
	$(".customprice_currency").text("USD");
	$("#mpd_customprice_currency").prop("disabled", true);	
	$("#mpd_limitedoffer_endtime").prop("disabled",true);
	$("#mpd_limitedoffer_endtime").val("0000-00-00 00:00:00");
	$("#mpd_screenshots_details").html("");
	$("#product_img_mainpic").attr("src","");
	$("#mpd_description").val("");
	$("#steam_link, #steamdb_link").attr("href", "#").show();
	
}

function loadProductInfo(productid)
{
	$("#modal_productdata").data('bs.modal').isShown = false;
	$("#modal_productdata").addClass("wait");
	
	$.ajax({
		data:  { action: "get", product_id: productid },
		url:   'scripts/php/ajax_product_modifications.php',
		type:  'post',
									
		success:  function (response) {

			var pinfo = JSON.parse(response);

			// llenar el cuadro de información del producto
			$("#hidden_productid").val(productid);
			
			$("#mpd_productname").val(pinfo["product_name"]);
			$("#mpd_productid").html("ID: <a href='../../juegos/" + pinfo["product_id"] + "/' target='_blank'>" + pinfo["product_id"] + "</a>");
			if(pinfo["product_enabled"] == 0) $("#mpd_activestate")[0].selectedIndex = 1;
			if(pinfo["product_enabled"] == 1) $("#mpd_activestate")[0].selectedIndex = 0;
			
			$("#mpd_platform")[0].selectedIndex = parseInt(pinfo["product_platform"]) - 1;
			$("#mpd_sellingsite")[0].selectedIndex = (pinfo["product_sellingsite"] - 1);
			$("#mpd_site_url").val(pinfo["product_site_url"]);
			
			$("#steam_link").attr("href",pinfo["product_site_url"]);
			if(pinfo["product_sellingsite"] == 1) {
				var regex = /^(https?:\/\/)?store\.steampowered\.com\/(sub|app)\/([0-9]{1,10})(.*)?$/;
				var matches = pinfo["product_site_url"].match(regex);
				if(matches != null) {
					var steamdb_link = "https://steamdb.info/"+matches[2]+"/"+matches[3]+"/";
					$("#steamdb_link").attr("href",steamdb_link);
				}
			} else $("#steamdb_link").hide();

			$("#mpd_has_limitedunits")[0].selectedIndex = pinfo["product_has_limited_units"];
			$("#mpd_has_limitedunits").trigger("change");
			
			$("#mpd_limitedunits").val(pinfo["product_limited_units"]);	
			
			$("#mpd_has_customprice")[0].selectedIndex = pinfo["product_has_customprice"];
			$("#mpd_has_customprice").trigger("change");

			if(pinfo["product_customprice_currency"] == "usd") $("#mpd_customprice_currency")[0].selectedIndex = 0;
			else if(pinfo["product_customprice_currency"] == "ars") $("#mpd_customprice_currency")[0].selectedIndex = 1;
			$("#mpd_customprice_currency").trigger("change");

			$("#mpd_ext_limitedoffer")[0].selectedIndex = pinfo["product_external_limited_offer"];
			$("#mpd_ext_limitedoffer").trigger("change");		
			$("#mpd_limitedoffer_endtime").val(pinfo["product_external_offer_endtime"]);
			
			$("#mpd_listprice").val(pinfo["product_listprice"])
			$("#mpd_finalprice").val(pinfo["product_finalprice"]);
			$("#mpd_steam_discount_price").val(pinfo["product_steam_discount_price"]);
			
			$("#mpd_pics").val(pinfo["product_pics"]);
			$("#mpd_pics").trigger("change");
			$("#mpd_tags").val(pinfo["product_tags"]);
			$("#mpd_sp")[0].selectedIndex = pinfo["product_singleplayer"];
			$("#mpd_mp")[0].selectedIndex = pinfo["product_multiplayer"];
			$("#mpd_coop")[0].selectedIndex = pinfo["product_cooperative"];
			
			$("#mpd_mainpicture").val(pinfo["product_mainpicture"]);
			$("#mpd_mainpicture").trigger("change");
			$("#mpd_description").val(pinfo["product_description"]);
			
			$("#mpd_delete_product").css("display","block");	
			$("#modal_productdata").data('bs.modal').isShown = true;
			$("#modal_productdata").removeClass("wait");
		}
	});	
	
}

function fetchProductInfo(siteurl) 
{
	$("#modal_productdata").data('bs.modal').isShown = false;
	$.ajax({
		data:  { steam_url: siteurl, data_requested:"n,p,d,h,s,t" },
		url:   'scripts/php/ajax_fetch_product_info.php',
		type:  'post',
									
		success:  function (response) {
			
			$("#modal_productdata").data('bs.modal').isShown = true;
			$("#modal_productdata").removeClass("wait");

			var result = JSON.parse(response);
			console.log(result);
			
			if(result["error"] != 0) {
				alert("Error, la clase steamProduct no cargó la URL correctamente");
				return;
			}
			
			if(result["product_name"]["error"] == 0) {
				$("#mpd_productname").val(result["product_name"]["value"]);
			} else console.log("error product_name, cod:"+result["product_name"]["error"]);
			
			if(result["product_image"]["error"] == 0) {
				$("#mpd_mainpicture").val(result["product_image"]["filename"]);
				$("#mpd_mainpicture").trigger("change");
			} else console.log("error product_image, cod:"+result["product_image"]["error"]);
			
			if(result["product_screenshots"]["error"] == 0) {
				$("#mpd_pics").val(result["product_screenshots"]["value"]);
				$("#mpd_pics").trigger("change");
			} else console.log("error product_screenshots, cod:"+result["product_screenshots"]["error"]);
			
			if(result["product_description"]["error"] == 0) {
				$("#mpd_description").val(result["product_description"]["value"]);
			} else console.log("error product_description, cod:"+result["product_description"]["error"]);
			
			if(result["product_price"]["error"] == 0) {
				if(result["product_price"]["product_discount"] == 0) {
					$("#mpd_ext_limitedoffer").prop("selectedIndex", 0);
					$("#mpd_ext_limitedoffer").trigger("change");
					$("#mpd_listprice").val(result["product_price"]["product_finalprice"]);
					$("#mpd_finalprice").val(result["product_price"]["product_finalprice"]);
					$("#mpd_steam_discount_price").val(0);
					$("#mpd_limitedoffer_endtime").val("0000-00-00 00:00:00");
				} else if(result["product_price"]["product_discount"] == 1) {
					$("#mpd_ext_limitedoffer").prop("selectedIndex", 1);
					$("#mpd_ext_limitedoffer").trigger("change");
					$("#mpd_listprice").val(result["product_price"]["product_firstprice"]);
					$("#mpd_finalprice").val(result["product_price"]["product_finalprice"]);
					$("#mpd_steam_discount_price").val(result["product_price"]["product_finalprice"]);
					if(result["product_price"]["product_discount_endtime"] != "n/a") {
						$("#mpd_limitedoffer_endtime").val(result["product_price"]["product_discount_endtime"]);
					} else $("#mpd_limitedoffer_endtime").val("0000-00-00 00:00:00");
				}
			} else console.log("error product_price, cod:"+result["product_price"]["error"]);
			
			if(result["product_tags"]["error"] == 0) {
				$("#mpd_tags").val(result["product_tags"]["value"]);	
			} else console.log("error product_tags, cod:"+result["product_tags"]["error"]);
		}
	});
	
}


function saveProductInfo()
{
	var product_enabled;
	if($("#mpd_activestate").prop("selectedIndex") == 0) product_enabled = 1;
	else if($("#mpd_activestate").prop("selectedIndex") == 1) product_enabled = 0;
	var product_update = {
		"product_id": current_id,
		"product_enabled": product_enabled,
		"product_name": $("#mpd_productname").val(), 
		"product_platform": ($("#mpd_platform").prop("selectedIndex") + 1), 
		"product_sellingsite": ($("#mpd_sellingsite").prop("selectedIndex") + 1),
		"product_site_url": $("#mpd_site_url").val(),
		"product_has_limited_units": $("#mpd_has_limitedunits").prop("selectedIndex"),
		"product_limited_units": $("#mpd_limitedunits").val(),
		"product_has_customprice": $("#mpd_has_customprice").prop("selectedIndex"),
		"product_customprice_currency": $("#mpd_customprice_currency option:selected").text().toLowerCase(),
		"product_external_limited_offer": $("#mpd_ext_limitedoffer").prop("selectedIndex"),
		"product_external_offer_endtime": $("#mpd_limitedoffer_endtime").val(),
		"product_listprice": $("#mpd_listprice").val(),
		"product_finalprice": $("#mpd_finalprice").val(),
		"product_steam_discount_price": $("#mpd_steam_discount_price").val(),
		"product_mainpicture": $("#mpd_mainpicture").val(),
		"product_pics": $("#mpd_pics").val(),
		"product_description": $("#mpd_description").val(),
		"product_tags": $("#mpd_tags").val(),
		"product_singleplayer": $("#mpd_sp").prop("selectedIndex"),
		"product_multiplayer": $("#mpd_mp").prop("selectedIndex"),
		"product_cooperative": $("#mpd_coop").prop("selectedIndex")
	};
	
	$.ajax({
		data:  { action: "set", product_data: product_update },
		url:   'scripts/php/ajax_product_modifications.php',
		type:  'post',
										
		success:  function (response) {
			$("#modal_productdata").removeClass("wait");
			$("#modal_productdata").data('bs.modal').isShown = true;
			$("#mpd_btn_save").attr("disabled",false);
			if(response == 1) { 
				$('#modal_productdata').modal('hide');
				$("#p"+current_id+" .pcp_middle").html($("#mpd_productname").val()+" <span style='color:#900;font-size:11px'>(Editado)</span>");
			} else {
				alert(response);
			}
		}
	});

}

function insertProduct()
{
	var product_enabled;
	if($("#mpd_activestate").prop("selectedIndex") == 0) product_enabled = 1;
	else if($("#mpd_activestate").prop("selectedIndex") == 1) product_enabled = 0;
	var product_data = {
		"product_enabled": product_enabled,
		"product_name": $("#mpd_productname").val(), 
		"product_platform": ($("#mpd_platform").prop("selectedIndex") + 1), 
		"product_sellingsite": ($("#mpd_sellingsite").prop("selectedIndex") + 1),
		"product_site_url": $("#mpd_site_url").val(),
		"product_has_limited_units": $("#mpd_has_limitedunits").prop("selectedIndex"),
		"product_limited_units": $("#mpd_limitedunits").val(),
		"product_has_customprice": $("#mpd_has_customprice").prop("selectedIndex"),
		"product_customprice_currency": $("#mpd_customprice_currency option:selected").text().toLowerCase(),
		"product_external_limited_offer": $("#mpd_ext_limitedoffer").prop("selectedIndex"),
		"product_external_offer_endtime": $("#mpd_limitedoffer_endtime").val(),
		"product_listprice": $("#mpd_listprice").val(),
		"product_finalprice": $("#mpd_finalprice").val(),
		"product_steam_discount_price": $("#mpd_steam_discount_price").val(),
		"product_mainpicture": $("#mpd_mainpicture").val(),
		"product_pics": $("#mpd_pics").val(),
		"product_description": $("#mpd_description").val(),
		"product_tags": $("#mpd_tags").val(),
		"product_singleplayer": $("#mpd_sp").prop("selectedIndex"),
		"product_multiplayer": $("#mpd_mp").prop("selectedIndex"),
		"product_cooperative": $("#mpd_coop").prop("selectedIndex")
	};
	$.ajax({
		data:  { action: "insert", product_data: product_data },
		url:   'scripts/php/ajax_product_modifications.php',
		type:  'post',						
		success:  function (response) {
			$("#modal_productdata").removeClass("wait");
			if(response == 1) applyProductsOrder();
			else {
				$("#mpd_btn_save").attr("disabled",false);	
				$("#modal_productdata").data('bs.modal').isShown = true;
				alert(response);
			}
		}
	});
}


function rand(min,max)
{
    return Math.floor(Math.random()*(max-min+1)+min);
}