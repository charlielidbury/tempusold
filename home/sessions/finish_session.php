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
	// Inserts the rows
	foreach ($_POST as $employee => $length)
		if ($length !== "on")
			insertRow($conn, "shift", [
				"date" => $_GET['session'],
				"employee" => $employee,
				"length" => $length,
				"rate" => q($conn, "SELECT rate FROM employee WHERE name = ?", ['args'=>$employee])
			]);

	// Updates the discord
	discoBot("deleteChannel", $_GET['session']);

	// redirect back
	if (isset($_GET['redirect']))
		header("Location: {$_GET['redirect']}");
}

$employees_query = <<<EOT
SELECT
	invite.employee,
	COALESCE(clock.length, SEC_TO_TIME(TIME_TO_SEC(`end`) - TIME_TO_SEC(`start`))) AS duration
FROM invite
	LEFT JOIN shift ON shift.employee = invite.employee AND shift.date = invite.session
	LEFT JOIN session ON session.date = invite.session
	LEFT JOIN (SELECT employee, SEC_TO_TIME(SUM(TIME_TO_SEC(COALESCE(clock_off, CURRENT_TIME())) - TIME_TO_SEC(clock_on))) AS length FROM clock WHERE session = CURRENT_DATE() GROUP BY employee) clock
		ON clock.employee = invite.employee
WHERE session = ?
	AND accepted = 1
	AND shift.employee IS NULL
EOT;

$employees = q($conn, $employees_query,
	['args'=>$_GET['session'], 'force'=>'TABLE']);

$duration = q($conn, "SELECT SEC_TO_TIME(TIME_TO_SEC(`end`)-TIME_TO_SEC(`start`)) FROM session WHERE `date` = ?",
	['args'=>$_GET['session']]);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Finish Session</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
				<table>
					<tr>
						<th>Employee</th>
						<th>Hours Worked</th>
						<th>Exclude</th>
					</tr>
					<?php foreach($employees as $employee): ?>
						<tr>
							<td><?= $employee['employee'] ?></td>
							<td><input type="time" name="<?= $employee['employee'] ?>" value="<?= $employee['duration'] ?>"></td>
							<!-- When checked this overrides the time and stop the time from being added -->
							<td><input type="checkbox" name="<?= $employee['employee'] ?>"></td>
						</tr>
					<?php endforeach ?>
				</table>
				<input type="submit" value="Finish"/>
			</form>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
