<?php
session_start();
include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure only logged on users past this point
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['HTTP_HOST']}");
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

		<?php table2HTML($conn, "CALL userSessions(?)", "s", $_SESSION['user']); ?>
	</body>
</html>