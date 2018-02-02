<?php
session_start();

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

// makes sure only people with correct perms can see the details
if (!hasPerms($conn, "sessions", 1))
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
		<h3>Details</h3>
		<?php
		// SESSION DETAILS
		// 2018-01-16 => 16/01/18
		$date = sprintf("%s/%s/%s",
			substr($_GET['session'], 8, 2), // day
			substr($_GET['session'], 5, 2), // month
			substr($_GET['session'], 2, 2) // year
		);
		echo row2HTML($conn, "view_session", "Date", $date);
		?>
	 	<!-- CONTAINED SHIFT DETAILS -->
		<h3>Employees</h3>
		<?= table2HTML($conn, "CALL sessionShifts(?)", "s", $_GET['session']); ?>
		<!-- INVITE DETAILS -->
		<h3>Invites</h3>
		<?= table2HTML($conn, "CALL sessionInvites(?)", "s", $_GET['session']); ?>
	</body>
</html>
