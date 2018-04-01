<?php

session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include  "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// permissions check
if (!hasPerms($conn, "team", 1))
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// makes sure only users with team::edit past this point
	if (!hasPerms($conn, "team", 2))
		header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

	insertRow($conn, "role", $_POST);
}

$levels = ['None', 'View', 'Edit'];

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Manage Roles</title>
	</head>
	<body>
		<?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>
		<div class="container">

			<h1>Existing Roles</h1>
			<table>
				<tr>
					<th>Role</th>
					<th>Team</th>
					<th>Sessions</th>
					<th>Payments</th>
					<th>Actions</th>
				</tr>
				<?php foreach (q($conn, "SELECT * FROM role") as $role): ?>
					<tr>
						<td><?= $role['role'] ?></td>
						<td><?= $levels[ $role['team'] ] ?></td>
						<td><?= $levels[ $role['sessions'] ] ?></td>
						<td><?= $levels[ $role['payments'] ] ?></td>
						<td><a href="delete_role.php?redirect=manage_roles.php&role=<?= $role['role'] ?>">Delete</a></td>
					</tr>
				<?php endforeach; ?>
			</table>

			<?php if (hasPerms($conn, "team", 2)): ?>
				<h1>New Role</h1>
				<form action="<?= "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" method="post">
					<table>
						<tr>
							<td>Role Name</td>
							<td><input type="text" name="role"></td>
						</tr>
					</table>
					<table>
						<tr>
							<th>Perk</th>
							<th>Name</th>
							<th>View</th>
							<th>Edit</th>
						</tr>
						<?php foreach (['team', 'sessions', 'payments'] as $perk): ?>
							<tr>
								<td><?= ucwords($perk) ?></td>
								<td><input type="radio" name="<?= $perk ?>" value="0" checked></td>
								<td><input type="radio" name="<?= $perk ?>" value="1"></td>
								<td><input type="radio" name="<?= $perk ?>" value="2"></td>
							</tr>
						<?php endforeach; ?>
					</table>
					<input type="submit" value="Create Role">
				</form>
			<?php endif; ?>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
