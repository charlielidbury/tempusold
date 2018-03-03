<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms($conn, "sessions", 2))
	header("Location: {$_SERVER['DOCUMENT_ROOT']}/permission_denied.php");

// ----- SAFE AREA -----
if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	// INSERT ROW
	$employees = q($conn, "SELECT name FROM employee");
	$row = [];
	$disco_cmds = []; // list of commands to be sent to the bot

	// sorts $_POST into $invites (invite details) and $row (session details)
	$row = $_POST;
	unset($row['submit']);

	// Adds organiser & inserts the session
	$row['organiser'] = $_SESSION['user'];
	insertRow($conn, "session", $row);
	discoBot("createChannel", $row['date']); // makes the disco channel

	$extra = "";
	if (!(!isset($_GET) && sizeof($_GET) > 0))
		$extra = "&".http_build_query($_GET);

	// Redirects
	if ($_POST['submit'] == "Create & Invite")
		header("Location: invite_people.php?session=$_POST[date]$extra");
	elseif (isset($_GET['redirect']))
		header("Location: {$_GET['redirect']}");
	else
		header("Location: {$_SERVER['HTTP_HOST']}");
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Create Session</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/sessions">Session</a></h3>
		<h3><a href="/home/sessions/create_session.php">Create Session</a></h3>

		<ul> <?php
			foreach ($errors as $error) printf("<li>%s</li>\n", $error);
		?>	</ul>

		<p id="required">Required Fields</p>

		<form action="<?= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" method="POST">
			<table>
				<tr>
					<td id="required">Date</td>
					<td><input type="date" name="date"></td>
				</tr>
				<tr>
					<td id="required">Start</td>
					<td><input type="time" name="start"></td>
				</tr>
				<tr>
					<td id="required">End</td>
					<td><input type="time" name="end"></td>
				</tr>
			</table>

			<input type="submit" value="Create & Invite" name="submit" />
			<br>
			<input type="submit" value="Create Session" name="submit" />
		</form>
	</body>
</html>
