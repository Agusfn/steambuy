<?php
define("FILE_NAME", "giftid_assoc_list.txt");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/javascript");


if(isset($_GET["action"]) && isset($_GET["code"])) {
	
	if($_GET["code"] != "0171373c5ca17801ed3bb5dba6c923e9042c03b3") exit;
	
	
	$action = $_GET["action"];
	
	if(!file_exists(FILE_NAME)) {
		file_put_contents(FILE_NAME, "");	
	}
	
	if($action == "obtain") {
		
		echo file_get_contents(FILE_NAME);

	} else if($action == "add") {
		
		$result = array("error"=>0, "error_text"=>""); // Error: 1: no se suministro data, 2: data formato invalido, 3:ya se hizo esta asociacion, 4: ya hay otra id de pedido asociada a este gift, 5: ya hay otro gift asociado a id de pedido,
		
		if(isset($_GET["line"])) {
			
			$line = $_GET["line"];
			if(preg_match("/([0-9]{15,25}):(J[0-9]{4,7})/", $line, $matches1)) {
				
				$content = file_get_contents(FILE_NAME);

				if(preg_match("/".$line."/", $content)) { // Si existe la misma asociacion
					
					$result["error"] = 3;
					$result["error_text"] = "El pedido ".$matches1[2]." ya esta asociado al gift ".$matches1[1];
					
				} else if(preg_match("/".$matches1[1].":(J[0-9]{4,7})/", $content, $matches2)) { // Si hay otra asociacion con ese gift

					$result["error"] = 4;
					$result["error_text"] = "El gift ".$matches1[1]." esta asociado a otro ID de pedido: ".$matches2[1];
					
				} else if(preg_match("/([0-9]{15,25}):".$matches1[2]."/", $content, $matches3)) { // Si hay otra asociacion con ese id de pedido
					
					$result["error"] = 5;
					$result["error_text"] = "El ID de pedido ".$matches1[2]." esta asociado a otro gift: ".$matches3[1];
				
				} else {
					
					if($content == "") {
						file_put_contents(FILE_NAME, "START==".$line);
					} else {
						file_put_contents(FILE_NAME, $content.",".$line);
					}
				}
				
			} else $result["error"] = 2;
			
		} else $result["error"] = 1;
		
		echo json_encode($result);
	}	
	
}


?>