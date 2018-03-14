<?php
session_start();

// redirects users who aren't logged in
if (!isset($_SESSION['user']))
	header("Location: http://{$_SERVER['HTTP_HOST']}/login.php?redirect={$_SERVER['REQUEST_URI']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms($conn, "team", 2))
	header("Location: {$_SERVER['DOCUMENT_ROOT']}/permission_denied.php");

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

	// CHECK: PASSWORD HAS NON WHITESPACES
	if (0 === preg_match("/\S+/", $_POST['password']))
		$errors[] = "Must enter a password.";

	// CHECK: PASSWORD & PASSWORD VERIFY ARE THE SAME
	if ($_POST['password'] !== $_POST['verify'])
		$errors[] = "Passwords must be the same";

	// CHECK: ICON IS A LINK TO IMAGE
	if (0 !== preg_match('#[-a-zA-Z0-9@:%_\+.~\#?&//=]{2,256}\.[a-z]{2,4}\b(\/[-a-zA-Z0-9@:%_\+.~\#?&//=]*)?#si', $_POST['icon']))
	{
		$headers = get_headers($_POST['icon'], 1);
		if (strpos($headers['Content-Type'], 'image/') === false)
		    $errors[] = "Icon must be link to valid image";
	}

	// CHECK: RATE IS A NUMBER
	if (!is_numeric($_POST['rate']) &&  0 !== preg_match("/\S+/", $_POST['rate']))
		$errors[] = "Rate must be an integer or decimal value";

	// INSERT ROW
	if (!count($errors))
	{
		$row = array();
		foreach($_POST as $field => $value)
			if ($value !== "")
				$row[$field] = $value;

		unset($row['password']);
		unset($row['verify']);
		unset($row['submit']);

		$row['hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

		insertRow($conn, "employee", $row);

		header("Location: {$_GET['redirect']}");
	}
}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<link rel="stylesheet" href="/style.css">

		<title>Bootstrap 4 Starter Template</title>
	</head>
	<body>
		<div class="container">
		    <?php include "{$_SERVER['DOCUMENT_ROOT']}/header.php"; ?>

			<ul> <?php
				foreach ($errors as $error) printf("<li>%s</li>\n", $error);
			?>	</ul>

			<p id="required">Required Fields</p>

			<form action="create_user.php?redirect=<?= $_GET['redirect']; ?>" method="POST">
				<table>
					<tr>
						<td id="required">Name</td>
						<td><input type="username" name="name" value=<?= $_POST['name']; ?>></td>
					</tr>
					<tr>
						<td id="required">Password</td>
						<td><input type="password" name="password" value=<?= $_POST['password']; ?>></td>
					</tr>
					<tr>
						<td id="required">Password Verify</td>
						<td><input type="password" name="verify" value=<?= $_POST['verify']; ?>></td>
					</tr>
					<tr>
						<td>Email</td>
						<td><input type="email" name="email" value=<?= $_POST['email']; ?>></td>
					</tr>
					<tr>
						<td>Discord</td>
						<td><input type="number" name="discord" value=<?= $_POST['discord']; ?>></td>
					</tr>
					<tr>
						<td>Icon</td>
						<td><input type="icon" name="icon" value=<?= $_POST['icon']; ?>></td>
					</tr>
					<tr>
						<td>Rate</td>
						<td><input type="rate" name="rate" value=<?= $_POST['rate']; ?>></td>
					</tr>
					<tr>
						<td id="required">Role</td>
						<td>
							<select name="role" multiple> <?php
							foreach (getColumn($conn, "role", "role") as $cell)
								printf("<option value='%s'>%s</option>\n", $cell, $cell);
							?> </select>
						</td>
					</tr>
				</table>
				<input type="submit" value="Create User" name="submit" />
			</form>
		</div>

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</body>
</html>
