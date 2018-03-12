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
			"date" => $_POST['date'],
			"start" => $_POST['start'],
			"end" => $_POST['end']
		]);

		// refreshes session data
		$session_data = getRow($conn, "session", ["date" => $_POST['date']]);
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
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Edit Session</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/sessions">Sessions</a></h3>
		<h4><a href="/home/sessions/edit_session.php">Edit Session</a></h4>

		<ul> <?php
			foreach ($errors as $error) printf("<li>%s</li>\n", $error);
		?>	</ul>

		<!-- CHANGE SESSION DETAILS -->
		<!-- ACTIONS -->
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
	</body>
</html>
