<?php

require_once("../config.php");
require_once(ROOT."app/lib/mysql-connection.php");
require_once(ROOT."app/lib/UserLogin.class.php");

//$userlogin = new UserLogin($con);

//$userlogin->save_auth_cookies(123);
session_start();
var_dump($_SESSION);
unset($_SESSION["login_userid"]);
session_destroy();
var_dump($_SESSION);
//echo $selector;
?>