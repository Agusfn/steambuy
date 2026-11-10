<?php 
$hash = "674f177cae4ab4b4e7aad5d7ea6bb600b510c035048ea94a62024e1d42f024b2400d5f0ce122363a";

function isAdminLoggedIn()
{
	global $hash;	
	
	if(isset($_SESSION["apw"])) {
		if($_SESSION["apw"] == $hash) {
			return true;
		} else { 
			return false; 
		}
	} else if(isset($_COOKIE["apw"])) {
		if($_COOKIE["apw"] == $hash) {
			$_SESSION["apw"] = $_COOKIE["apw"];
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}
?>