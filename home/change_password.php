<?php
session_start();

include "../src/db.php";

if (isset($_GET['user']))
{
	$user = $_GET['user'];
	$hash = getCell($conn, "hash", "employee", "name", $user);
} else {
	$user = $_SESSION['user_data']['name'];
	$hash = $_SESSION['user_data']['hash'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') // login has been pressed
{
	// initial data validation
	$errors = array();

	if (password_verify($_POST['old'], $hash))
	{
		// password correct
		if ($_POST['new'] === $_POST['verify'])
		{
			// passwords are the same (no typos!)
			// update the database

			$new_hash = password_hash($_POST['new'], PASSWORD_DEFAULT);

			$stmt = $conn->prepare("UPDATE `employee` SET `hash` = ? WHERE `name` = ?");
			if (!$stmt) die ("Statement failed to prepare: " . $mysqli->error);

			$stmt->bind_param("ss", $new_hash, $user);
			$stmt->execute();

			header("Location: .");

		} else {
			// passwords are not the same
			$errors[] = "Passwords must match";
		}
	} else {
		// password incorrect
		$errors[] = "Incorrect Password";
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

		<form action="change_password.php<?php if (isset($_GET['user'])) echo '?user=' . $_GET['user']; ?>" method="POST">
			<table>
				<tr>
					<td>Old Password:</td>
					<td><input type="password" name="old" /> <br/></td>
				</tr>
				<tr>
					<td>New Password:</td>
					<td><input type="password" name="new" /> <br/></td>
				</tr>
				<tr>
					<td>New Password Verify:</td>
					<td><input type="password" name="verify" /> <br/></td>
				</tr>
			</table>
			<input type="submit" value="Change" name="submit" />
		</form>
	</body>
</html>
