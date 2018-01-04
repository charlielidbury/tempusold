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
		include "db.php";

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
				session_start();
				$_SESSION['user_data'] = $row;
				header("Location: .");

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
	</head>
	<body>
		<h1><a href=".">Tempus</a></h1>
		<h2><a href="login.php">Login</a></h2>

		<p>Please login here; to create an account please contact an admin who can make one for you.</p>

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
