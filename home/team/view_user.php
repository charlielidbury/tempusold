<?php
session_start();

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure $_GET['user'] is set
if (!isset($_GET['user']))
	die("User not set in GET variables");

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

// makes sure only people with correct perms can see the details
if ( (!hasPerms($conn, "team", 1)) && $_SESSION['user'] != $_GET['user'])
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

$user_data = q($conn, 'SELECT
	COALESCE(`employee`.`rate`, 0) as `rate`,
	`total_shift`.`hours` AS `hours`,
	`total_shift`.`earnt` AS `earnt`,
	`total_payment`.`paid` AS `paid`,
	`total_shift`.`earnt` - `total_payment`.`paid` AS `outstanding`,
	DATE_FORMAT(COALESCE(`total_shift`.`start`, DATE(NOW())), "%d/%m/%y") AS `join`,
	`icon`,
	`employee`.`role`,
	`team`,
	`sessions`,
	`payments`
FROM `employee`
	LEFT JOIN `role` ON `role`.`role` = `employee`.`role`
	LEFT JOIN (SELECT `employee`, ROUND(SUM( TIME_TO_SEC(`length`)*`rate`/3600 ), 2) as `earnt`, SEC_TO_TIME(SUM(TIME_TO_SEC(`length`))) AS `hours`, MIN(`date`) AS `start` FROM `shift` GROUP BY `employee`) `total_shift`
		ON `total_shift`.`employee` = `employee`.`name`
	LEFT JOIN (SELECT `payee`, SUM(`amount`) AS `paid` FROM `payment` GROUP BY `payee`) `total_payment`
		ON `total_payment`.`payee` = `employee`.`name`
WHERE `employee`.`name` = ?
GROUP BY `employee`.`name`', ['args'=>$_GET['user']]);

$roles = ['None', 'View', 'Edit'];

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Profile</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<pre><?php //die(var_export($_SESSION['user_data'], true)) ?></pre>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/team">Team</a></h3>
		<h3><a href="/home/team/view_user.php?user=<?= $_GET['user']; ?>">View User</a></h3>
		<p>Profile details:</p>
		<ul>
			<li><a href="<?= "change_password.php?user={$_GET['user']}&redirect=http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" ?>">Change Password</a></li>
		</ul>

		<table>
			<tr>
				<th>Aspect</th>
				<th>Value</th>
			</tr>
			<tr>
				<td>Name</td>
				<td><?= $_SESSION['user'] ?></td>
			</tr>
			<tr>
				<td>Hourly Rate</td>
				<td>£<?= $user_data['rate'] ?></td>
			</tr>
			<tr>
				<td>Total Hours</td>
				<td><?= $user_data['hours'] ?></td>
			</tr>
			<tr>
				<td>Total Earnt</td>
				<td>£<?= $user_data['earnt'] ?></td>
			</tr>
			<tr>
				<td>Total Paid</td>
				<td>£<?= $user_data['paid'] ?></td>
			</tr>
			<tr>
				<td>Outstanding</td>
				<td>£<?= $user_data['outstanding'] ?></td>
			</tr>
			<tr>
				<td>Join Date</td>
				<td><?= $user_data['join'] ?></td>
			</tr>
			<tr>
				<td>Icon</td>
				<td><img src="<?= $user_data['icon'] ?>" alt="<?= $_SESSION['user'] ?>'s PP"  width="256" height="256"></td>
			</tr>
			<tr>
				<td>Permissions<br>(<?= $user_data['role'] ?>)</td>
				<td>
					<table>
						<tr>
							<td>Team</td>
							<td><?= $roles[$user_data['team']] ?></td>
						</tr>
						<tr>
							<td>Sessions</td>
							<td><?= $roles[$user_data['sessions']] ?></td>
						</tr>
						<tr>
							<td>Payments</td>
							<td><?= $roles[$user_data['payments']] ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

	</body>
</html>
