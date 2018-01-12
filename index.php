<?php

session_start();

if (isset($_SESSION['user_data']))
{
	header("Location: /home/");
} else {
	include "login.php";
}

?>
