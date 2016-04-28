<?php
require_once("../global_scripts/php/mysql_connection.php");

echo mysqli_real_escape_string($con, "\\");
?>