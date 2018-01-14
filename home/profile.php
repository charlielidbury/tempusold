<?php
session_start();
include "../src/db.php";

if (!isset($_SESSION['user_data'])) header("Location: ..");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Profile</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<pre><?php //die(var_export($_SESSION['user_data'], true)) ?></pre>
		<h1><a href="..">Tempus</a></h1>
		<h2><a href=".">Home</a></h2>
		<h3><a href="profile.php">Profile</a></h3>
		<p>Profile details:</p>
		<?php
		// adds permissions to the table
		$extra = '<tr><td>Permissions</td><td>';
		$extra .= row2HTML($conn, "role", "role", $_SESSION['user_data']['role']);
		$extra .= '</td></tr>';
		echo row2HTML($conn, "view_employee", "name", $_SESSION['user_data']['name'], $extra);
		?>
	</body>
</html>
