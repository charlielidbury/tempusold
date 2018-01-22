<?php
session_start();
include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure only logged in users past this point
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['HTTP_HOST']}");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Members</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3><a href="/home/payments/">Payments</a></h3>

		<h4>Create Payment To:</h4>
		<ul><?php
			foreach (getColumn($conn, "view_owed", "name") as $employee)
				printf("<li><a href='create_payment.php?user=%s&redirect=index.php'>%s</a></li>", $employee, ucwords($employee));
		?></ul>

		<?php table2HTML($conn, "SELECT * FROM `view_payment`"); ?>
	</body>
</html>
