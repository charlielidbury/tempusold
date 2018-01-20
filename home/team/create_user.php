<?php
session_start();

// makes sure only logged in users get here
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['DOCUMENT_ROOT']}");

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// if custom user the logged in user must have perms to edit member's details
if (!hasPerms("team", 2))
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

<<<<<<< HEAD
		insertRow("employee", $row);
=======
		insertRow($conn, "employee", $row);
>>>>>>> parent of 8f19b50... Added viewing, editing and adding employees to sessions. Woot!

		header("Location: {$_GET['redirect']}");
	}
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Change Details</title>
		<link rel="stylesheet" href="/css/style.css"/>
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="/home">Home</a></h2>
		<h3><a href="/home/team">Team</a></h3>
		<h3><a href="/home/tean/change_details.php">Create User</a></h3>

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
						foreach (getColumn("role", "role") as $cell)
							printf("<option value='%s'>%s</option>\n", $cell, $cell);
						?> </select>
					</td>
				</tr>
			</table>
			<input type="submit" value="Create User" name="submit" />
		</form>
	</body>
</html>
