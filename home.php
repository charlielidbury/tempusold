<?php
session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Home</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>
		<h1><a href="index.php">Tempus</a></h1>
		<h2><a href="home.php">Home</a></h2>
		<h3>Logged in as <?= $_SESSION["user_data"]["name"]; ?></h3>
		<ul>
			<li><a href="profile.php">Profile</a></li>
			<li><a href="wip.php">Members</a></li>
			<li><a href="wip.php">Sessions</a></li>
			<li><a href="wip.php">Payments</a></li>
			<li><a href="logout.php">Logout</a></li>
		</ul>
	</body>
</html>
