<?php

session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit session's details
if (!hasPerms($conn, "sessions", 2))
	header("Location: {$_SERVER['HTTP_HOST']}/permission_denied.php");

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	foreach ($_POST as $employee => $length)
		insertRow($conn, "shift", [
			"date" => $_GET['session'],
			"employee" => $employee,
			"length" => $length,
			"rate" => q($conn, "SELECT rate FROM employee WHERE name = ?", ['args'=>$employee])
		]);

	// redirect back
	if (isset($_GET['redirect']))
		header("Location: {$_GET['redirect']}");
}

$employees = q($conn, "SELECT employee FROM invite WHERE session = ? AND accepted = 1",
	['args'=>$_GET['session'], 'force'=>'COLUMN']);

$duration = q($conn, "SELECT SEC_TO_TIME(TIME_TO_SEC(`end`)-TIME_TO_SEC(`start`)) FROM session WHERE `date` = ?",
	['args'=>$_GET['session']]);

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Finish Session</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/sessions">Sessions</a></h3>
		<h4><a href="/home/sessions/edit_session.php">Edit Session</a></h4>

		<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
			<table>
				<tr>
					<th>Employee</th>
					<th>Hours Worked</th>
				</tr>
				<?php foreach($employees as $employee): ?>
					<tr>
						<td><?= $employee ?></td>
						<td><input type="time" name="<?= $employee ?>" value="<?= $duration ?>"></td>
					</tr>
				<?php endforeach ?>
			</table>
			<input type="submit" value="Finish"/>
		</form>
	</body>
</html>
