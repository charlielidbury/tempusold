<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') // login has been pressed
{
	// getting the data from the database
	include "src/db.php";

	$hash = getCell($conn, "hash", "employee", "name", $_POST['username']);

	$errors = array();

	// CHECK: USERNAME ISN'T BLANK
	if (0 === preg_match("/\S+/", $_POST['username']))
		$errors[] = "Must enter a username.";

	// CHECK: USER EXISTS
	if (!$hash)
		$errors[] = "User ${_POST['username']} does not exist.";

	// CHECK: PASSWORD IS CORRECT
	if (!password_verify($_POST["password"], $hash))
		$errors[] = "Incorrect password";

	// ACTUAL LOGIN
	if (!count($errors))
	{
		session_start();

		$_SESSION['user'] = $_POST['username'];

		// Sends user to home page
		header("Location: /home/");
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Tempus - Login</title>
		<link rel="stylesheet" href="styles.css">
	</head>
	<body>
		<h1><a href=".">Tempus</a></h1>
		<h2><a href="login.php">Login</a></h2>

		<p>Do you see https:// in the top left? No. Nothing about this site is secure. So please use a unique password for it.</p>

		<ul> <?php
			foreach ($errors as $error) echo "<li>$error</li>";
		?> </ul>

		<form action="." method="POST">
			<table>
				<tr>
					<td>Username:</td>
					<td><input type="text" name="username" /> <br/></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password" /> <br/></td>
				</tr>
			</table>
			<input type="submit" value="Login" name="submit" />
		</form>
	</body>
</html>
