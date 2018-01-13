<?php
session_start();
include "../src/db.php";

if (!isset($_SESSION['user_data'])) header("Location: ..");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Members</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="index.php">Tempus</a></h1>
		<h2><a href="home.php">Home</a></h2>
		<h3><a href="payments.php">Payments</a></h3>
		<?php query2Table($conn, "CALL userPayments(?)", "s", $_SESSION['user_data']['name']); ?>
	</body>
</html>
