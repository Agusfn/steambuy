$(document).ready(function() {  
	
	$(".button").on('dragstart', function(event) { event.preventDefault(); });
	
	$('[data-toggle="tooltip"]').tooltip()
	
	var stickyNavTop = $('.nav_bar').offset().top;  
      
	var stickyNav = function(){  
		if($(window).width() > 1000) {
			var scrollTop = $(window).scrollTop();  
			if (scrollTop > stickyNavTop) {   
				if(!$(".nav_bar").hasClass("nav_sticky")) $('.nav_bar').addClass('nav_sticky');  
			} else {  
				if($(".nav_bar").hasClass("nav_sticky")) $('.nav_bar').removeClass('nav_sticky'); 
			}  
		} else {
			if($(".nav_bar").hasClass("nav_sticky")) $('.nav_bar').removeClass('nav_sticky'); 
		}
    	
    };  
    stickyNav();  
    $(window).scroll(function() {  
        stickyNav();  
    });  
	$(window).resize(function(e) {
        if($(window).width() > 1000) {
			if($(".nav_bar").hasClass("nav_top")) $('.nav_bar').removeClass('nav_top'); 
		} else {
			if(!$(".nav_bar").hasClass("nav_top")) $('.nav_bar').addClass('nav_top'); 
			if($(".nav_bar").hasClass("nav_sticky")) $('.nav_bar').removeClass('nav_sticky'); 
		}
    });
	
	
	var keyTimer, root_path, xhr;
	var autoCompleteShown = false;
	var autoCompleteSearching = false;
	
	
	// Guardar directorio raíz para la solicitud Ajax
	if(location.hostname === "localhost") root_abs_path = document.location.origin + "/steambuy/public_html/";
	else root_abs_path = document.location.origin + "/";
	
	$("#search-products-input").on("input", function(e) {
		if(!$(this).is(":focus")) return;
		clearTimeout(keyTimer);
		if($(this).val().length >= 3) {
			keyTimer = setTimeout(function () {
				if(!autoCompleteShown) {
					autoCompleteShown = true;
					$("#search-autocomplete-box").show();
				}
				$("#search-autocomplete-spinner").show();
				
				if(autoCompleteSearching) {
					xhr.abort();
				}
				autoCompleteSearching = true;
				xhr = $.ajax({
					data: { search_term: $("#search-products-input").val()},
					url: root_abs_path + "resources/php/ajax-search-autocomplete.php",
					type: "POST",
					success: function(response) {
						autoCompleteSearching = false;
						results = JSON.parse(response);
						$("#search-autocomplete-spinner").hide();
						clean_search_autocomplete();
						if(results.length > 0) {
							results.forEach(function(result) {
								add_autocomplete_result(root_abs_path + result["url"], result["nombre"]);
							});
						} else hide_search_autocomplete();
					}
				});
			}, 350);
		} else hide_search_autocomplete();
    });
	
	$("#search-products-input").keydown(function(e) {
        if(e.which == 9 || e.which == 27) hide_search_autocomplete();
    });
	
	$(document).click(function(event) { 
    	if(!$(event.target).closest('.nav-search-form').length) {
			hide_search_autocomplete();
		}    
	})
	
	function hide_search_autocomplete() {
		if(autoCompleteShown) {
			autoCompleteShown = false;
			if(autoCompleteSearching) {
				autoCompleteSearching = false;
				xhr.abort();
			}
			clean_search_autocomplete();
			$("#search-autocomplete-box").hide();			
		}
	}
	
	function clean_search_autocomplete() {
		$("#search-autocomplete-box > div").empty();	
	}
	
	function add_autocomplete_result(url, product_name) {
		$("#search-autocomplete-box > div").append("<a href='" + url + "'><div class='search-autocomplete-result'>" + product_name + "</div></a>");	
	}
	

}); 


