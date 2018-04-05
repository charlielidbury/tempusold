<?php

session_start();

include  "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

if (!q($conn, "SELECT date FROM session WHERE date = CURRENT_DATE()"))
	die("There is no session on today.");

if (!isset($_SESSION['user']))
	// nothing assumed; password and username entered for every clock on / off
	$mode = "kiosk";
elseif (hasPerms($conn, "sessions", 2))
	// user has sessions::edit, can toggle everything without password
	$mode = "admin";
else
	// user can see everything but can only toggle own time
	$mode = "user";

if ($mode == "user")
	$mode = "kiosk";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	if ($mode == "admin") {
		// Admin actions
		if (isset($_POST['employee']))
			// Toggles a specific user
			q($conn, "CALL toggleClock(?)", ['args'=>$_POST['employee']]);
		elseif ($_POST['submit'] == "All On")
			// Turns all on
			q($conn, "INSERT INTO clock (session, employee, clock_on) SELECT CURRENT_DATE(), name, CURRENT_TIME() FROM employee LEFT JOIN clock ON clock.session = CURRENT_DATE() AND clock.employee = employee.name AND clock_off IS NULL WHERE clock.session IS NULL");
		elseif ($_POST['submit'] == "All Off")
			// Turns all off
			q($conn, "UPDATE clock SET clock_off = CURRENT_TIME() WHERE session = CURRENT_DATE() AND clock_off IS NULL");
	} elseif ($mode == "kiosk" && $_POST['username'] != "") {
		$errors = array();

		// CHECK: USER EXISTS
		if (!in_array($_POST['username'], getColumn($conn, "employee", "name"), true))
			$errors[] = "User {$_POST['username']} does not exist.";

		$hash = q($conn, "SELECT hash FROM employee WHERE name = ?", ['args'=>$_POST['username']]);

		// CHECK: PASSWORD IS CORRECT
		if (!password_verify($_POST["password"], $hash))
			$errors[] = "Incorrect password";

		// ACTUAL LOGIN
		if (!count($errors))
			q($conn, "CALL toggleClock(?)", ['args'=>$_POST['username']]);
	}
	header("Location: http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
}

$clocking_query = <<<EOT
SELECT
	invite.employee,
	COALESCE(clock.length, SEC_TO_TIME(0)) AS duration,
	IF(clocked_on.employee IS NOT NULL, "On", "Off") AS status
FROM invite
	LEFT JOIN shift ON shift.employee = invite.employee AND shift.date = invite.session
	LEFT JOIN session ON session.date = invite.session
	LEFT JOIN (SELECT employee, SEC_TO_TIME(SUM(TIME_TO_SEC(COALESCE(clock_off, CURRENT_TIME())) - TIME_TO_SEC(clock_on))) AS length FROM clock WHERE session = CURRENT_DATE() GROUP BY employee) clock
		ON clock.employee = invite.employee
	LEFT JOIN (SELECT employee FROM clock WHERE session = CURRENT_DATE() AND clock_off IS NULL GROUP BY employee) `clocked_on`
		ON clocked_on.employee = invite.employee
WHERE session = CURRENT_DATE()
	AND accepted = 1
	AND shift.employee IS NULL
EOT;

$clocking_data = q($conn, $clocking_query, ['force'=>"TABLE"]);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Clock</title>
		<script src="/node_modules/moment/moment.js"></script>
		<script type="text/javascript">
			var times = [];

			function incrementTimes() {
				times.map(time => { time.time.add(1, "second") });
				times.forEach(time => { document.getElementById(time.employee).innerHTML = time.time.format("HH:mm:ss") });
			}

			// REFRESHING THE TIMES
			window.setInterval(incrementTimes, 1000);

			// REFRESHING THE WHOLE SCREEN
			<?php if (isset($_GET['rr'])): // (refresh rate) ?>
				window.setInterval("location.reload(true)", <?= $_GET['rr'] ?>);
			<?php else: ?>
				window.setInterval("location.reload(true)", 30000);
			<?php endif; ?>
		</script>
	</head>
	<body>
		<?php if ($mode != "kiosk") include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>
		<div class="container">
			<form action="<?= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" ?>" method="post">
				<!-- TABLE -->
				<input type="submit" value="Refresh">
				<table>
					<tr>
						<th>Employee</th>
						<th>Current Time</th>
						<th>Status</th>
					</tr>
					<?php foreach ($clocking_data as $clock): ?>
						<?php if ($clock['status'] == "On"): ?>
							<script type="text/javascript">
								times.push({employee:"<?= $clock['employee'] ?>", time:moment("<?= $clock['duration'] ?>", "HH:mm:ss")});
							</script>
						<?php endif; ?>
						<tr>
							<td><?= $clock['employee'] ?></td>
							<td id="<?= $clock['employee'] ?>"><?= $clock['duration'] ?></td>
							<?php if ($mode == "admin"): ?>
								<td><button type="submit" name="employee" value="<?= $clock['employee'] ?>"><?= $clock['status'] ?></button></td>
							<?php else: ?>
								<td><?= $clock['status']; ?></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</table>
				<!-- ACTION BOX -->
				<?php if ($mode == "kiosk"): ?>
					<ul> <?php
						foreach ($errors as $error) echo "<li>$error</li>";
					?> </ul>
					<table>
						<tr>
							<td>Username:</td>
							<td><input type="text" name="username"> <br></td>
						</tr>
						<tr>
							<td>Password:</td>
							<td><input type="password" name="password"><br></td>
						</tr>
					</table>
					<input type="submit" value="Toggle Clock" name="submit">
				<?php endif; ?>
				<?php if ($mode == "admin"): ?>
					<input type="submit" name="submit" value="All On">
					<input type="submit" name="submit" value="All Off">
				<?php endif; ?>
			</form>


		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
