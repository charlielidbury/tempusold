<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Profile</title>
		<link rel="stylesheet" type="test/css" href="styles.css">
		<style>
			table {
				border-collapse: collapse;
			}
			
			td {
				border: 1px solid black;
			}
		</style>
	</head>
	<body>
		<h1><a href="index.php">Tempus</a></h1>
		<h2><a href="home.php">Home</a></h2>
		<h3><a href="profile.php">Profile</a></h3>
		<p>Profile details:</p>
		<table>
			<tr>
				<td>Username</td>
				<td><?= $_SESSION["userdata"]["name"]; ?></td>
			</tr>
			<tr>
				<td>Password</td>
				<td><a href="#">Change</a></td>
			</tr>
			<tr>
				<td>Icon</td>
				<td><img src="<?= $_SESSION["userdata"]["icon"]; ?>" alt="User Icon"></td>
			</tr>
			<tr>
				<td>Email</td>
				<td><?= $_SESSION["userdata"]["email"]; ?></td>
			</tr>
			<tr>
				<td>Discord</td>
				<td><?= $_SESSION["userdata"]["discord"]; ?></td>
			</tr>
			<tr>
				<td>Rate</td>
				<td><?= $_SESSION["userdata"]["rate"]; ?></td>
			</tr>
			<tr>
				<td>Permissions</td>
				<td>
					<table>
						<tr>
							<td>Payments</td>
							<td><?= $_SESSION["userdata"]["perms"]["payments"]; ?></td>
						</tr>
						<tr>
							<td>Members</td>
							<td><?= $_SESSION["userdata"]["perms"]["members"]; ?></td>
						</tr>
						<tr>
							<td>Shifts</td>
							<td><?= $_SESSION["userdata"]["perms"]["shifts"]; ?></td>
						</tr>
						<tr>
							<td>Sessions</td>
							<td><?= $_SESSION["userdata"]["perms"]["sessions"]; ?></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
</html>
