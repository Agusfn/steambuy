<?php
session_start();

define("ROOT_LEVEL", "../");

header("Content-Type: text/html; charset=UTF-8");

require_once("../global_scripts/php/client_page_preload.php");
require_once("../global_scripts/php/admlogin_functions.php");
require_once("../global_scripts/php/main_purchase_functions.php");


$admin = false;
if(isAdminLoggedIn())
{
	$admin = true;
}

if(isset($_GET["v"])) {
	if(is_numeric($_GET["v"])) mysqli_query($con, "UPDATE `faq` SET `visits` = `visits` + 1 WHERE `order` = ".mysqli_real_escape_string($con, $_GET["v"]).";");
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" itemscope itemtype="http://schema.org/Article">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <title>Preguntas frecuentes - SteamBuy</title>
        
        <meta name="description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes.">
        <meta name="keywords" content="steambuy,preguntas,frecuentes,lista,duda,problema">
        
        <meta property="og:title" content="Preguntas frecuentes" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="http://steambuy.com.ar/faq/" />
        <meta property="og:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg" />
        <meta property="og:site_name" content="SteamBuy" />
        <meta property="og:description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes." />
        
        <meta name="twitter:card" content="summary">
        <meta name="twitter:url" content="http://steambuy.com.ar/faq/">
        <meta name="twitter:title" content="Preguntas frecuentes">
        <meta name="twitter:description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes.">
        <meta name="twitter:image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        <meta itemprop="name" content="Preguntas frecuentes">
        <meta itemprop="description" content="Si tienes alguna consulta aquí podrás revisar la lista completa de preguntas frecuentes.">
        <meta itemprop="image" content="http://steambuy.com.ar/global_design/img/logo-complete-meta.jpg">
        
        
        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/faq_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>

		<script type="text/javascript">
		
		$(document).ready(function(e) {
            if(window.location.hash) {
				var qnumber = window.location.hash.substring(1);
				if(qnumber.indexOf("c") > -1) {
					$("#"+qnumber).collapse("show");
				} else {
					$("#c"+qnumber).collapse("show");
				}
			}
        });
		
		</script>
        
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="main_title">Preguntas frecuentes</h3>
            	
                <div class="panel-group" id="accordion">
                	
                    <?php
					$res = mysqli_query($con, "SELECT * FROM faq ORDER BY `order` ASC");
					while($faq=mysqli_fetch_assoc($res)) {
						?>
						<div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="<?php echo "#c".$faq["number"]; ?>" name="<?php echo $faq["number"]; ?>"><?php echo $faq["question"]; ?></a></h4>
                            </div>
                            <div id="<?php echo "c".$faq["number"]; ?>" class="panel-collapse collapse">
                                <div class="panel-body"><?php echo nl2br($faq["answer"]); ?></div>
                            </div>
              			</div>
                        <?php
					}
					?>
				</div>
            </div><!-- End main content -->
            
        	<?php require_once("../global_scripts/php/footer.php"); ?>
        	
        </div><!-- End container -->
    </body>
    
    
</html>

