<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include  "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// permission check
if (!hasPerms($conn, "team", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

$query = 'SELECT
	`employee`.`name`,
	COALESCE(`employee`.`rate`, 0) as `rate`,
	COALESCE(`total_shift`.`hours`, SEC_TO_TIME(0)) AS `hours`,
	COALESCE(`total_shift`.`earnt`, 0) AS `earnt`,
	COALESCE(`total_payment`.`paid`, 0) AS `paid`,
	COALESCE(`total_shift`.`earnt` - `total_payment`.`paid`, 0) AS `outstanding`,
	DATE_FORMAT(COALESCE(`total_shift`.`start`, DATE(NOW())), "%d/%m/%y") AS `join`,
	`icon`
FROM `employee`
	LEFT JOIN (SELECT `employee`, ROUND(SUM( TIME_TO_SEC(`length`)*`rate`/3600 ), 2) as `earnt`, SEC_TO_TIME(SUM(TIME_TO_SEC(`length`))) AS `hours`, MIN(`date`) AS `start` FROM `shift` GROUP BY `employee`) `total_shift`
		ON `total_shift`.`employee` = `employee`.`name`
	LEFT JOIN (SELECT `payee`, SUM(`amount`) AS `paid` FROM `payment` GROUP BY `payee`) `total_payment`
		ON `total_payment`.`payee` = `employee`.`name`
GROUP BY `employee`.`name`';

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Team</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/team">Team</a></h3>

		<ul>
			<li><a href="create_user.php?redirect=index.php">Create new user</a></li>
		</ul>

		<table>
			<tr>
				<th>Name</th>
				<th>Hourly Rate</th>
				<th>Total Hours</th>
				<th>Total Earnt</th>
				<th>Total Paid</th>
				<th>Outstanding</th>
				<th>Join Date</th>
				<th>Icon</th>
				<th>Actions</th>
			</tr>

			<?php foreach(q($conn, $query) as $employee): ?>
				<tr>
					<td><?= $employee['name'] ?></td>
					<td>£<?= $employee['rate'] ?></td>
					<td><?= $employee['hours'] ?></td>
					<td>£<?= $employee['earnt'] ?></td>
					<td>£<?= $employee['paid'] ?></td>
					<td>£<?= $employee['outstanding'] ?></td>
					<td><?= $employee['join'] ?></td>
					<td><img src="<?=$employee['icon']?>" alt="<?=$employee['name']?>'s icon'" width="128" height="128"></td>
					<td>
						<a href="view_user.php?user=<?= $employee['name'] ?>&redirect=index.php">View</a>
						<br>
						<a href="edit_user.php?user=<?= $employee['name'] ?>&redirect=index.php">Edit</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</body>
</html>
