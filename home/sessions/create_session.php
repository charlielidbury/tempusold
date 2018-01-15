<?php
session_start();

// makes sure only logged in users get here
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['DOCUMENT_ROOT']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms($conn, "sessions", "edit"))
	header("Location: {$_SERVER['DOCUMENT_ROOT']}/permission_denied.php");

// ----- SAFE AREA -----

if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	$errors = [];

	// CHECK:


	// INSERT ROW
	if (!count($errors))
	{
		// basically just makes $row a copy of what was in the form minus the blank values
		$row = array();
		foreach($_POST as $field => $value)
			if ($value !== "")
				$row[$field] = $value;
		unset($row['submit']);

		// Sets the organiser to the current user
		$row['organiser'] = $_SESSION['user'];

		insertRow($conn, "session", $row);

		if (isset($_GET['redirect']))
			header("Location: {$_GET['redirect']}");
		else
			header("Location: {$_SERVER['HTTP_HOST']}");
	}
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

		<form action="create_session.php?redirect=<?= $_GET['redirect']; ?>" method="POST">
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
			<input type="submit" value="Create Session" name="submit" />
		</form>
	</body>
</html>
