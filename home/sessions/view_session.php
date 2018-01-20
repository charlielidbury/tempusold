<?php
session_start();

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure only logged on users past this point
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}");

// makes sure only people with correct perms can see the details
if (!hasPerms("sessions", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - View Session</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<pre><?php //die(var_export($_SESSION['user_data'], true)) ?></pre>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/team">Team</a></h3>
		<h3><a href="/home/team/view_session.php?session=<?= $_GET['session']; ?>">View User</a></h3>
<<<<<<< HEAD
		<p>Session details:</p>
		<?= row2HTML("view_session", "Date", sprintf("%s/%s/%s",
=======
		<p>Profile details:</p>
		<?php
		// SESSION DETAILS
		// 2018-01-16 => 16/01/18
		$date = sprintf("%s/%s/%s",
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!
			substr($_GET['session'], 8, 2), // day
			substr($_GET['session'], 5, 2), // month
			substr($_GET['session'], 2, 2) // year
		);
		echo row2HTML($conn, "view_session", "Date", $date);

<<<<<<< HEAD
	 	<!-- CONTAINED SHIFT DETAILS -->
		<p>Employees in Session:</p>
		<?= table2HTML("CALL sessionShifts(?)", "s", $_GET['session']); ?>
=======
		// CONTAINED SHIFT DETAILS
		echo table2HTML($conn, "CALL sessionShifts(?)", "s", $_GET['session']);

		?>
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!
	</body>
</html>
