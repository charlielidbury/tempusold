<?php
session_start();

include "{$_SERVER['DOCUMENT_ROOT']}/src/db.php";

// makes sure only logged in users get here
if (!isset($_SESSION['user'])) header("Location: {$_SERVER['HTTP_HOST']}");

if (isset($_GET['user']))
	// if custom user the logged in user must have perms to edit member's details
	$user = $_GET['user'];

if ((!hasPerms("team", 2)) && $user != $_SESSION['user'])
{
	die("test");
	header("Location: http://{$_SERVER['HTTP_HOST']}/permission_denied.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') // login has been pressed
{
	$errors = array();

	// CHECK: PASSWORDS ARE THE SAME
	if ($_POST['new'] !== $_POST['verify'])
		$errors[] = "Passwords must match";

	// ACTUAL PASSWORD CHANGE
	if (!count($errors))
	{
		updateRow("employee", ["name" => $user], [
			"hash" => password_hash($_POST['new'], PASSWORD_DEFAULT)
		]);

		header("Location: {$_GET['redirect']}");
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Change Password</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>
		<h1><a href="/">Tempus</a></h1>
		<h2><a href="change_password.php">Change Password</a></h2>

		<p>Do you see https:// in the top left? No. Nothing about this site is secure. So please use a unique password for it.</p>

		<ul> <?php
			foreach ($errors as $error) echo "<li>$error</li>";
		?> </ul>

		<form action="<?= "http://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}" ?>" method="POST">
			<table>
				<tr>
					<td>New Password:</td>
					<td><input type="password" name="new" /></td>
				</tr>
				<tr>
					<td>New Password Verify:</td>
					<td><input type="password" name="verify" /></td>
				</tr>
			</table>
			<input type="submit" value="Change" name="submit" />
		</form>
	</body>
</html>
