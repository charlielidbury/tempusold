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

		$stmt = $conn->prepare("SELECT * FROM `employee` WHERE `name` = ?");

		if (!$stmt) die ("Statement failed to prepare: " . $mysqli->error);

		// execute query
		$stmt->bind_param("s", $_POST['username']);
		$stmt->execute();

		// get result
		$result = $stmt->get_result();

		if ($row = $result->fetch_assoc())
		{
			// user exists
			if (password_verify($_POST["password"], $row["hash"]))
			{
				// password is correct
				// THIS IS WHERE THE LOGIN HAPPENS
				session_start();
				$_SESSION['user_data'] = $row;

				// Prepare statement to get perms
				$stmt = $conn->prepare("SELECT * FROM `role` WHERE `role` = ?");

				if (!$stmt) die ("Statement failed to prepare: " . $mysqli->error);

				// execute query
				$stmt->bind_param("s", $_SESSION["user_data"]["role"]);
				$stmt->execute();

				// get result
				$result = $stmt->get_result();
				$_SESSION["user_data"]["perms"] = $result->fetch_assoc();

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
			Username: <input type="text" name="username" /> <br/>
			Password: <input type="password" name="password" /> <br/>

			<input type="submit" value="Login" name="submit" />
		</form>
	</body>
</html>
