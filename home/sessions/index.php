<?php
session_start();
include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure only logged on users past this point
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}");

// makes sure only users with view session perms past this point
if (!hasPerms("sessions", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php")

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Sessions</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3><a href="/home/sessions/">Sessions</a></h3>

		<ul>
			<li><a href="create_session.php?redirect=index.php">Create Session</a></li>
		</ul>

		<?php table2HTML("SELECT * FROM `view_session`"); ?>
	</body>
</html>
