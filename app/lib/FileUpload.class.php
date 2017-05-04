<?php


class FileUpload {
	
	public $uploadError;
	public $file_name; // se obtiene una vez que se guarda	
	
	private $var_name;
	
	private $file_extension;
	private $tmp_file_name;

	
	/* Inicializar. $file_var_name sería el "name" en el input file del archivo. -> $_FILE["<name>"]
	*/
	public function __construct($file_var_name) {
		$this->var_name = $file_var_name;
	}
	
	
	/* Método para revisar si el archivo subido es válido. (No tiene errores, es del tipo solicitado, no supera el tamaño indicado).
	*/
	public function valid_file($max_size_mb, $necessary_file_type) {
		
		$max_size = $max_size_mb * 1048576; // bytes
		
		if(!isset($_FILES[$this->var_name])) return false;
		
		if($_FILES[$this->var_name]["error"] == 0) {
			
			$fileType = $_FILES[$this->var_name]["type"];
			$fileSize = intval($_FILES[$this->var_name]["size"]);
			
			if($necessary_file_type == "img") { // Por ahora el único formato

				if($fileType == "image/png" || $fileType == "image/jpeg") {
					
					if($fileSize <= $max_size) {
						
						$split = explode("image/",$fileType);
						
						$this->file_extension = ".".$split[1];
						$this->tmp_file_name = $_FILES[$this->var_name]["tmp_name"];
						return true;
						
					} else {
						$this->uploadError = "El archivo supera el tamaño máximo permitido (".$max_size_mb." MB).";
						return false;
					}
					
				} else {
					$this->uploadError = "Se ha enviado un archivo con formato inválido.";
					return false;
				}
				
			} else return false;

		} else {
			$this->uploadError = "Se ha producido un error subiendo el archivo, intentalo nuevamente en un momento.";
			return false;
		}
		
	}
	
	
	/* Método para guardar archivo subido (debe ser válido) en directorio.
	*/
	public function save_file($file_name_title, $to_dir) {
		
		$file_name = $file_name_title.$this->file_extension;
		if(move_uploaded_file($this->tmp_file_name, $to_dir . $file_name)) {
			$this->file_name = $file_name;
			return true;
		} else return false;
			
	}

	
	
}


?>