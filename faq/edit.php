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
} else return;


?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <meta name="robots" content="noindex, nofollow" />
        
        <title>Editar preguntas frecuentes - SteamBuy</title>


        <link rel="shortcut icon" href="../favicon.ico?2"> 
        
        <link rel="stylesheet" href="../global_design/font-awesome-4.1.0/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/bootstrap-3.1.1/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="../global_design/css/main.css?2" type="text/css">
        <link rel="stylesheet" href="design/faq_pg.css?2" type="text/css">
        
		<script type="text/javascript" src="../global_scripts/js/jquery-1.8.3.min.js"></script>     
        <script type="text/javascript" src="../global_design/jquery-ui-1.11.0/jquery-ui.min.js"></script>  
        <script type="text/javascript" src="../global_design/bootstrap-3.1.1/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="../global_scripts/js/global_scripts.js?2"></script>
		
        <script type="text/javascript">
		$(document).ready(function(e) {
			$("#accordion").sortable();
			$("#accordion").disableSelection();

			$("#boton").click(function(e) {
                var a = $("#accordion").sortable("toArray");
				
				$.ajax({
					data:{faq_array:a},
					url:"scripts/ajax_faqorder.php",
					type:"post",
					
					success: function(response) {
						location.reload(); 
					}
				});
			 });
        });
		</script>
        
    </head>
    
    <body>

		<?php require_once("../global_scripts/php/header.php"); ?>
        
        <div class="wrapper">
        	
            <div class="main_content">
                
                <h3 class="main_title">Preguntas frecuentes</h3><button id="boton" class="btn btn-primary" style="margin-bottom:20px;">Aplicar</button>            	
                <div class="panel-group" id="accordion">
                	
                    <?php
					$res = mysqli_query($con, "SELECT * FROM faq ORDER BY `order` ASC");
					$i = 0;
					while($faq=mysqli_fetch_assoc($res)) {
						$i+=1;
						?>
						<div class="panel panel-default" id="<?php echo $faq["number"]; ?>">
                            <div class="panel-heading">
                                <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="<?php echo "#collapse".$faq["number"]; ?>" name="<?php echo $faq["number"]; ?>"><?php echo $faq["question"]; ?></a></h4>
                            </div>
                            <div id="<?php echo "collapse".$faq["number"]; ?>" class="panel-collapse collapse">
                                <div class="panel-body"><?php echo $faq["answer"]; ?></div>
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

