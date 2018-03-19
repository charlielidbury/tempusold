<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit session's details
$user = $_GET['user'];
if (!hasPerms($conn, "sessions", 2))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

$query = "SELECT *, HOUR(`length`) as hours, MINUTE(`length`) as minutes  FROM `shift` WHERE `date` = ?";
$session_data = getRow($conn, "session", ["date" => $_GET['session']]);
$shift_data = getTable($conn, $query, "s", $_GET['session']);
$session_employees = array_column($shift_data, "employee");

// ----- SAFE AREA -----
if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	if ($_POST['submit'] == "Update Details") // UPDATE DETAILS
	{
		// update session info
		updateRow($conn, "session", ["date" => $_GET['session']], [
			"start" => $_POST['start'],
			"end" => $_POST['end']
		]);

		// refreshes session data
		$session_data = getRow($conn, "session", ["date" => $_GET['session']]);
	}
	elseif ($_POST['submit'] == "Update Shifts") // UPDATE SHIFTS
	{
		// update shift info
		foreach ($session_employees as $employee)
			if ($_POST[$employee . "remove"] == "on")
				// removes user from session
				deleteRow($conn, "shift", [
					"employee" => $employee,
					"date" => $_GET['session']
				]);
			else {
				updateRow($conn, "shift", [
					"employee" => $employee,
					"date" => $_GET['session']
				], [
					"length" => $_POST[$employee . "hours"]
				]);
			}

		// refreshes shift data
		$shift_data = getTable($conn, $query, "s", $_GET['session']);
	}
	elseif ($_POST['submit'] == "Add Workers") // ADD WORKERS
	{
		unset($_POST['submit']);
		foreach ($_POST as $employee)
			insertRow($conn, "shift", [
				"date" => $_GET['session'],
				"employee" => $employee,
				"rate" => getCell($conn, "rate", "employee", "name", $employee)
			]);

		// updates shift data
		$shift_data = getTable($conn, $query, "s", $_GET['session']);
	}
	elseif ($_POST['submit'] == "Invite Workers")
	{ // invites the poeple
		$disco_cmds = [];
		unset($_POST['submit']);
		foreach ($_POST as $employee)
		{
			// puts the invites into the db
			insertRow($conn, "invite", [
				"session" => $session_data['date'],
				"employee" => $employee
			]);

			// invites them on discord
			$disco_cmds[] = ["sendInvite", $session_data['date'], $employee];
		}
		discoBot(...$disco_cmds);
	}
	elseif (isset($_POST['employee']))
		deleteRow($conn, "invite", [
			"session" => $session_data['date'],
			"employee" => $_POST['employee']
		]);
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

		<title>Tempus - Edit Session</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<ul> <?php
				foreach ($errors as $error) printf("<li>%s</li>\n", $error);
			?>	</ul>

			<!-- CHANGE SESSION DETAILS -->
			<!-- ACTIONS -->
			<h3>Actions</h3>
			<ul>
				<li><a href="<?= "delete_session.php?session={$_GET['session']}&redirect={$_GET['redirect']}" ?>">Delete Session</a></li>
				<li><a href="<?= "finish_session.php?session={$_GET['session']}&redirect={$_GET['redirect']}" ?>">Finish Session</a></li>
				<li><a href="<?= "invite_people.php?session={$_GET['session']}&redirect={$_GET['redirect']}" ?>">Invite People</a></li>
			</ul>
			<h3>Details</h3>
			<form action="<?= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" method="POST">
				<table>
					<tr>
						<th>Aspect</th>
						<th>Value</th>
					</tr>
					<tr>
						<td>Date</td>
						<td><?= $session_data['date']; ?></td>
					</tr>
					<tr>
						<td>Organiser</td>
						<td><?= $session_data['organiser']; ?></td>
					</tr>
					<tr>
						<td>Start</td>
						<td><input type="time" name="start" value="<?= $session_data['start']; ?>"></td>
					</tr>
					<tr>
						<td>End</td>
						<td><input type="time" name="end" value="<?= $session_data['end']; ?>"></td>
					</tr>
				</table>
				<input type="submit" value="Update Details" name="submit" />
			</form>
			<!-- CHANGE SESSION WORKERS -->
			<h3>Workers</h3>
			<!-- Current shifts -->
			<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
				<table>
					<tr>
						<th>Employee</th>
						<th>Hours Worked</th>
						<th>Remove</th>
					</tr>
					<?php foreach($shift_data as $shift): ?>
						<tr>
							<td><?= $shift['employee']; ?></td>
							<td><input type="time" name="<?= $shift['employee'] ?>hours" value="<?= $shift['length']; ?>"></td>
							<td><input type="checkbox" name="<?= $shift['employee'] ?>remove"></td>
						</tr>
					<?php endforeach ?>
				</table>
				<input type="submit" value="Update Shifts" name="submit" />
			</form>
			<!-- Add shifts -->
			<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
				<?php
				foreach(getColumn($conn, "employee", "name") as $employee)
					if (!in_array($employee, $session_employees))
						printf('<input type="checkbox" name="%1$s" value="%1$s">%1$s<br>', $employee);
				?>
				<input type="submit" value="Add Workers" name="submit">
			</form>
			<!-- INVITES -->
			<h3>Invites</h3>
			<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
				<?php table2HTML($conn, "CALL sessionInvites(?)", $session_data['date']); ?>
			</form>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
