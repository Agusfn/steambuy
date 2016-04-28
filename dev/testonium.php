<?php
/*require_once("shared_scripts/connection_data.php");
require_once("shared_scripts/swiftmailer/lib/swift_required.php");

$sql = "SELECT * FROM sorteo WHERE game_key != ''";
$query = mysql_query($sql,$connection);


while($res = mysql_fetch_array($query)) {

	$name = explode(" ", $res["name"]);

	$titulo = "Ganaste el sorteo de SteamBuy! Recibiste el juego ". $res["game_name"];
	
	$mensaje = "Estimado/a ".$name[0].", <strong>felicidades, sos uno de los ganadores!</strong> Gracias por participar en el sorteo y en la encuesta para ayudarnos a mejorar, y gracias por comprar con nosotros!.<br/><br />
	A continuación podés ver la clave de activación de tu juego y la plataforma en donde activarlo:<br/>

	<strong>".$res["game_name"]."</strong>: ".$res["game_key"]."
	<br />
	<br />
	Un saludo y esperamos volver a verte!";
	
	$transport = Swift_SmtpTransport::newInstance("smtp.gmail.com", 587,"tls");
	$transport->setUsername("admin@steambuy.com.ar");
	$transport->setPassword("galapagos235");
	$mailer = Swift_Mailer::newInstance($transport);
	$message = Swift_Message::newInstance();
	$message->setSubject($titulo);
	$message->setFrom(array("admin@steambuy.com.ar" => "SteamBuy"));
	$message->setTo(array($res["email"]));
	$message->setBody($mensaje, "text/html");
	$result = $mailer->send($message);

}*/


/*if(isset($_POST["data"])) {
	
	$query = mysql_query("SELECT * FROM sorteo",$connection);
	$count = mysql_num_rows($query);
	
	
	$games = explode("\n", $_POST["data"]);

	
	$winners = array();
	$i = 0;
    while(count($winners) < 22) {
        $x = mt_rand(1,$count);
        if(!in_array_r($x,$winners)) { 
			$winners[$i] = array($x,$games[$i]); 
			$i++;
		}
    }
     
	for($i = 0; $i < count($winners); $i++)
	{
		//var_dump($winners[$i]);
		
		$game = explode("=", $winners[$i][1]);
		
		
		$sql = "UPDATE sorteo SET game_name = '".$game[0]."', game_key = '".trim($game[1])."' WHERE count = ". $winners[$i][0];
		
		//mysql_query($sql, $connection);
	}
}*/



function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

?>
<form action="" method="post">
<textarea name="data"></textarea>
<input type="submit" />
</form>