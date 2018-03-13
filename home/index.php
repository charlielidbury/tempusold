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
GROUP BY employee;
EOT;

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Home</title>
		<link rel="stylesheet" href="../css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>

		<!-- INVITE LEADERBOARD -->
		<h1>Invite Leaderboard</h1>
		<?php table2HTML($conn, $leaderboard_query) ?>

		<!-- USER ACTIONS -->
		<h1>Logged in as <?= $_SESSION["user"]; ?>:</h1>
		<ul>
			<li><a href="/home/team/view_user.php?user=<?= $_SESSION['user']; ?>">Profile</a></li>
			<li>
				<a href="/home/my_sessions.php">
					Personal Sessions
					(<?= q($conn, "SELECT SUM(1) FROM invite WHERE accepted IS NULL AND employee = ?", [ 'args'=>[$_SESSION['user']] ]) ?>)
				</a>
			</li>
			<li><a href="/home/my_payments.php">Personal Payments</a></li>
			<li><a href="/src/logout.php">Logout</a></li>
		</ul>

		<!-- QUICK ACTIONS -->
		<?php
		if ($render_upcoming)
			{ echo "<h1>Quick Actions</h1>"; table2HTML($conn, $upcoming_query); }
		?>

		<!-- ADMIN ACTIONS -->
		<?php if (count($perms) > 0): ?>
			<h1>Admin Actions:</h1>

			<ul><?php
				foreach ($perms as $perm)
					printf("<li>%s</li>", $perm);
			?></ul>
		<?php endif ?>
	</body>
</html>
