<?php


class MysqlHelp {
	
	public $con;
		
	public function __construct($con) {
		$this->con = $con;
	}
	

	/* Función para obtener el primer valor de la primer y única fila que se supone que debe arrojar como resultado la consulta
	   Return: FALSE si hay un error.
	*/
	public function fetch_value($query) {
		if($query = mysqli_query($this->con, $query)) {
			$results = mysqli_num_rows($query);	
			if($results == 1) {
				$row = mysqli_fetch_row($query);
				return $row[0];	
			} else return false;
		} else return false;	
	}


	/* Función para obtener un array asociativo con los valores de la fila que arroje como result. la consulta
	Return: array, o false si hay error.
	*/
	public function fetch_row($query) {
		if($query = mysqli_query($this->con, $query)) {
			$results = mysqli_num_rows($query);	
			if($results == 1) {
				return mysqli_fetch_assoc($query);	
			} else return false;
		} else return false;
	}
	
	
	public function update_table($sql) {
		if(!mysqli_query($this->con, $sql)) return false;
		if(mysqli_affected_rows($this->con) >= 1) return true;
		else return false;
	}

	public function insert_into_table($sql) {
		return mysqli_query($this->con, $sql);	
	}	
	
	public function escape_str($str) {
		return mysqli_real_escape_string($this->con, $str);
	}
	
	
	public function error() {
		return mysqli_error($this->con);	
	}
	
	
}



?>