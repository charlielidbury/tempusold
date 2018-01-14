<?php
session_start();
include "../src/db.php";

// makes sure only logged on users past this point
if (!isset($_SESSION['user'])) header("Location: ..");
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
		$extra .= row2HTML($conn, "role", "role", getCell($conn, "role", "employee", "name", $_SESSION['user']));
		$extra .= '</td></tr>';
		echo row2HTML($conn, "view_employee", "name", $_SESSION['user'], $extra);
		?>
	</body>
</html>
