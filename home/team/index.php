<?php
session_start();

// makes sure only people who are logged on go past this point
if (!isset($_SESSION['user'])) header("Location: http://{$_SERVER['HTTP_HOST']}");


include  "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// permission check
if (!hasPerms($conn, "team", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Team</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/team">Team</a></h3>

		<ul>
			<li><a href="create_user.php?redirect=index.php">Create new user</a></li>
		</ul>

		<?php table2HTML($conn, "SELECT * FROM `view_employee`");	?>
	</body>
</html>
