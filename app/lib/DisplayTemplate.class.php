<?php
/*
 Clase de prueba para mostrar el template del sitio web con algun contenido dentro. Usado inicialmente para mensajes de error/success, etc.
*/

class DisplayTemplate {
	
	private $pageHtml;
	
	// Cargar template
	/*public function __construct() {
		
	}*/


	// Insertar contenido html a .main_content
	public function insert_content($content_html, $page_title) {
		global $loggedUser;
		global $con;
		ob_start();
		require ROOT."app/template/base_template.php";
		$this->pageHtml = ob_get_clean();
	}
	
	// Mostrar pag
	public function display_rendered_html() {
		echo $this->pageHtml;	
	}
	
}

?>