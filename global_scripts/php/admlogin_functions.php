<?php 
$hash = "f653845eefa45805362c784021d115bf27b0545c7a89774ea1d5f6d4d19f3e1aa1e48f8d3a6a8995";

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