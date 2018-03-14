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
	// Records whether or not to mark as accepted
	$accept = $_POST['submit'] == "Mark as Accepted";
	unset($_POST['submit']);
	// Records invite message
	$invite_message = $_POST['invite_message'];
	unset($_POST['invite_message']);
	// Remaining data is just the invites left
	$invites = array_values($_POST);

	// invites the people
	if ($accept)
		foreach ($invites as $employee)
			insertRow($conn, "invite", [
				"session" => $_GET['session'],
				"employee" => $employee,
				"accepted" => 1
			]);
	else {
		$disco_cmds = []; // list of commands to be sent to the bot
		foreach ($invites as $employee)
		{
			// puts the invites into the db
			insertRow($conn, "invite", [
				"session" => $_GET['session'],
				"employee" => $employee
			]);
			// invites them on discord
			$disco_cmds[] = ["sendInvite", $_GET['session'], $employee, $invite_message];
		}
		discoBot(...$disco_cmds); // sends off disco commands
	}

	// Redirects
	if (isset($_GET['redirect']))
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

				<h3>Invite People</h3>
				<?php
				foreach(q($conn, "SELECT name FROM employee") as $employee)
					printf('<input type="checkbox" name="%1$s" value="%1$s">%1$s<br>', $employee);
				?>

				<h3>Invite message</h3>
				<textarea name="invite_message" cols="50" rows="10">You have been invited into session: <?= $_GET['session'] ?>. Reply with '!invite accept' to accept the invite or '!invite decline' to decline. Go to http://tempus.microbarbox.com/home/my_sessions.php to see all invites.</textarea>

				<br>

				<input type="submit" value="Invite People" name="submit" />
				<br>
				<input type="submit" value="Mark as Accepted" name="submit" />
			</form>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
