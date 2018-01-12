<?php
session_start();
include "../src/db.php";

if ($_SESSION['user_data']['perms']['members'] == "none")
	header("Location: ../permission_denied.php");

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Members</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="index.php">Tempus</a></h1>
		<h2><a href=".">Home</a></h2>
		<h3><a href="profile.php">Members</a></h3>
		<?php
		query2Table($conn, "SELECT * FROM `view_employee`");
		?>
	</body>
</html>
