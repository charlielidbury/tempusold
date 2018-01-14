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
		<title>Tempus - Sessions</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="index.php"   >Tempus</a></h1>
		<h2><a href="."           >Home</a></h2>
		<h3><a href="sessions.php">Sessions</a></h3>
		<?php table2HTML($conn, "CALL userSessions(?)", "s", $_SESSION['user']); ?>
	</body>
</html>
