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
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Create Session</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

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
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
