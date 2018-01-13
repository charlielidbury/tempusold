<?php
session_start();
include "../src/db.php";

if (!isset($_SESSION['user_data'])) header("Location: ..");
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
		<h1><a href="..">Tempus</a></h1>
		<h2><a href=".">Home</a></h2>
		<h3><a href="profile.php">Profile</a></h3>
		<p>Profile details:</p>
		<table>
			<tr>
				<td>Username</td>
				<td><?= $_SESSION["user_data"]["name"]; ?></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><a href="change_password.php">Change</a></td>
			</tr>
			<tr>
				<td>Icon</td>
				<td><img src="<?= $_SESSION["user_data"]["icon"]; ?>" alt="User Icon" width="256"></td>
			</tr>
			<tr>
				<td>Email</td>
				<td><?= $_SESSION["user_data"]["email"]; ?></td>
			</tr>
			<tr>
				<td>Discord</td>
				<td><?= $_SESSION["user_data"]["discord"]; ?></td>
			</tr>
			<tr>
				<td>Rate</td>
				<td><?= $_SESSION["user_data"]["rate"]; ?></td>
			</tr>
			<tr>
				<td>Permissions<br>(<?= $_SESSION["user_data"]["role"]; ?>)</td>
				<td>
					<table>
						<tr>
							<td>Payments</td>
							<td><?= $_SESSION["user_data"]["perms"]["payments"]; ?></td>
						</tr>
						<tr>
							<td>Members</td>
							<td><?= $_SESSION["user_data"]["perms"]["members"]; ?></td>
						</tr>
						<tr>
							<td>Shifts</td>
							<td><?= $_SESSION["user_data"]["perms"]["shifts"]; ?></td>
						</tr>
						<tr>
							<td>Sessions</td>
							<td><?= $_SESSION["user_data"]["perms"]["sessions"]; ?></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>Stats</td>
				<td>
					<table>
						<tr>
							<td>Total Hours</td>
							<td><?= getCell($conn, "Total Hours", "view_employee", "name", $_SESSION['user_data']['name']); ?></td>
						</tr>
						<tr>
							<td>Total Earnt</td>
							<td>£<?= getCell($conn, "Total Earnt", "view_employee", "name", $_SESSION['user_data']['name']); ?></td>
						</tr>
						<tr>
							<td>Outstanding</td>
							<td>£<?= getCell($conn, "Outstanding", "view_employee", "name", $_SESSION['user_data']['name']); ?></td>
						</tr>
						<tr>
							<td>Join Date</td>
							<td><?= getCell($conn, "Join Date", "view_employee", "name", $_SESSION['user_data']['name']); ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>
