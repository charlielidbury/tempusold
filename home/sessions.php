<?php
session_start();
include "../src/db.php";

if (!isset($_SESSION['user_data'])) header("Location: ..");
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
		<?php query2Table($conn, "CALL userSessions(?)", "s", $_SESSION['user_data']['name']); ?>
	</body>
</html>
