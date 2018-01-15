<?php
session_start();

// Checks if logged on
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['DOCUMENT_ROOT']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Home</title>
		<link rel="stylesheet" href="../css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3>Logged in as <?= $_SESSION["user"]; ?></h3>
		<ul>
			<li><a href="/home/team/view_user.php?user=<?= $_SESSION['user']; ?>">Profile</a></li>
			<?php if (hasPerms($conn, "members", 2)): ?>
			<li><a href="/home/team">Team</a></li>
			<?php endif ?>
			<li><a href="/home/sessions.php">Sessions</a></li>
			<li><a href="/home/payments.php">Payments</a></li>
			<li><a href="/src/logout.php">Logout</a></li>
		</ul>
	</body>
</html>
