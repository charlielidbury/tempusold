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
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Team</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<h1>Actions</h1>
			<ul>
				<li><a href="create_user.php?redirect=index.php">Create New User</a></li>
				<li><a href="manage_roles.php?redirect=index.php">Manage Roles</a></li>
			</ul>

			<h1>Team Members</h1>
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
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
