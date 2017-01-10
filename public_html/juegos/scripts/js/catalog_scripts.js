
$(document).ready(function() {  
	
	$("#filter_site_buttons").children("button").click(function() {
		var index = $(this).index() + 1;
		switch(index) {
			case 1: 
				if($(this).hasClass("active")) so_f_st="&st=0";
				else so_f_st="";
			break;
			case 2:
				if($(this).hasClass("active")) so_f_stb="&stb=0";
				else so_f_stb="";
			break;
			case 3:
				if($(this).hasClass("active")) so_f_amz="&amz=0";
				else so_f_amz="";
			break;
			case 4:	
				if($(this).hasClass("active")) so_f_hb="&hb=0";
				else so_f_hb="";
			break;
			case 5:
				if($(this).hasClass("active")) so_f_bs="&bs=0";
				else so_f_bs="";
			break;
		}
		if($(this).hasClass("active")) {
			$(this).removeClass("active");	
		} else {
			$(this).addClass("active");	
		}
		window.location = "../juegos/?"+so_query+so_order+so_f_st+so_f_stb+so_f_amz+so_f_hb+so_f_bs+so_f_gm+so_pg;
	});
	
	
	$("#filter_order_buttons").children("button").click(function () {
		if(!$(this).hasClass(".active")) {
			$(this).siblings().removeClass("active");
			$(this).addClass("active");
		}
		var index = $(this).index() + 1;
		switch(index) {
			case 1: so_order="&order=1"; break;
			case 2: so_order="&order=2"; break;
			case 3: so_order="&order=3"; break;
		}
		window.location = "../juegos/?"+so_query+so_order+so_f_st+so_f_stb+so_f_amz+so_f_hb+so_f_bs+so_f_gm+so_pg;
	});
	
	$("#filter_gamemode_buttons").children("button").click(function() {
		if(!$(this).hasClass("active")) {
			$(this).siblings().removeClass("active");
			$(this).addClass("active");	
			so_f_gm = "&gm="+($(this).index() + 1);
		} else {
			$(this).removeClass("active");
			so_f_gm = "&gm=0";
		}
		window.location = "../juegos/?"+so_query+so_order+so_f_st+so_f_stb+so_f_amz+so_f_hb+so_f_bs+so_f_gm+so_pg;
	});
	
	
	
});  

