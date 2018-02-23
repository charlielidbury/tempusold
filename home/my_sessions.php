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
	if (isset($_GET['response']))
		q($conn, "UPDATE `invite` SET `received` = NOW(), `accepted` = ? WHERE `session` = ? AND `employee` = ?",
			[ 'args' => [$_GET['response'], $_GET['session'], $_SESSION['user']] ]);
	else
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
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Sessions</title>
		<link rel="stylesheet" href="/css/style.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home/">Home</a></h2>
		<h3><a href="/home/sessions/">Sessions</a></h3>

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
	</body>
</html>
