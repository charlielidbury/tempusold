<?php
session_start();
include "../src/db.php";

if ($_SESSION['user_data']['perms']['members'] == "none")
	header("Location: ../permission_denied.php");

if (!isset($_SESSION['user_data'])) header("Location: ..");
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

		<?php
		if (isset($_GET['user']))
		{
			// PAGE FOR EDITING A SPECIFIC MEMBER
			if ($_SESSION['user_data']['perms']['members'] == "edit")
			{
				include "edit_employee.php";
			} else {
				header("Location: ../permission_denied.php");
			}
		} else {
			// PAGE FOR VIEWING ALL MEMBERS
			table2HTML($conn, "SELECT * FROM `view_employee`");
		}
		?>
	</body>
</html>
