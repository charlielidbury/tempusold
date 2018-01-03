<?php

session_start();

if (isset($_SESSION['username']))
{
	include "home.php";
} else {
	include "login.php";
}

?>
