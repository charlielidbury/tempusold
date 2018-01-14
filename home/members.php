<?php
session_start();
include "../src/db.php";

// makes sure only people who are logged on go past this point
if (!isset($_SESSION['user'])) header("Location: ..");

// permission check
if (hasPerms($conn, "members", "none"))
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
		<h3><a href="members.php">Members</a></h3>

		<?php table2HTML($conn, "SELECT * FROM `view_employee`");	?>
	</body>
</html>
