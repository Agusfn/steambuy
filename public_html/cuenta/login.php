<?php
require_once "../../config.php";


if(isset($_POST["redir"])) {
	header("Location: ".$_POST["redir"]);
} else header("Location: ".PUBLIC_URL);

?>