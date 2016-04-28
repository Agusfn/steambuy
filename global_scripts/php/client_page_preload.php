<?php
error_reporting(0); // Oculta errores

require_once("mysql_connection.php");


if (!$con) {
    ?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" href="<?php echo ROOT_LEVEL; ?>favicon.ico?2">
        <title>Error de la base de datos</title>
    </head>
    <body>
		<?php require_once("g_analytics.php"); ?>
        <h3>Ha ocurrido un error estableciendo una conexión con la base de datos.</h3>
    </body>
    </html>
	<?php
	exit;
}

error_reporting(-1); // Muestra errores nuevamente



$query = mysqli_query($con, "SELECT * FROM banlist WHERE ip = '".$_SERVER["REMOTE_ADDR"]."'");	
if(mysqli_num_rows($query) == 1){
	$banData = mysqli_fetch_array($query);
	?>
    <html>
	    <head>
    	   	<title>Accesso denegado</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta name="robots" content="noindex, nofollow">
        	<link rel="shortcut icon" href="<?php echo ROOT_LEVEL; ?>favicon.ico?2">
            <link rel="stylesheet" href="<?php echo ROOT_LEVEL; ?>global_design/css/main.css?2" type="text/css">
            
            <style type="text/css">
			.sq
			{
				position:absolute; 
				width:760px; 
				top:50%;
				left:50%;
				margin: -80px 0 0 -380px;
				padding:10px 10px 10px 10px;		
				background-color:#FBFBFB;
				border:1px solid #AAA;
				color:#444;
				border-radius: 4px;
			}
			.title
			{
				font-size:24px;
				margin:7px 0 0 50px;
				color: rgba(82, 126, 204, 1);
			}	 
			.text
			{
				margin:10px 35px 10px 35px;
				line-height:140%;
				font-size:17px;	
			}
			</style>
            
       	</head>
        <body>  
        	<?php require_once("g_analytics.php"); ?>       
       		<div class="sq">  
            	<div class="title">Bloqueado</div>
                <div class="text">
                	<strong>Has sido bloqueado permanentemente de SteamBuy.<br/></strong> Razón: <?php echo $banData["reason"]; ?>
                </div>
            </div>
        </body>
	</html>           
    <?php
	exit;
} 

?>