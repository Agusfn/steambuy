<?php

define("ROOT", dirname(__FILE__)."/");
define("ROOT_PUBLIC", dirname(__FILE__)."/public_html/");

define("G_ANALYTICS", "resources/php/g_analytics.php");

// si el server es el servidor local (test)
if($_SERVER["SERVER_NAME"] == "localhost") { 
	
	define("MYSQL_SERVER", "localhost");
	define("MYSQL_USER", "root");
	define("MYSQL_PASS", "20596");
	define("MYSQL_DATABASE", "steambuy_av");
	
	define("PUBLIC_URL", "http://localhost/steambuy/public_html/");
	
} else { // Sino se asume que es el de producción
	
	define("MYSQL_SERVER", "localhost");
	define("MYSQL_USER", "steambuy_dbadmin");
	define("MYSQL_PASS", "ld}u{@zl(x^4");
	define("MYSQL_DATABASE", "steambuy_db");
	
	define("PUBLIC_URL", "http://steambuy.com.ar/");
	
}
?>