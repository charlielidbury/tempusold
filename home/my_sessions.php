<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") die(var_dump($_POST));

if (isset($_GET['session']))
{
	// responds to invite
	if (isset($_GET['response'])) {
		// Invite replied to
		q($conn, "UPDATE `invite` SET `received` = NOW(), `accepted` = ? WHERE `session` = ? AND `employee` = ?",
			[ 'args' => [$_GET['response'], $_GET['session'], $_SESSION['user']] ]);
		discoBot("addUser", $_GET['session'], $_SESSION['user']);
	} else
		// Invite unreplied to
		q($conn, "UPDATE `invite` SET `received` = NULL, `accepted` = NULL WHERE `session` = ? AND `employee` = ?",
			[ 'args' => [$_GET['session'], $_SESSION['user']] ]);

	header("Location: http://{$_SERVER['HTTP_HOST']}/home/my_sessions.php");
}

$upcoming_query = "SELECT
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`,
	(SELECT GROUP_CONCAT(`i`.`employee`) FROM `invite` `i`
		WHERE `i`.`session` = `session`.`date`
		  AND `i`.`employee` != `invite`.`employee`
		  AND `i`.`accepted` = 1
	) AS `others`,
	`invite`.`accepted`
FROM `invite`
	JOIN `session` ON `session`.`date` = `invite`.`session`
	LEFT JOIN `shift` ON `shift`.`date` = `invite`.`session`
WHERE `invite`.`employee` = ?
	AND `shift`.`date` IS NULL";

$upcoming_data = q($conn, $upcoming_query, ['args' => $_SESSION['user'], 'force' => 'TABLE']);

$archive_query = "SELECT
	`shift`.`date` AS `Date`,
	`session`.`organiser` AS `Organiser`,
	`session`.`start` AS `Start`,
	`session`.`end` AS `End`,
	`shift_members`.`members` AS `Other Workers`
FROM `shift`
	LEFT JOIN `session` ON `session`.`date` = `shift`.`date`
	LEFT JOIN (SELECT `date`, GROUP_CONCAT(`employee`) AS `members` FROM `shift` `s` WHERE `s`.`employee` != ? GROUP BY `date`) `shift_members`
		ON `shift_members`.`date` = `shift`.`date`
WHERE `shift`.`employee` = ?
ORDER BY `shift`.`date` DESC";

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

			<!-- UPCOMING SESSIONS -->
			<?php if (sizeof($upcoming_data)): ?>
				<h1>Upcoming Sessions</h1>
				<table>
					<tr>
						<th>Date</th>
						<th>Organiser</th>
						<th>Start</th>
						<th>End</th>
						<th>Confirmed Colleagues</th>
						<th>Invite</th>
					</tr>

					<?php foreach($upcoming_data as $session): ?>

						<tr>
							<td><?= $session['date'] ?></td>
							<td><?= $session['organiser'] ?></td>
							<td><?= $session['start'] ?></td>
							<td><?= $session['end'] ?></td>
							<td><?= $session['others'] ?></td>
							<td>
								<?php if (gettype($session['accepted']) == "integer") { ?>
									<a href="<?= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?session={$session['date']}" ?>">
										<?php if ($session['accepted']) echo "Unaccept"; else echo "Undecline"; ?>
									</a>
								<?php } else { ?>
									<a href="<?= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?session={$session['date']}&response=1" ?>">Accept</a>
									<a href="<?= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?session={$session['date']}&response=0" ?>">Decline</a>
								<?php } ?>
							</td>
						</tr>

					<?php endforeach; ?>
				</table>
			<?php endif; ?>

			<!-- SESSION ARCHIVE -->
			<h1>Session Archive</h1>
			<?php table2HTML($conn, $archive_query, $_SESSION['user'], $_SESSION['user']) ?>

		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
