<?php
session_start();
include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

// makes sure only users with view session perms past this point
if (!hasPerms($conn, "sessions", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

$query = <<<EOT
SELECT
	DATE_FORMAT(`session`.`date`, "%d/%m/%y") AS `Date`,
	CONCAT("<a href='../team/view_user.php?user=", `session`.`organiser`, "'>", `session`.`organiser`, "</a>") AS `Organiser`,
	TIME_FORMAT(`session`.`start`, "%H:%i") AS `Start`,
	TIME_FORMAT(`session`.`end`, "%H:%i") AS `End`,
	GROUP_CONCAT(CONCAT("<a href='../team/view_user.php?user=", `shift`.`employee`, "'>", `shift`.`employee`, "</a>")) AS `Employees`,
	CONCAT("<a href='edit_session.php?session=", `session`.`date`, "&redirect=index.php'>Edit</a>") AS `Actions`
FROM `session`
	LEFT JOIN `shift` ON `shift`.`date` = `session`.`date`
GROUP BY
	`session`.`date`,
	`session`.`organiser`,
	`session`.`start`,
	`session`.`end`
ORDER BY `session`.`date` DESC
EOT;

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

		<ul>
			<li><a href="create_session.php?redirect=index.php">Create Session</a></li>
		</ul>

		<?php table2HTML($conn, $query); ?>
	</body>
</html>
