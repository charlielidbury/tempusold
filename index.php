<?php

session_start();

if (isset($_SESSION['user']))
	header("Location: /home/");
else
	header("Location: login.php?redirect=/home/");

?>
