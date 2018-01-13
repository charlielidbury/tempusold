<?php
session_start();
if (!isset($_SESSION['user_data'])) header("Location: ..");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Home</title>
		<link rel="stylesheet" href="../css/style.css">
	</head>
	<body>
		<h1><a href="index.php">Tempus</a></h1>
		<h2><a href=".">Home</a></h2>
		<h3>Logged in as <?= $_SESSION["user_data"]["name"]; ?></h3>
		<ul>
			<li><a href="profile.php">Profile</a></li>
			<?php if ($_SESSION["user_data"]["perms"]["members"] == "edit"): ?>
			<li><a href="members.php">Members</a></li>
			<?php endif ?>
			<li><a href="sessions.php">Sessions</a></li>
			<li><a href="payments.php">Payments</a></li>
			<li><a href="../src/logout.php">Logout</a></li>
		</ul>
	</body>
</html>
