<?php
session_start();

// makes sure only logged in users get here
if (!isset($_SESSION['user_data'])) header("Location: ..");

include "../src/db.php";

if (isset($_GET['user']))
{
	// if custom user the logged in user must have perms to edit member's details
	$user = $_GET['user'];
	if ($_SESSION['user_data']['perms']['members'] != "edit") header("Location: ../permission_denied.php");
} else {
	// otherwise just use the logged in user
	$user = $_SESSION['user_data']['name'];
}

$hash = $_SESSION['user_data']['hash'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') // login has been pressed
{
	// initial data validation
	$errors = array();

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
