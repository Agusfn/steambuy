<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");




$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}


function createFaqCollapseNode($branch_dir, $collapse_count, $title, $content) {
	$headingId = "heading-".$branch_dir."-".$collapse_count;
	$collapseId = "collapse-".$branch_dir."-".$collapse_count;
	$collapse = "
	<div class='panel panel-default faq2-panel'>
    	<a role='button' data-toggle='collapse' data-parent='#accordion-".$branch_dir."' href='#".$collapseId."' aria-expanded='false' aria-controls='".$collapseId."'>
        	<div class='panel-heading' role='tab' id='".$headingId."'><h4 class='panel-title'>".$title."</h4></div>
        </a>
        <div id='".$collapseId."' class='panel-collapse collapse' role='tabpanel' aria-labelledby='".$headingId."'>
        	<div class='panel-body'>".$content."</div>
		</div>
	</div>";
	
	return $collapse;
}


function developCategoryContentTree($content_tree, $branch_dir) {

	$accordion = "<div class='panel-group' id='accordion-".$branch_dir."' role='tablist' aria-multiselectable='true'>"; // accordion-1 (dentro del primer collapse del main accordion)
	$collapse_count = 0;
	if(isset($content_tree["qa"])) {
		if(!isset($content_tree["qa"][0])) { // Si está definido ["qa"] pero el índice (0) no existe, es una sóla pregunta
			$questions[0] = $content_tree["qa"]; 
		} else $questions = $content_tree["qa"]; 
		foreach($questions as $question) {
			$collapse_count += 1;
			$accordion .= createFaqCollapseNode($branch_dir, $collapse_count, $question["question"], $question["answer_id"]);
		}
	}
	if(isset($content_tree["subcategory"])) {	
		if(!isset($content_tree["subcategory"][0])) { // Si está definido ["subcategory"] pero el índice (0) no existe, es una sóla subcategoría
			$subcategories[0] = $content_tree["subcategory"]; 
		} else $subcategories = $content_tree["subcategory"]; 
		foreach($subcategories as $subcategory) {
			$collapse_count += 1;
			$subcategory_content = developCategoryContentTree($subcategory["content"], $branch_dir."-".$collapse_count);
			$accordion .= createFaqCollapseNode($branch_dir, $collapse_count, $subcategory["title"], $subcategory_content);
		}
	}
	$accordion .= "</div>";
	
	return $accordion;
}


function displaySupportTree() {
	
	$xml = simplexml_load_file("resources/support_tree.xml");	
	$json_string = json_encode($xml);
	$result_array = json_decode($json_string, TRUE);
		
	$main_html = "<div class='panel-group' id='accordion-faq-main' role='tablist' aria-multiselectable='true'>";
	
	
	// Cada categoría tiene un accordion
	$category_n = 0;
	
	if(isset($result_array["category"])) {
		if(!isset($result_array["category"][0])) {
			$categories[0] = $result_array["category"];
		}
		else $categories = $result_array["category"];

		foreach($categories as $category) {
			$category_n += 1;
			
			$headingId = "heading-".$category_n;
			$collapseId = "collapse-".$category_n;
			$category_html = "
			<div class='panel panel-default faq1-panel'>
				<a role='button' data-toggle='collapse' data-parent='#accordion-faq-main' href='#".$collapseId."' aria-expanded='false' aria-controls='".$collapseId."'>
					<div class='panel-heading' role='tab' id='".$headingId."'>
						<h4 class='panel-title'>".$category["title"]."</h4>
					</div>
				</a>
				<div id='".$collapseId."' class='panel-collapse collapse' role='tabpanel' aria-labelledby='".$headingId."'>
					<div class='panel-body'>".developCategoryContentTree($category["content"], $category_n)."</div>
				</div>
			</div>
			";
			
			$main_html .= $category_html;	
		}		 
		 
	}
	$main_html .= "</div>";
	
	echo $main_html;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Soporte - SteamBuy</title>
        
        <meta name="description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos.">
        <meta name="keywords" content="steambuy,comprar,duda,consulta,ayuda,soporte,contacto">
        
        <meta property="og:title" content="Soporte" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/soporte/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/soporte/">
        <meta name="twitter:title" content="Soporte">
        <meta name="twitter:description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Soporte">
        <meta itemprop="description" content="¿Tienes alguna consulta? Revisa en esta página los temas de ayuda o contáctanos.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">


        <link rel="shortcut icon" href="../favicon.ico"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css" type="text/css">
        <link rel="stylesheet" href="resources/css/support.css" type="text/css">

		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js"></script>
		<script type="text/javascript" src=""></script>
        
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">

            	<h3 class="page-title">¿En qué necesitas ayuda?</h3>
            
            	<?php
				
				displaySupportTree();
				
				?>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>