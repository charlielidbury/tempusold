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
	</body>
</html>
