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
	if (isset($_POST['auto'])) {
		// AUTOMATIC INVITATION
		// populates the search
		$queries = [
			"joined" => "INSERT INTO search (session, priority, employee)
				SELECT
					?,
					@n := @n +1 AS n,
					employee
				FROM shift, (SELECT @n := 0) m
				GROUP BY employee
				ORDER BY MIN(date) {$_POST['direction']}",
			"hours" => "INSERT INTO search (session, priority, employee)
				SELECT
					?,
					@n := @n +1 AS n,
					employee
				FROM shift, (SELECT @n := 0) m
				GROUP BY employee
				ORDER BY SUM(TIME_TO_SEC(shift.length)) {$_POST['direction']}",
			"rate" => "INSERT INTO search (session, priority, employee)
				SELECT
					?,
					@n := @n +1 AS n,
					name
				FROM employee, (SELECT @n := 0) m
				ORDER BY rate {$_POST['direction']}"
		];

		q($conn, "UPDATE session SET
			search_timeout = SEC_TO_TIME(? * 3600),
			search_size = ?
		WHERE date = ?", ['args'=>[
			$_POST['timeout'],
			$_POST['size'],
			$_GET['session']
		]]);

		q($conn, $queries[$_POST['sortby']], ['args'=>$_GET['session']]);

	} else {
		// MANUAL INVITATION
		unset($_POST['size']);
		unset($_POST['sortby']);
		unset($_POST['direction']);
		// Records whether or not to mark as accepted
		$accept = $_POST['submit'] == "Mark as Accepted";
		unset($_POST['submit']);

		// Records invite message
		$invite_message = $_POST['invite_message'];
		unset($_POST['invite_message']);

		// Remaining data is just the invites left
		$invites = array_values($_POST);

		// invites the people
		$disco_cmds = []; // list of commands to be sent to the bot
		if ($accept) {
			foreach ($invites as $employee)
				insertRow($conn, "invite", [
					"session" => $_GET['session'],
					"employee" => $employee,
					"accepted" => 1
				]);
				// invites them on discord
				$disco_cmds[] = ["addUser", $_GET['session'], $employee];
		} else {
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

		<style>
			input[name=auto]:checked ~ #manual{	display: none;	}
			#manual { display: block;	}
			input[name=auto]:checked ~ #auto{	display: block; }
			#auto{ display: none;	}
		</style>

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

				<!-- INVITES
				<label><h3>Automatic Invites: </h3></label>
				<input type="checkbox" name="auto" checked>
 -->
				<div id="manual">
					<?php
					foreach(q($conn, "SELECT name FROM employee") as $employee)
						printf('<input type="checkbox" name="%1$s" value="%1$s">%1$s<br>', $employee);
					?>
				</div>
				<div id="auto">
					<!-- Size of search -->
					<label>People to Invite: </label>
					<input type="number" min="1" max="6" step="1" value="1" name="size">
					<br>
					<!-- Search criteria -->
					<label>Sort by</label>
					<select name="sortby">
						<option value="joined">Date Joined</option>
						<option value="hours">Hours Worked</option>
						<option value="rate">Hourly Rate</option>
					</select>
					<select name="direction">
						<option value="asc">Lowest First</option>y
						<option value="desc">Highest First</option>
					</select>
					<br>
					<!-- Timeout -->
					<label>Hours Given to Respond: </label>
					<input type="number" name="timeout" value="24">
				</div>


				<!-- MESSAGE -->
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
