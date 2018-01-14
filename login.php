<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') // login has been pressed
{
	// initial data validation
	$errors = array();

	if (0 === preg_match("/\S+/", $_POST['username']))
	{
		// checks username has non-whitespaces
		$errors[] = "Must enter a username.";
	} else {

		// getting the data from the database
		include "./src/db.php";

		$row = getRow($conn, "employee", "name", $_POST['username']);

		if ($row)
		{
			// user exists
			if (password_verify($_POST["password"], $row["hash"]))
			{
				// password is correct
				// THIS IS WHERE THE LOGIN HAPPENS
				session_start();

				$_SESSION['user_data'] = $row;
				$_SESSION["user_data"]["perms"] = getRow($conn, "role", "role", $_SESSION["user_data"]["role"]);

				//die(var_dump($_SESSION['user_data']));

				// Sends user to home page
				header("Location: /home/");

			} else {
				// password is incorrect
				$errors[] = "Incorrect password";
			}

		} else {
			// user doesn't exist
			$errors[] = "User ${_POST['username']} does not exist.";
		}
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
