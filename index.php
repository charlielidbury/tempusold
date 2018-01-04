<?php

session_start();

if (isset($_SESSION['user_data']))
{
	include "home.php";
} else {
	include "login.php";
}

?>
