<<<<<<< HEAD
<?php
session_start();

// makes sure only logged in users get here
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['DOCUMENT_ROOT']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit session's details
$user = $_GET['user'];
if (!hasPerms("sessions", 2))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

$session_data = getRow("session", "date", $_GET['session']);
$shift_data = getTable("SELECT * FROM `shift` WHERE `date` = ?", "s", $_GET['session']);
$session_employees = array_column($shift_data, "employee");

// ----- SAFE AREA -----
if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	if ($_POST['submit'] == "Update") // UPDATE DETAILS
	{
		// update session info
		updateRow("session", ["date" => $_GET['session']], [
			"date" => $_POST['date'],
			"start" => $_POST['start'],
			"end" => $_POST['end']
		]);

		// update shift info
		foreach ($session_employees as $employee)
			if ($_POST[$employee . "remove"] == "on")
				// removes user from session
				deleteRow("shift", [
					"employee" => $employee,
					"date" => $_GET['session']
				]);
			else
				// updates user info
				updateRow("shift", [
					"employee" => $employee,
					"date" => $_GET['session']
				], [
					"length" => $_POST[$employee . "hours"],
					"rate" => $_POST[$employee . "rate"]
				]);

		// refreshes session data
		$session_data = getRow("session", "date", $_GET['session']);
		$shift_data = getTable("SELECT * FROM `shift` WHERE `date` = ?", "s", $_GET['session']);

	} elseif ($_POST['submit'] == "Add") // ADD WORKERS
	{
		unset($_POST['submit']);
		foreach ($_POST as $employee)
			insertRow("shift", ["date" => $_GET['session'], "employee" => $employee]);

		// updates shift data
		$shift_data = getTable("SELECT * FROM `shift` WHERE `date` = ?", "s", $_GET['session']);
	}
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
		<h4><a href="/home/sessions/change_details.php">Edit User</a></h4>

		<ul> <?php
			foreach ($errors as $error) printf("<li>%s</li>\n", $error);
		?>	</ul>

		<!-- Changes to existing fields -->
		<form action="<?= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" method="POST">
			<p>Edit Session Details:</p>
			<table>
				<tr>
					<th>Aspect</th>
					<th>Value</th>
				</tr>
				<tr>
					<td>Date</td>
					<td><input type="date" name="date" value=<?= $_GET['session']; ?>></td>
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
			<p>Edit Shifts:</p>
			<table>
				<tr>
					<th>Employee</th>
					<th>Hours Worked</th>
					<th>Hourly Rate</th>
					<th>Remove</th>
				</tr>
				<?php foreach($shift_data as $shift): ?>
				<tr>
					<td><?= $shift['employee']; ?></td>
					<td><input type="time" name="<?= $shift['employee'] ?>hours" value="<?= $shift['length']; ?>"></td>
					<td><input type="number" name="<?= $shift['employee'] ?>rate" step="0.01" value="<?= $shift['rate']; ?>"></td>
					<td><input type="checkbox" name="<?= $shift['employee'] ?>remove"></td>
				</tr>
				<?php endforeach ?>
			</table>
			<input type="submit" value="Update" name="submit" />
		</form>
		<!-- Add extra workers -->
		<p>Add Extra Workers:</p>
		<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
			<?php
			foreach(getColumn("employee", "name") as $employee)
				if (!in_array($employee, $session_employees))
					printf('<input type="checkbox" name="%1$s" value="%1$s">%1$s<br>', $employee);
			?>
			<input type="submit" value="Add" name="submit">
		</form>
		<!-- Delete button -->
		<a href="<?= "delete_session.php?session={$_GET['session']}&redirect={$_GET['redirect']}" ?>">
			Delete Session
		</a>
	</body>
</html>
=======
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!
