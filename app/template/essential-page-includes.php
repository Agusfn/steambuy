<?php
/*
INCLUIR ELEMENTOS IMPORTANTES PARA EL RENDERIZADO/FUNCIONAMIENTO FRONT-END GENERAL

Carga de favicon. 
De CSS: librerías de FA, Bootstrap, y css principal.
Y de JS: Jquery, bootstrap, y global-scripts.js
*/
?>
        <link rel="shortcut icon" href="<?php echo PUBLIC_URL; ?>favicon.ico">
        
        <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>resources/vendors/font-awesome/css/font-awesome.min.css" type="text/css">
        <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>resources/vendors/bootstrap/css/bootstrap.min.css" type="text/css">
        <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>resources/css/main.css" type="text/css">

		<script type="text/javascript" src="<?php echo PUBLIC_URL; ?>resources/vendors/jquery/jquery.min.js"></script>     
        <script type="text/javascript" src="<?php echo PUBLIC_URL; ?>resources/vendors/bootstrap/js/bootstrap.min.js"></script>       
		<script type="text/javascript" src="<?php echo PUBLIC_URL; ?>resources/js/global-scripts.js"></script>