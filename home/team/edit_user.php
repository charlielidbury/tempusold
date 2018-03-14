<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
$user = $_GET['user'];
if (!hasPerms($conn, "team", 2) && $_GET['user'] != $_SESSION['user'])
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");

// restricts the field if user is just editing their own
$restricted = !hasPerms($conn, "team", 2);

// ----- SAFE AREA -----
if ($_SERVER['REQUEST_METHOD'] == 'POST') // update has been pressed
{
	$errors = [];

	// CHECK: NAME HAS NON WHITESPACES
	if (0 === preg_match("/\S+/", $_POST['name']))
		$errors[] = "Must enter a username.";

	// CHECK: NAME DOESN'T CONTAIN SPACES
	if (0 !== preg_match('/\s/', $_POST['name']))
		$errors[] = "Username cannot contain spaces";

	// CHECK: ICON IS A LINK TO IMAGE
	if (0 !== preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $_POST['icon']))
	{
		$headers = get_headers($_POST['icon'], 1);
		if (strpos($headers['Content-Type'], 'image/') === false)
		    $errors[] = "Icon must be link to valid image";
	}

	// CHECK: RATE IS A NUMBER
	if (! (is_numeric($_POST['rate']) || $restricted) )
		$errors[] = "Rate must be an integer or decimal value";

	// UPDATE ROW
	if (!count($errors))
	{
		if ($restricted)
			$changes = array(
				"name" => $_POST['name'],
				"email" => $_POST['email'],
				"icon" => $_POST['icon']
			);
		else
			$changes = array(
				"name" => $_POST['name'],
				"email" => $_POST['email'],
				"icon" => $_POST['icon'],
				"rate" => $_POST['rate'],
				"role" => $_POST['role']
			);

		updateRow($conn, "employee", ["name" => $_POST['name']], $changes);
	}
}

$user_data = getRow($conn, "employee", ["name" => $_GET['user']]);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Tempus - Change Details</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<ul> <?php
				foreach ($errors as $error) printf("<li>%s</li>\n", $error);
			?>	</ul>

			<form action="<?= "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}" ?>" method="POST">
				<table>
					<tr>
						<th>Aspect</th>
						<th>Value</th>
					</tr>
					<tr>
						<td>Name</td>
						<td><input type="username" name="name" value='<?= $user_data['name']; ?>'></td>
					</tr>
					<tr>
						<td>Password</td>
						<td><a href="change_password.php?user=<?= $user_data['name'] ?>">Change</a></td>
					</tr>
					<tr>
						<td>Email</td>
						<td><input type="email" name="email" value="<?= $user_data['email']; ?>" /></td>
					</tr>
					<tr>
						<td>Discord</td>
						<td><?= $user_data['discord']; ?></td>
					</tr>
					<tr>
						<td>Icon</td>
						<td><input type="icon" name="icon" value="<?= $user_data['icon'] ?>" /></td>
						<td><img src="<?= $user_data['icon']; ?>" alt="No Icon Set" width="128"></td>
					</tr>
					<?php if (!$restricted): ?>
						<tr>
							<td>Rate</td>
							<td><input type="rate" name="rate" value="<?= $user_data['rate'] ?>" /></td>
						</tr>
						<tr>
							<td>Rank</td>
							<td><a href="../wip.php">Change</a></td>
						</tr>
						<tr>
							<td>Role</td>
							<td>
								<select name="role" multiple> <?php
								foreach (getColumn($conn, "role", "role") as $cell)
									if ($cell == $user_data['role'])
										printf("<option selected='selected' value='%s'>%s</option>\n", $cell, $cell);
									else
										printf("<option value='%s'>%s</option>\n", $cell, $cell);
								?> </select>
							</td>
						</tr>
					<?php endif ?>
				</table>
				<input type="submit" value="Update" name="submit" />
			</form>
			<?php if (!$restricted): ?>
				<a href="<?= "delete_user.php?user={$user_data['name']}&redirect={$_GET['redirect']}" ?>">
					Delete User
				</a>
			<?php endif; ?>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
