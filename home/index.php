<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

$row = getRow($conn, "role", [
	"role" => getCell($conn, "role", "employee", "name", $_SESSION['user'])
]);
unset($row['role']);

$perms = [];
foreach ($row as $perm => $level)
	if ($level >= 1)
		$perms[] = [
			"team" => "<a href='/home/team'>Team</a>",
			"sessions" => "<a href='/home/sessions'>Manage Sessions</a>",
			"payments" => "<a href='/home/payments'>Manage Payments</a>"
		][$perm];

$upcoming_query = <<<EOT
SELECT
	DATE_FORMAT(`session`.`date`, "%d/%m/%y") AS `Session`,
	CONCAT(
		"<a href='sessions/delete_session.php?session=", `session`.`date`, "&redirect=index.php'>Cancel</a>|",
		"<a href='sessions/edit_session.php?session=", `session`.`date`, "&redirect=index.php'>Edit</a>|",
		"<a href='sessions/finish_session.php?session=", `session`.`date`, "&redirect=../payments/index.php'>Finish</a>|",
		"<a href='sessions/invite_people.php?session=", `session`.`date`, "&redirect=index.php'>Invite</a>"
	) AS `Actions`
FROM `session`
	LEFT JOIN (SELECT date, COUNT(*) AS shifts FROM shift GROUP BY date) s ON s.date = session.date
	LEFT JOIN (SELECT session, COUNT(*) AS invites FROM invite GROUP BY session) i ON i.session = session.date
WHERE COALESCE(i.invites, 0) > COALESCE(s.shifts, 0)
GROUP BY
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`
ORDER BY `session`.`date` ASC
EOT;

$query = <<<EOT
SELECT
	COUNT(*)
FROM `session`
	LEFT JOIN (SELECT date, COUNT(*) AS shifts FROM shift GROUP BY date) s ON s.date = session.date
	LEFT JOIN (SELECT session, COUNT(*) AS invites FROM invite GROUP BY session) i ON i.session = session.date
WHERE COALESCE(i.invites, 0) > COALESCE(s.shifts, 0)
GROUP BY
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`
ORDER BY `session`.`date` ASC
EOT;

if (hasPerms($conn, "sessions", 2))
	$render_upcoming = q($conn, $query);
else
	$render_upcoming = false;

$leaderboard_query = <<<EOT
SELECT employee AS `Employee`,
	TIME_FORMAT(SEC_TO_TIME(AVG(TO_SECONDS(received) - TO_SECONDS(sent))), "%H:%i")
		AS `Avg. Response Time (HH:MM)`
FROM invite
WHERE TO_SECONDS(received) - TO_SECONDS(sent) > 0
GROUP BY employee
ORDER BY AVG(TO_SECONDS(received) - TO_SECONDS(sent)) ASC;
EOT;

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Home</title>
	</head>
	<body>
		<div class="container">
			<!-- HEADER -->
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<!-- INVITE LEADERBOARD -->
			<h1>Invite Leaderboard</h1>
			<?php table2HTML($conn, $leaderboard_query) ?>

			<!-- QUICK ACTIONS -->
			<?php
			if ($render_upcoming)
				{ echo "<h1>Quick Actions</h1>"; table2HTML($conn, $upcoming_query); }
			?>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
