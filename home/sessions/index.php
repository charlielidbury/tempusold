<?php
session_start();
include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

// makes sure only users with view session perms past this point
if (!hasPerms($conn, "sessions", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

$upcoming_query = <<<EOT
SELECT
	DATE_FORMAT(`session`.`date`, "%d/%m/%y") AS `Date`,
	CONCAT("<a href='../team/view_user.php?user=", `session`.`organiser`, "'>", `session`.`organiser`, "</a>") AS `Organiser`,
	TIME_FORMAT(`session`.`start`, "%H:%i") AS `Start`,
	TIME_FORMAT(`session`.`end`, "%H:%i") AS `End`,
	(SELECT GROUP_CONCAT(`ii`.`employee`) FROM `invite` `ii`
		WHERE `ii`.`session` = `session`.`date`
		  AND `ii`.`accepted` = 1
	) AS `Confirmed`,
	CONCAT(
		"<a href='delete_session.php?session=", `session`.`date`, "&redirect=index.php'>Cancel</a>|",
		"<a href='edit_session.php?session=", `session`.`date`, "&redirect=index.php'>Edit</a>|",
		"<a href='finish_session.php?session=", `session`.`date`, "&redirect=../payments/index.php'>Finish</a>|",
		"<a href='invite_people.php?session=", `session`.`date`, "&redirect=index.php'>Invite</a>"
	) AS `Actions`
FROM `session`
	LEFT JOIN (SELECT date, COUNT(*) AS shifts FROM shift GROUP BY date) s ON s.date = session.date
	LEFT JOIN (SELECT session, COUNT(*) AS invites FROM invite GROUP BY session) i ON i.session = session.date
WHERE COALESCE(i.invites, 1) > COALESCE(s.shifts, 0)
GROUP BY
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`
ORDER BY `session`.`date` ASC
EOT;

$archive_query = <<<EOT
SELECT
	DATE_FORMAT(`session`.`date`, "%d/%m/%y") AS `Date`,
	CONCAT("<a href='../team/view_user.php?user=", `session`.`organiser`, "'>", `session`.`organiser`, "</a>") AS `Organiser`,
	TIME_FORMAT(`session`.`start`, "%H:%i") AS `Start`,
	TIME_FORMAT(`session`.`end`, "%H:%i") AS `End`,
	GROUP_CONCAT(CONCAT("<a href='../team/view_user.php?user=",
						`shift`.`employee`,
						"'>",
						`shift`.`employee`,
						"</a>(",
						TIME_FORMAT(`shift`.`length`, "%h:%m"),
						")"
				)) AS `Employees`,
	CONCAT(
		"<a href='edit_session.php?session=", `session`.`date`, "&redirect=index.php'>Edit</a>|"
		"<a href='delete_session.php?session=", `session`.`date`, "&redirect=index.php'>Delete</a>"
	) AS `Actions`
FROM `session`
	JOIN `shift` ON `shift`.`date` = `session`.`date`
GROUP BY
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`
ORDER BY `session`.`date` DESC
EOT;

$query = <<<EOT
SELECT
	COUNT(*)
FROM `session`
	LEFT JOIN (SELECT date, COUNT(*) AS shifts FROM shift GROUP BY date) s ON s.date = session.date
	LEFT JOIN (SELECT session, COUNT(*) AS invites FROM invite GROUP BY session) i ON i.session = session.date
WHERE COALESCE(i.invites, 1) > COALESCE(s.shifts, 0)
GROUP BY
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`
ORDER BY `session`.`date` ASC
EOT;

$render_upcoming = q($conn, $query);

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Sessions</title>
	</head>
	<body>
		<?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>
		<div class="container">

			<h1>Actions</h1>
			<ul>
				<li><a href="create_session.php?redirect=index.php">Create Session</a></li>
			</ul>

			<?php if ($render_upcoming)
				{ echo "<h1>Upcoming Sessions</h1>"; table2HTML($conn, $upcoming_query); } ?>


			<h1>Session Archive</h1>
			<?php table2HTML($conn, $archive_query); ?>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
